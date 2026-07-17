#!/usr/bin/env bash
#
# Backfills the website field for prisoners with verified support-campaign or
# personal sites found during the July 2026 photo-findability audit. All four
# URLs were checked live (HTTP 200) before inclusion.
#
# Fill-if-empty: never overwrites an existing website value. Idempotent.
#
# Run from the repo root:  bash database/data/backfill-support-websites.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$sites = [
    "keith-lamar"       => "https://www.keithlamar.org/",
    "priscilla-grim"    => "https://supportpriscilla.org/",
    "brian-willson"     => "https://www.brianwillson.com/",
    "marissa-alexander" => "https://www.freemarissanow.org/",
    // Defense/support pages that photo batches were sourced from (each URL
    // verified live): Jericho Movement per-prisoner profiles and the Free
    // Zulu campaign.
    "robert-seth-hayes"   => "https://www.thejerichomovement.com/profile/robert-seth-hayes",
    "kazi-toure"          => "https://www.thejerichomovement.com/profile/kazi-toure",
    "masai-ehehosi"       => "https://www.thejerichomovement.com/profile/masai-ehehosi",
    "kenneth-whitmore"    => "https://freezulu.org/",
];
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
// --- Social accounts (fill-if-empty; campaign accounts verified via the
// --- campaigns own materials during the July 2026 audit) -----------------
$socials = [
    "keith-lamar" => [
        "twitter"   => "https://twitter.com/FREEKeithLaMar",
        "facebook"  => "https://www.facebook.com/justiceforkeithlamar/",
        "instagram" => "https://www.instagram.com/justiceforkeithlamar/",
    ],
    "victor-puertas" => [
        "instagram" => "https://www.instagram.com/victordoors/",
    ],
];
foreach ($socials as $slug => $fields) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    foreach ($fields as $f => $url) {
        if (! empty($p->{$f})) { continue; }
        $p->{$f} = $url;
        $set++;
        echo "SET  {$slug}.{$f} -> {$url}\n";
    }
    $p->save();
}

// Edward Snowden: his X account was stored in the facebook field — move it.
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "edward-snowden")->first();
if ($p && str_contains((string) $p->facebook, "x.com/Snowden") && empty($p->twitter)) {
    $p->twitter = $p->facebook;
    $p->facebook = null;
    $p->save();
    $set++;
    echo "MOVED edward-snowden facebook -> twitter ({$p->twitter})\n";
}

if ($set > 0) {
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "Done. {$set} link(s) set.\n";
'
