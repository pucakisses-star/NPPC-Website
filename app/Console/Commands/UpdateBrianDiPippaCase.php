<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateBrianDiPippaCase extends Command
{
    protected $signature = 'prisoners:update-brian-dipippa';
    protected $description = 'Update Brian DiPippa\'s case to reflect the January 6, 2025 federal sentencing (5 years, Western District of Pennsylvania).';

    public function handle(): int
    {
        $prisoner = Prisoner::where('slug', 'brian-dipippa')->first();
        if (! $prisoner) {
            $this->error('Brian DiPippa not found in database.');
            return self::FAILURE;
        }

        $bopFederal = Institution::firstOrCreate(
            ['name' => 'Federal Bureau of Prisons (location varied)'],
            ['city' => null, 'state' => null]
        );

        DB::transaction(function () use ($prisoner, $bopFederal) {
            // Confirm in-custody status (he's still serving the 5-year sentence)
            $prisoner->in_custody = true;
            $prisoner->released   = false;
            $prisoner->awaiting_trial = false;
            $prisoner->save();

            // Replace/update the existing case (or create one if none exists) with
            // the now-final federal sentencing data.
            $case = $prisoner->cases()->orderBy('created_at')->first();

            $caseAttrs = [
                'institution_id'     => $bopFederal->id,
                'charges'            => 'Conspiracy to obstruct law enforcement during civil disorder (18 U.S.C. § 231(a)(3)); obstructing law enforcement during civil disorder — for igniting two homemade smoke devices and a large firework that injured several University of Pittsburgh police officers during the April 18, 2023 student protest of conservative commentator Michael Knowles\' campus debate on transgender rights',
                'arrest_date'        => '2023-05-04',
                'sentenced_date'     => '2025-01-06',
                'incarceration_date' => '2025-01-06',
                'release_date'       => null,
                'convicted'          => 'Yes — pleaded guilty in U.S. District Court for the Western District of Pennsylvania, August 5, 2024',
                'sentence'           => '5 years (60 months) in federal prison; $1,400 restitution to the University of Pittsburgh and approximately $47,000 in restitution to a UPP officer injured by the firework. (His wife and co-defendant Krystal DiPippa was separately sentenced January 6, 2025 to 3 years of federal probation on a single obstruction count.)',
                'judge'              => 'W. Scott Hardy (W.D. Pa.)',
            ];

            if ($case) {
                $case->update($caseAttrs);
                $this->info("Updated existing case for {$prisoner->name}.");
            } else {
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $caseAttrs));
                $this->info("Created new case for {$prisoner->name}.");
            }
        });

        $this->info("Status: in_custody=true, released=false (currently serving 5-year federal sentence; eligible for release approximately 2029).");

        return self::SUCCESS;
    }
}
