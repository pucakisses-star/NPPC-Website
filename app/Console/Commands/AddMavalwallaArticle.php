<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddMavalwallaArticle extends Command
{
    protected $signature = 'articles:add-mavalwalla';
    protected $description = 'Add a News article and Prisoner record for Bajun Mavalwalla II — US Army Afghanistan war veteran charged with federal conspiracy after a June 2025 protest against ICE in Spokane.';

    public function handle(): int
    {
        DB::transaction(function () {
            // ─── Author ──────────────────────────────────────────────
            $author = Author::firstOrCreate(['name' => 'Aaron Glantz (The Guardian)']);

            // ─── Category ────────────────────────────────────────────
            $category = Category::firstOrCreate(
                ['slug' => 'news'],
                ['title' => 'News']
            );

            // ─── Article ─────────────────────────────────────────────
            $title = 'FBI arrests US Army veteran Bajun Mavalwalla II for "conspiracy" over Spokane ICE protest';

            if (! Article::where('title', $title)->exists()) {
                $intro = "Bajun Mavalwalla II — a former U.S. Army sergeant who survived a roadside-bomb blast on a special-operations mission in Afghanistan — was charged in July 2025 with \"conspiracy to impede or injure officers\" after joining a June 11 demonstration against federal Immigration and Customs Enforcement in Spokane, Washington. Legal experts say the case marks an escalation in the Trump administration's attacks on First Amendment rights and an early test of how broadly the federal conspiracy statute can be wielded against ICE protesters.";

                $body = $this->buildBody();

                $citations = [
                    [
                        'title'  => 'Alarm after FBI arrests US army veteran for "conspiracy" over protest against Ice',
                        'author' => 'Aaron Glantz',
                        'source' => 'The Guardian',
                        'date'   => '2025-09-02',
                        'url'    => 'https://www.theguardian.com/us-news/2025/sep/02/fbi-arrest-us-army-veteran-ice-protest',
                    ],
                ];

                Article::create([
                    'title'          => $title,
                    'intro'          => $intro,
                    'body'           => $body,
                    'author_id'      => $author->id,
                    'category_id'    => $category->id,
                    'published_at'   => Carbon::parse('2025-09-02 06:00:00 -0400'),
                    'image_caption'  => 'Bajun Mavalwalla II and his father, serving in Afghanistan. Photograph: Courtesy Mavalwalla family / The Guardian.',
                    'citations_json' => json_encode($citations),
                ]);

                $this->info("Created article: {$title}");
            } else {
                $this->warn("Article already exists; skipping.");
            }

            // ─── Prisoner record ─────────────────────────────────────
            if (! Prisoner::where('name', 'Bajun Mavalwalla II')->exists()) {
                $institution = Institution::firstOrCreate(
                    ['name' => 'United States District Court, Eastern District of Washington'],
                    ['city' => 'Spokane', 'state' => 'Washington']
                );

                $prisoner = Prisoner::create([
                    'name'         => 'Bajun Mavalwalla II',
                    'first_name'   => 'Bajun',
                    'last_name'    => 'Mavalwalla',
                    'gender'       => 'Male',
                    'state'        => 'Washington',
                    'era'          => '2020s',
                    'ideologies'   => ['Anti-war', 'Pacifist', 'Immigrant solidarity'],
                    'affiliation'  => ['Spokane anti-ICE protesters', 'U.S. Army (veteran)'],
                    'in_custody'   => false,
                    'released'     => true,
                    'description'  => "Bajun Mavalwalla II is a 35-year-old former U.S. Army sergeant who served in Afghanistan on a special-operations mission, where he survived a roadside-bomb blast and was awarded a disability rating for service-connected PTSD. He held a top-secret clearance and trained as a crypto-linguist. After leaving the military he used his GI Bill to earn a degree in sustainable communities from Sonoma State University and settled in Spokane, Washington with his girlfriend Katelyn Gaston, a nurse and fellow Afghanistan war veteran.\n\nOn June 11, 2025, Mavalwalla joined a public demonstration in Spokane against an ICE transport carrying two Venezuelan immigrants who had entered the United States legally and were petitioning for asylum when ICE detained them. The protest was called by former Spokane city council president Ben Stuckart in a public social-media post (\"I am going to sit in front of the bus. Feel free to join me.\"). Other demonstrators slashed the tires of the ICE transport van and broke its windshield; Mavalwalla was not among the more than two dozen people arrested at the scene that day.\n\nMore than a month later, at 6 a.m. on July 15, 2025 — moving day for the dream home Mavalwalla and Gaston had just bought with a VA-backed loan — FBI agents arrived at Mavalwalla's door and arrested him on a federal indictment. He was charged with one count of \"conspiracy to impede or injure officers\" under 18 U.S.C. § 372 alongside eight co-defendants including Stuckart. The maximum statutory penalty is six years in federal prison, a \$250,000 fine, and three years of supervised release.\n\nMavalwalla pleaded not guilty. He was released on his own recognizance pending trial — a federal judge granted him permission to travel to Disneyland for a previously planned family vacation. He has no criminal record. The indictment was returned two days after the previous acting U.S. Attorney for the Eastern District of Washington, career prosecutor Richard Barker, resigned; the case is being prosecuted under acting U.S. Attorney Pete Serrano, a former Silent Majority Foundation litigator with no prosecutorial experience who has publicly described January 6 Capitol rioters as \"political prisoners.\"\n\nMavalwalla's family traces a six-generation commitment to nonviolent resistance: his great-great-grand-uncle Parsee Rustomjee worked alongside Mahatma Gandhi in South Africa during the launch of the satyagraha campaign against British imperialism, and Gandhi was godfather to Mavalwalla's great-grandmother. After Gandhi's 1948 assassination, fragments of his burial shroud were distributed among his closest confidants, including the Mavalwalla family — a fragment of which is held today by Mavalwalla's mother, U.S. Army veteran Ellyn Mavalwalla. His father, retired Army intelligence officer Bajun Ray Mavalwalla, earned three Bronze Stars in tours in Iraq and Afghanistan.\n\nLegal scholars cited in the Guardian's reporting characterize the federal conspiracy charge as an early test of how broadly the Trump administration's Justice Department will use 18 U.S.C. § 372 — a statute that does not require proof of a specific assaultive act, only of an agreement to act in concert — against First Amendment-protected protest activity.",
                ]);

                PrisonerCase::create([
                    'prisoner_id'        => $prisoner->id,
                    'institution_id'     => $institution->id,
                    'charges'            => 'Federal indictment, 18 U.S.C. § 372 — conspiracy to impede or injure officers, in connection with the June 11, 2025 demonstration against an ICE transport at the federal facility in Spokane, Washington',
                    'arrest_date'        => '2025-07-15',
                    'incarceration_date' => '2025-07-15',
                    'release_date'       => '2025-07-15',
                    'plead'              => 'Not guilty',
                    'sentence'           => 'Released on own recognizance pending trial; maximum statutory penalty if convicted is 6 years federal prison, $250,000 fine, and 3 years supervised release',
                ]);

                $this->info("Created prisoner record: {$prisoner->name}");
            } else {
                $this->warn("Prisoner record for Bajun Mavalwalla II already exists; skipping.");
            }
        });

        return self::SUCCESS;
    }

    private function buildBody(): string
    {
        return <<<HTML
<p>The arrest of a U.S. Army veteran who protested against the Trump administration's immigration crackdown has raised alarms among legal experts and fellow veterans familiar with his service in Afghanistan.</p>

<p>Bajun Mavalwalla II — a former Army sergeant who survived a roadside bomb blast on a special-operations mission in Afghanistan — was charged in July with "conspiracy to impede or injure officers" after joining a demonstration against federal Immigration and Customs Enforcement (ICE) in Spokane, Washington.</p>

<p>Legal experts say the case marks an escalation in the administration's attacks on First Amendment rights. Afghanistan war veterans who know him say the case against Mavalwalla appears unjust.</p>

<blockquote><p>"Here's a guy who held a top secret clearance and was privy to some of the most sensitive information we have, who served in a combat zone. To see him treated like this really sticks in my craw."<br><em>— Kenneth Koop, retired colonel who trained the Afghan military and police during Mavalwalla's deployment</em></p></blockquote>

<p>The June 11 protest against ICE that led to Mavalwalla's arrest was confrontational, leaving a government van's windshield smashed and tires slashed, but Mavalwalla was not among the more than two dozen people arrested at the scene. More than a month passed before the FBI arrived at his door on July 15.</p>

<p>The 35-year-old, who used his GI Bill to earn a degree in sustainable communities from Sonoma State University, was set to move into a 3,000-square-foot house that day, which he had bought with his girlfriend, Katelyn Gaston — a nurse and fellow Afghanistan war veteran — with the help of a loan backed by the Department of Veterans Affairs.</p>

<p>Mavalwalla's father, a retired U.S. Army intelligence officer with three Bronze Stars earned during tours in Iraq and Afghanistan, brought his truck for the occasion. He planned to move his son into a dream home in a bucolic, southern section of Spokane that was large enough to accommodate their blended family.</p>

<p>But at 6 a.m. the FBI knocked on Mavalwalla's door and arrested him. Cell-phone video shot by Mavalwalla's father shows the veteran — tall, fit, with wire-rimmed glasses, tight ponytail and trim goatee — smiling in apparent disbelief, his hands shackled behind his back.</p>

<blockquote><p>"This is not how I planned to spend my moving day. I'm a military veteran. I'm an American citizen."<br><em>— Bajun Mavalwalla II, as agents searched his pockets and forced him into a black pickup truck</em></p></blockquote>

<p>At 3 p.m., Mavalwalla, who receives disability compensation for post-traumatic stress disorder connected to his service in Afghanistan, appeared in federal court along with eight other people indicted in connection with a protest against an ICE transport that occurred a month earlier.</p>

<p>While the indictment alleges other protesters struck federal officers and let the air out of the tires of an ICE transport, Mavalwalla was not charged with obstruction or assault. Instead, he was charged with "conspiracy to impede or injure officers" under 18 U.S.C. § 372.</p>

<p>According to the indictment, Mavalwalla and his co-defendants "physically blocked the drive-way of the federal facility and/or physically pushed against officers despite orders to disperse and efforts to remove them from the property." Mavalwalla, who has no criminal record, pleaded not guilty.</p>

<p>The conspiracy count carries a maximum penalty of six years in prison, a \$250,000 fine and three years of supervised release. He was released on his own recognizance while awaiting trial — a judge even gave him permission to travel to Disneyland for a previously planned family vacation.</p>

<h2>"He's a test case to see how far they can go"</h2>

<p>The U.S. Attorney's Office in Spokane, which brought the charges, declined to comment, citing an ongoing investigation.</p>

<p>The indictment was handed down two days after career prosecutor Richard Barker, the acting U.S. Attorney for Eastern Washington state, resigned. In a social post, Barker called his exit "a very difficult decision." "I am grateful that I never had to sign an indictment or file a brief that I didn't believe in," he wrote.</p>

<p>The current acting U.S. Attorney, nominated for the permanent post by Donald Trump, is Pete Serrano — a former litigator for the Silent Majority Foundation, a conservative advocacy group. In February, Serrano filed an amicus brief in support of Trump's executive order to end birthright citizenship, a position at odds with the 14th Amendment. He has no prosecutorial experience and has publicly described the January 6 U.S. Capitol rioters as "political prisoners." Senator Patty Murray (D-WA) has pledged to block his confirmation.</p>

<p>Legal experts say the conspiracy charges against Mavalwalla underscore the lengths the Trump administration will go to quash protests against ICE, giving the immigration agency a free hand as it steps up raids, adds agents and seeks to achieve the president's goal of 3,000 deportations per day.</p>

<p>So far, the administration has primarily charged demonstrators for assault and obstruction — acts that typically involve a victim and an assailant. But a federal conspiracy charge is a crime of intent. Prosecutors would just have to prove that defendants agreed in concert to impede or injure an officer.</p>

<blockquote><p>"He's a test case to see how far they can go."<br><em>— Luis Miranda, former chief spokesperson for the Department of Homeland Security under Joe Biden</em></p></blockquote>

<h2>"Honest, direct, polite and very trustworthy"</h2>

<p>The charges against Mavalwalla sent shockwaves through a tight community of veterans with connections forged in Afghanistan. After his arrest, Mavalwalla's commanding officer, retired Col. Charles Hancock, wrote on Facebook that he knew the trained crypto-linguist to be "honest, direct, polite and very trustworthy" and was "deeply concerned about the current state of affairs in our country."</p>

<p>Koop, the retired colonel, said Mavalwalla put the diplomatic connections he gained due to his security clearance at the disposal of Koop's translator, who escaped Afghanistan and otherwise might have been murdered by the Taliban after the 2021 U.S. withdrawal. As word spread that Mavalwalla knew how to rescue people in the frenetic days after the fall of Kabul, more people reached out.</p>

<p>Erin Piper, the community director at a church in Livonia, Michigan, called Mavalwalla after learning a relative of one of her son's friends had worked as a custodian on a U.S. military base in Afghanistan. Mavalwalla located safe houses for 20 members of the former custodian's extended family and at least a dozen other Afghan civilians whose relatives collaborated with the U.S. military, helped them acquire travel documents, arranged for safe transport overland to Pakistan, and raised \$130,000 to pay expenses.</p>

<blockquote><p>"It does no good for us to neglect those right in front of us. You cannot save the world. It's good to try though."<br><em>— Bajun Mavalwalla II in text messages to Erin Piper, September 25, 2021</em></p></blockquote>

<h2>"An issue of selective prosecution"</h2>

<p>Mavalwalla was one of hundreds of people to respond to a June 11 social-media post from former Spokane city council president Ben Stuckart that encouraged protesters to block an ICE transport they believed would carry two Venezuelan immigrants who were in the country legally, petitioning for asylum when they were detained. "I am going to sit in front of the bus," Stuckart wrote. "Feel free to join me."</p>

<p>In interviews, former prosecutors said the conspiracy statute is broad and affords the Trump administration potentially sweeping powers.</p>

<blockquote><p>"Federal conspiracy charges are a wondrous thing. It is a vast net which you can use to catch a bunch of people. The major issue in a conspiracy case is intent. You have to prove an agreement. You don't have to prove that people sat down together and made a pledge. You don't even have to write up an agreement they have verbally — but you have to prove that these people agreed to act in concert."<br><em>— Bruce Antkowiak, former federal prosecutor, Saint Vincent College</em></p></blockquote>

<blockquote><p>"It seems like what we have here is an issue of selective prosecution [that will have a] chilling effect on free speech under the First Amendment."<br><em>— Robert Chang, law professor, University of California, Irvine</em></p></blockquote>

<p>Antkowiak said he expected the Justice Department to bring conspiracy charges more frequently in the months ahead, given the Trump administration's desire for ICE agents to pursue an agenda of rapid deportations unhindered. Jennifer Chacón, a Stanford University law professor who studies the intersection of immigration and criminal law, said she would not be surprised if ICE increased monitoring of social media to bring more cases like the one against Mavalwalla.</p>

<blockquote><p>"You could view this as an attempt to send a message to everyone who feels a sense of justice and moral outrage over ICE raids — you could face prosecution, too."<br><em>— Jennifer Chacón, law professor, Stanford University</em></p></blockquote>

<p>Mavalwalla's father, retired intelligence officer Bajun Ray Mavalwalla, said he believed his son had been racially profiled — that in reviewing footage from the demonstration, federal authorities had fixated on the demonstrator "with a funny name." He said he worried the United States was being "taken over by fascists," but also that the promise of America that drew his family here generations ago would endure.</p>

<h2>Gandhi's legacy</h2>

<p>In addition to military service, a commitment to peaceful protest has been at the heart of the Mavalwalla family for generations. Mavalwalla II's great-great-grand-uncle, Parsee Rustomjee, worked with Mahatma Gandhi in South Africa and supported the Indian independence leader when he launched his legendary, nonviolent revolution against British imperialism.</p>

<p>The two families were close — according to the family, Gandhi was godfather to Mavalwalla's great-grandmother — "and Bajun grew up with stories," his mother said, with social justice at the center. After Gandhi was assassinated in 1948, his burial shroud was distributed among his closest confidants, including members of Mavalwalla's family, and is now held by Mavalwalla's mother.</p>

<p>Three days after the protest, his mother texted him: "Channel your inner Gandhi." "I know, mom," Mavalwalla replied. "Always non-violence."</p>

<hr>

<p><em>Adapted from "Alarm after FBI arrests US army veteran for 'conspiracy' over protest against Ice" by Aaron Glantz, The Guardian, September 2, 2025. <a href="https://www.theguardian.com/us-news/2025/sep/02/fbi-arrest-us-army-veteran-ice-protest" rel="noopener" target="_blank">Read the original report at The Guardian.</a></em></p>
HTML;
    }
}
