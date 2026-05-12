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
 * Wikipedia REST API: https://en.wikipedia.org/api/rest_v1/page/summary/{title}
 * Wikidata entity API: https://www.wikidata.org/wiki/Special:EntityData/{qid}.json
 *
 * Only fills fields that are currently empty — never overwrites existing
 * data. Idempotent.
 */
final class EnrichPrisonersFromWikipedia extends Command {
    protected $signature = 'archive:enrich-prisoners-from-wikipedia {--hours=24 : look back this many hours} {--limit=500} {--name= : only this prisoner} {--dry-run}';
    protected $description = 'Backfill photo / birthdate / death_date from Wikipedia for recently-added prisoners';

    public function handle(): int {
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

            $summary = $this->fetchSummary($p->name);
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
     * @return array<string,mixed>|null
     */
    private function fetchSummary(string $name): ?array {
        // Try a few title variants
        $candidates = [
            $name,
            preg_replace('/\\s*\\(.*\\)\\s*$/', '', $name),  // strip parenthetical
        ];
        $candidates = array_unique(array_filter($candidates));

        foreach ($candidates as $title) {
            $encoded = rawurlencode(str_replace(' ', '_', $title));
            try {
                $resp = Http::timeout(20)
                    ->withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (nppc.example)'])
                    ->get("https://en.wikipedia.org/api/rest_v1/page/summary/{$encoded}");
            } catch (\Throwable $e) {
                continue;
            }
            if (! $resp->successful()) {
                continue;
            }
            $data = $resp->json();
            if (($data['type'] ?? '') === 'disambiguation') {
                continue;
            }
            // Skip if the summary clearly doesn't match a person (no birth in Wikidata makes this hard;
            // we trust the title lookup at this stage).
            return $data;
        }

        return null;
    }

    /**
     * @return array{birth:?string,death:?string}
     */
    private function fetchWikidataDates(string $qid): array {
        try {
            $resp = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (nppc.example)'])
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
        // Wikidata format: +1890-08-07T00:00:00Z (precision 11 = day; 10 = month; 9 = year)
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
                ->withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (nppc.example)'])
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
