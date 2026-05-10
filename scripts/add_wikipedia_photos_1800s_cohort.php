<?php

declare(strict_types=1);

/**
 * For each prisoner in the 1800s gap-fill tranche, query the
 * Wikipedia REST summary API for the canonical article and download
 * the lead image to storage/app/public/prisoners/{slug}.{ext}.
 *
 * Skips any prisoner that already has a non-empty photo (to avoid
 * overwriting existing curated images). Hardcoded prisoner-name to
 * Wikipedia article-title map prevents free-text search hitting the
 * wrong page (e.g. "Thomas Cooper" the Sedition Act defendant vs.
 * the many other Thomas Coopers).
 *
 * Defendants with no Wikipedia article (David Brown, Anthony Haswell,
 * Luther Baldwin, Edward Sayres) are intentionally absent from the
 * map.
 *
 * Idempotent — a re-run with --force overwrites; default skips.
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
    // A. Sedition Act of 1798
    'Matthew Lyon'                  => 'Matthew_Lyon',
    'Thomas Cooper'                 => 'Thomas_Cooper_(American_politician,_born_1759)',
    'James Thompson Callender'      => 'James_Thomson_Callender',
    'William Duane'                 => 'William_Duane_(journalist)',
    // (David Brown, Anthony Haswell, Luther Baldwin: no Wikipedia article)

    // B. Antebellum abolitionists
    'William Lloyd Garrison'        => 'William_Lloyd_Garrison',
    'Prudence Crandall'             => 'Prudence_Crandall',
    'Calvin Fairbank'               => 'Calvin_Fairbank',
    'Charles T. Torrey'             => 'Charles_Turner_Torrey',
    'Daniel Drayton'                => 'Daniel_Drayton',
    'Jonathan Walker'               => 'Jonathan_Walker_(abolitionist)',
    'Sherman Booth'                 => 'Sherman_Booth',
    // (Edward Sayres: no Wikipedia article)

    // C. Civil War habeas
    'Lambdin P. Milligan'           => 'Lambdin_P._Milligan',
    'John Merryman'                 => 'John_Merryman',

    // D. Mormon polygamy
    'George Reynolds'               => 'George_Reynolds_(Mormon)',
    'George Q. Cannon'              => 'George_Q._Cannon',
    'John Taylor (LDS President)'   => 'John_Taylor_(Mormon)',

    // E. Suffrage / women's rights
    'Susan B. Anthony'              => 'Susan_B._Anthony',
    'Sojourner Truth'               => 'Sojourner_Truth',

    // F. Labor
    'Eugene Debs'                   => 'Eugene_V._Debs',
    'Jacob Coxey'                   => 'Jacob_Coxey',
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
        echo "  [skip]    {$name} already has a photo: {$p->photo}\n";
        $skippedExisting++;
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
        echo "  [no img]  {$name}: Wikipedia page has no lead image\n"; $noImg++;
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
echo "Note: defendants with no Wikipedia article (David Brown, Anthony Haswell, Luther Baldwin, Edward Sayres) keep an empty photo field and can be hand-uploaded later via the admin if needed.\n";
