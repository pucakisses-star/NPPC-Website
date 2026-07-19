#!/usr/bin/env bash
#
# Archive-wide duplicate audit (July 2026), triggered by the Women of
# the Ohio 7 duplicate. Every source URL registered by the site's
# archive importers was cross-checked; this script removes the
# fourteen records that failed the audit, in two classes:
#
# DUPLICATES (same document registered twice; the richer or
# collection-correct copy is kept):
#   - fag-c256-free-huey-workshop-2            (identical catalog row)
#   - fag-c256-whites-for-defense-of-newton-2  (identical catalog row)
#   - fa1057-a-guide-to-the-grand-jury-2       (identical catalog row)
#   - freedom-archives-galeano-child-lost-1990 (kept: fa1013 copy)
#   - freedom-archives-sf8-torture-methods-abu-ghraib (kept: c1109 copy)
#   - freedom-archives-c167-repression-breeds-resistance-rico-grand-jury
#                                              (kept: fa1057 copy)
#   - sprout-prairie-fire-...-scan-via-social  (kept: raf-mappe18 copy)
#   - jericho-us-political-prisoners-joint-report-upr-usa-2010-annex-23
#                                              (kept: ushrn-upr command copy)
#   - the Boston ABC Internet Archive digitization of "Surviving a
#     Grand Jury" (kept: the CrimethInc./Boston ABC digitization)
#
# MIS-LINKED (catalog rows whose pdf_url pointed at a DIFFERENT
# document's scan — the record displayed the wrong PDF, and the item's
# real scan is not held; the correctly-linked sibling record keeps the
# scan):
#   - fag-c28-alberto-rodriguez-puerto-rican-prisoner-of-war-poster
#       (showed the Parole Committee scan)
#   - fag-c1-demonstrate-april-19 (1986 flyer showed the 1985 flyer)
#   - fag-c270-stop-the-war-on-women (1993 ad showed the 1992 speech)
#   - fa1057-criminal-justice-new-developments-in-the-judicial-art-of-repression
#       (showed the "Letter to Friends" scan)
#   - nycabc-513-soledad-brothers-support-the-soledad-brothers
#       (a Hugo Pinell memorial statement showing the Soledad
#       Brothers monograph scan, which collection 29 holds correctly)
#
# The corresponding rows have been removed from the importer JSON
# files (and the Boston zine URL list) so re-running any importer will
# not recreate them.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/dedupe-archive-batch.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$slugs = [
    "fag-c256-free-huey-workshop-2",
    "fag-c256-whites-for-defense-of-newton-2",
    "fag-c28-alberto-rodriguez-puerto-rican-prisoner-of-war-poster",
    "fag-c1-demonstrate-april-19",
    "fag-c270-stop-the-war-on-women",
    "fa1057-a-guide-to-the-grand-jury-2",
    "fa1057-criminal-justice-new-developments-in-the-judicial-art-of-repression",
    "freedom-archives-galeano-child-lost-1990",
    "freedom-archives-sf8-torture-methods-abu-ghraib",
    "freedom-archives-c167-repression-breeds-resistance-rico-grand-jury",
    "sprout-prairie-fire-the-politics-of-revolutionary-anti-imperialism-1974-scan-via-social",
    "jericho-us-political-prisoners-joint-report-upr-usa-2010-annex-23",
    "nycabc-513-soledad-brothers-support-the-soledad-brothers",
];
foreach ($slugs as $slug) {
    $r = \App\Models\ArchiveRecord::where("slug", $slug)->first();
    if ($r) {
        $file = (string) $r->file;
        $r->delete();
        echo "DELETED record {$slug}\n";
        if ($file !== "" && is_file(public_path(ltrim($file, "/")))) {
            unlink(public_path(ltrim($file, "/")));
            echo "DELETED file {$file}\n";
        }
    } else {
        echo "absent (ok) {$slug}\n";
    }
}

$boston = \App\Models\ArchiveRecord::where("file", "/pdfs/abc-chapters/bostonanarchistblackcross.__surviving-a-grand-jury.pdf")->first();
if ($boston) {
    $boston->delete();
    echo "DELETED record boston IA surviving-a-grand-jury\n";
}
$f = public_path("pdfs/abc-chapters/bostonanarchistblackcross.__surviving-a-grand-jury.pdf");
if (is_file($f)) { unlink($f); echo "DELETED file abc-chapters surviving-a-grand-jury (IA copy)\n"; }

echo "Done.\n";
'

echo
echo "Done. Archive duplicates and mis-linked records removed."
