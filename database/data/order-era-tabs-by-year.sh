#!/usr/bin/env bash
#
# Reorder the "Eras" section tabs on the /topics explorer into chronological
# order (oldest to newest).
#
# This supersedes the earlier keyword-based ordering script: instead of a fixed
# list of keywords (which silently dumped any newly-added or re-titled tab at
# the end), it derives each tab's position from the FIRST year in its own
# title — e.g. "Abolitionism & the Slave Power (1850-1861)" -> 1850,
# "Japanese Incarceration & the First Smith Act Trials (1941-1945)" -> 1941.
# So any era tab added later automatically sorts into its correct earlier
# slot; nothing has to be hard-coded.
#
# Tabs whose title has no year are kept, in their existing relative order,
# after all the dated ones (nothing is dropped). Only the direct children of
# the "Eras" section are touched.
#
# Idempotent: it recomputes and rewrites sort_order each run. Run from the
# repo root:
#   bash database/data/order-era-tabs-by-year.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$eras = \App\Models\Topic::where("slug", "eras")->orWhere("title", "Eras")
    ->whereNull("parent_id")->first();
if (! $eras) { echo "No \"Eras\" section topic found; nothing to do.\n"; return; }

$children = \App\Models\Topic::where("parent_id", $eras->id)->orderBy("sort_order")->orderBy("title")->get();
if ($children->isEmpty()) { echo "The Eras section has no child tabs.\n"; return; }

// Derive a sort year from the first 4-digit year (1600-2099) in each title.
$dated = []; $undated = [];
foreach ($children as $t) {
    if (preg_match("/\b(1[6-9]\d\d|20\d\d)\b/", (string) $t->title, $m)) {
        $dated[] = [(int) $m[1], $t];
    } else {
        $undated[] = $t;
    }
}

// Stable sort dated tabs by year ascending.
usort($dated, function ($a, $b) { return $a[0] <=> $b[0]; });

$ordered = array_map(function ($p) { return $p[1]; }, $dated);
foreach ($undated as $t) { $ordered[] = $t; }

$i = 0; $changed = 0;
foreach ($ordered as $t) {
    if ((int) $t->sort_order !== $i) { $t->sort_order = $i; $t->save(); $changed++; }
    $i++;
}

echo "Reordered " . count($ordered) . " era tab(s); {$changed} sort_order value(s) changed.\n\n";
echo "New order:\n";
$n = 1;
foreach ($ordered as $t) { echo "  " . ($n++) . ". " . $t->title . "\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Era tabs reordered chronologically by title year."
