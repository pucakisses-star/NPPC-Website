<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publishes a news/explainer article on the "Broadview Six" case — the federal
 * conspiracy prosecution of six Chicago-area anti-ICE protesters that collapsed
 * in 2026 over grand-jury misconduct. Keyed by slug, idempotent (re-runs update
 * in place). Appears at /news/{slug} and in the news grid under "News".
 */
final class AddBroadviewSixArticle extends Command {
    protected $signature = 'articles:add-broadview-six';
    protected $description = 'Publish the Broadview Six explainer article';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $slug = 'broadview-six-ice-protest-case-collapses-2026';
        $title = 'The Broadview Six: A Federal ICE-Protest Conspiracy Case Collapses Over Grand-Jury Misconduct';
        $intro = "A rare federal conspiracy case against six Chicago-area anti-ICE protesters — among them congressional candidate Kat Abughazaleh — collapsed in 2026 after grand-jury transcripts showed that prosecutors, rebuffed by one grand jury, removed dissenting jurors and won an indictment from a second. Chicago's top federal prosecutor admitted 'significant errors,' the charges were dismissed with prejudice, and the cleared defendants are now seeking sanctions and evidence of whether the White House pressured the government to indict.";

        $body = <<<'BODY'
<p><em>The "Broadview Six" were charged with a rare federal conspiracy count after a protest outside an Immigration and Customs Enforcement facility in suburban Chicago. By the spring of 2026 the case had collapsed entirely — dismissed with prejudice after defense lawyers obtained grand-jury transcripts that, they argued, exposed gross prosecutorial misconduct. It is one of the few politically charged ICE-protest prosecutions of the Trump era to fall apart under a judge's scrutiny rather than end in a plea.</em></p>

<h2>A protest outside the Broadview ICE facility</h2>

<p>The case grew out of a demonstration on September 26, 2025, outside the ICE processing center in Broadview, a working-class suburb west of Chicago that became a flashpoint of "Operation Midway Blitz," the administration's intensified immigration crackdown across the Chicago area. Federal prosecutors alleged that the defendants, part of a larger crowd, surrounded and briefly blocked an immigration agent's van as it tried to leave the facility.</p>

<p>Protests at Broadview had been near-daily for weeks, drawing clergy, elected officials, and neighbors. What set this prosecution apart was not the conduct alleged but the charge the government chose to bring.</p>

<h2>A rare conspiracy charge against six</h2>

<p>Rather than the misdemeanor counts typically used for protest cases, prosecutors obtained a felony <strong>conspiracy</strong> indictment — a charge that treats the protesters as having agreed together to impede federal officers. The six defendants were a cross-section of Chicago civic and political life:</p>

<ul>
<li><strong>Kat Abughazaleh</strong> — a journalist and Democratic candidate for Congress;</li>
<li><strong>Andre Martin</strong> — a member of Abughazaleh's campaign staff;</li>
<li><strong>Brian Straw</strong> — an Oak Park village trustee;</li>
<li><strong>Michael Rabbitt</strong> — a Democratic committeeperson;</li>
<li><strong>Catherine "Cat" Sharp</strong> — a former Cook County Board candidate; and</li>
<li><strong>Joselyn Walsh</strong> — a musician.</li>
</ul>

<p>That a sitting congressional candidate and several local officials were charged with a conspiracy felony for conduct at a protest drew immediate alarm that the prosecution was political.</p>

<h2>The case falls apart</h2>

<p>It unraveled from the inside. When defense lawyers finally pried loose the grand-jury transcripts, they said the record showed that a <strong>first grand jury had refused to indict</strong>. Prosecutors then, according to the defense, removed grand jurors who had voiced doubts about the case and re-presented it to a second panel, which returned the indictment. One juror had reportedly called the government's theory a "crock of s---." The transcripts, defense attorneys argued, also showed prosecutors improperly "vouching" for the case and communicating with jurors outside the formal proceedings.</p>

<p>The government's position eroded in stages: charges against two defendants were dropped in March 2026, the felony conspiracy charge against the remaining defendants — including Abughazaleh — was dropped in April, and the last counts were dismissed on May 21, 2026. Crucially, the dismissal was <strong>with prejudice</strong>, meaning the case cannot be refiled. In court, U.S. Attorney <strong>Andrew Boutros</strong>, the top federal prosecutor in Chicago, acknowledged that his office had made "significant errors" in the grand-jury process.</p>

<h2>Now the defendants want answers — and sanctions</h2>

<p>Clearing the charges did not end the matter. The presiding judge floated a separate hearing on possible <strong>sanctions</strong> against the U.S. Attorney's Office for the conduct before the grand jury. The now-cleared defendants have filed motions seeking discovery — including any evidence that the prosecution was driven by <strong>pressure from the White House</strong> to indict. Separately, the former lead prosecutor on the case was reported to have been fired from a new job in Washington after the misconduct allegations surfaced, and other Broadview protesters have sued over the government's collection of their DNA.</p>

<h2>Why it matters</h2>

<p>The Broadview Six case is a window into how immigration-enforcement protest was prosecuted during the Midway Blitz: with an aggressive, rarely used conspiracy charge, brought against demonstrators that included a congressional candidate and local officeholders, and — the transcripts suggest — pushed past a grand jury that had already said no. Its collapse is the exception that illustrates the rule. Most protest defendants in this era face the choice between a plea and a costly federal trial; here, a judge's willingness to let the defense see the grand-jury record turned the case inside out. Whether that produces sanctions, or an accounting of who ordered the charges, is the question the cleared defendants are now pressing.</p>
BODY;

        $citations = [
            ['title' => 'CBS Chicago — All charges dismissed against "Broadview Six," defense says grand jury transcript revealed "gross misconduct"', 'url' => 'https://www.cbsnews.com/chicago/news/charges-dismissed-broadview-six-grand-jury-transcript/'],
            ['title' => 'Chicago Sun-Times — "Broadview Six" charges dropped as Chicago\'s top federal prosecutor admits case was tainted by misconduct', 'url' => 'https://chicago.suntimes.com/immigration/2026/05/21/broadview-ice-protest-grand-jury-transcript-kat-abughazaleh-trump'],
            ['title' => 'WBEZ Chicago — "Broadview 6" defendants reflect on a case doomed by the feds', 'url' => 'https://www.wbez.org/immigration/2026/06/01/broadview-six-midway-blitz-abughazaleh-straw-rabbit-perry-trump-ice-crime'],
            ['title' => 'WTTW — Feds drop all charges in "Broadview Six" case following closed-door meeting over grand jury', 'url' => 'https://news.wttw.com/2026/05/21/broadview-six-no-longer-set-trial-next-week-following-closed-door-meeting-over-grand'],
            ['title' => 'Capitol News Illinois — Now-cleared "Broadview 6" protesters seek evidence of White House pressure to indict', 'url' => 'https://capitolnewsillinois.com/news/now-cleared-broadview-6-immigration-protesters-seek-evidence-of-white-house-pressure-to-indict/'],
            ['title' => 'NBC Chicago — All remaining charges dismissed against "Broadview Six" defendants', 'url' => 'https://www.nbcchicago.com/news/local/chicago-politics/broadview-six-defendants-have-all-remaining-charges-dismissed-by-judge/3939029/'],
            ['title' => 'The New Republic — Feds forced to drop case against "Broadview Six" anti-ICE protesters', 'url' => 'https://newrepublic.com/post/210825/feds-drop-case-broadview-six-anti-ice-protesters'],
        ];

        $payload = [
            'title'          => $title,
            'intro'          => $intro,
            'body'           => $body,
            'category_id'    => $category->id,
            'author_id'      => $author->id,
            'published_at'   => Carbon::parse('2026-06-10 12:00:00'),
            'citations_json' => $citations,
        ];

        $existing = Article::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info("Updated: {$title}");
        } else {
            Article::create(['slug' => $slug] + $payload);
            $this->info("Created: {$title}");
        }

        $this->line("Live at /news/{$slug}");

        return self::SUCCESS;
    }
}
