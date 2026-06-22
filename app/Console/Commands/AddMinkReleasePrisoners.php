<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Northumberland 2" — animal-liberation activists Cara Mitrano and
 * Celeste Legere of Worcester, Massachusetts, charged over the October 18–19,
 * 2024 release of more than 600 mink from the Stahl family fur farm (Richard H.
 * Stahl & Sons) in Rockefeller Township, Northumberland County, Pennsylvania.
 * Arrested October 19, 2024 and held at the Northumberland County Prison, they
 * were released on bond that November. The felony corrupt-organizations (RICO)
 * charge was dropped on July 21, 2025 and the felony "ecoterrorism" count was
 * also dismissed, but a stack of lesser counts remains; trial is set for May
 * 2026, and supporters run a "Northumberland 2" defense campaign.
 *
 * They are awaiting trial (out on bond). Idempotent — skips anyone already in
 * the database by name.
 */
final class AddMinkReleasePrisoners extends Command
{
    protected $signature = 'prisoners:add-mink-release';

    protected $description = 'Add the Northumberland 2 (Cara Mitrano and Celeste Legere; 2024 mink release)';

    public function handle(): int
    {
        $institution = Institution::firstOrCreate(
            ['name' => 'Northumberland County Prison'],
            ['city' => 'Sunbury', 'state' => 'Pennsylvania']
        );

        $sharedCase = [
            'charges' => 'More than a dozen counts over the October 18–19, 2024 release of over 600 mink from the Stahl family fur farm in Rockefeller Township, Northumberland County, Pennsylvania — including burglary, theft, agricultural vandalism, criminal mischief, loitering and prowling at night, agricultural trespass, and cruelty to animals (with an added aggravated-animal-cruelty count), plus conspiracy.',
            'convicted' => 'No — awaiting trial (set for May 2026). The felony corrupt-organizations (RICO) charge was dropped on July 21, 2025 and the felony "ecoterrorism" count was also dismissed.',
            'arrest_date' => '2024-10-19',
            'sentence' => 'Not yet tried. Held at the Northumberland County Prison after the October 19, 2024 arrest, then released on bond in November 2024; trial set for May 2026.',
            'institution_id' => $institution->id,
        ];

        $people = [
            [
                'name' => 'Cara Mitrano',
                'first_name' => 'Cara',
                'last_name' => 'Mitrano',
                'gender' => 'Female',
                'state' => 'Massachusetts',
                'era' => '2020s',
                'ideologies' => ['Animal liberation', 'Anarchism'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'description' => 'Cara Mitrano (born c. 1996), an animal-liberation activist from Worcester, Massachusetts, is one of the "Northumberland 2." She and Celeste Legere were arrested on October 19, 2024 over the overnight release of more than 600 mink from the Stahl family fur farm in Rockefeller Township, Northumberland County, Pennsylvania. Held at the Northumberland County Prison, she was released on bond that November. The felony corrupt-organizations (RICO) charge against the pair was dropped in July 2025 and the felony "ecoterrorism" count was also dismissed, but she still faces numerous lesser counts — burglary, theft, agricultural vandalism, criminal mischief, and cruelty to animals among them — with trial set for May 2026.',
            ],
            [
                'name' => 'Celeste Legere',
                'first_name' => 'Celeste',
                'last_name' => 'Legere',
                'aka' => 'Christopher Legere',
                'gender' => 'Female',
                'state' => 'Massachusetts',
                'era' => '2020s',
                'ideologies' => ['Animal liberation', 'Anarchism'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'description' => 'Celeste Legere (born c. 1994), an animal-liberation activist from Worcester, Massachusetts, is one of the "Northumberland 2." Arrested with Cara Mitrano on October 19, 2024 over the overnight release of more than 600 mink from the Stahl family fur farm in Rockefeller Township, Northumberland County, Pennsylvania, Legere was held at the Northumberland County Prison and released on bond that November. The felony corrupt-organizations (RICO) charge was dropped in July 2025 and the felony "ecoterrorism" count was also dismissed; a stack of lesser counts — burglary, theft, agricultural vandalism, criminal mischief, and cruelty to animals — remains, with trial set for May 2026. A "Northumberland 2" campaign supports the pair.',
            ],
        ];

        $added = 0;
        $skipped = 0;
        foreach ($people as $fields) {
            $name = $fields['name'];
            if (Prisoner::withUnderReview()->where('name', $name)->exists()) {
                $this->line("Already present, skipping: {$name}");
                $skipped++;

                continue;
            }
            DB::transaction(function () use ($fields, $sharedCase) {
                $prisoner = Prisoner::create($fields);
                $case = $sharedCase;
                $case['prisoner_id'] = $prisoner->id;
                PrisonerCase::create($case);
            });
            $this->info("Added: {$name}");
            $added++;
        }

        $this->info("\nDone. added={$added} skipped={$skipped}");

        return self::SUCCESS;
    }
}
