<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects Robert Weiss's case record. Weiss was a U.S. Army soldier charged
 * with desertion / missing movement (UCMJ) and held at the U.S. military
 * confinement facility at Coleman Barracks in Mannheim, Germany. He was arrested
 * on February 11, 2008 and released on November 9, 2008. The record previously
 * carried placeholder dates (May/Dec 2008) and a generic "Federal Bureau of
 * Prisons" location.
 *
 * incarceration_date is set to the arrest date: as a detained soldier he was in
 * custody from his arrest through his release, so the "time imprisoned" counter
 * reflects that full span (distinct from the 7-month adjudged sentence).
 *
 * Idempotent.
 */
final class FixRobertWeissCase extends Command
{
    protected $signature = 'prisoners:fix-robert-weiss-case';

    protected $description = "Correct Robert Weiss's arrest/release dates and confinement facility (Coleman Barracks, Mannheim)";

    private const ARREST = '2008-02-11';

    private const RELEASE = '2008-11-09';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'like', '%Robert Weiss%')->first();

        if (! $prisoner) {
            $this->warn('Robert Weiss not found, nothing to do.');

            return self::SUCCESS;
        }

        $case = $prisoner->cases->first(fn ($c) => str_contains(strtolower((string) $c->charges), 'desertion'))
            ?? $prisoner->cases->first();

        if (! $case) {
            $this->warn('Robert Weiss has no case to update.');

            return self::SUCCESS;
        }

        // The U.S. Army stockade in Germany — not the unrelated USP Coleman in Florida.
        $institution = Institution::firstOrCreate(
            ['name' => 'Coleman Barracks'],
            ['city' => 'Mannheim', 'state' => 'Germany'],
        );

        $before = sprintf(
            'arrest=%s incarceration=%s release=%s institution=%s',
            $case->arrest_date?->format('Y-m-d') ?? '(null)',
            $case->incarceration_date?->format('Y-m-d') ?? '(null)',
            $case->release_date?->format('Y-m-d') ?? '(null)',
            $case->institution?->name ?? '(none)',
        );

        $case->arrest_date = self::ARREST;
        $case->incarceration_date = self::ARREST;
        $case->release_date = self::RELEASE;
        $case->institution_id = $institution->id;
        $case->save();

        $case->refresh();

        $this->info('Robert Weiss case updated.');
        $this->line("  before: {$before}");
        $this->line(sprintf(
            '  after:  arrest=%s incarceration=%s release=%s institution=%s, %s (%d days)',
            $case->arrest_date?->format('Y-m-d'),
            $case->incarceration_date?->format('Y-m-d'),
            $case->release_date?->format('Y-m-d'),
            $case->institution?->name,
            $case->institution?->city,
            $case->imprisoned_for_days ?? 0,
        ));

        return self::SUCCESS;
    }
}
