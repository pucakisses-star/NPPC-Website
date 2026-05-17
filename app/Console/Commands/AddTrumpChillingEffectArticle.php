<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Restore content at the original placeholder slug
 *   /news/trump-administration-creates-chilling-effect-on-free-speech-by-weaponizeing-immigration-enforcement-to-silences-political-opposition
 * (note: typo in original slug preserved — that's the URL inbound traffic
 * arrives on, and changing the slug would break the existing links).
 *
 * Generic analytical piece on the second-term Trump administration's
 * pattern of using civil immigration authority to detain, deport, or
 * deter people for political speech — the through-line connecting
 * Khalil, Mahdawi, Chung, Öztürk, Kordia, and Rodríguez. Dated to the
 * point in spring 2025 when the pattern had crystallized to the point
 * it could be named.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddTrumpChillingEffectArticle extends Command {
    protected $signature = 'articles:add-trump-chilling-effect';
    protected $description = 'Restore Trump-admin chilling-effect / immigration-enforcement-against-speech article at its original slug';

    private const SLUG     = 'trump-administration-creates-chilling-effect-on-free-speech-by-weaponizeing-immigration-enforcement-to-silences-political-opposition';
    private const PUB_DATE = '2025-04-15 10:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>Within ninety days of the second Trump administration's return to the White House, a recognizable new template of political imprisonment had emerged: not criminal charges, but civil immigration detention; not for an alleged crime, but for an op-ed, a rally, a social-media post, a campus encampment. The cases are not isolated. They are a policy.</em></p>

<p>The pattern took shape almost instantly. In <strong>March 2025</strong>, federal immigration officers arrived at the Manhattan apartment of <strong>Mahmoud Khalil</strong>, a Columbia University graduate student and lead negotiator for the campus's spring-2024 Gaza solidarity encampment. Khalil was a lawful permanent resident — a green card holder, married to a U.S. citizen, expecting a child. He had not been charged with any crime. He was detained, flown 1,400 miles to an immigration prison in <strong>Jena, Louisiana</strong>, and the State Department invoked an obscure Cold War-era provision of the Immigration and Nationality Act to argue that his presence in the United States posed "potentially serious adverse foreign policy consequences."</p>

<p>The "consequence," in plain language, was that he had organized against the war in Gaza on a U.S. college campus.</p>

<h2>The template</h2>

<p>What happened to Khalil was not an experiment. It was the first deployment of a template the administration has now used repeatedly:</p>

<ul>
  <li><strong>Identify a person whose political speech the government wants to chill</strong> — typically a non-citizen organizing or writing about Palestine, Sudan, or U.S. immigration enforcement itself.</li>
  <li><strong>Avoid criminal charges</strong> — which would require indictment, counsel, public trial, and proof beyond a reasonable doubt — by routing the case through civil immigration authority instead.</li>
  <li><strong>Detain quickly and transfer far</strong> — typically to a remote ICE facility in Louisiana, Texas, or Alabama, away from family, counsel, and the local press.</li>
  <li><strong>Hold without bond</strong> on the theory that the underlying immigration question is unresolved.</li>
  <li><strong>Let the detention itself be the punishment</strong> — even when the person eventually wins, weeks or months of imprisonment have already silenced them and signaled to others.</li>
</ul>

<h2>The names</h2>

<p>Since March 2025 the same machinery has come for:</p>

<ul>
  <li><strong>Rumeysa Öztürk</strong>, a Tufts PhD student from Turkey, arrested on a Somerville sidewalk by plainclothes officers and flown to Louisiana — her only known "offense" being a campus op-ed she co-signed about Gaza.</li>
  <li><strong>Mohsen Mahdawi</strong>, a Columbia student and lawful permanent resident, detained at his green-card naturalization interview in Vermont — i.e., the federal government used a routine immigration appointment as a trap to seize someone for organizing against the war.</li>
  <li><strong>Yunseo Chung</strong>, a 21-year-old Columbia undergraduate who has lived in the U.S. since age 7, targeted for removal over participation in a campus sit-in.</li>
  <li><strong>Leqaa Kordia</strong>, a Palestinian woman from New Jersey, arrested in Newark over a visa overstay surfaced only after her participation in pro-Palestine campus protests.</li>
  <li><strong>Badar Khan Suri</strong>, a Georgetown postdoctoral fellow from India, snatched outside his Virginia home and accused of "spreading Hamas propaganda" — meaning, in the State Department's own words, posting about Gaza on social media.</li>
</ul>

<h2>The point</h2>

<p>The administration's defenders have characterized these cases as routine immigration enforcement against people who happened to be politically active. That framing is, on the government's own record, not honest. Khalil was a green-card holder. Mahdawi was a green-card holder. Chung has been a U.S. resident since elementary school. The unifying fact across these cases is not immigration status — which varies — but speech. And the unifying mechanism is not the criminal-legal system — which has built-in adversarial protections — but the immigration-enforcement apparatus, which doesn't.</p>

<p>That choice of forum is the policy. By routing political imprisonment through civil immigration authority instead of through Article III courts, the administration has built a parallel detention system for political speech that requires almost no due process, can be deployed almost instantly, transfers detainees thousands of miles from their support networks, and produces a chilling effect on speech that does not depend on conviction — only on detention.</p>

<h2>The chilling effect, by design</h2>

<p>The point of arresting a green-card holder for an op-ed is not, primarily, to punish that one writer. The point is to teach every other non-citizen — student, worker, journalist, organizer — what the cost of speaking out is now. PEN America, the Reporters Committee for Freedom of the Press, FIRE, and the Knight First Amendment Institute have all warned that this is happening in real time: campus speech is quieter than it was a year ago, op-eds are being pulled, signatories are asking to be removed from open letters, professors are advising international students not to attend protests.</p>

<p>That is not a side effect. That is the deliverable.</p>

<h2>What NPPC will do</h2>

<p>The National Political Prisoner Coalition will track each of these cases as it develops — by name, with case numbers, with detention facility — and add them to the U.S. political-prisoner roster the moment civil immigration detention is being used as a substitute for criminal prosecution against political speech. We will treat people imprisoned for what they said the same way we treat people imprisoned for what they did, because the United States is currently treating them the same way, only with fewer rights.</p>

<p>The legal labels matter to lawyers. They do not change the fact of the imprisonment. A person detained by federal officers for political speech is a political prisoner. That has been true throughout U.S. history, and it is true now.</p>
BODY;

        $data = [
            'title'        => 'Trump Administration Creates Chilling Effect on Free Speech by Weaponizing Immigration Enforcement to Silence Political Opposition',
            'intro'        => "Within ninety days of the second Trump administration's return to the White House, a recognizable new template of political imprisonment had emerged: not criminal charges, but civil immigration detention; not for an alleged crime, but for an op-ed, a rally, a social-media post, a campus encampment. The cases are not isolated. They are a policy.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'ACLU — Khalil v. Trump: arrest and detention of Mahmoud Khalil', 'url' => 'https://www.aclu.org/cases/khalil-v-trump'],
                ['title' => 'Knight First Amendment Institute — On the arrest and detention of Mahmoud Khalil', 'url' => 'https://knightcolumbia.org/blog/on-the-arrest-and-detention-of-mahmoud-khalil'],
                ['title' => 'PEN America — Detentions and deportations of student protesters threaten free expression', 'url' => 'https://pen.org/press-release/student-protester-detentions-2025/'],
                ['title' => 'FIRE — Mahmoud Khalil arrest is unconstitutional and dangerous', 'url' => 'https://www.thefire.org/news/mahmoud-khalil-arrest-unconstitutional-and-dangerous'],
                ['title' => 'New York Times — Rumeysa Öztürk arrest on Somerville street', 'url' => 'https://www.nytimes.com/2025/03/26/us/tufts-student-detained-ozturk.html'],
                ['title' => 'AP — Mohsen Mahdawi detained at green-card interview', 'url' => 'https://apnews.com/article/mohsen-mahdawi-columbia-immigration-vermont'],
                ['title' => 'Reuters — Yunseo Chung deportation case', 'url' => 'https://www.reuters.com/world/us/yunseo-chung-columbia-deportation/'],
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
