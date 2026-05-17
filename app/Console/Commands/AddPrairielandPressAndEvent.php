<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Publish a press-release article on the March 13, 2026 Prairieland
 * trial verdicts (9 defendants convicted on most counts) AND an
 * event entry for the April 4, 2026 International Day of Solidarity
 * with the Prairieland Defendants. Both pull the IllWill graphic
 * from the @illwilleditions 2026-03-17 tweet.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddPrairielandPressAndEvent extends Command {
    protected $signature = 'articles:add-prairieland-press-and-event';
    protected $description = 'Publish Prairieland verdicts press release + April 4 solidarity event entry';

    private const ARTICLE_SLUG = 'prairieland-defendants-convicted-most-counts-2026';
    private const EVENT_SLUG   = 'prairieland-international-day-of-solidarity-2026-04-04';
    private const IMAGE_URL    = 'https://pbs.twimg.com/media/HDogbuZW4AAtuPu.jpg';
    private const TWEET_DATE   = '2026-03-17 18:28:48';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'Repression'], ['slug' => 'repression']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        // Pull the IllWill solidarity-day graphic once for both records.
        $articleImage = 'articles/'.self::ARTICLE_SLUG.'.jpg';
        $eventImage   = 'events/'.self::EVENT_SLUG.'.jpg';
        $remoteFallback = self::IMAGE_URL;

        try {
            $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                ->timeout(60)
                ->get(self::IMAGE_URL);
            if ($resp->successful() && strlen($resp->body()) > 5000) {
                Storage::disk('public')->put($articleImage, $resp->body());
                Storage::disk('public')->put($eventImage, $resp->body());
                $this->info('Saved image to articles/ and events/ directories.');
            } else {
                $this->warn('Image download failed — using remote URL.');
                $articleImage = $remoteFallback;
                $eventImage   = $remoteFallback;
            }
        } catch (\Throwable $e) {
            $this->warn('Image fetch error: '.$e->getMessage());
            $articleImage = $remoteFallback;
            $eventImage   = $remoteFallback;
        }

        // ─── Article (press release) ───
        $articleBody = <<<'BODY'
<p><em>A jury has convicted nine defendants on most counts in the Prairieland Trial. They face decades in federal prison. Supporters call for an April 4 International Day of Solidarity — letter-writing nights, info sessions, noise demos, and fundraisers.</em></p>

<p>On <strong>March 13, 2026</strong>, after weeks of trial, a jury returned guilty verdicts on most counts against <strong>nine defendants</strong> in the Prairieland federal prosecution arising from the September 2024 demonstration at the construction site of the Prairieland Detention Center — a planned ICE jail in Alvarado, Texas slated to be one of the largest immigration-detention facilities in the United States.</p>

<p>The convicted defendants now face <strong>decades in federal prison</strong>. Federal prosecutors continue to pursue additional state-level charges in Johnson County, Texas, which will be tried separately. The federal verdicts mark the conclusion of the first major U.S. trial directly arising from anti-ICE-construction protest activity in the second Trump term.</p>

<p>The defendant support coalition issued a statement through IllWill Editions on March 17, 2026 calling on movement organizations and individuals across the country to mark <strong>Friday, April 4, 2026</strong> as an <strong>International Day of Solidarity with the Prairieland Defendants</strong>:</p>

<blockquote>
<p>"Show solidarity with these defendants by hosting letter writing nights, info sessions, noise demos, and fundraisers on April 4. We have a long journey ahead of us to continue fighting these charges, along with the state level charges. What happens here sets the tone for what's to come. We are here and we won't give up."</p>
</blockquote>

<p>The Prairieland prosecutions are part of a broader pattern of federal escalation against direct-action protest of immigration-enforcement infrastructure — a pattern that also includes the conspiracy indictment of U.S. Army veteran Bajun Mavalwalla over the June 2025 Spokane DHS action, the J20 inauguration kettle of 230 in 2017, and the Stop Cop City RICO and arson prosecutions in Atlanta. Each case, defense organizers argue, is positioned by prosecutors as a precedent-setting deterrent against the next wave of organized resistance.</p>

<p>Updates on individual defendants, court schedules, mailing addresses for letters of support, and bail/legal-defense fundraising channels are being maintained by the Prairieland support coalition; this archive will be kept current as sentencing dates are scheduled and the state-court phase opens.</p>
BODY;

        $articleData = [
            'title'        => 'Prairieland Defendants Convicted on Most Counts; Supporters Call for April 4 International Day of Solidarity',
            'intro'        => "A jury has convicted nine defendants on most counts in the Prairieland Trial. They face decades in federal prison. Supporters call for an April 4 International Day of Solidarity — letter-writing nights, info sessions, noise demos, and fundraisers.",
            'body'         => $articleBody,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $articleImage,
            'published_at' => Carbon::parse(self::TWEET_DATE),
            'citations_json' => [
                ['title' => '@illwilleditions on X (origin announcement)', 'url' => 'https://x.com/illwilleditions/status/2033973845288157321'],
                ['title' => 'IllWill Editions', 'url' => 'https://illwill.com'],
            ],
        ];

        $articleExisting = Article::where('slug', self::ARTICLE_SLUG)->first();
        if ($articleExisting) {
            $articleExisting->update($articleData);
            $this->info('Updated article: '.$articleData['title']);
        } else {
            Article::create(['slug' => self::ARTICLE_SLUG] + $articleData);
            $this->info('Created article: '.$articleData['title']);
        }

        // ─── Event entry ───
        $eventDescription = "On March 13, 2026 a federal jury convicted nine Prairieland Defendants on most counts; they now face decades in federal prison and additional state-level charges. The Prairieland support coalition is calling on movement organizations and individuals around the world to mark Friday, April 4, 2026 as an International Day of Solidarity — host a letter-writing night, info session, noise demo, or fundraiser. "
            ."\n\n\"What happens here sets the tone for what's to come. We are here and we won't give up.\" — Prairieland defendant support statement, March 17, 2026.";

        $eventData = [
            'title'       => 'International Day of Solidarity with the Prairieland Defendants',
            'description' => $eventDescription,
            'body'        => null,
            'image'       => $eventImage,
            'event_date'  => '2026-04-04',
            'time'        => null,
            'location'    => 'Worldwide (decentralized actions)',
            'event_url'   => 'https://x.com/illwilleditions/status/2033973845288157321',
            'series'      => 'Prairieland Defendant Support',
            'published'   => true,
        ];

        $eventExisting = Event::where('slug', self::EVENT_SLUG)->first();
        if ($eventExisting) {
            $eventExisting->update($eventData);
            $this->info('Updated event: '.$eventData['title']);
        } else {
            Event::create(['slug' => self::EVENT_SLUG] + $eventData);
            $this->info('Created event: '.$eventData['title']);
        }

        return self::SUCCESS;
    }
}
