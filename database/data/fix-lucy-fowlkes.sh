#!/usr/bin/env bash
#
# Lucy Fowlkes -- portrait, and released on bond July 14, 2026.
#
# The record still had her IN CUSTODY with no release date, so the
# imprisonment counter was climbing daily (205 and counting). She was
# RELEASED ON BOND ON JULY 14, 2026 -- pretrial release, not the end of
# the case, which remains pending.
#
#   Jan  5, 2026   arrested at her home in Weatherford, Texas -- the
#                  nineteenth Prairieland defendant
#   Jun 11, 2026   bond reduced to 150,000 dollars, from the 5,000,000
#                  then 10,000,000 previously reported
#   Jul 14, 2026   released on bond
#                  = 190 days in the Johnson County Jail
#
# The counter drops from a running number to a fixed 190, which is
# correct: pretrial custody ended, the prosecution did not.
#
# THE PORTRAIT comes from the support campaign site
# (prairielanddefendants.com), cropped from a full-length outdoor photo
# to a 492x700 headshot.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-lucy-fowlkes.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "lucy-fowlkes")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: lucy-fowlkes\n";
    exit(1);
}

$p->first_name = "Lucy";
$p->last_name = "Fowlkes";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = true;
$p->description = "Lucy Fowlkes, of Weatherford, Texas, was arrested at her home on January 5, 2026 — the nineteenth Prairieland defendant — in an operation involving FBI agents although she faces only state charges. Not alleged to have participated in the July 4, 2025 noise demonstration at the Prairieland ICE detention center, she was charged with hindering the prosecution of terrorism after refusing to assist prosecutors. Her bond was reported at 5,000,000 and then 10,000,000 dollars, reduced to 150,000 dollars on June 11, 2026, and she was released on bond on July 14, 2026 after 190 days in the Johnson County Jail. Her state case remains pending.";
$p->save();

$src = database_path("data/photos/nonfree/lucy-fowlkes.jpg");
if (is_file($src)) {
    File::ensureDirectoryExists(storage_path("app/public/prisoners"));
    $dest = "prisoners/lucy-fowlkes.jpg";
    File::copy($src, storage_path("app/public/".$dest), true);
    touch(storage_path("app/public/".$dest));
    $p->photo = $dest;
    $p->save();
} else {
    echo "  photo file missing: {$src}\n";
}

$jail = Institution::firstOrCreate(
    ["name" => "Johnson County Jail"],
    ["city" => "Cleburne", "state" => "Texas"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $jail->id;
$case->sentence = "Pretrial detention only — the case is pending, and this row records custody rather than a sentence. Held from her arrest on January 5, 2026; bond, first reported at five million and then ten million dollars, was reduced to 150,000 dollars on June 11, 2026, and she was RELEASED ON BOND on July 14, 2026 after 190 days in the Johnson County Jail. Release on bond is pretrial release, not the end of the case.";
$case->setPartialDate("release_date", 2026, 7, 14);
$case->save();

$p->refresh()->load("cases");
$c = $p->cases->first();
echo "Lucy Fowlkes  [{$p->slug}]\n";
echo "  in_custody ".var_export($p->in_custody, true)."   released ".var_export($p->released, true)."   awaiting_trial ".var_export($p->awaiting_trial, true)."\n";
echo "  incarcerated ".(optional($c->incarceration_date)->toDateString() ?: "-")."   released ".(optional($c->release_date)->toDateString() ?: "-")."\n";
echo "  imprisoned_for_days = ".($c->imprisoned_for_days ?? "null")."  (expect 190, was a running counter)\n";
echo "  photo ".($p->photo ?: "(none)")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
