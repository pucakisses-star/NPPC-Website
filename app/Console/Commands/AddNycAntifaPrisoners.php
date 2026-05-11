<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the 9 US political prisoners surfaced from nycantifa.wordpress.com
 * (4 NYC-antifa-documented + the 5 Tinley Park Five) and registers the
 * "No Pasaran" 2013 NYC antifa newsletter PDF in the archive.
 *
 * Idempotent — each prisoner add goes through prisoner:add which refuses
 * duplicates by name; the ArchiveRecord uses updateOrCreate by slug.
 */
final class AddNycAntifaPrisoners extends Command {
    protected $signature = 'archive:add-nyc-antifa-prisoners';
    protected $description = 'Add NYC-Antifa-surfaced prisoners + No Pasaran 2013 newsletter';

    public function handle(): int {
        $created = 0;
        $skipped = 0;

        foreach ($this->payloads() as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("Added: {$name}");
                $created++;
            } else {
                $this->warn("Skipped: {$name}");
                $skipped++;
            }
        }

        $this->registerNoPasaran();

        $this->info("\nDone. Prisoners created={$created} skipped={$skipped}");

        return self::SUCCESS;
    }

    private function registerNoPasaran(): void {
        $slug = 'nyc-antifa-no-pasaran-2013';
        $path = public_path('pdfs/nyc-antifa/nopasaran2013.pdf');

        if (! is_file($path)) {
            $this->warn("No Pasaran PDF not found at {$path} — skipping ArchiveRecord registration.");

            return;
        }

        ArchiveRecord::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => 'No Pasaran! NYC 2013 Fall Newsletter',
                'description' => 'Fall 2013 newsletter from NYC Antifa profiling antifascist prisoners Jerry Koch, Jason Hammond, CeCe McDonald, the Tinley Park Five, and others, with calls for solidarity correspondence.',
                'record_type' => 'document',
                'source_format' => 'newsletter',
                'file' => '/pdfs/nyc-antifa/nopasaran2013.pdf',
                'collection' => 'NYC Antifa',
                'subjects' => ['Antifascism', 'Political Prisoners', 'Prisoner Support'],
                'year' => 2013,
                'is_digitized' => true,
                'published' => true,
                'sort_order' => 700,
            ]
        );
        $this->info('Registered ArchiveRecord: No Pasaran 2013 newsletter.');
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function payloads(): array {
        return [
            // --- NYC-Antifa documented solo cases ---
            [
                'name' => "Luke O'Donovan",
                'first_name' => 'Luke',
                'last_name' => "O'Donovan",
                'description' => "Queer anarchist from Atlanta who was attacked by a group of men shouting homophobic slurs at a New Year's Eve party in East Atlanta on December 31, 2012. O'Donovan defended himself with a pocketknife, stabbing several attackers, and was charged with five counts of aggravated assault and one count of attempted murder. After more than a year of organizing by supporters, he pleaded guilty in August 2014 and was sentenced to two years in prison followed by eight years of probation, during which he was banished from Georgia outside the metro Atlanta area. His case became a rallying point for queer self-defense and anti-prison activism.",
                'state' => 'Georgia',
                'gender' => 'Male',
                'ideologies' => ['Anarchism', 'Queer liberation', 'Anti-fascism'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://letlukego.com',
                'cases' => [[
                    'institution_name' => 'Georgia Department of Corrections',
                    'institution_state' => 'Georgia',
                    'charges' => 'Five counts of aggravated assault and one count of attempted murder',
                    'arrest_date' => '2013-01-01',
                    'incarceration_date' => '2014-08-12',
                    'release_date' => '2016-07-25',
                    'convicted' => 'Yes — pleaded guilty to aggravated assault',
                    'sentence' => 'Two years in prison plus eight years of probation, with banishment from Georgia outside the metro Atlanta area',
                ]],
            ],
            [
                'name' => 'CeCe McDonald',
                'first_name' => 'Chrishaun',
                'last_name' => 'McDonald',
                'description' => "Black transgender woman from Minneapolis who became an international symbol of trans resistance after being imprisoned for defending herself against a racist and transphobic attack. On June 5, 2011, McDonald and friends were verbally assaulted outside the Schooner Tavern in South Minneapolis; one attacker smashed a glass into her face, and in the ensuing fight Dean Schmitz was fatally stabbed. Initially charged with second-degree murder, she pleaded guilty to second-degree manslaughter and was sentenced to 41 months, which she served in men's prisons despite being a trans woman. She was released in January 2014 after approximately 19 months and has since become a prominent activist for trans rights and prison abolition.",
                'state' => 'Minnesota',
                'race' => 'Black',
                'gender' => 'Female',
                'ideologies' => ['Trans liberation', 'Prison abolition'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://supportceceblog.wordpress.com',
                'cases' => [[
                    'institution_name' => 'Minnesota Correctional Facility — St. Cloud',
                    'institution_city' => 'St. Cloud',
                    'institution_state' => 'Minnesota',
                    'charges' => 'Second-degree murder (initially); pleaded to second-degree manslaughter',
                    'arrest_date' => '2011-06-05',
                    'incarceration_date' => '2012-06-04',
                    'release_date' => '2014-01-13',
                    'convicted' => 'Yes — pleaded guilty to second-degree manslaughter',
                    'judge' => 'Daniel Moreno',
                    'sentence' => "41 months in prison, served in men's facilities",
                ]],
            ],
            [
                'name' => 'Gerald Koch',
                'first_name' => 'Gerald',
                'last_name' => 'Koch',
                'description' => 'New York City anarchist and grand jury resister, commonly known as Jerry Koch, who was jailed for civil contempt after refusing to testify before a federal grand jury in the Southern District of New York investigating the 2008 bombing of the Times Square military recruiting station. Koch had previously been subpoenaed in 2009 and again in 2013, and consistently refused to answer questions about the New York anarchist community. He was incarcerated at the Metropolitan Correctional Center in Manhattan beginning May 23, 2013, and was released approximately eight months later, around April 30, 2014, after the court accepted that further imprisonment would not coerce his testimony.',
                'state' => 'New York',
                'gender' => 'Male',
                'ideologies' => ['Anarchism'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://jerryresists.net',
                'cases' => [[
                    'institution_name' => 'Metropolitan Correctional Center, New York',
                    'institution_city' => 'New York',
                    'institution_state' => 'New York',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury',
                    'incarceration_date' => '2013-05-23',
                    'release_date' => '2014-04-30',
                    'convicted' => 'Held in civil contempt (not a criminal conviction)',
                    'sentence' => 'Indefinite civil confinement for the life of the grand jury; held approximately eight months',
                    'imprisoned_for_days' => 342,
                ]],
            ],
            [
                'name' => 'Dane Powell',
                'first_name' => 'Dane',
                'last_name' => 'Powell',
                'description' => "Anti-fascist protester and former Bernie Sanders delegate from Florida who became the first of roughly 200 J20 defendants to be sentenced for actions during the protests against Donald Trump's inauguration in Washington, D.C. on January 20, 2017. Powell pleaded guilty to two felonies — assault on a police officer and engaging in a riot — stemming from the DisruptJ20 march. On July 7, 2017, he was sentenced to four months in federal prison followed by three years of supervised release. His prosecution was part of the broader federal effort to charge inauguration protesters with felony rioting, a strategy that ultimately collapsed when prosecutors dropped the remaining cases.",
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_city' => 'Washington',
                    'institution_state' => 'District of Columbia',
                    'charges' => 'Assault on a police officer and engaging in a riot (felonies)',
                    'arrest_date' => '2017-01-20',
                    'incarceration_date' => '2017-07-07',
                    'convicted' => 'Yes — pleaded guilty to two felonies',
                    'sentence' => 'Four months in federal prison followed by three years of supervised release',
                    'imprisoned_for_days' => 120,
                ]],
            ],
            // --- Tinley Park Five (May 19, 2012 — Cook County, IL) ---
            [
                'name' => 'Cody Sutherlin',
                'first_name' => 'Cody',
                'last_name' => 'Sutherlin',
                'description' => 'One of five antifascists arrested fleeing the May 19, 2012 raid on a white nationalist "Economic Summit" at the Ashford House restaurant in Tinley Park, Illinois, where roughly 18 black-clad assailants attacked attendees with hammers and bats. Pleaded guilty in Cook County Circuit Court in January 2013 to armed violence and mob action and was sentenced to 5 years in the Illinois Department of Corrections. The Tinley Park Five became one of the largest antifa prisoner-support campaigns of the decade.',
                'state' => 'Illinois',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'affiliation' => ['Tinley Park Five'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://tinleyparkfive.wordpress.com',
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Felony mob action and armed violence (aggravated battery)',
                    'arrest_date' => '2012-05-19',
                    'convicted' => 'Yes — pleaded guilty January 2013',
                    'sentence' => '5 years Illinois DOC',
                ]],
            ],
            [
                'name' => 'Dylan Sutherlin',
                'first_name' => 'Dylan',
                'last_name' => 'Sutherlin',
                'description' => 'Brother of co-defendant Cody Sutherlin. Arrested fleeing the May 19, 2012 militant antifascist attack on a white nationalist gathering at the Ashford House restaurant in Tinley Park, Illinois. Pleaded guilty in Cook County Circuit Court in January 2013 to armed violence and mob action and received a 5-year sentence in the Illinois Department of Corrections. The Tinley Park Five case drew widespread anarchist and antifascist solidarity organizing on his behalf.',
                'state' => 'Illinois',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'affiliation' => ['Tinley Park Five'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://tinleyparkfive.wordpress.com',
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Felony mob action and armed violence (aggravated battery)',
                    'arrest_date' => '2012-05-19',
                    'convicted' => 'Yes — pleaded guilty January 2013',
                    'sentence' => '5 years Illinois DOC',
                ]],
            ],
            [
                'name' => 'Alex Stuck',
                'first_name' => 'Alex',
                'last_name' => 'Stuck',
                'description' => 'One of five antifascists arrested fleeing the May 19, 2012 raid on a white nationalist "Economic Summit" at the Ashford House restaurant in Tinley Park, Illinois, in which roughly 18 black-clad attackers struck attendees with hammers and bats. Pleaded guilty in Cook County Circuit Court in January 2013 and received the longest of the four co-defendants\' sentences — 6 years in the Illinois Department of Corrections. The Tinley Park Five became a major antifa prisoner-support campaign throughout the mid-2010s.',
                'state' => 'Illinois',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'affiliation' => ['Tinley Park Five'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://tinleyparkfive.wordpress.com',
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Felony mob action and armed violence (aggravated battery)',
                    'arrest_date' => '2012-05-19',
                    'convicted' => 'Yes — pleaded guilty January 2013',
                    'sentence' => '6 years Illinois DOC',
                ]],
            ],
            [
                'name' => 'John Tucker',
                'first_name' => 'John',
                'last_name' => 'Tucker',
                'description' => 'Arrested with three co-defendants while fleeing the May 19, 2012 militant antifascist attack on a white nationalist gathering at the Ashford House restaurant in Tinley Park, Illinois. Pleaded guilty in Cook County Circuit Court in January 2013 to armed violence and mob action and was sentenced to 5 years in the Illinois Department of Corrections. His case, alongside the other members of the Tinley Park Five, became a focal point for antifascist prisoner solidarity in the United States.',
                'state' => 'Illinois',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'affiliation' => ['Tinley Park Five'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://tinleyparkfive.wordpress.com',
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Felony mob action and armed violence (aggravated battery)',
                    'arrest_date' => '2012-05-19',
                    'convicted' => 'Yes — pleaded guilty January 2013',
                    'sentence' => '5 years Illinois DOC',
                ]],
            ],
            [
                'name' => 'Jason Hammond',
                'first_name' => 'Jason',
                'last_name' => 'Hammond',
                'description' => 'Twin brother of Anonymous hacker Jeremy Hammond. Identified later as a participant in the May 19, 2012 antifascist attack on a white nationalist "Economic Summit" at the Ashford House restaurant in Tinley Park, Illinois. Arrested in 2013 and ultimately pleaded guilty in Cook County Circuit Court in 2016 to a single count of armed violence, receiving a sentence of 41 months in the Illinois Department of Corrections. His prosecution extended the Tinley Park Five antifa prisoner-support campaign for several more years.',
                'state' => 'Illinois',
                'gender' => 'Male',
                'ideologies' => ['Anti-fascism'],
                'affiliation' => ['Tinley Park Five'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'website' => 'https://freejasonhammond.blogspot.com',
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Armed violence (felony)',
                    'convicted' => 'Yes — pleaded guilty 2016',
                    'sentence' => '41 months Illinois DOC',
                ]],
            ],
        ];
    }
}
