<?php

declare(strict_types=1);

/**
 * For each 1920s prisoner just added, query the Wikipedia REST
 * summary API for the canonical article and download the lead
 * image to storage/app/public/prisoners/{slug}.{ext}.
 *
 * Skips any prisoner that already has a non-empty photo. Hardcoded
 * map prevents free-text matches against the wrong page (the
 * Centralia defendants have generic-sounding names — there are many
 * "Britt Smith"s, "Ray Becker"s, "John Lamb"s, etc. on Wikipedia).
 *
 * Most of the Centralia 8 don't have individual Wikipedia articles;
 * those will register as [no page] and keep an empty photo. The
 * shared "Centralia massacre" article on Wikipedia carries
 * cohort/group images that aren't suitable as individual photos.
 *
 * Idempotent. --force to overwrite.
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
    'John Scopes'           => 'John_T._Scopes',
    'Wesley Everest'        => 'Wesley_Everest',
    'Eugene Barnett'        => 'Eugene_Barnett',
    'Britt Smith'           => 'Britt_Smith_(IWW)',
    'John Lamb'             => 'John_Lamb_(IWW)',
    'Oliver Charles Bland'  => 'O._C._Bland',
    'James Bertie Bland'    => 'James_Bertie_Bland',
    'James McInerney'       => 'James_McInerney_(IWW)',
    'Ray Becker'            => 'Ray_Becker_(IWW)',
    'Loren Roberts'         => 'Loren_Roberts',
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
echo "Note: most of the Centralia 8 don't have individual Wikipedia articles; those will register as [no page] and keep an empty photo. The cohort photo on the Centralia massacre Wikipedia page is a group shot of all 7 defendants together — not suitable for individual records, but if you want it as a shared illustration on each profile let me know and I'll wire it up separately.\n";
