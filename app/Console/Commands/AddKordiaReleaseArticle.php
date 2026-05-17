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
 * Publish article on the March 16, 2026 release of Leqaa Kordia
 * from ICE detention in Texas — the last of the 2024 Columbia
 * University pro-Palestine protest defendants still in federal
 * immigration custody, freed after more than a year without ever
 * being charged with a crime, on a $100,000 bond, after a federal
 * judge ordered her release for the third time over DOJ appeals.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddKordiaReleaseArticle extends Command {
    protected $signature = 'articles:add-kordia-release';
    protected $description = 'Publish article on the March 2026 release of Leqaa Kordia from ICE detention';

    private const SLUG      = 'leqaa-kordia-released-ice-detention-last-columbia-protest-detainee-2026';
    private const IMAGE_URL = 'https://pbs.twimg.com/media/HDkH7SHXYAARt-9.jpg';
    private const PUB_DATE  = '2026-03-16 22:26:16';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        // Save the Defending Rights & Dissent image.
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
<p><em>The last person still in federal immigration custody from the 2024 Columbia University pro-Palestine protests walked out of a Texas detention center on a $100,000 bond — after a year of imprisonment without ever being charged with a crime, and after a federal judge ordered her release for the third time over the Justice Department's appeals.</em></p>

<p>On <strong>Monday, March 16, 2026</strong>, <strong>Leqaa Kordia</strong> — a 33-year-old Palestinian woman, born in the West Bank, who has lived in New Jersey since 2016 — was released from a federal Immigration and Customs Enforcement detention center in Texas, more than a year after federal agents arrested her in March 2025. She was one of roughly 100 people taken into custody outside Columbia University during the 2024 spring wave of pro-Palestine campus demonstrations.</p>

<p>Kordia was the <strong>last of those 2024 Columbia protest detainees still in federal immigration custody</strong>. She had never been charged with a crime in the United States.</p>

<h2>The case</h2>

<p>The federal government's stated immigration grounds for her detention shifted over the course of the year — initially asserting visa-status irregularities, later relying on the same Immigration and Nationality Act "foreign policy deportability" provision and Rubio-memo theory used against Mahmoud Khalil, Mohsen Mahdawi, Rümeysa Öztürk, and Yunseo Chung. Her attorneys, civil-rights groups, and a federal judge consistently disputed each rationale.</p>

<p>An immigration judge had ordered her release on bond. The Department of Justice appealed. A federal district judge ordered her release. The DOJ appealed again. As Defending Rights & Dissent reported on March 16, the order under which she finally walked out was the <strong>third</strong> such order — the federal court system, repeatedly presented with the government's evidence, repeatedly found no lawful basis to keep her locked up.</p>

<h2>What it cost her</h2>

<p>Kordia spent <strong>more than 365 days</strong> in ICE detention in Texas, separated from her family in New Jersey, on the strength of allegations the federal courts would not credit. Her bond was set at <strong>$100,000</strong>. She walked out on Monday afternoon and said, per CBS New York reporters at the gate: <em>"I'm not free inside until everyone is free."</em></p>

<h2>What her release means</h2>

<p>NPR framed it bluntly: this is the <strong>end of the federal immigration-detention arc</strong> for the 2024 Columbia campus protests. The administration's targeted-deportation theory, applied across this cluster of cases through the Rubio memo and the Khalil-Mahdawi-Chung-Öztürk-Kordia chain, has now produced <strong>zero successful deportations of Columbia protesters</strong>. Every one of the named lead-plaintiff figures has been released by federal court order, and most are continuing their immigration proceedings outside of custody under preliminary injunctions that constrain ICE's ability to re-detain them.</p>

<p>The underlying legal questions — whether the State Department's "foreign policy" deportability theory survives First Amendment scrutiny, whether ICE may use immigration detention as a content-based censorship instrument against non-citizen political speech, and whether the federal courts will continue to push back on the DOJ's repeated appeals of release orders — remain live. Kordia's case is one of several headed toward higher-court rulings that will set durable precedent for everyone subject to the same federal targeting.</p>

<p>For now, on March 16, 2026, the last of the 2024 Columbia protest defendants is home. The cost was a year of her life.</p>
BODY;

        $data = [
            'title'        => 'Last 2024 Columbia Protest Detainee Released: Leqaa Kordia Walks Out of Texas ICE Detention After 365 Days',
            'intro'        => "The last person still in federal immigration custody from the 2024 Columbia University pro-Palestine protests walked out of a Texas detention center on a \$100,000 bond — after a year of imprisonment without ever being charged with a crime, and after a federal judge ordered her release for the third time over the Justice Department's appeals.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'New York Times — Breaking news: NJ woman released from federal detention in Texas', 'url' => 'https://x.com/nytimes/status/2033671218700734579'],
                ['title' => 'Defending Rights & Dissent — Breaking: Leqaa Kordia is out of detention!', 'url' => 'https://x.com/RightsDissent/status/2033665425058500987'],
                ['title' => 'NBC New York — NJ Palestinian woman freed from detention after Columbia protest', 'url' => 'https://www.nbcnewyork.com/news/local/nj-palestinian-woman-freed-immigration-detention-year-after-columbia-protests/6477223/'],
                ['title' => 'NPR — Last protester in detention after Trump\'s campus crackdown has been released', 'url' => 'https://www.npr.org/2026/03/17/g-s1-114023/last-protester-campus-crackdown-released'],
                ['title' => 'CBS New York — ICE detainee released after 1 year says "I\'m not free inside until everyone is free"', 'url' => 'https://www.cbsnews.com/newyork/news/leqaa-kordia-released-from-ice-custody/'],
                ['title' => 'Just The News — Last pro-Palestinian protester linked to 2024 Columbia protests released', 'url' => 'https://justthenews.com/government/federal-agencies/last-palestinian-protester-released-immigration-detention-after-trumps'],
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
