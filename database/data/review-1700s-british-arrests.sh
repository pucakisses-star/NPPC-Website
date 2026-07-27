#!/usr/bin/env bash
#
# Review the 1700s-era records and separate the people arrested by BRITISH
# authorities (Crown, royal governors, British troops, colonial administration
# acting for the Crown) from those arrested by American authorities
# (Continental or state militia, committees of safety, US marshals, federal
# courts).
#
# Every record is classified from the words in its own bio, charges, sentence
# and institution, and the MATCHED EVIDENCE IS PRINTED, so the call is visible
# rather than hidden inside the script:
#
#   BRITISH   British markers, no American markers  -> removable
#   AMERICAN  American markers present               -> kept
#   UNCLEAR   neither, or both                       -> kept, listed for review
#
# Nothing is removed without APPLY=1, and only the BRITISH group is ever
# touched. UNCLEAR records are never removed automatically -- decide those by
# hand.
#
#   bash database/data/review-1700s-british-arrests.sh                  # preview
#   APPLY=1 bash database/data/review-1700s-british-arrests.sh          # hide them (reversible)
#   APPLY=1 MODE=delete bash database/data/review-1700s-british-arrests.sh   # permanent
#
# MODE=unpublish (the default) sets under_review, which removes them from the
# public database, search, map and calendar while keeping the data editable in
# the admin. MODE=delete removes the records and their cases for good.

set -euo pipefail
cd "$(dirname "$0")/../.."

APPLY="${APPLY:-0}" MODE="${MODE:-unpublish}" php artisan tinker --execute='
use App\Models\Prisoner;

$apply = getenv("APPLY") === "1";
$mode = getenv("MODE") ?: "unpublish";

// Words that indicate who did the arresting.
$britishMarkers = [
    "british", "crown", "royal governor", "his majesty", "king george",
    "redcoat", "st. augustine", "st augustine", "castillo", "tryon",
    "parliament", "loyalist militia", "provost marshal", "royal navy",
    "colonial governor", "kings troops", "king troops",
];
$americanMarkers = [
    "continental", "american militia", "patriot", "committee of safety",
    "sons of liberty", "united states marshal", "us marshal", "federal",
    "state militia", "general assembly", "county militia", "washington",
    "whiskey", "shays", "sedition act", "congress",
];

$rows = Prisoner::withoutGlobalScopes()
    ->where("era", "like", "%1700%")
    ->with(["cases.institution"])
    ->get();

echo "1700s-era records: ".$rows->count()."\n";

$buckets = ["BRITISH" => [], "AMERICAN" => [], "UNCLEAR" => []];

foreach ($rows as $p) {
    $text = strtolower(implode(" ", array_filter([
        $p->description,
        $p->cases->pluck("charges")->implode(" "),
        $p->cases->pluck("sentence")->implode(" "),
        $p->cases->pluck("convicted")->implode(" "),
        $p->cases->map(fn ($c) => optional($c->institution)->name." ".optional($c->institution)->city." ".optional($c->institution)->state)->implode(" "),
    ])));

    $bh = array_values(array_filter($britishMarkers, fn ($w) => str_contains($text, $w)));
    $am = array_values(array_filter($americanMarkers, fn ($w) => str_contains($text, $w)));

    $verdict = ($bh && ! $am) ? "BRITISH" : (($am) ? "AMERICAN" : "UNCLEAR");
    $buckets[$verdict][] = [$p, $bh, $am];
}

foreach (["BRITISH", "AMERICAN", "UNCLEAR"] as $verdict) {
    $list = $buckets[$verdict];
    echo "\n".str_repeat("=", 72)."\n";
    echo $verdict." (".count($list).")\n";
    echo str_repeat("=", 72)."\n";
    foreach ($list as [$p, $bh, $am]) {
        echo sprintf("  %-28s [%-26s] sort=%d\n", $p->name, $p->slug, $p->sort_order);
        if ($bh) { echo "      british markers:  ".implode(", ", $bh)."\n"; }
        if ($am) { echo "      american markers: ".implode(", ", $am)."\n"; }
        $inst = $p->cases->map(fn ($c) => optional($c->institution)->name)->filter()->unique()->implode("; ");
        if ($inst) { echo "      institution:      {$inst}\n"; }
        echo "      bio:              ".substr(preg_replace("/\s+/", " ", (string) $p->description), 0, 120)."\n";
    }
}

$targets = array_map(fn ($x) => $x[0], $buckets["BRITISH"]);

if (! $apply) {
    echo "\nPREVIEW ONLY -- nothing changed.\n";
    echo count($targets)." record(s) classified BRITISH would be ".($mode === "delete" ? "DELETED" : "hidden (under_review)").".\n";
    echo "UNCLEAR records are never touched automatically -- tell me which of those to remove.\n";
    echo "Apply with:\n";
    echo "  APPLY=1 bash database/data/review-1700s-british-arrests.sh                 # reversible\n";
    echo "  APPLY=1 MODE=delete bash database/data/review-1700s-british-arrests.sh     # permanent\n";
    exit(0);
}

foreach ($targets as $p) {
    if ($mode === "delete") {
        $n = $p->cases()->count();
        $slug = $p->slug;
        $p->delete();
        echo "deleted  {$slug} (and {$n} case(s))\n";
    } else {
        $p->under_review = true;
        $p->save();
        echo "hidden   {$p->slug} (under_review = true)\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. ".count($targets)." record(s) ".($mode === "delete" ? "deleted" : "hidden").".\n";
'
