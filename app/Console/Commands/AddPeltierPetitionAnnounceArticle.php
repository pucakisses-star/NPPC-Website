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
 * Publishes the announcement article for the Free Leonard Peltier
 * petition, dated to the September 12, 2024 national rally at the
 * White House on Peltier's 80th birthday — the same date the
 * petition itself is backdated to.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddPeltierPetitionAnnounceArticle extends Command {
    protected $signature = 'articles:add-peltier-petition-announce';
    protected $description = 'Publish announcement article for the Free Leonard Peltier petition (dated Sept 12 2024)';

    private const SLUG     = 'free-leonard-peltier-petition-launch-80th-birthday-dc-rally-2024';
    private const IMAGE_URL = 'https://www.amnestyusa.org/wp-content/uploads/2023/03/20230912-Leonard-Peltier-DC-79-Birthday-Action_by-Willi-White-WIDE-144-scaled.jpeg';
    private const PUB_DATE = '2024-09-12 13:00:00';

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
<p><em>On Leonard Peltier's 80th birthday, Indigenous nations, allies, and human rights organizations converge on the White House for what may be the last realistic clemency push of the Biden presidency. NPPC is launching a petition campaign to channel that energy into sustained pressure for a presidential pardon.</em></p>

<p>Today — <strong>September 12, 2024 — Leonard Peltier turns 80 years old</strong> behind the walls of USP Coleman in Florida, beginning his 49th calendar year inside the U.S. federal prison system for a 1975 case the FBI's own former senior officials, four of the original prosecutors' colleagues, and decades of independent reporting have called a frame.</p>

<p>And today, on the sidewalk in front of the White House, hundreds of <strong>members of Indigenous nations, NDN Collective, Amnesty International USA, the International Leonard Peltier Defense Committee, and the broader prison-abolition and clemency movement</strong> are converging for a national rally — with sister actions in dozens of cities across Turtle Island — to demand that President Biden grant clemency before he leaves office.</p>

<p><strong>To meet that moment, NPPC is launching the <a href="/petition/free-leonard-peltier">Free Leonard Peltier — Pardon Now</a> petition.</strong> Add your name. The petition asks the President of the United States, the Attorney General, and the U.S. Pardon Attorney to grant Peltier a full pardon — not a commutation, not a transfer, not parole, but a pardon that vacates the conviction obtained through the documented government misconduct that Amnesty International, the National Congress of American Indians, the European Parliament, the Dalai Lama, Pope Francis, Coretta Scott King, and three of Peltier's own former prosecutors have already condemned.</p>

<h2>Why this moment</h2>

<p>The Biden administration leaves office in <strong>130 days</strong>. The path to clemency for Leonard Peltier — an Anishinaabe and Dakota American Indian Movement organizer who has spent more than two-thirds of his life in federal custody for the 1975 Pine Ridge Oglala firefight that left FBI agents Jack Coler and Ronald Williams and AIM member Joseph Stuntz dead — has been formally open since his attorneys submitted the most recent clemency petition in 2022. The 2024 <em>Walk to Justice</em> covered 1,000 miles from Minneapolis to Washington under the leadership of the NDN Collective. The 2023 birthday DC mobilization brought the campaign to the White House gates. Today's 80th-birthday action is the largest yet.</p>

<p>Leonard's health has deteriorated. His abdominal aortic aneurysm and diabetes are documented in his medical filings. His parole has been denied repeatedly — most recently in <strong>July 2024</strong> — on procedural grounds rather than on the merits of the underlying conviction. There is no remaining administrative path. There is only executive clemency.</p>

<h2>What the petition asks</h2>

<p>The petition is addressed to the President of the United States, the Attorney General, and the Pardon Attorney. It asks for three things:</p>

<ul>
    <li><strong>A full presidential pardon</strong> vacating Leonard Peltier's federal conviction.</li>
    <li><strong>Immediate release</strong> from federal custody, with no conditions of supervision that would prevent him from accessing the medical care, ceremony, and family contact he has been denied for nearly five decades.</li>
    <li><strong>A formal federal acknowledgment</strong> of the COINTELPRO-era FBI war on the American Indian Movement and the prosecutorial misconduct documented in his case — including the coerced witness affidavits, the fabricated ballistics, and the suppressed exculpatory evidence later released under FOIA.</li>
</ul>

<h2>How to act today</h2>

<ul>
    <li><strong>Sign the petition</strong> at <a href="/petition/free-leonard-peltier">/petition/free-leonard-peltier</a>.</li>
    <li><strong>Call the White House comment line</strong> at <strong>(202) 456-1111</strong> and ask the operator to record your call as supporting clemency for Leonard Peltier.</li>
    <li><strong>Write a letter</strong> — <a href="/prisoner/leonard-peltier">Leonard's address is here</a>. Even a birthday card matters.</li>
    <li><strong>Share the rally</strong> on social media. Tag <strong>#FreeLeonardPeltier</strong>, <strong>#BringLeonardHome</strong>, <strong>#NDNCollective</strong>.</li>
</ul>

<p>The fight for Leonard Peltier's freedom is the longest sustained political-prisoner support campaign in U.S. history. It has outlasted the COINTELPRO program that produced his case. It has outlasted nine presidents. With your name on this petition, it will outlast this one too — and it will not stop until he is home.</p>
BODY;

        $data = [
            'title'        => 'On Leonard Peltier\'s 80th Birthday, NPPC Launches Petition for a Pardon',
            'intro'        => "On Leonard Peltier's 80th birthday, Indigenous nations, allies, and human rights organizations converge on the White House for what may be the last realistic clemency push of the Biden presidency. NPPC is launching a petition campaign to channel that energy into sustained pressure for a presidential pardon.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'image_caption' => 'Indigenous nations and allies rally at the White House on Leonard Peltier\'s 79th birthday, September 12, 2023. Photo: Willi White / Amnesty International USA.',
            'published_at' => Carbon::parse(self::PUB_DATE),
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
