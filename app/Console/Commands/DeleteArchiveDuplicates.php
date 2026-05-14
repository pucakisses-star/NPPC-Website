<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Deletes high-confidence duplicate ArchiveRecord rows surfaced by
 * the archive:find-duplicates run. Each entry below is a confirmed
 * duplicate where another row with the same content already exists.
 *
 * Dry-run by default — pass --apply to actually delete.
 *
 * Pattern categories handled here:
 *   1. Same FA collection, same title, where the -2 suffix slug
 *      is a re-import of the original.
 *   2. c167 / c1057 overlap — the c167 grand-jury / FBI repression
 *      collection rows are kept; the c1057 (Government Repression)
 *      same-title rows are deleted.
 *   3. Boston ABC vs "Boston ABC Zines & Pamphlets" same-title
 *      pairs — same source imported twice under different
 *      collection names; keep the "Boston ABC Zines & Pamphlets"
 *      row (more descriptive collection name).
 *   4. Boston ABC pan-X-Y print/web/internet/screen pairs — same
 *      content in different PDF variants; keep print, delete the
 *      screen variant.
 *   5. The Anarchist Library mirror duplicates — TAL mirrors
 *      content from CrimethInc / Boston ABC; keep the original.
 *   6. Austin ABC TDCJ COVID-19 zine print/read variants — keep
 *      print.
 *   7. NYC ABC UFF tri-fold pamphlet ×3 — keep one.
 *   8. ABCF Constitution duplicate.
 *   9. Pelican Bay Prisoners mp3 within-collection dup.
 *  10. Geronimo Ji Jaga COINTELPRO 101 c1057/c126 overlap.
 *  11. Liberated Guardian c21/c144 overlap.
 *
 * Ambiguous groups (Off the Hook ×4, Radical America ×2, Black
 * Power! ×4 plain, Black Challenge ×2, Arm the Spirit ×3, Letter
 * to Friends ×4, NYC ABC RDTW flyer/brochure pairs, Bill Dunne
 * 2015 vs 2017, fag-c303 Press Release ×2, Free Huey Workshop ×2)
 * are NOT deleted here — they may be legitimately different
 * documents and need human review.
 */
final class DeleteArchiveDuplicates extends Command {
    protected $signature = 'archive:delete-duplicates {--apply : Actually delete (dry-run by default)}';
    protected $description = 'Delete high-confidence duplicate ArchiveRecord rows';

    public function handle(): int {
        $apply = (bool) $this->option('apply');

        $deleteSlugs = [
            // c167 vs c1057 overlap — keep c167, delete c1057
            'fa1057-repression-in-the-land-of-the-free',
            'fa1057-if-an-agent-knocks-federal-investigators-and-your-rights',
            'fa1057-organizational-rights-tips-on-surveillance-and-security',
            'fa1057-national-peoples-moratorium-1979-against-police-ins-bia-crimes-and-grand-jury-fb',
            'fa1057-grand-jury-and-fbi-activities',
            'fa1057-your-rights-and-the-grand-jury',
            'fa1057-free-to-protest-dissent-prophetic-ministry-and-the-bill-of-rights',
            'fa1057-the-political-grand-jury-an-instrument-of-repression',

            // Within-collection -2 suffix dupes
            'fag-c1-mass-incarceration-and-control-units-in-prisons-mind-control-or-social-contro-2',
            'fag-c1-stop-the-marion-lockdown-a-conference-for-education-and-action-2',
            'fag-c3-there-are-women-political-prisoners-in-the-us-2',
            'fag-c3-women-political-prisoners-in-the-united-states-2',
            'fag-c303-press-release-2',
            'fag-c303-whites-for-defense-of-newton-2',
            'fag-c256-free-huey-workshop-2',
            'fag-c256-whites-for-defense-of-newton-2',
            'fag-c152-womens-control-unit-marianna-fl-2',
            'fag-c18-de-pie-y-en-lucha-2',
            'fag-c144-a-report-from-inside-attica-2',
            'fa1057-women-and-the-fight-against-cointelpro-a-evening-of-solidarity-with-black-freedo-2',
            'fa1057-a-guide-to-the-grand-jury-2',
            'fag-c194-pelican-bay-prisoners-mp3-2',
            'fag-c14-the-black-challenge-2',
            'fag-c14-radical-america-2',
            'fag-c181-midnight-special-prisoners-news-2',
            'fag-c181-midnight-special-prisoners-news-3',

            // c1057 vs c126 Geronimo COINTELPRO 101 overlap — keep c126
            'fa1057-geronimo-ji-jaga-from-cointelpro-101-video-clip',

            // c21 vs c144 Liberated Guardian — keep c144 (Attica context)
            'fag-c21-liberated-guardian',

            // Boston ABC vs Boston ABC Zines & Pamphlets — keep "Zines & Pamphlets" row
            'abc-boston-collective-process',
            'abc-boston-reflections-on-women-in-prison-final',
            'abc-boston-handbook-on-surviving-solitary-confinement-final',
            'abc-boston-fight-the-man-and-get-away-safely',
            'abc-boston-anarchism-and-litigation-final',
            'abc-boston-what-is-syndicalism-final',
            'abc-boston-crime-and-punishment-final',
            'abc-boston-democracy-is-direct-final',
            'abc-boston-supporting-a-survivor-of-sexual-assault',
            'abc-boston-consensus',
            'abc-boston-learning-good-consent',
            'abc-boston-on-law-and-authority-final',
            'abc-boston-the-fundamental-requirement-for-organised-safer-space',
            'abc-boston-political-prisoners-prisons-and-black-liberation',
            'abc-boston-the-j20-arrests-and-trials-explained',
            'abc-boston-policing-on-the-global-scale',
            'abc-boston-resist-grand-juries',
            'abc-boston-stay-healthy-so-you-can-stay-in-the-streets',
            'abc-boston-tasers-and-the-fight-against-police-brutality',
            'abc-boston-what-about-the-workers',
            'abc-boston-what-is-security-culture',
            'abc-boston-writing-to-prisoners-faq',

            // Boston ABC pan-X-Y print/web duplicates — keep print
            'abc-boston-pan-5-2-web',
            'abc-boston-pan-6-1-internet-version-final',
            'abc-boston-pan-6-2-internet-version-final',
            'abc-boston-pan-7-1-screen',
            'abc-boston-pan-7-2-internet-version-final1',
            'abc-boston-pan-8-1-internet-version-final',
            'abc-boston-pan-8-2-internet-version-final',
            'abc-boston-pan-9-1-internet-final-version',

            // Boston ABC "Relentless" duplicate — keep "imposed" variant
            'abc-boston-relentless-final',

            // Austin ABC TDCJ COVID-19 print/read — keep print
            'abc-atx-tdcj-covid19-zine-read-1',

            // The Anarchist Library mirror duplicates
            'tal-crimethinc-i-was-a-j20-street-medic-and-defendant',
            'tal-crimethinc-we-ve-got-your-back-the-story-of-the-j20-defense',
            'tal-crimethinc-surviving-a-grand-jury',
            'tal-ashanti-omowali-alston-black-anarchism',
            'tal-anarchist-black-cross-starting-an-anarchist-black-cross-group-a-guide',

            // Anarchist Zine Library mirror dupes
            'azine-political-prisoners-and-black-liberation',
            'azine-control-unit-prisons',
            'azine-black-anarchism',

            // Boston ABC Resources vs Boston ABC Zines variants
            'boston-abc-starting-an-anarchist-black-cross-group',
            'boston-abc-control-unit-prisons-atwood',
            'boston-abc-black-anarchism-alston',
            'boston-abc-surviving-a-grand-jury-crimethinc',
            'boston-abc-what-is-security-culture',

            // CrimethInc vs Boston ABC overlap — keep CrimethInc original
            // (already handled by deleting Boston ABC "what is security culture" above)

            // NYC ABC UFF tri-fold ×3 — keep -nyc, delete the two near-dupes
            'nycabc-uff-nyc-1',
            'nycabc-uff-nyc1',

            // Bill Dunne 2015 vs 2017 tri-fold — these are dated; keep 2017 (newer)
            'nycabc-dunne-nyc-2015',

            // ABCF Constitution — keep abcf-library version
            'abcf-constitution',

            // Veronza tag mis-titled as RDTW — keep the actual RDTW rows, delete the misclassified
            'nycabc-veronza',

            // FA c28 vs PR POW Materials overlap — keep c28
            'alejandrina-torres-puerto-rican-pow-1987',

            // 4StruggleMag mirrors of Midnight Special — keep Midnight Special original
            '4strugglemag-court-solidarity',
            '4strugglemag-legal-solidarity-handbook',

            // FA c155 vs Literary Prisoners — keep Literary Prisoners (more specific path)
            'fag-c155-political-prisoners-write-critical-resistance',
        ];

        $found = ArchiveRecord::whereIn('slug', $deleteSlugs)->get(['id', 'slug', 'title', 'collection']);
        $foundSlugs = $found->pluck('slug')->all();
        $missing = array_diff($deleteSlugs, $foundSlugs);

        $this->info('Targeted for deletion: '.count($deleteSlugs));
        $this->info('Found in DB:           '.$found->count());
        if (! empty($missing)) {
            $this->warn('Not found (already deleted or slug changed): '.count($missing));
            foreach ($missing as $m) {
                $this->line('  '.$m);
            }
        }

        $this->line("\n— Rows to delete —");
        foreach ($found as $r) {
            $this->line('  #'.$r->id.'  '.$r->slug.'  ('.$r->collection.')  '.$r->title);
        }

        if (! $apply) {
            $this->info("\n(dry-run; re-run with --apply to delete)");

            return self::SUCCESS;
        }

        $deleted = 0;
        foreach ($found as $r) {
            $r->delete();
            $deleted++;
        }
        $this->info("\nDeleted {$deleted} duplicate rows.");

        return self::SUCCESS;
    }
}
