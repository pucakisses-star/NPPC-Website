<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds Wayne Hsiung's 2018 North Carolina "baby goat" open-rescue case as a
 * proper case record. He is already in the database with two structured cases
 * (the Sonoma County ducks/chickens case he served 90 days for, and the pending
 * Wisconsin Ridglan beagle case); the North Carolina goat conviction is only
 * mentioned in prose in his description. This gives it a real case entry.
 *
 * Facts: in February 2018 Hsiung and DxE removed a sick baby goat (pneumonia)
 * from Sospiro Ranch in Pisgah Forest (Brevard), Transylvania County, NC, and
 * live-streamed it. Arrested June 2018; convicted by a jury on December 6, 2021
 * of felony larceny after breaking and entering and felony breaking and
 * entering; given a suspended sentence — no jail, 24 months of supervised
 * probation and restitution.
 *
 * Enriches an existing record; idempotent — skips if a goat/Sospiro case is
 * already present.
 */
final class AddHsiungGoatCase extends Command
{
    protected $signature = 'prisoners:add-hsiung-goat-case';

    protected $description = "Add Wayne Hsiung's 2018 NC baby-goat rescue case to his existing record";

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'wayne-hsiung')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Wayne Hsiung')->first();

        if (! $prisoner) {
            $this->warn('No Wayne Hsiung record found — nothing to add.');

            return self::SUCCESS;
        }

        foreach ($prisoner->cases as $existing) {
            $charges = is_array($existing->charges) ? implode(' ', $existing->charges) : (string) $existing->charges;
            if (Str::contains(Str::lower($charges), ['goat', 'sospiro', 'transylvania'])) {
                $this->info("{$prisoner->name} already has the North Carolina goat case — nothing to add.");

                return self::SUCCESS;
            }
        }

        $prisoner->cases()->create([
            'charges' => 'Felony breaking and entering, felony larceny after breaking and entering, and misdemeanor trespass — for the February 2018 "open rescue" of a sick baby goat (ill with pneumonia) from Sospiro Ranch in Pisgah Forest (Brevard), Transylvania County, North Carolina, an action he live-streamed on Facebook for Direct Action Everywhere. Arrested June 2018.',
            'arrest_date' => '2018-06-08',
            'convicted' => 'Yes — Transylvania County, North Carolina jury, December 6, 2021 (felony larceny after breaking and entering, and felony breaking and entering).',
            'sentenced_date' => '2021-12-06',
            // Suspended sentence — no jail — so no incarceration/release dates and
            // imprisoned_for_days stays null.
            'sentence' => "Suspended sentence — no jail time; 24 months of supervised probation and restitution to the goat's owners.",
        ]);

        $this->info("Added the North Carolina goat-rescue case to {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
