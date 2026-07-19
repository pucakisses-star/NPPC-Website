<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Eric Hafner — New Jersey perennial political candidate serving a 20-year
 * federal sentence at FCI Otisville for threatening officials and false bomb
 * threats, who drew national attention by qualifying for Alaska's 2024 U.S.
 * House general-election ballot while incarcerated — and attaches his committed
 * profile photo. Idempotent: prisoner:add refuses duplicates, and the photo is
 * (re)attached from the committed source on every run.
 */
final class AddEricHafner extends Command
{
    protected $signature = 'prisoner:add-eric-hafner';

    protected $description = 'Add Eric Hafner (incarcerated congressional candidate, FCI Otisville) with photo';

    private const PHOTO_SOURCE = 'data/photos/legacy/eric-hafner.jpg';

    private const PHOTO = 'prisoners/eric-hafner.jpg';

    public function handle(): int
    {
        $description = 'Eric Hafner (born 1991, New Jersey) is a perennial political candidate serving a 20-year federal sentence at the Federal Correctional Institution in Otisville, New York, with a projected release in October 2036. After years living abroad and in hiding, he was arrested in September 2019 at the airport in Saipan, Northern Mariana Islands, and charged in an October 2019 federal indictment with threatening to kill or injure New Jersey elected officials, police officers, attorneys, and judges and their families, conveying false bomb threats, and an attempted extortion. He pleaded guilty in May 2022 and was sentenced that December. Hafner drew national attention in 2024 when, running his campaign from federal prison, he qualified for the November general-election ballot in Alaska\'s at-large U.S. House race as a Democrat — advancing from a sixth-place primary finish after two higher-placed Republicans withdrew — despite having no ties to Alaska; the Alaska Democratic Party disavowed him. He had previously run for Congress in Hawaii (2016) and Oregon (2018) and filed numerous federal lawsuits asserting candidacies in other states. NPPC lists him as an incarcerated U.S. congressional candidate.';

        $payload = [
            'name' => 'Eric Hafner',
            'first_name' => 'Eric',
            'last_name' => 'Hafner',
            'description' => $description,
            'state' => 'New York',
            'gender' => 'Male',
            'affiliation' => ['Perennial U.S. congressional candidate'],
            'era' => 'Contemporary',
            'in_custody' => true,
            'released' => false,
            'cases' => [
                [
                    'institution_name' => 'Federal Correctional Institution, Otisville',
                    'institution_city' => 'Otisville',
                    'institution_state' => 'New York',
                    'charges' => 'Threatening to kill or injure New Jersey elected officials, police officers, attorneys, and judges and their families; conveying false bomb threats; and an attempted extortion — charged in an October 2019 federal indictment. Arrested September 2019 at the airport in Saipan, Northern Mariana Islands.',
                    'convicted' => 'Pleaded guilty, May 2022',
                    'sentence' => '20 years in federal prison (sentenced December 2022); projected release October 12, 2036.',
                ],
            ],
        ];

        $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
        if ($exit !== self::SUCCESS) {
            $this->info('Eric Hafner already exists or could not be added — continuing to photo.');
        }

        // Attach the committed profile photo (copy to public disk, set photo).
        $source = public_path(self::PHOTO_SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::PHOTO, file_get_contents($source));

            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'eric-hafner')->first()
                ?? Prisoner::withoutGlobalScopes()->where('name', 'Eric Hafner')->first();

            if ($prisoner) {
                $prisoner->photo = self::PHOTO;
                $prisoner->save();
                $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");
            }
        } else {
            $this->warn('Photo source not found: public/'.self::PHOTO_SOURCE);
        }

        return self::SUCCESS;
    }
}
