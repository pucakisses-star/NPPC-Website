<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds three prisoners surfaced from the Alejandrina Torres 1987
 * LIDLIP monograph (Women's Day speech roll-call):
 *   - Doña Isabel Rosado Morales (Puerto Rican Nationalist Party)
 *   - Doña Consuelo Lee de Corretjer (Puerto Rican Nationalist poet)
 *   - Anna Mae Pictou Aquash (AIM, killed at Pine Ridge 1975) — memorial
 *
 * Also enriches the existing Alejandrina Torres record with case-detail
 * facts pulled from the monograph: Aug 1, 1985 trial date, Chicago MCC
 * pretrial isolation, July 11, 1984 staff incident, heart condition
 * details, Lexington HSU sensory-deprivation conditions.
 */
final class AddRosadoCorretjerAquash extends Command {
    protected $signature = 'archive:add-rosado-corretjer-aquash';
    protected $description = 'Add Rosado, Corretjer, Aquash + enrich Alejandrina Torres record from the 1987 monograph';

    public function handle(): int {
        $payloads = $this->additions();
        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }

        // Enrich Alejandrina Torres
        $at = Prisoner::where('name', 'Alejandrina Torres')->first();
        if ($at) {
            $append = "Trial began August 1, 1985 in U.S. District Court for the Northern District of Illinois under Judge George Leighton, with prosecutor Mr. Hartzler. Held pretrial in isolation at the Chicago Metropolitan Correctional Center from June 29, 1983 — placed alone in the men's unit with a curtainless shower, surveillance camera over the toilet, and male-guard strip-searches; she was hospitalized for chest pain (mitral valve prolapse and bundle-branch block) before being moved to the women's unit on July 11, 1984 after a staff-engineered confrontation. From 1986 she was held at the Lexington High Security Unit (HSU) — an underground sensory-deprivation chamber for women political prisoners built by the BOP, where she was subject to 24-hour surveillance, rectal and vaginal probes not used on other prisoners, and severe heart problems and limited use of her right arm developed under abusive treatment. The Lexington HSU was later closed by federal court order as illegally targeting prisoners for their political beliefs. Released September 10, 1999 by President Clinton's commutation.";
            $current = trim((string) $at->description);
            if (! str_contains($current, 'Trial began August 1, 1985')) {
                $at->description = $current === '' ? $append : $current."\n\n".$append;
                $at->save();
                $this->info('UPDATE: Alejandrina Torres description enriched.');
            } else {
                $this->info('Alejandrina Torres description already enriched.');
            }
        } else {
            $this->warn('Alejandrina Torres not found.');
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function additions(): array {
        return [
            [
                'name' => 'Isabel Rosado Morales',
                'first_name' => 'Isabel',
                'last_name' => 'Rosado Morales',
                'aka' => 'Doña Isabel Rosado',
                'description' => 'Puerto Rican Nationalist Party leader and educator known as "Doña Isabel," repeatedly imprisoned for her independentista activism. Active in the Nationalist Party from the 1930s, she was arrested in the wave of repression that followed the 1950 Jayuya Uprising and the 1954 attack on the U.S. House of Representatives, and was held at the Vega Alta women\'s prison in Puerto Rico for roughly five years (1954–1958) alongside Carmen Valentín, Lolita Lebrón\'s mother, and other Nationalist women. Arrested again in 1985 in the FBI sweep of the Macheteros / Wells Fargo investigation. She remained a leading figure of the Nationalist Party until her death in San Juan in 2017 at the age of 109.',
                'state' => 'Puerto Rico',
                'race' => 'Puerto Rican',
                'gender' => 'Female',
                'birthdate' => '1907-08-19',
                'death_date' => '2017-04-04',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonialism'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'era' => '1950s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Vega Alta Women\'s Prison',
                    'institution_city' => 'Vega Alta',
                    'institution_state' => 'Puerto Rico',
                    'charges' => 'Seditious conspiracy / violations of Puerto Rico\'s Ley de la Mordaza (Gag Law, Ley 53)',
                    'arrest_date' => '1954-03-04',
                    'incarceration_date' => '1954-03-04',
                    'release_date' => '1958-08-01',
                    'convicted' => 'Yes — convicted under the Gag Law in the wave of repression following the 1954 House attack',
                    'sentence' => 'Approximately 5 years',
                    'imprisoned_for_days' => 1611,
                ]],
            ],
            [
                'name' => 'Consuelo Lee de Corretjer',
                'first_name' => 'Consuelo',
                'last_name' => 'Lee Tapia',
                'aka' => 'Doña Consuelo Lee de Corretjer',
                'description' => 'Puerto Rican poet, journalist, and Nationalist Party activist; wife and political comrade of the poet and Nationalist leader Juan Antonio Corretjer. Imprisoned in 1936 in the wave of arrests that followed the assassination of Police Chief E. Francis Riggs and the prosecution of Pedro Albizu Campos; held in Puerto Rican and U.S. federal custody as a Nationalist Party member. She continued writing and organizing for Puerto Rican independence until her death in 1989.',
                'state' => 'Puerto Rico',
                'race' => 'Puerto Rican',
                'gender' => 'Female',
                'birthdate' => '1904-02-09',
                'death_date' => '1989-09-02',
                'ideologies' => ['Puerto Rican Independence', 'Socialism'],
                'affiliation' => ['Puerto Rican Nationalist Party', 'Partido Socialista Puertorriqueño'],
                'era' => '1930s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Puerto Rico',
                    'charges' => 'Conspiracy / Nationalist Party membership (1936 roundup following the Riggs assassination)',
                    'arrest_date' => '1936-03-01',
                    'convicted' => 'Yes — sentenced under the wave of federal prosecutions against the Nationalist Party',
                    'sentence' => 'Federal imprisonment (term not precisely documented in available sources)',
                ]],
            ],
            [
                'name' => 'Anna Mae Pictou Aquash',
                'first_name' => 'Anna',
                'middle_name' => 'Mae',
                'last_name' => 'Aquash',
                'aka' => 'Anna Mae Pictou-Aquash',
                'description' => 'Mi\'kmaq AIM activist born March 27, 1945 in Indian Brook (Shubenacadie 13), Nova Scotia. She participated in the 1973 Wounded Knee occupation, organized at the AIM Trail of Broken Treaties caravan, and worked at Pine Ridge during the 1970s "Reign of Terror." Repeatedly arrested by federal and tribal authorities, she was held briefly at the Pierre, SD jail in early 1975 on weapons charges and questioned about Peltier-era cases. She disappeared in late 1975 and her body was found on the Pine Ridge Reservation on February 24, 1976 with a single gunshot wound to the back of the head. Her killing — for years officially attributed to "exposure" before a re-autopsy revealed the bullet — became one of the most notorious cases of the FBI/AIM era. AIM members Arlo Looking Cloud and John Graham were eventually convicted of her murder in 2004 and 2010 respectively. Commemorated in NPPC as a memorial entry alongside Pedro Bissonette and other AIM/Pine Ridge martyrs.',
                'state' => 'South Dakota',
                'race' => 'Native American',
                'gender' => 'Female',
                'birthdate' => '1945-03-27',
                'death_date' => '1975-12-12',
                'ideologies' => ['Indigenous sovereignty', 'AIM', 'Red Power'],
                'affiliation' => ['American Indian Movement'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_state' => 'South Dakota',
                    'charges' => 'Weapons charges, federal grand jury subpoenas (Pine Ridge / Peltier-era investigations)',
                    'arrest_date' => '1975-09-05',
                    'convicted' => 'No — killed before any trial',
                    'sentence' => 'Murdered December 12, 1975 on Pine Ridge Reservation; body found February 24, 1976',
                ]],
            ],
        ];
    }
}
