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
 * Publish an article on the April 2026 Cobb County indictment of
 * Katie Kloth, Tyler Norman, and Hannah Kass for the May 2022
 * Stop Cop City protest at Brasfield & Gorrie's Atlanta HQ.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddCopCityArsonArticle extends Command {
    protected $signature = 'articles:add-cop-city-arson-indictment';
    protected $description = 'Publish article on the April 2026 Cop City Brasfield & Gorrie arson indictment';

    private const SLUG = 'nothing-burned-three-indicted-georgia-ag-charges-cop-city-protesters-2026';
    private const IMAGE_URL = 'https://pbs.twimg.com/media/HGn5zi8XMAAObkX.jpg';
    private const TWEET_DATE = '2026-04-23 22:28:31';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        // Download the protest photo from the @defendATLforest tweet and
        // store it on the public disk. Falls back to remote URL if the
        // download fails so the article still renders.
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
            $this->warn('Image fetch error ('.$e->getMessage().') — using remote URL.');
        }

        $body = <<<'BODY'
<p><em>Days before his gubernatorial primary, AG Chris Carr unveils new "arson" charges against three out-of-state Stop Cop City defendants — for an action where, organizers say, no structure was ever set on fire.</em></p>

<p>Nearly four years after roughly eighty people converged on the headquarters of Brasfield &amp; Gorrie in Atlanta to protest the general contractor's role in building "Cop City," Georgia Attorney General Chris Carr has returned to the case. On April 23, 2026, a Cobb County grand jury handed up an indictment charging three out-of-state defendants — <strong>Katie Marie Kloth</strong>, 39, of Schofield, Pennsylvania; <strong>Tyler John Norman</strong>, 42, of Blue Mountain, Wisconsin; and <strong>Hannah Kass</strong>, 33, of Philadelphia — with two counts of criminal damage to property in the second degree and one count of arson of lands stemming from the May 12, 2022 demonstration.</p>

<p>The state alleges that during the protest, the three threw incendiary devices that damaged the Brasfield &amp; Gorrie building while employees were inside, and set fire to surrounding grass. Movement supporters point out a notable absence in that allegation: <strong>the building never burned</strong>. Even the indictment, organizers note, charges "arson of <em>lands</em>" rather than the felony arson of a structure that would apply to a serious building fire — a tacit acknowledgment that what state prosecutors are calling "arson" amounts, on the government's own pleading, to spray paint and roman candles that briefly set grass alight.</p>

<p>For the three defendants, the indictment is the second time Carr's office has dragged them into the criminal-legal system over Stop Cop City organizing. All three were among the <strong>sixty-one activists</strong> swept up in the AG's sprawling 2023 RICO conspiracy case against the movement — a prosecution a Fulton County Superior Court judge dismissed earlier in 2025 after ruling that Carr lacked the statutory authority to bring RICO charges without the governor's permission. With the RICO theory gone, prosecutors have now circled back to recharge a slice of the same defendants under different statutes, hours ahead of the four-year statute of limitations.</p>

<p>The timing has not gone unnoticed. Carr, who has built his political career around tough-on-protest prosecution, is running in the May 2026 Republican gubernatorial primary. At the press conference announcing the indictment he framed the charges as part of his commitment to "fighting Antifa," precisely the kind of campaign-trail line the failed RICO case had been engineered to produce. Defendant Hannah Kass put it directly: "The Attorney General is still gunning for election as governor in the primary in May. He needed his political win."</p>

<p>The defendants' lawyers say the case is politically motivated and point to a pattern: the AG's office has tried, and failed, repeatedly, to secure convictions of Stop Cop City defendants. Of the dozens of activists charged with domestic terrorism and money laundering during the 2023 RICO sweep, none has been convicted. The Cobb County indictment is narrower and easier to defend — three named defendants, three specific counts — but it preserves Carr's ability to keep the Stop Cop City prosecution alive as a campaign asset through summer 2026.</p>

<p>Brasfield &amp; Gorrie was the general contractor for the Atlanta Public Safety Training Center, the project that has come to be known among its opponents as <strong>Cop City</strong>. The May 2022 protest at the company's headquarters was one of the earliest large-scale actions in the multi-year campaign that has since included tree-sits in the Atlanta Forest, the police killing of activist Manuel "Tortuguita" Terán in January 2023, and a state and federal repression campaign that civil-liberties groups have called the most aggressive use of domestic-terror statutes against environmental protesters in U.S. history.</p>

<p>The three defendants face significant prison exposure if convicted — second-degree criminal damage and arson of lands are both felonies in Georgia. Defense teams are coordinating; supporters are calling for court-watch attendance and bail-fund contributions through Stop Cop City legal-support channels.</p>

<p>The next court dates are pending; updates will be posted as they are scheduled.</p>
BODY;

        $data = [
            'title'        => 'Nothing Burned, Three Indicted: Georgia Attorney General Charges Cop City Protesters for 2022 Action',
            'intro'        => "Days before his gubernatorial primary, AG Chris Carr unveils new \"arson\" charges against three out-of-state Stop Cop City defendants — for an action where, organizers say, no structure was ever set on fire.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'published_at' => Carbon::parse(self::TWEET_DATE),
            'citations_json' => [
                ['title' => 'Latest Cop City Indictments Drag Former RICO Defendants into New Legal Battle (UNICORN RIOT)', 'url' => 'https://unicornriot.ninja/2026/latest-cop-city-indictments-drag-former-rico-defendants-into-new-legal-battle/'],
                ['title' => 'Georgia AG Charges Three in Brasfield & Gorrie Arson (Rough Draft Atlanta)', 'url' => 'https://roughdraftatlanta.com/2026/04/27/arson-charges-cop-city/'],
                ['title' => 'Georgia AG indicts three in alleged arson targeting "Cop City" contractor (CBS Atlanta)', 'url' => 'https://www.cbsnews.com/atlanta/news/georgia-ag-indicts-three-in-alleged-arson-targeting-cop-city-contractor-as-legal-crackdown-deepens/'],
                ['title' => 'Cobb grand jury indicts 3 over 2022 Stop Cop City protest (ATL Press Collective)', 'url' => 'https://atlpresscollective.com/2026/04/24/cobb-county-indictment-stop-cop-city-protest-2022/'],
                ['title' => '@defendATLforest on X (origin tweet)', 'url' => 'https://x.com/defendATLforest/status/2047442520393605404'],
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
