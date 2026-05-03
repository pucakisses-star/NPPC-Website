<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddCourtneyWilliams extends Command
{
    protected $signature = 'prisoners:add-courtney-williams';
    protected $description = 'Add Courtney Williams, the former Delta Force veteran charged in April 2026 under the Espionage Act for sharing information with a journalist that exposed sexual harassment in the Army\'s most elite unit.';

    private const BIO = <<<'TXT'
Courtney Williams is a U.S. Army veteran who served eight years with the 1st Special Forces Operational Detachment-Delta — Delta Force, the Army's most secretive Tier-1 special-mission unit, based at Fort Bragg in North Carolina. During her service she held a Top Secret / Sensitive Compartmented Information (TS/SCI) clearance and worked at the unit's headquarters and operations cells. After leaving uniformed service she continued as a civilian Department of the Army employee, retaining her clearance until shortly before her April 2026 arrest.

Beginning in 2022, Williams communicated extensively with the journalist Seth Harp, who was researching what would become his August 2025 book "The Fort Bragg Cartel: Drug Trafficking and Murder in the Special Forces." Federal investigators have catalogued hundreds of minutes of phone calls and approximately 180 text messages between Williams and Harp between 2022 and 2025, along with documents and other materials she allegedly transmitted. The August 2024 Politico Magazine excerpt of Harp's reporting, titled "My Life Became a Living Hell: One Woman's Career in Delta Force, the Army's Most Elite Unit," was based largely on Williams's first-person testimony of sustained sexual harassment, gender discrimination, and retaliation she experienced inside the unit.

On April 22, 2026, FBI agents arrested Williams at her home in Wagram, North Carolina. The Department of Justice's National Security Division charged her with one count of unauthorized communication of national defense information under 18 U.S.C. § 793(d) — a provision of the Espionage Act of 1917 that carries a maximum penalty of ten years in federal prison. The criminal complaint and accompanying FBI affidavit allege that the materials she shared with Harp included tactics, techniques and procedures used by elite military units. Her supporters and First Amendment scholars have noted that former Delta operators routinely discuss similar operational material on podcasts, YouTube channels, and in commercially published memoirs without prosecution, and have argued that the prosecution selectively targets her for the underlying disclosure of sexual harassment within the unit rather than for any genuine harm to national security.

Williams was held briefly at the federal courthouse in Fayetteville and released on bond conditions including home confinement with electronic monitoring at her Wagram, North Carolina residence pending trial. Her case is one of the small number of post-2010 Espionage Act prosecutions targeting a person who shared information with a journalist rather than with a foreign power, in the lineage of Thomas Drake (NSA, 2010), Jeffrey Sterling (CIA, 2011, in this database), Daniel E. Hale (intelligence contractor, 2019, in this database), Terry J. Albury (FBI, 2018, in this database), and Reality Winner (NSA contractor, 2017, in this database).
TXT;

    public function handle(): int
    {
        if (Prisoner::where('name', 'Courtney Williams')->exists()) {
            $this->error('Courtney Williams already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $fayetteville = Institution::firstOrCreate(
                ['name' => 'Home confinement (Wagram, NC) on federal pretrial release'],
                ['city' => 'Wagram', 'state' => 'North Carolina']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Courtney Williams',
                'first_name'     => 'Courtney',
                'last_name'      => 'Williams',
                'description'    => self::BIO,
                'gender'         => 'Female',
                'state'          => 'North Carolina',
                'era'            => '2020s',
                'ideologies'     => ['Whistleblower', 'First Amendment', 'Anti-military-misconduct'],
                'affiliation'    => ['U.S. Army (former)', '1st Special Forces Operational Detachment-Delta (former)'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => true,
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $fayetteville->id,
                'charges'            => "One count of unauthorized communication of national defense information (18 U.S.C. § 793(d), Espionage Act of 1917) — for allegedly transmitting tactics, techniques, and procedures of elite military units to journalist Seth Harp between 2022 and 2025 in connection with his August 2025 book 'The Fort Bragg Cartel: Drug Trafficking and Murder in the Special Forces' and the August 2024 Politico Magazine excerpt 'My Life Became a Living Hell: One Woman's Career in Delta Force, the Army's Most Elite Unit'",
                'arrest_date'        => '2026-04-22',
                'incarceration_date' => '2026-04-22',
                'release_date'       => '2026-04-23',
                'convicted'          => 'No — pretrial. Released on bond after brief federal custody at the U.S. courthouse in Fayetteville, NC',
                'sentence'           => 'No sentence yet. Maximum statutory penalty: 10 years in federal prison. Currently on home confinement with electronic monitoring at her residence in Wagram, NC pending trial',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
