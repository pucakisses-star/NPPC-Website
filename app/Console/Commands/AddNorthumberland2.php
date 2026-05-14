<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds the "Northumberland 2" — Cara Mitrano and Celeste Legere,
 * the engaged Worcester, MA anarchist couple arrested October 19,
 * 2024 for allegedly releasing 683 mink from Richard H. Stahl &
 * Sons Inc. fur farm in Rockefeller Township, Northumberland
 * County, PA. The state's felony ecoterrorism and RICO/corrupt-
 * organizations charges were dismissed in pretrial rulings; trial
 * on remaining counts scheduled May 2026. Both spent ~3 weeks in
 * pretrial detention before being released on $15,000 bail.
 */
final class AddNorthumberland2 extends Command {
    protected $signature = 'archive:add-northumberland-2';
    protected $description = 'Add Cara Mitrano + Celeste Legere (Northumberland 2 mink-farm case)';

    public function handle(): int {
        $shared = [
            'state' => 'Pennsylvania',
            'race' => 'White',
            'ideologies' => ['Animal liberation', 'Anarchism'],
            'affiliation' => ['Northumberland 2', 'Animal Liberation Front (alleged)'],
            'era' => '2020s',
            'in_custody' => false,
            'awaiting_trial' => true,
            'released' => false,
        ];

        $entries = [
            array_merge($shared, [
                'name' => 'Cara Mitrano',
                'first_name' => 'Cara',
                'middle_name' => 'Ashley',
                'last_name' => 'Mitrano',
                'gender' => 'Female',
                'description' => "Worcester, Massachusetts anarchist arrested October 19, 2024 with her partner Celeste Legere for allegedly cutting fencing at Richard H. Stahl & Sons Inc. fur farm in Rockefeller Township, Northumberland County, Pennsylvania and releasing 683 mink. Pennsylvania State Police recovered crowbars, bolt cutters, lock-picking kits, and anarchist literature from the couple's vehicle. The Northumberland County DA initially filed sixteen counts including felony ecoterrorism under PA's animal-and-ecological-terrorism statute and RICO/corrupt-organizations; the ecoterrorism count was dismissed by Judge Paige Rosini in 2025 and the RICO charge was dropped by the prosecution. Mitrano spent twenty-one days in Northumberland County Prison before being released on \$15,000 bail; trial on remaining felony cruelty / theft / criminal mischief / conspiracy counts is set for May 2026. In March 2025 the ALF claimed a solidarity action releasing 2,000 mink elsewhere \"in solidarity with cara and celeste.\"",
                'cases' => [
                    [
                        'institution_name' => 'Northumberland County Prison',
                        'institution_city' => 'Sunbury',
                        'institution_state' => 'Pennsylvania',
                        'charges' => 'Felony aggravated cruelty to animals; cruelty to animals; criminal mischief; theft by unlawful taking; criminal trespass; conspiracy; depositing waste on highway; recklessly endangering another person; accidents involving damage to property. (Felony ecoterrorism dismissed; RICO/corrupt-organizations dropped; no federal AETA charges.)',
                        'arrest_date' => '2024-10-19',
                        'incarceration_date' => '2024-10-19',
                        'release_date' => '2024-11-09',
                        'prosecutor' => 'Northumberland County DA Mike O\'Donnell',
                        'judge' => 'Paige Rosini',
                        'imprisoned_for_days' => 21,
                    ],
                ],
            ]),
            array_merge($shared, [
                'name' => 'Celeste Legere',
                'first_name' => 'Celeste',
                'last_name' => 'Legere',
                'aka' => 'Christopher Jacob Legere',
                'gender' => 'Non-binary',
                'description' => "Worcester, Massachusetts anarchist arrested October 19, 2024 with her partner Cara Mitrano for the alleged release of 683 mink from Richard H. Stahl & Sons Inc. fur farm in Rockefeller Township, Northumberland County, Pennsylvania. Affiliated with the Firehouse and Collective A Go Go Worcester anarchist communes. The Northumberland County DA's felony ecoterrorism and RICO charges were dismissed pretrial; trial on remaining felony cruelty, theft, criminal mischief, conspiracy, burglary, and agricultural-vandalism counts is set for May 2026. Legere spent twenty-one days in Northumberland County Prison before being released on \$15,000 bail. In March 2025 the ALF released 2,000 mink elsewhere in the couple's name.",
                'cases' => [
                    [
                        'institution_name' => 'Northumberland County Prison',
                        'institution_city' => 'Sunbury',
                        'institution_state' => 'Pennsylvania',
                        'charges' => 'Felony aggravated cruelty to animals; cruelty to animals; criminal mischief; theft by unlawful taking; criminal trespass; conspiracy; burglary; agricultural vandalism. (Felony ecoterrorism dismissed; RICO/corrupt-organizations dropped; no federal AETA charges.)',
                        'arrest_date' => '2024-10-19',
                        'incarceration_date' => '2024-10-19',
                        'release_date' => '2024-11-09',
                        'prosecutor' => 'Northumberland County DA Mike O\'Donnell',
                        'judge' => 'Paige Rosini',
                        'imprisoned_for_days' => 21,
                    ],
                ],
            ]),
        ];

        $added = 0;
        $failed = 0;
        foreach ($entries as $entry) {
            $this->line("\n— {$entry['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($entry)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $failed++;
            }
        }

        $this->info("\nAdded: {$added}    Failed: {$failed}");

        return self::SUCCESS;
    }
}
