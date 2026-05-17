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
 * Publish news article on the May 4, 2026 halfway-house release of
 * anarchist / environmental / trans political prisoner Marius
 * Mason after 18 years in federal custody on a Green Scare
 * (Operation Backfire) Earth Liberation Front case. Surfaced by
 * @ADayIn1920 share, 2026-05-14.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddMariusMasonReleaseArticle extends Command {
    protected $signature = 'articles:add-marius-mason-release';
    protected $description = 'Publish article on Marius Mason\'s May 2026 release to a Detroit halfway house';

    private const SLUG      = 'marius-mason-released-halfway-house-detroit-2026';
    private const IMAGE_URL = 'https://pbs.twimg.com/media/HITW76_W4AEmRIj.jpg';
    private const PUB_DATE  = '2026-05-14 19:13:47';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
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
<p><em>After 18 years inside the Federal Bureau of Prisons on one of the longest sentences any Green Scare defendant received, environmental and anarchist political prisoner Marius Mason walked out of federal custody on May 4, 2026 and is on his way to a halfway house in Detroit. Support organizers are calling for continued solidarity and post-release fundraising.</em></p>

<p>On <strong>May 4, 2026</strong>, longtime Earth Liberation Front political prisoner <strong>Marius Mason</strong> was released from federal custody and routed to a halfway house in <strong>Detroit, Michigan</strong> — his home state. His full sentence is scheduled to end in <strong>May 2027</strong>; he will be at the halfway house through then. The release announcement was made by the Anarchist Black Cross Federation and amplified by support groups across the radical-ecology, anarchist, and trans-prisoner-support networks that have organized for his freedom for nearly two decades.</p>

<h2>The case</h2>

<p>Mason — an anarchist, environmentalist, animal-rights activist, artist, poet, musician, and parent — was arrested on <strong>March 10, 2008</strong> as part of the FBI's <strong>"Operation Backfire"</strong> investigation, the multi-state federal sweep targeting alleged Earth Liberation Front and Animal Liberation Front actions that came to be known as the <strong>Green Scare</strong>. The investigation rested heavily on testimony from cooperating witnesses turned by years of FBI pressure, and on the post-September-11 federal practice of charging non-violent property-destruction actions as <strong>"acts of terrorism"</strong> under newly-empowered statutes.</p>

<p>Mason pleaded guilty later that year to two ELF arson actions — most prominently the 1999 burning of a Michigan State University research building (Agriculture Hall) that housed federally-funded genetically-modified-crop research, and the same-period torching of equipment at a commercial logging operation. No one was injured in either action. He was sentenced to <strong>21 years and 10 months</strong> in federal prison — among the longest sentences imposed in any Green Scare case, and reportedly the longest of any defendant whose conduct caused no human injury.</p>

<h2>What he did inside</h2>

<p>Mason served at multiple FBOP facilities, including Federal Medical Center Carswell (Texas) and FCI Danbury (Connecticut). Inside, he became a prominent organizer and writer:</p>

<ul>
  <li><strong>Came out as trans in 2014</strong>, becoming one of the most visible publicly-trans federal prisoners and a key plaintiff and named figure in litigation pushing the Federal Bureau of Prisons to provide hormone therapy and other gender-affirming medical care.</li>
  <li>Earned a <strong>Paralegal Degree</strong> and completed studies in immigration law inside.</li>
  <li>Completed a writing tutorship through the <strong>Yale Prison Education Initiative</strong>.</li>
  <li>Continued to produce <strong>art, poetry, and music</strong> while incarcerated; pieces have been exhibited and sold to support his commissary and his outside campaign.</li>
  <li>Maintained correspondence with other Green Scare and ecological-defense prisoners — Daniel McGowan, Eric McDavid, Rebecca Rubin, the SHAC 7 — and with the broader political-prisoner support universe.</li>
</ul>

<h2>The fundraising ask</h2>

<p>The <strong>Support Marius Mason</strong> campaign (<a href="https://supportmariusmason.org/support/">supportmariusmason.org/support</a>) has launched a post-release fundraising push to help cover the basics of restart: clothing, transportation, ID and documents, medical care, and the immediate financial needs of someone re-entering civilian life with a federal felony record and the cost burdens of trans medical care. Movement organizers, including the originating @ADayIn1920 share that surfaced this news, are urging anyone in a position to donate to do so and anyone in a position to amplify the campaign link to do that.</p>

<h2>What this closes — and what it doesn't</h2>

<p>Marius Mason's release is the <strong>winding-down of the last actively-incarcerated Green Scare political prisoner</strong> in the United States from the original 2005–2008 prosecution wave. Daniel McGowan was released in 2013. Eric McDavid's conviction was vacated in 2015 over withheld evidence. Rebecca Rubin came home in 2018. Joseph Dibee — the longest fugitive — was extradited in 2018 and sentenced in 2022 with credit for time served. Jeffrey "Free" Luers, sentenced before the formal Green Scare prosecutions, was paroled in 2009. With Mason's halfway-house release, the federal arc of the original Green Scare is — by the metric of who is still behind FBOP walls — effectively closed.</p>

<p>What is not closed is the precedent. The <strong>"eco-terrorism" charging framework</strong> the FBI and DOJ built in those cases — federal terrorism enhancements on non-violent property-defense actions, prosecutions assembled on snitch testimony and entrapment, sentences in the decades for arsons that injured no one — is now the template currently being deployed against Stop Cop City, Prairieland, and the broader U.S. federal-court response to ecological and anti-police organizing. The Green Scare's defendants are coming home. The Green Scare's playbook is still being run.</p>

<p>For now, the simple sentence: <strong>after 18 years, Marius Mason is out.</strong></p>
BODY;

        $data = [
            'title'        => 'After 18 Years, Marius Mason Walks Out: Last Green Scare ELF Political Prisoner Released to Detroit Halfway House',
            'intro'        => "After 18 years inside the Federal Bureau of Prisons on one of the longest sentences any Green Scare defendant received, environmental and anarchist political prisoner Marius Mason walked out of federal custody on May 4, 2026 and is on his way to a halfway house in Detroit. Support organizers are calling for continued solidarity and post-release fundraising.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => '@ADayIn1920 on X (origin share, May 14 2026)', 'url' => 'https://x.com/ADayIn1920/status/2055003660594139631'],
                ['title' => 'Anarchist Black Cross Federation — Marius Mason to be released to halfway house 5.4!', 'url' => 'https://www.abcf.net/blog/marius-mason-to-be-released-to-halfway-house-5-4/'],
                ['title' => 'Anarchist Black Cross Federation — Statement from Marius Mason on his May 2026 release', 'url' => 'https://www.abcf.net/blog/statement-from-marius-mason-on-his-may-2026-release/'],
                ['title' => 'Anarchist Federation — Welcome Home, Marius Mason!', 'url' => 'https://www.anarchistfederation.net/welcome-home-marius-mason'],
                ['title' => 'Abolition Media — Marius Mason to be Released to Halfway House, May 4th!', 'url' => 'https://abolitionmedia.noblogs.org/30895/'],
                ['title' => 'Support Marius Mason — donation page', 'url' => 'https://supportmariusmason.org/support/'],
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
