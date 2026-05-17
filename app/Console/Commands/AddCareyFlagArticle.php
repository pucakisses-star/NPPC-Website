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
 * Publish article on the March 14, 2026 DOJ dismissal of the
 * prosecution of Army veteran Jan "Jay" Carey, who burned a U.S.
 * flag in Lafayette Park hours after President Trump signed an
 * executive order purporting to criminalize flag burning — a
 * direct test of the 1989 Texas v. Johnson First Amendment ruling.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddCareyFlagArticle extends Command {
    protected $signature = 'articles:add-carey-flag';
    protected $description = 'Publish article on DOJ dismissal of charges against vet Jay Carey for flag burning';

    private const SLUG     = 'jay-carey-flag-burning-charges-dismissed-lafayette-park-2026';
    private const IMAGE_URL = 'https://pbs.twimg.com/media/HDdbheYXkAAALqt.jpg';
    private const PUB_DATE = '2026-03-15 14:51:36';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'Repression'], ['slug' => 'repression']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $imagePath = 'articles/'.self::SLUG.'.jpg';
        try {
            if (! Storage::disk('public')->exists($imagePath)) {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_URL);
                if ($resp->successful() && strlen($resp->body()) > 5000) {
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
<p><em>An Army veteran burned a U.S. flag in Lafayette Park in front of the White House hours after the president signed an executive order purporting to criminalize the act. The Justice Department charged him with two unrelated misdemeanors. A federal judge raised pointed questions about whether the prosecution itself was a First Amendment violation. The DOJ moved to dismiss.</em></p>

<p>On <strong>Friday, March 13, 2026</strong>, the U.S. Department of Justice moved to dismiss its prosecution of <strong>Jan "Jay" Carey, 55</strong>, of Henderson, North Carolina — an Army veteran (1989–2012) who served deployments in Bosnia, Iraq, and Afghanistan. The dismissal closed an eight-month case that civil-liberties advocates had been describing from the start as a politically-directed retaliatory prosecution dressed up in misdemeanor robes.</p>

<h2>The protest</h2>

<p>On <strong>August 25, 2025</strong>, hours after President Donald Trump signed an executive order purporting to criminalize flag burning, Carey walked into <strong>Lafayette Square</strong> directly across Pennsylvania Avenue from the White House and burned an American flag in protest. The action was a deliberate test of the 1989 Supreme Court ruling in <em>Texas v. Johnson</em> — affirmed in <em>United States v. Eichman</em> the following year — which placed flag desecration squarely inside the First Amendment's protection.</p>

<p>Carey explained the choice in plain terms in his statement after the dismissal: <em>"I set out to demonstrate that the First Amendment is sacred and that no administration has the right to supersede our constitutional rights."</em></p>

<h2>The charges</h2>

<p>Federal prosecutors did not — could not — charge Carey with flag burning. <em>Texas v. Johnson</em> still controls. Instead, they reached for two unrelated misdemeanors related to <strong>setting fire on federal property without authorization</strong>, including igniting a fire in an undesignated area. If convicted on both, Carey faced up to <strong>six months</strong> in federal prison.</p>

<p>His legal team, led by <strong>Mara Verheyden-Hilliard</strong> of the <strong>Partnership for Civil Justice Fund</strong> on a pro bono basis, argued from arraignment that the misdemeanor charges were a transparent vehicle for the prosecution Trump had announced in his executive order — that they were brought, in her words, "at the whims and the directives of a president who has said that he disfavors a particular viewpoint," and as part of the administration's "effort to stifle and punish freedom of expression."</p>

<h2>How it collapsed</h2>

<p>In <strong>January 2026</strong>, a federal judge issued a ruling that would have triggered further inquiry — including discovery — into whether Carey's prosecution had been driven by the executive order rather than by ordinary federal-property-protection enforcement priorities. That kind of inquiry would have required prosecutors to expose internal charging-decision communications and, potentially, to put senior DOJ officials on the record about whether the EO drove the case. Two months later, the government chose to drop the case rather than open that door.</p>

<p>The dismissal is the second high-profile First Amendment prosecution to collapse on the Justice Department in less than three months — coming shortly after the DOJ's defeats in the law-firm executive order cases (Perkins Coie, WilmerHale, Jenner & Block, Susman Godfrey), each of which a federal judge struck down on viewpoint-discrimination grounds.</p>

<h2>What's left of the EO</h2>

<p>The flag-burning executive order itself has not been judicially struck down — Carey's case never reached that question because of the misdemeanor framing. But the order's enforcement authority is now in clear retreat: the leading test case has been dropped, the underlying Supreme Court precedent is still good law, and any future prosecutor weighing whether to charge a flag-burner now has the Carey dismissal as a precedent of the kind that lives in DOJ memos and pretrial motions for years.</p>

<p>Carey is back in North Carolina. The flag he burned is ash. The First Amendment, on this issue, is still where the Supreme Court left it in 1989.</p>
BODY;

        $data = [
            'title'        => 'DOJ Dismisses Case Against Army Vet Who Burned Flag Outside White House Hours After Trump\'s Executive Order',
            'intro'        => "An Army veteran burned a U.S. flag in Lafayette Park in front of the White House hours after the president signed an executive order purporting to criminalize the act. The Justice Department charged him with two unrelated misdemeanors. A federal judge raised pointed questions about whether the prosecution itself was a First Amendment violation. The DOJ moved to dismiss.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => '@BTnewsroom on X (origin tweet)', 'url' => 'https://x.com/BTnewsroom/status/2033194408456151348'],
                ['title' => 'Military.com — Feds Move to Dismiss Charges Against Army Veteran Who Burned American Flag Near White House', 'url' => 'https://www.military.com/daily-news/2026/03/14/feds-move-dismiss-charges-against-army-veteran-who-burned-american-flag-near-white-house.html'],
                ['title' => 'NBC News — DOJ drops case against veteran arrested after burning American flag near White House', 'url' => 'https://www.nbcnews.com/politics/justice-department/drops-case-veteran-carey-arrested-burning-american-flag-white-house-rcna263438'],
                ['title' => 'Democracy Now! — DOJ Dismisses Charges Against Army Veteran Who Burned U.S. Flag Across from White House', 'url' => 'https://www.democracynow.org/2026/3/18/headlines/doj_dismisses_charges_against_army_veteran_who_burned_us_flag_across_from_white_house_last_year'],
                ['title' => 'Spectrum Local News (NC) — Feds move to dismiss charges against Army veteran who burned flag', 'url' => 'https://spectrumlocalnews.com/nc/charlotte/news/2026/03/14/jay-carey-flag-burning-charges'],
                ['title' => 'WLOS — DOJ moves to dismiss charges against WNC veteran arrested for burning flag by White House', 'url' => 'https://wlos.com/news/local/western-north-carolina-wnc-veteran-arrested-charges-dismiss-department-justice-united-states-flag-burning-white-house-lafayette-park-president-donald-trump-executive-order-supreme-court-constitution-chuck-edwards-viral-town-hall-escorted-out-headlines'],
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
