<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Crystal Aguilar, the Kern County immigrant-rights activist arrested on
 * February 6, 2025 at Hart Memorial Park near Bakersfield, California, after she
 * cut the flagpole chain, took down the U.S. flag, and raised a Mexican flag in
 * its place ("This is Mexican land") during the period of the ICE-raid protests.
 *
 * She was only briefly jailed: booked into the Kern County (Lerdo) jail, she
 * posted the ~$20,000 bail within days, pleaded not guilty, and was out on bond
 * awaiting trial (preliminary hearing set for March 13, 2025). Recorded with a
 * same-week release so no meaningful jail term is implied.
 *
 * Uses prisoner:add then backfills flags, case dates, and an approximate birth
 * year. Idempotent.
 */
final class AddCrystalAguilar extends Command
{
    protected $signature = 'prisoners:add-crystal-aguilar';

    protected $description = 'Add Crystal Aguilar (Bakersfield Hart Park flag protest)';

    public function handle(): int
    {
        $payload = [
            'name' => 'Crystal Aguilar',
            'first_name' => 'Crystal',
            'last_name' => 'Aguilar',
            'description' => "Crystal Aguilar, a Kern County immigrant-rights activist, was arrested on February 6, 2025 at Hart Memorial Park near Bakersfield, California, after she cut the chain on the park's flagpole, took down the U.S. flag, and raised a Mexican flag in its place, declaring \"This is Mexican land.\" She had recently protested the ICE raids in her community and has said her Mexican parents were deported when she was a child. Booked into the Kern County (Lerdo) jail, she posted the roughly \$20,000 bail within days and pleaded not guilty to a felony resisting-arrest charge and misdemeanor counts of resisting arrest, vandalism, and battery on a peace officer. After briefly failing to appear for a hearing she was re-arrested and again released on bond, with a preliminary hearing set for March 13, 2025.",
            'state' => 'California',
            'gender' => 'Female',
            'ideologies' => ['Immigrant Rights'],
            'era' => '2020s',
            'in_custody' => false,
            'released' => true,
            'awaiting_trial' => true,
            'cases' => [[
                'charges' => 'One felony count of resisting arrest, plus misdemeanor counts of resisting arrest, vandalism, and battery on a peace officer — for cutting down the U.S. flag at Hart Memorial Park near Bakersfield on February 6, 2025 and raising a Mexican flag in its place during the ICE-raid protests. (Initially booked on suspicion of trespassing, threatening a peace officer, resisting arrest, vandalism, and marijuana possession.)',
                'arrest_date' => '2025-02-06',
                'incarceration_date' => '2025-02-06',
                'release_date' => '2025-02-06',
                'convicted' => 'No — pleaded not guilty and was released on bond; preliminary hearing set for March 13, 2025.',
            ]],
        ];

        $this->call('prisoner:add', ['json' => json_encode($payload)]);

        $prisoner = Prisoner::withoutGlobalScopes()->where('name', 'Crystal Aguilar')->first();
        if (! $prisoner) {
            $this->warn('Crystal Aguilar record not found after prisoner:add.');

            return self::SUCCESS;
        }

        $prisoner->in_custody = false;
        $prisoner->released = true;
        $prisoner->awaiting_trial = true;
        if (! $prisoner->birthdate) {
            $prisoner->setPartialDate('birthdate', 2001); // age 24 in Feb 2025 (approx)
        }
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if ($case) {
            $case->arrest_date = '2025-02-06';
            $case->incarceration_date = '2025-02-06';
            $case->release_date = '2025-02-06'; // posted bail within days — brief booking, no real jail term
            $case->save();
        }

        $this->info("Added Crystal Aguilar. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
