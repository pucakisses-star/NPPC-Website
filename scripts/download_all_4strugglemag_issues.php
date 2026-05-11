<?php

declare(strict_types=1);

/**
 * Download every 4strugglemag PDF currently listed on the
 * publisher's /download/ page into storage/app/public/archive/,
 * then rewrite the 4strugglemag section of the Archive page so
 * the issue grid lists all 11 issues plus the two supplementary
 * solidarity booklets.
 *
 * Issue dates use the publisher's WordPress upload date as a
 * best-available proxy where the original publication date isn't
 * confirmed. Known exact dates: #11 = Fall 2008, #14 = Winter
 * 2009. Others labelled with their upload month; edit in admin
 * to tighten if you have better data.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;
use Illuminate\Support\Facades\Storage;

$disk = Storage::disk('public');
@mkdir($disk->path('archive'), 0775, true);

// issue => [source URL, local filename, date label]
$issues = [
    11 => ['https://4strugglemag.org/wp-content/uploads/2010/03/4sm11collated.pdf', '4strugglemag-issue-11.pdf', 'Fall 2008'],
    12 => ['https://4strugglemag.org/wp-content/uploads/2010/03/4sm12collated.pdf', '4strugglemag-issue-12.pdf', 'Winter 2008–09'],
    13 => ['https://4strugglemag.org/wp-content/uploads/2010/03/4sm13collated.pdf', '4strugglemag-issue-13.pdf', 'Spring 2009'],
    14 => ['https://4strugglemag.org/wp-content/uploads/2010/03/4sm14collated.pdf', '4strugglemag-issue-14.pdf', 'Winter 2009'],
    15 => ['https://4strugglemag.org/wp-content/uploads/2010/03/4sm15collated1.pdf','4strugglemag-issue-15.pdf', 'Spring 2010'],
    16 => ['https://4strugglemag.org/wp-content/uploads/2010/08/4sm16collated.pdf', '4strugglemag-issue-16.pdf', 'Summer 2010'],
    17 => ['https://4strugglemag.org/wp-content/uploads/2010/11/4sm17collated.pdf', '4strugglemag-issue-17.pdf', 'Fall 2010'],
    18 => ['https://4strugglemag.org/wp-content/uploads/2011/03/4sm18collated.pdf', '4strugglemag-issue-18.pdf', 'Spring 2011'],
    19 => ['https://4strugglemag.org/wp-content/uploads/2011/07/4sm19collated.pdf', '4strugglemag-issue-19.pdf', 'Summer 2011'],
    20 => ['https://4strugglemag.org/wp-content/uploads/2012/10/4sm20collated2.pdf','4strugglemag-issue-20.pdf', 'Fall 2012'],
    21 => ['https://4strugglemag.org/wp-content/uploads/2012/10/4sm21collated.pdf', '4strugglemag-issue-21.pdf', 'Late 2012'],
];

$extras = [
    'courtsolidarity'         => ['https://4strugglemag.org/wp-content/uploads/2010/03/courtsolidarity.pdf',         'courtsolidarity.pdf',         'Court Solidarity (booklet)'],
    'legalsolidarityhandbook' => ['https://4strugglemag.org/wp-content/uploads/2010/03/legalsolidarityhandbook.pdf', 'legalsolidarityhandbook.pdf', 'Legal Solidarity Handbook'],
];

function fetchPdf(string $src, string $dst): bool
{
    if (is_file($dst) && filesize($dst) > 1024) return true;
    $ctx = stream_context_create([
        'http' => ['user_agent' => 'Mozilla/5.0 (NPPC archive downloader)', 'timeout' => 60],
    ]);
    $bytes = @file_get_contents($src, false, $ctx);
    if ($bytes === false || strlen($bytes) < 1024) return false;
    file_put_contents($dst, $bytes);
    return true;
}

// ---- Downloads ---------------------------------------------------
foreach ($issues as $n => [$src, $rel, $date]) {
    $abs = Storage::disk('public')->path("archive/{$rel}");
    if (fetchPdf($src, $abs)) {
        echo "[pdf] issue #{$n} ({$date}) -> archive/{$rel} (" . filesize($abs) . " bytes)\n";
    } else {
        echo "[pdf] issue #{$n} FAILED ({$src})\n";
    }
}

foreach ($extras as $slug => [$src, $rel, $label]) {
    $abs = Storage::disk('public')->path("archive/{$rel}");
    if (fetchPdf($src, $abs)) {
        echo "[pdf] extra '{$label}' -> archive/{$rel} (" . filesize($abs) . " bytes)\n";
    } else {
        echo "[pdf] extra '{$label}' FAILED ({$src})\n";
    }
}

// ---- Page body rewrite -------------------------------------------
$page = Page::where('slug', 'archive')->first();
if (! $page) {
    echo "Archive page not found.\n";
    exit(1);
}
$body = $page->body;

// Build the new issue-tile grid
$tiles = '';
foreach ($issues as $n => [, $rel, $date]) {
    $tiles .= <<<HTML

      <a href="/storage/archive/{$rel}" class="archive-card" data-issue="4strugglemag-{$n}" style="display: block; padding: 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; text-decoration: none; color: inherit;">
        <div style="font-size: 13px; opacity: 0.6; letter-spacing: 0.08em; text-transform: uppercase;">Issue #{$n}</div>
        <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">{$date}</div>
      </a>
HTML;
}

// Supplementary booklets
$extraTiles = '';
foreach ($extras as [, $rel, $label]) {
    $extraTiles .= <<<HTML

      <a href="/storage/archive/{$rel}" class="archive-card" style="display: block; padding: 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; text-decoration: none; color: inherit;">
        <div style="font-size: 13px; opacity: 0.6; letter-spacing: 0.08em; text-transform: uppercase;">Supplement</div>
        <div style="font-size: 16px; font-weight: 700; margin-top: 4px;">{$label}</div>
      </a>
HTML;
}

// Replace everything between the 4strugglemag grid markers
// (the <div style="display: grid; ...">...</div> immediately
// following the description). Strategy: locate the section by
// its data-publication attribute, find the inner grid, replace.
$marker  = 'data-publication="4strugglemag"';
$gridOpen  = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">';
$gridClose = '</div>';

if (str_contains($body, $marker)) {
    // Find the position of the data-publication marker, then the
    // next $gridOpen after it, and replace that grid's contents.
    $startMarker = strpos($body, $marker);
    $gridStart   = strpos($body, $gridOpen, $startMarker);
    if ($gridStart !== false) {
        // Find matching close — count nested divs
        $i = $gridStart + strlen($gridOpen);
        $depth = 1;
        while ($i < strlen($body) && $depth > 0) {
            $nextOpen  = strpos($body, '<div', $i);
            $nextClose = strpos($body, '</div>', $i);
            if ($nextClose === false) break;
            if ($nextOpen !== false && $nextOpen < $nextClose) {
                $depth++;
                $i = $nextOpen + 4;
            } else {
                $depth--;
                $i = $nextClose + 6;
            }
        }
        // $i is just after the matching </div>
        $body = substr($body, 0, $gridStart)
              . $gridOpen
              . $tiles . "\n\n      <!-- Supplementary booklets -->" . $extraTiles . "\n\n    "
              . substr($body, $i - 6); // re-include the closing </div>
        echo "[body] rewrote 4strugglemag issue grid with " . count($issues) . " issues + " . count($extras) . " extras\n";
    } else {
        echo "[body] couldn't find grid open inside 4strugglemag section — skipping rewrite\n";
    }
} else {
    echo "[body] 4strugglemag section not found in page body — run add_4strugglemag_to_archive.php first\n";
}

$page->body = $body;
$page->save();
echo "Done.\n";
