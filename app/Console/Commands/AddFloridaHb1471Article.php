<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Publish article on Florida HB 1471 (April 6, 2026) — the law
 * allowing the governor and cabinet to designate "domestic
 * terrorist organizations," signed after a federal judge blocked
 * DeSantis's December 2025 executive order labelling CAIR and
 * the Muslim Brotherhood as terrorist groups.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddFloridaHb1471Article extends Command {
    protected $signature = 'articles:add-florida-hb1471';
    protected $description = 'Publish article on Florida HB 1471 domestic-terrorist designation law';

    private const SLUG       = 'florida-hb1471-domestic-terrorist-designation-cair-2026';
    private const IMAGE_URL  = 'https://pbs.twimg.com/media/HFQ4zKgXQAEKXtq.jpg';
    private const PUB_DATE   = '2026-04-07 02:16:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        // Pull the tweet image so the article has artwork.
        $imagePath = 'articles/'.self::SLUG.'.jpg';
        try {
            if (! Storage::disk('public')->exists($imagePath)) {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_URL);
                if ($resp->successful() && strlen($resp->body()) > 1000) {
                    Storage::disk('public')->put($imagePath, $resp->body());
                    $this->info('Saved article image to '.$imagePath);
                } else {
                    $imagePath = self::IMAGE_URL;
                    $this->warn('Image download failed — using remote URL.');
                }
            }
        } catch (\Throwable $e) {
            $imagePath = self::IMAGE_URL;
            $this->warn('Image fetch error: '.$e->getMessage());
        }

        $body = <<<'BODY'
<p><em>The Florida governor signs HB 1471 at a University of South Florida event one month after a federal judge enjoined his December executive order targeting CAIR. The new statute gives the governor and three cabinet members unilateral authority to designate "domestic terrorist organizations" — and allows state universities to expel students for "promoting" them.</em></p>

<p>On Monday afternoon, April 6, 2026, Florida Governor Ron DeSantis signed <strong>HB 1471</strong> into law at a press event on the University of South Florida campus in Tampa. The bill, sponsored by Rep. Hillary Cassel (R-Dania Beach), with a companion Senate bill (SB 1632) sponsored by Sen. Erin Grall (R-Vero Beach) and reportedly drafted by the governor's own staff, allows a handful of statewide officials to designate any organization — foreign or domestic — as a "terrorist organization" under Florida law, with consequences that fall not only on the group itself but on students who "promote" it at any of Florida's public colleges and universities.</p>

<p>The law takes effect <strong>July 1, 2026</strong>.</p>

<h2>What the law does</h2>

<p>Under HB 1471, the state's chief of domestic security — currently Florida Department of Law Enforcement Commissioner Mark Glass — may submit a written designation recommendation to the governor and the three elected members of the Florida Cabinet (the agriculture commissioner, the chief financial officer, and the attorney general). If the governor and Cabinet approve, the designation takes effect immediately. There is no requirement of judicial finding, no legislative ratification, no advance notice to the designated group. Within seven days the designation is published in the Florida Administrative Register; within thirty days, the organization or any member may file a challenge in the state Circuit Court in Leon County.</p>

<p>The triggering standard, drawn from Florida Statute 775.30, allows designation of any group whose conduct is "intended to intimidate, injure, or coerce a civilian population; influence the policy of a government by intimidation or coercion; or affect the conduct of government through destruction of property, assassination, murder, kidnapping, or aircraft piracy." The standard is broad enough, a University of Florida law professor told reporters, that "any group could be surveilled under it."</p>

<p>The law also forbids state-funded schools from maintaining "affiliations" with designated organizations and authorizes universities to expel students who "promote" them — without any requirement that a criminal conviction precede expulsion. And it bars Florida courts from enforcing any provision of Sharia law in family, civil, or commercial proceedings.</p>

<h2>What it was designed to do</h2>

<p>The sequencing of events makes clear what HB 1471 was written to accomplish. In <strong>December 2025</strong>, DeSantis issued an executive order designating the <strong>Council on American-Islamic Relations (CAIR)</strong> and the <strong>Muslim Brotherhood</strong> as terrorist organizations. CAIR Florida sued. On <strong>March 4, 2026</strong>, U.S. District Judge <strong>Mark Walker</strong> issued a preliminary injunction blocking the executive order, finding that it violated CAIR's First Amendment rights by targeting and threatening those who provide the organization with material support.</p>

<p>HB 1471 was already moving through the legislature when the injunction landed. It passed the House <strong>80–25</strong> and the Senate <strong>25–11</strong>, on straight party-line votes. CAIR Florida's attorney described the result as "codifying an illegal order to sustain its chilling effect even while litigation works through the courts."</p>

<blockquote>"We'll do millions for public safety. Millions for education. But never one red cent for jihad."<br><em>— Gov. Ron DeSantis at the USF Tampa signing, April 6, 2026</em></blockquote>

<h2>Who could be next</h2>

<p>Democratic legislators pressed during floor debate on exactly the question of how broad the designation power is. <strong>Rep. Rita Harris</strong> (D-Orlando) called the bill "an extraordinary concentration of power" in the hands of "a few executive officials without guardrails." <strong>Rep. Robin Bartleman</strong> (D-Weston) pushed further: "Does the governor and his Cabinet have the ability to designate NOW [the National Organization for Women] or Planned Parenthood as a domestic terrorist organization? Many would characterize them as a danger to unborn fetuses. So, it is not out of the realm of possibility without tighter guardrails."</p>

<p>CAIR Florida's Executive Director <strong>Hiba Rahim</strong> said in a statement issued within hours of the signing: "We have already been unjustly targeted — most notably when Governor DeSantis falsely labelled CAIR as terrorists without lawful authority or evidence. This is not just about CAIR. This expanded and deeply-flawed framework can attack any organization that dares to dissent."</p>

<h2>The likely litigation</h2>

<p>DeSantis himself predicted the law would be challenged in court. He told reporters he was confident the state would prevail. The legal terrain, however, looks unchanged: the First Amendment objections that persuaded Judge Walker in March — material-support liability without due process, viewpoint-discriminatory targeting, the chilling of association — apply equally to a statute as to an executive order. The procedural cosmetics differ; the constitutional vulnerabilities do not.</p>

<p>For the present, what HB 1471 has accomplished is a chilling effect. Florida's roughly 1.4 million Muslim residents now live, with respect to the most prominent civil-rights organization advocating for them, in a state whose governor has twice declared that organization a terrorist group — once by order a federal court enjoined, and now by statute the same court has yet to rule on. Public-university students who organize a CAIR speaker, sign a CAIR petition, or wear a CAIR T-shirt face potential expulsion, without conviction, before any court has found the underlying designation lawful.</p>

<p>The First Amendment question, as the social-media analyst whose thread first traced this sequence put it, does not get easier the second time through.</p>
BODY;

        $data = [
            'title'        => 'Florida Codifies What Court Blocked: DeSantis Signs Terrorist-Designation Law After Federal Judge Halted CAIR Executive Order',
            'intro'        => "The Florida governor signs HB 1471 at a University of South Florida event one month after a federal judge enjoined his December executive order targeting CAIR. The new statute gives the governor and three cabinet members unilateral authority to designate \"domestic terrorist organizations\" — and allows state universities to expel students for \"promoting\" them.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'DeSantis signs bill allowing state officials to issue domestic "terrorist" designations (Florida Phoenix)', 'url' => 'https://floridaphoenix.com/2026/04/06/desantis-signs-bill-allowing-state-officials-to-issue-domestic-terrorist-designations/'],
                ['title' => 'DeSantis signs Florida law allowing designation of "domestic terrorist organizations," raising civil rights concerns (CBS Miami)', 'url' => 'https://www.cbsnews.com/miami/news/ron-desantis-signs-florida-law-designate-terrorist-organizations-civil-rights-concerns/'],
                ['title' => 'Florida allows state designation of domestic terrorist organizations (JURIST)', 'url' => 'https://www.jurist.org/news/2026/04/florida-allows-state-designation-of-domestic-terrorist-organizations/'],
                ['title' => 'Federal judge blocks DeSantis executive order declaring CAIR a "terrorist organization" (Florida Phoenix)', 'url' => 'https://floridaphoenix.com/2026/03/04/federal-judge-blocks-desantis-executive-order-declaring-cair-a-terrorist-organization/'],
                ['title' => 'Governor DeSantis Signs Law Allowing Designation of "Domestic Terrorist Organizations" in Florida (Charity & Security Network)', 'url' => 'https://charityandsecurity.org/news/governor-desantis-signs-law-allowing-designation-of-domestic-terrorist-organizations-in-florida/'],
                ['title' => '@micyoung75 X thread tracing the sequencing', 'url' => 'https://x.com/micyoung75/status/2041339175325335996'],
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
