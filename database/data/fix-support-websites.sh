#!/usr/bin/env bash
#
# Support-website audit (July 2026), full pass over all 7,100+ records:
#
#  1. Merges four newly found duplicate-record clusters (surfaced while
#     checking which famous political prisoners lacked websites):
#     H. Rap Brown / Imam Jamil Al-Amin (x3), Xinachtli / Alvaro Luna
#     Hernandez (x2), Oso Blanco / Byron Chubbuck (x2), and Meagan Morris /
#     Bradford Morris (x2, canonical is the name she goes by).
#  2. Normalizes 33 malformed website values: markdown link syntax, angle
#     brackets, multi-URL blobs, "Obituary:" prefixes, schemeless domains
#     (which rendered as broken relative links), and emails stored as
#     websites (cleared).
#  3. Fills verified missing support sites (fill-if-empty, every URL checked
#     live): Rev. Joy Powell, Ian Freeman, Christopher Trotter (pendleton2.com,
#     already on his co-defendant John Cole), Michael Kimble, and the
#     Prairieland defendants' committee site on all 17 defendant records.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/fix-support-websites.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=jamil-abdullah-al-amin,alvaro-hernandez,byron-chubbuck,meagan-morris --apply

php artisan tinker --execute='
// --- 1. Normalize every malformed website value -------------------------
$fixed = 0;
foreach (\App\Models\Prisoner::withoutGlobalScopes()->whereNotNull("website")->where("website", "!=", "")->get() as $p) {
    $orig = $p->website;
    $w = trim($orig);
    if (preg_match("/\[(.*?)\]\((.*?)\)/", $w, $m)) { $w = $m[2]; }          // markdown [text](url)
    $w = trim($w, "<> \t\n\r");
    if (preg_match("~https?://[^\s<>\)\]]+~", $w, $m)) {
        $w = $m[0];                                                            // first real URL in the blob
    } elseif (preg_match("~^(www\.)?[a-z0-9][a-z0-9.-]*\.[a-z]{2,}(/\S*)?$~i", $w)) {
        $w = "https://" . $w;                                                  // schemeless domain
    } elseif (str_contains($w, "@")) {
        $w = null;                                                             // an email is not a website
    }
    if ($w !== $orig) {
        $p->website = $w;
        $p->save();
        $fixed++;
        echo "NORM {$p->slug}: {$orig}  ->  " . ($w ?? "(cleared)") . "\n";
    }
}
echo "Normalized {$fixed} website value(s).\n\n";

// --- 2. Fill verified missing support sites (fill-if-empty) --------------
$sites = [
    "rev-joy-powell"      => "https://freejoypowell.org/",
    "ian-freeman"         => "https://freeiannow.org/",
    "christopher-trotter" => "https://www.pendleton2.com/",
    "michael-kimble"      => "https://anarchylive.noblogs.org/",
];
foreach ([
    "benjamin-song", "cameron-arnold", "daniel-sanchez-estrada", "elizabeth-soto",
    "ines-soto", "john-thomas", "joy-gibson", "lynette-sharp", "maricela-rueda",
    "meagan-morris", "nathan-baumann", "rebecca-morgan", "savanna-batten",
    "seth-sikes", "susan-kent", "zachary-evetts",
] as $slug) {
    $sites[$slug] = "https://prairielanddefendants.com/";
}
$set = 0;
foreach ($sites as $slug => $url) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    if (! empty($p->website)) { echo "SKIP {$slug} (already has {$p->website})\n"; continue; }
    $p->website = $url;
    $p->save();
    $set++;
    echo "SET  {$slug} -> {$url}\n";
}
echo "Set {$set} website(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
'

echo
echo "Done. Support-website audit fixes applied."
