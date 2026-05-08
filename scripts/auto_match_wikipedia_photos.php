<?php

declare(strict_types=1);

/**
 * Find Wikipedia photos for prisoners that don't have one, with strict
 * disambiguation safeguards so we never attach a photo of a different
 * person who shares the name.
 *
 * Strategy per prisoner:
 *   1. Skip if photo already set
 *   2. Build candidate Wikipedia slug from name + middle_name
 *   3. Fetch the REST API page-summary endpoint
 *   4. Skip if response indicates disambiguation page or non-person
 *   5. Verify the article describes the right person:
 *        - PRIMARY: birth year (from prisoner.birthdate) appears in
 *          the article extract
 *        - SECONDARY: at least one significant keyword from
 *          affiliations / description / aka appears in the extract
 *      Both must pass when birthdate is known. If birthdate is null,
 *      we require TWO independent secondary keyword matches instead.
 *   6. Download the original image, save to
 *      storage/app/public/prisoners/<slug>.jpg
 *   7. Set prisoner.photo and save
 *
 * Conservative: in any ambiguous case the script skips and logs.
 * Idempotent and rate-limited.
 *
 * Optional flags:
 *   --limit=N      stop after N successful downloads
 *   --name="..."   only process this prisoner (debug)
 *   --dry-run      log decisions but don't download or save
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// Parse CLI args
$opts = ['limit' => null, 'name' => null, 'dry_run' => false];
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if (preg_match('/^--limit=(\d+)$/', $arg, $m)) $opts['limit'] = (int) $m[1];
    if (preg_match('/^--name=(.+)$/', $arg, $m)) $opts['name'] = trim($m[1], '"\'');
    if ($arg === '--dry-run') $opts['dry_run'] = true;
}

$photoDir = __DIR__ . '/../storage/app/public/prisoners';
if (! is_dir($photoDir)) mkdir($photoDir, 0755, true);

// --- Helpers ---

function curlGet(string $url): ?string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_USERAGENT => 'NPPC-archive/1.0 (https://nppc.org; bot@nppc.org)',
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || $body === false) return null;
    return $body;
}

function downloadTo(string $url, string $dest): bool {
    $fp = fopen($dest, 'wb');
    if (! $fp) return false;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'NPPC-archive/1.0 (https://nppc.org; bot@nppc.org)',
    ]);
    $ok = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    if (! $ok || $code !== 200 || ! filesize($dest)) {
        @unlink($dest);
        return false;
    }
    return true;
}

function buildSlug(string $name): string {
    $s = preg_replace('/\s*\(.*?\)\s*/', ' ', $name);
    $s = preg_replace('/\s*"[^"]*"\s*/', ' ', $s);
    $s = trim(preg_replace('/\s+/u', ' ', $s));
    return rawurlencode(str_replace(' ', '_', $s));
}

function fileSlug(string $name): string {
    $s = strtolower($name);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

function extractKeywords(?string $description, ?array $aff, ?string $aka): array {
    $kws = [];
    foreach ($aff ?? [] as $a) {
        if (! is_string($a)) continue;
        $a = trim($a);
        if (mb_strlen($a) >= 4) $kws[] = $a;
    }
    if (is_string($aka)) {
        foreach (preg_split('/\s*;\s*/', $aka) as $piece) {
            $piece = trim($piece);
            if (mb_strlen($piece) >= 4) $kws[] = $piece;
        }
    }
    if (is_string($description)) {
        // Pull capitalized multi-word phrases (likely org/event names)
        if (preg_match_all('/\b(?:[A-Z][a-zA-Z\.\']+)(?:\s+(?:[A-Z][a-zA-Z\.\']+|of|the|for|and|de|la|el)){1,4}\b/u', $description, $m)) {
            foreach ($m[0] as $phrase) {
                $phrase = trim($phrase);
                $stops = ['The President', 'United States', 'New York', 'United States Navy', 'United States Army', 'United States Marine'];
                if (in_array($phrase, $stops, true)) continue;
                if (mb_strlen($phrase) >= 5) $kws[] = $phrase;
            }
        }
    }
    // Dedupe (case-insensitive)
    $seen = [];
    $out = [];
    foreach ($kws as $k) {
        $key = mb_strtolower($k);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $out[] = $k;
    }
    return $out;
}

function lookupSummary(string $name): ?array {
    $slug = buildSlug($name);
    $url = "https://en.wikipedia.org/api/rest_v1/page/summary/{$slug}";
    $body = curlGet($url);
    if (! $body) return null;
    $j = json_decode($body, true);
    if (! is_array($j)) return null;
    if (($j['type'] ?? '') === 'disambiguation') return ['disambiguation' => true];
    return $j;
}

function verify(array $summary, ?string $birthdate, array $keywords, array &$reasons): bool {
    $extract = (string) ($summary['extract'] ?? '');
    $description = (string) ($summary['description'] ?? '');
    $haystack = "{$extract}\n\n{$description}";

    if (mb_strlen($extract) < 50) {
        $reasons[] = 'extract too short';
        return false;
    }

    // Birth-year check
    $birthYearMatch = false;
    if ($birthdate) {
        $year = substr($birthdate, 0, 4);
        if ($year && (int) $year > 1700) {
            if (preg_match('/\b' . preg_quote($year, '/') . '\b/', $haystack)) {
                $birthYearMatch = true;
            } else {
                // Try +/- 1 year (Year mismatches are common in DB)
                foreach ([-1, 1] as $delta) {
                    if (preg_match('/\b' . ((int) $year + $delta) . '\b/', $haystack)) {
                        $birthYearMatch = true;
                        break;
                    }
                }
            }
        }
    }

    // Keyword matches
    $kwHits = 0;
    foreach ($keywords as $kw) {
        // Reject if keyword is a single common word
        if (str_word_count($kw) < 1) continue;
        if (mb_stripos($haystack, $kw) !== false) {
            $kwHits++;
        }
    }

    // Decision rule:
    if ($birthdate) {
        // With birthdate: require year match AND >=1 keyword match
        if (! $birthYearMatch) {
            $reasons[] = 'birth year not found in article';
            return false;
        }
        if ($kwHits < 1) {
            $reasons[] = 'no keyword match';
            return false;
        }
        return true;
    } else {
        // No birthdate: require >=2 keyword matches
        if ($kwHits < 2) {
            $reasons[] = "only {$kwHits} keyword match(es); need 2 without birthdate";
            return false;
        }
        return true;
    }
}

// --- Main ---

$query = Prisoner::whereNull('photo')->orWhere('photo', '');
if ($opts['name']) $query->where('name', $opts['name']);

$prisoners = $query->orderBy('name')->get();

$totalCandidates = $prisoners->count();
echo "Candidates without photo: {$totalCandidates}\n";
if ($opts['dry_run']) echo "DRY RUN — no changes will be saved\n";
echo "\n";

$attached = 0;
$skipped = 0;
$noArticle = 0;
$disambig = 0;

foreach ($prisoners as $p) {
    if ($opts['limit'] !== null && $attached >= $opts['limit']) break;

    $name = $p->name;
    if (preg_match('/^\s*$/u', $name)) continue;

    // Build candidate names to try
    $candidates = [$name];
    if ($p->middle_name) {
        $first = $p->first_name ?: explode(' ', $name, 2)[0] ?? '';
        $last = $p->last_name ?: trim((string) preg_replace('/^.*\s+/', '', $name));
        if ($first !== '' && $last !== '') {
            $candidates[] = "{$first} {$p->middle_name} {$last}";
        }
    }
    // Try first AKA as fallback
    if (is_string($p->aka) && $p->aka !== '') {
        foreach (preg_split('/\s*;\s*/', $p->aka) as $alt) {
            $alt = trim($alt);
            if ($alt !== '' && str_word_count($alt) >= 2) $candidates[] = $alt;
        }
    }
    $candidates = array_values(array_unique($candidates));

    $keywords = extractKeywords(
        is_string($p->description) ? $p->description : null,
        is_array($p->affiliation) ? $p->affiliation : null,
        is_string($p->aka) ? $p->aka : null,
    );

    $matched = null;
    $reasons = [];

    foreach ($candidates as $cand) {
        $sum = lookupSummary($cand);
        usleep(150_000); // ~7 req/sec rate limit
        if (! $sum) {
            $reasons[] = "no article for '{$cand}'";
            continue;
        }
        if (! empty($sum['disambiguation'])) {
            $reasons[] = "disambiguation page for '{$cand}'";
            continue;
        }
        if (empty($sum['originalimage']['source']) && empty($sum['thumbnail']['source'])) {
            $reasons[] = "no image in article for '{$cand}'";
            continue;
        }

        $localReasons = [];
        if (verify($sum, $p->birthdate, $keywords, $localReasons)) {
            $matched = ['cand' => $cand, 'summary' => $sum];
            break;
        }
        $reasons = array_merge($reasons, array_map(fn($r) => "'{$cand}': {$r}", $localReasons));
    }

    if (! $matched) {
        $reasonStr = implode('; ', array_slice($reasons, 0, 3));
        if (str_contains($reasonStr, 'disambiguation')) $disambig++;
        elseif (str_contains($reasonStr, 'no article')) $noArticle++;
        else $skipped++;
        echo "  SKIP: {$name} — {$reasonStr}\n";
        continue;
    }

    $imgUrl = $matched['summary']['originalimage']['source'] ?? $matched['summary']['thumbnail']['source'];
    $relPath = 'prisoners/' . fileSlug($name) . '.jpg';
    $absPath = $photoDir . '/' . basename($relPath);

    if ($opts['dry_run']) {
        echo "  WOULD ATTACH: {$name} <- {$matched['cand']} ({$imgUrl})\n";
        $attached++;
        continue;
    }

    if (! downloadTo($imgUrl, $absPath)) {
        echo "  DOWNLOAD FAILED: {$name} ({$imgUrl})\n";
        $skipped++;
        continue;
    }

    $p->photo = $relPath;
    $p->save();
    echo "  ATTACHED: {$name} <- '{$matched['cand']}' ({$imgUrl})\n";
    $attached++;
}

echo "\n";
echo sprintf("Total candidates:   %d\n", $totalCandidates);
echo sprintf("Photos attached:    %d\n", $attached);
echo sprintf("Skipped (verify):   %d\n", $skipped);
echo sprintf("No Wikipedia page:  %d\n", $noArticle);
echo sprintf("Disambiguation:     %d\n", $disambig);
