<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Muhammad Pahlawan, a Pakistani national convicted in the Eastern
 * District of Virginia of transporting Iranian-made advanced conventional
 * weaponry bound for the Houthis, captured during the Jan. 11, 2024 dhow
 * interdiction in the Arabian Sea, and sentenced Oct. 16, 2025 to 40 years.
 * Convicted and sentenced, so in_custody is true and released/awaiting_trial
 * are false. Dedupes by name — safe to re-run.
 */
class AddPahlawanPrisoner extends Command {
    protected $signature = 'prisoners:add-pahlawan';
    protected $description = 'Add Muhammad Pahlawan (convicted, 40-year sentence, E.D. Va.)';

    public function handle(): int {
        $name = 'Muhammad Pahlawan';
        if (Prisoner::withoutGlobalScopes()->where('name', $name)->exists()) {
            $this->warn("{$name} already exists — skipping.");

            return self::SUCCESS;
        }

        $edva = Institution::firstOrCreate(
            ['name' => 'U.S. District Court, Eastern District of Virginia (Norfolk)'],
            ['city' => 'Norfolk', 'state' => 'Virginia']
        );

        DB::transaction(function () use ($name, $edva) {
            $prisoner = Prisoner::create([
                'name'           => $name,
                'first_name'     => 'Muhammad',
                'last_name'      => 'Pahlawan',
                'gender'         => 'Male',
                'era'            => '2020s',
                'in_custody'     => true,
                'released'       => false,
                'awaiting_trial' => false,
                'description'    => "Muhammad Pahlawan is a Pakistani national convicted in the U.S. District Court for the Eastern District of Virginia of transporting Iranian-made advanced conventional weaponry bound for Yemen's Houthi movement. On January 11, 2024, U.S. Central Command naval forces operating from the USS Lewis B. Puller — including Navy SEALs and a U.S. Coast Guard Maritime Security Response Team — boarded the unflagged dhow Pahlawan captained in the Arabian Sea off the coast of Somalia and seized ballistic-missile components, anti-ship cruise-missile components, and a warhead. Two Navy SEALs, Christopher Chambers and Nathan Gage Ingram, were lost at sea during the interdiction.\n\nOn June 5, 2025, a federal jury convicted Pahlawan of conspiring to provide and providing material support to terrorists, providing material support to Iran's and the Islamic Revolutionary Guard Corps' weapons-of-mass-destruction programs, conspiring to and transporting explosive devices to the Houthis knowing they would be used to cause harm, and threatening his crew. On October 16, 2025, U.S. District Judge David J. Novak sentenced him to 40 years in prison.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $edva->id,
                'charges'            => 'Conspiring to provide and providing material support to terrorists; providing material support to Iran\'s and the IRGC\'s weapons-of-mass-destruction programs; conspiring to and transporting explosive devices to the Houthis knowing they would be used to cause harm; threatening his crew',
                'arrest_date'        => '2024-01-11',
                'incarceration_date' => '2024-01-11',
                'convicted'          => 'Yes — federal jury, Eastern District of Virginia, June 5, 2025',
                'sentenced_date'     => '2025-10-16',
                'judge'              => 'David J. Novak',
                'sentence'           => '40 years in federal prison',
            ]);

            $this->info("Added {$prisoner->name} (slug: {$prisoner->slug}).");
        });

        return self::SUCCESS;
    }
}
