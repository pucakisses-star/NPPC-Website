#!/usr/bin/env bash
#
# Part 2 of the July 2026 archive duplicate audit: a byte-level
# checksum sweep of all ~1,000 vendored PDFs found 16 identical-content
# pairs. This script removes the ArchiveRecords for the dropped copy of
# each pair (the files themselves are deleted from the repo in the same
# commit, so `git pull` removes them from disk; this script also
# unlinks any leftover server-side copies that were downloaded rather
# than tracked in git).
#
# Kept copy → dropped copy:
#   azine-library zines            ← 4 Boston ABC distro re-downloads
#   south-chicago-abc zines        ← 2 Boston ABC distro re-downloads
#   political-prisoner-library Arm The Spirit Aug 1979 ← fa-c267 self-host copy
#   political-prisoner-library Collected Works of the BLA vol 1 ← ia-pp-may-2026-batch2 copy
#   ia-self-host libertad no-7 / no-8 alt scans ← the same files under "pfoc-alt" names
#   abc/ NYC ABC Illustrated Guide v19.3 (April 2026) ← nyc-abc listing copy AND the abcf batch row
#   pfoc-breakthrough Break! 23 (Fall 1992) ← political-prisoner-library copy
#   books COINTELPRO Papers / Let Freedom Ring ← political-prisoner-library copies
#
# FBI FOIA anomaly (flagged, deduped): fbi-malcolm-x-part-33 was
# byte-identical to part-20, and fbi-martin-luther-king-jr-part-2 to
# part-1 — one part of each pair was a mis-download, and vault.fbi.gov
# blocks automated re-fetching. The part-33 / part-2 records are
# removed; re-add them by downloading the true parts from
# https://vault.fbi.gov in a browser.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/dedupe-archive-files.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$paths = [
    "/pdfs/abc-chapters/bostonanarchistblackcross.__black-anarchism-final.pdf",
    "/pdfs/abc-chapters/bostonanarchistblackcross.__black_hole_final.pdf",
    "/pdfs/abc-chapters/bostonanarchistblackcross.__fjatwood_cup_12final.pdf",
    "/pdfs/abc-chapters/bostonanarchistblackcross.__political_prisoners_and_black_liberation.pdf",
    "/pdfs/abc-chapters/bostonanarchistblackcross.__black-peoples-prison-survival-guide-final.pdf",
    "/pdfs/abc-chapters/bostonanarchistblackcross.__women_in_prison-final.pdf",
    "/pdfs/external-imports/fa-c267-arm-the-spirit-no-4.pdf",
    "/pdfs/external-imports/fbi-malcolm-x-part-33.pdf",
    "/pdfs/external-imports/fbi-martin-luther-king-jr-part-2.pdf",
    "/pdfs/ia-pp-may-2026-batch2/collected-works-of-bla-vol-1.pdf",
    "/pdfs/ia-self-host/libertad-may-1985-pfoc-alt.pdf",
    "/pdfs/ia-self-host/libertad-july-august-1987-pfoc-alt.pdf",
    "/pdfs/nyc-abc/nycabc_polprislisting_april-2026_legal.pdf",
    "/pdfs/political-prisoner-library/break-23-fall-1992.pdf",
    "/pdfs/political-prisoner-library/cointelpro-papers-churchill.pdf",
    "/pdfs/political-prisoner-library/let-freedom-ring-meyer.pdf",
    "/pdfs/abcf/abcf-illustrated-guide-v19-3-april-2026.pdf",
];
foreach ($paths as $path) {
    foreach (\App\Models\ArchiveRecord::where("file", $path)->get() as $r) {
        $r->delete();
        echo "DELETED record {$r->slug} ({$path})\n";
    }
    $abs = public_path(ltrim($path, "/"));
    if (is_file($abs)) { unlink($abs); echo "DELETED file {$path}\n"; }
}

foreach (["abcf-illustrated-guide-v19-3-april-2026", "nyc-abc-pppow-listing-april-2026"] as $slug) {
    $r = \App\Models\ArchiveRecord::where("slug", $slug)->first();
    if ($r) { $r->delete(); echo "DELETED record {$slug}\n"; }
}

echo "Done.\n";
'

echo
echo "Done. Byte-identical archive duplicates removed."
