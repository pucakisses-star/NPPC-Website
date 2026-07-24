#!/usr/bin/env bash
#
# Foley Square defense lawyers: set Richard Gladstein's bio, fill the custody
# dates of all five defense lawyers (all already in the database), and place the
# five consecutively in the prisoner sort order (WITHOUT adding any affiliation
# tag), anchored at Gladstein's position.
#
# All five voluntarily surrendered April 24, 1952 after the Supreme Court upheld
# their contempt convictions:
#   Richard Gladstein  Apr 24 - Sep 23, 1952 (6 mo)   FCI Texarkana, TX   [verified]
#   Harry Sacher       Apr 24 - Sep 23, 1952 (6 mo)   FCI Ashland, KY     [probable]
#   Abraham Isserman   Apr 24 - Aug 23, 1952 (4 mo)   FCI Danbury, CT     [reconstructed]
#   George Crockett    Apr 24 - Aug 23, 1952 (4 mo)   FCI Ashland, KY     [reconstructed]
#   Louis McCabe       Apr 24 - May 23, 1952 (30 d)   Fed. House of Det., NYC [reconstructed]
#
# NOTE: the grouping is a direct sort_order placement, not an affiliation. A
# future run of the chronological sort (which clusters by affiliation) would
# scatter them again, since there is no shared tag holding them together.
#
# Idempotent. Run from the repo root:
#   bash database/data/foley-square-lawyers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
// [slug, relY, relM, relD, sentence, instName, instCity, instState]
$data = [
    ["richard-gladstein", 1952, 9, 23, "6 months (served 152 days; released early via statutory good-time)", "Federal Correctional Institution, Texarkana", "Texarkana", "Texas"],
    ["harry-sacher",      1952, 9, 23, "6 months (probable release September 23, 1952 by statutory good-time; not independently verified)", "Federal Correctional Institution, Ashland", "Ashland", "Kentucky"],
    ["abraham-isserman",  1952, 8, 23, "4 months (reconstructed full-term release August 23, 1952)", "Federal Correctional Institution, Danbury", "Danbury", "Connecticut"],
    ["george-crockett",   1952, 8, 23, "4 months (reconstructed full-term release August 23, 1952)", "Federal Correctional Institution, Ashland", "Ashland", "Kentucky"],
    ["louis-mccabe",      1952, 5, 23, "30 days (reconstructed full-term release May 23, 1952)", "Federal House of Detention, West Street, New York City", "New York", "New York"],
];
$order = ["richard-gladstein", "harry-sacher", "abraham-isserman", "george-crockett", "louis-mccabe"];

$done = 0;
foreach ($data as $d) {
    [$slug, $ry, $rm, $rd, $sentence, $instName, $instCity, $instState] = $d;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }
    $p->ideologies = array_values(array_unique(array_merge((array) $p->ideologies, ["Civil Liberties"])));
    if (empty($p->era)) { $p->era = "1950s"; }
    $p->in_custody = false; $p->released = true;
    $p->save();

    $inst = \App\Models\Institution::firstOrCreate(["name" => $instName], ["city" => $instCity, "state" => $instState]);
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->institution_id = $inst->id;
    $c->setPartialDate("incarceration_date", 1952, 4, 24);
    $c->setPartialDate("release_date", $ry, $rm, $rd);
    $c->sentence = $sentence;
    if (empty($c->convicted)) { $c->convicted = "Yes — criminal contempt of court (Foley Square Smith Act trial defense counsel); conviction upheld by the Supreme Court (Sacher v. United States, 1952)"; }
    $c->save();
    echo "  {$slug}: {$c->imprisoned_for_days} days\n"; $done++;
}

// Richard Gladstein bio (provided text).
$g = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "richard-gladstein")->first();
if ($g) {
    $g->description = trim(file_get_contents(base_path("database/data/gladstein-bio.txt")));
    $g->save();
    echo "  gladstein bio set\n";
}

// --- Place the five consecutively in sort_order, anchored at Gladstein. ---
$anchor = (int) \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "richard-gladstein")->value("sort_order");
// open five slots after the anchor by pushing everything after it down (excluding the group)
\App\Models\Prisoner::withoutGlobalScopes()
    ->where("sort_order", ">", $anchor)
    ->whereNotIn("slug", $order)
    ->update(["sort_order" => \Illuminate\Support\Facades\DB::raw("sort_order + 5")]);
$i = 0;
foreach ($order as $slug) {
    \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->update(["sort_order" => $anchor + $i]);
    $i++;
}
echo "  placed 5 lawyers consecutively at sort_order {$anchor}..".($anchor + 4)."\n";

echo "\nUpdated {$done} Foley Square lawyer(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Foley Square defense lawyers updated and grouped in sort order."
