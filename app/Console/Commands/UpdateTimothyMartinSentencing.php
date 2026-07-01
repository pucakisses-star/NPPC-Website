<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Fills in Timothy Martin's sentencing details from the 31 October 2025 Art
 * Newspaper report. He is already in the database with an accurate case; this
 * only adds the sentencing date and the full sentence terms (restitution,
 * community service, probation) that were missing. Idempotent.
 */
final class UpdateTimothyMartinSentencing extends Command
{
    protected $signature = 'prisoners:update-timothy-martin-sentencing';

    protected $description = "Add Timothy Martin's sentencing date and full sentence terms";

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'timothy-martin')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Timothy Martin')->first();

        if (! $prisoner) {
            $this->warn('No Timothy Martin record found — nothing to update.');

            return self::SUCCESS;
        }

        $prisoner->in_custody = true;
        $prisoner->released = false;
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if (! $case) {
            $this->warn('Timothy Martin has no case to update.');

            return self::SUCCESS;
        }

        $case->sentenced_date = '2025-10-31';
        $case->sentence = '18 months in federal prison (with credit for time served), $4,250 restitution, 150 hours of community service, and two years of supervised release; prosecutors had sought five years.';
        $case->save();

        $this->info("Updated Timothy Martin's sentencing (sentenced 2025-10-31). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
