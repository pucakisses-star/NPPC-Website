<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Ensures every woman in the National Woman's Party "Appendix 4" roster
 * (database/data/rosters/nwp-suffrage-roster.json) is in the database, and
 * applies ONLY the incarceration intervals a court/newspaper audit could
 * establish confidently.
 *
 * Per that audit, Doris Stevens's appendix records the sentence imposed — not
 * the day of entry or the actual time served — so a sentence is never turned
 * into a release date. Confident cohort intervals (below) are applied as real
 * incarceration/release dates; everyone else gets the term text but NO release,
 * which the model reads as "length unknown" (imprisoned_for_days stays null for
 * a released prisoner without a release date). Where a woman had several terms
 * her single case takes her earliest confident interval.
 *
 * Matching is tolerant of name variants (titles, middle names/initials,
 * Katharine/Katherine) so existing records — e.g. "Lavinia Lloyd Dock" vs the
 * roster's "Lavinia L. Dock", or "Alice Cosu" vs "Alice M. Cosu" — are updated
 * rather than duplicated. Idempotent; a second case is never added. Run
 * --dry-run first and check the "create" list for any unexpected duplicates.
 */
final class ApplySuffrageRoster extends Command
{
    protected $signature = 'prisoners:apply-suffrage-roster {--dry-run : Preview without writing}';

    protected $description = 'Add/complete the NWP suffrage roster and apply audited incarceration intervals';

    /** cohort key => [ [incY,incM,incD], [relY,relM,relD]|null ] */
    private const COHORTS = [
        'first_six'         => [[1917, 6, 27], null],
        'july4'             => [[1917, 7, 6], [1917, 7, 8]],
        'july14'            => [[1917, 7, 17], [1917, 7, 19]],
        'aug17_1917'        => [[1917, 8, 17], [1917, 9, 11]],
        'sept4_1917'        => [[1917, 9, 4], null],
        'oct8_1917'         => [[1917, 10, 8], null],
        'oct15_winslow'     => [[1917, 10, 15], [1917, 11, 27]],
        'oct15_1917'        => [[1917, 10, 15], null],
        'oct20_1917'        => [[1917, 10, 20], null],
        'alice_paul'        => [[1917, 10, 22], [1917, 11, 27]],
        'nov10'             => [[1917, 11, 14], [1917, 11, 28]],
        'nov10_nolan'       => [[1917, 11, 14], [1917, 11, 20]],
        'aug1918_first'     => [[1918, 8, 15], [1918, 8, 20]],
        'jan13_1919'        => [[1919, 1, 13], null],
        'feb9_1919'         => [[1919, 2, 10], [1919, 2, 13]],
        'boston_released26' => [[1919, 2, 25], [1919, 2, 26]],
        'boston_1919'       => [[1919, 2, 25], null],
    ];

    /** roster name (lower) => forced normalized key, to reconcile cross-command variants */
    private const SYNONYM = ['mrs. robert walker' => 'mary walker'];

    /** roster names that must always be created fresh (distinct person that would else collide) */
    private const FORCE_NEW = ['lucy g. branham'];

    private const TITLES = ['mrs', 'mr', 'dr', 'miss', 'rev', 'mme', 'madame'];

    private function keyOf(string $name): string
    {
        $n = strtolower(str_replace(['.', ','], '', $name));
        $toks = preg_split('/\s+/', trim($n)) ?: [];
        while (count($toks) > 1 && in_array($toks[0], self::TITLES, true)) {
            array_shift($toks);
        }
        $toks = array_map(fn ($t) => $t === 'katharine' ? 'katherine' : $t, $toks);
        if (count($toks) === 0) {
            return $n;
        }

        return count($toks) === 1 ? $toks[0] : $toks[0].' '.end($toks);
    }

    /** first/last name for a created record, with any leading title stripped */
    private function nameParts(string $name): array
    {
        $toks = preg_split('/\s+/', trim($name)) ?: [];
        while (count($toks) > 1 && in_array(strtolower(str_replace('.', '', $toks[0])), self::TITLES, true)) {
            array_shift($toks);
        }

        return [$toks[0] ?? $name, count($toks) > 1 ? end($toks) : ($toks[0] ?? $name)];
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $path = base_path('database/data/rosters/nwp-suffrage-roster.json');
        if (! is_file($path)) {
            $this->error("Roster file not found: {$path}");

            return self::FAILURE;
        }
        $roster = json_decode(file_get_contents($path), true);
        if (! is_array($roster)) {
            $this->error('Roster JSON did not parse to an array.');

            return self::FAILURE;
        }

        // Exact-name map over ALL prisoners; normalized map scoped to plausible
        // suffragists (era 1910s or an NWP affiliation) to avoid false merges
        // with unrelated people who share a first+last name.
        $exact = [];
        foreach (Prisoner::withoutGlobalScopes()->get(['id', 'name']) as $p) {
            $exact[strtolower($p->name)] = $p->id;
        }
        $norm = [];
        $scoped = Prisoner::withoutGlobalScopes()
            ->where(fn ($q) => $q->where('era', '1910s')
                ->orWhere('affiliation', 'like', '%Woman\'s Party%')
                ->orWhere('affiliation', 'like', '%Silent Sentinel%'))
            ->get(['id', 'name']);
        foreach ($scoped as $p) {
            $norm[$this->keyOf($p->name)] ??= $p->id;
        }

        $base = [
            'ideologies' => ['Women\'s suffrage'],
            'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
            'era' => '1910s',
            'gender' => 'Female',
            'state' => 'District of Columbia',
            'in_custody' => false,
            'released' => true,
        ];

        $created = 0; $matched = 0; $dated = 0; $undated = 0;
        foreach ($roster as $r) {
            $name = trim($r['name'] ?? '');
            if ($name === '') { continue; }
            $aka = $r['aka'] ?? null;
            $summary = trim($r['sentence'] ?? '');
            $cohorts = is_array($r['cohorts'] ?? null) ? $r['cohorts'] : [];
            $lname = strtolower($name);

            $forceNew = in_array($lname, self::FORCE_NEW, true);

            // Resolve to an existing prisoner id (unless forced new).
            $id = null;
            if (! $forceNew) {
                $keys = [self::SYNONYM[$lname] ?? $this->keyOf($name)];
                if ($aka) { $keys[] = $this->keyOf($aka); }
                if (isset($exact[$lname])) {
                    $id = $exact[$lname];
                } elseif ($aka && isset($exact[strtolower($aka)])) {
                    $id = $exact[strtolower($aka)];
                } else {
                    foreach ($keys as $k) {
                        if (isset($norm[$k])) { $id = $norm[$k]; break; }
                    }
                }
            }

            $p = $id ? Prisoner::withoutGlobalScopes()->find($id) : null;

            if (! $p) {
                $this->line(($dry ? '  would create: ' : '  create: ').$name);
                $created++;
                if (! $dry) {
                    [$first, $last] = $this->nameParts($name);
                    $payload = array_merge($base, [
                        'name' => $name,
                        'first_name' => $first,
                        'last_name' => $last,
                        'description' => "{$name} was a member of the National Woman's Party and one of the \"Silent Sentinels\" imprisoned during the 1917-1919 campaign to picket the Wilson White House for woman suffrage. ".($aka ? "She appears in Doris Stevens's roster as {$aka}. " : '')."Doris Stevens recorded her term as: {$summary}",
                        'cases' => [[
                            'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                            'convicted' => 'Imprisoned in the 1917-1919 National Woman\'s Party suffrage campaign',
                            'sentence' => $summary !== '' ? $summary : null,
                        ]],
                    ]);
                    if ($aka) { $payload['aka'] = $aka; }
                    $this->call('prisoner:add', ['json' => json_encode($payload)]);
                    $p = Prisoner::withoutGlobalScopes()->whereRaw('LOWER(name) = ?', [$lname])->first();
                    if ($p) {
                        $exact[$lname] = $p->id;
                        $norm[$this->keyOf($name)] ??= $p->id;
                    }
                }
            } else {
                $matched++;
                // Merge NWP affiliation without clobbering existing tags.
                $aff = is_array($p->affiliation) ? $p->affiliation : [];
                $merged = array_values(array_unique(array_merge($aff, ['National Woman\'s Party', 'Silent Sentinels'])));
                if (! $dry && $merged !== $aff) { $p->affiliation = $merged; $p->save(); }
            }

            if (! $p) { continue; }

            // Earliest confident cohort interval.
            $best = null;
            foreach ($cohorts as $key) {
                if (! isset(self::COHORTS[$key])) { continue; }
                [$inc, $rel] = self::COHORTS[$key];
                $incInt = $inc[0] * 10000 + $inc[1] * 100 + $inc[2];
                if ($best === null || $incInt < $best[0]) { $best = [$incInt, $inc, $rel]; }
            }
            if ($best === null) { $undated++; continue; }
            $dated++;

            if (! $dry) {
                $case = $p->cases()->first() ?: $p->cases()->create([
                    'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                    'convicted' => 'Imprisoned in the 1917-1919 National Woman\'s Party suffrage campaign',
                    'sentence' => $summary !== '' ? $summary : null,
                ]);
                [, $inc, $rel] = $best;
                $case->setPartialDate('incarceration_date', $inc[0], $inc[1], $inc[2]);
                if ($rel !== null) {
                    $case->setPartialDate('release_date', $rel[0], $rel[1], $rel[2]);
                } else {
                    $case->release_date = null; // audit: release unresolved — do not fabricate
                }
                $case->save();
            }
        }

        \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());

        $this->newLine();
        $this->info(($dry ? 'DRY RUN — ' : '')."roster=".count($roster).", created={$created}, matched_existing={$matched}, given_audited_dates={$dated}, left_undated={$undated}.");
        if ($dry) { $this->warn('No changes written. Review the "create" lines above for any unexpected duplicates before running for real.'); }

        return self::SUCCESS;
    }
}
