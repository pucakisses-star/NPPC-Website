<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publish article on the DOJ's February 27, 2026 superseding
 * indictment expanding charges from 9 to 39 defendants in the
 * Cities Church (St. Paul, MN) anti-ICE protest, where the
 * pastor — David Easterwood — is also the local ICE Field
 * Office Director. Charges include conspiracy against religious
 * freedom rights (18 U.S.C. §241) and 18 U.S.C. §247 (FACE
 * Act–style church-interference) counts. Surfaced by Democracy
 * Now! headline on March 3, 2026.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddCitiesChurch30Article extends Command {
    protected $signature = 'articles:add-cities-church-30';
    protected $description = 'Publish article on the DOJ superseding indictment of 39 Cities Church protesters';

    private const SLUG     = 'doj-indicts-39-cities-church-anti-ice-protesters-st-paul-2026';
    private const PUB_DATE = '2026-03-03 20:33:01';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>The Justice Department's superseding indictment in the Cities Church protest case grows from nine defendants to thirty-nine, charging every named participant — including former CNN anchor Don Lemon and Minnesota reporter Georgia Fort, both there as working journalists — with conspiracy against religious freedom. The pastor whose service was interrupted is also the federal ICE official whose agency had killed a 37-year-old mother of three two weeks earlier.</em></p>

<p>On <strong>February 27, 2026</strong>, a federal grand jury in Minnesota returned a superseding indictment expanding the Department of Justice's prosecution of demonstrators who entered <strong>Cities Church</strong> in St. Paul on January 19, 2026 to protest the local ICE Field Office Director — who is also <strong>Pastor David Easterwood</strong> of that church. The expanded indictment brings the number of charged defendants from <strong>9 to 39</strong>. Each defendant faces two federal counts: <strong>conspiracy against the right of religious freedom</strong> and <strong>efforts to injure, intimidate, or interfere with the exercise of religious freedom</strong> — statutory cousins of the Church Arson Prevention and FACE Acts that were originally enacted to protect civil-rights-era Black churches and reproductive-health clinics from political violence.</p>

<h2>What the protest was about</h2>

<p>The action — organized under the name <strong>"Operation Pullup"</strong> — came less than two weeks after the <strong>January 7, 2026</strong> killing of <strong>Renee Good</strong>, a 37-year-old mother of three, by an ICE agent who fired into the vehicle she was driving during enforcement operations in the Twin Cities. Her death, captured on multiple cameras and circulated widely on social media, was the catalyst. The protesters who walked into Cities Church chanted "ICE out" and "Justice for Renee Good," and physically occupied the church's center aisle and front pews. They did not, by the government's own indictment, damage property or use force against any worshipper.</p>

<p>The protest targeted the dual role of the pastor — <strong>David Easterwood</strong> serves Cities Church (Southern Baptist Convention) on Sundays and runs the U.S. Immigration and Customs Enforcement Twin Cities field office the rest of the week. He was the supervisor responsible for "Operation Metro Surge," the federal enforcement campaign that, at its peak, deployed up to 3,000 ICE agents in Minnesota and produced documented incidents of officers breaking car windows, pepper-spraying demonstrators, and entering homes without judicial warrants. Renee Good was killed during that operation.</p>

<h2>How the prosecution grew</h2>

<p>The Justice Department moved aggressively from the start. After the protest, federal prosecutors sought charges against nine of the named participants. On <strong>January 22, 2026</strong>, a U.S. magistrate judge in St. Paul rejected the DOJ's initial charging papers — an unusual moment of judicial pushback at the magistrate stage. The Department of Justice responded by going around the magistrate to a federal grand jury, which returned an indictment on <strong>January 29, 2026</strong>, made public the next day.</p>

<p>The February 27 superseding indictment quadrupled the defendant list to 39 and added new conspiracy counts. <strong>Attorney General Pam Bondi</strong> announced the expansion personally, telling reporters: <em>"If you do so, you cannot hide from us — we will find you, arrest you, and prosecute you."</em> Her framing positioned the prosecution as protection of religious institutions; civil-rights and press-freedom organizations have framed it, equally directly, as the federal use of religious-freedom statutes — originally written to shield Black congregations from white-supremacist arson and clinic patients from Operation Rescue blockades — to criminalize protest of federal immigration enforcement.</p>

<h2>The press-freedom defendants</h2>

<p>Two of the 39 defendants stand out: <strong>Don Lemon</strong>, the former CNN anchor, and <strong>Georgia Fort</strong>, a Minnesota investigative reporter. Both have entered not-guilty pleas. Both say they were inside the church <strong>as working journalists</strong> documenting the protest — Lemon for his independent platform, Fort for her ongoing reporting on Operation Metro Surge — and that prosecuting them under §241 turns ordinary journalism into federal conspiracy. Their attorneys have indicated they will mount First Amendment / Press Clause defenses; the Reporters Committee for Freedom of the Press has been monitoring the case alongside its parallel work on the ICE-detention prosecution of Nashville journalist Estefany Rodríguez Flórez.</p>

<h2>The broader pattern</h2>

<p>The Cities Church indictment is one piece of a recognizable federal posture. The same Justice Department, run by the same Attorney General, has in recent months brought:</p>

<ul>
  <li>The Mavalwalla conspiracy prosecution of an Army veteran for the Spokane DHS protest — the first conspiracy indictment of an anti-ICE protester in the second Trump term.</li>
  <li>The Prairieland Texas verdicts: nine defendants convicted on most counts for the 2024 demonstration at an ICE-jail construction site.</li>
  <li>The Stop Cop City RICO sweep (since dismissed) and the 2026 Cobb County re-indictment of three of those defendants on arson-of-lands charges.</li>
  <li>The ICE detentions of journalists Estefany Rodríguez Flórez and Mario Guevara, and of student speakers Khalil, Mahdawi, Chung, Öztürk, and Kordia under the Rubio "foreign policy deportability" memo.</li>
</ul>

<p>Taken together, the Cities Church 39 sit inside a clearly intentional federal strategy: use of statutes originally designed to protect protected groups from political violence — religious-freedom laws, the Espionage Act, the federal ICE-impeding statute, RICO, and the immigration-detention apparatus — against ordinary participants in protest, journalism, and student organizing whose viewpoint the executive branch finds inconvenient.</p>

<p>The case heads next to preliminary motions in U.S. District Court for the District of Minnesota. Defense teams are coordinating; bail-fund organizing and court-watch mobilizations are being run by the local CAARPR-affiliated and Twin Cities movement legal-defense networks. Updates will be posted as the docket moves.</p>
BODY;

        $data = [
            'title'        => 'DOJ Expands Cities Church Indictment to 39 — Including Two Journalists — for Anti-ICE Protest of Pastor Who Runs the Local Field Office',
            'intro'        => "The Justice Department's superseding indictment in the Cities Church protest case grows from nine defendants to thirty-nine, charging every named participant — including former CNN anchor Don Lemon and Minnesota reporter Georgia Fort, both there as working journalists — with conspiracy against religious freedom. The pastor whose service was interrupted is also the federal ICE official whose agency had killed a 37-year-old mother of three two weeks earlier.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'Democracy Now! — DOJ Indicts Another 30 People over Minnesota Church Protest', 'url' => 'https://www.democracynow.org/2026/3/3/headlines/doj_indicts_another_30_people_over_minnesota_church_protest_of_pastor_who_works_for_ice'],
                ['title' => 'Al Jazeera — Trump administration charges 30 more people for Minnesota church protest', 'url' => 'https://www.aljazeera.com/news/2026/2/28/trump-administration-charges-30-more-people-for-minnesota-church-protest'],
                ['title' => 'NBC News — Justice Department indicts 30 more in anti-ICE church protest in Minnesota', 'url' => 'https://www.nbcnews.com/news/us-news/justice-department-indicts-30-anti-ice-church-protest-rcna261021'],
                ['title' => 'NPR — Anti-ICE protest at Minnesota church leads to 3 arrests but no charges for a journalist (Jan 2026)', 'url' => 'https://www.npr.org/2026/01/22/g-s1-106899/minnesota-church-protest-arrests-pam-bondi-don-lemon'],
                ['title' => 'CNN — Trump officials investigate protesters who interrupted Minnesota church service, targeting ICE official', 'url' => 'https://www.cnn.com/2026/01/19/us/st-paul-minnesota-church-protest-investigation'],
                ['title' => 'PBS NewsHour — DOJ says it will investigate, press charges after activists disrupt church where Minnesota ICE official is a pastor', 'url' => 'https://www.pbs.org/newshour/nation/doj-says-it-will-investigate-press-charges-after-activists-disrupt-church-where-minnesota-ice-official-is-a-pastor'],
                ['title' => '@democracynow on X (origin tweet)', 'url' => 'https://x.com/democracynow/status/2028931673174184413'],
            ],
        ];

        $existing = Article::where('slug', self::SLUG)->first();
        if ($existing) {
            $existing->update($data);
            $this->info('Updated article: '.$data['title']);
        } else {
            Article::create(['slug' => self::SLUG] + $data);
            $this->info('Created article: '.$data['title']);
        }

        return self::SUCCESS;
    }
}
