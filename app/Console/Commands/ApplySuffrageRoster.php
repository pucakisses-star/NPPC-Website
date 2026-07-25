<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Ensures every woman in the 168-name National Woman's Party "Appendix 4"
 * roster (database/data/rosters/nwp-suffrage-roster.json) is in the database,
 * and applies ONLY the incarceration intervals that a court/newspaper audit
 * could establish confidently.
 *
 * Per that audit, Doris Stevens's appendix records the sentence imposed — not
 * the day of entry or the actual time served — so we never turn a sentence into
 * a release date. Confident cohort intervals (below) are applied as real
 * incarceration/release dates; everyone else gets the term text but NO release,
 * which the model reads as "length unknown" (imprisoned_for_days stays null for
 * a released prisoner without a release date).
 *
 * Idempotent: prisoners are matched by name/aka and created only if missing; a
 * second case is never added. Supports --dry-run.
 */
final class ApplySuffrageRoster extends Command
{
    protected $signature = 'prisoners:apply-suffrage-roster {--dry-run : Preview without writing}';

    protected $description = 'Add/complete the 168-name NWP roster and apply audited incarceration intervals';

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

        $base = [
            'ideologies' => ['Women\'s suffrage'],
            'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
            'era' => '1910s',
            'gender' => 'Female',
            'state' => 'District of Columbia',
            'in_custody' => false,
            'released' => true,
        ];

        $created = 0; $enriched = 0; $dated = 0; $undated = 0;
        foreach ($roster as $r) {
            $name = trim($r['name'] ?? '');
            if ($name === '') { continue; }
            $first = $r['first_name'] ?? null;
            $last = $r['last_name'] ?? null;
            $aka = $r['aka'] ?? null;
            $summary = trim($r['sentence'] ?? '');
            $cohorts = is_array($r['cohorts'] ?? null) ? $r['cohorts'] : [];

            // Find by name or aka.
            $p = Prisoner::withoutGlobalScopes()
                ->where(function ($q) use ($name, $aka) {
                    $q->whereRaw('LOWER(name) = ?', [strtolower($name)]);
                    if ($aka) { $q->orWhereRaw('LOWER(name) = ?', [strtolower($aka)]); }
                })
                ->first();

            if (! $p) {
                $this->line(($dry ? '  would create: ' : '  create: ').$name);
                if (! $dry) {
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
                    $p = Prisoner::withoutGlobalScopes()->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                }
                $created++;
            } else {
                // Merge NWP affiliation onto an existing record without clobbering.
                if (! $dry) {
                    $aff = is_array($p->affiliation) ? $p->affiliation : [];
                    $merged = array_values(array_unique(array_merge($aff, ['National Woman\'s Party', 'Silent Sentinels'])));
                    if ($merged !== $aff) { $p->affiliation = $merged; $p->save(); $enriched++; }
                }
            }

            if (! $p) { continue; }

            // Resolve the earliest confident cohort interval.
            $best = null; // [incInt, inc, rel]
            foreach ($cohorts as $key) {
                if (! isset(self::COHORTS[$key])) { continue; }
                [$inc, $rel] = self::COHORTS[$key];
                $incInt = $inc[0] * 10000 + $inc[1] * 100 + $inc[2];
                if ($best === null || $incInt < $best[0]) { $best = [$incInt, $inc, $rel]; }
            }

            if ($best === null) { $undated++; continue; }

            if (! $dry) {
                $case = $p->cases()->first();
                if (! $case) {
                    $case = $p->cases()->create([
                        'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                        'convicted' => 'Imprisoned in the 1917-1919 National Woman\'s Party suffrage campaign',
                        'sentence' => $summary !== '' ? $summary : null,
                    ]);
                }
                [, $inc, $rel] = $best;
                $case->setPartialDate('incarceration_date', $inc[0], $inc[1], $inc[2]);
                if ($rel !== null) {
                    $case->setPartialDate('release_date', $rel[0], $rel[1], $rel[2]);
                } else {
                    $case->release_date = null; // audit: release unresolved — do not fabricate
                }
                $case->save();
            }
            $dated++;
        }

        \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());

        $this->newLine();
        $verb = $dry ? 'Would process' : 'Processed';
        $this->info("{$verb}: ".count($roster)." roster entries. created={$created}, enriched_affiliation={$enriched}, given_audited_dates={$dated}, left_undated={$undated}.");
        if ($dry) { $this->warn('Dry run — no changes written.'); }

        return self::SUCCESS;
    }
}
