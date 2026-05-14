<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds Yaoning "Mike" Sun — Chino Hills, CA resident convicted in
 * the federal § 951 prosecution for acting as a covert agent of
 * the People's Republic of China. Pleaded guilty October 2025;
 * sentenced February 10, 2026 to 48 months federal prison.
 */
final class AddYaoningMikeSun extends Command {
    protected $signature = 'archive:add-yaoning-mike-sun';
    protected $description = 'Add Yaoning "Mike" Sun (PRC § 951 agent case, 48 months federal)';

    public function handle(): int {
        $entry = [
            'name' => 'Yaoning Sun',
            'first_name' => 'Yaoning',
            'last_name' => 'Sun',
            'aka' => 'Mike Sun',
            'description' => "Chino Hills, California political operative and operator of the Chinese-language outlet U.S. News Center, prosecuted federally for acting as a covert agent of the People's Republic of China. From at least 2022 through January 2024, Sun took taskings from PRC government officials to push pro-Beijing material through U.S. News Center, surveil groups Beijing viewed as adversarial, shadow Taiwanese President Tsai Ing-wen during her April 2023 Southern California transit, and organize support for the 2022 Arcadia city council election of Eileen Wang. Described in court papers as the right-hand man of higher-level PRC-linked figure John Chen. Pleaded guilty in October 2025 to one count of acting as an unregistered foreign agent (18 U.S.C. § 951). Sentenced February 10, 2026 by U.S. District Judge R. Gary Klausner (C.D. Cal.) to 48 months in federal prison.",
            'state' => 'California',
            'race' => 'Asian',
            'gender' => 'Male',
            'affiliation' => ['U.S. News Center', 'Pro-PRC media network'],
            'era' => '2020s',
            'in_custody' => true,
            'released' => false,
            'cases' => [
                [
                    'institution_name' => 'Federal Bureau of Prisons (designation pending)',
                    'institution_state' => 'California',
                    'charges' => 'Acting as an unregistered agent of a foreign government (18 U.S.C. § 951); conspiracy',
                    'arrest_date' => '2024-12-01',
                    'sentenced_date' => '2026-02-10',
                    'incarceration_date' => '2026-02-10',
                    'prosecutor' => 'U.S. Attorney\'s Office, Central District of California; DOJ National Security Division',
                    'judge' => 'R. Gary Klausner (U.S.D.J., C.D. Cal.)',
                    'sentence' => '48 months federal prison',
                ],
            ],
        ];

        $code = Artisan::call('prisoner:add', ['json' => json_encode($entry)]);
        $this->line(trim(Artisan::output()));

        return $code;
    }
}
