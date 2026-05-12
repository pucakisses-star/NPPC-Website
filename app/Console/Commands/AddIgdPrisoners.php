<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds (or updates) prisoners surfaced from It's Going Down's "In Contempt"
 * column and adjacent coverage:
 *
 *   - 17 federal/state defendants from the 2020 George Floyd Uprising
 *   - 7 contemporary anarchist / animal-liberation cases
 *   - 4 Merrimack 4 Palestine-solidarity defendants
 *   - 3 Stop Cop City "ATL61" RICO defendants
 *   - 1 long-term Black liberation prisoner not yet in NPPC (Oso Blanco)
 *   - 5 long-term Black liberation prisoners already in NPPC — refresh
 *     fields (inmate #, facility hints, status notes) without overwriting
 *     existing description (appended instead).
 *
 * Idempotent: prisoner:add refuses duplicates by name, and updates only
 * fill empty fields or append to description.
 */
final class AddIgdPrisoners extends Command {
    protected $signature = 'archive:add-igd-prisoners';
    protected $description = 'Add/update prisoners surfaced from itsgoingdown.org';

    public function handle(): int {
        $added = 0;
        $skippedAdds = 0;
        $updated = 0;
        $missing = 0;

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

        foreach ($this->updates() as $spec) {
            $name = $spec['name'];
            $prisoner = Prisoner::where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("UPDATE skipped — not found: {$name}");
                $missing++;

                continue;
            }
            $append = $spec['fields']['description_append'] ?? null;
            $fields = $spec['fields'];
            unset($fields['description_append']);

            foreach ($fields as $col => $val) {
                if ($val === null || $val === '') {
                    continue;
                }
                $current = $prisoner->{$col};
                if ($current === null || $current === '' || $current === [] || $current === false) {
                    $prisoner->{$col} = $val;
                }
            }
            if ($append) {
                $existing = trim((string) $prisoner->description);
                if (! str_contains($existing, $append)) {
                    $prisoner->description = $existing === '' ? $append : $existing."\n\n".$append;
                }
            }
            $prisoner->save();
            $this->info("UPDATE: {$name}");
            $updated++;
        }

        $this->info("\nDone. Added={$added} SkippedAdds={$skippedAdds} Updated={$updated} Missing={$missing}");

        return self::SUCCESS;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function additions(): array {
        $gfU = ['George Floyd Uprising'];

        return [
            // ---------- 2020 George Floyd Uprising (17) ----------
            [
                'name' => 'Malik Muhammad',
                'first_name' => 'Malik',
                'last_name' => 'Muhammad',
                'description' => 'Malik Muhammad is a Black anarchist organizer from Portland, Oregon, prosecuted for actions during the 2020 George Floyd Uprising. He was convicted in Multnomah County of arson and related charges stemming from protests in summer 2020 and sentenced to a lengthy state prison term. In early 2025 he was transferred from Oregon State Penitentiary to Snake River Correctional Institution after conducting a nine-day hunger strike protesting his conditions of confinement.',
                'state' => 'Oregon',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', 'Anarchism', '2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'website' => 'https://malikspeaks.noblogs.org',
                'inmate_number' => '23935744',
                'cases' => [[
                    'institution_name' => 'Snake River Correctional Institution',
                    'institution_city' => 'Ontario',
                    'institution_state' => 'Oregon',
                    'charges' => 'Arson and related offenses arising from 2020 George Floyd Uprising protests in Portland',
                    'convicted' => 'Yes — state arson conviction',
                ]],
            ],
            [
                'name' => 'Montez Lee',
                'first_name' => 'Montez',
                'last_name' => 'Lee',
                'description' => 'Montez Lee is a Black man from Minneapolis prosecuted federally for setting fire to the Max It Pawn shop on Lake Street during the May 2020 George Floyd Uprising. Although a body was later discovered in the building, prosecutors did not charge him with the death and acknowledged he had no knowledge of anyone inside. He pleaded guilty to one count of arson and was sentenced in 2022 to 10 years in federal prison.',
                'state' => 'Minnesota',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', '2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '22429-041',
                'cases' => [[
                    'institution_name' => 'FCI Ray Brook',
                    'institution_city' => 'Ray Brook',
                    'institution_state' => 'New York',
                    'charges' => 'Federal arson (18 U.S.C. § 844(i)) for the burning of Max It Pawn during the Minneapolis George Floyd Uprising',
                    'convicted' => 'Yes — pleaded guilty to arson',
                    'sentence' => '10 years federal prison',
                ]],
            ],
            [
                'name' => 'Margaret Channon',
                'first_name' => 'Margaret',
                'last_name' => 'Channon',
                'description' => 'Margaret Channon was prosecuted federally for setting fire to five Seattle Police Department vehicles during the May 30, 2020 George Floyd Uprising protests in downtown Seattle. She pleaded guilty to multiple counts of arson and was sentenced in 2022 to approximately seven years in federal prison. She is held at FCI Tallahassee.',
                'state' => 'Washington',
                'gender' => 'Female',
                'ideologies' => ['Anti-police', '2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '49955-086',
                'cases' => [[
                    'institution_name' => 'FCI Tallahassee',
                    'institution_city' => 'Tallahassee',
                    'institution_state' => 'Florida',
                    'charges' => 'Federal arson for burning five SPD police vehicles in Seattle on May 30, 2020',
                    'arrest_date' => '2020-06-10',
                    'convicted' => 'Yes — pleaded guilty to arson',
                    'sentence' => 'Approximately 7 years federal prison',
                ]],
            ],
            [
                'name' => 'Matthew Rupert',
                'first_name' => 'Matthew',
                'last_name' => 'Rupert',
                'description' => 'Matthew Rupert traveled from Galesburg, Illinois to Minneapolis during the George Floyd Uprising in late May 2020, where he livestreamed himself distributing explosive devices and participating in the burning of a Sprint store. He was charged federally with civil disorder, possession of unregistered destructive devices, and arson. He was convicted and sentenced to approximately seven years in federal prison.',
                'state' => 'Illinois',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '55013-424',
                'cases' => [[
                    'institution_name' => 'USP Big Sandy',
                    'institution_city' => 'Inez',
                    'institution_state' => 'Kentucky',
                    'charges' => 'Federal arson, civil disorder, possession of unregistered destructive devices in Minneapolis during the George Floyd Uprising',
                    'arrest_date' => '2020-05-30',
                    'convicted' => 'Yes — pleaded guilty',
                    'sentence' => 'Approximately 7 years federal prison',
                ]],
            ],
            [
                'name' => 'Matthew White',
                'first_name' => 'Matthew',
                'last_name' => 'White',
                'description' => 'Matthew White was prosecuted federally for arson committed during the 2020 George Floyd Uprising. He is currently held at USP Terre Haute serving a federal sentence.',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '21434-041',
                'cases' => [[
                    'institution_name' => 'USP Terre Haute',
                    'institution_city' => 'Terre Haute',
                    'institution_state' => 'Indiana',
                    'charges' => 'Federal arson arising from 2020 George Floyd Uprising',
                    'convicted' => 'Yes — federal arson conviction',
                ]],
            ],
            [
                'name' => 'José Felan',
                'first_name' => 'José',
                'last_name' => 'Felan',
                'description' => 'José Felan was federally charged with setting fires to a Goodwill store, a Minneapolis Public Schools building, and a Gordon Parks High School during the May 2020 George Floyd Uprising in St. Paul and Minneapolis. After his indictment he fled to Mexico but was apprehended and extradited back to the United States. He was convicted on multiple arson counts and sentenced to approximately ten years in federal prison.',
                'state' => 'Minnesota',
                'race' => 'Latino',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '54146-380',
                'cases' => [[
                    'institution_name' => 'FCI Terre Haute',
                    'institution_city' => 'Terre Haute',
                    'institution_state' => 'Indiana',
                    'charges' => 'Federal arson of a Goodwill, Minneapolis Public Schools building, and Gordon Parks High School during the George Floyd Uprising; unlawful flight to avoid prosecution',
                    'convicted' => 'Yes — pleaded guilty to arson',
                    'sentence' => 'Approximately 10 years federal prison',
                ]],
            ],
            [
                'name' => 'David Elmakayes',
                'first_name' => 'David',
                'last_name' => 'Elmakayes',
                'description' => 'David Elmakayes was federally prosecuted for arson committed during the George Floyd Uprising protests in Philadelphia in late May and early June 2020, including the burning of a police vehicle. He pleaded guilty to federal arson and was sentenced to approximately ten years in federal prison. He is held at FCI McKean.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising', 'Anti-police'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '77782-066',
                'cases' => [[
                    'institution_name' => 'FCI McKean',
                    'institution_city' => 'Lewis Run',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Federal arson of a police vehicle during the 2020 George Floyd Uprising in Philadelphia',
                    'convicted' => 'Yes — pleaded guilty to arson',
                    'sentence' => 'Approximately 10 years federal prison',
                ]],
            ],
            [
                'name' => 'Andrew Duncan-Augustyniak',
                'first_name' => 'Andrew',
                'last_name' => 'Duncan-Augustyniak',
                'description' => 'Andrew Duncan-Augustyniak was prosecuted in Pennsylvania state court for arson and related charges arising from the George Floyd Uprising protests in Pittsburgh on May 30, 2020. He is serving a state prison sentence at SCI Rockview.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => 'QN9211',
                'cases' => [[
                    'institution_name' => 'SCI Rockview',
                    'institution_city' => 'Bellefonte',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'State arson and related offenses during the 2020 George Floyd Uprising in Pittsburgh',
                    'convicted' => 'Yes — state arson conviction',
                ]],
            ],
            [
                'name' => 'Khalif Miller',
                'first_name' => 'Khalif',
                'last_name' => 'Miller',
                'description' => 'Khalif Miller was federally prosecuted for arson committed during the George Floyd Uprising in Philadelphia in 2020. After completing his federal sentence he was transferred to Pennsylvania state custody on a state parole hold and is currently held at SCI Camp Hill.',
                'state' => 'Pennsylvania',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', '2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => 'QQ9287',
                'cases' => [[
                    'institution_name' => 'SCI Camp Hill',
                    'institution_city' => 'Camp Hill',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Federal arson during the 2020 George Floyd Uprising in Philadelphia; subsequent state parole hold',
                    'convicted' => 'Yes — federal arson conviction',
                ]],
            ],
            [
                'name' => 'Alvin Joseph',
                'first_name' => 'Alvin',
                'last_name' => 'Joseph',
                'description' => 'Alvin Joseph was prosecuted in Georgia state court for arson of Atlanta Police Department vehicles during the George Floyd Uprising protests in Atlanta in late May 2020. He is serving a state sentence at Hays State Prison.',
                'state' => 'Georgia',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', '2020 Uprising', 'Anti-police'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '1002016959',
                'cases' => [[
                    'institution_name' => 'Hays State Prison',
                    'institution_city' => 'Trion',
                    'institution_state' => 'Georgia',
                    'charges' => 'State arson of police vehicles during the 2020 George Floyd Uprising in Atlanta',
                    'convicted' => 'Yes — state arson conviction',
                ]],
            ],
            [
                'name' => 'John Wade',
                'first_name' => 'John',
                'last_name' => 'Wade',
                'description' => 'John Wade was prosecuted in Georgia state court for offenses related to the Atlanta George Floyd Uprising protests in 2020. He is currently held at the Georgia Diagnostic and Classification Prison in Jackson, where he recently conducted a hunger strike protesting his conditions of confinement.',
                'state' => 'Georgia',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '1003510744',
                'cases' => [[
                    'institution_name' => 'Georgia Diagnostic and Classification Prison',
                    'institution_city' => 'Jackson',
                    'institution_state' => 'Georgia',
                    'charges' => 'State charges arising from the 2020 George Floyd Uprising in Atlanta',
                    'convicted' => 'Yes — state conviction',
                ]],
            ],
            [
                'name' => 'Diego Vargas',
                'first_name' => 'Diego',
                'last_name' => 'Vargas',
                'description' => 'Diego Vargas was federally prosecuted for arson and related offenses during the 2020 George Floyd Uprising. He is serving his federal sentence at FCI Schuylkill.',
                'race' => 'Latino',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '55070-424',
                'cases' => [[
                    'institution_name' => 'FCI Schuylkill',
                    'institution_city' => 'Minersville',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Federal arson arising from the 2020 George Floyd Uprising',
                    'convicted' => 'Yes — federal arson conviction',
                ]],
            ],
            [
                'name' => 'Aline Espinosa-Villegas',
                'first_name' => 'Aline',
                'last_name' => 'Espinosa-Villegas',
                'aka' => 'Ángel',
                'description' => 'Aline "Ángel" Espinosa-Villegas is a trans woman federally prosecuted for arson committed during the George Floyd Uprising protests in Little Rock, Arkansas in 2020. She pleaded guilty to federal arson and is serving her sentence at FMC Carswell. She is expected to face ICE detention upon release due to her immigration status.',
                'state' => 'Arkansas',
                'race' => 'Latina',
                'gender' => 'Female',
                'ideologies' => ['Trans Liberation', '2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '22814-509',
                'cases' => [[
                    'institution_name' => 'FMC Carswell',
                    'institution_city' => 'Fort Worth',
                    'institution_state' => 'Texas',
                    'charges' => 'Federal arson during the 2020 George Floyd Uprising in Little Rock, Arkansas',
                    'convicted' => 'Yes — pleaded guilty to arson',
                ]],
            ],
            [
                'name' => "Mujera Benjamin Lunga'ho",
                'first_name' => 'Mujera',
                'last_name' => "Lunga'ho",
                'description' => "Mujera Benjamin Lunga'ho was federally prosecuted as a co-defendant of Aline Espinosa-Villegas for arson committed during the George Floyd Uprising protests in Little Rock, Arkansas in 2020. He pleaded guilty to federal arson and is serving his sentence at FCI Forrest City.",
                'state' => 'Arkansas',
                'race' => 'Black',
                'gender' => 'Male',
                'ideologies' => ['Black Liberation', '2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '08572-509',
                'cases' => [[
                    'institution_name' => 'FCI Forrest City',
                    'institution_city' => 'Forrest City',
                    'institution_state' => 'Arkansas',
                    'charges' => 'Federal arson during the 2020 George Floyd Uprising in Little Rock, Arkansas',
                    'convicted' => 'Yes — pleaded guilty to arson',
                ]],
            ],
            [
                'name' => 'Christopher Tindal',
                'first_name' => 'Christopher',
                'last_name' => 'Tindal',
                'description' => 'Christopher Tindal was federally prosecuted for offenses arising from the 2020 George Floyd Uprising. He is serving his federal sentence at FCI Cumberland.',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '04392-509',
                'cases' => [[
                    'institution_name' => 'FCI Cumberland',
                    'institution_city' => 'Cumberland',
                    'institution_state' => 'Maryland',
                    'charges' => 'Federal charges arising from the 2020 George Floyd Uprising',
                    'convicted' => 'Yes — federal conviction',
                ]],
            ],
            [
                'name' => 'Cyan Bass',
                'first_name' => 'Cyan',
                'last_name' => 'Bass',
                'description' => 'Cyan Bass was prosecuted for offenses arising from the George Floyd Uprising protests in Minneapolis in 2020. After serving their sentence, they were released from custody on December 1, 2024.',
                'state' => 'Minnesota',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_city' => 'Minneapolis',
                    'institution_state' => 'Minnesota',
                    'charges' => 'Charges arising from the 2020 George Floyd Uprising in Minneapolis',
                    'release_date' => '2024-12-01',
                    'convicted' => 'Yes',
                ]],
            ],
            [
                'name' => 'Richard Hunsinger',
                'first_name' => 'Richard',
                'last_name' => 'Hunsinger',
                'description' => 'Richard Hunsinger was prosecuted in Georgia for offenses arising from the 2020 George Floyd Uprising protests in Atlanta. He was released from custody in late November 2024.',
                'state' => 'Georgia',
                'gender' => 'Male',
                'ideologies' => ['2020 Uprising'],
                'affiliation' => $gfU,
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_state' => 'Georgia',
                    'charges' => 'Charges arising from the 2020 George Floyd Uprising in Atlanta',
                    'release_date' => '2024-11-25',
                    'convicted' => 'Yes',
                ]],
            ],

            // ---------- Contemporary anarchist / animal liberation (8) ----------
            [
                'name' => 'Casey Goonan',
                'first_name' => 'Casey',
                'last_name' => 'Goonan',
                'description' => 'Anti-imperialist activist arrested in 2024 for a series of arson actions targeting University of California, Berkeley buildings in solidarity with Palestine, protesting UC ties to Israeli weapons research and the genocide in Gaza. Took a non-cooperating plea agreement in April 2025. A diabetic prisoner, Goonan has participated in hunger strikes in solidarity with other political prisoners.',
                'state' => 'California',
                'gender' => 'Male',
                'ideologies' => ['Anti-imperialism', 'Anarchism', 'Pro-Palestine'],
                'affiliation' => ['Anarchist Black Cross'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => 'UMF227',
                'cases' => [[
                    'institution_name' => 'Santa Rita Jail',
                    'institution_city' => 'Dublin',
                    'institution_state' => 'California',
                    'charges' => 'Arson, use of destructive device, possession of unregistered destructive device (federal); related state charges for arson targeting UC Berkeley buildings tied to Israeli weapons research and military contracts',
                    'arrest_date' => '2024-06-09',
                    'convicted' => 'Yes — non-cooperating plea, April 2025',
                ]],
            ],
            [
                'name' => 'Dan Baker',
                'first_name' => 'Dan',
                'last_name' => 'Baker',
                'description' => 'Anarchist and antifascist veteran prosecuted federally for online posts in January 2021 calling for armed counter-mobilization against far-right militias in the wake of the January 6 Capitol attack. Charged with transmitting threats in interstate commerce, he was sentenced in October 2021 to 44 months in federal prison. His case has been widely criticized by civil liberties advocates as a politically motivated prosecution targeting anarchist speech.',
                'state' => 'Florida',
                'gender' => 'Male',
                'ideologies' => ['Anarchism', 'Anti-fascism'],
                'affiliation' => ['Anarchist Black Cross'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://itsgoingdown.org/anarchist-political-prisoner-dan-baker-needs-support/',
                'cases' => [[
                    'charges' => 'Transmitting a communication containing a threat to kidnap or injure another person (18 U.S.C. § 875(c)); transmitting in interstate commerce a communication containing a threat to injure',
                    'arrest_date' => '2021-01-15',
                    'convicted' => 'Yes — found guilty at trial, 2021',
                    'sentence' => '44 months federal prison plus 3 years supervised release',
                ]],
            ],
            [
                'name' => 'Daniel Andreas San Diego',
                'first_name' => 'Daniel',
                'last_name' => 'San Diego',
                'description' => 'Animal liberation activist accused of carrying out two 2003 bombings in the San Francisco Bay Area against Chiron Corporation and Shaklee Corporation, biotech and consumer-products firms linked to Huntingdon Life Sciences animal testing. He fled before federal charges were filed and spent two decades on the FBI Most Wanted Terrorists list — the first domestic animal-rights activist added to the list. He was arrested in rural Wales in November 2024 and is fighting extradition to the United States.',
                'state' => 'California',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1978-02-09',
                'ideologies' => ['Animal Liberation', 'Anarchism'],
                'affiliation' => ['Revolutionary Cells — Animal Liberation Brigade'],
                'era' => '2000s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'charges' => 'Use of a destructive device during a crime of violence; malicious destruction of property by means of explosives (two counts) — for the August 28, 2003 bombing of Chiron Corp. in Emeryville, CA and the September 26, 2003 bombing of Shaklee Corp. in Pleasanton, CA',
                    'arrest_date' => '2024-11-25',
                ]],
            ],
            [
                'name' => 'Brian DiPippa',
                'first_name' => 'Brian',
                'last_name' => 'DiPippa',
                'aka' => 'Peppy',
                'description' => 'Pittsburgh-area antifascist activist convicted federally for throwing a smoke bomb / improvised device at police during an April 2023 protest outside a Pittsburgh event featuring an anti-LGBTQ+ speaker. He pleaded guilty in 2024 to civil disorder and explosives-related charges and was sentenced to approximately 60 months in federal prison followed by 3 years of supervised release.',
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism', 'LGBTQ+ Liberation'],
                'affiliation' => ['Pittsburgh Antifascists'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '66590-510',
                'cases' => [[
                    'institution_name' => 'FCI Elkton',
                    'institution_city' => 'Lisbon',
                    'institution_state' => 'Ohio',
                    'charges' => 'Civil disorder; possession of an unregistered destructive device, related to deploying a smoke/explosive device at an April 2023 anti-LGBTQ+ event protest in Pittsburgh',
                    'arrest_date' => '2023-04-02',
                    'convicted' => 'Yes — guilty plea, 2024',
                    'sentence' => 'Approximately 60 months federal prison plus 3 years supervised release',
                ]],
            ],
            [
                'name' => 'Krystal DiPippa',
                'first_name' => 'Krystal',
                'last_name' => 'DiPippa',
                'description' => 'Co-defendant and partner of Brian DiPippa in the April 2023 Pittsburgh anti-LGBTQ+ event protest case. She pleaded guilty to federal charges related to the protest and received a shorter sentence than her co-defendant.',
                'state' => 'Pennsylvania',
                'gender' => 'Female',
                'ideologies' => ['Anti-fascism', 'LGBTQ+ Liberation'],
                'affiliation' => ['Pittsburgh Antifascists'],
                'era' => '2020s',
                'cases' => [[
                    'charges' => 'Civil disorder and related federal charges stemming from the April 2023 Pittsburgh anti-LGBTQ+ event protest',
                    'arrest_date' => '2023-04-02',
                    'convicted' => 'Yes — guilty plea, 2024',
                ]],
            ],
            [
                'name' => 'Gabriella Oropesa',
                'first_name' => 'Gabriella',
                'last_name' => 'Oropesa',
                'description' => 'Reproductive justice activist found guilty in August 2025 of federal "conspiracy against rights" charges (18 U.S.C. § 241) for participating in actions against anti-abortion "crisis pregnancy centers" in Florida. The prosecution is part of a broader federal pattern of using conspiracy-against-rights statutes against abortion-rights activists who target CPCs.',
                'state' => 'Florida',
                'gender' => 'Female',
                'ideologies' => ['Reproductive Justice', 'Anarchism'],
                'era' => '2020s',
                'cases' => [[
                    'charges' => 'Conspiracy against rights (18 U.S.C. § 241) related to actions against anti-abortion crisis pregnancy centers in Florida',
                    'convicted' => 'Yes — found guilty at trial, August 2025',
                ]],
            ],
            [
                'name' => 'Cara Tobe',
                'first_name' => 'Cara',
                'last_name' => 'Tobe',
                'description' => 'One half of the "Northumberland 2," charged in connection with a mink liberation action at a fur farm in Northumberland County, Pennsylvania. Out on bail awaiting trial as of 2025, with an April 2025 court date in Sunbury, PA.',
                'state' => 'Pennsylvania',
                'ideologies' => ['Animal Liberation'],
                'affiliation' => ['Northumberland 2'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'cases' => [[
                    'institution_city' => 'Sunbury',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Felony charges related to a mink liberation action at a Pennsylvania fur farm',
                ]],
            ],
            [
                'name' => 'Celeste Friend',
                'first_name' => 'Celeste',
                'last_name' => 'Friend',
                'description' => 'One half of the "Northumberland 2," charged alongside Cara Tobe in connection with a mink liberation action at a fur farm in Northumberland County, Pennsylvania. Out on bail awaiting trial as of 2025, with an April 2025 court date in Sunbury, PA.',
                'state' => 'Pennsylvania',
                'ideologies' => ['Animal Liberation'],
                'affiliation' => ['Northumberland 2'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'cases' => [[
                    'institution_city' => 'Sunbury',
                    'institution_state' => 'Pennsylvania',
                    'charges' => 'Felony charges related to a mink liberation action at a Pennsylvania fur farm',
                ]],
            ],

            // ---------- Merrimack 4 (4) ----------
            [
                'name' => 'Calla Walsh',
                'first_name' => 'Calla',
                'last_name' => 'Walsh',
                'description' => 'One of the "Merrimack 4," a group of Palestine solidarity activists who took action in November 2023 against the Merrimack, New Hampshire facility of Elbit Systems of America, the U.S. subsidiary of Israel\'s largest weapons manufacturer. Convicted in late 2024 of misdemeanor charges and released in late December 2024 after serving a brief jail sentence.',
                'state' => 'New Hampshire',
                'gender' => 'Female',
                'ideologies' => ['Pro-Palestine', 'Anti-imperialism'],
                'affiliation' => ['Merrimack 4', 'Palestine Action US'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_city' => 'Merrimack',
                    'institution_state' => 'New Hampshire',
                    'charges' => 'Riot, criminal mischief, and related charges for a November 2023 action at the Elbit Systems of America facility in Merrimack, NH',
                    'arrest_date' => '2023-11-20',
                    'convicted' => 'Yes — late 2024',
                    'release_date' => '2024-12-23',
                ]],
            ],
            [
                'name' => 'Sophie Ross',
                'first_name' => 'Sophie',
                'last_name' => 'Ross',
                'description' => 'One of the "Merrimack 4," arrested in November 2023 for participating in a direct action at the Elbit Systems of America facility in Merrimack, New Hampshire — the U.S. arm of Israel\'s largest weapons manufacturer. Convicted in late 2024 and released in late December 2024.',
                'state' => 'New Hampshire',
                'ideologies' => ['Pro-Palestine', 'Anti-imperialism'],
                'affiliation' => ['Merrimack 4', 'Palestine Action US'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_city' => 'Merrimack',
                    'institution_state' => 'New Hampshire',
                    'charges' => 'Riot, criminal mischief, and related charges for a November 2023 action at the Elbit Systems of America facility in Merrimack, NH',
                    'arrest_date' => '2023-11-20',
                    'convicted' => 'Yes — late 2024',
                    'release_date' => '2024-12-23',
                ]],
            ],
            [
                'name' => 'Bridget Shergalis',
                'first_name' => 'Bridget',
                'last_name' => 'Shergalis',
                'description' => 'One of the "Merrimack 4," arrested in November 2023 for an action at the Elbit Systems of America facility in Merrimack, New Hampshire, a U.S. site of Israel\'s largest weapons manufacturer. Convicted in late 2024 and released in late December 2024.',
                'state' => 'New Hampshire',
                'ideologies' => ['Pro-Palestine', 'Anti-imperialism'],
                'affiliation' => ['Merrimack 4', 'Palestine Action US'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_city' => 'Merrimack',
                    'institution_state' => 'New Hampshire',
                    'charges' => 'Riot, criminal mischief, and related charges for a November 2023 action at the Elbit Systems of America facility in Merrimack, NH',
                    'arrest_date' => '2023-11-20',
                    'convicted' => 'Yes — late 2024',
                    'release_date' => '2024-12-23',
                ]],
            ],
            [
                'name' => 'Paige Belanger',
                'first_name' => 'Paige',
                'last_name' => 'Belanger',
                'description' => 'One of the "Merrimack 4," arrested in November 2023 for participating in a Palestine solidarity action at the Elbit Systems of America facility in Merrimack, New Hampshire. Convicted in late 2024 and released in late December 2024.',
                'state' => 'New Hampshire',
                'ideologies' => ['Pro-Palestine', 'Anti-imperialism'],
                'affiliation' => ['Merrimack 4', 'Palestine Action US'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_city' => 'Merrimack',
                    'institution_state' => 'New Hampshire',
                    'charges' => 'Riot, criminal mischief, and related charges for a November 2023 action at the Elbit Systems of America facility in Merrimack, NH',
                    'arrest_date' => '2023-11-20',
                    'convicted' => 'Yes — late 2024',
                    'release_date' => '2024-12-23',
                ]],
            ],

            // ---------- Stop Cop City ATL61 named (3) ----------
            [
                'name' => 'Ayla King',
                'first_name' => 'Ayla',
                'last_name' => 'King',
                'description' => 'The first defendant in the sweeping Georgia RICO indictment against Stop Cop City forest defenders to go to trial. King was indicted in August 2023 along with 60+ co-defendants on RICO charges for opposition to the Atlanta Public Safety Training Center ("Cop City"). The trial ended in a mistrial in July 2025.',
                'state' => 'Georgia',
                'ideologies' => ['Anti-fascism', 'Forest Defense', 'Stop Cop City'],
                'affiliation' => ['Stop Cop City', 'Atlanta Forest Defenders'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'website' => 'https://atlsolidarity.org',
                'cases' => [[
                    'institution_city' => 'Atlanta',
                    'institution_state' => 'Georgia',
                    'charges' => 'Violation of the Georgia RICO Act in connection with the Stop Cop City movement against the Atlanta Public Safety Training Center',
                    'convicted' => 'Mistrial declared July 2025',
                ]],
            ],
            [
                'name' => 'Jack Mazurek',
                'first_name' => 'Jack',
                'last_name' => 'Mazurek',
                'description' => 'Stop Cop City defendant charged under Georgia\'s RICO Act in the August 2023 indictment of 61 forest defenders opposing the Atlanta Public Safety Training Center. In March 2025, Mazurek filed a federal civil rights lawsuit against the Atlanta Police Department over his arrest and prosecution.',
                'state' => 'Georgia',
                'ideologies' => ['Anti-fascism', 'Forest Defense', 'Stop Cop City'],
                'affiliation' => ['Stop Cop City', 'Atlanta Forest Defenders'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'website' => 'https://freejack.co',
                'cases' => [[
                    'institution_city' => 'Atlanta',
                    'institution_state' => 'Georgia',
                    'charges' => 'Violation of the Georgia RICO Act in connection with the Stop Cop City movement against the Atlanta Public Safety Training Center',
                ]],
            ],
            [
                'name' => 'Esmond Adams',
                'first_name' => 'Esmond',
                'last_name' => 'Adams',
                'description' => 'Stop Cop City defendant indicted in August 2023 under Georgia\'s RICO Act along with 60+ co-defendants for opposition to the Atlanta Public Safety Training Center. Adams\'s case was severed from the main RICO trial.',
                'state' => 'Georgia',
                'ideologies' => ['Anti-fascism', 'Forest Defense', 'Stop Cop City'],
                'affiliation' => ['Stop Cop City', 'Atlanta Forest Defenders'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => true,
                'website' => 'https://atlsolidarity.org',
                'cases' => [[
                    'institution_city' => 'Atlanta',
                    'institution_state' => 'Georgia',
                    'charges' => 'Violation of the Georgia RICO Act in connection with the Stop Cop City movement against the Atlanta Public Safety Training Center; case severed from main trial',
                ]],
            ],

            // ---------- Long-term BL: Oso Blanco (only new) ----------
            [
                'name' => 'Oso Blanco (Byron Shane Chubbuck)',
                'first_name' => 'Byron',
                'middle_name' => 'Shane',
                'last_name' => 'Chubbuck',
                'aka' => 'Oso Blanco; Yona Unega',
                'description' => 'Indigenous (Cherokee/Choctaw) anti-imperialist and Zapatista solidarity activist. Between 1998 and August 1999 Chubbuck robbed 13 banks and attempted a 14th across the U.S. Southwest, telling tellers he was expropriating funds for poor and Indigenous people fighting for autonomy in Chiapas, Mexico — earning the FBI nickname "Robin the Hood." Arrested August 13, 1999 in Albuquerque after a shootout with FBI agents (no one injured); originally sentenced to roughly 80 years federal. Approximately 25 years were vacated in 2016 following Johnson v. United States invalidating the ACCA residual clause. Continues to organize from inside in solidarity with Indigenous, Black, and Chicano liberation movements.',
                'state' => 'California',
                'race' => 'Indigenous (Cherokee/Choctaw)',
                'gender' => 'Male',
                'birthdate' => '1967-02-26',
                'ideologies' => ['Indigenous liberation', 'Anti-imperialism', 'Zapatista solidarity'],
                'affiliation' => ['Wolf Clan', 'Zapatista solidarity'],
                'era' => '1990s',
                'in_custody' => true,
                'released' => false,
                'inmate_number' => '07909-051',
                'cases' => [[
                    'institution_name' => 'USP Atwater',
                    'institution_city' => 'Atwater',
                    'institution_state' => 'California',
                    'charges' => 'Multiple counts of federal armed bank robbery (13 banks, 1 attempted), use of a firearm during a crime of violence, and assault on federal officers arising from an August 13, 1999 shootout with FBI agents in Albuquerque, NM.',
                    'arrest_date' => '1999-08-13',
                    'convicted' => 'Yes — federal jury verdict, D.N.M.',
                    'sentence' => 'Originally ~80 years federal; approximately 25 years vacated in 2016 following Johnson v. United States.',
                ]],
            ],
        ];
    }

    /**
     * @return array<int,array{name:string,fields:array<string,mixed>}>
     */
    private function updates(): array {
        return [
            [
                'name' => 'Kevin Rashid Johnson',
                'fields' => [
                    'aka' => 'Rashid',
                    'inmate_number' => '1007485',
                    'state' => 'Virginia',
                    'race' => 'Black',
                    'gender' => 'Male',
                    'ideologies' => ['New Afrikan Black Panther Party (Prison Chapter)', 'Revolutionary Intercommunalism', 'Pan-Africanism'],
                    'affiliation' => ['NABPP-PC', 'Incarcerated Workers Organizing Committee (IWOC)'],
                    'era' => '1990s',
                    'in_custody' => true,
                    'website' => 'https://rashidmod.com',
                    'description_append' => 'Diagnosed with multiple myeloma; supporters have publicly called for medical/compassionate release. Has been repeatedly transferred under interstate compact in apparent retaliation for prison-conditions journalism (prior stints in Oregon, Texas, Florida, Indiana). Most recent verifiable placement as of 2024–2025 reporting is the Virginia Department of Corrections.',
                ],
            ],
            [
                'name' => 'Fred Burton (Muhammad Burton)',
                'fields' => [
                    'aka' => 'Muhammad Burton; Frederick Burton',
                    'inmate_number' => 'AF-3896',
                    'state' => 'Pennsylvania',
                    'race' => 'Black',
                    'gender' => 'Male',
                    'birthdate' => '1946-12-15',
                    'ideologies' => ['Black liberation'],
                    'affiliation' => ['Philly 5', 'Black Liberation Army-era defendants'],
                    'era' => '1970s',
                    'in_custody' => true,
                    'description_append' => 'Convicted of the August 29, 1970 killing of Philadelphia police Sgt. Frank Von Colln during the Cobbs Creek Park guardhouse attack; one of the "Philly 5." Tried before an all-white jury after every Black juror was struck. Continues to seek commutation; PA Board of Pardons clemency campaign ongoing as of 2024. Currently held at SCI Phoenix.',
                ],
            ],
            [
                'name' => 'Kamau Sadiki (Freddie Hilton)',
                'fields' => [
                    'aka' => 'Freddie Hilton',
                    'state' => 'Georgia',
                    'race' => 'Black',
                    'gender' => 'Male',
                    'birthdate' => '1953-02-19',
                    'ideologies' => ['Black liberation', 'Black Panther Party'],
                    'affiliation' => ['Black Panther Party (Jamaica, NY office)', 'Black Liberation Army'],
                    'era' => '2000s',
                    'in_custody' => true,
                    'description_append' => 'Held at Augusta State Medical Prison (Augusta, GA) due to serious health conditions including diabetes, neuropathy, and sarcoidosis; reported cancer diagnoses in supporter communications. Supporters argue he was targeted in the 2003 prosecution for refusing FBI cooperation in attempts to extradite Assata Shakur from Cuba. Parole was denied in 2023.',
                ],
            ],
            [
                'name' => 'H. Rap Brown, Hubert Gerold Brown (Jamil Abdullah al-Amin)',
                'fields' => [
                    'aka' => 'Imam Jamil Al-Amin; H. Rap Brown; Hubert Gerold Brown',
                    'birthdate' => '1943-10-04',
                    'race' => 'Black',
                    'gender' => 'Male',
                    'ideologies' => ['Black liberation', 'Islam', 'SNCC-era radicalism'],
                    'affiliation' => ['Student Nonviolent Coordinating Committee (former chair)', 'Black Panther Party (former Minister of Justice)', 'Dar-ul-Islam movement'],
                    'era' => '2000s',
                    'in_custody' => true,
                    'description_append' => 'Originally held at ADX Florence (federal supermax); transferred around 2014 to USP Tucson, where he remains as of 2024–2025 reporting. Diagnosed with multiple myeloma; the "Justice for Imam Jamil" campaign and his legal team have repeatedly petitioned for compassionate release citing the failure-to-investigate-Otis-Jackson confession, his age (80+), and serious medical issues.',
                ],
            ],
            [
                'name' => 'Michael Kimble',
                'fields' => [
                    'inmate_number' => '138017',
                    'state' => 'Alabama',
                    'race' => 'Black',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism', 'Queer liberation', 'Black liberation'],
                    'affiliation' => ['Free Alabama Movement', 'Anarchist Black Cross supporters'],
                    'era' => '1980s',
                    'in_custody' => true,
                    'website' => 'https://anarchylive.noblogs.org',
                    'description_append' => 'Queer Black anarchist prisoner held by the Alabama DOC since November 12, 1987 (incident date November 1986) for a self-defense killing of a white assailant who used racist and homophobic slurs. Convicted by a Jefferson County, AL jury and sentenced to life. Continues to organize from inside (Prisoners Action Committee, AJAMU, August 7th Movement, INACELL magazine) and to participate in Free Alabama Movement work strikes. Sentence-reduction motion pending per supporter reporting as of 2024–2025.',
                ],
            ],
        ];
    }
}
