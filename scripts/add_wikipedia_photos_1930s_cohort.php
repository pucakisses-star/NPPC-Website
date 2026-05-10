<?php

declare(strict_types=1);

/**
 * Wikipedia photo fetch for the 1930s cohort just added.
 *
 * Most of the 9 Scottsboro Boys have only group / mug-shot photos
 * on the shared "Scottsboro Boys" Wikipedia article rather than
 * individual articles. The few who DO have individual Wikipedia
 * articles (Haywood Patterson, Clarence Norris, Ozie Powell, Andy
 * Wright) get their lead images downloaded directly. The rest fall
 * back to the cohort group photo on the Scottsboro Boys article so
 * every defendant has at least an illustrative photo.
 *
 * Same fallback for the Gastonia 7: only Fred Beal has an
 * individual Wikipedia article. The other six get the group photo
 * from the Loray Mill strike Wikipedia article (or none if Wikipedia
 * doesn't have one).
 *
 * Skips any prisoner that already has a non-empty photo. --force to
 * overwrite. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

$force = in_array('--force', $argv ?? [], true);

// Individual articles to try first.
$individuals = [
    'Haywood Patterson'    => 'Haywood_Patterson',
    'Clarence Norris'      => 'Clarence_Norris',
    'Ozie Powell'          => 'Ozie_Powell',
    'Andy Wright'          => 'Andy_Wright',
    'Charlie Weems'        => 'Charlie_Weems',
    'Olen Montgomery'      => 'Olen_Montgomery',
    'Willie Roberson'      => 'Willie_Roberson',
    'Eugene Williams'      => 'Eugene_Williams_(Scottsboro_Boy)',
    'Roy Wright'           => 'Roy_Wright_(Scottsboro_Boy)',
    'Fred Beal'            => 'Fred_Erwin_Beal',
];

// Cohort fallbacks: prisoner names => shared Wikipedia article whose
// lead image will stand in for an individual photo.
$cohortFallbacks = [
    'Scottsboro Boys' => [
        'Haywood Patterson', 'Clarence Norris', 'Ozie Powell',
        'Andy Wright', 'Charlie Weems', 'Olen Montgomery',
        'Willie Roberson', 'Eugene Williams', 'Roy Wright',
    ],
    'Loray_Mill_strike' => [
        'Fred Beal', 'Clarence Miller', 'Joseph Harrison',
        'K. Y. Hendricks', 'George Carter', 'Louis McLaughlin',
        'Robert Allen',
    ],
];

$disk = Storage::disk('public');
if (! $disk->exists('prisoners')) $disk->makeDirectory('prisoners');

$ua = ['User-Agent' => 'NPPC-photo-import/1.0 (https://nppc.org)'];

$cohortImageCache = [];
$fetchCohortImage = function (string $title) use (&$cohortImageCache, $ua): ?string {
    if (array_key_exists($title, $cohortImageCache)) return $cohortImageCache[$title];
    try {
        $resp = Http::withHeaders($ua + ['Accept' => 'application/json'])->timeout(20)
                    ->get("https://en.wikipedia.org/api/rest_v1/page/summary/" . $title);
    } catch (\Throwable $e) { $cohortImageCache[$title] = null; return null; }
    if (! $resp->successful()) { $cohortImageCache[$title] = null; return null; }
    $body = $resp->json() ?: [];
    $url = $body['originalimage']['source'] ?? $body['thumbnail']['source'] ?? null;
    $cohortImageCache[$title] = $url;
    return $url;
};

$tryDownload = function (string $url) use ($ua): ?string {
    try {
        $r = Http::withHeaders($ua)->timeout(30)->get($url);
    } catch (\Throwable $e) { return null; }
    if (! $r->successful() || strlen($r->body()) < 1024) return null;
    return $r->body();
};

$saveAndAssign = function (Prisoner $p, string $bytes, string $url): void {
    $disk = Storage::disk('public');
    $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg');
    if (! in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) $ext = 'jpg';
    $slug = $p->slug ?: Str::slug($p->name);
    $relPath = "prisoners/{$slug}.{$ext}";
    $disk->put($relPath, $bytes);
    $p->photo = $relPath;
    $p->save();
};

$updated = 0;
$skippedExisting = 0;
$noImg   = 0;
$missing = 0;

// Build a name -> cohort fallback title map for second-pass lookups.
$nameToCohort = [];
foreach ($cohortFallbacks as $title => $names) {
    foreach ($names as $n) $nameToCohort[$n] = $title;
}

foreach ($individuals as $name => $title) {
    $p = Prisoner::where('name', $name)->first();
    if (! $p) {
        echo "  [warn]    {$name} not in DB\n"; $missing++;
        continue;
    }
    if (! $force && ! empty($p->photo)) {
        echo "  [skip]    {$name} already has a photo: {$p->photo}\n"; $skippedExisting++;
        continue;
    }

    $imgUrl = $fetchCohortImage($title);
    if ($imgUrl) {
        $bytes = $tryDownload($imgUrl);
        if ($bytes) {
            $saveAndAssign($p, $bytes, $imgUrl);
            echo "  [add]     {$name}  -> individual Wikipedia article {$title}\n";
            $updated++;
            continue;
        }
    }

    // Fall back to a cohort image if available.
    $cohortTitle = $nameToCohort[$name] ?? null;
    if ($cohortTitle) {
        $cohortUrl = $fetchCohortImage($cohortTitle);
        if ($cohortUrl) {
            $bytes = $tryDownload($cohortUrl);
            if ($bytes) {
                $saveAndAssign($p, $bytes, $cohortUrl);
                echo "  [add*]    {$name}  -> cohort image from {$cohortTitle}\n";
                $updated++;
                continue;
            }
        }
    }

    echo "  [no img]  {$name}: no Wikipedia image found\n"; $noImg++;
}

// Now Gastonia 7 members not in $individuals, fallback only.
$gastoniaOnly = ['Clarence Miller', 'Joseph Harrison', 'K. Y. Hendricks', 'George Carter', 'Louis McLaughlin', 'Robert Allen'];
foreach ($gastoniaOnly as $name) {
    $p = Prisoner::where('name', $name)->first();
    if (! $p) { echo "  [warn]    {$name} not in DB\n"; $missing++; continue; }
    if (! $force && ! empty($p->photo)) {
        echo "  [skip]    {$name} already has a photo\n"; $skippedExisting++;
        continue;
    }
    $cohortUrl = $fetchCohortImage('Loray_Mill_strike');
    if ($cohortUrl) {
        $bytes = $tryDownload($cohortUrl);
        if ($bytes) {
            $saveAndAssign($p, $bytes, $cohortUrl);
            echo "  [add*]    {$name}  -> cohort image from Loray_Mill_strike\n";
            $updated++;
            continue;
        }
    }
    echo "  [no img]  {$name}: no Wikipedia cohort image available\n"; $noImg++;
}

echo "\nDone. updated={$updated}, skipped_existing={$skippedExisting}, no_image={$noImg}, missing_in_db={$missing}\n";
echo "Notes:\n";
echo "  [add]   = pulled from the individual's own Wikipedia article.\n";
echo "  [add*]  = no individual article; pulled the cohort image from the shared article (Scottsboro Boys / Loray Mill strike).\n";
