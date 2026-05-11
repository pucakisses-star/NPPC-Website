<?php

declare(strict_types=1);

/**
 * Download two 4strugglemag PDFs from azinelibrary.org into the
 * Archive storage, and update the Archive page body so the issue
 * tiles point at the actual downloaded filenames.
 *
 *   Issue #11 (Fall 2008)
 *   Issue #14 (Winter 2009)
 *
 * Idempotent: skips downloads if files already exist; only
 * updates the body if the matching href isn't already present.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;
use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('public');
$dir = $disk->path('archive');
if (! is_dir($dir)) @mkdir($dir, 0775, true);

$pdfs = [
    [
        'src'    => 'https://azinelibrary.org/approved/4strugglemag-issue-11-1.pdf',
        'rel'    => 'archive/4strugglemag-issue-11.pdf',
        'issue'  => '#11',
        'date'   => 'Fall 2008',
        'anchor' => 'data-issue="4strugglemag-11"',
    ],
    [
        'src'    => 'https://azinelibrary.org/approved/4strugglemag-issue-14-1.pdf',
        'rel'    => 'archive/4strugglemag-issue-14.pdf',
        'issue'  => '#14',
        'date'   => 'Winter 2009',
        'anchor' => 'data-issue="4strugglemag-14"',
    ],
];

foreach ($pdfs as $p) {
    $abs = $disk->path($p['rel']);
    if (is_file($abs) && filesize($abs) > 1024) {
        echo "[pdf] {$p['rel']} already present ({$abs}, " . filesize($abs) . " bytes)\n";
        continue;
    }
    $ctx = stream_context_create([
        'http' => ['user_agent' => 'Mozilla/5.0 (NPPC archive downloader)', 'timeout' => 30],
    ]);
    $bytes = @file_get_contents($p['src'], false, $ctx);
    if ($bytes === false || strlen($bytes) < 1024) {
        echo "[pdf] FAILED to download {$p['src']}\n";
        continue;
    }
    file_put_contents($abs, $bytes);
    echo "[pdf] downloaded {$p['src']} -> {$p['rel']} (" . strlen($bytes) . " bytes)\n";
}

// ---- Update the Archive page body so tiles point at the downloads
$page = Page::where('slug', 'archive')->first();
if (! $page) {
    echo "Archive page not found.\n";
    exit(1);
}

$body = $page->body;

// Replace the existing #14 tile (the previous script set it with
// href=/storage/archive/4strugglemag-14.pdf and no data-issue
// anchor) and add an #11 tile alongside it.
//
// We rewrite the #14 tile to use the correct downloaded filename
// and add data-issue for idempotency, then insert #11 ahead of it.
$oldTile14 = '<a href="/storage/archive/4strugglemag-14.pdf" class="archive-card"';
$newTile14 = '<a href="/storage/archive/4strugglemag-issue-14.pdf" class="archive-card" data-issue="4strugglemag-14"';

if (str_contains($body, $oldTile14)) {
    $body = str_replace($oldTile14, $newTile14, $body);
    echo "[body] rewrote #14 href to the downloaded filename\n";
}

// Insert #11 tile before #14 tile (chronological)
if (! str_contains($body, 'data-issue="4strugglemag-11"')) {
    $tile11 = <<<'HTML'
      <a href="/storage/archive/4strugglemag-issue-11.pdf" class="archive-card" data-issue="4strugglemag-11" style="display: block; padding: 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; text-decoration: none; color: inherit;">
        <div style="font-size: 13px; opacity: 0.6; letter-spacing: 0.08em; text-transform: uppercase;">Issue #11</div>
        <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">Fall 2008</div>
      </a>


HTML;

    // Insert immediately before the #14 tile
    $needle = '<a href="/storage/archive/4strugglemag-issue-14.pdf"';
    if (str_contains($body, $needle)) {
        $body = str_replace($needle, $tile11 . '      ' . $needle, $body);
        echo "[body] inserted #11 tile before #14\n";
    } else {
        echo "[body] couldn't find #14 anchor to insert #11 — manual placement needed.\n";
    }
} else {
    echo "[body] #11 already present\n";
}

$page->body = $body;
$page->save();
echo "Done.\n";
