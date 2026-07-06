<?php

/**
 * Mirror the "Conscientious Objection and the Great War" digital collection
 * (Swarthmore College Peace Collection) as a self-hosted, browsable WEB-DISPLAY
 * copy — fullsize + thumbnail JPEGs only, not the archival TIFF/JPEG originals.
 *
 * Source:  https://cosandgreatwar.swarthmore.edu/  (Omeka + IIIF image server)
 * Images are pulled from the site's IIIF endpoint at display sizes, so the
 * ~3,000 page-scans come to roughly ~450 MB at the default sizes.
 *
 * This is Swarthmore's digitized material. Re-hosting the originals is a rights
 * question worth confirming with the Peace Collection; this copy is deliberately
 * limited to display derivatives and runs politely (rate-limited, one request at
 * a time, resumable).
 *
 * USAGE (run on the server — it has direct internet, no proxy needed):
 *   php scripts/mirror-swarthmore-cos.php [options]
 *
 * Options:
 *   --dir=PATH        output directory (default: storage/app/swarthmore-cos)
 *   --full=PX         fullsize longest edge in px      (default: 1000; the
 *                     site's IIIF server caps around 1000px, so larger values
 *                     will fail for many images)
 *   --thumb=PX        thumbnail longest edge in px     (default: 300)
 *   --delay=MS        pause between HTTP requests in ms (default: 350)
 *   --limit=N         only process the first N items   (0 = all; for testing)
 *   --start=N         skip to the Nth item (1-based; for resuming a range)
 *   --items=ID,ID     only these item IDs (comma-separated; for testing)
 *   --dry-run         discover items + files and report totals; download nothing
 *
 * Resumable: existing image files are skipped, so re-running continues where it
 * left off. Safe to Ctrl-C and restart.
 */

error_reporting(E_ALL & ~E_DEPRECATED);

const BASE = 'https://cosandgreatwar.swarthmore.edu';

$opts = parseArgs($argv);
$dir = rtrim($opts['dir'] ?? 'storage/app/swarthmore-cos', '/');
$full = (int) ($opts['full'] ?? 1000);
$thumb = (int) ($opts['thumb'] ?? 300);
$delayUs = (int) (($opts['delay'] ?? 350) * 1000);
$limit = (int) ($opts['limit'] ?? 0);
$start = max(1, (int) ($opts['start'] ?? 1));
$dryRun = isset($opts['dry-run']);
$onlyItems = isset($opts['items']) && $opts['items'] !== ''
    ? array_map('intval', explode(',', $opts['items']))
    : null;

@mkdir($dir, 0775, true);
out("Output dir: {$dir}");
out(sprintf('Sizes: fullsize=%dpx thumbnail=%dpx | delay=%dms%s', $full, $thumb, $delayUs / 1000, $dryRun ? ' | DRY RUN' : ''));

// --- 1. discover item IDs --------------------------------------------------
$itemIds = $onlyItems ?? discoverItemIds($delayUs);
out('Discovered '.count($itemIds).' items.');
if ($start > 1 || $limit > 0) {
    $itemIds = array_slice($itemIds, $start - 1, $limit > 0 ? $limit : null);
    out('Processing '.count($itemIds).' items (start='.$start.', limit='.($limit ?: 'all').').');
}

// --- 2 & 3. per item: manifest -> file ids -> download ---------------------
$totItems = 0;
$totFiles = 0;
$downloaded = 0;
$skipped = 0;
$failed = 0;
$bytes = 0;

foreach ($itemIds as $idx => $itemId) {
    $manifest = getJson(BASE."/iiif/{$itemId}/manifest", $delayUs);
    if (! $manifest) {
        out(sprintf('[%d/%d] item %d: manifest unavailable, skipping', $idx + 1, count($itemIds), $itemId));

        continue;
    }
    $canvases = $manifest['sequences'][0]['canvases'] ?? [];
    $fileIds = [];
    foreach ($canvases as $c) {
        foreach ($c['images'] ?? [] as $im) {
            $rid = $im['resource']['@id'] ?? '';
            if (preg_match('#/iiif-img/(\d+)/#', $rid, $m)) {
                $fileIds[] = (int) $m[1];
            }
        }
    }
    $totItems++;
    $totFiles += count($fileIds);

    $itemDir = "{$dir}/items/{$itemId}";
    @mkdir($itemDir, 0775, true);

    // metadata (tiny) — makes the copy browsable/useful
    if (! $dryRun) {
        $meta = [
            'item_id' => $itemId,
            'label' => $manifest['label'] ?? null,
            'source' => BASE."/items/show/{$itemId}",
            'metadata' => $manifest['metadata'] ?? [],
            'page_count' => count($fileIds),
        ];
        file_put_contents("{$itemDir}/item.json", json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    $seq = 0;
    foreach ($fileIds as $fileId) {
        $seq++;
        $pad = str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
        $targets = [
            "{$itemDir}/{$pad}-full.jpg" => "/iiif-img/{$fileId}/full/!{$full},{$full}/0/default.jpg",
            "{$itemDir}/{$pad}-thumb.jpg" => "/iiif-img/{$fileId}/full/!{$thumb},{$thumb}/0/default.jpg",
        ];
        foreach ($targets as $path => $iiifPath) {
            if (is_file($path) && filesize($path) > 0) {
                $skipped++;

                continue;
            }
            if ($dryRun) {
                continue;
            }
            $data = getBinary(BASE.$iiifPath, $delayUs);
            if ($data === null || strlen($data) < 100) {
                $failed++;
                out("  ! failed: item {$itemId} file {$fileId} ({$iiifPath})");

                continue;
            }
            file_put_contents($path, $data);
            $downloaded++;
            $bytes += strlen($data);
        }
    }

    out(sprintf('[%d/%d] item %d: %d page(s) | downloaded=%d skipped=%d failed=%d | %s',
        $idx + 1, count($itemIds), $itemId, count($fileIds), $downloaded, $skipped, $failed, human($bytes)));
}

out('');
out('DONE.');
out(sprintf('Items: %d | Files (pages): %d | Downloaded: %d | Skipped(existing): %d | Failed: %d | Total: %s',
    $totItems, $totFiles, $downloaded, $skipped, $failed, human($bytes)));
if ($dryRun) {
    out('(dry run — nothing was downloaded; estimated total is ~'.human((int) ($totFiles * ($full >= 1000 ? 150000 : 90000))).' for fullsize + a little more for thumbs)');
}

// =========================================================================

function discoverItemIds(int $delayUs): array
{
    $ids = [];
    for ($page = 1; ; $page++) {
        $html = fetchText(BASE.'/items/browse?page='.$page, $delayUs);
        if ($html === null) {
            break;
        }
        preg_match_all('#/items/show/(\d+)#', $html, $m);
        $found = array_values(array_unique(array_map('intval', $m[1])));
        $new = array_diff($found, $ids);
        if (empty($new)) {
            break; // no new items on this page → past the end
        }
        foreach ($new as $id) {
            $ids[] = $id;
        }
        out('  browse page '.$page.': +'.count($new).' items (total '.count($ids).')');
    }

    return $ids;
}

// --- HTTP helpers ----------------------------------------------------------

function httpGet(string $url, int $delayUs, int $tries = 4): ?array
{
    static $proxy = null, $ca = null, $init = false;
    if (! $init) {
        $init = true;
        $proxy = getenv('HTTPS_PROXY') ?: getenv('https_proxy') ?: null; // set in dev sandbox; absent on server
        foreach ([getenv('NODE_EXTRA_CA_CERTS'), '/root/.ccr/ca-bundle.crt'] as $c) {
            if ($c && is_file($c)) {
                $ca = $c;
                break;
            }
        }
    }

    for ($attempt = 1; $attempt <= $tries; $attempt++) {
        usleep($delayUs);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => 'NPPC-swarthmore-cos-mirror/1.0 (polite; contact via NPPC)',
        ]);
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        if ($ca) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body !== false && $code >= 200 && $code < 300) {
            return ['code' => $code, 'body' => $body];
        }
        if ($code === 404) {
            return ['code' => 404, 'body' => null]; // definitive miss, don't retry
        }
        // transient (5xx / network): back off and retry
        usleep($delayUs * $attempt * 2);
    }

    return null;
}

function fetchText(string $url, int $delayUs): ?string
{
    $r = httpGet($url, $delayUs);

    return $r && $r['body'] !== null ? $r['body'] : null;
}

function getBinary(string $url, int $delayUs): ?string
{
    $r = httpGet($url, $delayUs);

    return $r && $r['body'] !== null && $r['body'] !== '' ? $r['body'] : null;
}

function getJson(string $url, int $delayUs): ?array
{
    $t = fetchText($url, $delayUs);
    if ($t === null) {
        return null;
    }
    $d = json_decode($t, true);

    return is_array($d) ? $d : null;
}

// --- misc ------------------------------------------------------------------

function parseArgs(array $argv): array
{
    $o = [];
    foreach (array_slice($argv, 1) as $a) {
        if (preg_match('/^--([a-z-]+)(?:=(.*))?$/', $a, $m)) {
            $o[$m[1]] = $m[2] ?? true;
        }
    }

    return $o;
}

function human(int $b): string
{
    $u = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    $n = (float) $b;
    while ($n >= 1024 && $i < count($u) - 1) {
        $n /= 1024;
        $i++;
    }

    return sprintf('%.1f %s', $n, $u[$i]);
}

function out(string $s): void
{
    fwrite(STDOUT, $s.PHP_EOL);
}
