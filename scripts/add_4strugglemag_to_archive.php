<?php

declare(strict_types=1);

/**
 * Add a 4strugglemag section to the Archive page.
 *
 * Downloads the magazine cover from southchicagoabc.org (the Brave
 * image-search URL the user supplied wraps that origin URL) into
 * storage/app/public/archive/covers/. Inserts a new publication
 * section into the page body right before the "Other Movement
 * Press" section so it sits alongside the other named publications.
 *
 * Idempotent: skips the body insert if a 4strugglemag section is
 * already present, and skips the image download if the file
 * already exists.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;
use Illuminate\Support\Facades\Storage;

$page = Page::where('slug', 'archive')->first();
if (! $page) {
    echo "Archive page not found. Run add_archive_page_under_learn_more.php first.\n";
    exit(1);
}

// ---- 1. Download the cover image (idempotent) ------------------
$disk = Storage::disk('public');
$relImg = 'archive/covers/4strugglemag-14-winter-2009.jpg';
$absImg = $disk->path($relImg);

if (! is_dir(dirname($absImg))) {
    @mkdir(dirname($absImg), 0775, true);
}

if (! is_file($absImg)) {
    $src = 'https://www.southchicagoabc.org/legacy-thumbnail/4strugglemag-14-winter-2009.jpg';
    $bytes = @file_get_contents($src);
    if ($bytes !== false) {
        file_put_contents($absImg, $bytes);
        echo "[image] downloaded {$src} -> {$relImg}\n";
    } else {
        echo "[image] download failed for {$src} — placeholder will 404 until image is added manually\n";
    }
} else {
    echo "[image] already present at {$relImg}\n";
}

// ---- 2. Insert the section into the page body ------------------
$body = $page->body;

if (str_contains($body, 'data-publication="4strugglemag"')) {
    echo "[body] 4strugglemag section already present — skipping insert.\n";
    exit(0);
}

$newSection = <<<'HTML'

  <!-- ============ 4strugglemag ============ -->
  <section data-publication="4strugglemag" style="margin-bottom: 56px;">
    <header style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 24px;">
      <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">4strugglemag</h2>
      <span style="font-size: 13px; opacity: 0.5;">Toronto ABCF, edited by Jaan Laaman</span>
    </header>
    <div style="display: flex; gap: 32px; align-items: flex-start; flex-wrap: wrap; margin-bottom: 24px;">
      <div style="flex: 0 0 200px;">
        <img src="/storage/archive/covers/4strugglemag-14-winter-2009.jpg" alt="4strugglemag issue #14, Winter 2009 cover" style="width: 100%; height: auto; border: 1px solid rgba(255,255,255,0.15);" />
      </div>
      <div style="flex: 1 1 320px;">
        <p style="font-size: 14px; line-height: 1.6; opacity: 0.85;">
          <em>4strugglemag</em> was an independent, non-sectarian revolutionary magazine produced by the Toronto chapter of the Anarchist Black Cross Federation and edited by anti-imperialist political prisoner Jaan Laaman. It featured writing from North American political prisoners and their supporters, focusing on issues such as justice, equality, socialism, and national-liberation struggles.
        </p>
      </div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">

      <a href="/storage/archive/4strugglemag-14.pdf" class="archive-card" style="display: block; padding: 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; text-decoration: none; color: inherit;">
        <div style="font-size: 13px; opacity: 0.6; letter-spacing: 0.08em; text-transform: uppercase;">Issue #14</div>
        <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">Winter 2009</div>
      </a>

      <!-- Add more 4strugglemag issues here following the same .archive-card structure. -->

    </div>
  </section>
HTML;

// Insert before the "Other Movement Press" section so it sits with
// the named publications. Falls back to inserting before the
// "Submit a publication" CTA if Other Movement Press isn't found.
$marker1 = '<!-- ============ Other movement press ============ -->';
$marker2 = '<!-- ============ Submit / contact ============ -->';

if (str_contains($body, $marker1)) {
    $body = str_replace($marker1, $newSection . "\n\n  " . $marker1, $body);
} elseif (str_contains($body, $marker2)) {
    $body = str_replace($marker2, $newSection . "\n\n  " . $marker2, $body);
} else {
    // No anchor found — append before the closing </section> wrapper
    $body = preg_replace('/<\/section>\s*$/', $newSection . "\n</section>\n", $body, 1);
}

$page->body = $body;
$page->save();

echo "[body] inserted 4strugglemag section into Archive page.\n";
echo "Done.\n";
