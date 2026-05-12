<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * For each prisoner created recently (default: last 24 hours), look up
 * the matching Wikipedia article and backfill missing fields:
 *   - photo (downloads the lead Wikipedia image to storage/app/public/prisoners/)
 *   - birthdate, death_date (from Wikidata P569/P570)
 *
 * Matching strategy:
 *   1. Direct Wikipedia title lookup, with a few title variants.
 *   2. Fallback to MediaWiki opensearch over the name + "activist".
 *   3. Every candidate is validated against the prisoner's own context
 *      (state, ideologies, affiliation, era keywords) — if the article
 *      description / extract shares no signal with the prisoner, we
 *      reject it. This avoids matching, e.g., "Frank Miller" the comics
 *      writer to a Frank Miller political defendant.
 *   4. Validates that the Wikidata entity is a human (P31 = Q5).
 *
 * Only fills fields that are currently empty. Idempotent.
 */
final class EnrichPrisonersFromWikipedia extends Command {
    protected $signature = 'archive:enrich-prisoners-from-wikipedia {--hours=24 : look back this many hours} {--limit=500} {--name= : only this prisoner} {--dry-run} {--verbose-misses : log why each non-match was rejected}';
    protected $description = 'Backfill photo / birthdate / death_date from Wikipedia for recently-added prisoners';

    private string $userAgent = 'NPPC-Archive/1.0 (https://github.com/pucakisses-star/NPPC-Website; archive@nppc)';

    /**
     * Generic political/movement keywords. We require the Wikipedia
     * description or extract to share at least one of these (or one
     * pulled from the prisoner's own metadata) with our record.
     *
     * @var array<int,string>
     */
    private array $genericKeywords = [
        'activist', 'organizer', 'organiser', 'revolutionary', 'anarchist',
        'communist', 'socialist', 'leftist', 'marxist', 'trotskyist',
        'panther', 'civil rights', 'political prisoner', 'pacifist',
        'antiwar', 'anti-war', 'antifa', 'anti-fascist',
        'union', 'labor leader', 'labour leader', 'iww', 'wobbly',
        'sedition', 'espionage act', 'smith act', 'indicted',
        'convicted', 'sentenced', 'imprisoned', 'paroled', 'pardoned',
        'draft resist', 'feminist',
        'puerto rican', 'independentista', 'nationalist party',
        'native american', 'indigenous', 'aim ', 'wounded knee',
        'sds ', 'weather under', 'weatherman', 'weatherwoman',
        'black panther', 'symbionese', 'soledad', 'attica',
        'whistleblow', 'pentagon papers', 'haymarket',
        'magonista', 'pan-african', 'garveyite', 'sccoldb',
    ];

    public function handle(): int {
        @set_time_limit(0);
        ini_set('memory_limit', '512M');

        $hours = (int) $this->option('hours');
        $limit = (int) $this->option('limit');
        $name = $this->option('name');
        $dry = (bool) $this->option('dry-run');

        $query = Prisoner::query();
        if ($name) {
            $query->where('name', $name);
        } else {
            $query->where('created_at', '>=', now()->subHours($hours));
        }
        $prisoners = $query->limit($limit)->get();
        $this->info("Processing {$prisoners->count()} prisoners…");

        Storage::disk('public')->makeDirectory('prisoners');

        $photoCount = 0;
        $birthCount = 0;
        $deathCount = 0;
        $miss = 0;

        foreach ($prisoners as $p) {
            $needsPhoto = empty($p->photo);
            $needsBirth = empty($p->birthdate);
            $needsDeath = empty($p->death_date);
            if (! $needsPhoto && ! $needsBirth && ! $needsDeath) {
                continue;
            }

            $summary = $this->resolveSummary($p);
            if (! $summary) {
                $miss++;

                continue;
            }

            // Photo
            if ($needsPhoto) {
                $imgUrl = $summary['originalimage']['source'] ?? $summary['thumbnail']['source'] ?? null;
                if ($imgUrl) {
                    $path = $this->downloadPhoto($p, $imgUrl, $dry);
                    if ($path) {
                        if (! $dry) {
                            $p->photo = $path;
                        }
                        $photoCount++;
                        $this->info("  photo: {$p->name} ← ".basename($imgUrl));
                    }
                }
            }

            // Dates from Wikidata
            $qid = $summary['wikibase_item'] ?? null;
            if ($qid && ($needsBirth || $needsDeath)) {
                $dates = $this->fetchWikidataDates($qid);
                if ($dates['birth'] && $needsBirth) {
                    if (! $dry) {
                        $p->birthdate = $dates['birth'];
                    }
                    $birthCount++;
                    $this->info("  birth: {$p->name} ← {$dates['birth']}");
                }
                if ($dates['death'] && $needsDeath) {
                    if (! $dry) {
                        $p->death_date = $dates['death'];
                    }
                    $deathCount++;
                    $this->info("  death: {$p->name} ← {$dates['death']}");
                }
            }

            if (! $dry) {
                $p->save();
            }
            usleep(300_000);
        }

        $this->info(sprintf("\nDone. photos=%d births=%d deaths=%d miss=%d %s",
            $photoCount, $birthCount, $deathCount, $miss, $dry ? '(DRY RUN)' : ''));

        return self::SUCCESS;
    }

    /**
     * Returns a verified Wikipedia summary array for the prisoner, or null.
     *
     * @return array<string,mixed>|null
     */
    private function resolveSummary(Prisoner $p): ?array {
        $candidates = $this->titleCandidates($p);

        // Phase 1: direct title lookup.
        foreach ($candidates as $title) {
            $summary = $this->fetchSummaryByTitle($title);
            if ($summary && $this->matchesPrisoner($p, $summary)) {
                return $summary;
            }
        }

        // Phase 2: search fallback (use first 2 candidates with context).
        foreach (array_slice($candidates, 0, 2) as $title) {
            $hits = $this->wikipediaSearch($title);
            foreach ($hits as $hit) {
                $summary = $this->fetchSummaryByTitle($hit);
                if ($summary && $this->matchesPrisoner($p, $summary)) {
                    return $summary;
                }
            }
        }

        if ($this->option('verbose-misses')) {
            $this->warn("  miss: {$p->name}");
        }

        return null;
    }

    /**
     * @return array<int,string>
     */
    private function titleCandidates(Prisoner $p): array {
        $candidates = [$p->name];

        // Strip parentheticals — "Oso Blanco (Byron Shane Chubbuck)" → "Oso Blanco"
        $stripped = trim((string) preg_replace('/\\s*\\(.*\\)\\s*$/', '', $p->name));
        if ($stripped !== '' && $stripped !== $p->name) {
            $candidates[] = $stripped;
            // Try the inner parenthetical as a fallback ("Byron Shane Chubbuck")
            if (preg_match('/\\(([^)]+)\\)/', $p->name, $m)) {
                $candidates[] = trim($m[1]);
            }
        }

        // first + last name (drop middle / aka)
        if ($p->first_name && $p->last_name) {
            $candidates[] = trim($p->first_name.' '.$p->last_name);
        }

        // Replace funky characters that sometimes confuse the API
        $candidates = array_values(array_unique(array_filter(array_map(
            fn ($s) => trim((string) preg_replace('/\\s+/', ' ', $s)),
            $candidates
        ))));

        return $candidates;
    }

    /**
     * @return array<string,mixed>|null
     */
    private function fetchSummaryByTitle(string $title): ?array {
        $encoded = rawurlencode(str_replace(' ', '_', $title));
        try {
            $resp = Http::timeout(20)
                ->withHeaders(['User-Agent' => $this->userAgent])
                ->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$encoded}");
        } catch (\Throwable $e) {
            return null;
        }
        if (! $resp->successful()) {
            return null;
        }
        $data = $resp->json();
        if (! is_array($data)) {
            return null;
        }
        if (($data['type'] ?? '') === 'disambiguation') {
            return null;
        }

        return $data;
    }

    /**
     * @return array<int,string>
     */
    private function wikipediaSearch(string $query): array {
        try {
            $resp = Http::timeout(20)
                ->withHeaders(['User-Agent' => $this->userAgent])
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action' => 'opensearch',
                    'search' => $query,
                    'limit' => 5,
                    'namespace' => 0,
                    'format' => 'json',
                ]);
        } catch (\Throwable $e) {
            return [];
        }
        if (! $resp->successful()) {
            return [];
        }
        $data = $resp->json();
        // opensearch returns [search, titles[], descriptions[], urls[]]
        return is_array($data[1] ?? null) ? $data[1] : [];
    }

    /**
     * Verifies the candidate Wikipedia summary shares enough context with
     * the prisoner record to be considered a match. Avoids false positives
     * like "Frank Miller" (comics writer) for an unrelated political defendant.
     */
    private function matchesPrisoner(Prisoner $p, array $summary): bool {
        $haystack = strtolower(implode(' ', [
            $summary['description'] ?? '',
            $summary['extract'] ?? '',
        ]));
        if ($haystack === '') {
            return false;
        }

        $keywords = $this->genericKeywords;
        // Pull keywords from the prisoner's own profile.
        foreach ((array) $p->ideologies as $k) {
            $keywords[] = strtolower((string) $k);
        }
        foreach ((array) $p->affiliation as $k) {
            $keywords[] = strtolower((string) $k);
        }
        if ($p->state) {
            $keywords[] = strtolower($p->state);
        }
        if ($p->era && preg_match('/^(\\d{4})s$/', (string) $p->era, $m)) {
            $keywords[] = $m[1];
            $keywords[] = (string) ((int) $m[1] + 1);
            $keywords[] = (string) ((int) $m[1] + 2);
            $keywords[] = (string) ((int) $m[1] + 5);
        }

        foreach ($keywords as $kw) {
            $kw = trim($kw);
            if ($kw === '' || strlen($kw) < 3) {
                continue;
            }
            if (str_contains($haystack, $kw)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{birth:?string,death:?string}
     */
    private function fetchWikidataDates(string $qid): array {
        try {
            $resp = Http::timeout(20)
                ->withHeaders(['User-Agent' => $this->userAgent])
                ->get("https://www.wikidata.org/wiki/Special:EntityData/{$qid}.json");
        } catch (\Throwable $e) {
            return ['birth' => null, 'death' => null];
        }
        if (! $resp->successful()) {
            return ['birth' => null, 'death' => null];
        }
        $data = $resp->json();
        $entity = $data['entities'][$qid] ?? null;
        if (! $entity) {
            return ['birth' => null, 'death' => null];
        }

        // Require P31 includes Q5 (human) before accepting dates.
        $isHuman = false;
        foreach ($entity['claims']['P31'] ?? [] as $claim) {
            $val = $claim['mainsnak']['datavalue']['value']['id'] ?? null;
            if ($val === 'Q5') {
                $isHuman = true;
                break;
            }
        }
        if (! $isHuman) {
            return ['birth' => null, 'death' => null];
        }

        return [
            'birth' => $this->extractDate($entity, 'P569'),
            'death' => $this->extractDate($entity, 'P570'),
        ];
    }

    private function extractDate(array $entity, string $prop): ?string {
        $statements = $entity['claims'][$prop][0]['mainsnak']['datavalue']['value']['time'] ?? null;
        if (! $statements) {
            return null;
        }
        if (preg_match('/^[+]?(\\d{4})-(\\d{2})-(\\d{2})/', $statements, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
            $day = (int) $m[3];
            if ($year === 0 || $month === 0 || $day === 0) {
                return null;
            }

            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        return null;
    }

    private function downloadPhoto(Prisoner $p, string $url, bool $dry): ?string {
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $ext = strtolower($ext);
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }
        $slug = $p->slug ?: preg_replace('/[^a-z0-9-]+/', '-', strtolower($p->name));
        $relative = "prisoners/{$slug}.{$ext}";

        if ($dry) {
            return $relative;
        }
        try {
            $resp = Http::timeout(60)
                ->withHeaders(['User-Agent' => $this->userAgent])
                ->get($url);
        } catch (\Throwable $e) {
            return null;
        }
        if (! $resp->successful() || strlen($resp->body()) < 2048) {
            return null;
        }
        Storage::disk('public')->put($relative, $resp->body());

        return $relative;
    }
}
