<?php

declare(strict_types=1);

/**
 * For each prisoner mentioned in the Delineator 1918 article (and a
 * few cohort members already in the DB), query the Wikipedia REST
 * summary API for the canonical article. If the page has an
 * `originalimage` (the high-res lead photo from Wikimedia Commons),
 * download it and replace the prisoner's photo — overwriting the
 * cropped Delineator mug-shot when a Wikipedia photo is available.
 *
 * Hardcoded mapping of prisoner name → Wikipedia article title is
 * used to avoid free-text search hitting the wrong page (e.g.
 * "Franz Bopp" the WWI Consul-General vs. Franz Bopp the 19th-century
 * Sanskritist). Prisoners with no plausible Wikipedia article are
 * intentionally absent from the map and keep their existing photo.
 *
 * Idempotent. Re-running this script just re-fetches the same
 * Wikipedia images.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

// Prisoner name in DB => Wikipedia article title (the URL-encoded
// version after /wiki/ on en.wikipedia.org).
$map = [
    "Kate Richards O'Hare"     => "Kate_Richards_O'Hare",
    'Louise Olivereau'         => 'Louise_Olivereau',
    'Wilhelm von Brincken'     => 'Wilhelm_von_Brincken',
    'Franz Bopp'               => 'Franz_Bopp_(consul)',
    'Franz von Rintelen'       => 'Franz_von_Rintelen',
    'David Lamar'              => 'David_Lamar',
    'Thomas Mooney'            => 'Tom_Mooney_(activist)',
    'Warren Billings'          => 'Warren_K._Billings',
    // Obscure defendants with no plausible Wikipedia article are
    // intentionally not listed here:
    //   Margaret E. Cornell, Charles C. Crowley, Eckhardt H. von
    //   Schack, Gustave H. Jacobsen, Homer C. Spence
];

$disk = Storage::disk('public');
if (! $disk->exists('prisoners')) $disk->makeDirectory('prisoners');

$updated = 0;
$noPage  = 0;
$noImg   = 0;
$missing = 0;
$errors  = 0;

foreach ($map as $name => $title) {
    $p = Prisoner::where('name', $name)->first();
    if (! $p) {
        echo "  [warn]    {$name} not in DB — skipping\n"; $missing++;
        continue;
    }

    $endpoint = "https://en.wikipedia.org/api/rest_v1/page/summary/" . $title;
    try {
        $resp = Http::withHeaders([
            'User-Agent' => 'NPPC-photo-import/1.0 (https://nppc.org)',
            'Accept'     => 'application/json',
        ])->timeout(20)->get($endpoint);
    } catch (\Throwable $e) {
        echo "  [error]   {$name}: {$e->getMessage()}\n"; $errors++;
        continue;
    }

    if (! $resp->successful()) {
        echo "  [no page] {$name}: HTTP {$resp->status()} for /{$title}\n";
        $noPage++;
        continue;
    }

    $body = $resp->json() ?: [];
    $imgUrl = $body['originalimage']['source']
           ?? $body['thumbnail']['source']
           ?? null;
    if (! $imgUrl) {
        echo "  [no img]  {$name}: Wikipedia page has no lead image\n"; $noImg++;
        continue;
    }

    // Pick a sensible extension from the URL (Wikipedia Commons returns
    // .jpg, .jpeg, .png, .gif, etc.).
    $ext = strtolower(pathinfo(parse_url($imgUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg');
    if (! in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) $ext = 'jpg';

    try {
        $imgResp = Http::withHeaders(['User-Agent' => 'NPPC-photo-import/1.0 (https://nppc.org)'])
                       ->timeout(30)->get($imgUrl);
    } catch (\Throwable $e) {
        echo "  [error]   {$name}: image download failed: {$e->getMessage()}\n"; $errors++;
        continue;
    }
    if (! $imgResp->successful() || strlen($imgResp->body()) < 1024) {
        echo "  [error]   {$name}: image download returned {$imgResp->status()} / " . strlen($imgResp->body()) . " bytes\n";
        $errors++;
        continue;
    }

    $slug = $p->slug ?: \Illuminate\Support\Str::slug($name);
    $relPath = "prisoners/{$slug}.{$ext}";
    $disk->put($relPath, $imgResp->body());

    $oldPhoto = (string) $p->photo;
    $p->photo = $relPath;
    $p->save();

    $note = $oldPhoto ? "(replaced {$oldPhoto})" : "";
    echo "  [swap]    {$name}  -> {$relPath} {$note}\n";
    $updated++;
}

echo "\nDone. updated={$updated}, no_page={$noPage}, no_image={$noImg}, missing_in_db={$missing}, errors={$errors}\n";
