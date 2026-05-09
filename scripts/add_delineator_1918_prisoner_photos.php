<?php

declare(strict_types=1);

/**
 * Download the six paired mug-shot scans from the witness2fashion
 * article on Butterick's Delineator (July 1918), crop each pair down
 * the middle, and assign each half as the photo for its prisoner.
 *
 * Source article:
 *   https://witness2fashion.wordpress.com/2017/02/18/german-spies-pictured-in-fashion-magazine-1918/
 *
 * The 1918 Delineator magazine is in the public domain; the cropped
 * mug-shot panels are likewise public-domain government / press
 * photographs from that issue.
 *
 * Idempotent: skips any prisoner that already has a non-empty photo
 * field.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\Storage;

$base = 'https://witness2fashion.wordpress.com/wp-content/uploads/2017/02/';
// pair URL slug => [left prisoner name, right prisoner name]
$pairs = [
    '500-o-hare-and-cornell-1918-july-german-spies-huns-everywhere.jpg'        => ['Kate Richards O\'Hare',     'Margaret E. Cornell'],
    '500-olivereau-and-schmidt-1918-july-german-spies-huns-everywhere.jpg'     => ['Louise Olivereau',           null], // Carl Schmidt not a prisoner — skipped
    '500-von-brincken-and-jacobsen-1918-july-german-spies-huns-everywhere.jpg' => ['Wilhelm von Brincken',        'Gustave H. Jacobsen'],
    '500-von-shack-and-crowley-1918-july-german-spies-huns-everywhere.jpg'     => ['Eckhardt H. von Schack',      'Charles C. Crowley'],
    '500-bopp-and-von-rintalen1918-july-german-spies-huns-everywhere.jpg'      => ['Franz Bopp',                  'Franz von Rintelen'],
    '500-spence-and-lamar1918-july-german-spies-huns-everywhere.jpg'           => ['Homer C. Spence',             'David Lamar'],
];

if (! function_exists('imagecreatefromjpeg')) {
    echo "ERROR: PHP GD extension is required. Install it (e.g. apt-get install php-gd) and retry.\n";
    exit(1);
}

$disk = Storage::disk('public');
if (! $disk->exists('prisoners')) {
    $disk->makeDirectory('prisoners');
}

$ua = 'Mozilla/5.0 (compatible; NPPC-photo-import/1.0)';
$ctx = stream_context_create(['http' => ['user_agent' => $ua, 'timeout' => 25]]);

$updated = 0;
$skipped = 0;
$errors  = 0;

foreach ($pairs as $file => [$leftName, $rightName]) {
    $url = $base . $file;
    $tmp = tempnam(sys_get_temp_dir(), 'delin_') . '.jpg';

    echo "  downloading {$file}\n";
    $bytes = @file_get_contents($url, false, $ctx);
    if (! $bytes || strlen($bytes) < 4096) {
        echo "    [error]   could not download (" . strlen($bytes ?: '') . " bytes)\n";
        $errors++;
        continue;
    }
    file_put_contents($tmp, $bytes);

    $img = @imagecreatefromjpeg($tmp);
    if (! $img) {
        echo "    [error]   not a JPEG\n";
        $errors++;
        @unlink($tmp);
        continue;
    }
    $w = imagesx($img);
    $h = imagesy($img);
    $halfW = (int) floor($w / 2);

    foreach ([['left', $leftName, 0, $halfW], ['right', $rightName, $halfW, $w - $halfW]] as [$side, $name, $x, $cw]) {
        if (! $name) {
            echo "    [skip]    {$side} half (no prisoner mapped)\n";
            continue;
        }
        $p = Prisoner::where('name', $name)->first();
        if (! $p) {
            echo "    [warn]    {$side}: {$name} not in DB\n";
            $skipped++;
            continue;
        }
        if (! empty($p->photo)) {
            echo "    [skip]    {$name} already has a photo: {$p->photo}\n";
            $skipped++;
            continue;
        }

        $crop = imagecreatetruecolor($cw, $h);
        imagecopy($crop, $img, 0, 0, $x, 0, $cw, $h);

        $slug = $p->slug ?: \Illuminate\Support\Str::slug($name);
        $relPath = "prisoners/{$slug}.jpg";
        $absPath = $disk->path($relPath);

        if (! is_dir(dirname($absPath))) mkdir(dirname($absPath), 0755, true);
        imagejpeg($crop, $absPath, 90);
        imagedestroy($crop);

        $p->photo = $relPath;
        $p->save();

        echo "    [add]     {$name}  -> {$relPath}\n";
        $updated++;
    }

    imagedestroy($img);
    @unlink($tmp);
}

echo "\nDone. updated={$updated}, skipped={$skipped}, errors={$errors}\n";
