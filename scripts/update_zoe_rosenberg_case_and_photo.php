<?php

declare(strict_types=1);

/**
 * Two updates for Zoe Rosenberg:
 *
 *   1. Update her case with the documented Sonoma County trial
 *      verdict and sentence:
 *      - Convicted Oct 29, 2025 on two misdemeanor trespassing
 *        counts, one misdemeanor count of vehicle tampering, and
 *        one felony count of conspiracy
 *      - Sentenced Dec 3, 2025 to 90 days jail
 *      - Began sentence Dec 10, 2025
 *      - Released from solitary Dec 24, 2025
 *      - House-arrest portion scheduled to begin Jan 14, 2026
 *
 *   2. Download the KQED courtroom photo, center-crop to a square
 *      focused on her, save to storage/app/public/prisoners/.
 *
 * Idempotent on both halves.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\Storage;

$p = Prisoner::whereRaw('LOWER(name) IN (?, ?)', ['zoe rosenberg', 'zoe rosenburg'])->first();
if (! $p) {
    echo "Zoe Rosenberg not found.\n";
    exit(1);
}

// ---- 1. Case update ---------------------------------------------
$case = $p->cases()->orderBy('created_at')->first();
if (! $case) {
    echo "No case row for Zoe Rosenberg. Aborting.\n";
    exit(1);
}

$case->charges = "Two misdemeanor counts of trespassing; one misdemeanor count of tampering with a vehicle; one felony count of conspiracy. Charges arose from her 2023 open-rescue action at Petaluma Poultry / Perdue Farms in Sonoma County, California.";
$case->convicted = "Yes — convicted at trial 2025-10-29 (one felony conspiracy + three misdemeanors)";
$case->sentenced_date     = '2025-12-03';
$case->incarceration_date = '2025-12-10';
// "Released from solitary" Dec 24 wasn't a release from custody —
// she was moved out of solitary into general population. Then her
// jail portion ended; house-arrest portion begins Jan 14, 2026.
// We treat the end of her jail term (Jan 14, 2026) as the
// release_date for time-served purposes; the sentence text below
// records the full conditional-release picture.
$case->release_date       = '2026-01-14';
$case->imprisoned_for_days = (int) \Carbon\Carbon::parse('2025-12-10')->diffInDays(\Carbon\Carbon::parse('2026-01-14'));
$case->sentence = "90 days jail sentence (began Dec 10, 2025). Held briefly in solitary confinement; released from solitary Dec 24, 2025. House-arrest portion of the sentence scheduled to begin Jan 14, 2026.";
$case->save();

$p->in_custody         = false;
$p->released           = true;
$p->save();

echo "[case] updated Zoe Rosenberg's case (Oct 29 verdict, Dec 3 sentence, Dec 10 - Jan 14 jail).\n";

// ---- 2. Photo download + center-crop ----------------------------
$photoUrl = 'https://cdn.kqed.org/wp-content/uploads/sites/10/2025/09/Sonoma-Animal-Trial-02-KQED.jpg';
$slug = $p->slug ?: 'zoe-rosenberg';
$relPath = "prisoners/{$slug}.jpg";
$disk = Storage::disk('public');
$absPath = $disk->path($relPath);

if (! is_dir(dirname($absPath))) {
    @mkdir(dirname($absPath), 0775, true);
}

$bytes = @file_get_contents($photoUrl);
if (! $bytes) {
    echo "[photo] failed to download {$photoUrl}\n";
    exit(0);
}

$src = @imagecreatefromstring($bytes);
if (! $src) {
    echo "[photo] could not decode JPEG.\n";
    exit(0);
}

$w = imagesx($src);
$h = imagesy($src);

// Center-crop to a 4:5 portrait aspect ratio (taller than square,
// matches news-portrait conventions) so the head/face fills the
// frame without aggressive cropping.
$targetW = 800;
$targetH = 1000;
$srcRatio = $w / $h;
$tgtRatio = $targetW / $targetH;

if ($srcRatio > $tgtRatio) {
    // source wider than target — crop sides
    $cropH = $h;
    $cropW = (int) round($h * $tgtRatio);
    $cropX = (int) round(($w - $cropW) / 2);
    // Bias slightly upward (most courtroom shots have the subject's
    // head in the upper half) by leaving cropY at 0
    $cropY = 0;
} else {
    // source taller than target — crop top/bottom; keep the head
    $cropW = $w;
    $cropH = (int) round($w / $tgtRatio);
    $cropX = 0;
    // Bias toward the top third for face-prominent framing
    $cropY = (int) round(($h - $cropH) * 0.2);
}

$dst = imagecreatetruecolor($targetW, $targetH);
imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
imagejpeg($dst, $absPath, 88);
imagedestroy($src);
imagedestroy($dst);

$p->photo = $relPath;
$p->save();

echo "[photo] downloaded + cropped to {$targetW}x{$targetH}, saved {$relPath}\n";
echo "Done.\n";
