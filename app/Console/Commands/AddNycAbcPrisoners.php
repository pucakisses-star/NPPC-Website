<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Imports prisoners surfaced from the NYC Anarchist Black Cross April
 * 2026 PP/POW listing + adjacent NYC ABC coverage (RDTW 2025 reportback,
 * Stop Cop City RICO 61, Prairieland Defendants).
 *
 * Adds (27):
 *   - 11 from the NYC ABC April 2026 listing
 *     (Ronald Reed, Kojo Bomani Sababu, Hridindu Roychowdhury,
 *     Xinachtli/Alvaro Luna Hernandez, Andrew Mickel, Joy Powell,
 *     Alex Stokes/Contompasis, Abdul Azeez, Hanif Shabazz Bey,
 *     Malik Smith, Beaumont Gereau)
 *   - 13 Prairieland Defendants (July 2025 ICE-facility action; trial verdict March 13, 2026)
 *   - 3 flagged (Jamel Floyd memorial, Priscilla Grim Stop Cop City RICO,
 *     Jakhi McCray Brooklyn NYPD-vehicle arson)
 *
 * Updates (1):
 *   - Brian "Peppy" DiPippa marked released (per NYC ABC March 2026 listing
 *     "Welcome home, Peppy!")
 *
 * Also registers the April 2026 NYC ABC PP/POW listing PDF as an
 * ArchiveRecord.
 */
final class AddNycAbcPrisoners extends Command {
    protected $signature = 'archive:add-nyc-abc-prisoners';
    protected $description = 'Add prisoners + PDF surfaced from nycabc.wordpress.com (April 2026 listing)';

    public function handle(): int {
        $added = 0;
        $skippedAdds = 0;
        foreach ($this->additions() as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skippedAdds++;
            }
        }

        // Peppy DiPippa release update
        $peppy = Prisoner::where('name', 'Brian DiPippa')->first();
        if ($peppy) {
            $peppy->in_custody = false;
            $peppy->released = true;
            $case = $peppy->cases()->first();
            if ($case && empty($case->release_date)) {
                $case->release_date = '2026-03-01';
                $case->save();
            }
            $append = 'Released from federal custody in early March 2026 per NYC ABC reporting.';
            $current = trim((string) $peppy->description);
            if (! str_contains($current, $append)) {
                $peppy->description = $current === '' ? $append : $current."\n\n".$append;
            }
            $peppy->save();
            $this->info('UPDATE: Brian DiPippa marked released.');
        } else {
            $this->warn('UPDATE skipped — Brian DiPippa not found.');
        }

        // Register the April 2026 PP/POW listing PDF
        $slug = 'nyc-abc-pppow-listing-april-2026';
        $payload = [
            'title' => 'NYC ABC Political Prisoner / POW Listing — April 2026',
            'description' => 'Monthly compendium of United States political prisoners and prisoners of war, edition 19.3 (April 2026), compiled by the New York City Anarchist Black Cross. Includes contact addresses, inmate numbers, charges, and case histories for currently imprisoned long-term political prisoners across federal and state systems.',
            'record_type' => 'document',
            'source_format' => 'newsletter',
            'file' => '/pdfs/nyc-abc/nycabc_polprislisting_april-2026_legal.pdf',
            'collection' => 'NYC Anarchist Black Cross',
            'publisher' => 'NYC ABC',
            'year' => 2026,
            'date' => '2026-04-01',
            'subjects' => ['Anarchist Black Cross', 'Political Prisoners', 'Prisoners of War'],
            'is_digitized' => true,
            'published' => true,
        ];
        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: NYC ABC April 2026 listing.');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: NYC ABC April 2026 listing.');
        }

        $this->info("\nDone. Added={$added} SkippedAdds={$skippedAdds}");

        return self::SUCCESS;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function additions(): array {
        $prairieland = ['Prairieland Defendants'];
        $prairielandIdeo = ['Anti-imperialism', 'Anti-ICE', 'Migrant Solidarity'];
        $pgWebsite = 'https://prairielanddefendants.com';

        return [
            // ---------- NYC ABC April 2026 listing (11) ----------
            [
                'name' => 'Ronald Reed',
                'first_name' => 'Ronald',
                'last_name' => 'Reed',
                'description' => 'Former St. Paul Central High School student activist and member of the Black United Front, convicted in 1995 for the May 22, 1970 shooting death of St. Paul police officer James Sackett. Reed has consistently maintained his innocence and the case was reopened decades after the killing. He is serving a life sentence at MCF Lino Lakes in Minnesota.',
                'state' => 'Minnesota',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation'],
                'affiliation' => ['Black United Front'],
                'era' => '1990s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '219531',
                'cases' => [[
                    'institution_name' => 'MCF Lino Lakes',
                    'institution_city' => 'Lino Lakes',
                    'institution_state' => 'Minnesota',
                    'charges' => 'First-degree murder of St. Paul police officer James Sackett (May 22, 1970)',
                    'sentence' => 'Life',
                ]],
            ],
            [
                'name' => 'Kojo Bomani Sababu',
                'aka' => 'Grailing Brown',
                'first_name' => 'Kojo',
                'middle_name' => 'Bomani',
                'last_name' => 'Sababu',
                'description' => 'New Afrikan prisoner of war captured after a December 19, 1975 bank expropriation in Princeton, New Jersey. He was later charged in a conspiracy alleging a plan to free Puerto Rican independentista Oscar López Rivera and other comrades. He is serving a 55-year sentence at USP Canaan in Pennsylvania.',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['New Afrikan Independence', 'Black Liberation'],
                'era' => '1970s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '39384-066',
                'cases' => [[
                    'institution_name' => 'USP Canaan',
                    'institution_city' => 'Waymart',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Bank expropriation; conspiracy',
                    'incarceration_date' => '1975-12-19',
                    'sentence' => '55 years',
                ]],
            ],
            [
                'name' => 'Hridindu Sankar Roychowdhury',
                'first_name' => 'Hridindu',
                'middle_name' => 'Sankar',
                'last_name' => 'Roychowdhury',
                'description' => 'PhD student arrested for an arson attempt on the Wisconsin Family Action headquarters in Madison in May 2022, days after the leak of the Supreme Court draft opinion overturning Roe v. Wade. He was identified through DNA recovered from a discarded burrito and entered a non-cooperating plea. He is serving 90 months in federal prison at FCI Thomson in Illinois.',
                'race' => 'South Asian',
                'gender' => 'Male',
                'ideologies' => ['Anarchism', 'Reproductive Justice'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '51111-510',
                'cases' => [[
                    'institution_name' => 'FCI Thomson',
                    'institution_city' => 'Thomson',
                    'institution_state' => 'Illinois',
                    'charges' => 'Attempted arson of Wisconsin Family Action headquarters',
                    'sentence' => '90 months',
                ]],
            ],
            [
                'name' => 'Alvaro Luna Hernandez',
                'aka' => 'Xinachtli',
                'first_name' => 'Alvaro',
                'middle_name' => 'Luna',
                'last_name' => 'Hernandez',
                'description' => 'Longtime Chicano human rights organizer from Texas who led the Ricardo Aldape Guerra Defense Committee and founded the Barrio Defense Committees. He was convicted in 1997 in Odessa, Texas for disarming a sheriff who had drawn a pistol on him, and sentenced to 50 years. He continues to organize from prison.',
                'state' => 'Texas',
                'race' => 'Chicano',
                'gender' => 'Male',
                'ideologies' => ['Chicano Liberation', 'Indigenous Liberation'],
                'era' => '1990s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '255735',
                'cases' => [[
                    'institution_name' => 'Texas Department of Criminal Justice',
                    'institution_city' => 'Dallas',
                    'institution_state' => 'Texas',
                    'charges' => 'Aggravated assault on a peace officer (disarming an officer)',
                    'sentence' => '50 years',
                ]],
            ],
            [
                'name' => 'Andrew Mickel',
                'first_name' => 'Andrew',
                'last_name' => 'Mickel',
                'description' => 'Anti-police-brutality activist who shot and killed Red Bluff, California police officer Daniel Walters on November 19, 2002, and identified himself via an internet posting describing the act as resistance to state violence. Originally sentenced to death, he is currently held at the California Institution for Men in Chino.',
                'state' => 'California',
                'gender' => 'Male',
                'ideologies' => ['Anti-imperialism', 'Anarchism'],
                'era' => '2000s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => 'V77400',
                'cases' => [[
                    'institution_name' => 'California Institution for Men',
                    'institution_city' => 'Chino',
                    'institution_state' => 'California',
                    'charges' => 'Murder of Red Bluff police officer Daniel Walters',
                    'incarceration_date' => '2002-11-19',
                    'sentence' => 'Death (currently held at CIM)',
                ]],
            ],
            [
                'name' => 'Joy Powell',
                'first_name' => 'Joy',
                'last_name' => 'Powell',
                'description' => 'Rochester, New York pastor and anti-police-corruption activist convicted by an all-white jury with no eyewitness testimony. She is serving a 16-year sentence with an additional 7-year concurrent term, and is held at Bedford Hills Correctional Facility. Supporters maintain her prosecution was retaliation for her organizing against police misconduct.',
                'state' => 'New York',
                'race' => 'Black',
                'gender' => 'Female',
                'ideologies' => ['Anti-Police Brutality', 'Black Liberation'],
                'era' => '2000s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '07-G-0632',
                'cases' => [[
                    'institution_name' => 'Bedford Hills Correctional Facility',
                    'institution_city' => 'Bedford Hills',
                    'institution_state' => 'New York',
                    'sentence' => '16 years plus 7 years concurrent',
                ]],
            ],
            [
                'name' => 'Alexander Contompasis',
                'aka' => 'Alex Stokes',
                'first_name' => 'Alexander',
                'last_name' => 'Contompasis',
                'description' => 'Antifascist journalist charged after a January 6, 2021 counter-protest in Albany, New York at which a Proud Boy tased a Black man. He was prosecuted long after the incident and sentenced to 20 years. He is currently held at Sing Sing Correctional Facility.',
                'state' => 'New York',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => 'https://freealexstokes.com',
                'inmate_number' => '22-B-5028',
                'cases' => [[
                    'institution_name' => 'Sing Sing Correctional Facility',
                    'institution_city' => 'Ossining',
                    'institution_state' => 'New York',
                    'charges' => 'Charges stemming from January 6, 2021 Albany counter-protest',
                    'sentence' => '20 years',
                ]],
            ],
            [
                'name' => 'Abdul Azeez',
                'first_name' => 'Abdul',
                'last_name' => 'Azeez',
                'description' => 'One of the men convicted in the 1973 trial following the September 6, 1972 Fountain Valley Golf Course incident on St. Croix, U.S. Virgin Islands, in which eight people were killed. The case took place against the backdrop of Black liberation organizing and anti-colonial resistance to U.S. rule in the Virgin Islands, and the defendants have long maintained their convictions were the product of torture and coerced confessions.',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', 'Anti-Colonial'],
                'affiliation' => ['Virgin Islands 5'],
                'era' => '1970s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'charges' => 'Murder (Fountain Valley Golf Course case, St. Croix, USVI)',
                    'incarceration_date' => '1973-08-13',
                    'sentence' => 'Eight consecutive life sentences',
                ]],
            ],
            [
                'name' => 'Hanif Shabazz Bey',
                'aka' => 'Warren Ballentine',
                'first_name' => 'Hanif',
                'middle_name' => 'Shabazz',
                'last_name' => 'Bey',
                'description' => 'One of the men convicted in the 1973 trial following the September 6, 1972 Fountain Valley Golf Course incident on St. Croix, U.S. Virgin Islands, in which eight people were killed. He has been incarcerated for more than five decades and his defense committee has long argued the convictions rested on confessions extracted through torture. He is currently held at Tallahatchie County Correctional Facility in Mississippi.',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', 'Anti-Colonial'],
                'affiliation' => ['Virgin Islands 5'],
                'era' => '1970s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '19-1878',
                'cases' => [[
                    'institution_name' => 'Tallahatchie County Correctional Facility',
                    'institution_state' => 'Mississippi',
                    'charges' => 'Murder (Fountain Valley Golf Course case, St. Croix, USVI)',
                    'incarceration_date' => '1973-08-13',
                    'sentence' => 'Eight consecutive life sentences',
                ]],
            ],
            [
                'name' => 'Malik Smith',
                'aka' => 'Meral Smith',
                'first_name' => 'Malik',
                'last_name' => 'Smith',
                'description' => 'One of the men convicted in the 1973 trial following the September 6, 1972 Fountain Valley Golf Course incident on St. Croix, U.S. Virgin Islands, in which eight people were killed. He has been incarcerated for over five decades despite long-standing claims that the convictions were obtained through torture and coerced confessions. He is currently held at Tallahatchie County Correctional Facility in Mississippi.',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', 'Anti-Colonial'],
                'affiliation' => ['Virgin Islands 5'],
                'era' => '1970s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '19-1874',
                'cases' => [[
                    'institution_name' => 'Tallahatchie County Correctional Facility',
                    'institution_state' => 'Mississippi',
                    'charges' => 'Murder (Fountain Valley Golf Course case, St. Croix, USVI)',
                    'incarceration_date' => '1973-08-13',
                    'sentence' => 'Eight consecutive life sentences',
                ]],
            ],
            [
                'name' => 'Beaumont Gereau',
                'first_name' => 'Beaumont',
                'last_name' => 'Gereau',
                'description' => 'One of the men convicted in the 1973 trial following the September 6, 1972 Fountain Valley Golf Course incident on St. Croix, U.S. Virgin Islands, in which eight people were killed. He has been imprisoned for more than five decades, and his defense has long maintained that the convictions were the product of torture and coerced confessions. He is currently held at Tallahatchie County Correctional Facility in Mississippi.',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', 'Anti-Colonial'],
                'affiliation' => ['Virgin Islands 5'],
                'era' => '1970s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '19-1952',
                'cases' => [[
                    'institution_name' => 'Tallahatchie County Correctional Facility',
                    'institution_state' => 'Mississippi',
                    'charges' => 'Murder (Fountain Valley Golf Course case, St. Croix, USVI)',
                    'incarceration_date' => '1973-08-13',
                    'sentence' => 'Eight consecutive life sentences',
                ]],
            ],

            // ---------- Flagged (3) ----------
            [
                'name' => 'Jamel Floyd',
                'first_name' => 'Jamel',
                'last_name' => 'Floyd',
                'description' => 'Jamel Floyd was a 35-year-old African American man killed on June 3, 2020 by Bureau of Prisons guards at the Metropolitan Detention Center (MDC) Brooklyn during the early days of the George Floyd uprising. Held at MDC while serving a state sentence for a 2007 Long Island home invasion, Floyd had been transferred to the federal facility in October 2019. After a disturbance in his cell, BOP staff deployed pepper spray into the confined space; Floyd became unresponsive shortly after being placed in restraints and died. His family filed a wrongful-death lawsuit alleging excessive force and deliberate indifference. His death, in the same week as George Floyd\'s murder in Minneapolis, became a rallying cry against carceral violence. NYC Anarchist Black Cross commemorates his birthday (September 15) annually as part of its political prisoner memorial work.',
                'race' => 'Black',
                'gender' => 'Male',
                'state' => 'New York',
                'death_date' => '2020-06-03',
                'era' => '2020s',
                'affiliation' => ['Memorial / Killed in Custody'],
                'in_custody' => false,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Metropolitan Detention Center, Brooklyn',
                    'institution_city' => 'Brooklyn',
                    'institution_state' => 'New York',
                    'charges' => 'Held on state firearm/home invasion sentence; transferred to MDC Brooklyn October 2019. Died in federal custody June 3, 2020 after BOP guards pepper-sprayed him in his cell.',
                    'sentence' => '12 to 15 years (state) — died in custody before completion',
                    'death_in_custody_date' => '2020-06-03',
                ]],
            ],
            [
                'name' => 'Priscilla Grim',
                'first_name' => 'Priscilla',
                'last_name' => 'Grim',
                'description' => 'Priscilla Grim is a long-time cultural worker, media organizer, and journalist based in New York, indicted in August 2023 as one of 61 defendants in Georgia\'s sweeping RICO case against Stop Cop City / Defend the Atlanta Forest activists — the largest racketeering case ever filed against protesters in U.S. history. A co-founder of the Occupy Wall Street zine "Tidal" and other independent media projects, Grim traveled to Atlanta in March 2023 for the Stop Cop City Week of Action, a gathering that included a music festival, arts workshops, and a free clinic in the Weelaunee Forest. She was arrested on March 5, 2023 in a mass sweep and initially charged with domestic terrorism before being swept into the August 2023 RICO indictment by Georgia AG Chris Carr. Following the indictment she lost her job, had her Airbnb account terminated, and had her Chase bank account closed. As of 2025-2026 she remains awaiting trial along with most of the Cop City RICO 61. She has filed for a speedy trial. Running Down The Walls 2025 recognized her as a political prisoner.',
                'race' => 'White',
                'gender' => 'Female',
                'state' => 'Georgia',
                'era' => '2020s',
                'ideologies' => ['Anti-fascism', 'Forest Defense', 'Stop Cop City'],
                'affiliation' => ['Stop Cop City', 'Atlanta Forest Defenders'],
                'website' => 'https://supportpriscilla.org',
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'cases' => [[
                    'institution_state' => 'Georgia',
                    'charges' => 'Domestic terrorism (Georgia); RICO conspiracy under Georgia\'s Racketeer Influenced and Corrupt Organizations Act as one of the Stop Cop City RICO 61',
                    'arrest_date' => '2023-03-05',
                    'prosecutor' => 'Georgia Attorney General Chris Carr',
                ]],
            ],
            [
                'name' => 'Jakhi McCray',
                'first_name' => 'Jakhi',
                'last_name' => 'McCray',
                'description' => 'Jakhi McCray (also Jakhi Lodgson-McCray) is a young Black organizer from Brooklyn, New York, active since October 2023 in Palestine solidarity and anti-ICE organizing — arrested more than 11 times at protests in that period. On June 12, 2025 he scaled a fence into a secure NYPD parking lot on DeKalb Avenue in Bushwick and set fire to ten NYPD vehicles and a trailer, causing roughly $800,000 in damage. After a federal manhunt he turned himself in on July 21, 2025, represented by the Federal Defenders of New York and attorney Ron Kuby. In April 2026 he pleaded guilty in Brooklyn federal court to arson; he faces a mandatory minimum of five years and up to twenty years in prison. He is supported by the Jakhi McCray Support Committee and recognized as a political prisoner by NYC Anarchist Black Cross.',
                'race' => 'Black',
                'gender' => 'Male',
                'state' => 'New York',
                'era' => '2020s',
                'ideologies' => ['Anti-imperialism', 'Pro-Palestine', 'Anti-police'],
                'affiliation' => ['Jakhi McCray Support Committee', 'Palestine solidarity movement'],
                'website' => 'https://jakhisolidarity.noblogs.org',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_city' => 'Brooklyn',
                    'institution_state' => 'New York',
                    'charges' => 'Federal arson (18 U.S.C. § 844) — setting fire to 10 NYPD vehicles and a trailer in a Bushwick parking lot on June 12, 2025, causing approximately $800,000 in damage. Pleaded guilty April 2026.',
                    'arrest_date' => '2025-07-21',
                    'sentence' => 'Awaiting sentencing; mandatory minimum 5 years, up to 20 years',
                ]],
            ],

            // ---------- Prairieland Defendants (13) ----------
            [
                'name' => 'Autumn Hill',
                'aka' => 'Cameron Arnold',
                'first_name' => 'Autumn',
                'last_name' => 'Hill',
                'description' => 'One of the Prairieland Defendants prosecuted federally over the July 4, 2025 noise demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Found guilty at trial on March 13, 2026 of riot, providing material support to terrorists, and use/carry of explosives during a riot; acquitted on three counts of attempted murder. Faces 10 to 60 years at sentencing.',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'FMC Fort Worth',
                    'institution_city' => 'Fort Worth',
                    'institution_state' => 'Texas',
                    'charges' => 'Riot; providing material support to terrorists (18 U.S.C. § 2339A); conspiracy regarding explosives; use/carry of explosives during a riot; attempted murder of a federal officer (acquitted, 3 counts)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
            [
                'name' => 'Benjamin Song',
                'aka' => 'Champagne',
                'first_name' => 'Benjamin',
                'last_name' => 'Song',
                'description' => 'Identified by federal prosecutors as the gunman in the July 4, 2025 incident at the Prairieland ICE Detention Center in Alvarado, Texas, who allegedly fired a rifle and struck an Alvarado police lieutenant in the neck. Convicted on March 13, 2026 of attempted murder of an officer, riot, providing material support to terrorists, use of explosives, and multiple counts of discharging a firearm during a crime of violence. Faces a mandatory minimum of 20 years and up to life in federal prison.',
                'gender' => 'Male',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Federal Custody (pre-sentencing)',
                    'institution_state' => 'Texas',
                    'charges' => 'Attempted murder of a federal officer; riot; providing material support to terrorists (18 U.S.C. § 2339A); use/carry of explosives during a riot; discharging a firearm during a crime of violence (3 counts); discharging a firearm (2 counts)',
                    'arrest_date' => '2025-07-15',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
            [
                'name' => 'Daniel Rolando Sanchez Estrada',
                'aka' => 'Des',
                'first_name' => 'Daniel',
                'middle_name' => 'Rolando',
                'last_name' => 'Sanchez Estrada',
                'description' => 'A Prairieland Defendant accused of helping conceal digital evidence following the July 4, 2025 demonstration at the Prairieland ICE Detention Center in Alvarado, Texas; he is not alleged to have participated in the protest itself. Convicted at trial on March 13, 2026 of corruptly concealing a document and conspiracy to conceal documents, facing up to 40 years at sentencing.',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Federal Custody (pre-sentencing)',
                    'institution_state' => 'Texas',
                    'charges' => 'Corruptly concealing a document (18 U.S.C. § 1512(c)(1)); conspiracy to conceal documents',
                    'arrest_date' => '2025-07-15',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
            [
                'name' => 'Dario Sanchez',
                'first_name' => 'Dario',
                'last_name' => 'Sanchez',
                'description' => 'A Prairieland Defendant from the Dallas area accused of tampering with evidence by removing other defendants from Signal and Discord group chats after the July 4, 2025 Prairieland ICE Detention Center demonstration; he is not alleged to have participated in the protest itself. He rejected a state offer of immunity in exchange for testifying against co-defendant Janette Goering.',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'awaiting_trial' => true,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Johnson County Jail',
                    'institution_city' => 'Cleburne',
                    'institution_state' => 'Texas',
                    'charges' => 'Hindering the Prosecution of Terrorism (Texas state); tampering with evidence',
                    'arrest_date' => '2025-07-15',
                ]],
            ],
            [
                'name' => 'Elizabeth Soto',
                'first_name' => 'Elizabeth',
                'last_name' => 'Soto',
                'description' => 'One of the Prairieland Defendants prosecuted federally over the July 4, 2025 noise demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Found guilty at trial on March 13, 2026 of riot, providing material support to terrorists, and use/carry of explosives during a riot; acquitted on attempted murder counts. Faces 10 to 60 years at sentencing.',
                'gender' => 'Female',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Federal Custody (pre-sentencing)',
                    'institution_state' => 'Texas',
                    'charges' => 'Riot; providing material support to terrorists (18 U.S.C. § 2339A); use/carry of explosives during a riot; attempted murder of a federal officer (acquitted)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
            [
                'name' => 'Ines Soto',
                'first_name' => 'Ines',
                'last_name' => 'Soto',
                'description' => 'One of the Prairieland Defendants prosecuted federally over the July 4, 2025 noise demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Found guilty at trial on March 13, 2026 of riot, providing material support to terrorists, and use/carry of explosives during a riot; acquitted on attempted murder counts. Faces 10 to 60 years at sentencing.',
                'gender' => 'Female',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Federal Custody (pre-sentencing)',
                    'institution_state' => 'Texas',
                    'charges' => 'Riot; providing material support to terrorists (18 U.S.C. § 2339A); use/carry of explosives during a riot; attempted murder of a federal officer (acquitted)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
            [
                'name' => 'Janette Goering',
                'first_name' => 'Janette',
                'last_name' => 'Goering',
                'description' => 'A Prairieland Defendant charged with aiding in the commission of terrorism for allegedly providing a Faraday signal-blocking bag to another defendant weeks before the July 4, 2025 Prairieland ICE Detention Center demonstration; she is not alleged to have participated in the protest itself.',
                'gender' => 'Female',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'awaiting_trial' => true,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Johnson County Jail',
                    'institution_city' => 'Cleburne',
                    'institution_state' => 'Texas',
                    'charges' => 'Aiding in the commission of terrorism (Texas state)',
                    'arrest_date' => '2025-07-15',
                ]],
            ],
            [
                'name' => 'Joy Gibson',
                'aka' => 'Rowan',
                'first_name' => 'Joy',
                'last_name' => 'Gibson',
                'description' => 'A Prairieland Defendant who signed a non-cooperating guilty plea in November 2025 to providing material support to terrorists arising from the July 4, 2025 demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Faces up to 15 years in federal prison at sentencing.',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Wichita County Detention Center',
                    'institution_city' => 'Wichita Falls',
                    'institution_state' => 'Texas',
                    'charges' => 'Providing material support to terrorists (18 U.S.C. § 2339A)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — pleaded guilty November 2025 (non-cooperating plea)',
                ]],
            ],
            [
                'name' => 'Lucy Fowlkes',
                'first_name' => 'Lucy',
                'last_name' => 'Fowlkes',
                'description' => 'A Prairieland Defendant from Weatherford, Texas, arrested in January 2026 and charged in Johnson County with Hindering the Prosecution of Terrorism for allegedly helping delete messages and remove people from group chats after the July 4, 2025 Prairieland ICE Detention Center demonstration; bond was set at $5 million. She is not alleged to have participated in the protest itself.',
                'gender' => 'Female',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'awaiting_trial' => true,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Johnson County Jail',
                    'institution_city' => 'Cleburne',
                    'institution_state' => 'Texas',
                    'charges' => 'Hindering the Prosecution of Terrorism (Texas state)',
                    'arrest_date' => '2026-01-06',
                ]],
            ],
            [
                'name' => 'Maricela Rueda',
                'first_name' => 'Maricela',
                'last_name' => 'Rueda',
                'description' => 'One of the Prairieland Defendants prosecuted federally over the July 4, 2025 noise demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Found guilty at trial on March 13, 2026 of riot, providing material support to terrorists, use/carry of explosives during a riot, and conspiracy to conceal documents; acquitted on attempted murder counts. Faces 10 to 60 years at sentencing.',
                'gender' => 'Female',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Wichita County Detention Center',
                    'institution_city' => 'Wichita Falls',
                    'institution_state' => 'Texas',
                    'charges' => 'Riot; providing material support to terrorists (18 U.S.C. § 2339A); use/carry of explosives during a riot; conspiracy to conceal documents; attempted murder of a federal officer (acquitted)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
            [
                'name' => 'Rebecca Morgan',
                'first_name' => 'Rebecca',
                'last_name' => 'Morgan',
                'description' => 'A Prairieland Defendant who signed a non-cooperating guilty plea in November 2025 to providing material support to terrorists arising from the July 4, 2025 demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Faces up to 15 years in federal prison at sentencing.',
                'gender' => 'Female',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Wichita County Detention Center',
                    'institution_city' => 'Wichita Falls',
                    'institution_state' => 'Texas',
                    'charges' => 'Providing material support to terrorists (18 U.S.C. § 2339A)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — pleaded guilty November 2025 (non-cooperating plea)',
                ]],
            ],
            [
                'name' => 'Savanna Batten',
                'first_name' => 'Savanna',
                'last_name' => 'Batten',
                'description' => 'One of the Prairieland Defendants prosecuted federally over the July 4, 2025 noise demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Found guilty at trial on March 13, 2026 of riot, providing material support to terrorists, and use/carry of explosives during a riot; acquitted on three counts of attempted murder. Faces 10 to 60 years at sentencing.',
                'gender' => 'Female',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'Federal Custody (pre-sentencing)',
                    'institution_state' => 'Texas',
                    'charges' => 'Riot; providing material support to terrorists (18 U.S.C. § 2339A); use/carry of explosives during a riot; attempted murder of a federal officer (acquitted, 3 counts)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
            [
                'name' => 'Zachary Evetts',
                'first_name' => 'Zachary',
                'last_name' => 'Evetts',
                'description' => 'One of the Prairieland Defendants prosecuted federally over the July 4, 2025 noise demonstration at the Prairieland ICE Detention Center in Alvarado, Texas. Found guilty at trial on March 13, 2026 of riot, providing material support to terrorists, and use/carry of explosives during a riot; acquitted on three counts of attempted murder. Faces 10 to 60 years at sentencing.',
                'gender' => 'Male',
                'state' => 'Texas',
                'ideologies' => $prairielandIdeo,
                'affiliation' => $prairieland,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => $pgWebsite,
                'cases' => [[
                    'institution_name' => 'FMC Fort Worth',
                    'institution_city' => 'Fort Worth',
                    'institution_state' => 'Texas',
                    'charges' => 'Riot; providing material support to terrorists (18 U.S.C. § 2339A); use/carry of explosives during a riot; attempted murder of a federal officer (acquitted, 3 counts)',
                    'arrest_date' => '2025-07-04',
                    'convicted' => 'Yes — found guilty at trial March 13, 2026',
                    'judge' => 'Mark T. Pittman',
                ]],
            ],
        ];
    }
}
