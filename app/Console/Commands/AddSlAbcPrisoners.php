<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds the prisoners surfaced from the Salt Lake City ABC site
 * (slabc.wordpress.com): Jordan Halliday, Katherine "KteeO" Olejnik,
 * Tim DeChristopher, and the 10 defendants in the 2011 federal RICO
 * indictment of the Greensboro ALKQN (United States v. Cornell et al.,
 * 1:11-cr-00387, M.D.N.C.).
 *
 * Each prisoner is created via the existing prisoner:add command, which
 * is idempotent (refuses to create if a Prisoner with the same name
 * already exists).
 */
final class AddSlAbcPrisoners extends Command {
    protected $signature = 'archive:add-slabc-prisoners';
    protected $description = 'Add SLABC-sourced prisoners (Halliday, Olejnik, DeChristopher, ALKQN NC-12)';

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

        $this->info("\nDone. Created={$created} Skipped={$skipped}");

        return self::SUCCESS;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function payloads(): array {
        $alkqnArrest = '2011-12-07';
        $alkqnAffiliation = ['Almighty Latin King & Queen Nation'];
        $alkqnProsecutor = 'Robert A. J. Lang (AUSA, MDNC)';
        $alkqnJudge = 'Catherine C. Eagles (MDNC)';

        return [
            [
                'name' => 'Jordan Halliday',
                'first_name' => 'Jordan',
                'last_name' => 'Halliday',
                'description' => "Jordan Halliday is a Utah-based animal liberation activist who was imprisoned for civil and criminal contempt after refusing to testify before a federal grand jury investigating the 2008 release of approximately 650 mink from the McMullin Mink Farm in South Jordan, Utah. Subpoenaed in 2009 by the U.S. Attorney for the District of Utah, Halliday repeatedly refused to answer questions about the animal liberation activist community, citing grand jury resistance principles widely held among anarchists and animal rights activists who view such proceedings as fishing expeditions used to gather intelligence on movements.\n\nAfter being held in civil contempt and jailed briefly in 2009, Halliday was later indicted for criminal contempt of court for his continued refusal to cooperate. In September 2011, he was sentenced by U.S. District Judge Dee Benson to 10 months in federal prison, followed by 36 months of supervised release. He served his sentence and was released in 2012. His case became a rallying point for the animal liberation and anarchist movements, with the support campaign organized at supportjordan.org documenting his resistance and calling for solidarity with grand jury resisters.",
                'state' => 'Utah',
                'gender' => 'Male',
                'ideologies' => ['Animal Liberation', 'Anarchism', 'Grand Jury Resistance'],
                'affiliation' => ['Animal Liberation Movement'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'charges' => 'Criminal contempt of court (refusal to testify before a federal grand jury)',
                    'arrest_date' => '2009-11-04',
                    'incarceration_date' => '2011-11-14',
                    'release_date' => '2012-07-20',
                    'convicted' => 'Yes — 2011 (criminal contempt)',
                    'prosecutor' => 'U.S. Attorney for the District of Utah',
                    'judge' => 'Dee Benson',
                    'sentence' => '10 months federal prison + 36 months supervised release',
                    'imprisoned_for_days' => 249,
                ]],
            ],
            [
                'name' => 'Katherine Olejnik',
                'first_name' => 'Katherine',
                'last_name' => 'Olejnik',
                'description' => "Katherine \"KteeO\" Olejnik is a Seattle-area anarchist who became a grand jury resister in 2012. She was subpoenaed to appear before a federal grand jury in the Western District of Washington that was investigating property destruction during the May Day 2012 anti-capitalist march in downtown Seattle, in which windows were broken at the federal courthouse and other buildings. The grand jury was widely understood by activists and observers to be a political fishing expedition targeting the Pacific Northwest anarchist community, with FBI raids in Portland, Olympia, and Seattle seizing anarchist literature, black clothing, and electronics rather than evidence of specific crimes.\n\nOlejnik refused to cooperate, declining to answer questions and invoking her political opposition to grand juries as tools of state repression. On September 27, 2012, she was found in civil contempt by U.S. District Judge Richard A. Jones and ordered jailed until she testified or until the grand jury's term expired. She was held at the Federal Detention Center SeaTac, much of the time in solitary confinement / administrative segregation, a condition her supporters and attorneys denounced as punitive coercion against a non-criminal witness. Alongside fellow resisters Matt Duran and Maddy Pfeiffer, her case drew international solidarity from anti-repression groups, prison abolitionists, and civil liberties advocates.\n\nOlejnik was released on February 27, 2013, after roughly five months in custody, when prosecutors and the court conceded that further confinement would not coerce her testimony — the legal standard at which civil contempt becomes impermissibly punitive. The grand jury ultimately returned no indictments related to the May Day events. Her resistance, and that of her co-resisters, is remembered as a significant chapter in the long history of grand jury non-cooperation by U.S. radical movements.",
                'state' => 'Washington',
                'gender' => 'Female',
                'ideologies' => ['Anarchism', 'Anti-capitalism'],
                'affiliation' => ['Pacific Northwest Grand Jury Resisters', 'Committee Against Political Repression'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Detention Center, SeaTac',
                    'institution_city' => 'SeaTac',
                    'institution_state' => 'Washington',
                    'charges' => 'Civil contempt of court for refusing to testify before a federal grand jury investigating May Day 2012 property destruction in Seattle',
                    'arrest_date' => '2012-09-27',
                    'incarceration_date' => '2012-09-27',
                    'release_date' => '2013-02-27',
                    'convicted' => 'No — held in civil contempt (non-criminal); released when court found further confinement would not coerce testimony',
                    'judge' => 'Richard A. Jones',
                    'sentence' => 'Indefinite civil contempt confinement until testimony given or grand jury term expired',
                    'imprisoned_for_days' => 153,
                ]],
            ],
            [
                'name' => 'Tim DeChristopher',
                'first_name' => 'Tim',
                'last_name' => 'DeChristopher',
                'description' => "Tim DeChristopher is an American climate activist who became known as \"Bidder 70\" after disrupting a Bureau of Land Management oil and gas lease auction in Salt Lake City, Utah on December 19, 2008. Then a 27-year-old economics student at the University of Utah, DeChristopher entered the auction intending to protest, was handed a bidder paddle (number 70), and proceeded to place winning bids totaling approximately \$1.8 million on 14 parcels covering roughly 22,000 acres of public land near Arches and Canyonlands National Parks. He had no means or intention to pay, and his bids drove up prices on other parcels as well.\n\nThe auction was later invalidated by the incoming Obama administration, which determined many of the parcels should never have been offered. Nevertheless, DeChristopher was indicted on two federal felony counts: violating the Federal Onshore Oil and Gas Leasing Reform Act and making false statements. He was barred from presenting a \"necessity defense\" based on climate change at trial. On March 3, 2011 he was convicted by a federal jury, and on July 26, 2011 Judge Dee Benson sentenced him to two years in federal prison plus three years of supervised release and a \$10,000 fine.\n\nDeChristopher co-founded Peaceful Uprising, a Salt Lake City-based climate justice group, and his story was chronicled in the 2012 documentary \"Bidder 70.\" He served his sentence at FCI Herlong in California (later transferred to a halfway house in Salt Lake City after a stint in solitary confinement for an email exchange) and was released on April 21, 2013. After prison he attended Harvard Divinity School and continued his work as a climate organizer and Unitarian Universalist minister.",
                'state' => 'Utah',
                'race' => 'White',
                'gender' => 'Male',
                'birthdate' => '1981-09-11',
                'ideologies' => ['Climate Justice', 'Environmentalism', 'Civil Disobedience'],
                'affiliation' => ['Peaceful Uprising', 'Bidder 70'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'FCI Herlong',
                    'institution_city' => 'Herlong',
                    'institution_state' => 'California',
                    'charges' => 'Two federal felonies: violating the Federal Onshore Oil and Gas Leasing Reform Act (43 U.S.C. § 195) and making false statements to the federal government (18 U.S.C. § 1001)',
                    'arrest_date' => '2008-12-19',
                    'incarceration_date' => '2011-07-26',
                    'release_date' => '2013-04-21',
                    'convicted' => 'Yes — March 3, 2011',
                    'prosecutor' => 'John Huber (AUSA)',
                    'judge' => 'Dee Benson',
                    'sentence' => '2 years federal prison + 3 years supervised release + $10,000 fine',
                    'imprisoned_for_days' => 635,
                ]],
            ],
            // ALKQN NC-12 (10 defendants)
            [
                'name' => 'Jorge Peter Cornell',
                'first_name' => 'Jorge',
                'last_name' => 'Cornell',
                'description' => 'Founder and "Inca" (leader) of the Greensboro, North Carolina chapter of the Almighty Latin King & Queen Nation. Indicted in December 2011 and convicted at trial in December 2012 on federal RICO conspiracy and violent crimes in aid of racketeering (VICAR) charges, including conspiracy to murder rival gang members.',
                'state' => 'North Carolina',
                'race' => 'Latino',
                'gender' => 'Male',
                'ideologies' => ['Anti-imperialism'],
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d)); VICAR conspiracy to commit murder; conspiracy to commit assault with a dangerous weapon',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — convicted at trial December 2012',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                    'sentence' => '28 years (336 months) federal prison',
                ]],
            ],
            [
                'name' => 'Russell Kilfoil',
                'first_name' => 'Russell',
                'last_name' => 'Kilfoil',
                'description' => 'High-ranking member ("King Peaceful") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 and convicted at trial December 2012 on federal RICO conspiracy and VICAR charges arising from gang-related violence in Greensboro, North Carolina.',
                'state' => 'North Carolina',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d)); VICAR conspiracy to commit murder and assault with a dangerous weapon',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — convicted at trial December 2012',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                    'sentence' => '24 years (288 months) federal prison',
                ]],
            ],
            [
                'name' => 'Randolph Kilfoil',
                'first_name' => 'Randolph',
                'last_name' => 'Kilfoil',
                'description' => 'Member ("King Paul") of the Greensboro chapter of the Almighty Latin King & Queen Nation and brother of co-defendant Russell Kilfoil. Indicted December 2011 and convicted at trial December 2012 on federal RICO conspiracy and VICAR charges related to gang violence in Greensboro, North Carolina.',
                'state' => 'North Carolina',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d)); VICAR conspiracy to commit murder and assault with a dangerous weapon',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — convicted at trial December 2012',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                    'sentence' => '24 years (288 months) federal prison',
                ]],
            ],
            [
                'name' => 'Ernesto Wilson',
                'first_name' => 'Ernesto',
                'last_name' => 'Wilson',
                'description' => 'Member ("King Cuba" / "King Yayo") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 and convicted at trial December 2012 on federal RICO conspiracy and VICAR charges related to gang-related violence in Greensboro, North Carolina.',
                'state' => 'North Carolina',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d)); VICAR conspiracy to commit murder and assault with a dangerous weapon',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — convicted at trial December 2012',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                    'sentence' => '24 years (288 months) federal prison',
                ]],
            ],
            [
                'name' => 'Samuel Velasquez',
                'first_name' => 'Samuel',
                'last_name' => 'Velasquez',
                'description' => 'Member ("King Hype") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO case against the Greensboro Latin Kings; pleaded guilty prior to the October 2012 trial of co-defendants.',
                'state' => 'North Carolina',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d))',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — pleaded guilty prior to trial',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                ]],
            ],
            [
                'name' => 'Jason Yates',
                'first_name' => 'Jason',
                'last_name' => 'Yates',
                'description' => 'Member ("King Squirrel") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO prosecution of the Greensboro Latin Kings and pleaded guilty before the 2012 trial.',
                'state' => 'North Carolina',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d))',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — pleaded guilty prior to trial',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                ]],
            ],
            [
                'name' => 'Irvin Vasquez',
                'first_name' => 'Irvin',
                'last_name' => 'Vasquez',
                'description' => 'Member ("King Dice") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO case in the Middle District of North Carolina and pleaded guilty prior to the October 2012 trial.',
                'state' => 'North Carolina',
                'race' => 'Latino',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d))',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — pleaded guilty prior to trial',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                ]],
            ],
            [
                'name' => 'Wesley Williams',
                'first_name' => 'Wesley',
                'last_name' => 'Williams',
                'description' => 'Member ("King Bam") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO prosecution of the Greensboro Latin Kings and pleaded guilty prior to the 2012 trial.',
                'state' => 'North Carolina',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d))',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — pleaded guilty prior to trial',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                ]],
            ],
            [
                'name' => 'Carlos Coleman',
                'first_name' => 'Carlos',
                'last_name' => 'Coleman',
                'description' => 'Member ("King Spanky") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO case against the Greensboro Latin Kings and pleaded guilty prior to the October 2012 trial.',
                'state' => 'North Carolina',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'institution_name' => 'North Carolina Department of Corrections',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d))',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — pleaded guilty prior to trial',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                ]],
            ],
            [
                'name' => 'Russell Lloyd Cornell',
                'first_name' => 'Russell',
                'last_name' => 'Cornell',
                'description' => 'Member ("King Reckless") of the Greensboro chapter of the Almighty Latin King & Queen Nation and brother of chapter leader Jorge Cornell. Indicted December 2011 in the federal RICO case in the Middle District of North Carolina and pleaded guilty prior to the October 2012 trial of his brother and three co-defendants.',
                'state' => 'North Carolina',
                'race' => 'Latino',
                'gender' => 'Male',
                'affiliation' => $alkqnAffiliation,
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'North Carolina',
                    'charges' => 'RICO conspiracy (18 U.S.C. 1962(d))',
                    'arrest_date' => $alkqnArrest,
                    'convicted' => 'Yes — pleaded guilty prior to trial',
                    'prosecutor' => $alkqnProsecutor,
                    'judge' => $alkqnJudge,
                ]],
            ],
        ];
    }
}
