<?php

declare(strict_types=1);

/**
 * Wikipedia photo fetch for the 8 prisoners just added in
 * scripts/apply_1940s_political_prisoners.php.
 *
 * All 8 have individual Wikipedia articles. Hardcoded title map so
 * that James Peck matches the pacifist (not James Peck the
 * Australian rugby player or the various other James Pecks) and
 * George Houser matches the CORE founder (not a different George
 * Houser).
 *
 * Skips prisoners that already have a non-empty photo. --force to
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

$map = [
    'Fred Korematsu'       => 'Fred_Korematsu',
    'Mitsuye Endo'         => 'Mitsuye_Endo',
    'Gordon Hirabayashi'   => 'Gordon_Hirabayashi',
    'Minoru Yasui'         => 'Minoru_Yasui',
    'Frank Emi'            => 'Frank_Emi',
    'Ralph DiGia'          => 'Ralph_DiGia',
    'James Peck'           => 'James_Peck_(pacifist)',
    'George Houser'        => 'George_Houser',
];

$disk = Storage::disk('public');
if (! $disk->exists('prisoners')) $disk->makeDirectory('prisoners');

$updated = 0;
$skippedExisting = 0;
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
    if (! $force && ! empty($p->photo)) {
        echo "  [skip]    {$name} already has a photo: {$p->photo}\n"; $skippedExisting++;
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
        echo "  [no page] {$name}: HTTP {$resp->status()} for /{$title}\n"; $noPage++;
        continue;
    }

    $body = $resp->json() ?: [];
    $imgUrl = $body['originalimage']['source']
           ?? $body['thumbnail']['source']
           ?? null;
    if (! $imgUrl) {
        echo "  [no img]  {$name}: page has no lead image\n"; $noImg++;
        continue;
    }

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
        echo "  [error]   {$name}: image download returned {$imgResp->status()} / " . strlen($imgResp->body()) . " bytes\n"; $errors++;
        continue;
    }

    $slug = $p->slug ?: Str::slug($name);
    $relPath = "prisoners/{$slug}.{$ext}";
    $disk->put($relPath, $imgResp->body());

    $oldPhoto = (string) $p->photo;
    $p->photo = $relPath;
    $p->save();

    $note = $oldPhoto ? "(replaced {$oldPhoto})" : '';
    echo "  [add]     {$name}  -> {$relPath} {$note}\n";
    $updated++;
}

echo "\nDone. updated={$updated}, skipped_existing={$skippedExisting}, no_page={$noPage}, no_image={$noImg}, missing_in_db={$missing}, errors={$errors}\n";
