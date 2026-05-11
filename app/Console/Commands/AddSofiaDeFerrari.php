<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds Sofia "Candle" DeFerrari from prisonersolidarity.com — the only U.S.
 * prisoner on PrisonerSolidarity.com's 143-entry directory not already in
 * NPPC's database (verified by name + alternate-spelling cross-reference).
 */
final class AddSofiaDeFerrari extends Command {
    protected $signature = 'archive:add-sofia-deferrari';
    protected $description = 'Add Sofia "Candle" DeFerrari (Oregon Egoist Anarchist) — sourced from prisonersolidarity.com';

    public function handle(): int {
        $name = 'Sofia DeFerrari';

        if (Prisoner::where('name', $name)->exists()) {
            $this->warn("Prisoner '{$name}' already exists; skipping.");

            return self::SUCCESS;
        }

        $data = [
            'name' => $name,
            'first_name' => 'Sofia',
            'last_name' => 'DeFerrari',
            'aka' => 'Sofia "Candle" de Ferrari',
            'description' => 'Sofia "Candle" DeFerrari is a non-binary trans woman and Egoist Anarchist in the custody of the Oregon Department of Corrections, held at Coffee Creek Correctional Facility in Wilsonville. Raised in Broward County, Florida and half-Venezuelan, she has been on hormone replacement therapy since March 2018 and uses she/they pronouns. She is the subject of an active support campaign at freesofiadeferrari.noblogs.org.'."\n\n".'DeFerrari took public responsibility for orchestrating a property-destruction action during a Portland, Oregon riot — smashing windows of a shopping district, a church, and destroying an ATM — and was arrested at the scene after being led into a honeypot by an FBI informant in her affinity group. She accepted a plea agreement on the riot charges and was convicted of Riot and Criminal Mischief in the First Degree, with roughly $11,000 in restitution. Most of her sentence length stems from a separate Beaverton, Oregon armed robbery conviction (a 7-Eleven robbery for approximately $860) for which she accepted the 90-month mandatory minimum under Oregon\'s Measure 11 / ORS 137.700. All sentences run concurrently with a hard-release date of December 22, 2028. After her arrest she publicly disclosed the legal name of the FBI informant in her affinity group as a deliberate "no-snitching by example" demonstration.',
            'gender' => 'Female',
            'state' => 'Oregon',
            'ideologies' => ['Anarchism', 'Egoist Anarchism', 'Anti-Authoritarian'],
            'affiliation' => ['Anarchist Movement'],
            'inmate_number' => 'SID #23976151',
            'website' => 'https://freesofiadeferrari.noblogs.org/',
            'in_custody' => true,
            'released' => false,
            'cases' => [[
                'institution_name' => 'Coffee Creek Correctional Facility',
                'institution_city' => 'Wilsonville',
                'institution_state' => 'Oregon',
                'charges' => 'Riot; Criminal Mischief in the First Degree (Portland riot); First-degree armed robbery (Beaverton, OR 7-Eleven)',
                'convicted' => 'Yes — pleaded guilty',
                'sentence' => '90 months mandatory minimum under Oregon ORS 137.700 (Measure 11); $11,000 in restitution; hard-release date December 22, 2028',
            ]],
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $exit = Artisan::call('prisoner:add', ['json' => $json]);
        $this->line(Artisan::output());

        return $exit;
    }
}
