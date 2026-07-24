#!/usr/bin/env bash
#
# Clean prisoner descriptions contaminated with an imported footnote/citation
# apparatus (the same shape as Fred Suttle's): a genuine short biography followed
# by a "NOTES" section and a long dump of case citations ("... (Calif. App. 1925)"),
# archive references ("ACLU Archives", "Industrial Worker,", "Labor Defender,"),
# and boilerplate. These records were imported from one scholarly source about
# IWW / criminal-syndicalism prisoners and share the same fingerprint.
#
# Auto-fix (high confidence): any description with a "NOTES" heading that is
# followed by citation markers is truncated at "NOTES", keeping only the real
# biography before it.
#
# Report only (not modified): a long, citation-heavy description that has NO clean
# "NOTES" boundary is a different shape -- it is surfaced for manual review rather
# than truncated by guesswork.
#
# Idempotent: once truncated, the kept text no longer carries the fingerprint, so
# re-runs skip it. Prints kept/dropped previews so the result is auditable.
# Run from the repo root:
#   bash database/data/clean-contaminated-bios.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// Fingerprint of the citation apparatus.
$markerRe = "/(Calif\\. App\\.|Cal\\. App\\.|\\bP\\. \\d{2,3}\\b|Industrial Worker,|Labor Defender,|ACLU Archives|N\\.?C\\.?L\\.?B|People v\\. |United States v\\.|Story of the Imperial Valley|Erickson Speaks from Prison|New Solidarity,|Criminal Syndicalism)/";

$fixed = 0; $review = 0;
$reviewList = [];

foreach (Prisoner::withoutGlobalScopes()->whereNotNull("description")->get() as $p) {
    $desc = (string) $p->description;
    if (strlen($desc) < 400) { continue; }

    $count = preg_match_all($markerRe, $desc, $mAll);

    // Strategy A: a NOTES heading followed by citation markers -> auto-cut.
    $cut = null;
    if (preg_match("/\\bNOTES\\b/", $desc, $mm, PREG_OFFSET_CAPTURE)) {
        $pos = (int) $mm[0][1];
        if (preg_match($markerRe, substr($desc, $pos)) === 1) {
            $cut = $pos;
        }
    }

    if ($cut === null) {
        // Strategy B: long + citation-heavy but no clean NOTES boundary -> report.
        if ($count >= 12) {
            $review++;
            $reviewList[] = "  ".$p->slug." | ".$p->name." | ".strlen($desc)." chars, ".$count." citation markers, no NOTES boundary";
        }
        continue;
    }

    $kept = rtrim(substr($desc, 0, $cut));
    $kept = rtrim($kept, " \t\n\r\0\x0B,;:[(-");

    if (strlen($kept) < 40) {
        $review++;
        $reviewList[] = "  ".$p->slug." | ".$p->name." | NOTES boundary leaves only ".strlen($kept)." chars -- manual review";
        continue;
    }

    $p->description = $kept;
    $p->save();
    $fixed++;

    $keptPreview = str_replace(["\n", "\r"], " ", substr($kept, 0, 280));
    $dropPreview = str_replace(["\n", "\r"], " ", substr(ltrim(substr($desc, $cut)), 0, 140));
    echo "FIXED ".$p->slug." | ".$p->name." | ".strlen($desc)." -> ".strlen($kept)." chars (".$count." citation markers)\n";
    echo "   KEPT: ".$keptPreview.(strlen($kept) > 280 ? " ..." : "")."\n";
    echo "   DROP: ".$dropPreview." ...\n";
}

echo "\n=== Summary ===\n";
echo "Fixed (NOTES-truncated): {$fixed}\n";
echo "Reported for manual review: {$review}\n";
if ($reviewList) {
    echo "\nManual review (long + citation-heavy, no clean NOTES boundary -- not modified):\n";
    echo implode("\n", $reviewList)."\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Contaminated bios cleaned."
