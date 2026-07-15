<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Imports political prisoners from the corrected OCR audit of the 1921 U.S.
 * Senate hearing "Amnesty and Pardon for Political Prisoners"
 * (archive.org/details/amnestyandpardo00stergoog), from
 * database/data/amnesty_pardon_audit.json.
 *
 * Only the reliable sheets are included: the image-verified conviction rows
 * (printed pp. 87-91), the manually transcribed 1921 parole list, and the
 * medium/high-confidence presidential-action (pardon / commutation) table.
 * The ~3,995 quarantined OCR fragments and the ~600 conservatively-retained
 * (not word-verified) OCR rows are intentionally excluded.
 *
 * For each person the command MATCHES against the existing roster (normalized
 * name, then last-name + first-name/initial):
 *   - already present  -> fills ONLY the empty case fields (charges, sentence,
 *                         incarceration/release date, institution). Never
 *                         overwrites existing values.
 *   - not present      -> creates the prisoner (era 1910s, a description that
 *                         records the offense, sentence, and any pardon /
 *                         commutation / parole disposition) and a case.
 *
 * Idempotent and safe to re-run. Use --dry to preview counts without writing.
 */
final class ImportAmnestyPardonAudit extends Command
{
    protected $signature = 'prisoners:import-amnesty-pardon-audit {--dry : Preview without writing}';

    protected $description = 'Add/enrich WWI political prisoners from the 1921 "Amnesty and Pardon" audit (create-if-missing, fill-if-empty)';

    /** @var array<string,Prisoner> */
    private array $byNorm = [];

    /** @var array<string,array<int,array{0:string,1:Prisoner}>> */
    private array $byLast = [];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $rows = json_decode((string) file_get_contents(database_path('data/amnesty_pardon_audit.json')), true);
        if (! is_array($rows)) {
            $this->error('Could not read the audit JSON.');

            return self::FAILURE;
        }

        $this->buildIndex();

        $created = 0;
        $filled = 0;
        $noop = 0;

        foreach ($rows as $r) {
            $prisoner = $this->match($r['name']);

            if ($prisoner) {
                $changed = $this->fillCase($prisoner, $r, $dry);
                if ($changed) {
                    $filled++;
                    $this->line('fill: '.$prisoner->name.' ('.implode(', ', $changed).')');
                } else {
                    $noop++;
                }

                continue;
            }

            if ($dry) {
                $created++;
                $this->line('would create: '.$r['name']);

                continue;
            }

            $new = $this->createPrisoner($r);
            $created++;
            $this->info('created: '.$new->name);
            $this->indexPrisoner($new);
        }

        if (! $dry && ($created > 0 || $filled > 0)) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->info(($dry ? '[dry] ' : '')."Created {$created}, filled {$filled}, unchanged {$noop}.");
        if (! $dry && $created > 0) {
            $this->comment('Re-run prisoners:normalize-sort-order to settle ordering.');
        }

        return self::SUCCESS;
    }

    private function buildIndex(): void
    {
        Prisoner::withUnderReview()->select(['id', 'name', 'aka'])->chunkById(1000, function ($chunk) {
            foreach ($chunk as $p) {
                $this->indexPrisoner($p);
            }
        });
    }

    private function indexPrisoner(Prisoner $p): void
    {
        $names = array_merge([$p->name], preg_split('/[;,\/]/', (string) $p->aka) ?: []);
        foreach ($names as $nm) {
            $n = $this->norm($nm);
            if ($n === '') {
                continue;
            }
            $this->byNorm[$n] ??= $p;
            $parts = explode(' ', $n);
            if (count($parts) >= 2) {
                $this->byLast[$parts[count($parts) - 1]][] = [$parts[0], $p];
            }
        }
    }

    private function match(string $name): ?Prisoner
    {
        $n = $this->norm($name);
        $parts = explode(' ', $n);
        if (count($parts) < 2) {
            return null;
        }
        if (isset($this->byNorm[$n])) {
            return $this->byNorm[$n];
        }
        $first = $parts[0];
        $last = $parts[count($parts) - 1];
        foreach ($this->byLast[$last] ?? [] as [$cf, $p]) {
            if ($cf === $first) {
                return $p; // spelled first name matches
            }
        }
        if (strlen($first) <= 2) { // initials-only: match on first initial + last
            foreach ($this->byLast[$last] ?? [] as [$cf, $p]) {
                if ($cf[0] === $first[0]) {
                    return $p;
                }
            }
        }

        return null;
    }

    /** @return string[] list of fields filled (empty = no change) */
    private function fillCase(Prisoner $p, array $r, bool $dry): array
    {
        $filled = [];
        $case = $p->cases()->first() ?? new PrisonerCase(['prisoner_id' => $p->id]);
        $case->prisoner_id = $p->id;

        if (! empty($r['charges']) && empty($case->charges)) {
            $case->charges = rtrim($r['charges'], '.').'.';
            $filled[] = 'charges';
        }
        if (! empty($r['sentence']) && empty($case->sentence)) {
            $case->sentence = rtrim($r['sentence'], '.').'.';
            $filled[] = 'sentence';
        }
        if (! empty($r['inc']) && empty($case->incarceration_date)) {
            $case->setPartialDate('incarceration_date', $r['inc'][0], $r['inc'][1] ?? null, $r['inc'][2] ?? null);
            $filled[] = 'incarceration_date';
        }
        if (! empty($r['rel']) && empty($case->release_date)) {
            $case->setPartialDate('release_date', $r['rel'][0], $r['rel'][1] ?? null, $r['rel'][2] ?? null);
            $filled[] = 'release_date';
        }
        if (! empty($r['institution']) && empty($case->institution_id)) {
            $case->institution_id = Institution::firstOrCreate(['name' => $r['institution']])->id;
            $filled[] = 'institution';
        }
        if (empty($case->imprisoned_for_days) && ! empty($r['inc']) && ! empty($r['rel'])
            && ($r['inc'][2] ?? null) && ($r['rel'][2] ?? null)) {
            $case->imprisoned_for_days = Carbon::create(...$r['inc'])->diffInDays(Carbon::create(...$r['rel']));
            $filled[] = 'imprisoned_for_days';
        }

        if ($filled && ! $dry) {
            $case->save();
        }

        return $filled;
    }

    private function createPrisoner(array $r): Prisoner
    {
        $charges = $r['charges'] ?? 'Wartime prosecution (Espionage, Sedition, or Selective Service Act)';
        $sentence = $r['sentence'] ?? 'Sentence not stated';
        $inst = ! empty($r['institution']) ? " Held at the {$r['institution']}." : '';
        $when = '';
        if (! empty($r['inc']) && ($r['inc'][2] ?? null)) {
            $when = ' Sentenced '.Carbon::create(...$r['inc'])->format('F j, Y').'.';
        }
        $disp = ! empty($r['disposition']) ? " Later disposition: {$r['disposition']}." : '';
        $desc = "{$r['name']} was prosecuted under United States wartime statutes of the World War I era "
            .'(chiefly the Espionage Act, the Sedition Act, or the Selective Service Act). '
            ."Recorded offense: {$charges}. Sentence: {$sentence}.{$when}{$inst}{$disp} "
            .'This case is documented in the 1921 U.S. Senate hearing "Amnesty and Pardon for Political '
            .'Prisoners" (digitized by the Internet Archive).';

        return DB::transaction(function () use ($r, $charges, $sentence, $desc) {
            $prisoner = Prisoner::create([
                'name' => $r['name'],
                'first_name' => $r['first'] ?? null,
                'middle_name' => $r['middle'] ?? null,
                'last_name' => $r['last'] ?? null,
                'gender' => $r['gender'] ?? null,
                'era' => '1910s',
                'ideologies' => $r['ideologies'] ?? [],
                'affiliation' => $r['affiliation'] ?? [],
                'description' => $desc,
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'currently_in_exile' => false,
                'awaiting_trial' => false,
            ]);

            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->prisoner_id = $prisoner->id;
            $case->charges = rtrim($charges, '.').'.';
            $case->sentence = rtrim($sentence, '.').'.';
            $case->convicted = ! empty($r['disposition'])
                ? "Yes — {$r['disposition']} (per the 1921 Senate \"Amnesty and Pardon for Political Prisoners\" hearing)."
                : 'Yes — listed in the 1921 Senate "Amnesty and Pardon for Political Prisoners" hearing.';
            if (! empty($r['inc'])) {
                $case->setPartialDate('incarceration_date', $r['inc'][0], $r['inc'][1] ?? null, $r['inc'][2] ?? null);
            }
            if (! empty($r['rel'])) {
                $case->setPartialDate('release_date', $r['rel'][0], $r['rel'][1] ?? null, $r['rel'][2] ?? null);
            }
            if (! empty($r['institution'])) {
                $case->institution_id = Institution::firstOrCreate(['name' => $r['institution']])->id;
            }
            if (! empty($r['inc']) && ! empty($r['rel']) && ($r['inc'][2] ?? null) && ($r['rel'][2] ?? null)) {
                $case->imprisoned_for_days = Carbon::create(...$r['inc'])->diffInDays(Carbon::create(...$r['rel']));
            }
            $case->save();

            return $prisoner;
        });
    }

    private function norm(string $s): string
    {
        $s = strtolower((string) iconv('UTF-8', 'ASCII//TRANSLIT', $s));
        $s = preg_replace('/\b(jr|sr|ii|iii|iv|rev|dr|mr|mrs|miss|hon|father|prof)\b/', ' ', $s);
        $s = preg_replace('/[^a-z ]/', ' ', $s);

        return trim(preg_replace('/\s+/', ' ', $s));
    }
}
