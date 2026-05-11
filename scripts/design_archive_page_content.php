<?php

declare(strict_types=1);

/**
 * Replace the placeholder Archive page body with a proper design:
 * an intro paragraph, then a grid of periodical-issue tiles
 * grouped by publication. The user can add or remove publications
 * and individual issues via the Filament admin (Pages > Archive)
 * by editing the HTML directly — the structure is just nested
 * <section>/<div> with Tailwind utility classes that already exist
 * site-wide.
 *
 * Sample entries are placeholders for common movement publications
 * the NPPC archive is likely to host (Nuclear Resister, Social
 * Anarchism, Earth First! Journal, etc.). Replace the .pdf hrefs
 * once the actual files are uploaded to /storage/archive/.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;

$page = Page::where('slug', 'archive')->first();
if (! $page) {
    echo "Page 'archive' not found. Run add_archive_page_under_learn_more.php first.\n";
    exit(1);
}

$body = <<<'HTML'
<section style="max-width: 1100px; margin: 0 auto; padding: 32px 0 64px;">
  <p style="font-size: 18px; line-height: 1.6; max-width: 720px; margin-bottom: 24px;">
    The NPPC Archive collects movement publications that have documented political-prisoner cases, state repression, and the resistance traditions that have sustained imprisoned dissidents over the last century. These periodicals were the primary record of cases the mainstream press ignored — issue numbers, prisoner addresses, court updates, and analytical writing that historians and organizers still rely on.
  </p>
  <p style="font-size: 16px; line-height: 1.6; max-width: 720px; opacity: 0.75; margin-bottom: 48px;">
    Files below are PDFs of original print issues. Click any cover or title to read or download. Where a publication is still active, we link to its current website.
  </p>

  <!-- ============ The Nuclear Resister ============ -->
  <section style="margin-bottom: 56px;">
    <header style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 24px;">
      <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">The Nuclear Resister</h2>
      <a href="http://www.nukeresister.org" target="_blank" rel="noopener" style="font-size: 13px; opacity: 0.7;">nukeresister.org &rarr;</a>
    </header>
    <p style="font-size: 14px; opacity: 0.7; max-width: 680px; margin-bottom: 24px;">
      Tucson-based newsletter founded 1980 by Jack and Felice Cohen-Joppa. The single most consistent record of US and international anti-nuclear arrests, prisoners, and trials.
    </p>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">

      <a href="/storage/archive/nuclear-resister-118.pdf" class="archive-card" style="display: block; padding: 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; text-decoration: none; color: inherit; transition: border-color 0.15s ease, background 0.15s ease;">
        <div style="font-size: 13px; opacity: 0.6; letter-spacing: 0.08em; text-transform: uppercase;">Issue #118</div>
        <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">Early 2003</div>
        <div style="font-size: 13px; opacity: 0.7; margin-top: 8px; line-height: 1.4;">Iraq War-era anti-war prisoners; Santa Cruz May 2002 protest defendants.</div>
      </a>

      <!-- Add more Nuclear Resister issues here following the same .archive-card structure. -->

    </div>
  </section>

  <!-- ============ Social Anarchism ============ -->
  <section style="margin-bottom: 56px;">
    <header style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 24px;">
      <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">Social Anarchism</h2>
      <span style="font-size: 13px; opacity: 0.5;">Baltimore, 1980-2017</span>
    </header>
    <p style="font-size: 14px; opacity: 0.7; max-width: 680px; margin-bottom: 24px;">
      Theoretical journal of Atlantic Center for Research and Education. Published the foundational Bennett 1996 essay "Political Trials and Prisoners in the United States" reproduced under Publications.
    </p>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">

      <a href="/storage/archive/social-anarchism-22.pdf" class="archive-card" style="display: block; padding: 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; text-decoration: none; color: inherit;">
        <div style="font-size: 13px; opacity: 0.6; letter-spacing: 0.08em; text-transform: uppercase;">Issue #22</div>
        <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">1996</div>
        <div style="font-size: 13px; opacity: 0.7; margin-top: 8px; line-height: 1.4;">James R. Bennett, "Political Trials and Prisoners in the United States: A Case for Political Defense."</div>
      </a>

    </div>
  </section>

  <!-- ============ Can't Jail the Spirit ============ -->
  <section style="margin-bottom: 56px;">
    <header style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 24px;">
      <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">Can't Jail the Spirit</h2>
      <span style="font-size: 13px; opacity: 0.5;">Editions 1988-2002</span>
    </header>
    <p style="font-size: 14px; opacity: 0.7; max-width: 680px; margin-bottom: 24px;">
      Biographical directory of US political prisoners published by the Crossroad Support Network and Editorial El Coquí. The standard prisoner-support reference work of the 1990s.
    </p>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">

      <a href="/storage/archive/cant-jail-the-spirit-5th-edition.pdf" class="archive-card" style="display: block; padding: 16px; border: 1px solid rgba(255,255,255,0.15); border-radius: 4px; text-decoration: none; color: inherit;">
        <div style="font-size: 13px; opacity: 0.6; letter-spacing: 0.08em; text-transform: uppercase;">5th edition</div>
        <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">2002</div>
        <div style="font-size: 13px; opacity: 0.7; margin-top: 8px; line-height: 1.4;">Final paper edition; ~80 prisoner biographies.</div>
      </a>

    </div>
  </section>

  <!-- ============ Other movement press ============ -->
  <section style="margin-bottom: 56px;">
    <header style="border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 24px;">
      <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">Other Movement Press</h2>
    </header>
    <p style="font-size: 14px; opacity: 0.7; max-width: 680px; margin-bottom: 24px;">
      Single issues and one-off PDFs from the broader movement-press archive — solidarity bulletins, court-update newsletters, and prisoner-written publications.
    </p>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
      <p style="font-style: italic; opacity: 0.5;">Add publications here.</p>
    </div>
  </section>

  <!-- ============ Submit / contact ============ -->
  <section style="margin-top: 64px; padding: 24px; border: 1px dashed rgba(255,255,255,0.3); border-radius: 4px;">
    <h3 style="font-size: 16px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px;">Submit a publication</h3>
    <p style="font-size: 14px; opacity: 0.8; line-height: 1.6;">
      If you have scans of relevant out-of-print movement publications and want them preserved in the NPPC Archive, contact us through the <a href="/contact" style="color: #fff; border-bottom: 1px solid rgba(255,255,255,0.4);">contact page</a>. We accept clean PDF scans of full issues; please include publication name, issue number, year, and a one-line description.
    </p>
  </section>
</section>

<style>
.archive-card:hover {
  border-color: rgba(255,255,255,0.5) !important;
  background: rgba(255,255,255,0.03);
}
</style>
HTML;

$page->body = $body;
$page->save();

echo "[update] Archive page body replaced with designed content.\n";
echo "URL: /{$page->slug}\n";
echo "Done.\n";
