<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds anti-fascist counter-protester cases similar to Brian DiPippa\'s:
 *
 *  - The 8 named defendants of the 2021 Pacific Beach (San Diego)
 *    "Patriot March" counter-protest case, sentenced June 28, 2024
 *    after the first major state-level "antifa" conspiracy prosecution
 *    in the U.S.
 *  - Brent Vincent Betterly, the third NATO 3 defendant whose two
 *    co-defendants (Brian "Jacob" Church and Jared "Jay" Chase) are
 *    already in the database.
 *  - Dane Powell, the only J20 (2017 Trump inauguration) defendant
 *    convicted at trial and the only one to serve significant federal
 *    time out of the ~230 originally charged.
 */
class AddAntifaCounterProtesters extends Command
{
    protected $signature = 'prisoners:add-antifa-counter-protesters';
    protected $description = 'Add anti-fascist counter-protester defendants similar to Brian DiPippa.';

    private const PACIFIC_BEACH_CONTEXT = <<<'TXT'
On January 9, 2021 — three days after the Capitol riot in Washington, D.C. — pro-Trump organizers held a rally they called the "Patriot March" along the boardwalk in the Pacific Beach neighborhood of San Diego, California. A group of anti-fascist counter-protesters appeared and confrontations broke out, including the use of pepper spray, the throwing of objects, and several physical assaults. The San Diego County District Attorney brought conspiracy-to-riot and related charges against eleven counter-protesters under what would become, when sentenced in 2024, the first major state-level "antifa" conspiracy prosecution in the United States. Nine of the eleven pleaded guilty; two were convicted at jury trial in May 2024. On June 28, 2024, San Diego Superior Court Judge Daniel B. Goldstein sentenced the eight named defendants to terms ranging from 180 days in county jail to two years of state prison.
TXT;

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $sdJail = Institution::firstOrCreate(
            ['name' => 'San Diego County Jail'],
            ['city' => 'San Diego', 'state' => 'California']
        );

        $cookCounty = Institution::firstOrCreate(
            ['name' => 'Cook County Jail'],
            ['city' => 'Chicago', 'state' => 'Illinois']
        );

        $dcCentral = Institution::firstOrCreate(
            ['name' => 'D.C. Central Detention Facility'],
            ['city' => 'Washington', 'state' => 'District of Columbia']
        );

        $sharedIdeologies  = ['Anti-fascist', 'Anti-racist'];
        $sharedAffiliation = ['Anti-fascist counter-protest community (Pacific Beach 2021)'];

        // Pacific Beach 8
        $pb = function (string $name, string $first, string $last, string $birth, string $sentence, string $sentenceText) use ($sdJail, $sharedIdeologies, $sharedAffiliation) {
            return [
                'data' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'birthdate' => $birth, 'gender' => 'Male', 'state' => 'California', 'era' => '2020s',
                    'ideologies' => $sharedIdeologies, 'affiliation' => $sharedAffiliation,
                    'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                    'description' => "{$name} was one of the eleven defendants in the San Diego County District Attorney's prosecution of anti-fascist counter-protesters who attended the January 9, 2021 \"Patriot March\" pro-Trump rally on the Pacific Beach boardwalk. He was sentenced on June 28, 2024 by San Diego Superior Court Judge Daniel B. Goldstein to {$sentence}.\n\n".self::PACIFIC_BEACH_CONTEXT,
                ],
                'case' => [
                    'institution_id' => $sdJail->id,
                    'charges'        => 'Conspiracy to commit a riot, plus various assault, unlawful weapon, and false-imprisonment charges arising from the January 9, 2021 anti-fascist counter-protest at the "Patriot March" pro-Trump rally on the Pacific Beach boardwalk in San Diego, California',
                    'arrest_date'    => '2021-04-21', // approximate — initial wave of indictments
                    'sentenced_date' => '2024-06-28',
                    'incarceration_date' => '2024-06-28',
                    'release_date'   => null,
                    'convicted'      => 'Yes — pleaded guilty (or was convicted by jury, where noted), San Diego Superior Court, 2024',
                    'sentence'       => $sentenceText,
                    'judge'          => 'Daniel B. Goldstein',
                ],
            ];
        };

        $entries = [];

        $entries[] = $pb(
            'Brian Cortez Lightfoot Jr.',
            'Brian',
            'Lightfoot Jr.',
            '1997-01-01',
            'two years in state prison (the longest sentence in the case), to be served in county jail',
            'Two years in state prison (longest sentence in the case), served in San Diego County Jail. Convicted at jury trial in May 2024 of conspiracy to commit a riot.'
        );

        $entries[] = $pb(
            'Jeremy White',
            'Jeremy',
            'White',
            '1983-01-01',
            'two years in state prison (the longest sentence in the case), to be served in county jail',
            'Two years in state prison (longest sentence in the case), served in San Diego County Jail. Convicted at jury trial in May 2024 of conspiracy to commit a riot.'
        );

        $entries[] = $pb(
            'Alexander Akridgejacobs',
            'Alexander',
            'Akridgejacobs',
            '1991-01-01',
            '270 days (approximately 9 months) in San Diego County Jail',
            '270 days (approximately 9 months) in San Diego County Jail.'
        );

        $entries[] = $pb(
            'Christian Martinez',
            'Christian',
            'Martinez',
            '1999-01-01',
            'a term in the 180-day to 2-year range in the Pacific Beach 8 sentencing',
            'Sentenced to a jail term in the 180-day to 2-year range as part of the eight-defendant Pacific Beach sentencing on June 28, 2024.'
        );

        $entries[] = $pb(
            'Ruchelle Ogden',
            'Ruchelle',
            'Ogden',
            '1998-01-01',
            'a term in the 180-day to 2-year range in the Pacific Beach 8 sentencing',
            'Sentenced to a jail term in the 180-day to 2-year range as part of the eight-defendant Pacific Beach sentencing on June 28, 2024.'
        );
        // Override gender for Ruchelle (typically a female name)
        $entries[count($entries) - 1]['data']['gender'] = 'Female';

        $entries[] = $pb(
            'Bryan Rivera',
            'Bryan',
            'Rivera',
            '2002-01-01',
            'a term in the 180-day to 2-year range in the Pacific Beach 8 sentencing',
            'Sentenced to a jail term in the 180-day to 2-year range as part of the eight-defendant Pacific Beach sentencing on June 28, 2024.'
        );

        $entries[] = $pb(
            'Faraz Martin Talab',
            'Faraz',
            'Talab',
            '1995-01-01',
            'a term in the 180-day to 2-year range in the Pacific Beach 8 sentencing',
            'Sentenced to a jail term in the 180-day to 2-year range as part of the eight-defendant Pacific Beach sentencing on June 28, 2024.'
        );

        $entries[] = $pb(
            'Joseph Austin Gaskins',
            'Joseph',
            'Gaskins',
            '2001-01-01',
            'a term in the 180-day to 2-year range in the Pacific Beach 8 sentencing',
            'Sentenced to a jail term in the 180-day to 2-year range as part of the eight-defendant Pacific Beach sentencing on June 28, 2024.'
        );

        // Brent Vincent Betterly — NATO 3 third defendant (other two already in DB)
        $entries[] = [
            'data' => [
                'name' => 'Brent Vincent Betterly',
                'first_name' => 'Brent',
                'middle_name' => 'Vincent',
                'last_name' => 'Betterly',
                'birthdate' => '1988-01-01',
                'gender' => 'Male',
                'state' => 'Illinois',
                'era' => '2010s',
                'ideologies' => ['Anti-fascist', 'Anti-war'],
                'affiliation' => ['Occupy Wall Street', 'NATO 3'],
                'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                'description' => "Brent Vincent Betterly was the third defendant in the NATO 3 prosecution alongside Brian \"Jacob\" Church and Jared \"Jay\" Chase (both already in this database). All three were arrested in Chicago on May 16, 2012, three days before the start of the NATO summit, on terrorism conspiracy charges built around an undercover Chicago Police Department sting in which two officers (\"Mo\" and \"Gloves\") supplied the activists with materials to make Molotov cocktails. The state's terrorism charges fell apart at trial — a Cook County jury acquitted all three of all terrorism counts in February 2014, but convicted them of misdemeanor mob action and lesser arson-related charges. Betterly was sentenced in May 2014 to six years in Illinois state prison; with day-for-day credit and pretrial credit (he had been held without bond since the May 2012 arrest), he was released in 2016. The NATO 3 case is widely cited as the first state-level use of an Illinois terrorism statute in a sting operation against political activists.",
            ],
            'case' => [
                'institution_id' => $cookCounty->id,
                'charges'        => "Material support for terrorism (acquitted); possession of an incendiary device with intent to commit arson (acquitted of felony, convicted of lesser); mob action (misdemeanor) — Illinois state prosecution arising from a Chicago Police Department undercover sting operation in the days before the May 2012 NATO summit",
                'arrest_date'    => '2012-05-16',
                'incarceration_date' => '2012-05-16',
                'release_date'   => '2016-05-16',
                'convicted'      => 'Acquitted of all terrorism charges by Cook County jury, February 7, 2014; convicted of mob action and lesser arson-related counts',
                'sentence'       => '6 years in Illinois state prison; released after 4 years with pretrial and good-time credit',
            ],
        ];

        // Dane Powell — J20 (Trump inauguration 2017) — only J20 defendant to serve real federal time
        $entries[] = [
            'data' => [
                'name' => 'Dane Powell',
                'first_name' => 'Dane',
                'last_name' => 'Powell',
                'birthdate' => '1985-01-01',
                'gender' => 'Male',
                'state' => 'Florida',
                'era' => '2010s',
                'ideologies' => ['Anti-fascist', 'Anti-war'],
                'affiliation' => ['Disrupt J20'],
                'in_custody' => false, 'released' => true, 'awaiting_trial' => false,
                'description' => "Dane Powell was the only one of approximately 230 people charged in the federal Disrupt J20 prosecution to serve significant prison time. He was arrested on January 20, 2017, the morning of Donald Trump's first presidential inauguration, during the black-bloc march through downtown Washington, D.C. that became known as J20. The U.S. Attorney for the District of Columbia initially charged virtually every protester arrested that morning — including journalists, legal observers, and bystanders — with felony rioting and conspiracy carrying potential decades-long sentences. After more than a year of mass-defendant trials that produced uniform acquittals and prosecutorial misconduct findings, the U.S. Attorney's Office dismissed the remaining charges in July 2018.\n\nPowell was the exception: he pleaded guilty in May 2017 to felony rioting and assault on a police officer (he had thrown a concrete chunk that struck an officer). He was sentenced on August 18, 2017 to four months in federal prison. He served his sentence at the D.C. Central Detention Facility and at a federal facility, and was released in early 2018. His case is regularly cited as the only conviction to result from one of the largest political mass-arrests in modern Washington, D.C. history.",
            ],
            'case' => [
                'institution_id' => $dcCentral->id,
                'charges'        => 'Felony rioting; assault on a police officer (D.C. Code) — for participation in the January 20, 2017 Disrupt J20 black-bloc march protesting Donald Trump\'s presidential inauguration',
                'arrest_date'    => '2017-01-20',
                'sentenced_date' => '2017-08-18',
                'incarceration_date' => '2017-08-18',
                'release_date'   => '2017-12-18',
                'convicted'      => 'Yes — guilty plea, May 2017, U.S. District Court for the District of Columbia',
                'sentence'       => '4 months in federal prison; served at the D.C. Central Detention Facility and a federal facility',
            ],
        ];

        foreach ($entries as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("  skipped: {$name} already exists");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));

                $this->info("  added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
