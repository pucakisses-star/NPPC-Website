<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddSeditionActDefendants extends Command
{
    protected $signature = 'prisoners:add-sedition-act-defendants';
    protected $description = 'Add the six pictured Espionage Act / Sedition Act defendants (A. Philip Randolph, Eugene V. Debs, Max Eastman, John Reed, Victor L. Berger, Bill Haywood).';

    public function handle(): int
    {
        $cuyahogaJail   = Institution::firstOrCreate(['name' => 'Cuyahoga County Jail'], ['city' => 'Cleveland', 'state' => 'Ohio']);
        $atlantaUSP     = Institution::firstOrCreate(['name' => 'United States Penitentiary, Atlanta'], ['city' => 'Atlanta', 'state' => 'Georgia']);
        $moundsvilleWV  = Institution::firstOrCreate(['name' => 'West Virginia State Penitentiary'], ['city' => 'Moundsville', 'state' => 'West Virginia']);
        $sdny           = Institution::firstOrCreate(['name' => 'United States District Court, Southern District of New York'], ['state' => 'New York']);
        $milwaukeeFed   = Institution::firstOrCreate(['name' => 'United States District Court, Eastern District of Wisconsin'], ['state' => 'Wisconsin']);
        $chicagoFed     = Institution::firstOrCreate(['name' => 'United States District Court, Northern District of Illinois (1918 IWW mass trial)'], ['state' => 'Illinois']);
        $leavenworthUSP = Institution::firstOrCreate(['name' => 'United States Penitentiary, Leavenworth'], ['city' => 'Leavenworth', 'state' => 'Kansas']);
        $ussRussia      = Institution::firstOrCreate(['name' => 'Soviet Russia (political exile)']);

        $entries = [
            // A. Philip Randolph ─────────────────────────────────────
            [
                'data' => [
                    'name' => 'A. Philip Randolph', 'first_name' => 'Asa', 'middle_name' => 'Philip', 'last_name' => 'Randolph',
                    'aka' => 'Asa Philip Randolph', 'gender' => 'Male', 'race' => 'Black',
                    'state' => 'Ohio', 'era' => '1910s', 'birthdate' => '1889-04-15', 'death_date' => '1979-05-16',
                    'ideologies' => ['Socialism', 'Black liberation', 'Labor organizing'],
                    'affiliation' => ['The Messenger', 'Brotherhood of Sleeping Car Porters', 'Socialist Party of America'],
                    'in_custody' => false, 'released' => true,
                    'description' => "A. Philip Randolph was a Black socialist labor organizer, civil-rights leader, and co-editor (with Chandler Owen) of The Messenger, a radical Black socialist magazine published in Harlem. In August 1918, while on a speaking tour for the magazine in Cleveland, Randolph and Owen were arrested under the Espionage Act of 1917 for distributing The Messenger and for a speech opposing U.S. entry into World War I. They were held in the Cuyahoga County Jail and released on \$1,000 bail. The case was ultimately dismissed — the federal judge reportedly told Randolph he was 'too young' to have written the editorial in question and refused to believe a Black man could have authored such radical material. Randolph went on to found the Brotherhood of Sleeping Car Porters in 1925, organized the planned 1941 March on Washington that pressured FDR into signing Executive Order 8802 desegregating the defense industry, and served as the lead organizer of the 1963 March on Washington for Jobs and Freedom.",
                ],
                'cases' => [[
                    'institution_id' => $cuyahogaJail->id,
                    'charges' => 'Espionage Act of 1917 — interference with the recruiting and enlistment service of the United States; distribution of The Messenger and anti-war speech in Cleveland',
                    'arrest_date' => '1918-08-04',
                    'release_date' => '1918-08-06',
                    'convicted' => 'No — charges dismissed at preliminary hearing',
                    'sentence' => 'Released on \$1,000 bail after two days; charges dismissed',
                ]],
            ],

            // Eugene V. Debs ─────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Eugene V. Debs', 'first_name' => 'Eugene', 'middle_name' => 'Victor', 'last_name' => 'Debs',
                    'gender' => 'Male', 'race' => 'White',
                    'state' => 'Indiana', 'era' => '1910s', 'birthdate' => '1855-11-05', 'death_date' => '1926-10-20',
                    'ideologies' => ['Socialism', 'Anti-war', 'Labor organizing'],
                    'affiliation' => ['Socialist Party of America', 'American Railway Union', 'Industrial Workers of the World (IWW)'],
                    'in_custody' => false, 'released' => true,
                    'inmate_number' => '9653',
                    'description' => "Eugene Victor Debs was the most prominent socialist in U.S. history — five-time Socialist Party of America presidential candidate, founder of the American Railway Union (which led the 1894 Pullman Strike, for which Debs served six months at the Woodstock IL jail), and co-founder of the Industrial Workers of the World in 1905. On June 16, 1918, in Canton, Ohio, Debs delivered a public speech denouncing U.S. participation in World War I and the prosecution of socialist anti-war organizers. He was arrested two weeks later under the Espionage Act of 1917, convicted September 12, 1918 in federal court in Cleveland, and sentenced to ten years in federal prison. He began serving in April 1919 at the West Virginia State Penitentiary in Moundsville, then was transferred to the United States Penitentiary in Atlanta. From his cell as Federal Prisoner #9653, Debs ran for president of the United States in 1920 on the Socialist Party ticket and received 919,799 votes — nearly one million Americans voting for a presidential candidate then in federal prison. President Warren G. Harding commuted Debs's sentence to time served on Christmas Day 1921; Debs was released December 25, 1921 after serving roughly two and a half years.",
                ],
                'cases' => [[
                    'institution_id' => $atlantaUSP->id,
                    'charges' => 'Espionage Act of 1917 — ten counts of attempting to cause insubordination, disloyalty, mutiny, and refusal of duty in the armed forces, in connection with his June 16 1918 anti-war speech in Canton, Ohio',
                    'arrest_date' => '1918-06-30',
                    'sentenced_date' => '1918-09-14',
                    'incarceration_date' => '1919-04-13',
                    'release_date' => '1921-12-25',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the Northern District of Ohio, September 12, 1918',
                    'sentence' => '10 years federal prison; sentence commuted to time served by President Warren G. Harding on December 25, 1921',
                ]],
            ],

            // Max Eastman ────────────────────────────────────────────
            [
                'data' => [
                    'name' => 'Max Eastman', 'first_name' => 'Max', 'middle_name' => 'Forrester', 'last_name' => 'Eastman',
                    'gender' => 'Male', 'race' => 'White',
                    'state' => 'New York', 'era' => '1910s', 'birthdate' => '1883-01-04', 'death_date' => '1969-03-25',
                    'ideologies' => ['Socialism', 'Anti-war', 'Free press'],
                    'affiliation' => ['The Masses', 'The Liberator'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Max Eastman was a poet, journalist, and the editor of The Masses, the radical New York socialist literary magazine that published Carl Sandburg, John Reed, Floyd Dell, and the cartoons of Art Young between 1911 and 1917. After the United States entered World War I, the Post Office revoked The Masses' second-class mailing privileges under the Espionage Act of 1917, effectively shutting it down. Federal prosecutors then indicted Eastman, John Reed, Floyd Dell, Art Young, and the magazine's business manager Merrill Rogers in November 1917 for conspiracy to obstruct military recruitment. The first trial in April 1918 ended in a hung jury; the second in October 1918 also ended in a hung jury. Federal prosecutors declined to bring a third prosecution, and the charges were dismissed. Eastman went on to launch The Liberator as the successor magazine and remained a prominent radical voice through the 1920s.",
                ],
                'cases' => [[
                    'institution_id' => $sdny->id,
                    'charges' => 'Espionage Act of 1917 — conspiracy to obstruct military recruitment and enlistment, in connection with editorials and cartoons published in The Masses',
                    'arrest_date' => '1917-11-19',
                    'release_date' => '1918-10-05',
                    'convicted' => 'No — two consecutive trials (April 1918 and October 1918) ended in hung juries; charges dismissed',
                    'sentence' => 'No conviction; case dismissed after two hung juries',
                ]],
            ],

            // John Reed ──────────────────────────────────────────────
            [
                'data' => [
                    'name' => 'John Reed', 'first_name' => 'John', 'middle_name' => 'Silas', 'last_name' => 'Reed',
                    'gender' => 'Male', 'race' => 'White',
                    'state' => 'Oregon', 'era' => '1910s', 'birthdate' => '1887-10-22', 'death_date' => '1920-10-19',
                    'ideologies' => ['Communism', 'Socialism', 'Anti-war'],
                    'affiliation' => ['The Masses', 'Communist Labor Party of America', 'The Liberator'],
                    'in_custody' => false, 'released' => true,
                    'description' => "John Reed was an American journalist and revolutionary, best known for his eyewitness account of the 1917 Russian Revolution, Ten Days That Shook the World. He was a contributor to The Masses and a co-defendant with Max Eastman in the November 1917 federal indictment under the Espionage Act for conspiracy to obstruct military recruitment. The first trial (April 1918) ended in a hung jury; the second (October 1918) also ended in a hung jury, and the charges were dismissed. In 1919 Reed co-founded the Communist Labor Party of America in Chicago and was indicted in absentia on sedition charges by Illinois state authorities under the criminal-syndicalism statute. He fled to Soviet Russia, where he died of typhus in Moscow on October 19, 1920 at age 32. Reed is one of only three Americans buried at the Kremlin Wall Necropolis.",
                ],
                'cases' => [[
                    'institution_id' => $sdny->id,
                    'charges' => 'Espionage Act of 1917 — conspiracy to obstruct military recruitment and enlistment, as a contributor to The Masses; subsequent 1919 Illinois state criminal-syndicalism indictment in absentia',
                    'arrest_date' => '1917-11-19',
                    'release_date' => '1918-10-05',
                    'in_exile_since' => '1919-10-01',
                    'end_of_exile' => '1920-10-19',
                    'convicted' => 'No — two hung juries in The Masses trial; 1919 sedition indictment never adjudicated; died in exile in Soviet Russia',
                    'sentence' => 'No conviction; died of typhus in Moscow at age 32 while in political exile',
                ]],
            ],

            // Victor L. Berger ───────────────────────────────────────
            [
                'data' => [
                    'name' => 'Victor L. Berger', 'first_name' => 'Victor', 'middle_name' => 'Luitpold', 'last_name' => 'Berger',
                    'gender' => 'Male', 'race' => 'White',
                    'state' => 'Wisconsin', 'era' => '1910s', 'birthdate' => '1860-02-28', 'death_date' => '1929-08-07',
                    'ideologies' => ['Socialism', 'Anti-war', 'Democratic socialism'],
                    'affiliation' => ['Socialist Party of America', 'United States House of Representatives'],
                    'in_custody' => false, 'released' => true,
                    'description' => "Victor Berger was the first Socialist elected to the United States Congress, representing Milwaukee, Wisconsin. He was indicted in February 1918 under the Espionage Act of 1917 for editorials he had published in the Milwaukee Leader opposing U.S. entry into World War I. He was convicted on January 8, 1919 in U.S. District Court in Chicago and sentenced on February 20, 1919 by Judge Kenesaw Mountain Landis to twenty years in federal prison. Berger had been re-elected to Congress in November 1918 while under indictment; the House refused to seat him on November 10, 1919 and again, after a Wisconsin special election returned him in December 1919. On January 31, 1921, the U.S. Supreme Court reversed Berger's conviction in Berger v. United States, holding that Judge Landis's hostility toward Berger's German-American ancestry deprived him of an impartial trial. The federal indictment was dismissed in 1922 and Berger was finally seated in the House in 1923, serving three more terms.",
                ],
                'cases' => [[
                    'institution_id' => $milwaukeeFed->id,
                    'charges' => 'Espionage Act of 1917 — five counts of conspiracy to obstruct military recruitment, in connection with editorials in the Milwaukee Leader',
                    'arrest_date' => '1918-02-02',
                    'sentenced_date' => '1919-02-20',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the Northern District of Illinois, January 8, 1919; conviction reversed by U.S. Supreme Court (Berger v. United States) on January 31, 1921',
                    'sentence' => '20 years federal prison; free on \$100,000 bail pending appeal; never served; case dismissed in 1922 after Supreme Court reversal',
                ]],
            ],

            // "Big Bill" Haywood ─────────────────────────────────────
            [
                'data' => [
                    'name' => 'Bill Haywood', 'first_name' => 'William', 'middle_name' => 'Dudley', 'last_name' => 'Haywood',
                    'aka' => 'William Dudley "Big Bill" Haywood', 'gender' => 'Male', 'race' => 'White',
                    'state' => 'Idaho', 'era' => '1910s', 'birthdate' => '1869-02-04', 'death_date' => '1928-05-18',
                    'ideologies' => ['Communism', 'Syndicalism', 'Industrial unionism'],
                    'affiliation' => ['Industrial Workers of the World (IWW)', 'Western Federation of Miners', 'Socialist Party of America'],
                    'in_custody' => false, 'released' => true,
                    'inmate_number' => '13106',
                    'description' => "William Dudley 'Big Bill' Haywood was the founding general secretary-treasurer of the Industrial Workers of the World (IWW, the Wobblies) and one of the most important figures in U.S. radical labor history. In September 1917, federal agents raided IWW offices across the country, seizing tons of records and arresting roughly 165 IWW leaders in coordinated raids. The resulting mass federal trial in Chicago — United States v. Haywood et al., overseen by Judge Kenesaw Mountain Landis — was the largest political prosecution in U.S. history at that point. After a four-month trial, on August 17, 1918, the jury convicted 101 of the 113 IWW defendants on hundreds of counts of obstructing the war effort. Haywood was sentenced on August 30, 1918 to twenty years in federal prison and a \$30,000 fine. While free on \$30,000 bail pending appeal in March 1921, Haywood and several co-defendants jumped bail and fled to Soviet Russia. He spent the rest of his life in exile in Moscow, where he died on May 18, 1928. His ashes were divided in two: half were interred in the Kremlin Wall Necropolis in Moscow, the other half buried beneath the Haymarket Martyrs Monument in Forest Park, Illinois.",
                ],
                'cases' => [[
                    'institution_id' => $chicagoFed->id,
                    'charges' => 'Espionage Act of 1917 and Selective Service Act — multiple counts of conspiracy to obstruct the war effort and induce draft evasion (lead defendant, United States v. Haywood et al., the IWW mass trial)',
                    'arrest_date' => '1917-09-28',
                    'sentenced_date' => '1918-08-30',
                    'in_exile_since' => '1921-04-01',
                    'end_of_exile' => '1928-05-18',
                    'convicted' => 'Yes — federal jury verdict, U.S. District Court for the Northern District of Illinois, August 17, 1918',
                    'sentence' => '20 years federal prison and \$30,000 fine; free on \$30,000 bail pending appeal; jumped bail in 1921 and fled to Soviet Russia, where he died in 1928',
                ]],
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($entries as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                foreach ($entry['cases'] as $case) {
                    PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $case));
                }
                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
