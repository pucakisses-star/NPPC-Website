<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Bulk-adds the 23 prisoners listed as petitioners in the 1990 International
 * Tribunal verdict (Yale Journal of Law & Liberation Vol. 2, p. 47, 1991)
 * who are not yet in the NPPC database. Skips any whose name already exists.
 */
final class AddYaleTribunalPrisoners extends Command {
    protected $signature = 'archive:add-yale-tribunal-prisoners';
    protected $description = 'Bulk add the 23 1990 Tribunal petitioners not yet in the NPPC database';

    public function handle(): int {
        $created = 0;
        $skipped = 0;

        foreach ($this->prisoners() as $data) {
            if (Prisoner::where('name', $data['name'])->exists()) {
                $this->warn("skip (exists): {$data['name']}");
                $skipped++;

                continue;
            }

            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $exit = Artisan::call('prisoner:add', ['json' => $json]);
            $this->line(Artisan::output());

            if ($exit === self::SUCCESS) {
                $created++;
            } else {
                $this->error("failed: {$data['name']}");
            }
        }

        $this->info("Done. {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function prisoners(): array {
        return [
            // ----- MOVE family -----
            [
                'name' => 'Alberta Wicker Africa',
                'first_name' => 'Alberta',
                'middle_name' => 'Wicker',
                'last_name' => 'Africa',
                'description' => 'Member of the MOVE Organization, a Black liberation / naturalist commune founded in Philadelphia in 1972 by John Africa. Listed as a political-prisoner petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States, which documented the systematic state repression of MOVE members in the aftermath of the 1978 Powelton Avenue confrontation and the 1985 police bombing of MOVE\'s Osage Avenue home.',
                'gender' => 'Female',
                'state' => 'Pennsylvania',
                'ideologies' => ['Black Liberation', 'Anti-Authoritarian', 'MOVE'],
                'affiliation' => ['MOVE'],
            ],
            [
                'name' => 'Carlos Perez Africa',
                'first_name' => 'Carlos',
                'middle_name' => 'Perez',
                'last_name' => 'Africa',
                'description' => 'Member of the MOVE Organization. Listed as a political-prisoner petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States documenting state repression of MOVE.',
                'gender' => 'Male',
                'state' => 'Pennsylvania',
                'ideologies' => ['Black Liberation', 'Anti-Authoritarian', 'MOVE'],
                'affiliation' => ['MOVE'],
            ],
            [
                'name' => 'Consuella Dotson Africa',
                'first_name' => 'Consuella',
                'middle_name' => 'Dotson',
                'last_name' => 'Africa',
                'description' => 'Member of the MOVE Organization. Listed as a political-prisoner petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States documenting state repression of MOVE.',
                'gender' => 'Female',
                'state' => 'Pennsylvania',
                'ideologies' => ['Black Liberation', 'Anti-Authoritarian', 'MOVE'],
                'affiliation' => ['MOVE'],
            ],
            [
                'name' => 'Michael Hill Africa',
                'first_name' => 'Michael',
                'middle_name' => 'Hill',
                'last_name' => 'Africa',
                'description' => 'Member of the MOVE Organization. Listed as a political-prisoner petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States documenting state repression of MOVE.',
                'gender' => 'Male',
                'state' => 'Pennsylvania',
                'ideologies' => ['Black Liberation', 'Anti-Authoritarian', 'MOVE'],
                'affiliation' => ['MOVE'],
            ],
            [
                'name' => 'Sue Leon Africa',
                'first_name' => 'Sue',
                'middle_name' => 'Leon',
                'last_name' => 'Africa',
                'description' => 'Member of the MOVE Organization. Listed as a political-prisoner petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States documenting state repression of MOVE.',
                'gender' => 'Female',
                'state' => 'Pennsylvania',
                'ideologies' => ['Black Liberation', 'Anti-Authoritarian', 'MOVE'],
                'affiliation' => ['MOVE'],
            ],

            // ----- Puerto Rican independence (FALN) -----
            [
                'name' => 'Haydée Beltrán Torres',
                'first_name' => 'Haydée',
                'last_name' => 'Beltrán Torres',
                'aka' => 'Haydée Beltrán',
                'description' => 'Puerto Rican independentista and member of the Fuerzas Armadas de Liberación Nacional (FALN). Arrested in April 1980 in the Chicago FBI raid that captured 11 FALN members. Convicted of seditious conspiracy and related federal charges and sentenced in 1981 to a long federal prison term. Held at various federal facilities including the Women\'s High Security Unit at Lexington (KY), which Amnesty International condemned for cruel, inhuman, and degrading conditions. Her sentence was commuted by President Bill Clinton in the 1999 FALN clemency along with most of her co-defendants.',
                'gender' => 'Female',
                'state' => 'Puerto Rico',
                'race' => 'Latino',
                'ideologies' => ['Puerto Rican Independence', 'Anti-Imperialism'],
                'affiliation' => ['FALN'],
                'era' => 'Puerto Rican Independence',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Seditious conspiracy and related federal charges',
                    'arrest_date' => '1980-04-04',
                    'convicted' => 'Yes — 1981',
                    'sentence' => 'Long federal sentence; commuted by Pres. Clinton 1999',
                ]],
            ],
            [
                'name' => 'Dylcia Pagán',
                'first_name' => 'Dylcia',
                'last_name' => 'Pagán',
                'description' => 'Puerto Rican independentista, educator, journalist, and television producer. A member of the Fuerzas Armadas de Liberación Nacional (FALN), she was arrested in April 1980 in the Chicago FBI raid that captured 11 FALN members. In 1981 she was convicted of seditious conspiracy, armed robbery, and weapons offenses and sentenced to 55 years in federal prison. Held at various federal facilities including the Women\'s High Security Unit at Lexington (KY). Her sentence was commuted by President Bill Clinton in September 1999 alongside most of her FALN co-defendants. After her release she has remained active in Puerto Rican independence and human-rights organizing.',
                'gender' => 'Female',
                'state' => 'Puerto Rico',
                'race' => 'Latino',
                'birthdate' => '1946-10-15',
                'ideologies' => ['Puerto Rican Independence', 'Anti-Imperialism'],
                'affiliation' => ['FALN'],
                'era' => 'Puerto Rican Independence',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Seditious conspiracy, armed robbery, weapons offenses',
                    'arrest_date' => '1980-04-04',
                    'convicted' => 'Yes — 1981',
                    'sentence' => '55 years federal prison; commuted by Pres. Clinton 1999',
                ]],
            ],
            [
                'name' => 'Lucy Rodríguez',
                'first_name' => 'Lucy',
                'last_name' => 'Rodríguez',
                'aka' => 'Ida Luz Rodríguez',
                'description' => 'Puerto Rican independentista and member of the Fuerzas Armadas de Liberación Nacional (FALN). Arrested in April 1980 in the Chicago FBI raid; convicted in 1981 of seditious conspiracy and related federal charges and sentenced to decades in federal prison. Her sentence was commuted by President Bill Clinton in September 1999 along with most of her FALN co-defendants.',
                'gender' => 'Female',
                'state' => 'Puerto Rico',
                'race' => 'Latino',
                'ideologies' => ['Puerto Rican Independence', 'Anti-Imperialism'],
                'affiliation' => ['FALN'],
                'era' => 'Puerto Rican Independence',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Seditious conspiracy and related federal charges',
                    'arrest_date' => '1980-04-04',
                    'convicted' => 'Yes — 1981',
                    'sentence' => 'Long federal sentence; commuted by Pres. Clinton 1999',
                ]],
            ],
            [
                'name' => 'Ana María Gelabert',
                'first_name' => 'Ana María',
                'last_name' => 'Gelabert',
                'description' => 'Puerto Rican independentista identified as a political-prisoner petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States. Held in U.S. custody in connection with the Puerto Rican national liberation movement.',
                'gender' => 'Female',
                'race' => 'Latino',
                'ideologies' => ['Puerto Rican Independence', 'Anti-Imperialism'],
                'era' => 'Puerto Rican Independence',
            ],

            // ----- Irish Republican -----
            [
                'name' => 'Joseph Doherty',
                'first_name' => 'Joseph',
                'last_name' => 'Doherty',
                'description' => 'Volunteer in the Provisional Irish Republican Army (PIRA). Born in Belfast on January 20, 1955. In May 1980 he was part of a four-man PIRA Active Service Unit that exchanged fire with a British SAS team in Belfast\'s Antrim Road; SAS Captain Herbert Westmacott was killed in the engagement. Convicted in absentia by a Diplock court after escaping (with seven others) from Crumlin Road jail in Belfast in June 1981. Fled to New York City, where the FBI captured him in 1983. His extradition was initially refused on the "political-offense" exception by Judge John E. Sprizzo. He became the subject of a nine-year diplomatic and legal battle that reached the U.S. Supreme Court (INS v. Doherty, 502 U.S. 314, 1992). He was deported to the United Kingdom in February 1992, returned to prison in Northern Ireland, and released in 1998 under the Good Friday Agreement.',
                'gender' => 'Male',
                'race' => 'White',
                'birthdate' => '1955-01-20',
                'ideologies' => ['Irish Republicanism', 'Anti-Imperialism'],
                'affiliation' => ['Provisional IRA'],
                'era' => 'Irish Republicanism',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Metropolitan Correctional Center, New York',
                    'institution_city' => 'New York',
                    'institution_state' => 'New York',
                    'charges' => 'Illegal entry; subject of extradition proceedings (escape from Crumlin Road jail; conviction in absentia for SAS engagement)',
                    'arrest_date' => '1983-06-18',
                    'release_date' => '1992-02-19',
                    'convicted' => 'Yes — convicted in absentia in Northern Ireland; held in U.S. on extradition/immigration matters',
                    'sentence' => 'Deported to U.K. February 1992 after nine years of U.S. detention; released under Good Friday Agreement 1998',
                ]],
            ],

            // ----- Plowshares / Peace -----
            [
                'name' => 'Dorothy Eber',
                'first_name' => 'Dorothy',
                'last_name' => 'Eber',
                'description' => 'Plowshares peace activist named as a petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States. Plowshares activists are arrested for symbolic acts of nuclear-disarmament protest at U.S. military and weapons-production facilities.',
                'gender' => 'Female',
                'ideologies' => ['Anti-Militarism', 'Plowshares', 'Pacifism'],
                'era' => 'Plowshares Movement',
            ],
            [
                'name' => 'Jennifer Haines',
                'first_name' => 'Jennifer',
                'last_name' => 'Haines',
                'description' => 'Plowshares peace activist named as a petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States, jailed for nonviolent direct action against the U.S. nuclear-weapons complex.',
                'gender' => 'Female',
                'ideologies' => ['Anti-Militarism', 'Plowshares', 'Pacifism'],
                'era' => 'Plowshares Movement',
            ],

            // ----- Anti-imperialist / UFF -----
            [
                'name' => 'Larry Giddings',
                'first_name' => 'Larry',
                'last_name' => 'Giddings',
                'description' => 'Anti-authoritarian political prisoner and co-defendant of Bill Dunne. In 1979 Giddings and Dunne were arrested following an attempt to liberate a comrade from the King County Jail in Seattle, Washington. Both were charged with possession of an automatic weapon, auto theft, and aiding and abetting the escape. Police alleged they were associated with a small armed group called the Wellspring Communion and that the action was funded by bank expropriations. Giddings served decades in federal control units, including the Marion Penitentiary, before being paroled from federal prison in 2004.',
                'gender' => 'Male',
                'race' => 'White',
                'ideologies' => ['Anti-Authoritarian', 'Anti-Imperialism'],
                'era' => 'Anti-Imperialist',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Possession of automatic weapon, auto theft, aiding and abetting escape from King County Jail',
                    'arrest_date' => '1979-01-01',
                    'release_date' => '2004-01-01',
                    'convicted' => 'Yes',
                    'sentence' => 'Federal sentence; paroled 2004',
                ]],
            ],
            [
                'name' => 'Carol Manning',
                'first_name' => 'Carol',
                'last_name' => 'Manning',
                'description' => 'Anti-imperialist political prisoner associated with the United Freedom Front (UFF), the armed leftist group that bombed U.S. military and corporate targets in the 1970s and 1980s. Spouse of UFF prisoner Thomas Manning. Arrested with Tom Manning on April 24, 1985, as part of the broader UFF roundup that included Ray Levasseur, Patricia Gross, Richard Williams, Jaan Laaman, and Barbara Curzi. Convicted and imprisoned in connection with UFF activity.',
                'gender' => 'Female',
                'race' => 'White',
                'ideologies' => ['Anti-Imperialism'],
                'affiliation' => ['United Freedom Front'],
                'era' => 'Anti-Imperialist',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'United Freedom Front-related charges',
                    'arrest_date' => '1985-04-24',
                    'convicted' => 'Yes',
                    'sentence' => 'Federal sentence; subsequently released',
                ]],
            ],
            [
                'name' => 'Richard Picariello',
                'first_name' => 'Richard',
                'last_name' => 'Picariello',
                'description' => 'Anti-imperialist political prisoner convicted of 1976 bombings of military and federal facilities in Massachusetts attributed to the Sam Melville/Jonathan Jackson Unit, a forerunner of the United Freedom Front. Sentenced in 1977 to a long federal prison term.',
                'gender' => 'Male',
                'race' => 'White',
                'ideologies' => ['Anti-Imperialism'],
                'affiliation' => ['Sam Melville/Jonathan Jackson Unit'],
                'era' => 'Anti-Imperialist',
                'cases' => [[
                    'charges' => 'Bombings of military and federal facilities in Massachusetts',
                    'convicted' => 'Yes — 1977',
                    'sentence' => 'Long federal sentence',
                ]],
            ],

            // ----- Black Liberation Army / New Afrikan -----
            [
                'name' => 'Basheer Hameed',
                'first_name' => 'Basheer',
                'last_name' => 'Hameed',
                'aka' => 'James York',
                'description' => 'Member of the Black Liberation Army. Convicted with Abdul Majid of the April 1981 killing of NYPD officer John Scarangella in Queens, New York. Sentenced to 25 years to life and held in New York State prisons until his death in custody.',
                'gender' => 'Male',
                'race' => 'Black',
                'ideologies' => ['Black Liberation', 'New Afrikan'],
                'affiliation' => ['Black Liberation Army'],
                'era' => 'Black Liberation',
                'cases' => [[
                    'charges' => 'Murder of NYPD officer (April 1981)',
                    'convicted' => 'Yes',
                    'sentence' => '25 years to life',
                ]],
            ],
            [
                'name' => 'Teddy Jah Heath',
                'first_name' => 'Teddy',
                'middle_name' => 'Jah',
                'last_name' => 'Heath',
                'aka' => 'Teddy "Jah" Heath',
                'description' => 'Member of the Black Liberation Army imprisoned in New York State for actions in the late 1970s arising from the BLA struggle. Identified as a political-prisoner petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States.',
                'gender' => 'Male',
                'race' => 'Black',
                'ideologies' => ['Black Liberation', 'New Afrikan'],
                'affiliation' => ['Black Liberation Army'],
                'era' => 'Black Liberation',
            ],
            [
                'name' => 'Raphael Kwesi Joseph',
                'first_name' => 'Raphael',
                'middle_name' => 'Kwesi',
                'last_name' => 'Joseph',
                'description' => 'New Afrikan political prisoner identified as a petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States.',
                'gender' => 'Male',
                'race' => 'Black',
                'ideologies' => ['Black Liberation', 'New Afrikan'],
                'era' => 'Black Liberation',
            ],
            [
                'name' => 'Mohaman Koti',
                'first_name' => 'Mohaman',
                'last_name' => 'Koti',
                'description' => 'New Afrikan political prisoner from the Black Liberation tradition. Identified as a petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States.',
                'gender' => 'Male',
                'race' => 'Black',
                'ideologies' => ['Black Liberation', 'New Afrikan'],
                'era' => 'Black Liberation',
            ],
            [
                'name' => 'Ahmad Abdur Rahman',
                'first_name' => 'Ahmad',
                'middle_name' => 'Abdur',
                'last_name' => 'Rahman',
                'description' => 'New Afrikan political prisoner from the Black Liberation tradition. Identified as a petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States.',
                'gender' => 'Male',
                'race' => 'Black',
                'ideologies' => ['Black Liberation', 'New Afrikan'],
                'era' => 'Black Liberation',
            ],
            [
                'name' => 'Yvonne Small',
                'first_name' => 'Yvonne',
                'last_name' => 'Small',
                'description' => 'Political prisoner identified as a petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States.',
                'gender' => 'Female',
                'ideologies' => ['Black Liberation'],
                'era' => 'Black Liberation',
            ],
            [
                'name' => 'Robert Taylor (political prisoner)',
                'first_name' => 'Robert',
                'last_name' => 'Taylor',
                'description' => 'Political prisoner identified as a petitioner in the 1990 Special International Tribunal on Political Prisoners and Prisoners of War in the United States.',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation'],
                'era' => 'Black Liberation',
            ],

            // ----- International -----
            [
                'name' => 'Yu Kikumura',
                'first_name' => 'Yu',
                'last_name' => 'Kikumura',
                'description' => 'Japanese Red Army member arrested on April 12, 1988, while driving south on the New Jersey Turnpike near East Brunswick. State troopers stopped him for a routine traffic violation and discovered three pipe bombs concealed in modified fire extinguishers in his car. Investigators concluded he was en route to bomb a U.S. Navy recruiting station in lower Manhattan timed to the second anniversary of the U.S. bombing of Libya. Convicted in November 1988 in U.S. District Court (NJ) of explosives and passport-fraud charges and originally sentenced to 30 years; that sentence was later reduced but he served decades in federal control units including USP Marion in Illinois. Released in 2007 and deported to Japan.',
                'gender' => 'Male',
                'race' => 'Asian',
                'ideologies' => ['Anti-Imperialism', 'International Solidarity'],
                'affiliation' => ['Japanese Red Army'],
                'era' => 'Anti-Imperialist',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'USP Marion',
                    'institution_city' => 'Marion',
                    'institution_state' => 'Illinois',
                    'charges' => 'Possession and transportation of explosives; passport fraud',
                    'arrest_date' => '1988-04-12',
                    'release_date' => '2007-01-01',
                    'convicted' => 'Yes — November 1988',
                    'sentence' => '30 years federal prison (later reduced)',
                ]],
            ],
        ];
    }
}
