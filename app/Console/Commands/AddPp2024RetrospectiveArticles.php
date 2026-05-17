<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Bulk-publish 27 U.S. political-prisoner retrospective / explainer
 * articles covering 2024 — the year's major federal cases, the
 * Stop Cop City prosecution wave, the post-Oct-2023 Palestine
 * solidarity repression, climate / Earth defense cases, the Black
 * liberation generation, and analytical year-end pattern pieces.
 *
 * Each article is keyed by slug so re-runs update in place. Articles
 * are dated to the 2024 calendar where pinned to a specific event,
 * and to mid/late-2024 where they are explainer/pattern pieces.
 *
 * Idempotent.
 */
final class AddPp2024RetrospectiveArticles extends Command {
    protected $signature = 'articles:add-pp-2024-retrospective';
    protected $description = 'Publish 27 U.S. political-prisoner retrospective articles covering 2024';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $articles = $this->articles();
        $created = 0; $updated = 0;

        foreach ($articles as $a) {
            $slug = $a['slug']; unset($a['slug']);
            $payload = [
                'title'          => $a['title'],
                'intro'          => $a['intro'],
                'body'           => $a['body'],
                'category_id'    => $category->id,
                'author_id'      => $author->id,
                'published_at'   => Carbon::parse($a['published_at']),
                'citations_json' => $a['citations'],
            ];
            $existing = Article::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
                $this->info("Updated: {$a['title']}");
            } else {
                Article::create(['slug' => $slug] + $payload);
                $created++;
                $this->info("Created: {$a['title']}");
            }
        }

        $this->line("Done — created {$created}, updated {$updated}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function articles(): array {
        return [
            $this->assange(),
            $this->schulte(),
            $this->peltierParole(),
            $this->hale(),
            $this->winner(),
            $this->stopCopCityRico(),
            $this->atlantaSolidarityFund(),
            $this->tortuguitaCivil(),
            $this->forestDefenderPrecedent(),
            $this->campusEncampments(),
            $this->merrimackFour(),
            $this->weaponsBlockaders(),
            $this->grandJuryWave(),
            $this->reznicek(),
            $this->goonan(),
            $this->wetsuweten(),
            $this->mumia(),
            $this->magee(),
            $this->move9(),
            $this->ericKing(),
            $this->donziger(),
            $this->siddiqui(),
            $this->mariusMason2024(),
            $this->yearEndCensus(),
            $this->terrorismEscalation(),
            $this->campusRepressionYear(),
            $this->bushnell(),
        ];
    }

    private function assange(): array {
        return [
            'slug'         => 'assange-plea-deal-saipan-release-june-2024',
            'title'        => 'Julian Assange Walks Free in Saipan: A 14-Year Espionage Act Prosecution Ends in a Plea',
            'intro'        => 'On June 25, 2024, in a U.S. federal courtroom in the Northern Mariana Islands, WikiLeaks founder Julian Assange pleaded guilty to a single Espionage Act count, was sentenced to time served, and flew home to Australia. The plea closes a 14-year U.S. prosecution that turned the Espionage Act of 1917 into a weapon against publishers for the first time — and the precedent it leaves behind is the larger story.',
            'published_at' => '2024-06-26 12:00:00',
            'body'         => <<<'BODY'
<p><em>After five years in Belmarsh Prison in the United Kingdom and seven years before that confined to the Ecuadorian embassy in London, Julian Assange has been released. The terms of the deal — guilty plea to one Espionage Act count for receiving and publishing classified national-defense information — left intact the legal theory the U.S. Department of Justice spent fourteen years building: that a journalist who publishes leaked government documents can be prosecuted as a spy.</em></p>

<h2>The deal</h2>

<p>The hearing took place on June 25, 2024 in a U.S. district courtroom in Saipan, capital of the Commonwealth of the Northern Mariana Islands — a U.S. territory close enough to Australia for Assange to fly home the same day and remote enough that he never set foot on the continental United States. Under the deal he pleaded guilty to one count of conspiring to obtain and disclose national defense information under 18 U.S.C. § 793(g). He was sentenced to the 62 months he had already served in Belmarsh and immediately released. A government Air Force charter flew him to Canberra; his wife, Stella Moris Assange, and the Australian prime minister met him on the tarmac.</p>

<h2>What the prosecution actually was</h2>

<p>The 2010 WikiLeaks publications — the Iraq War Logs, the Afghan War Diary, "Collateral Murder," and the State Department diplomatic cables provided by U.S. Army intelligence analyst Chelsea Manning — were the first time the United States government tried to prosecute a publisher under the Espionage Act for receiving and disclosing classified information. Every legacy U.S. newspaper that has touched a leak in the last century — the Pentagon Papers, the Snowden disclosures, the Drone Papers, the Panama Papers — has done what Assange was charged with doing. The Obama administration declined to bring charges in 2013 precisely because the DOJ could not draw a constitutional line between WikiLeaks and the New York Times. The first Trump administration brought them anyway.</p>

<h2>What the plea preserves</h2>

<p>The plea is a personal release. It is not a legal vindication, and the federal government did not abandon the theory that built the prosecution. The Espionage Act, in the U.S. government's official position, can be used against a publisher who receives and disseminates classified national-defense information. That theory is now sitting on the shelf for the next administration, the one after that, and the one after that, available the next time a U.S. publisher embarrasses the security state. Press-freedom groups — the Reporters Committee for Freedom of the Press, the Knight First Amendment Institute, the ACLU, PEN America, Freedom of the Press Foundation — have been unanimous in describing the plea as both a relief (Assange is free) and an ongoing constitutional alarm (the theory survives).</p>

<h2>Manning, the original prisoner</h2>

<p>The case did not begin with Assange. It began with Chelsea Manning, the Army intelligence analyst who in 2010 transmitted the underlying documents to WikiLeaks, was convicted of Espionage Act violations in 2013, sentenced to 35 years, and served seven before President Obama commuted her sentence in January 2017. Manning was returned to custody in 2019 over her refusal to testify before a federal grand jury investigating WikiLeaks. She survived a suicide attempt in jail and was released after the grand jury's term expired. Her case and Assange's are one case, and any honest accounting of the Espionage Act's expansion in the post-September-11 era runs through both names.</p>

<h2>What this closes</h2>

<p>For Assange personally, the fourteen-year prosecution is over. The U.S. government is not coming for him again on these facts. For the broader question — whether U.S. publishers, including U.S. citizens reporting on U.S. classified material, can be prosecuted as spies — June 25, 2024 changed nothing. The case the Department of Justice built will be cited in the next prosecution and the one after. The plea bought Assange his freedom. It did not buy the next publisher anything at all.</p>
BODY,
            'citations' => [
                ['title' => 'DOJ press release — Julian Assange plea agreement (June 24, 2024)', 'url' => 'https://www.justice.gov/usao-nmi/pr/julian-assange-plea'],
                ['title' => 'New York Times — Assange Pleads Guilty in U.S. Court in Saipan and Walks Free', 'url' => 'https://www.nytimes.com/2024/06/25/world/julian-assange-plea-deal-saipan.html'],
                ['title' => 'Reporters Committee for Freedom of the Press — On the Assange plea', 'url' => 'https://www.rcfp.org/assange-plea-deal-2024/'],
                ['title' => 'Knight First Amendment Institute — Statement on Assange plea (Jameel Jaffer)', 'url' => 'https://knightcolumbia.org/blog/assange-plea-2024'],
                ['title' => 'Freedom of the Press Foundation — Statement on Assange release', 'url' => 'https://freedom.press/news/assange-released-2024'],
            ],
        ];
    }

    private function schulte(): array {
        return [
            'slug'         => 'joshua-schulte-40-years-vault-7-sentencing-2024',
            'title'        => '40 Years for Vault 7: Joshua Schulte Receives the Longest Sentence Ever Imposed on a U.S. National-Security Leaker',
            'intro'        => 'On February 1, 2024, a federal judge in Manhattan sentenced former CIA software engineer Joshua Schulte to 40 years in prison for the 2017 "Vault 7" disclosure to WikiLeaks of the agency\'s cyber-weapons toolkit. It is the longest sentence the United States has ever imposed on a national-security leaker — longer than Manning, longer than Reality Winner, longer than Daniel Hale, longer than every other Espionage Act defendant of the modern era combined.',
            'published_at' => '2024-02-02 14:00:00',
            'body'         => <<<'BODY'
<p><em>The Vault 7 release exposed the CIA's offensive-cyber-operations toolkit — the malware, the implants, the zero-day exploits the agency uses against foreign and U.S.-adjacent targets. Schulte was convicted in 2022 of nine counts including espionage and computer-intrusion charges. The 40-year sentence imposed in February 2024 is the federal government's answer to the entire post-Snowden generation of national-security leak prosecutions.</em></p>

<h2>The sentence</h2>

<p>U.S. District Judge Jesse M. Furman, sitting in the Southern District of New York, imposed the sentence on February 1, 2024. Forty years federal time. Schulte will not be eligible for release before his late 70s. He will serve approximately every additional day of his life on supervised release after that.</p>

<h2>The case</h2>

<p>Schulte was a CIA software engineer in the Office of Operations Support / Engineering Development Group (EDG) — the unit that developed the offensive cyber tools the CIA uses against foreign targets. In March 2016 he was reassigned within EDG following internal disputes; in April 2016 he is alleged to have copied the entire EDG repository — the "Vault 7" trove — and transmitted it to WikiLeaks, which began publishing it in March 2017. The disclosure burned the entire CIA cyber-operations toolkit and forced the agency to rebuild from scratch — the largest data loss in CIA history.</p>

<p>Schulte was arrested in 2017 on possession-of-child-sexual-abuse-material charges discovered when the FBI searched his home in connection with the Vault 7 investigation, and indicted on the leak charges in 2018. His first trial (2020) hung on the leak counts. He was convicted at retrial in 2022 of nine counts: illegal gathering and transmission of national-defense information, theft of government property, computer-intrusion offenses, contempt of court, false statements to the FBI, and the child-abuse-material counts. He represented himself in significant portions of the proceedings.</p>

<h2>Where this sits in the leak-prosecution lineage</h2>

<p>The pattern is unmistakable. Daniel Ellsberg, the Pentagon Papers leaker, faced 115 years in 1973; charges were dismissed over Nixon-administration misconduct. Thomas Drake, NSA, 2010: charges dropped to a misdemeanor; no prison. Chelsea Manning, 2013: 35 years, commuted to 7 by President Obama. Edward Snowden: in exile in Russia since 2013, no trial. Jeffrey Sterling, 2015: 42 months. John Kiriakou, CIA, 2013: 30 months. Reality Winner, 2018: 63 months. Daniel Hale, drone whistleblower, 2021: 45 months. Then, in 2024: Joshua Schulte, 40 years.</p>

<p>The fact-pattern that distinguishes Schulte from his predecessors — the CIA's strongest motivation for an exemplary sentence — is the operational severity of the disclosure: he gave WikiLeaks the agency's cyber-weapons themselves, not merely documents describing them. But the gradient is also unmistakable on its face: the U.S. government is escalating, sharply and steadily, the federal-prison cost of disclosing classified material. Schulte's 40 years is the marker for the next prosecution.</p>

<h2>What is not in dispute</h2>

<p>Schulte's case carries complications other leak cases do not. The child-abuse-material conviction is separate and substantial, and is part of why some movement civil-liberties groups have been more cautious in claiming him as a political prisoner. What is in dispute among press-freedom and surveillance-state critics is not whether his conduct in that respect should be punished but whether the leak component of the sentence — most of the 40 years — is calibrated to the offense or calibrated to deter the next leaker. The federal government has answered that question with the largest leak-related sentence in U.S. history.</p>
BODY,
            'citations' => [
                ['title' => 'DOJ press release — Joshua Schulte sentenced to 40 years (Feb 1, 2024)', 'url' => 'https://www.justice.gov/usao-sdny/pr/former-cia-employee-joshua-schulte-sentenced-40-years-prison-vault-7-disclosure'],
                ['title' => 'Reuters — Ex-CIA programmer gets 40 years for Vault 7 leak', 'url' => 'https://www.reuters.com/world/us/ex-cia-programmer-schulte-sentencing-2024-02-01/'],
                ['title' => 'Lawfare — The Joshua Schulte sentencing in context', 'url' => 'https://www.lawfaremedia.org/article/joshua-schulte-sentencing-vault-7'],
                ['title' => 'WikiLeaks — Vault 7 disclosure (March 2017)', 'url' => 'https://wikileaks.org/ciav7p1/'],
            ],
        ];
    }

    private function peltierParole(): array {
        return [
            'slug'         => 'leonard-peltier-parole-denied-july-2024',
            'title'        => 'Leonard Peltier Denied Parole Again: The 49-Year U.S. AIM Case Stays Open',
            'intro'        => 'On July 2, 2024, the U.S. Parole Commission denied Leonard Peltier parole. He has been in federal custody since 1976. He will be 81 in September. The Parole Commission scheduled his next hearing for 2026. The denial sets up the executive clemency push that, six months later, produced President Biden\'s January 20, 2025 commutation.',
            'published_at' => '2024-07-03 10:00:00',
            'body'         => <<<'BODY'
<p><em>Leonard Peltier — Anishinaabe / Lakota American Indian Movement member, convicted in 1977 of the murders of FBI agents Jack Coler and Ronald Williams during the June 26, 1975 Oglala firefight at Pine Ridge, after a trial whose prosecutorial conduct has been condemned by Amnesty International, the United Nations Working Group on Arbitrary Detention, the National Congress of American Indians, the European Parliament, the Dalai Lama, Pope Francis, and dozens of U.S. members of Congress — was denied parole again on July 2, 2024.</em></p>

<h2>The hearing</h2>

<p>Peltier's June 10, 2024 parole hearing — his first since 2009 — was held at USP Coleman I, the Florida federal penitentiary where he has been confined since 2011. He is in failing health. He is diabetic, partially blind, has had an aortic aneurysm, and contracted COVID-19 twice in custody. He uses a walker.</p>

<p>The U.S. Parole Commission issued its decision on July 2: parole denied; reconsideration in fifteen years — i.e., 2039, when Peltier, if alive, would be 95. (The commission later corrected this to a 2026 reconsideration window.) The grounds cited were familiar: the seriousness of the offense, and the commission's assessment that Peltier had not sufficiently expressed remorse for the deaths of Agents Coler and Williams.</p>

<h2>The case the government will not reexamine</h2>

<p>The strength of the federal case against Peltier has been the subject of essentially uninterrupted critique for nearly fifty years. The ballistics evidence that purported to tie his rifle to the bullets that killed the agents was, on the government's own subsequent admission, more equivocal than the prosecution presented at trial. Cooperating witness Myrtle Poor Bear, whose affidavits the government used to extradite him from Canada, was later shown to have not been at Pine Ridge that day — a fact the FBI and DOJ concealed at the time of extradition. The Eighth Circuit Court of Appeals, in <em>Peltier v. Henman</em>, acknowledged that the government had withheld 6,000 pages of FBI material from the defense and held that, had defense counsel possessed that material, the outcome of the trial might have differed. The court denied a new trial anyway. The government has never since reopened the case.</p>

<h2>What the parole denial actually was</h2>

<p>The Parole Commission's July 2024 denial was not a finding that Peltier remained a danger to society. It was, on its face, an assessment that he had not sufficiently expressed remorse. Peltier has maintained his innocence for 48 years. The federal government's position is that he must confess to crimes he denies committing in order to be released. That is not parole. That is a condition the U.S. parole system imposes on political prisoners and almost no one else.</p>

<h2>The road to clemency</h2>

<p>The July 2024 denial galvanized the clemency push that had been quietly building inside the Biden administration's last year. Amnesty International, the NDN Collective, the Indigenous Environmental Network, dozens of tribal governments, and a bipartisan group of former federal prosecutors (including James H. Reynolds, the U.S. Attorney whose office prosecuted Peltier in 1977 and who has since publicly called for his release) intensified the campaign through fall 2024. On January 20, 2025, on his last day in office, President Biden commuted Peltier's sentence to home confinement. Peltier returned to the Turtle Mountain Reservation in North Dakota. The federal conviction was not vacated, the case was not reopened, and the FBI's institutional opposition to his release remains the federal position. But after 49 years he came home.</p>

<p>July 2, 2024 is the day that did not work — the parole denial that, in retrospect, was the federal system's final closure of the orderly route. Clemency was the route that was left.</p>
BODY,
            'citations' => [
                ['title' => 'NDN Collective — Statement on Peltier parole denial (July 2024)', 'url' => 'https://ndncollective.org/statement-on-peltier-parole-denial-july-2024/'],
                ['title' => 'Amnesty International — Leonard Peltier denied parole', 'url' => 'https://www.amnestyusa.org/press-releases/leonard-peltier-parole-denied-july-2024/'],
                ['title' => 'NPR — Leonard Peltier denied parole at age 79', 'url' => 'https://www.npr.org/2024/07/02/leonard-peltier-parole-denied'],
                ['title' => 'James H. Reynolds (former U.S. Attorney) — letter calling for Peltier\'s release', 'url' => 'https://www.whoisleonardpeltier.info/reynolds-letter/'],
                ['title' => 'White House — Biden grants Peltier clemency (Jan 20, 2025)', 'url' => 'https://www.whitehouse.gov/briefing-room/statements-releases/2025/01/20/peltier-clemency/'],
            ],
        ];
    }

    private function hale(): array {
        return [
            'slug'         => 'daniel-hale-drone-whistleblower-2024-clemency-push',
            'title'        => 'Year Three Inside for Daniel Hale: The Drone-Program Whistleblower the U.S. Will Not Free',
            'intro'        => 'Daniel Hale, the former Air Force / NSA intelligence analyst whose leak of the Drone Papers documented for the public the actual lethality rate, civilian-casualty rate, and bureaucratic structure of the U.S. signature-strike drone program, spent all of 2024 inside FCI Marianna. His scheduled release date is July 2026. The PEN America-led clemency push intensified through 2024 and did not produce a Biden-administration commutation.',
            'published_at' => '2024-09-15 09:00:00',
            'body'         => <<<'BODY'
<p><em>Hale pleaded guilty in March 2021 to one count of violating the Espionage Act for transmitting classified information about the U.S. drone-strike program to a journalist. He was sentenced to 45 months in July 2021. He has now spent more than three years in federal custody for telling the public, in essence, what the public has the right to know about how the United States kills people abroad with no due process.</em></p>

<h2>What he leaked</h2>

<p>The Drone Papers — published by The Intercept in October 2015 — were the first comprehensive look at the U.S. signature-strike program from inside the apparatus. The documents Hale provided showed, among other things, that in some operational windows nearly 90% of those killed in U.S. drone strikes were not the intended targets. They showed the kill-chain bureaucracy. They showed the legal cover. They showed the difference between what the U.S. government tells Americans about the drone program and what the drone program actually is. They are the reason a public conversation about U.S. signature-strike warfare existed at all in the late 2010s.</p>

<h2>The prosecution</h2>

<p>Hale was charged in 2019 under the Espionage Act of 1917 — the law the U.S. government has now used against essentially every modern national-security whistleblower. The Espionage Act famously does not permit a "public interest" defense; a jury cannot consider why the defendant disclosed the documents, only whether the disclosure happened. Hale pleaded guilty in March 2021 rather than face the alternative. At sentencing, before Judge Liam O'Grady in the Eastern District of Virginia, he read a statement explaining that the killings he had documented gave him no other choice. He was sentenced to 45 months. He has been at FCI Marianna in Florida since.</p>

<h2>The clemency campaign</h2>

<p>Through 2024, PEN America, the Center for Constitutional Rights, the Government Accountability Project, Reporters Without Borders, the Knight First Amendment Institute, and a broad coalition of press-freedom and human-rights organizations pressed the Biden administration for clemency. Twenty members of Congress, including Senators Wyden and Markey and Representatives Pressley, Bowman, Omar, Bush, and Khanna, signed letters supporting his release. Dozens of former senior intelligence officials — including former CIA officer John Kiriakou (himself a federal-prison alumnus over a torture-program disclosure) — added their names. Biden left office in January 2025 without acting on Hale's clemency petition.</p>

<h2>What this is, plainly</h2>

<p>Daniel Hale is in U.S. federal prison for an act of journalism. He provided documents to a reporter. The documents proved that what the U.S. government told the public about the drone program was not true. The public, in receipt of that information, was able to make better-informed judgments about U.S. foreign policy. There is no version of that sequence in which the United States is the side punishing the right person. His scheduled release date is in July 2026. The federal conviction will stand. The Espionage Act precedent it built is still the law.</p>

<p>NPPC lists Hale on the U.S. political-prisoner roster and will continue to publish updates on his case until he is home.</p>
BODY,
            'citations' => [
                ['title' => 'The Intercept — The Drone Papers (October 2015)', 'url' => 'https://theintercept.com/drone-papers/'],
                ['title' => 'Knight First Amendment Institute — Daniel Hale clemency petition', 'url' => 'https://knightcolumbia.org/cases/daniel-hale-clemency'],
                ['title' => 'PEN America — Free Daniel Hale campaign', 'url' => 'https://pen.org/free-daniel-hale/'],
                ['title' => 'Government Accountability Project — Hale clemency letter', 'url' => 'https://whistleblower.org/hale-clemency-letter-2024/'],
                ['title' => 'DOJ press release — Hale sentenced to 45 months (July 2021)', 'url' => 'https://www.justice.gov/opa/pr/former-intelligence-analyst-sentenced-45-months-prison-disclosing-classified-information'],
            ],
        ];
    }

    private function winner(): array {
        return [
            'slug'         => 'reality-winner-post-release-2024-supervised-release-restrictions',
            'title'        => 'Reality Winner, Three Years Out: The Supervised-Release Restrictions That Have Followed Her Home',
            'intro'        => 'Reality Winner — the former NSA contractor who in 2017 leaked to The Intercept a single classified document on Russian military-intelligence efforts to interfere with U.S. state election systems — was released from federal custody to a halfway house in June 2021 and to home confinement that November. Through all of 2024 she remained on the most restrictive supervised-release conditions imposed on any modern Espionage Act defendant outside of prison.',
            'published_at' => '2024-08-12 09:00:00',
            'body'         => <<<'BODY'
<p><em>Winner served 63 months in federal prison — at the time of her sentencing, the longest sentence ever imposed on a U.S. defendant for an Espionage Act leak. She is home in Texas. She remains subject to supervised-release conditions that limit what she can say publicly, where she can travel, and what professional opportunities she can accept. PEN America and the Whistleblower & Source Protection Program have spent 2024 pressing for her conditions to be reviewed.</em></p>

<h2>The leak that wasn\'t a leak</h2>

<p>What Reality Winner actually did was simple. In May 2017, while working at an NSA contractor facility in Augusta, Georgia, she printed a single classified report describing Russian GRU operations targeting U.S. voting-equipment vendors and election workers in the run-up to the November 2016 U.S. presidential election. She mailed it to The Intercept. The Intercept, in the act of asking the U.S. government for comment, inadvertently disclosed identifying metadata on the printed document that led the FBI to Winner within days. She was arrested in June 2017 and held without bail through her June 2018 sentencing.</p>

<p>The document told American voters something the U.S. government had not told them — that a foreign intelligence service had been actively targeting the infrastructure of the U.S. election. Every published analysis of her case has concluded that her disclosure caused no operational harm beyond inconvenience to the agencies whose secrecy preference it overrode.</p>

<h2>The sentence</h2>

<p>Winner pleaded guilty to one count of violating the Espionage Act and was sentenced in August 2018 to 63 months in federal prison — at the time, the longest sentence ever imposed on a U.S. media-source leaker. She served four-plus years before being released to a halfway house in Bryan, Texas in June 2021. She transferred to home confinement that November and exited federal custody in November 2021.</p>

<h2>The conditions</h2>

<p>Winner is not, in any ordinary sense of the word, free. Her supervised-release conditions include limits on whom she may discuss her case with, what employment she may accept, what travel she may undertake, and what speech she may give publicly. She has been blocked at various points from speaking at journalism schools and at press-freedom events. PEN America, the Knight First Amendment Institute, the Whistleblower & Source Protection Program at ExposeFacts, and the Center for Constitutional Rights have all argued that these restrictions are, in practice, a continuation of the punishment that was supposed to end with her prison release.</p>

<p>Through 2024, the campaign to modify or terminate her supervised-release conditions continued. Films and documentaries about her case — including the 2023 dramatic feature <em>Reality</em> and the 2024 documentary work that followed — kept the public-attention curve alive. No formal modification of conditions has issued. Her supervised-release term is scheduled to expire in November 2024.</p>

<h2>The arithmetic that matters</h2>

<p>A young Air Force veteran with a security clearance read a classified document, decided it described conduct American voters needed to know about, mailed a single copy to a publication, was arrested within days, and served four years and four months in federal prison for it. The information she leaked is now part of the public record on the 2016 election and is uncontested. The Espionage Act gives no defendant a public-interest defense. Winner's case, like Hale's and Manning's, is the architecture inside which every future U.S. national-security source operates.</p>
BODY,
            'citations' => [
                ['title' => 'The Intercept — Top-Secret NSA Report Details Russian Hacking Effort (June 5, 2017)', 'url' => 'https://theintercept.com/2017/06/05/top-secret-nsa-report-details-russian-hacking-effort-days-before-2016-election/'],
                ['title' => 'PEN America — Reality Winner case page', 'url' => 'https://pen.org/case-list/reality-winner/'],
                ['title' => 'Knight First Amendment Institute — Reality Winner amicus', 'url' => 'https://knightcolumbia.org/cases/winner'],
                ['title' => 'Whistleblower & Source Protection Program — Reality Winner supervised release', 'url' => 'https://exposefacts.org/reality-winner-supervised-release/'],
                ['title' => 'NBC News — Reality Winner released to halfway house (June 2021)', 'url' => 'https://www.nbcnews.com/news/us-news/reality-winner-released-prison-halfway-house-rcna1116'],
            ],
        ];
    }

    private function stopCopCityRico(): array {
        return [
            'slug'         => 'stop-cop-city-rico-pretrial-2024-defense-erosion',
            'title'        => 'Stop Cop City: How the Georgia AG\'s 61-Defendant RICO Case Came Apart in 2024',
            'intro'        => 'Georgia Attorney General Chris Carr\'s September 2023 indictment charged 61 Stop Cop City defendants with RICO conspiracy — a sweeping racketeering theory that treated movement organizing as an enterprise and bail-fund work, leafleting, and forest-defense as predicate acts. Over the course of 2024 the case eroded steadily in pretrial motion practice, setting up the eventual dismissal of the racketeering theory in 2025.',
            'published_at' => '2024-11-15 11:00:00',
            'body'         => <<<'BODY'
<p><em>The Atlanta Public Safety Training Center — known to its opponents as Cop City — has been the subject of one of the largest state-level prosecutions of an environmental movement in U.S. history. Carr\'s 2023 racketeering case was the apex of that effort. Through 2024 it visibly came apart.</em></p>

<h2>The indictment</h2>

<p>The September 5, 2023 indictment named 61 defendants. The state\'s theory was that the broad multi-tendency movement against the Atlanta training-center project — encampments in the Weelaunee Forest, bail-fund work, mutual-aid organizing, leafleting, demonstrations at corporate offices, mass-mobilization weeks of action — constituted a racketeering enterprise within the meaning of Georgia\'s RICO statute. Predicate acts charged ranged from felony intimidation to misdemeanor trespass to handing out fliers. Three defendants associated with the Atlanta Solidarity Fund were charged separately and earlier on money-laundering and charity-fraud theories tied to bail-fund accounting.</p>

<p>The volume of the indictment — 109 pages, 220+ overt acts, 61 named defendants — was, in critics\' reading, less a charging document than a charging-as-suppression document. The point was the prosecution itself, not the convictions.</p>

<h2>The 2024 pretrial year</h2>

<p>Through 2024, defense counsel pressed motions on the threshold question that would eventually decide the case: whether the Georgia Attorney General had the statutory authority to bring a RICO prosecution at all without express written authorization from the governor, as Georgia law on its face requires. Carr\'s office had not obtained that authorization. The state\'s response was that the authorization requirement was a formality that could be cured at any point.</p>

<p>Through the year defendants moved out of pretrial conditions, were severed from larger groups, fought bond and travel restrictions, and slowly forced the state to litigate the case it had filed. Press attention, after the chaotic first months of the indictment, normalized into beat coverage by Atlanta independent media (the Atlanta Press Collective and Mainline) and national outlets (the Intercept, the Marshall Project, Bolts).</p>

<p>By the end of 2024 the case was structurally weakened. The state had not produced a coherent theory of which defendants were enterprise members and which were merely associates. The bail-fund-related counts against the Atlanta Solidarity Fund defendants were under separate motion. The state\'s reliance on movement attendance as predicate acts had been challenged on First Amendment grounds in multiple briefs.</p>

<h2>The 2025 dismissal — built in 2024</h2>

<p>The case as a racketeering matter was effectively over by spring 2025, when a Fulton County Superior Court judge dismissed it on the authorization point that defense counsel had built throughout 2024. The dismissal was the result of the year of work the public never saw — motion practice, jurisdictional research, coordinated defense strategy across 61 defendants and dozens of attorneys.</p>

<p>The state has since pivoted to narrower charging instruments — including the April 2026 Cobb County indictment of three protest defendants for a 2022 action at Brasfield & Gorrie\'s Atlanta headquarters — but the broad RICO theory that defined the 2023–2024 phase of the prosecution has been retired. The Stop Cop City movement, with multiple of its named defendants still facing serious individual exposure, treats the 2024 erosion of the RICO case as one of the most consequential pretrial-defense victories of the decade.</p>
BODY,
            'citations' => [
                ['title' => 'UNICORN RIOT — Cop City RICO indictment coverage', 'url' => 'https://unicornriot.ninja/category/atlanta-public-safety-training-center/'],
                ['title' => 'The Marshall Project — Inside the Cop City prosecution', 'url' => 'https://www.themarshallproject.org/2024/cop-city-rico-prosecution'],
                ['title' => 'Bolts Magazine — Georgia\'s "Stop Cop City" prosecution test', 'url' => 'https://boltsmag.org/cop-city-georgia-rico-2024/'],
                ['title' => 'Atlanta Press Collective — Cop City RICO docket coverage', 'url' => 'https://atlpresscollective.com/tag/rico/'],
                ['title' => 'Defend the Atlanta Forest — Legal updates', 'url' => 'https://defendtheatlantaforest.org/legal/'],
            ],
        ];
    }

    private function atlantaSolidarityFund(): array {
        return [
            'slug'         => 'atlanta-solidarity-fund-organizers-2024-bail-fund-charges',
            'title'        => 'Year Two of the Atlanta Solidarity Fund Prosecution: The State Is Trying to Criminalize Bail Funds',
            'intro'        => 'Marlon Kautz, Adele Maclean, and Savannah Patterson — three organizers of the Atlanta Solidarity Fund — entered 2024 fighting state money-laundering and charity-fraud charges over their bail-fund work for Stop Cop City defendants. The case is the most aggressive U.S. attempt in the post-Ferguson era to criminalize the act of bailing protesters out.',
            'published_at' => '2024-10-08 13:00:00',
            'body'         => <<<'BODY'
<p><em>The Atlanta Solidarity Fund had been functioning as a movement bail fund for nearly fifteen years before Georgia Attorney General Chris Carr indicted its three named organizers in May 2023 on money-laundering and charity-fraud counts arising from how they accounted for the money they used to bail out Stop Cop City defendants. Through all of 2024 the three remained under indictment, on bond, and fighting the case.</em></p>

<h2>What the fund did</h2>

<p>The Atlanta Solidarity Fund, organized as a project of the Network for Strong Communities, provided bail and legal-support funds for arrested protesters in Atlanta — anti-police-violence demonstrators, environmental defenders, racial-justice organizers, and beginning in 2021–2023 a large cohort of Stop Cop City forest defenders. Bail funds are a long-standing U.S. social-movement infrastructure dating to the National Bail Out collective and earlier; they exist because the U.S. cash-bail system makes pretrial freedom a function of wealth.</p>

<h2>The prosecution</h2>

<p>In May 2023 the Georgia State Patrol arrested Kautz, Maclean, and Patterson at their shared home in Atlanta on charges of money laundering and charity fraud — the state\'s theory being that reimbursing forest-defender expenses (camping supplies, food, gas) constituted laundering "racketeering proceeds" and that listing those reimbursements in the fund\'s accounting amounted to charity fraud. Bail was set at $15,000 each. They were among the first defendants in U.S. history to be criminally charged for the act of organizing bail support.</p>

<p>The three were later added as defendants in the September 2023 RICO indictment that swept up 61 Stop Cop City organizers. Their cases have proceeded on parallel tracks — the separate Atlanta Solidarity Fund charging and the broader RICO inclusion — both pressed by Carr\'s office and both increasingly understood by national civil-liberties observers as a test prosecution.</p>

<h2>What civil-society organizations did in 2024</h2>

<p>The case attracted broad opposition across civil society in 2024. The Movement for Black Lives, the National Lawyers Guild, the ACLU, the Center for Constitutional Rights, the Bail Project, the Community Justice Exchange, and dozens of regional bail funds signed letters condemning the prosecution. Forty-two civil-rights organizations filed an open letter to Attorney General Merrick Garland in 2024 asking the federal government to publicly disavow the state\'s theory. The Inter-American Commission on Human Rights opened a formal monitoring file. The U.N. Special Rapporteur on Human Rights Defenders raised concerns.</p>

<p>None of it has caused the state of Georgia to drop the charges.</p>

<h2>Why this case matters beyond Atlanta</h2>

<p>If the state of Georgia can hold bail-fund organizers criminally liable for the act of bailing out protesters, the legal theory will travel. Every state has post-Ferguson bail funds. Every protest movement of the next decade will face the same calculation: whether to organize bail support knowing that doing so could itself become a charging instrument. The Atlanta Solidarity Fund prosecution is being watched, accordingly, by every U.S. movement-defense organization at the level of an existential test case.</p>

<p>As of end-of-2024 the case remains unresolved. The defendants continue to operate the fund. NPPC tracks the case and publishes updates as the matter moves through pretrial.</p>
BODY,
            'citations' => [
                ['title' => 'Atlanta Community Press Collective — Solidarity Fund coverage', 'url' => 'https://atlpresscollective.com/tag/atlanta-solidarity-fund/'],
                ['title' => 'ACLU of Georgia — On the criminalization of bail funds', 'url' => 'https://www.acluga.org/atlanta-solidarity-fund-2024'],
                ['title' => 'The Intercept — Georgia Charges Bail Fund Organizers', 'url' => 'https://theintercept.com/2023/06/01/georgia-cop-city-bail-fund-charges/'],
                ['title' => 'Community Justice Exchange — Statement', 'url' => 'https://communityjusticeexchange.org/atlanta-solidarity-fund-statement'],
                ['title' => '42-org civil-rights letter to AG Garland (2024)', 'url' => 'https://m4bl.org/letter/atlanta-solidarity-fund-garland-2024/'],
            ],
        ];
    }

    private function tortuguitaCivil(): array {
        return [
            'slug'         => 'tortuguita-teran-one-year-civil-investigation-2024',
            'title'        => "One Year After Tortuguita: The Civil Case the State of Georgia Is Trying to Avoid",
            'intro'        => 'On January 18, 2023, Georgia State Patrol troopers killed Manuel "Tortuguita" Terán in a clearing in the Weelaunee Forest — the first U.S. environmental defender to be shot and killed by police in modern memory. Through 2024, the Terán family\'s civil-rights lawsuit, the family-commissioned independent autopsy, and the Stop Cop City legal-defense network kept the case alive against the state of Georgia\'s effort to close it.',
            'published_at' => '2024-01-18 09:00:00',
            'body'         => <<<'BODY'
<p><em>Tortuguita was 26. They were a non-violent environmental defender — Venezuelan-born, raised in the United States and Panama, trained as a teacher, living in a tent in the Atlanta forest the city was about to clear-cut for the Public Safety Training Center known to its opponents as Cop City. Six Georgia State Patrol troopers opened fire on them at close range during a multi-agency "clearing operation" on the morning of January 18, 2023. They were shot 57 times.</em></p>

<h2>The state\'s account</h2>

<p>The Georgia Bureau of Investigation and the Georgia State Patrol claimed Tortuguita shot at troopers first, wounding one. They produced no body-camera footage of the encounter — Georgia State Patrol troopers were not at the time required to wear body cameras and were not wearing them on the morning of the shooting. They produced no clear photographic record of the moments preceding the shooting. They produced a GSP-commissioned ballistics analysis that they said tied the bullet that wounded the trooper to a handgun Tortuguita is alleged to have purchased.</p>

<h2>The independent record</h2>

<p>The Terán family commissioned an independent autopsy by Dr. Kris Sperry, a former Georgia chief medical examiner. The Sperry autopsy found that Tortuguita had been shot at least 57 times; that the entry wounds were consistent with their hands being raised; and that the trajectory of certain wounds was inconsistent with them firing a weapon at the moment of the shooting. The DeKalb County medical examiner\'s own later autopsy reached similar conclusions on the hands-raised question.</p>

<p>Through 2023 and 2024, independent journalists (Unicorn Riot, the Atlanta Community Press Collective, the Marshall Project), legal observers, and civil-rights litigators built out a parallel evidentiary record from open-source mapping, body-camera footage from cooperating officers (some of which contradicted the GSP\'s account), and audio recordings.</p>

<h2>The civil case</h2>

<p>The Terán family filed a federal civil-rights suit against the state of Georgia and the individual troopers in 2023. Through 2024, the suit moved through early discovery — with the state contesting essentially every disclosure obligation, claiming sovereign immunity, qualified immunity, and the unavailability of trooper depositions. The family\'s lawyers, including Brian Spears and Jeff Filipovits, continued to press. The case is on a multi-year track.</p>

<h2>What 2024 actually was</h2>

<p>The first anniversary of Tortuguita\'s death was marked by memorial actions in Atlanta, across the United States, and in Panama and Venezuela. The campaign to compel the state of Georgia to release the body-camera and forensic record continued. The case was named in U.N. Special Rapporteur communications on environmental defenders. The Stop Cop City legal-defense network grew.</p>

<p>The state of Georgia has not, as of the end of 2024, charged any officer involved in the shooting. The DeKalb County district attorney announced in 2023 he would not pursue charges. The fact pattern that has been built into the public record — 57 rounds at close range, no body-camera footage, contested ballistics, hands consistent with surrender — is now part of the durable historical record. The state\'s account is part of that record too. They are not consistent with each other.</p>

<p>NPPC marks January 18 every year and will continue to publish on the Terán case as the civil suit moves through federal court.</p>
BODY,
            'citations' => [
                ['title' => 'UNICORN RIOT — Tortuguita case coverage', 'url' => 'https://unicornriot.ninja/tag/tortuguita/'],
                ['title' => 'The Intercept — The killing of Manuel Terán', 'url' => 'https://theintercept.com/2023/03/13/cop-city-tortuguita-killing/'],
                ['title' => 'DeKalb County Medical Examiner — Terán autopsy (full report)', 'url' => 'https://www.dekalbcountyga.gov/medical-examiner/teran-autopsy'],
                ['title' => 'Sperry independent autopsy (Terán family release)', 'url' => 'https://defendtheatlantaforest.org/teran-independent-autopsy/'],
                ['title' => 'Atlanta Community Press Collective — Terán memorial coverage', 'url' => 'https://atlpresscollective.com/tag/tortuguita/'],
            ],
        ];
    }

    private function forestDefenderPrecedent(): array {
        return [
            'slug'         => 'forest-defender-domestic-terrorism-charging-precedents-2024',
            'title'        => 'The Domestic-Terrorism Template the Cop City Prosecution Built — And Is Now Being Run Against Everyone Else',
            'intro'        => 'Through 2024, U.S. federal and state prosecutors began applying the charging architecture built in the Stop Cop City prosecution — domestic-terrorism enhancements on non-violent environmental defense, RICO theories on movement organizing, conspiracy charges on coalition-building — to other contemporary movements. The Cop City case was a template. By the end of 2024 it had been copied.',
            'published_at' => '2024-12-05 11:00:00',
            'body'         => <<<'BODY'
<p><em>The Atlanta Forest defenders were not the first U.S. environmental movement the federal government charged as terrorists. The Earth Liberation Front and Animal Liberation Front prisoners of the 2005–2008 Green Scare were. What 2023–2024 produced was a re-deployment of that architecture at scale, and against a wider range of movements.</em></p>

<h2>What the template is</h2>

<p>Four moves, refined in the Cop City prosecution and now visible in other 2024 cases:</p>

<ol>
  <li><strong>Domestic-terrorism enhancement on non-violent property-defense action.</strong> Trespass, occupation of public land, sabotage of construction equipment — none of these would normally produce a terrorism charge. The Cop City prosecutors applied Georgia\'s domestic-terrorism statute to forest-encampment defendants and won the precedent in pretrial rulings.</li>
  <li><strong>RICO theory on coalition movements.</strong> By treating a multi-tendency movement (encampments + bail funds + leafleting + protest weeks) as a racketeering enterprise, the state can charge any individual as a member of the whole. Predicate acts can be as minor as handing out a flier.</li>
  <li><strong>Money-laundering and charity-fraud on bail-fund organizers.</strong> The Atlanta Solidarity Fund prosecution applied this. By 2024 the theory was sitting on the shelf for any state that wanted to take it down.</li>
  <li><strong>Federal grand-jury sweeps in parallel.</strong> Even where state cases stall, federal grand juries can be empaneled to extract testimony from organizers under contempt threat. The post-Oct-2023 Palestine solidarity wave has produced new examples.</li>
</ol>

<h2>Where the template traveled in 2024</h2>

<p><strong>Prairieland (Texas, late 2024)</strong>: Texas state prosecutors brought aggravated-trespass and conspiracy charges against pipeline-protest defendants at the proposed Prairieland Energy fracked-gas export terminal, citing Cop City case law in their pleadings. Defense counsel filed motions that explicitly track the Stop Cop City First Amendment briefing.</p>

<p><strong>Palestine solidarity (multiple jurisdictions, 2024)</strong>: Federal and state prosecutors began applying conspiracy and "material support" theories to Palestine solidarity organizers — weapons-manufacturer blockaders, campus-encampment leaders, fundraising coordinators. The October 2024 federal grand-jury sweep targeting Samidoun and adjacent Palestinian-rights organizations relied on the same coalition-as-conspiracy logic Carr\'s office had pioneered.</p>

<p><strong>Coastal GasLink / Wet\'suwet\'en (U.S.-Canada, 2024)</strong>: U.S. federal courts have begun handling cross-border solidarity charges arising from the Wet\'suwet\'en pipeline-defense matter using language borrowed from the domestic-terrorism Cop City framework.</p>

<p><strong>Climate Defenders Toolkit organizers (multiple states, 2024)</strong>: Several U.S. climate-defense legal-support organizations reported grand-jury subpoenas in 2024 seeking organizing documents, donor lists, and internal communications. The theory is the same as the Solidarity Fund theory: that supporting a movement is, with the right framing, participating in a conspiracy.</p>

<h2>What civil society has been saying</h2>

<p>The ACLU, the Center for Constitutional Rights, the Movement for Black Lives, the Climate Defense Project, the Sunrise Movement\'s legal counsel, and the U.N. Special Rapporteur on the rights to freedom of peaceful assembly and association have all spent 2024 documenting and pushing back on this expansion. The pattern they describe is consistent: U.S. domestic-terrorism law, originally constructed in the post-September-11 moment to target a narrow class of national-security threats, is being deployed against the U.S. environmental, anti-police, and Palestine-solidarity movements as a routine charging tool.</p>

<p>The Cop City defendants who fought this template in Georgia spent 2024 building the legal architecture by which the next movement\'s defendants will fight it. The next movements are already in court.</p>
BODY,
            'citations' => [
                ['title' => 'Climate Defense Project — Stop Cop City charging precedents', 'url' => 'https://climatedefenseproject.org/cop-city-domestic-terrorism-precedent/'],
                ['title' => 'ACLU — On state domestic-terrorism statutes against protesters', 'url' => 'https://www.aclu.org/news/free-speech/state-anti-protest-laws-2024'],
                ['title' => 'Center for Constitutional Rights — Protest-defense litigation', 'url' => 'https://ccrjustice.org/home/what-we-do/issues/protest-rights'],
                ['title' => 'The Intercept — From Cop City to Prairieland', 'url' => 'https://theintercept.com/2024/11/cop-city-prairieland-template/'],
                ['title' => 'UN Special Rapporteur on FAA — Communications to U.S. on protest prosecutions', 'url' => 'https://www.ohchr.org/en/special-procedures/sr-freedom-of-assembly-and-of-association'],
            ],
        ];
    }

    private function campusEncampments(): array {
        return [
            'slug'         => 'campus-encampment-mass-arrests-spring-2024',
            'title'        => 'The Spring 2024 Campus Encampment Arrests: 3,100 Students, Faculty, and Workers Detained for Pro-Palestine Organizing',
            'intro'        => 'Between April 17, 2024 (the Columbia encampment\'s first day) and the end of the spring semester, U.S. police arrested more than 3,100 people at pro-Palestine campus encampments across the country. Faculty were tackled, students were suspended, graduations were canceled, and the post-1968 modern record for mass-arrest mobilization at U.S. universities was, quietly, broken.',
            'published_at' => '2024-05-30 10:00:00',
            'body'         => <<<'BODY'
<p><em>The spring 2024 U.S. campus Gaza solidarity encampments were the largest student-protest mobilization since the anti-Vietnam-War movement. The institutional response — police raids, mass arrests, faculty firings, disciplinary suspensions, graduation cancellations — was the largest U.S. university-administration repression response in the same period.</em></p>

<h2>The arc</h2>

<p>The encampments began at Columbia University on the morning of April 17, 2024, when students set up tents on the South Lawn demanding the university divest from companies invested in the Israeli military and disclose its endowment\'s ties to Israel\'s war on Gaza. President Minouche Shafik called the NYPD onto campus the following day; 108 students were arrested. The encampment reformed within hours. The model — tent-based occupation of an iconic campus space, formal demands focused on divestment, faculty-led negotiation, internal mutual-aid systems — was copied within a week at more than 130 U.S. universities.</p>

<p>By the end of April: Yale, NYU, Northwestern, Brown, USC, Emory, the University of Texas at Austin, Princeton, MIT, the University of Michigan, the University of Wisconsin, Cal Poly Humboldt, Indiana University, the University of California system, the City University of New York system, Vanderbilt, Washington University in St. Louis, the University of Florida, the University of Pennsylvania, Harvard, and dozens more.</p>

<h2>The raids</h2>

<p>The on-campus police responses varied in violence but converged in template:</p>

<ul>
  <li><strong>Columbia / Hamilton Hall (April 30 night)</strong>: NYPD officers in tactical gear raided the building students had occupied; 282 arrested.</li>
  <li><strong>UCLA (May 1 night)</strong>: A pro-Israel counter-mob attacked the encampment for hours while LAPD declined to intervene. Police then cleared the encampment, with arrests of demonstrators rather than the assailants.</li>
  <li><strong>Emory University (April 25 morning)</strong>: Georgia State Patrol troopers in tactical gear cleared the encampment within hours; faculty member Caroline Fohlin was visibly tackled and arrested in widely-circulated footage.</li>
  <li><strong>UT Austin (April 24)</strong>: Mounted state troopers and DPS officers swept the encampment; 79 arrested.</li>
  <li><strong>USC (April 24)</strong>: 90+ arrested; commencement valedictorian Asna Tabassum stripped of her speaking role.</li>
  <li><strong>The City College of New York (April 30 night)</strong>: NYPD cleared encampment in coordinated raid with Columbia.</li>
</ul>

<h2>The numbers</h2>

<p>By independent estimates compiled by the Crowd Counting Consortium, the Brennan Center, the Civil Liberties Defense Center, and the National Lawyers Guild, between 3,100 and 3,300 people were arrested in U.S. campus pro-Palestine actions between April 17 and the end of the academic year. Thousands more faced disciplinary suspensions, dismissals from on-campus housing, withholding of degrees, and bans from campus property. Dozens of faculty — across the U.S. — were placed on leave, sanctioned, denied tenure, or fired in connection with their support of the encampments.</p>

<h2>What followed</h2>

<p>The misdemeanor and felony cases arising from the spring 2024 arrests proceeded through summer and fall 2024 in dozens of jurisdictions. Most resolved with non-criminal dispositions — community service, ACDs, dismissals — but a substantial minority moved to bench trials and contested motions, and produced a body of First Amendment / curtilage / use-of-force litigation that has shaped public-space protest law going forward. The expulsion and degree-withholding sanctions, which fall outside the criminal-legal system, were in many cases more durable than the arrests themselves.</p>

<p>NPPC tracks the still-pending criminal cases and lists named defendants whose prosecutions are ongoing. The full census of 2024 campus encampment arrestees is one of the largest U.S. mass-arrest cohorts of the post-Ferguson decade and a defining piece of the year\'s political-prisoner record.</p>
BODY,
            'citations' => [
                ['title' => 'Crowd Counting Consortium — Spring 2024 campus protest dataset', 'url' => 'https://crowdcounting.org/data/campus-2024'],
                ['title' => 'AP — Tally of arrests at U.S. campus protests passes 2,400', 'url' => 'https://apnews.com/article/college-campus-protests-arrests-tally-2024'],
                ['title' => 'PEN America — On the 2024 university response', 'url' => 'https://pen.org/campus-protest-response-2024/'],
                ['title' => 'Civil Liberties Defense Center — Campus encampment legal support', 'url' => 'https://cldc.org/campus-encampment-support-2024/'],
                ['title' => 'National Lawyers Guild — Mass-arrest support documentation', 'url' => 'https://www.nlg.org/2024-campus-encampments/'],
            ],
        ];
    }

    private function merrimackFour(): array {
        return [
            'slug'         => 'merrimack-4-elbit-blockade-new-hampshire-defendants',
            'title'        => 'The Merrimack 4: New Hampshire\'s Felony Charging of Pro-Palestine Weapons-Manufacturer Blockaders',
            'intro'        => 'On November 20, 2023, four pro-Palestine activists blockaded the Merrimack, New Hampshire facility of Elbit Systems of America — the U.S. subsidiary of the Israeli defense contractor whose drones, surveillance equipment, and weaponry are used in Gaza. New Hampshire state prosecutors charged them with felony riot, criminal mischief, and trespass. Their case moved through 2024 as a national test of how aggressively a small Republican-trifecta state would prosecute a non-violent factory action.',
            'published_at' => '2024-09-10 12:00:00',
            'body'         => <<<'BODY'
<p><em>Elbit Systems of America is the U.S. subsidiary of Israel\'s largest defense contractor. Its New Hampshire facility manufactures drone components and electronic warfare equipment used in Israeli operations in Gaza. On the morning of November 20, 2023, four activists chained themselves to the facility\'s doors and to construction equipment, halting operations for several hours. The action was part of a multi-city Palestine Action US campaign targeting Elbit\'s North American footprint.</em></p>

<h2>The defendants</h2>

<p>The four defendants — known by supporters as the Merrimack 4 — are <strong>Sophie Ross</strong>, <strong>Calla Walsh</strong>, <strong>Bridget Shergalis</strong>, and <strong>Paige Belanger</strong>. All four are young women in their early 20s; all four had no prior felony record. New Hampshire prosecutors charged each with felony riot under RSA 644:1, felony criminal mischief over $1,500 of damage, and misdemeanor criminal trespass. The maximum exposure on the felony riot count alone is seven years state-prison time.</p>

<h2>Why the charging is unusual</h2>

<p>Felony riot prosecutions of non-violent blockaders are rare. The statute typically requires that the defendants joined a group of at least six engaged in disorderly conduct that threatened public safety. The Merrimack 4 were four people locked to objects on private commercial property. Defense counsel from the National Lawyers Guild argued through 2024 that the charge as applied was a clear over-extension designed to inflate criminal exposure and chill the Palestine Action US campaign.</p>

<p>The criminal-mischief felony charge rested on the state\'s damage valuation, which defense counsel disputed in pretrial motions. The state alleged $1,500+ in damage; defense moved to compel the state to produce repair invoices and documentary substantiation.</p>

<h2>The 2024 docket</h2>

<p>Through 2024 the case moved through arraignment, pretrial motions, and discovery in Hillsborough Superior Court — Southern District in Nashua. The defendants raised the necessity defense (action taken to prevent imminent harm — in this case, Elbit\'s contribution to civilian deaths in Gaza); the state moved to bar the defense at trial. Pretrial rulings on the necessity defense became, themselves, a national-organizing focal point for the Palestine Action US campaign and adjacent direct-action networks.</p>

<p>National civil-liberties and movement-defense organizations — the Center for Constitutional Rights, the National Lawyers Guild Mass Defense Committee, the Civil Liberties Defense Center — provided amicus and legal support. Fundraising for the Merrimack 4 defense was coordinated through Defend the Merrimack 4.</p>

<h2>The pattern</h2>

<p>The Merrimack 4 prosecution sits alongside parallel U.S. felony-charging escalations of Palestine-solidarity weapons-manufacturer blockaders in 2024 — at Pittsburgh\'s Boeing facility, at New Jersey\'s Lockheed Martin operations, at the Holtec nuclear-components plant in New Jersey, at the General Dynamics facility in Vermont. The pattern is consistent: state and federal prosecutors charging non-violent factory blockades at the highest possible end of the available statute, in jurisdictions where the political balance permits a tough-on-protest framing.</p>

<p>As of end-of-2024 the Merrimack 4 case remained pending. NPPC tracks the matter and will publish updates as the cases move toward trial. The defendants\' campaign asks supporters to attend court days, contribute to legal-defense funds, and amplify the Defend the Merrimack 4 channels.</p>
BODY,
            'citations' => [
                ['title' => 'Defend the Merrimack 4 — Campaign site', 'url' => 'https://defendthemerrimack4.org'],
                ['title' => 'Mondoweiss — Merrimack 4 face years in prison', 'url' => 'https://mondoweiss.net/2024/merrimack-4-elbit-prosecution/'],
                ['title' => 'National Lawyers Guild — Merrimack 4 amicus', 'url' => 'https://www.nlg.org/merrimack-4-amicus/'],
                ['title' => 'New Hampshire Public Radio — Charges in Elbit blockade', 'url' => 'https://www.nhpr.org/nh-news/2023-11-21/elbit-merrimack-arrest-charges'],
                ['title' => 'Palestine Action US — Campaign updates', 'url' => 'https://palestineaction.us/merrimack-4/'],
            ],
        ];
    }

    private function weaponsBlockaders(): array {
        return [
            'slug'         => 'weapons-manufacturer-blockaders-pittsburgh-holtec-boeing-2024',
            'title'        => 'The Weapons-Manufacturer Blockade Wave: 2024 Felony Charging of Pro-Palestine Direct Action at U.S. Defense Plants',
            'intro'        => 'Through 2024, U.S. local prosecutors escalated felony charges against pro-Palestine activists who blockaded weapons-manufacturing facilities across the country — Boeing in Pittsburgh, Holtec in New Jersey, Lockheed Martin in multiple cities, General Atomics in San Diego, Northrop Grumman in Maryland. The cases test how much state criminal exposure the U.S. can be persuaded to load onto a non-violent factory-gate action against the war on Gaza.',
            'published_at' => '2024-10-22 13:00:00',
            'body'         => <<<'BODY'
<p><em>The model is straightforward and old: activists chain themselves or lock their bodies to the gates of a weapons-manufacturing facility, halting operations for hours, and accept arrest as part of the action. The model goes back to the 1980s Plowshares actions and to the 1960s direct-action tradition before that. What is new in 2024 is the consistency of felony-level charging.</em></p>

<h2>The 2024 actions</h2>

<p>A non-exhaustive list of major U.S. weapons-plant blockades through 2024 that produced felony charges or substantial misdemeanor cases:</p>

<ul>
  <li><strong>Boeing Pittsburgh (multiple actions across 2024)</strong>: U.S. Palestinian Community Network and Pittsburgh Palestine Coalition activists shut down access to Boeing\'s Allegheny County campus. Felony obstruction and conspiracy charges filed against named organizers.</li>
  <li><strong>Holtec International (Camden, NJ, multiple actions)</strong>: Defenders blockading the parking-lot entrance for the nuclear-components and energy contractor faced state criminal-mischief and conspiracy charges.</li>
  <li><strong>Lockheed Martin (multiple cities — Bethesda, Fort Worth, Marietta, Syracuse)</strong>: 2024 saw arrests at Lockheed facilities ranging from misdemeanor trespass dispositions to (in some Texas cases) state felony charging.</li>
  <li><strong>General Atomics (San Diego)</strong>: Drone-manufacturer plant blockaded by Veterans for Peace and CodePink coalition activists. Misdemeanor and infraction dispositions.</li>
  <li><strong>Northrop Grumman (Linthicum, MD)</strong>: Maryland blockades produced state misdemeanor charges with negotiated dispositions.</li>
  <li><strong>RTX/Raytheon (Tucson, multiple actions)</strong>: Catholic Worker–aligned Plowshares-tradition activists faced Arizona state felony charging.</li>
</ul>

<h2>The legal pattern</h2>

<p>Local U.S. prosecutors in 2024 — particularly in states with hostile political climates for pro-Palestine organizing — have favored stacking charges: felony rioting, felony criminal mischief over a damage-valuation threshold, felony conspiracy, and the residual misdemeanors. The Merrimack 4 (New Hampshire) and several Pittsburgh and Camden cases have been the most visible felony stacks. The point appears to be deterrence by pretrial exposure: even where prosecutors do not expect to win on the high-end charges, the threat of multi-year sentences keeps defendants in plea-bargain pressure for months.</p>

<p>Defense counsel from the National Lawyers Guild, the Center for Constitutional Rights, and the Mass Defense Committee have been coordinating across jurisdictions to share necessity-defense briefs, damage-valuation challenges, and First Amendment / public-forum motions.</p>

<h2>The lineage</h2>

<p>The current wave of U.S. weapons-plant blockades belongs to a long lineage that prosecutors have prosecuted just as aggressively in earlier decades. The 1980 Plowshares Eight; the 2002 Riverside Plowshares; the 2012 Y-12 Plowshares action by Sister Megan Rice, Greg Boertje-Obed, and Michael Walli (89-year-old Rice was sentenced to nearly three years on federal sabotage charges later vacated on appeal); the 2018 Kings Bay Plowshares 7. Every generation of weapons-plant defenders has faced this prosecutorial pattern. What 2024 brought was the application of that pattern to a notably younger, broader, and more racially-mixed cohort of Palestine-solidarity defendants. The historical lineage is theirs to claim.</p>

<p>NPPC tracks weapons-plant blockade prosecutions and publishes updates as the 2024 cases move toward trial.</p>
BODY,
            'citations' => [
                ['title' => 'CodePink — Weapons plant blockade campaign', 'url' => 'https://www.codepink.org/divestfrombombing'],
                ['title' => 'National Lawyers Guild — Mass Defense Committee', 'url' => 'https://www.nlg.org/massdefense/'],
                ['title' => 'Civil Liberties Defense Center — Weapons plant blockade support', 'url' => 'https://cldc.org/weapons-plant-blockades-2024/'],
                ['title' => 'The Intercept — Pro-Palestine blockades hit defense plants nationwide', 'url' => 'https://theintercept.com/2024/05/defense-plant-blockades-palestine/'],
                ['title' => 'Jewish Voice for Peace — Action gallery', 'url' => 'https://www.jewishvoiceforpeace.org/actions/2024-blockades/'],
            ],
        ];
    }

    private function grandJuryWave(): array {
        return [
            'slug'         => 'federal-grand-jury-wave-palestine-solidarity-2024',
            'title'        => 'The Federal Grand-Jury Wave Against U.S. Palestine Solidarity Organizers (2024)',
            'intro'        => 'Beginning in early 2024, the U.S. Department of Justice empaneled multiple federal grand juries targeting Palestine solidarity organizers — issuing subpoenas for testimony, financial records, and internal organizational communications. The grand-jury phase preceded and prepared the way for the 2025 escalation that placed Mahmoud Khalil, Rumeysa Öztürk, and others into immigration detention.',
            'published_at' => '2024-11-18 11:00:00',
            'body'         => <<<'BODY'
<p><em>Federal grand juries are a uniquely powerful and uniquely insulated instrument of investigation. They permit prosecutors to compel testimony under penalty of contempt, to operate in secrecy, and to require witnesses to appear without counsel inside the grand-jury room itself. Through 2024, the DOJ used them against U.S. Palestine solidarity organizing networks at a pace not seen since the post-September-11 federal grand-jury campaign against U.S. Muslim charities and the 2010 Chicago / Minneapolis federal grand-jury sweep of antiwar and Palestine-solidarity activists.</em></p>

<h2>What 2024 produced</h2>

<p>By the end of 2024, federal grand-jury subpoenas had been served on or in connection with:</p>

<ul>
  <li><strong>Samidoun (Palestinian Prisoner Solidarity Network)</strong> — The Treasury Department designated Samidoun as a "specially designated global terrorist" in October 2024; in parallel, a federal grand jury (Southern District of New York) sought records of U.S.-based associates, donors, and event organizers.</li>
  <li><strong>U.S. Palestinian Community Network (USPCN)</strong> — Subpoenas served on regional chapter coordinators seeking organizing documents and communications.</li>
  <li><strong>Multiple campus-encampment-affiliated student organizations</strong> — Federal subpoenas in spring/summer 2024 sought university-association records and donor lists in connection with what prosecutors characterized as "material support" investigations.</li>
  <li><strong>Bay Area, New York, and Chicago Palestine-solidarity organizers individually</strong> — A pattern of subpoenas seeking testimony about meetings, attendance lists, and financial transfers.</li>
</ul>

<h2>The "material support" theory</h2>

<p>The federal statute being deployed in most of these matters is 18 U.S.C. § 2339B — providing "material support or resources" to a designated foreign terrorist organization. The statute is famously broad: the Supreme Court in <em>Holder v. Humanitarian Law Project</em> (2010) held that even pure speech in coordination with a designated organization can constitute material support. The Samidoun designation in October 2024 was the key step that made the statute usable against U.S. Palestine solidarity organizing on a scale not previously possible.</p>

<h2>What happened in the rooms</h2>

<p>Several subpoenaed organizers — supported by counsel from the Center for Constitutional Rights, Palestine Legal, the National Lawyers Guild, and the Civil Liberties Defense Center — refused to testify, invoking First and Fifth Amendment grounds and the long radical tradition of grand-jury refusal that dates to the Puerto Rican Independence movement, the Black Panther grand-jury cases of the 1970s, the 1980s Plowshares grand-jury resistance, the 2010 Carlos Montes / Chicago 14 case, and the 2019 WikiLeaks grand-jury contempt of Chelsea Manning. As of end-of-2024, several grand-jury witnesses faced contempt referrals.</p>

<h2>What 2024 prepared</h2>

<p>The federal grand-jury wave of 2024 was, in retrospect, the preparation phase. By early 2025 the federal government had pivoted from grand-jury investigation as the principal anti-Palestine-solidarity tool to immigration detention as the principal tool — arresting Mahmoud Khalil at Columbia, Rumeysa Öztürk at Tufts, Mohsen Mahdawi at his green-card interview, Yunseo Chung in New York, Leqaa Kordia in New Jersey, Badar Khan Suri in Virginia. The grand-jury investigations of 2024 had identified the names. The immigration system was the faster machinery.</p>

<p>NPPC tracks the still-active federal grand-jury matters and the post-2024 immigration-detention cases as one continuous federal campaign. They are.</p>
BODY,
            'citations' => [
                ['title' => 'Palestine Legal — Grand-jury defense resources', 'url' => 'https://palestinelegal.org/grand-jury-resources'],
                ['title' => 'Center for Constitutional Rights — Movement support and material-support cases', 'url' => 'https://ccrjustice.org/material-support'],
                ['title' => 'Treasury Department — Samidoun designation (October 2024)', 'url' => 'https://home.treasury.gov/news/press-releases/samidoun-designation-2024'],
                ['title' => 'The Intercept — Federal grand jury targets Palestine organizers', 'url' => 'https://theintercept.com/2024/grand-jury-palestine-organizers/'],
                ['title' => 'NLG — Movement defense and grand-jury refusal tradition', 'url' => 'https://www.nlg.org/grand-jury-resistance/'],
            ],
        ];
    }

    private function reznicek(): array {
        return [
            'slug'         => 'jessica-reznicek-mid-sentence-2024-terrorism-enhancement-appeal',
            'title'        => 'Jessica Reznicek, Mid-Sentence: The Federal Terrorism Enhancement That Turned a Pipeline-Defense Action Into Eight Years',
            'intro'        => 'Jessica Reznicek, the Catholic Worker activist who sabotaged Dakota Access Pipeline construction equipment in 2016 and 2017, spent all of 2024 in federal custody at the Federal Medical Center in Carswell, Texas, serving an eight-year sentence — most of which is the product of a domestic-terrorism enhancement the federal government applied to a non-violent act of property destruction that injured no one.',
            'published_at' => '2024-08-20 10:00:00',
            'body'         => <<<'BODY'
<p><em>Reznicek and her co-defendant Ruby Montoya publicly admitted in 2017 to using oxyacetylene torches to disable valves and burn construction equipment along the Dakota Access Pipeline route in Iowa, in protest of the pipeline that the Standing Rock Sioux Tribe and a broad Indigenous-led coalition had been seeking to stop since 2016. They were charged in 2019 with conspiracy to damage an energy facility and use of fire to commit a federal felony.</em></p>

<h2>The sentence — and the enhancement</h2>

<p>Reznicek pleaded guilty in 2021 to one count of conspiracy to damage an energy facility. Federal prosecutors then sought — and U.S. District Judge Rebecca Goodgame Ebinger in the Southern District of Iowa applied — a domestic-terrorism sentencing enhancement under U.S. Sentencing Guideline § 3A1.4, increasing her offense level by 12 levels and her criminal-history category to VI. The enhancement quadrupled her sentencing range. She was sentenced in August 2021 to 96 months — eight years — in federal prison, plus $3.2 million in restitution to Energy Transfer Partners.</p>

<p>The application of the terrorism enhancement to a non-violent act of property destruction that caused no human injury and was carried out as an act of climate-and-Indigenous-solidarity political protest set a precedent that civil-liberties and movement-defense groups warned would travel. It has.</p>

<h2>The appeal</h2>

<p>Reznicek\'s appellate counsel argued at the Eighth Circuit Court of Appeals that the terrorism enhancement was both factually unsupported and constitutionally problematic as applied — that the federal definition of "terrorism" requires conduct calculated to intimidate or coerce a civilian population or the government, and that Reznicek\'s conduct, on the trial record, was calculated to disable a pipeline, not to intimidate the public. The Eighth Circuit upheld the enhancement in May 2023. The Supreme Court denied certiorari in October 2023.</p>

<p>Through 2024 her legal team pursued post-conviction motions and a clemency petition, supported by a broad coalition including the Climate Defense Project, the Center for Constitutional Rights, hundreds of faith leaders (Catholic Workers, Quakers, Mennonites, Indigenous water-protector organizations), and a substantial number of climate scientists and elected officials.</p>

<h2>FMC Carswell</h2>

<p>Reznicek is held at the Federal Medical Center in Fort Worth, Texas — the federal facility for medically-complex women. Her support committee maintains a steady mail and writing relationship; her statements from inside have circulated widely in climate-defense, prisoner-support, and Catholic Worker movement publications.</p>

<h2>What the case did to the precedent</h2>

<p>Reznicek\'s sentence is the federal benchmark for the terrorism enhancement on environmental defense. Federal prosecutors cited her case in subsequent indictments — Stop Cop City forest defenders, the 2023–2024 Coastal GasLink and other pipeline-defense matters, and the Atlanta Forest defendants — and her sentencing transcript is now a standard part of the briefing in any U.S. environmental-defense federal case where prosecutors threaten the enhancement.</p>

<p>Her scheduled release date is in 2027. The campaign for sentence reduction, clemency, or appellate reconsideration continues. NPPC publishes updates as her case moves and treats her as one of the central political-prisoner cases of the contemporary U.S. environmental-defense movement.</p>
BODY,
            'citations' => [
                ['title' => 'Climate Defense Project — Jessica Reznicek case', 'url' => 'https://climatedefenseproject.org/case/jessica-reznicek/'],
                ['title' => 'Free Jessica Reznicek — Support campaign', 'url' => 'https://freejessicareznicek.com'],
                ['title' => 'Eighth Circuit — United States v. Reznicek (2023)', 'url' => 'https://ecf.ca8.uscourts.gov/opndir/23/05/reznicek.pdf'],
                ['title' => 'Center for Constitutional Rights — Amicus on terrorism enhancement', 'url' => 'https://ccrjustice.org/reznicek-amicus'],
                ['title' => 'DOJ press release — Reznicek sentenced to 96 months (Aug 2021)', 'url' => 'https://www.justice.gov/usao-sdia/pr/reznicek-sentenced-pipeline'],
            ],
        ];
    }

    private function goonan(): array {
        return [
            'slug'         => 'casey-goonan-uc-berkeley-arson-2024-indictment',
            'title'        => 'Casey Goonan: The Bay Area Anarchist Indicted by the Feds Over a 2024 UC Berkeley Police-Car Arson',
            'intro'        => 'In July 2024, federal prosecutors in the Northern District of California indicted Casey Goonan, a 34-year-old Bay Area antifascist, on use-of-fire-to-damage-property charges arising from an alleged late-night arson of a UC Berkeley police vehicle. The case is one of the year\'s clearest tests of how federal prosecutors are pursuing antifascist defendants in 2024.',
            'published_at' => '2024-07-18 14:00:00',
            'body'         => <<<'BODY'
<p><em>Goonan was arrested on July 8, 2024 and charged with one count of using fire to damage a vehicle owned by an entity receiving federal financial assistance, in violation of 18 U.S.C. § 844(f). The maximum sentence on the count is 20 years federal time. He has been held without bond at the Federal Correctional Institution at Dublin.</em></p>

<h2>The alleged conduct</h2>

<p>According to the federal indictment, in the early-morning hours of an evening in late spring 2024, an individual the federal government identifies as Goonan placed an incendiary device under a UC Berkeley Police Department patrol vehicle parked on campus. The device ignited; the vehicle burned. No injuries. The federal government built its case on surveillance camera footage, license-plate-reader data, and the recovery of unfired incendiary materials in a subsequent search of Goonan\'s residence.</p>

<h2>The charge</h2>

<p>Federal prosecutors charged the case under 18 U.S.C. § 844(f) — using fire or explosives to damage property owned by or leased to the United States or any institution receiving federal financial assistance. The University of California system receives federal funding; UC Berkeley Police are accordingly federal-jurisdiction-reachable. The choice to bring the case federally rather than as a state arson charge increased Goonan\'s sentencing exposure substantially.</p>

<p>The federal indictment did not include a terrorism-enhancement notice at the charging stage, but several legal observers noted that the U.S. Sentencing Guidelines permit the enhancement to be raised at sentencing — and that the Reznicek precedent (terrorism enhancement on non-violent property destruction in service of a political cause) is the obvious reference case.</p>

<h2>The defense and the support</h2>

<p>Goonan is represented by counsel from the Federal Public Defender for the Northern District of California. His support committee — Free Casey Goonan — coordinates commissary, court attendance, and mail. Bay Area antifascist and prisoner-support networks (Anti-Police Terror Project, NorCal Resist, Bay Area Anti-Repression Committee) have organized around his case as part of the broader U.S. antifascist political-prisoner cohort that has expanded since 2020.</p>

<h2>Where this fits</h2>

<p>Goonan\'s case sits in a small but visible 2023–2024 cohort of U.S. antifascist federal political-prisoner cases. The cohort includes earlier figures like Eric King (released from ADX in December 2023 after nearly a decade following his protest of the police killing of Michael Brown); ongoing matters arising from 2020 George Floyd-uprising prosecutions; and a handful of post-2022 abortion-rights and reproductive-justice direct-action cases.</p>

<p>What ties them together is the federal government\'s 2024 willingness to charge politically-motivated property-defense or property-destruction actions at the highest end of the federal arson and use-of-fire statutes, irrespective of human-injury outcomes. Goonan\'s case will be one of the year\'s clearest tests of where the federal courts draw the sentencing line.</p>

<p>NPPC tracks the case and publishes updates as it moves through pretrial and toward trial or plea resolution.</p>
BODY,
            'citations' => [
                ['title' => 'DOJ press release — Casey Goonan indictment (July 2024)', 'url' => 'https://www.justice.gov/usao-ndca/pr/goonan-arson-uc-berkeley'],
                ['title' => 'Free Casey Goonan — Support campaign', 'url' => 'https://freecaseygoonan.noblogs.org'],
                ['title' => 'It\'s Going Down — Coverage of the Goonan case', 'url' => 'https://itsgoingdown.org/casey-goonan-uc-berkeley/'],
                ['title' => 'San Francisco Chronicle — Berkeley arrest in police-car fire', 'url' => 'https://www.sfchronicle.com/bayarea/article/uc-berkeley-arson-goonan-2024'],
                ['title' => 'NLG — Antifascist defendant support', 'url' => 'https://www.nlg.org/antifascist-defense/'],
            ],
        ];
    }

    private function wetsuweten(): array {
        return [
            'slug'         => 'wetsuweten-solidarity-defendants-2024-coastal-gaslink',
            'title'        => 'Wet\'suwet\'en Solidarity Defendants in U.S. Federal Court: The Cross-Border 2024 Cases Arising From Coastal GasLink Resistance',
            'intro'        => 'Through 2024, U.S. federal and state prosecutors brought a series of charges against U.S.-based participants in the Wet\'suwet\'en-led resistance to Coastal GasLink — the Canadian fracked-gas pipeline that crosses unceded Wet\'suwet\'en territory in British Columbia and has been the subject of an ongoing Indigenous-led land defense since 2018. The cross-border charging is one of the year\'s clearest examples of U.S.-Canadian state coordination against Indigenous environmental defense.',
            'published_at' => '2024-09-25 11:00:00',
            'body'         => <<<'BODY'
<p><em>Coastal GasLink is the 670-kilometer fracked-gas pipeline running from Dawson Creek, British Columbia, to the LNG Canada terminal in Kitimat. It crosses the unceded territory of the Wet\'suwet\'en Nation, whose hereditary chiefs — exercising authority recognized in the 1997 Supreme Court of Canada decision in Delgamuukw — never granted consent. Since 2018, Wet\'suwet\'en land defenders and their allies have built, and rebuilt, encampments and checkpoints across the pipeline corridor. Royal Canadian Mounted Police raids have cleared those encampments repeatedly, including in militarized 2019, 2020, 2021, and 2022 enforcement operations.</em></p>

<h2>The U.S. dimension</h2>

<p>The U.S. dimension to the Wet\'suwet\'en defense matters because the U.S.-based Indigenous-solidarity and climate-defense networks have provided ongoing material, legal, and physical support to the land-defense camps for nearly a decade. By 2024, that support work had produced a documentable cohort of U.S.-based defendants facing charges in either U.S. or Canadian courts — and, in a small but consequential number of cases, U.S. federal charges arising from associated U.S.-side actions.</p>

<h2>What 2024 produced in U.S. courts</h2>

<p>Through 2024, U.S. federal and state prosecutors brought charges in connection with:</p>

<ul>
  <li><strong>2024 RBC Royal Bank actions across the United States</strong>: Solidarity-with-Wet\'suwet\'en pickets and lock-downs at RBC Royal Bank branches (RBC is the principal financier of Coastal GasLink) produced misdemeanor and in several jurisdictions felony state-court charges in U.S. cities including Los Angeles, San Francisco, New York, and Boston.</li>
  <li><strong>U.S. petitioners in Canadian contempt proceedings</strong>: A small number of U.S. citizens detained at Wet\'suwet\'en camps in late 2023 / early 2024 were charged under Canadian contempt of the Coastal GasLink injunction. Cross-border extradition or travel-restriction questions came back to U.S. courts.</li>
  <li><strong>The "Tiny House Warrior" and adjacent matters</strong>: Indigenous-led tiny-house camps on the proposed Trans Mountain pipeline route in B.C. produced cross-border legal complications resolved in U.S. courts in late 2024.</li>
  <li><strong>Doxxing and surveillance suits</strong>: U.S.-based defenders alleging surveillance and harassment by private security contractors hired by TC Energy / Coastal GasLink filed civil suits in U.S. district court in 2024.</li>
</ul>

<h2>The shared template</h2>

<p>The U.S. side of these prosecutions adopted, in 2024, much of the same charging architecture that the Cop City and Reznicek cases produced — domestic-terrorism enhancements on non-violent property defense, conspiracy theories on coalition movements, RICO-adjacent theories on coordinated direct action. The cross-border element added complications: Canadian disclosure rules, RCMP intelligence-sharing with U.S. agencies, and the practical question of how a U.S. defendant can mount a defense to an alleged offense that occurred on or about unceded Indigenous land in another country.</p>

<h2>What this connects to</h2>

<p>The U.S. Wet\'suwet\'en-solidarity prosecutions sit alongside Standing Rock-era defendants (Reznicek being the most prominent), the Stop Line 3 prosecutions in Minnesota, and the ongoing prosecutions of Mountain Valley Pipeline defenders in Virginia and West Virginia. The 2024 cohort represents the continuation of a roughly decade-long federal-court track record of treating Indigenous-led pipeline-defense as a federal-charging matter.</p>

<p>NPPC tracks U.S.-based Wet\'suwet\'en-solidarity cases and publishes updates as 2024 indictments move toward trial or resolution.</p>
BODY,
            'citations' => [
                ['title' => 'Yintah Access — Wet\'suwet\'en hereditary chiefs', 'url' => 'https://yintahaccess.com'],
                ['title' => 'Gidimt\'en Checkpoint — Land defense updates', 'url' => 'https://www.yintahaccess.com/gidimten'],
                ['title' => 'The Narwhal — Coverage of Coastal GasLink prosecutions', 'url' => 'https://thenarwhal.ca/topics/coastal-gaslink/'],
                ['title' => 'Indigenous Environmental Network — Solidarity actions and legal support', 'url' => 'https://www.ienearth.org/wetsuweten/'],
                ['title' => 'Climate Defense Project — Wet\'suwet\'en solidarity defendants', 'url' => 'https://climatedefenseproject.org/wetsuweten-solidarity-2024/'],
            ],
        ];
    }

    private function mumia(): array {
        return [
            'slug'         => 'mumia-abu-jamal-43rd-year-pcra-brought-to-light-2024',
            'title'        => 'Mumia Abu-Jamal, Year 43: PCRA Litigation and the Brought-to-Light Campaign in 2024',
            'intro'        => 'Mumia Abu-Jamal, the journalist, MOVE supporter, and former Black Panther Party member convicted of the 1981 killing of Philadelphia police officer Daniel Faulkner, spent all of 2024 inside SCI Mahanoy continuing his post-conviction litigation in Pennsylvania state court while the international "Brought to Light" campaign pressed for his release on grounds of his serious medical conditions.',
            'published_at' => '2024-10-09 10:00:00',
            'body'         => <<<'BODY'
<p><em>2024 marked the 43rd year of Mumia\'s incarceration. He is 70. He has hepatitis C, cirrhosis, congestive heart failure, and a documented history of medical neglect by the Pennsylvania Department of Corrections. He has been off death row since 2011 when a federal appellate ruling found his death sentence unconstitutional; he is currently serving life without the possibility of parole.</em></p>

<h2>The PCRA litigation in 2024</h2>

<p>Mumia\'s post-conviction litigation continued through 2024 in the Pennsylvania Court of Common Pleas of Philadelphia County. The 2024 phase centered on prosecutorial-misconduct claims arising from a 2018-and-after document discovery — six boxes of previously-undisclosed materials from the Philadelphia District Attorney\'s office that included Brady-material the original prosecution had not turned over. The materials related principally to the credibility of key prosecution witness Robert Chobert, the financial inducements offered to him, and the handling of the ballistics evidence.</p>

<p>Through 2024 his counsel — including a team of attorneys at the Abolitionist Law Center and the National Lawyers Guild — pressed for an evidentiary hearing. The court denied the most recent PCRA petition; appeals from that denial continued through 2024 in the Pennsylvania Superior Court.</p>

<h2>The medical campaign</h2>

<p>The "Bring Mumia Home" / "Brought to Light" campaign through 2024 emphasized his medical conditions and the case for compassionate release. International figures including Angela Davis, Cornel West, Noam Chomsky, Alice Walker, Roger Waters, Pink Floyd, and a long roster of African heads of state and former African heads of state continued the call. The U.N. Working Group on Arbitrary Detention took up his case in 2024.</p>

<p>Pennsylvania law on compassionate release is narrow and does not in practice extend to defendants serving life-without-parole sentences. Mumia\'s legal team continued to argue that the documented medical neglect and his deteriorating condition constitute Eighth Amendment violations cognizable on federal habeas.</p>

<h2>The longer arc</h2>

<p>Mumia\'s case is the central political-prisoner case of the contemporary U.S. Black liberation movement. His journalism — both before his arrest and from inside, through his hundreds of essays, his books, his Prison Radio commentaries, and his syndicated columns — has made him among the most heard incarcerated writers in the world. He is widely regarded by the U.S. abolitionist movement, the international human-rights community, and the African diaspora as a political prisoner. He is regarded by Philadelphia police and the Fraternal Order of Police as a cop killer. Neither position has moved in 43 years.</p>

<p>NPPC marks Mumia\'s birthday (April 24) every year and tracks the PCRA litigation as it moves. The 2024 docket is the latest chapter in a case that has now consumed more than half a century of legal effort, movement organizing, and counter-organizing.</p>
BODY,
            'citations' => [
                ['title' => 'Prison Radio — Mumia Abu-Jamal\'s commentaries', 'url' => 'https://www.prisonradio.org/correspondent/mumia-abu-jamal/'],
                ['title' => 'Abolitionist Law Center — Mumia legal updates', 'url' => 'https://abolitionistlawcenter.org/category/mumia/'],
                ['title' => 'Bring Mumia Home — Campaign', 'url' => 'https://bringmumiahome.com'],
                ['title' => 'UN Working Group on Arbitrary Detention — Communication on Mumia (2024)', 'url' => 'https://www.ohchr.org/en/special-procedures/wg-arbitrary-detention'],
                ['title' => 'International Concerned Family and Friends of Mumia Abu-Jamal', 'url' => 'http://www.freemumia.com'],
            ],
        ];
    }

    private function magee(): array {
        return [
            'slug'         => 'ruchell-cinque-magee-one-year-memoriam-2024',
            'title'        => 'Ruchell "Cinque" Magee in Memoriam: One Year After California\'s Longest-Held Political Prisoner Came Home to Die',
            'intro'        => 'Ruchell Cinque Magee was released from California state custody on July 19, 2023 after 67 years in prison — making him, at the time of his release, the longest-held political prisoner in U.S. history. He died on October 17, 2023, eighty-four years old, three months after his release. October 2024 marked the first anniversary of his death and the first full year in which the U.S. political-prisoner roster does not have his name on it.',
            'published_at' => '2024-10-17 09:00:00',
            'body'         => <<<'BODY'
<p><em>Magee survived. That is the first fact about his case, and it remains the most important one. He survived sixty-seven years in California state prisons — at Soledad, at Folsom, at San Quentin, at Pelican Bay, at the California Medical Facility at Vacaville. He survived the August 7, 1970 Marin County Civic Center incident in which Jonathan Jackson, James McClain, and William Christmas were killed and a Marin County judge died. He survived being charged with a capital crime arising from that incident, conviction, and life-without-parole sentencing. He survived George Jackson\'s 1971 killing at San Quentin, the death of essentially every contemporary of his original Marin County prosecution, and the better part of a century of denied parole hearings. He died at home.</em></p>

<h2>What he was inside for</h2>

<p>Magee was first imprisoned in 1963 on a state-court conviction for aggravated kidnapping arising from a $10 marijuana sale dispute that escalated into a charge of armed kidnapping. He always maintained the original conviction was a frame. He was 24. The original sentence was indeterminate — life with the possibility of parole.</p>

<p>On August 7, 1970, Magee was one of three Black prisoners being transported to a courtroom at the Marin County Civic Center to testify in a trial unrelated to his original case. Jonathan Jackson — the 17-year-old brother of George Jackson — entered the courtroom that morning with smuggled weapons, took the judge, the prosecutor, and several jurors hostage in an attempt to negotiate the release of his brother and the Soledad Brothers. The hostage attempt ended in gunfire in the parking lot outside the courthouse: Jonathan Jackson, James McClain, William Christmas, and the judge died. Magee survived, shot. He was charged with aggravated kidnapping and conspiracy to commit murder arising from the incident, convicted, and sentenced to life without parole.</p>

<p>He filed pro se federal habeas petitions for decades. He represented himself in multiple proceedings. He maintained a steady correspondence and statements from inside.</p>

<h2>The release</h2>

<p>In 2023 the California Board of Parole Hearings, applying the state\'s elder-parole and youth-offender-parole frameworks that California had enacted in 2017 (and that Magee, then 84, was finally eligible under for the first time), granted parole. Governor Gavin Newsom did not block the recommendation. Magee was released to a halfway facility on July 19, 2023. He died three months later, on October 17, 2023, at age 84.</p>

<h2>The first year without him</h2>

<p>The year between Magee\'s death and October 2024 was the first in nearly seventy years in which no political-prisoner list anywhere in the United States needed to carry his name. The Jericho Movement, the National Jericho Movement, the Anarchist Black Cross network, the Sundiata Acoli Freedom Campaign, the Friends of Ruchell Magee, and the Black August commemorations across the diaspora marked the year. His extant writings have begun to be republished — the open letters, the federal habeas petitions, the prison correspondence.</p>

<p>What Magee left behind is, in the simplest accounting, the longest single record of survival in modern U.S. political imprisonment. He should never have served sixty-seven years. He served them, and lived to come home, and died free. The political-prisoner support tradition NPPC documents marks October 17 every year because what he survived is what every long-held prisoner is being asked to survive.</p>
BODY,
            'citations' => [
                ['title' => 'KQED — Ruchell Magee, longtime California political prisoner, dies at 84', 'url' => 'https://www.kqed.org/news/ruchell-magee-dies-2023'],
                ['title' => 'NY Times — Ruchell Magee obituary', 'url' => 'https://www.nytimes.com/2023/10/19/us/ruchell-magee-dead.html'],
                ['title' => 'Jericho Movement — Magee memorial', 'url' => 'https://www.thejerichomovement.com/magee-memorial'],
                ['title' => 'Friends of Ruchell Magee', 'url' => 'https://freeruchellmagee.org'],
                ['title' => 'San Francisco Bay View — On Magee', 'url' => 'https://sfbayview.com/tag/ruchell-magee/'],
            ],
        ];
    }

    private function move9(): array {
        return [
            'slug'         => 'move-9-2024-status-survivors',
            'title'        => 'The MOVE 9 in 2024: Where the Survivors of Philadelphia\'s Forty-Year Frame-up Are Now',
            'intro'        => 'The MOVE 9 — nine members of the Philadelphia Black liberation / naturalist commune sentenced to 30–100 years each for the August 8, 1978 police siege of MOVE\'s Powelton Village home in which one officer died — entered 2024 with two members dead in prison, seven released over the 2018–2020 period, and one of those released (Delbert Africa) dead within six months of his release. 2024 was the first year in which the MOVE 9 case existed essentially in retrospect.',
            'published_at' => '2024-08-08 09:00:00',
            'body'         => <<<'BODY'
<p><em>Chuck Africa. Debbie Sims Africa. Delbert Orr Africa. Edward Goodman Africa. Janet Holloway Africa. Janine Phillips Africa. Merle Austin Africa. Mike Davis Africa. Phil Africa. Nine names, one sentence of 30 to 100 years each, four decades of incarceration. The MOVE 9 case is the central post-1970s U.S. Black liberation political-prisoner case to come out of Philadelphia. August 8 — the anniversary of the 1978 siege — is observed every year.</em></p>

<h2>The case</h2>

<p>On August 8, 1978, after months of mounting confrontation between MOVE and the Philadelphia Police Department, hundreds of officers laid siege to MOVE\'s home at 309 N. 33rd Street in Powelton Village. The stated mission was the service of weapons-possession warrants on MOVE members. The siege ended after Police Officer James Ramp was killed by a single bullet whose trajectory has been disputed by every independent ballistics review that has examined the evidence — including reviews that have argued the round came from a direction that would have made it physically impossible for any of the nine MOVE members charged to have fired it.</p>

<p>The state of Pennsylvania nevertheless prosecuted nine adult MOVE members as a single conspiracy. All nine were convicted of third-degree murder in 1980 and each received concurrent sentences of 30 to 100 years. The prosecution\'s theory was that since MOVE members had collectively engaged in conduct that produced the death of an officer, each individual was liable for the death regardless of who fired the round. None of the nine ever publicly identified an alleged shooter from inside their own group, and the state\'s ballistics evidence on which member fired the fatal round was incoherent on its face.</p>

<h2>The deaths inside</h2>

<p>Two of the MOVE 9 died in Pennsylvania state custody without ever being paroled: <strong>Merle Africa</strong> died in 1998 at SCI Cambridge Springs. <strong>Phil Africa</strong> died in January 2015 at SCI Dallas. Both deaths drew protest from MOVE supporters and from independent observers, both for the underlying length of sentence and for the documented medical and grievance histories of the prisoners involved.</p>

<h2>The releases (2018–2020)</h2>

<p>Beginning in 2018 — forty years after the original convictions, as the surviving MOVE 9 reached parole-eligibility under Pennsylvania\'s sentencing structure — the remaining members began to be granted parole:</p>

<ul>
  <li><strong>Debbie Sims Africa</strong>, June 2018</li>
  <li><strong>Mike Davis Africa</strong>, October 2018</li>
  <li><strong>Janine Phillips Africa</strong>, May 2019</li>
  <li><strong>Janet Holloway Africa</strong>, May 2019</li>
  <li><strong>Eddie Goodman Africa</strong>, June 2019</li>
  <li><strong>Delbert Orr Africa</strong>, January 2020</li>
  <li><strong>Chuck Africa</strong>, February 2020</li>
</ul>

<p>Each was paroled to MOVE family in Philadelphia. The releases were widely celebrated; the 2018–2020 window was the closest the U.S. movement-defense tradition has come in the modern era to bringing a full cohort of Black liberation political prisoners home alive.</p>

<h2>Delbert</h2>

<p>Delbert Orr Africa — paroled January 18, 2020 after nearly 42 years — was visibly ill on release. He died on June 15, 2020, less than six months later, of complications widely attributed by his family and supporters to the cumulative medical neglect of his decades in Pennsylvania state custody. He was 74.</p>

<h2>What 2024 was for the survivors</h2>

<p>2024 was a year of memorial and continuation. The surviving MOVE 9 — Debbie, Mike, Janine, Janet, Eddie, Chuck — continue to live in the Philadelphia area and to participate in MOVE\'s ongoing organizing, including the May 13 anniversary commemorations of the 1985 bombing. They write. They speak. They are present in court when other political prisoners need court support. They are the surviving generational link between the Black liberation prison tradition of the 1970s and the contemporary Black abolitionist movement.</p>

<p>NPPC tracks the surviving members\' public work and continues to publish on the MOVE political-prisoner record. August 8 and May 13 are observed annually.</p>
BODY,
            'citations' => [
                ['title' => 'MOVE Organization — Official site', 'url' => 'https://onamove.com'],
                ['title' => 'Abolitionist Law Center — MOVE 9 case history', 'url' => 'https://abolitionistlawcenter.org/tag/move-9/'],
                ['title' => 'The Intercept — "I\'ve Been Waiting 40 Years": The MOVE 9 Reunion', 'url' => 'https://theintercept.com/2020/02/move-9-reunion-philadelphia/'],
                ['title' => 'Workers World — Delbert Africa obituary', 'url' => 'https://www.workers.org/2020/06/delbert-africa/'],
                ['title' => 'Bring Mumia Home — MOVE 9 background', 'url' => 'https://bringmumiahome.com/move-9/'],
            ],
        ];
    }

    private function ericKing(): array {
        return [
            'slug'         => 'eric-king-first-year-out-2024-post-adx-life',
            'title'        => 'Eric King, First Year Out: What Happens After Nine Years in ADX',
            'intro'        => 'Eric King — antifascist anarchist political prisoner held in federal custody from 2014 to December 2023, with much of that time in the federal supermax ADX Florence — spent his first full year of freedom in 2024. The case he came home from, and the prison conditions he survived, made his post-release year a public reckoning for the U.S. abolitionist movement about what ADX does to people and what coming home from it looks like.',
            'published_at' => '2024-12-12 11:00:00',
            'body'         => <<<'BODY'
<p><em>King was released from federal custody on December 12, 2023. He had served the better part of a decade for an attempted Molotov cocktail throw at a U.S. congressman\'s Kansas City office in 2014, an act of protest against the police killing of Michael Brown in Ferguson the same year. He had been moved through eight federal prisons. He had spent years in solitary confinement at ADX Florence — the federal supermax — for organizing prison-condition complaints, refusing to be silenced about staff violence against him, and supporting other prisoners. He was 38 when he came home.</em></p>

<h2>What the case actually was</h2>

<p>King\'s underlying federal offense was a single attempted incendiary throw at the Kansas City office of Congressman Emanuel Cleaver II in 2014. No one was injured. No property was substantially damaged. He pleaded guilty in 2016 to one count of attempted arson and was sentenced to 10 years. He was not a national-security defendant. He was not a serial offender. He was, in the U.S. federal-prison apparatus\'s own internal classification, a low-to-medium-security custody level.</p>

<p>His time inside was a steady escalation. He was moved more often than almost any prisoner of his security classification in the same period — through Leavenworth, Englewood, Florence, McCreary, ADX, and others. He was repeatedly placed in solitary, in many cases following his filing of grievances about staff misconduct, his correspondence with movement-press and prisoner-support networks, and his refusal to be silenced about the white-supremacist violence he documented from other prisoners and from staff. He survived an assault at FCI Florence in 2018 that the U.S. government later prosecuted him for as a fight he had instigated; he was acquitted at trial in 2022.</p>

<h2>Post-release 2024</h2>

<p>King\'s first year out was structured around several public projects. He took on paralegal work with the Bread and Roses Legal Center. He continued to write — zines, essays, prison-letter republications, and a series of short pieces on what ADX does to a person\'s ability to be in a room with other people. He participated in the June 2024 Firestorm Books panel "Continuing the Struggle Inside & Out" with Ray Luc Levasseur and Ashanti Alston, the recording of which is now mirrored on Internet Archive and in the NPPC archive. He spoke at multiple movement events and conferences. He continued to do mail and support work with currently-incarcerated antifascist and anarchist prisoners.</p>

<p>His writing in 2024 was — by his own framing — about the slow process of remembering how to be unconfined. The cumulative physiological and psychiatric record of long-term solitary confinement is well-established in the medical literature (the Eighth Amendment litigation on ADX over the past two decades has produced thousands of pages of expert testimony on it), and King\'s public writing has been one of the few first-person accounts published in 2024 by a former ADX prisoner describing what the immediate post-release year is actually like.</p>

<h2>The cohort</h2>

<p>King\'s release brought him into a small contemporary U.S. cohort of recently-freed antifascist and anarchist political prisoners — including the surviving Green Scare defendants, several U.S. participants in the 2008–2017 anti-fascist defense matters, and the post-Standing Rock pipeline-defense cohort. He has been one of the most visible of that cohort in movement-public-facing work, which has produced both ongoing organizing capacity and ongoing surveillance and harassment from federal agencies still treating him as a person of interest.</p>

<p>NPPC tracks former political prisoners as actively as currently-incarcerated ones. The 2024 King record is part of the U.S. political-prisoner story of the year — and a reminder that for the people who survive, freedom is the next chapter, not the final one.</p>
BODY,
            'citations' => [
                ['title' => 'Bread and Roses Legal Center', 'url' => 'https://www.breadandroseslegal.org'],
                ['title' => 'Support Eric King — Archive', 'url' => 'https://supportericking.org'],
                ['title' => 'Rattling the Cages: Oral Histories of North American Political Prisoners (AK Press)', 'url' => 'https://www.akpress.org/rattling-the-cages.html'],
                ['title' => 'It\'s Going Down — Eric King\'s release coverage', 'url' => 'https://itsgoingdown.org/eric-king-released/'],
                ['title' => 'AK Press — Defiance: Anarchist Statements Before Judge and Jury', 'url' => 'https://www.akpress.org/defiance.html'],
            ],
        ];
    }

    private function donziger(): array {
        return [
            'slug'         => 'steven-donziger-2024-scotus-cert-denial',
            'title'        => 'Steven Donziger After SCOTUS: The 2024 End-of-Road for the Chevron Lawyer\'s Constitutional Challenge',
            'intro'        => 'In April 2024, the U.S. Supreme Court denied certiorari in the case of Steven Donziger — the U.S. human-rights lawyer prosecuted for criminal contempt by a private law firm appointed by a federal judge after the federal government declined to take the case. The cert denial closes the constitutional challenge to the most unusual private criminal prosecution in modern U.S. legal history.',
            'published_at' => '2024-04-22 11:00:00',
            'body'         => <<<'BODY'
<p><em>Donziger represented 30,000 Ecuadorian Amazon residents in a decades-long environmental lawsuit against Chevron arising from Texaco\'s catastrophic oil pollution of the Ecuadorian Amazon in the 1970s and 1980s. His clients won a $9.5 billion judgment in Ecuadorian court in 2011. Chevron retaliated by suing Donziger personally in U.S. federal court under the RICO statute — and what followed was one of the most aggressive corporate-retaliation campaigns in modern U.S. legal history.</em></p>

<h2>The criminal contempt prosecution</h2>

<p>U.S. District Judge Lewis A. Kaplan held Donziger in criminal contempt in 2019 for declining to turn over privileged communications. When the U.S. Attorney\'s Office for the Southern District of New York reviewed the matter and declined to prosecute, Judge Kaplan took the unprecedented step of appointing a private law firm — Seward & Kissel — to prosecute Donziger as a "special prosecutor." Seward & Kissel had Chevron as a client.</p>

<p>Donziger was convicted of the contempt charges in 2021. He was sentenced to six months in federal prison. By the time he served the sentence (much of it in home detention during the pandemic followed by a stint at the Federal Correctional Institution at Danbury), he had been held under various forms of detention or restriction for nearly 1,000 days — among the longest pretrial-and-post-conviction custodial periods ever imposed on a U.S. attorney for a misdemeanor offense.</p>

<h2>The constitutional challenge</h2>

<p>Donziger\'s appellate counsel — supported by amicus filings from the National Lawyers Guild, the Center for Constitutional Rights, the International Bar Association, hundreds of U.S. and international human-rights organizations, and several Nobel Peace Prize laureates — argued that the private criminal prosecution violated the U.S. Constitution\'s Appointments Clause, the separation of powers, and Article III. The Second Circuit Court of Appeals rejected those arguments in 2023. Donziger petitioned the U.S. Supreme Court for certiorari.</p>

<p>On April 22, 2024, the Supreme Court denied cert. Without an opinion, without dissent. The constitutional question — whether a federal judge can appoint a private firm with a client interest in the defendant\'s prosecution to prosecute that defendant criminally — was left unresolved as a matter of binding precedent and resolved against Donziger as a matter of his case.</p>

<h2>2024: disbarment and aftermath</h2>

<p>Through 2024, Donziger continued to face state-bar disciplinary proceedings in New York arising from the same underlying conduct. His New York bar license, which had been suspended in 2018 and disbarred in 2020, remained in disbarment status through 2024. He continued to write, speak, and organize on the Ecuadorian Amazon contamination matter, the corporate-judicial-collaboration pattern, and the broader chilling effect his case has had on environmental and human-rights lawyering.</p>

<p>The Ecuadorian Amazon contamination itself remains uncleaned. Chevron has paid nothing of the 2011 judgment. The U.N. Working Group on Arbitrary Detention found in 2021 that Donziger\'s detention was arbitrary and called for his release; the U.S. government rejected that finding.</p>

<h2>What this case proves</h2>

<p>Donziger\'s case is a U.S. legal-history landmark for what it shows about the boundaries of corporate retaliation. A U.S. attorney who won a $9.5 billion judgment against an oil company in foreign court spent nearly three years in some form of detention as a result of a private criminal prosecution that the U.S. Department of Justice itself declined to bring. The U.S. Supreme Court declined to review the constitutional question. The Ecuadorian Amazon residents whose interests he represented received nothing. That is the precedent on the shelf for the next U.S. attorney who considers taking on a major-corporate environmental adversary.</p>
BODY,
            'citations' => [
                ['title' => 'Free Donziger — Campaign site', 'url' => 'https://www.freedonziger.com'],
                ['title' => 'UN Working Group on Arbitrary Detention — Opinion on Donziger', 'url' => 'https://www.ohchr.org/en/special-procedures/wg-arbitrary-detention/donziger'],
                ['title' => 'The Intercept — Steven Donziger and the Chevron retaliation', 'url' => 'https://theintercept.com/2021/donziger-chevron-retaliation/'],
                ['title' => 'NY Times — Supreme Court declines Donziger appeal (April 2024)', 'url' => 'https://www.nytimes.com/2024/04/22/us/scotus-donziger-cert-denied.html'],
                ['title' => 'NLG — Donziger amicus brief', 'url' => 'https://www.nlg.org/donziger-amicus/'],
            ],
        ];
    }

    private function siddiqui(): array {
        return [
            'slug'         => 'aafia-siddiqui-2024-clemency-push-fmc-carswell',
            'title'        => 'Aafia Siddiqui: The 86-Year Sentence That 2024\'s International Clemency Push Did Not Move',
            'intro'        => 'Dr. Aafia Siddiqui — the Pakistani neuroscientist and MIT-trained cognitive-science PhD detained by U.S. forces in Afghanistan in 2008 and convicted in 2010 by a U.S. federal court in Manhattan of attempted murder of U.S. service members — spent all of 2024 at the Federal Medical Center in Carswell, Texas, on an 86-year sentence. International and domestic clemency advocacy intensified through 2024 and the Biden administration left office without acting.',
            'published_at' => '2024-12-08 12:00:00',
            'body'         => <<<'BODY'
<p><em>Siddiqui\'s case is one of the most internationally-known U.S. political-prisoner cases of the post-September-11 era. Her name has been invoked by every Pakistani head of state of the past fifteen years, by U.S. and international human-rights organizations, by Muslim civil-rights organizations, and by the U.N. Working Group on Arbitrary Detention. Her 86-year sentence — for shooting and missing U.S. soldiers and FBI personnel during a 2008 interrogation at an Afghan police facility — is among the most disproportionate U.S. terrorism-adjacent sentences of the modern era.</em></p>

<h2>The arc</h2>

<p>Siddiqui was an MIT-educated neuroscientist (PhD in cognitive neuroscience, Brandeis, 2001) who had been living and working in the United States with her young children. In 2003 she disappeared in Karachi. For five years there was no public information about her whereabouts. Her family and a broad international advocacy network maintain — with substantial corroboration from journalistic and U.N. investigations — that she was held in U.S. custody at Bagram Air Base during that period, subjected to interrogation and what survivors of Bagram in the same era have consistently described as torture.</p>

<p>In July 2008 she reappeared, in U.S. custody, in Ghazni, Afghanistan, where she was alleged to have been arrested by local authorities and then shot in the abdomen during an attempt to grab a U.S. soldier\'s rifle and fire on Americans and Afghan personnel in the room. She wounded no one. She was flown to the United States, indicted in the Southern District of New York, and tried in 2010 on attempted-murder and assault-of-U.S.-personnel charges. She was convicted on all counts and sentenced in September 2010 to 86 years.</p>

<h2>The international advocacy</h2>

<p>The advocacy for Siddiqui\'s release has been continuous since her sentencing. The government of Pakistan has formally requested her transfer to Pakistani custody on multiple occasions. Amnesty International, the Council on American-Islamic Relations, the Center for Constitutional Rights, the Muslim Legal Fund of America, the U.N. Working Group on Arbitrary Detention, and dozens of U.S. and international human-rights and faith organizations have called for her release.</p>

<p>In January 2022 her case received unwanted U.S. public attention when a British-Pakistani man took four hostages at a synagogue in Colleyville, Texas — including the rabbi — demanding her release. The hostage-taker was killed by FBI; all hostages survived. The Texas attack drew the U.S. political-prisoner movement\'s long-standing demands for her release into a national-news context that complicated rather than helped her case.</p>

<h2>FMC Carswell and 2024 conditions</h2>

<p>Siddiqui has been held at the Federal Medical Center in Carswell, Texas, the BOP facility for medically-complex women prisoners. Her family and advocates have documented serious medical-neglect and physical-assault incidents at the facility, including a July 2021 assault by another prisoner that her counsel said the BOP did not adequately investigate. Through 2024 the Muslim Legal Fund of America, CAIR, and her family pressed Biden-administration officials for a humanitarian transfer to Pakistani custody, citing both the assault history and the underlying medical conditions arising from her years of incarceration.</p>

<p>President Biden left office in January 2025 without granting clemency or arranging transfer. Her case sits with the second Trump administration, which is unlikely to act.</p>

<h2>Why this case matters</h2>

<p>Siddiqui\'s case is the U.S. political-prisoner case in which the disproportionality of U.S. terrorism-adjacent sentencing is most legible. The conduct she was convicted of injured no one. The 86-year sentence is the U.S. federal court\'s answer to that conduct. Around her sentence sits a documented record of pre-trial enforced disappearance, of Bagram-era interrogation, of trial-court rulings that excluded substantial portions of her defense, and of a hostage-taking incident she had nothing to do with that the U.S. political and media response treated as her responsibility. NPPC lists her on the U.S. political-prisoner roster on the simple proposition that an 86-year federal sentence for conduct that injured no one, in a case structured around the U.S. extraordinary-rendition apparatus, is what political imprisonment looks like.</p>
BODY,
            'citations' => [
                ['title' => 'Aafia Movement — Family campaign', 'url' => 'https://aafia.org'],
                ['title' => 'Council on American-Islamic Relations — Aafia Siddiqui case', 'url' => 'https://www.cair.com/aafia-siddiqui'],
                ['title' => 'Muslim Legal Fund of America — Siddiqui defense fund', 'url' => 'https://mlfa.org/case/aafia-siddiqui/'],
                ['title' => 'UN Working Group on Arbitrary Detention — Opinion on Siddiqui', 'url' => 'https://www.ohchr.org/en/special-procedures/wg-arbitrary-detention/siddiqui'],
                ['title' => 'Amnesty International — Aafia Siddiqui case', 'url' => 'https://www.amnesty.org/en/wire/march-2014/aafia-siddiqui/'],
            ],
        ];
    }

    private function mariusMason2024(): array {
        return [
            'slug'         => 'marius-mason-year-16-pre-release-ramp-2024',
            'title'        => 'Marius Mason, Year Sixteen: The Pre-Release Ramp of 2024 That Set Up the May 2026 Halfway-House Release',
            'intro'        => 'Marius Mason — anarchist, environmentalist, trans man, longest-serving Green Scare political prisoner — entered 2024 in his sixteenth year inside on a 21-year-and-10-month federal sentence for Earth Liberation Front actions in Michigan in 1999. 2024 was the pre-release-planning year: medical reviews, halfway-house programming, the slow movement of his case toward the May 2026 release that arrived as scheduled.',
            'published_at' => '2024-11-10 11:00:00',
            'body'         => <<<'BODY'
<p><em>Mason\'s 21-year-and-10-month sentence — imposed in 2009 for the 1999 ELF arson of a Michigan State University research building and adjacent property destruction at a Michigan logging operation — has been, by the metric most often cited, the longest sentence imposed on any Green Scare or post-Green Scare environmental defendant who caused no human injury. By the end of 2024 he had been in federal custody for sixteen-plus years.</em></p>

<h2>The 2024 docket</h2>

<p>Mason\'s 2024 was administrative: BOP custody-classification review, halfway-house program planning, ongoing medical care (including the gender-affirming hormone-therapy regimen he has had to fight to maintain through his federal incarceration, having come out as trans in 2014 and become a named plaintiff in litigation pushing the Bureau of Prisons to provide trans medical care to federal prisoners), education programming, and the slow institutional preparation for the May 2026 release that the BOP\'s sentencing structure made foreseeable.</p>

<p>He was held during 2024 at FCI Danbury in Connecticut, the BOP\'s women\'s facility where he had been transferred years earlier. His support committee — Support Marius Mason — coordinated commissary, mail, art and book sales, and the ongoing public-facing campaign.</p>

<h2>The work inside</h2>

<p>Mason\'s sixteen years of federal incarceration produced an unusually well-documented prisoner intellectual and artistic record. He earned a paralegal degree inside. He completed a writing tutorship through the Yale Prison Education Initiative. He produced — and continues to produce — a substantial body of visual art, poetry, music, and prose, pieces of which have been exhibited and sold to support his commissary and outside campaign work. He maintained correspondence with the entire surviving Green Scare political-prisoner cohort and with the broader U.S. and international political-prisoner support universe.</p>

<p>His public writing through 2024 included pieces on trans prisoner medical care, on the BOP\'s grievance system, on the long arc of the environmental-defense movement, and on the case for cross-movement solidarity work between climate, abolitionist, and trans-liberation organizing.</p>

<h2>What 2024 produced for the campaign</h2>

<p>The Support Marius Mason campaign in 2024 was structured around the foreseeable release: building halfway-house support infrastructure in Detroit (Mason\'s release destination), arranging the post-release medical-care continuity that would be required, fundraising for the immediate restart costs that political prisoners face on release (clothing, transportation, ID, documentation, medical care, the financial burden of the first months out), and continuing the public-attention curve around his name. The campaign had been planning this transition for the better part of half a decade by 2024.</p>

<p>The May 4, 2026 release — covered in NPPC\'s May 2026 article on Mason\'s release to a Detroit halfway house — arrived as scheduled. 2024 was the year that made it possible. The post-release campaign continues; the federal sentence formally ends in May 2027.</p>

<h2>The lineage that closed</h2>

<p>Mason\'s release in 2026 closes the federal-prison arc of the original 2005–2008 Green Scare prosecution wave. Daniel McGowan was released in 2013. Eric McDavid\'s conviction was vacated in 2015 over withheld evidence. Rebecca Rubin came home in 2018. Joseph Dibee — the longest fugitive — was extradited in 2018 and sentenced in 2022 with credit for time served. With Mason\'s halfway-house release, the federal Green Scare prison arc is — by the metric of who is still behind FBOP walls — effectively closed.</p>

<p>What is not closed is the precedent: the "eco-terrorism" charging framework the FBI and DOJ built in those cases is now the template being deployed against Stop Cop City, Prairieland, and the broader U.S. response to environmental and anti-police organizing. NPPC tracks the Mason post-release transition, the continuing campaign for the unconditional release of all post-Mason political prisoners, and the lineage that connects the Green Scare to the contemporary moment.</p>
BODY,
            'citations' => [
                ['title' => 'Support Marius Mason — Campaign site', 'url' => 'https://supportmariusmason.org'],
                ['title' => 'Anarchist Black Cross Federation — Marius Mason updates', 'url' => 'https://www.abcf.net/blog/tag/marius-mason/'],
                ['title' => 'Yale Prison Education Initiative — Programming', 'url' => 'https://prisoneducation.yale.edu'],
                ['title' => 'Earth First! Newswire — Mason updates', 'url' => 'https://earthfirstjournal.news/tag/marius-mason/'],
                ['title' => 'NCTE — Trans medical-care litigation in federal prisons', 'url' => 'https://transequality.org/issues/incarcerated-trans-people'],
            ],
        ];
    }

    private function yearEndCensus(): array {
        return [
            'slug'         => 'political-prisoner-2024-year-end-census',
            'title'        => 'The 2024 U.S. Political-Prisoner Year-End: Who Came Home, Who Didn\'t, Who Died Inside',
            'intro'        => 'At the close of 2024, the U.S. political-prisoner roster looked the way it had looked all year — with two named exceptions, both produced by Julian Assange\'s June 25 Saipan plea and by the slow attrition of a generation. This is the year-end accounting.',
            'published_at' => '2024-12-30 11:00:00',
            'body'         => <<<'BODY'
<p><em>The U.S. political-prisoner roster moves only on three triggers: someone comes home, someone is added, or someone dies inside. The 2024 movement on each of those triggers, organized by category, is the year\'s defining administrative record.</em></p>

<h2>Came home in 2024</h2>

<ul>
  <li><strong>Julian Assange</strong> — June 25, 2024, Saipan. Time-served plea on one Espionage Act count. Returned to Australia same day. After 14 years (7 in the Ecuadorian embassy, 5 in Belmarsh, 2 in pre-embassy detention) of U.S.-driven prosecution.</li>
  <li><strong>The 2018–2020 MOVE 9 release cohort</strong> remains out, with the year\'s most consequential update being the continued post-release survival of Debbie, Mike, Janine, Janet, Eddie, and Chuck — six surviving members nearly forty-six years after the 1978 Powelton Village siege.</li>
  <li><strong>Multiple Stop Cop City forest-defender defendants</strong> resolved pretrial matters with non-incarceratory dispositions or had charges dismissed through pretrial motion practice. The 2024 docket movement was steady, not headline-grabbing.</li>
  <li><strong>Multiple spring-2024 campus encampment defendants</strong> resolved misdemeanor matters through ACDs, community service, or dismissals. The roster of those still facing criminal exposure narrowed substantially through summer and fall 2024.</li>
</ul>

<h2>Did not come home in 2024</h2>

<ul>
  <li><strong>Leonard Peltier</strong> — parole denied July 2, 2024 (commuted to home confinement Jan 20, 2025 by Biden on his last day in office)</li>
  <li><strong>Daniel Hale</strong> — drone-program whistleblower; Biden left office without acting on his clemency petition</li>
  <li><strong>Reality Winner</strong> — out but on restrictive supervised release; the conditions remained through 2024</li>
  <li><strong>Mumia Abu-Jamal</strong> — 43rd year inside; PCRA litigation continued without an evidentiary hearing being granted</li>
  <li><strong>Marius Mason</strong> — sixteenth year inside; the May 2026 release was already in foreseeable view but not yet arrived</li>
  <li><strong>Jessica Reznicek</strong> — eighth year of an eight-year terrorism-enhanced sentence for pipeline sabotage that injured no one</li>
  <li><strong>Aafia Siddiqui</strong> — fourteenth year of an 86-year sentence</li>
  <li><strong>Joshua Schulte</strong> — sentenced February 2024 to 40 years for Vault 7</li>
  <li><strong>Steven Donziger</strong> — disbarred; Supreme Court cert denied April 22, 2024</li>
</ul>

<h2>Newly imprisoned, indicted, or significantly charged in 2024</h2>

<ul>
  <li><strong>Casey Goonan</strong> — federally indicted July 2024 on UC Berkeley police-car arson</li>
  <li><strong>The Merrimack 4</strong> — Elbit Systems blockaders; felony state charges pending</li>
  <li><strong>The spring 2024 campus encampment arrestees</strong> — 3,100+ aggregate; the still-pending subset is the year\'s largest single political-prisoner cohort</li>
  <li><strong>Multiple weapons-plant blockaders</strong> in Pittsburgh, Camden, Bethesda, Tucson, and elsewhere — felony charging in several jurisdictions</li>
  <li><strong>The federal grand-jury wave</strong> against Palestine solidarity organizers — testimony refusals, contempt referrals, the 2025 immigration-detention pivot already foreshadowed</li>
</ul>

<h2>Died in 2024</h2>

<p>The political-prisoner roster did not have a confirmed in-custody death in 2024 of a person commonly listed by NPPC and parallel organizations. This is the first year in roughly half a decade where that line in the year-end accounting is empty.</p>

<p>The year\'s closest-adjacent loss is the first anniversary of Ruchell Magee\'s October 17, 2023 death — the longest-held political prisoner in U.S. history, who came home in July 2023 and died three months later. The 2024 calendar marked the first full year in which the U.S. political-prisoner support tradition did not have his name on its inside-list.</p>

<h2>What 2024 means</h2>

<p>The year produced two large gains (Assange\'s release; the structural erosion of the Cop City RICO theory) and a substantial expansion of new prisoner cohorts (the Palestine solidarity prosecutions, the weapons-plant blockade defendants, the Goonan-type antifascist federal cases, the spring 2024 campus encampment arrestees still facing charges). Net, the U.S. political-prisoner roster grew. The legal architecture used to grow it — domestic-terrorism enhancements, coalition-as-conspiracy RICO theories, "material support" prosecutions, federal grand-jury sweeps — got more refined.</p>

<p>2025 inherits the cases. NPPC publishes a similar year-end accounting every December.</p>
BODY,
            'citations' => [
                ['title' => 'National Jericho Movement — U.S. political-prisoner roster', 'url' => 'https://www.thejerichomovement.com'],
                ['title' => 'Anarchist Black Cross Federation — Prisoner support', 'url' => 'https://www.abcf.net'],
                ['title' => 'Center for Constitutional Rights — Year-end political-prisoner brief', 'url' => 'https://ccrjustice.org/2024-year-end'],
                ['title' => 'Climate Defense Project — 2024 environmental-defender prosecution census', 'url' => 'https://climatedefenseproject.org/2024-census'],
                ['title' => 'Palestine Legal — 2024 repression year in review', 'url' => 'https://palestinelegal.org/2024-year-review'],
            ],
        ];
    }

    private function terrorismEscalation(): array {
        return [
            'slug'         => 'domestic-terrorism-charging-escalation-2024-pattern',
            'title'        => 'The Domestic-Terrorism Charging Escalation of 2024: How the Same Pattern Hit Cop City, Climate Defense, and Palestine Solidarity',
            'intro'        => 'Through 2024, U.S. federal and state prosecutors applied a near-identical charging architecture — domestic-terrorism enhancements, coalition-as-conspiracy RICO theories, material-support charges, money-laundering theories on bail-fund organizers, and federal grand-jury sweeps — across three nominally distinct movements: Stop Cop City, climate / pipeline defense, and Palestine solidarity. The pattern was the year\'s single most consequential U.S. political-prisoner trend.',
            'published_at' => '2024-12-20 12:00:00',
            'body'         => <<<'BODY'
<p><em>The architecture isn\'t new. The Earth Liberation Front and Animal Liberation Front prisoners of the 2005–2008 Green Scare faced an earlier version of the same template. The 1970s federal prosecutions of the Black Panthers, the American Indian Movement, the Puerto Rican independentistas, and the Weather Underground produced the precedents the post-September-11 federal apparatus inherited. What 2024 produced was the consolidation of the modern version of the template into a near-uniform federal-prosecution playbook, deployed at scale across multiple movements within the same calendar year.</em></p>

<h2>The five moves</h2>

<p><strong>One: Apply domestic-terrorism enhancements to non-violent property defense.</strong> The federal Sentencing Guideline § 3A1.4 enhancement was developed to address actual terrorism. Federal courts in 2024 applied or considered applying it in cases including Jessica Reznicek\'s pipeline-defense matter (already in custody), Stop Cop City forest defenders, and antifascist defendants. The state-court equivalent — Georgia\'s domestic-terrorism statute as applied to Atlanta forest defenders — became the model for other states\' legislative copycats through 2024.</p>

<p><strong>Two: Charge movements as racketeering enterprises.</strong> The Cop City RICO indictment of 61 defendants on a theory that movement organizing constituted an "enterprise" with predicate acts ranging from felony intimidation to handing out fliers was the year\'s most aggressive version of this move. The Atlanta Solidarity Fund prosecution — money-laundering and charity-fraud charges on bail-fund work — was the smaller, narrower version. Both theories survived 2024 in some form. The RICO theory was dismissed in 2025 on the authorization grounds defense counsel built through 2024; the money-laundering theory is still pending.</p>

<p><strong>Three: Use "material support" to reach speech and association.</strong> The October 2024 Treasury designation of Samidoun made the federal material-support statute usable against U.S. Palestine solidarity organizing on a scale not previously possible. Federal grand juries followed; the 2025 immigration-detention pivot was prefigured.</p>

<p><strong>Four: Charge bail-fund and movement-support work as criminal.</strong> Atlanta is the leading example. The implication is that supporting protesters financially or organizationally is itself a chargeable act. The template is now sitting on the shelf for every U.S. state.</p>

<p><strong>Five: Use federal grand juries as parallel investigatory machinery.</strong> Even where state cases stall, federal grand-jury subpoenas extract organizational records, testimony, donor lists, and movement intelligence under contempt threat. The 2024 wave against Palestine solidarity was the largest such deployment in roughly a decade.</p>

<h2>Why this pattern works</h2>

<p>The pattern works because the moves complement each other. Domestic-terrorism enhancements at sentencing produce the multi-year sentences that make plea-bargaining pressure overwhelming. RICO and material-support charging produce the multi-defendant case structure that fragments coalitions. Money-laundering charges on bail-fund work produce a chilling effect on the financial support that makes protest sustainable. Federal grand juries produce the intelligence database. Together they make every individual decision to participate in a movement, support a movement, or coordinate with a movement carry a non-trivial criminal-legal risk.</p>

<h2>The civil-society 2024 response</h2>

<p>The ACLU, the Center for Constitutional Rights, the Climate Defense Project, the National Lawyers Guild, the Movement for Black Lives, Palestine Legal, the Knight First Amendment Institute, FIRE, PEN America, the Reporters Committee for Freedom of the Press, the Brennan Center for Justice, and the U.N. Special Rapporteurs on the rights to freedom of peaceful assembly and association, on human rights defenders, on counter-terrorism and human rights, and on the protection of journalists, all spent 2024 documenting and pushing back on the pattern. The U.N. communications, the academic literature, the journalism, and the movement-defense legal briefings are now substantial.</p>

<h2>What changes the pattern</h2>

<p>The pattern is built on prosecutorial discretion. It is changeable only by prosecutorial discretion at federal and state levels — which, in the current political environment, is moving in the opposite direction. The next four years of U.S. politics will determine how much further the template is deployed. The 2024 record is the baseline. NPPC documents it as one continuous pattern across the three movements it has been most visibly applied against and treats the defendants in each as members of a single contemporary U.S. political-prisoner cohort.</p>
BODY,
            'citations' => [
                ['title' => 'Brennan Center for Justice — Domestic terrorism law and policy', 'url' => 'https://www.brennancenter.org/issues/protect-liberty-security/domestic-terrorism'],
                ['title' => 'Climate Defense Project — Movement-defense legal architecture', 'url' => 'https://climatedefenseproject.org'],
                ['title' => 'Center for Constitutional Rights — Charging trends', 'url' => 'https://ccrjustice.org'],
                ['title' => 'Knight First Amendment Institute — Material-support cases', 'url' => 'https://knightcolumbia.org/research/material-support-statute'],
                ['title' => 'Movement for Black Lives — Year-in-review on movement criminalization', 'url' => 'https://m4bl.org/2024-year-review'],
            ],
        ];
    }

    private function campusRepressionYear(): array {
        return [
            'slug'         => 'campus-repression-year-oct-2023-dec-2024-data',
            'title'        => 'The Campus Repression Year: October 2023 to December 2024 in Numbers',
            'intro'        => 'Between October 7, 2023 and the end of 2024, U.S. universities produced the largest sustained anti-protest disciplinary response of the post-1968 era. Three thousand-plus arrests, hundreds of faculty disciplined or fired, scores of student organizations suspended or banned, dozens of pro-Palestine speakers disinvited, multiple commencements canceled. This is the data.',
            'published_at' => '2024-12-22 10:00:00',
            'body'         => <<<'BODY'
<p><em>The 2024 U.S. campus disciplinary response to pro-Palestine organizing is one of the most-documented institutional-repression episodes of the contemporary period. The Crowd Counting Consortium, the National Lawyers Guild, the Civil Liberties Defense Center, PEN America, FIRE, Palestine Legal, Jewish Voice for Peace Academic Council, the Knight First Amendment Institute, the AAUP, and the Reporters Committee for Freedom of the Press have between them produced the dataset. Synthesized:</em></p>

<h2>Arrests</h2>

<p><strong>3,100–3,300 documented arrests</strong> at U.S. campus pro-Palestine actions from April 17, 2024 (Columbia encampment day one) to mid-June 2024. The largest single-institution arrest count was Columbia (282 at Hamilton Hall + earlier sweeps); the largest single-night police mobilization was the joint Columbia/CUNY raid on April 30. Total arrests since October 7, 2023 — including pre-encampment vigils, marches, sit-ins, and post-encampment summer/fall actions — exceeded 5,000 by year\'s end.</p>

<h2>Faculty</h2>

<p><strong>500-plus U.S. faculty members</strong> faced some form of institutional discipline during the period: arrests, suspensions, denials of tenure, terminations, withdrawal of fellowships, refusal of contract renewals, denial of leave. Notable named cases include Caroline Fohlin (Emory, tackled on camera), Steve Tamayo (UC Berkeley), the Columbia faculty suspensions arising from the encampment-defense walkouts, the multiple cases at Florida public universities triggered by state political pressure on the SUS system, and the textile of denials and dismissals at private institutions that did not produce headline coverage but did produce employment outcomes.</p>

<h2>Students</h2>

<p><strong>2,000-plus U.S. students</strong> were placed on disciplinary suspension, evicted from on-campus housing, banned from campus property, refused diplomas, prevented from registering for the following semester, or expelled. The post-arrest disciplinary process operates outside the criminal-legal system and produced outcomes more severe and durable than the criminal cases themselves in a substantial number of jurisdictions. Notable cohorts: the Columbia and Barnard suspensions; the UCLA-arrestee post-clearance disciplinary letters; the USC valedictorian removal of Asna Tabassum.</p>

<h2>Student organizations</h2>

<p><strong>50-plus chapters</strong> of Students for Justice in Palestine, Jewish Voice for Peace Campus Network, and adjacent groups were suspended, derecognized, banned from campus space, prohibited from on-campus fundraising, or formally dissolved by university administrative action through 2024. Tennessee, Florida, Texas, and Indiana saw the most aggressive state-political-pressure-driven derecognitions; private institutions saw a different but parallel pattern.</p>

<h2>Speakers and events</h2>

<p>Dozens of pro-Palestine speakers — including faculty, journalists, and organizers — were disinvited from U.S. campus speaking engagements through 2024. Multiple Palestinian-American academics were uninvited from named lectures and visiting positions. The pattern was documented in real time by PEN America, FIRE, and the Knight First Amendment Institute.</p>

<h2>Commencements</h2>

<p>USC canceled its valedictorian speech. Multiple institutions canceled or downsized commencement ceremonies in spring 2024 in response to ongoing protest. Several university presidents resigned during the period (Columbia\'s Shafik, others) as the disciplinary cycle collided with faculty no-confidence votes and trustee pressure.</p>

<h2>The Title VI / DOE pressure</h2>

<p>Through 2024 the U.S. Department of Education and the Office for Civil Rights initiated dozens of Title VI investigations of major U.S. universities arising from pro-Palestine protest activity. The investigations functioned, in practice, as additional federal pressure on universities to escalate disciplinary responses against pro-Palestine students.</p>

<h2>What this dataset establishes</h2>

<p>The 2024 U.S. campus disciplinary response was an institutional-repression event of a scale not seen since the 1968–1972 anti-Vietnam-War campus movement. It was carried out — across public and private institutions, across regions, across institution types — with substantial uniformity. The criminal-legal arrests are visible and trackable. The institutional-disciplinary record is less visible and in many respects more durable. NPPC tracks the cohort and publishes named defendants whose cases remain on criminal-court dockets. The 2024 campus year is part of the contemporary U.S. political-prisoner record.</p>
BODY,
            'citations' => [
                ['title' => 'Crowd Counting Consortium — 2024 dataset', 'url' => 'https://crowdcounting.org'],
                ['title' => 'PEN America — Campus protest response data', 'url' => 'https://pen.org/campus-2024-data/'],
                ['title' => 'FIRE — Disinvitation database', 'url' => 'https://www.thefire.org/research-learn/disinvitation-database'],
                ['title' => 'Palestine Legal — 2024 year in review', 'url' => 'https://palestinelegal.org/2024-year-review'],
                ['title' => 'AAUP — On the 2024 academic-freedom record', 'url' => 'https://www.aaup.org/2024-academic-freedom-report'],
            ],
        ];
    }

    private function bushnell(): array {
        return [
            'slug'         => 'aaron-bushnell-self-immolation-feb-2024-fbi-investigation',
            'title'        => 'Aaron Bushnell: An Act of Conscience, an FBI Investigation, and the Limits of the Political-Prisoner Framework',
            'intro'        => 'On February 25, 2024, U.S. Air Force active-duty service member Aaron Bushnell, 25, set himself on fire outside the Israeli embassy in Washington, D.C., declaring as he burned that he would no longer be complicit in genocide. He died of his injuries that night. He was not a political prisoner — he survived no carceral system because he did not survive at all — but his act, and the FBI investigation that followed of his networks, sits adjacent to the 2024 political-prisoner record in a way that warrants its own entry.',
            'published_at' => '2024-02-26 09:00:00',
            'body'         => <<<'BODY'
<p><em>Bushnell was an active-duty U.S. Air Force cyber-defense specialist at Joint Base San Antonio-Lackland in Texas. He had been in the Air Force for nearly four years. He had, by his own account in messages to friends and in the streamed final statement he posted online before his death, become unable to continue serving an institution whose direct material contribution to the war on Gaza he understood himself to be participating in.</em></p>

<h2>The act</h2>

<p>On the afternoon of February 25, 2024, Bushnell traveled to the Israeli embassy in Washington, D.C., set up a livestream on Twitch, walked toward the embassy entrance, poured an accelerant over himself, and ignited it. As he burned he called out: "I will no longer be complicit in genocide. I am about to engage in an extreme act of protest, but compared to what people have been experiencing in Palestine at the hands of their colonizers, it\'s not extreme at all." He continued shouting "Free Palestine" as he burned. Embassy security and responding D.C. police extinguished him; he was transported to Washington Hospital Center and died that evening of his injuries.</p>

<h2>The lineage</h2>

<p>Bushnell\'s act sits in a long tradition of political self-immolation as protest — most famously Thich Quang Duc in Saigon (1963), Norman Morrison outside the Pentagon (1965), Jan Palach in Prague (1969), Mohamed Bouazizi in Tunisia (2010), and most recently the Tibetan self-immolation cycle of the 2010s. In U.S.-veteran context, the parallel most often drawn is to Morrison, the 31-year-old Quaker who set himself on fire below Defense Secretary Robert McNamara\'s Pentagon office window in 1965 in protest of the U.S. war in Vietnam.</p>

<h2>The FBI investigation</h2>

<p>What makes Bushnell\'s case adjacent to the 2024 political-prisoner record is the federal investigation that followed his death. FBI agents — in the months after February 25 — interviewed members of his networks, his fellow Air Force service members, his Twitch and Discord-server contacts, his radical-mail-correspondence partners, and adjacent anti-war / Palestine-solidarity organizing communities in the San Antonio and online radical-left spaces he had frequented. Several of those interviewed reported, through Palestine Legal and the National Lawyers Guild, that they had been pressed on questions about whether Bushnell\'s act had been "organized" or "encouraged" by their networks.</p>

<p>No charges have been filed against any person in connection with Bushnell\'s death. No reasonable theory of criminal liability for the act of a 25-year-old active-duty Air Force member who chose to set himself on fire in protest has been articulated by any prosecutor. The investigative attention nevertheless functioned, in practical terms, as a chilling-effect operation on the networks of people who had known him.</p>

<h2>The aftermath</h2>

<p>Bushnell\'s memorial actions through 2024 — vigils, namings, fundraising-in-his-name for Palestinian-aid organizations — were attended by tens of thousands of people across U.S. cities. His final statement, his livestream, and the medical examiner\'s confirmation of cause of death (suicide by self-immolation) are part of the durable public record. The U.S. Air Force conducted an internal review that concluded no policy was violated by his off-duty conduct at the Israeli embassy.</p>

<h2>Why this is in the political-prisoner record</h2>

<p>Bushnell was not imprisoned. He died. NPPC includes his act in the 2024 record for the same reason political-prisoner support traditions across the world include named conscience-actors in their commemoration cycles: because the act, and the official response to the networks that knew the person, are part of the same political-repression apparatus that produces the prisoners whose names this organization publishes. The FBI interviews of Bushnell\'s associates in spring and summer 2024 were not unconnected to the federal grand-jury wave against Palestine solidarity organizers running in parallel. They are part of one federal posture.</p>

<p>Aaron Bushnell\'s name is in the year\'s record. NPPC marks February 25 every year.</p>
BODY,
            'citations' => [
                ['title' => 'Washington Post — Aaron Bushnell sets himself on fire outside Israeli embassy', 'url' => 'https://www.washingtonpost.com/national-security/2024/02/25/airman-self-immolation-israeli-embassy/'],
                ['title' => 'AP — Air Force member dies after setting himself on fire outside Israeli embassy', 'url' => 'https://apnews.com/article/airman-self-immolation-aaron-bushnell-2024'],
                ['title' => 'The Intercept — On Aaron Bushnell\'s networks and the FBI follow-up', 'url' => 'https://theintercept.com/2024/04/aaron-bushnell-fbi-investigation/'],
                ['title' => 'Palestine Legal — On the federal investigative response', 'url' => 'https://palestinelegal.org/news/2024/05/bushnell-investigation/'],
                ['title' => 'D.C. Office of the Chief Medical Examiner — Bushnell cause-of-death finding', 'url' => 'https://ocme.dc.gov/release/bushnell-2024'],
            ],
        ];
    }
}
