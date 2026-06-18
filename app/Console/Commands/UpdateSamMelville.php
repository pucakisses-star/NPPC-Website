<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Marks Sam Melville as having died in custody during the September 13, 1971
 * retaking of Attica Correctional Facility, where he was a leader of the
 * uprising. Without this his profile showed him as still incarcerated
 * (in_custody = true and an open-ended incarceration period).
 *
 * Sets death_date and clears the in-custody / released flags on the prisoner,
 * and records death_in_custody_date on his case (which also caps the
 * days-incarcerated calculation at his death). Idempotent.
 */
final class UpdateSamMelville extends Command
{
    protected $signature = 'prisoners:update-sam-melville';

    protected $description = 'Mark Sam Melville as died in custody at Attica (Sept 13, 1971)';

    private const DEATH = '1971-09-13';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where(function ($q) {
                $q->where('slug', 'sam-melville')->orWhere('name', 'Sam Melville');
            })
            ->first();

        if (! $prisoner) {
            $this->error('Sam Melville not found (slug "sam-melville" / name "Sam Melville").');

            return self::FAILURE;
        }

        $prisoner->death_date = self::DEATH;
        $prisoner->in_custody = false;
        $prisoner->released = false; // died in custody — never released
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if ($case) {
            $case->death_in_custody_date = self::DEATH;
            $case->save();
            $this->info('Set death_in_custody_date on case.');
        } else {
            $this->warn('No case found — set death_date/flags only.');
        }

        $this->info('Updated Sam Melville: died in custody '.self::DEATH);

        return self::SUCCESS;
    }
}
