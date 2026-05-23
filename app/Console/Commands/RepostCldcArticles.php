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

        foreach (self::SLUGS as $slug) {
            $sourceUrl = 'https://cldc.org/'.$slug.'/';
            try {
                $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                    ->timeout(30)
                    ->get('https://cldc.org/wp-json/wp/v2/posts', ['slug' => $slug, '_embed' => 1]);
            } catch (\Throwable $e) {
                $this->warn("FETCH FAILED: {$slug} ({$e->getMessage()})");
                $skipped++;
                continue;
            }

            if (! $resp->successful() || empty($resp->json())) {
                $this->warn("NOT FOUND: {$slug}");
                $skipped++;
                continue;
            }

            $post = $resp->json()[0];
            $title = html_entity_decode(strip_tags($post['title']['rendered']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $body = $post['content']['rendered'];
            $publishedAt = Carbon::parse($post['date']);

            $imagePath = null;
            $embedded = $post['_embedded']['wp:featuredmedia'][0] ?? null;
            if ($embedded && ! empty($embedded['source_url'])) {
                $imagePath = $this->downloadImage($slug, $embedded['source_url']);
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

            $existing = Article::query()->where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $this->info("UPDATE: {$slug}");
                $updated++;
            } else {
                Article::create(['slug' => $slug] + $payload);
                $this->info("ADD:    {$slug}");
                $added++;
            }
        }

        $this->info("Done — added {$added}, updated {$updated}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    private function downloadImage(string $slug, string $url): ?string {
        $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
        $ext = strtolower(preg_replace('/[^a-z0-9]/i', '', $ext)) ?: 'jpg';
        $path = 'articles/'.$slug.'.'.$ext;

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
