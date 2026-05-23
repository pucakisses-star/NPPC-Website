<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Republishes the curated set of Civil Liberties Defense Center
 * (cldc.org) case-update press releases as Article rows under the
 * News category, with attribution header and a link back to the
 * source for every post.
 *
 * Uses CLDC's WordPress REST API (/wp-json/wp/v2/posts?slug=...) so
 * we get clean structured title + content + date + featured-media
 * URL without scraping.
 *
 * Idempotent — matches by slug; re-runs update the article body and
 * keep the local image cache.
 */
final class RepostCldcArticles extends Command {
    protected $signature = 'articles:repost-cldc';
    protected $description = 'Republish CLDC press-release articles (case updates, victories, PP solidarity)';

    /**
     * CLDC source slugs (everything after `https://cldc.org/`).
     * Order = chronological by publication date.
     */
    private const SLUGS = [
        // 2012
        'a-federal-trial-win-in-the-schlossberg-case',
        'free-bradley-manning',
        'nw-regional-march-and-rally-for-clemency-for-leonard-peltier',
        'celebrate-daniel-mcgowans-release-from-prison',

        // 2013
        'court-dismisses-animal-enterprise-terrorist-act-lawsuit-heres-how-that-affects-you',
        'daniel-mcgowan-reimprisoned-please-support-him',
        'daniel-mcgowan-released-after-lawyers-confirm-he-was-jailed-for-huffpost-blog',
        'a-life-and-death-appeal-from-renowned-peoples-attorney-lynne-stewart',

        // 2014
        'activist-defense-2',
        'ferguson-the-evils-of-the-grand-jury-system',

        // 2015
        'cldc-defends-right-to-protest-at-federal-plaza-24-hours-a-day',
        'federal-appeals-court-confirms-right-to-protest',
        'city-dismisses-charges-against-sleeps-activist',

        // 2016
        'court-vacates-civil-contempt-order-against-greenpeace',
        'shellno-cases-update-violations-dismissed-fine-reduced',
        'cldc-supporting-standing-rock',
        'in-standing-rock-the-cops-are-out-of-control',
        'update-militarized-police-presence-at-standing-rock',

        // 2017
        'pipeline-protester-trial-ends-with-hung-jury-in-victory-for-climate-movement',
        'update-from-climate-trial',
        'valve-turner-appeals-court-refusal-to-allow-the-climate-necessity-defense',
        'water-protector-undeterred-one-year-after-standing-rock-police-brutality',

        // 2018
        'leonard-higgins-walks-free',
        'victory-for-valve-turner',
        'green-scare-defendant-apprehended-in-cuba-after-12-years',
        'minnesota-supreme-court-grants-use-of-the-necessity-defense-we-are-trial-bound',
        'victory-for-valve-turners-in-minnesota',
        'sophia-wilansky-files-lawsuit-against-morton-county-north-dakota',

        // 2019
        'valve-turner-ken-ward-wins-climate-necessity-defense-in-washington-supreme-court-case-establishes-washington-state-legal-precedent',

        // 2020
        'necessity-defense-victory-Portland-XR',
        'Standing+Rock+Indigenous+Water+Protector+Poemoceah+Suit',
        'cldc-letter-to-city-of-eugene-and-state-of-oregon-officials-regarding-the-city-of-eugenes-response-to-recent-protests',
        'CLDC+Files+Suit+to+Protect+the+Free+Press+from+Police+Violence',
        'standing-rock-civil-rights-lawsuits-moving-forward-after-north-dakota-federal-court-rejects-defendants-dismissal-request',
        'eugene-police-department-settles-excessive-force-lawsuit-brought-by-journalist-at-eugene-weekly',

        // 2021
        'cldc-files-civil-rights-suit-on-behalf-of-black-unity-members-brutalized-by-springfield-police',
        'facebooking-while-brown-indigenous-man-in-arizona-imprisoned-for-social-media-shock-tal',
        'houseless-activist-continues-his-fight-for-free-speech',
        'climate-activists-celebrate-dismissal-of-charges-in-the-midst-of-the-hottest-summer-on-record',
        'springfield-police-engaged-in-politically-motivated-surveillance-and-spying-claims-amended-lawsuit',

        // 2022
        'dibee-plea',
        'jourdan',
        'firelight-victory',
        'springfield-two-years-later',

        // 2023
        'black-lives-matter-activists-file-motion-for-summary-judgment-in-civil-rights-lawsuit-challenging-the-city-of-eugenes-response-to-may-2020-george-floyd-protests',
        'night-of-rage-pr',
        'andy-ngo-loses-lawsuit-portland-jury-finds-no-fault-for-two-activists-in-civil-trial',

        // 2024
        'north-dakota-court-ruling-dismisses-the-civil-rights-case-misses-the-mark',
        'press-release-jury-misses-mark-and-finds-independent-journalist-alissa-azar-guilty-of-riot-disorderly-conduct',
        'press-release-standing-rock-water-protector-wins-north-dakota-federal-appeal',
        'support-for-malik-muhammad',

        // 2025
        'janes-revenge-defendants-share-their-experiences-inside-federal-prison',
        'a15-press-release-younes-anton',
        'a15-trial-defendants-acquitted',
        'maliks-birthday',
        'uscpr-press-release-slapp',
        'animal-rights-activists-found-not-guilty-in-multnomah-county',
        'win-for-medic-assaulted-by-officer-during-reproductive-rights-rally',
        'mahmoud-khalils-abduction-by-ice',
        'courtroom-victories-prove-we-must-keep-fighting',
        'for-immediate-release-civil-rights-groups-sue-city-of-medford-for-targeting-public-health-workers-and-retaliating-against-harm-reduction-activists',
        'protecting-free-speech-cldc-challenges-ice',
        'press-release-cldc-secures-win-in-activist-subpoena-fight-against-ice',
        'press-release-activists-sue-trump-regime-noem-and-dhs-for-violating-first-amendment-rights',
        'press-release-federal-court-grants-motion-for-temporary-restraining-order-against-trump',

        // 2026
        'cop-city-update-honor-the-day-of-the-forest-defender',
        'press-release-court-enjoins-federal-agents-from-arresting-ice-protesters',
        'press-release-federal-court-rules-in-favor-of-anti-racist-activists-holds-police-to-account',
        'cldc-wins-temporary-restraining-order-preliminary-injunction-against-authoritarian-crackdown-on-protest',
        'press-release-reddit-user-pushes-back-against-illegal-dhs-surveillance',
        'shining-a-light-on-police-brutality',
        'press-release-dhs-withdraws-summons-in-resounding-win-for-first-amendment-rights',
        'press-release-civil-liberties-defense-center-files-motion-to-quash-reddit-grand-jury-subpoena-in-washington-d-c',
    ];

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author = Author::firstOrCreate(['name' => 'Civil Liberties Defense Center (republished)']);

        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach (self::SLUGS as $localSlug) {
            $sourceUrl = 'https://cldc.org/'.$localSlug.'/';

            $post = $this->fetchBySlug($localSlug) ?? $this->fetchByPermalink($sourceUrl);
            if ($post === null) {
                $this->warn("NOT FOUND: {$localSlug}");
                $skipped++;
                continue;
            }

            $title = html_entity_decode(strip_tags($post['title']['rendered']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $body = $post['content']['rendered'];
            $publishedAt = Carbon::parse($post['date']);
            $localSlug = $post['slug'] ?: $localSlug;

            $imagePath = null;
            $embedded = $post['_embedded']['wp:featuredmedia'][0] ?? null;
            if ($embedded && ! empty($embedded['source_url'])) {
                $imagePath = $this->downloadImage($localSlug, $embedded['source_url']);
            }

            $header = '<p><em>The following press release was originally published by the <a href="'.e($sourceUrl).'">Civil Liberties Defense Center</a> on '.$publishedAt->format('F j, Y').'. Republished here with attribution.</em></p>';

            $payload = [
                'title' => $title,
                'body' => $header.$body,
                'image' => $imagePath ?: $embedded['source_url'] ?? '/images/site/default-article.jpg',
                'category_id' => $category->id,
                'author_id' => $author->id,
                'published_at' => $publishedAt,
            ];

            $existing = Article::query()->where('slug', $localSlug)->first();
            if ($existing) {
                $existing->update($payload);
                $this->info("UPDATE: {$localSlug}");
                $updated++;
            } else {
                Article::create(['slug' => $localSlug] + $payload);
                $this->info("ADD:    {$localSlug}");
                $added++;
            }
        }

        $this->info("Done — added {$added}, updated {$updated}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** Fetch a CLDC post by WP slug via /wp-json/wp/v2/posts?slug=... */
    private function fetchBySlug(string $slug): ?array {
        try {
            $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                ->timeout(30)
                ->get('https://cldc.org/wp-json/wp/v2/posts', ['slug' => $slug, '_embed' => 1]);
        } catch (\Throwable $e) {
            return null;
        }
        $data = $resp->successful() ? $resp->json() : null;
        return is_array($data) && ! empty($data) ? $data[0] : null;
    }

    /**
     * Fallback: when slug doesn't resolve, scrape the permalink HTML
     * for a `wp-json/wp/v2/posts/<id>` reference and fetch by ID.
     * Handles CLDC posts whose URL slug diverges from the WP slug.
     */
    private function fetchByPermalink(string $url): ?array {
        try {
            $html = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])
                ->timeout(30)
                ->get($url);
        } catch (\Throwable $e) {
            return null;
        }
        if (! $html->successful()) {
            return null;
        }
        if (! preg_match('#wp-json/wp/v2/posts/(\d+)#', $html->body(), $m)) {
            return null;
        }
        try {
            $byId = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])
                ->timeout(30)
                ->get('https://cldc.org/wp-json/wp/v2/posts/'.$m[1], ['_embed' => 1]);
        } catch (\Throwable $e) {
            return null;
        }
        $data = $byId->successful() ? $byId->json() : null;
        return is_array($data) && isset($data['id']) ? $data : null;
    }

    private function downloadImage(string $localSlug, string $url): ?string {
        $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
        $ext = strtolower(preg_replace('/[^a-z0-9]/i', '', $ext)) ?: 'jpg';
        $path = 'articles/'.$localSlug.'.'.$ext;

        if (Storage::disk('public')->exists($path)) {
            return $path;
        }

        try {
            $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])
                ->timeout(60)
                ->get($url);
            if ($resp->successful() && strlen($resp->body()) > 5000) {
                Storage::disk('public')->put($path, $resp->body());
                return $path;
            }
        } catch (\Throwable $e) {
            // fall through
        }
        return null;
    }
}
