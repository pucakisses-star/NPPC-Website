<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Cleans up the two South Texas Sanctuary Movement records — Stacey Merkt and
 * Jack Elder — which exist in the database as bare/duplicate stubs (an earlier
 * Airtable import created them as empty names, and prisoners:add-sanctuary-movement
 * skips any name that already exists, so it never filled them in).
 *
 * It does three things, all idempotent and safe to run repeatedly:
 *   1) De-duplicates Merkt: if both "stacey-merkt" and "stacey-lynn-merkt" exist,
 *      it moves the duplicate's cases onto the survivor and deletes the duplicate.
 *   2) Enriches each record by filling ONLY blank fields (bio, era, state,
 *      gender, names, aka, ideologies, affiliation, status) — it never overwrites
 *      anything already set.
 *   3) Adds documented cases only when the record currently has none, so it won't
 *      duplicate a case created by an earlier command.
 *
 * Facts/dates are from the UPI chronology (1985-03-27), the Christian Science
 * Monitor (1985-03-28), UPI (1987-01-30), and United States v. Merkt,
 * 764 F.2d 266 (5th Cir. 1985) / United States v. Elder, 601 F. Supp. 1574.
 * Prison-term dates are intentionally left off the cases (only the sentence text
 * records the time served) because exact release dates aren't documented.
 */
final class FixSanctuaryMerktElder extends Command
{
    protected $signature = 'prisoners:fix-sanctuary-merkt-elder {--dry-run : Preview without saving}';

    protected $description = 'Enrich and de-duplicate the Sanctuary Movement records for Stacey Merkt and Jack Elder';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Every write below is guarded by $dry, so a dry run is read-only and
        // needs no transaction; the real run is wrapped for atomicity.
        $run = function () use ($dry) {
            $merkt = $this->resolveMerkt($dry);
            if ($merkt) {
                $this->enrich($merkt, $this->merktFields(), $this->merktCases(), $dry);
            } else {
                $this->warn('No Merkt record found.');
            }

            $elder = $this->resolve(['jack-elder'], ['Jack Elder']);
            if ($elder) {
                $this->enrich($elder, $this->elderFields(), $this->elderCases(), $dry);
            } else {
                $this->warn('No Jack Elder record found.');
            }
        };

        $dry ? $run() : DB::transaction($run);

        $this->info("\nDone".($dry ? ' (dry run)' : '').'.');

        return self::SUCCESS;
    }

    /** Merge the Merkt duplicate (if present) and return the surviving record. */
    private function resolveMerkt(bool $dry): ?Prisoner
    {
        $primary = Prisoner::withUnderReview()->where('slug', 'stacey-merkt')->first();
        $dup = Prisoner::withUnderReview()->where('slug', 'stacey-lynn-merkt')->first();

        // Fall back to name lookups if the slugs differ.
        $primary ??= Prisoner::withUnderReview()->where('name', 'Stacey Merkt')->first();
        if (! $primary && $dup) {
            return $dup; // only the "lynn" record exists; enrich it in place
        }
        if ($primary && ! $dup) {
            return $primary;
        }
        if (! $primary && ! $dup) {
            return null;
        }

        // Both exist → merge $dup into $primary.
        $movedCases = 0;
        foreach ($dup->cases as $case) {
            if ($dry) {
                $movedCases++;

                continue;
            }
            $case->prisoner_id = $primary->id;
            $case->save();
            $movedCases++;
        }

        if (empty($primary->aka) && ! $dry) {
            $aka = $dup->name !== $primary->name ? $dup->name : 'Stacey Lynn Merkt';
            $primary->forceFill(['aka' => $aka])->save();
        }

        if ($dry) {
            $this->line("  [merge] would move {$movedCases} case(s) from '{$dup->name}' (/{$dup->slug}) then delete it");
        } else {
            $dup->delete();
            $this->info("  [merge] merged '{$dup->name}' into '{$primary->name}', moved {$movedCases} case(s), deleted duplicate");
        }

        return $primary->fresh();
    }

    private function resolve(array $slugs, array $names): ?Prisoner
    {
        foreach ($slugs as $slug) {
            if ($p = Prisoner::withUnderReview()->where('slug', $slug)->first()) {
                return $p;
            }
        }
        foreach ($names as $name) {
            if ($p = Prisoner::withUnderReview()->where('name', $name)->first()) {
                return $p;
            }
        }

        return null;
    }

    /**
     * @param  array<string,mixed>  $fields
     * @param  array<int,array<string,mixed>>  $cases
     */
    private function enrich(Prisoner $p, array $fields, array $cases, bool $dry): void
    {
        $changes = [];
        foreach ($fields as $key => $value) {
            $current = $p->{$key};
            $isBlank = is_array($current) ? empty($current) : ($current === null || $current === '');
            // Booleans: only fill when null (don't flip an admin's explicit choice).
            if (in_array($key, ['in_custody', 'released'], true)) {
                $isBlank = $current === null;
            }
            if ($isBlank) {
                $changes[$key] = $value;
            }
        }

        if ($changes) {
            $this->line('  ['.$p->name.'] '.($dry ? 'would fill' : 'filled').': '.implode(', ', array_keys($changes)));
            if (! $dry) {
                $p->forceFill($changes)->save();
            }
        } else {
            $this->line("  [{$p->name}] no blank fields to fill");
        }

        // The Airtable import left an empty placeholder case on each record;
        // drop any case with no substantive content so it doesn't block (or
        // clutter alongside) the documented cases below.
        $emptyCount = 0;
        $substantive = 0;
        foreach ($p->cases as $case) {
            if ($this->isEmptyCase($case)) {
                $emptyCount++;
                if (! $dry) {
                    $case->delete();
                }
            } else {
                $substantive++;
            }
        }
        if ($emptyCount > 0) {
            $this->line("  [{$p->name}] ".($dry ? 'would remove' : 'removed')." {$emptyCount} empty placeholder case(s)");
        }

        if ($substantive > 0) {
            $this->line("  [{$p->name}] keeps {$substantive} existing case(s); not adding documented cases");

            return;
        }

        foreach ($cases as $caseData) {
            if ($dry) {
                $this->line("  [{$p->name}] would add case: ".Str::limit($caseData['charges'], 60));

                continue;
            }
            PrisonerCase::create(['prisoner_id' => $p->id] + $caseData);
        }
        if (! $dry && $cases) {
            $this->info("  [{$p->name}] added ".count($cases).' case(s)');
        }
    }

    /** A case is "empty" when none of its meaningful fields carry any content. */
    private function isEmptyCase(PrisonerCase $case): bool
    {
        $fields = [
            'charges', 'convicted', 'sentence', 'plead', 'indicted',
            'arrest_date', 'sentenced_date', 'incarceration_date', 'release_date',
            'death_in_custody_date', 'prosecutor', 'judge', 'institution_id',
        ];
        foreach ($fields as $f) {
            if (! empty($case->{$f})) {
                return false;
            }
        }

        return true;
    }

    /** @return array<string,mixed> */
    private function merktFields(): array
    {
        return [
            'first_name' => 'Stacey',
            'last_name' => 'Merkt',
            'aka' => 'Stacey Lynn Merkt',
            'gender' => 'Female',
            'state' => 'Texas',
            'era' => '1980s',
            'ideologies' => ['Sanctuary movement', 'Human rights'],
            'affiliation' => ['Casa Óscar Romero', 'Bijou House Religious Community'],
            'in_custody' => false,
            'released' => true,
            'description' => 'Stacey Lynn Merkt was a lay religious volunteer — affiliated with the Bijou House '
                .'community in Colorado Springs and with Casa Óscar Romero, the Catholic refugee shelter in San Benito, '
                .'Texas — and the first member of the 1980s Sanctuary Movement convicted of an immigration crime for '
                .'helping Central Americans fleeing the U.S.-backed wars in El Salvador and Guatemala. On February 17, '
                .'1984, near Guerra, Texas, the Border Patrol stopped and arrested her, a Catholic nun, and a newspaper '
                .'reporter while they were driving an undocumented Salvadoran family; she was convicted of felony alien '
                .'transportation on May 4, 1984, and on June 27, 1984 U.S. District Judge Filemón Vela gave her a 90-day '
                .'suspended sentence and two years\' probation. Indicted again that December with shelter director Jack '
                .'Elder, she was convicted on February 21, 1985 of conspiring to transport Salvadoran refugees and '
                .'sentenced to 179 days in prison. Pregnant during the prosecution and backed by churches nationwide, '
                .'Merkt became a national symbol of the movement; after her appeals failed (United States v. Merkt, '
                .'764 F.2d 266) she entered prison in late January 1987.',
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function merktCases(): array
    {
        return [
            [
                'charges' => 'Felony transportation of undocumented immigrants — arrested February 17, 1984 near Guerra, '
                    .'Texas while driving an undocumented Salvadoran family, together with a Catholic nun and a newspaper '
                    .'reporter, in a car owned by the Diocese of Brownsville.',
                'arrest_date' => '1984-02-17',
                'convicted' => 'Yes — convicted May 4, 1984; the first Sanctuary Movement worker convicted of an '
                    .'immigration offense.',
                'sentenced_date' => '1984-06-27',
                'sentence' => '90 days in jail, suspended, plus two years\' probation.',
                'judge' => 'Filemón Vela',
            ],
            [
                'charges' => 'Conspiracy to transport undocumented Salvadoran refugees (two adults and three children) '
                    .'from Brownsville toward a bus station in November 1984; indicted December 12, 1984 with Jack Elder '
                    .'and tried in Houston on a change of venue.',
                'convicted' => 'Yes — convicted February 21, 1985 of conspiracy (acquitted on the illegal-transportation '
                    .'count).',
                'sentence' => '179 days in prison; her appeals failed (United States v. Merkt, 764 F.2d 266) and she '
                    .'entered prison in late January 1987.',
                'judge' => 'Filemón Vela',
            ],
        ];
    }

    /** @return array<string,mixed> */
    private function elderFields(): array
    {
        return [
            'first_name' => 'Jack',
            'last_name' => 'Elder',
            'gender' => 'Male',
            'state' => 'Texas',
            'era' => '1980s',
            'ideologies' => ['Sanctuary movement', 'Human rights'],
            'affiliation' => ['Casa Óscar Romero'],
            'in_custody' => false,
            'released' => true,
            'description' => 'Jack Elder was the director of Casa Óscar Romero, the Catholic refugee shelter in San '
                .'Benito, Texas sponsored by the Diocese of Brownsville, and one of the most prominent Sanctuary '
                .'Movement workers prosecuted for aiding Central American refugees fleeing the U.S.-backed wars in El '
                .'Salvador and Guatemala. On March 12, 1984 he drove three undocumented Salvadoran men to a bus station '
                .'about five miles from the shelter; federal agents arrested him on April 13, 1984 on three felony '
                .'charges carrying up to 15 years, but a Corpus Christi jury acquitted him on January 24, 1985, finding '
                .'the government had not proved his help "furthered" the men\'s entry. Indicted again with Stacey Merkt, '
                .'he was convicted on February 21, 1985 of conspiracy and illegal transportation (United States v. '
                .'Elder, 601 F. Supp. 1574). Judge Filemón Vela first imposed one-year concurrent terms on six counts; '
                .'Elder rejected a probation offer that would have restricted his movement work, and on reconsideration '
                .'Vela ordered him to serve 150 days in a halfway house in San Antonio. Elder maintained that giving '
                .'refuge to those fleeing death squads was both a religious duty and protected by U.S. refugee law.',
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function elderCases(): array
    {
        return [
            [
                'charges' => 'Three felony counts of transporting undocumented immigrants — for driving three '
                    .'Salvadoran men, on March 12, 1984, from the Casa Óscar Romero shelter he directed to a nearby bus '
                    .'station. Arrested April 13, 1984.',
                'arrest_date' => '1984-04-13',
                'convicted' => 'No — acquitted by a Corpus Christi jury on January 24, 1985 (the government failed to '
                    .'prove the transportation "furthered" the men\'s unlawful entry).',
                'judge' => 'Filemón Vela (recused; case tried in Corpus Christi)',
            ],
            [
                'charges' => 'Conspiracy and illegal transportation of undocumented Salvadoran refugees — indicted '
                    .'December 12, 1984 with Stacey Merkt and tried in Houston (United States v. Elder, 601 F. Supp. '
                    .'1574).',
                'convicted' => 'Yes — convicted February 21, 1985 on conspiracy and transportation counts.',
                'sentence' => 'Initially one-year concurrent terms on six counts; after he rejected a probation offer, '
                    .'Judge Vela on reconsideration ordered 150 days in a halfway house in San Antonio.',
                'judge' => 'Filemón Vela',
            ],
        ];
    }
}
