<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchCreativeCommonsPhotos extends Command
{
    protected $signature = 'prisoners:fetch-cc-photos
                            {--limit= : Cap the number of prisoners processed in this run}
                            {--overwrite : Re-fetch even if photo already exists}
                            {--dry-run : Show what would be downloaded, do not write}
                            {--names= : Only run for specific names, comma-separated}
                            {--sources=openverse,wikipedia,loc,nypl : Comma-separated list of providers to try, in order}';

    protected $description = 'Fetch missing prisoner photos from open Creative Commons / public-domain image APIs (Openverse, Wikimedia Commons via Wikipedia, Library of Congress, NYPL Digital Collections). Saves attribution + license alongside.';

    private const USER_AGENT = 'NPPC-Website/1.0 (+https://nppc.org; political-prisoner-database)';

    public function handle(): int
    {
        $dryRun    = (bool) $this->option('dry-run');
        $overwrite = (bool) $this->option('overwrite');
        $limit     = $this->option('limit') ? (int) $this->option('limit') : null;
        $namesOpt  = $this->option('names');
        $sources   = array_filter(array_map('trim', explode(',', (string) $this->option('sources'))));

        if (empty($sources)) {
            $sources = ['openverse', 'wikipedia', 'loc', 'nypl'];
        }

        $query = Prisoner::query();
        if (! $overwrite) {
            $query->where(function ($q) {
                $q->whereNull('photo')->orWhere('photo', '');
            });
        }
        if ($namesOpt) {
            $names = array_map('trim', explode(',', $namesOpt));
            $query->whereIn('name', $names);
        }
        $prisoners = $query->orderBy('name')->get();

        if ($limit) {
            $prisoners = $prisoners->take($limit);
        }

        if ($prisoners->isEmpty()) {
            $this->info('No prisoners need photos.');

            return self::SUCCESS;
        }

        $this->info($prisoners->count() . ' prisoners to process.');
        $this->info('Source order: ' . implode(' → ', $sources));
        $this->newLine();

        if (! $dryRun) {
            Storage::disk('public')->makeDirectory('prisoners');
        }

        $downloaded = 0;
        $byProvider = array_fill_keys($sources, 0);
        $missing = 0;
        $failed  = 0;

        foreach ($prisoners as $prisoner) {
            $hit = null;
            foreach ($sources as $provider) {
                $hit = match ($provider) {
                    'openverse' => $this->lookupOpenverse($prisoner),
                    'wikipedia' => $this->lookupWikipedia($prisoner),
                    'loc'       => $this->lookupLibraryOfCongress($prisoner),
                    'nypl'      => $this->lookupNYPL($prisoner),
                    default     => null,
                };
                if ($hit) {
                    $hit['provider'] = $provider;
                    break;
                }
            }

            if (! $hit) {
                $missing++;
                $this->line("  [no match]    {$prisoner->name}");
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf(
                    "  [DRY %-9s] %s  →  %s  (%s)",
                    $hit['provider'],
                    $prisoner->name,
                    Str::limit($hit['url'], 70),
                    $hit['license'] ?? 'unknown license'
                ));
                continue;
            }

            try {
                $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(30)
                    ->get($hit['url']);
                if (! $resp->successful()) {
                    $failed++;
                    $this->line("  [http {$resp->status()}]   {$prisoner->name}");
                    continue;
                }

                $ext = $this->extensionFromMime($resp->header('Content-Type')) ?? $this->extensionFromUrl($hit['url']);
                $filename = 'prisoners/' . Str::slug($prisoner->name) . '-' . $prisoner->id . '.' . $ext;
                Storage::disk('public')->put($filename, $resp->body());

                $prisoner->photo = $filename;
                $prisoner->photo_source_url = $hit['source_url'] ?? $hit['url'];
                $prisoner->photo_attribution = $hit['attribution'] ?? null;
                $prisoner->photo_license = $hit['license'] ?? null;
                $prisoner->saveQuietly();

                $downloaded++;
                $byProvider[$hit['provider']]++;
                $this->line(sprintf(
                    "  [%-9s] %s  ←  %s  (%s)",
                    $hit['provider'],
                    $prisoner->name,
                    Str::limit($hit['attribution'] ?? '(unknown)', 50),
                    $hit['license'] ?? 'unknown license'
                ));
            } catch (\Throwable $e) {
                $failed++;
                $this->line("  [error] {$prisoner->name}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Downloaded: {$downloaded}");
        foreach ($byProvider as $p => $n) {
            if ($n > 0) {
                $this->info("  via {$p}: {$n}");
            }
        }
        $this->info("No match:   {$missing}");
        if ($failed > 0) {
            $this->warn("Failed:     {$failed}");
        }

        return self::SUCCESS;
    }

    /* ---------------------------------------------------------------- */
    /* Provider: Openverse (api.openverse.org) — Wikimedia Foundation   */
    /* aggregator covering Flickr CC, Wikimedia Commons, Smithsonian,   */
    /* museums, etc. No auth key required for anonymous use.            */
    /* ---------------------------------------------------------------- */
    private function lookupOpenverse(Prisoner $p): ?array
    {
        foreach (array_filter([$p->name, $p->aka]) as $candidate) {
            try {
                $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(15)
                    ->get('https://api.openverse.org/v1/images/', [
                        'q'           => $candidate,
                        'page_size'   => 5,
                        // license_type=all to include CC0/PDM as well as CC-BY family
                        'license_type' => 'all',
                    ]);
                if (! $resp->successful()) {
                    continue;
                }
                $results = data_get($resp->json(), 'results', []);
                foreach ($results as $r) {
                    $url = $r['url'] ?? null;
                    if (! $url) continue;
                    return [
                        'url'         => $url,
                        'source_url'  => $r['foreign_landing_url'] ?? $url,
                        'attribution' => $this->buildOpenverseAttribution($r),
                        'license'     => $this->formatOpenverseLicense($r),
                    ];
                }
            } catch (\Throwable $e) {
                // try next candidate
            }
        }
        return null;
    }

    private function buildOpenverseAttribution(array $r): string
    {
        $parts = array_filter([
            $r['creator'] ?? null,
            $r['source'] ?? null,
        ]);
        return mb_substr(implode(' — ', $parts) ?: 'Openverse', 0, 250);
    }

    private function formatOpenverseLicense(array $r): ?string
    {
        $license = $r['license'] ?? null;
        $version = $r['license_version'] ?? null;
        if (! $license) return null;
        return strtoupper($license) . ($version ? " {$version}" : '');
    }

    /* ---------------------------------------------------------------- */
    /* Provider: Wikipedia pageimages (en.wikipedia.org)                 */
    /* The page's primary image, served from Commons.                    */
    /* ---------------------------------------------------------------- */
    private function lookupWikipedia(Prisoner $p): ?array
    {
        foreach (array_filter([$p->name, $p->aka]) as $candidate) {
            $title = $this->searchWikipedia($candidate);
            if (! $title) continue;
            $img = $this->fetchPageImage($title);
            if (! $img) continue;
            $attr = $this->fetchCommonsAttribution($img['filename']);
            return [
                'url'         => $img['url'],
                'source_url'  => 'https://commons.wikimedia.org/wiki/File:' . str_replace(' ', '_', $img['filename']),
                'attribution' => $attr['attribution'] ?? 'Wikimedia Commons',
                'license'     => $attr['license'] ?? null,
            ];
        }
        return null;
    }

    private function searchWikipedia(string $query): ?string
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(15)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action'   => 'query',
                    'list'     => 'search',
                    'srsearch' => $query,
                    'srlimit'  => 1,
                    'format'   => 'json',
                ]);
            if (! $resp->successful()) return null;
            $hits = data_get($resp->json(), 'query.search', []);
            return $hits[0]['title'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchPageImage(string $title): ?array
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(15)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'titles' => $title,
                    'prop'   => 'pageimages',
                    'piprop' => 'original|name',
                    'format' => 'json',
                ]);
            if (! $resp->successful()) return null;
            $pages = data_get($resp->json(), 'query.pages', []);
            $page = is_array($pages) ? reset($pages) : null;
            $url = data_get($page, 'original.source');
            $filename = data_get($page, 'pageimage');
            if (! $url || ! $filename) return null;
            if (! str_contains($url, 'upload.wikimedia.org')) return null;
            return ['url' => $url, 'filename' => $filename];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fetchCommonsAttribution(string $filename): array
    {
        try {
            $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                ->timeout(15)
                ->get('https://commons.wikimedia.org/w/api.php', [
                    'action' => 'query',
                    'titles' => 'File:' . $filename,
                    'prop'   => 'imageinfo',
                    'iiprop' => 'extmetadata',
                    'format' => 'json',
                ]);
            if (! $resp->successful()) return [];
            $pages = data_get($resp->json(), 'query.pages', []);
            $page = is_array($pages) ? reset($pages) : null;
            $meta = data_get($page, 'imageinfo.0.extmetadata', []);
            $artist = strip_tags(data_get($meta, 'Artist.value', ''));
            $license = data_get($meta, 'LicenseShortName.value', '');
            $credit = strip_tags(data_get($meta, 'Credit.value', ''));
            $attribution = trim($artist) ?: trim($credit) ?: 'Wikimedia Commons';
            return [
                'attribution' => mb_substr(html_entity_decode($attribution), 0, 250),
                'license'     => $license ?: null,
            ];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* ---------------------------------------------------------------- */
    /* Provider: Library of Congress (loc.gov) — public domain / no    */
    /* known restrictions. Strong for historical figures pre-1970s.     */
    /* ---------------------------------------------------------------- */
    private function lookupLibraryOfCongress(Prisoner $p): ?array
    {
        foreach (array_filter([$p->name, $p->aka]) as $candidate) {
            try {
                $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(15)
                    ->get('https://www.loc.gov/photos/', [
                        'q'  => $candidate,
                        'fo' => 'json',
                        'c'  => 5,
                    ]);
                if (! $resp->successful()) continue;
                $results = data_get($resp->json(), 'results', []);
                foreach ($results as $r) {
                    // LoC results expose image_url as an array of progressive sizes;
                    // pick the largest available.
                    $images = $r['image_url'] ?? [];
                    if (! is_array($images) || empty($images)) continue;
                    $url = end($images);
                    if (! $url) continue;
                    // Some LoC results return CDN URLs without a scheme.
                    if (str_starts_with($url, '//')) {
                        $url = 'https:' . $url;
                    }
                    $rights = $r['rights'] ?? ($r['rights_advisory'] ?? '');
                    if (is_array($rights)) {
                        $rights = implode(' ', $rights);
                    }
                    return [
                        'url'         => $url,
                        'source_url'  => $r['url'] ?? $url,
                        'attribution' => mb_substr('Library of Congress: ' . ($r['title'] ?? ''), 0, 250),
                        'license'     => $this->classifyLocRights((string) $rights),
                    ];
                }
            } catch (\Throwable $e) {
                // try next candidate
            }
        }
        return null;
    }

    private function classifyLocRights(string $rights): string
    {
        $r = strtolower($rights);
        if (str_contains($r, 'no known restrictions')) return 'No Known Restrictions (LoC)';
        if (str_contains($r, 'public domain'))         return 'Public Domain';
        if (str_contains($r, 'cc'))                    return 'Creative Commons (LoC)';
        return 'See LoC rights statement';
    }

    /* ---------------------------------------------------------------- */
    /* Provider: NYPL Digital Collections (api.repo.nypl.org)           */
    /* Strong for historical political and labor movement figures.       */
    /* The NYPL API requires no auth for the public-search endpoint.     */
    /* ---------------------------------------------------------------- */
    private function lookupNYPL(Prisoner $p): ?array
    {
        foreach (array_filter([$p->name, $p->aka]) as $candidate) {
            try {
                $resp = Http::withHeaders(['User-Agent' => self::USER_AGENT])
                    ->timeout(15)
                    ->get('https://api.repo.nypl.org/api/v2/items/search.json', [
                        'q'             => $candidate,
                        'per_page'      => 5,
                        'publicDomainOnly' => 'true',
                    ]);
                if (! $resp->successful()) continue;
                $results = data_get($resp->json(), 'nyplAPI.response.result', []);
                if (! is_array($results)) continue;
                foreach ($results as $r) {
                    $imageId = $r['imageID'] ?? null;
                    if (! $imageId) continue;
                    // NYPL public IIIF derivative: largest available is usually .jpg
                    $url = "https://images.nypl.org/index.php?id={$imageId}&t=w";
                    $title = $r['title'] ?? '';
                    return [
                        'url'         => $url,
                        'source_url'  => $r['itemLink'] ?? $url,
                        'attribution' => mb_substr('New York Public Library Digital Collections: ' . $title, 0, 250),
                        'license'     => 'Public Domain',
                    ];
                }
            } catch (\Throwable $e) {
                // try next candidate
            }
        }
        return null;
    }

    /* ---------------------------------------------------------------- */
    /* Helpers                                                           */
    /* ---------------------------------------------------------------- */
    private function extensionFromMime(?string $mime): ?string
    {
        if (! $mime) return null;
        return match (true) {
            str_contains($mime, 'png')  => 'png',
            str_contains($mime, 'gif')  => 'gif',
            str_contains($mime, 'webp') => 'webp',
            str_contains($mime, 'svg')  => 'svg',
            str_contains($mime, 'jpeg') => 'jpg',
            str_contains($mime, 'jpg')  => 'jpg',
            default                     => null,
        };
    }

    private function extensionFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['png', 'gif', 'webp', 'svg', 'jpg', 'jpeg'], true)
            ? ($ext === 'jpeg' ? 'jpg' : $ext)
            : 'jpg';
    }
}
