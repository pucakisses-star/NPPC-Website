#!/usr/bin/env bash
#
# Mines Revolutionary Cause vol. 2 no. 1 (March 1977, August Twenty-Ninth
# Movement M-L) — also registered in the site archive by
# archive:add-revolutionary-cause-1977.
#
# Already present: Joann Little, Assata Shakur, Yvonne Wanrow, Paul
# Skyhorse, Richard Mohawk. Not addable: the St. Luke's 23 (Chicago
# hospital-protest defendants, tried February 1977 — all unnamed in the
# issue) and the 40+ ILWU strikers forced into guilty pleas (unnamed).
# Out of scope: the Arab prisoners held by Israel and the southern-Africa
# stories.
#
#  1. Adds Ben Lenard — the Black International Harvester worker framed
#     and jailed by Chicago police on January 31, 1977, in the racist
#     frame-up tradition the site documents (McGee, Thompson).
#  2. Appends the issue's custody-conditions reporting to the Skyhorse
#     and Mohawk records (solitary in separate prisons, beatings, the
#     shackled punishment cell, forced drugging, courtroom "subduing").
#
# Idempotent: prisoner:add refuses duplicates; appends are marker-guarded.
#
# Run from the repo root:  bash database/data/add-revolutionary-cause-1977.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Ben Lenard","first_name":"Ben","last_name":"Lenard","description":"Ben Lenard was a Black worker at International Harvester in Chicago who, driving home from work on January 31, 1977, was struck by another car. When police finally arrived, the other driver — a white woman with no license or insurance — claimed Lenard had assaulted her; without any investigation the officers handcuffed him, beat him nearly beyond recognition in the squad car, beat him again at the jail, stripped him, threw him into a cell, opened the windows in zero-degree weather and doused him with cold water. His family was able to bail him out only after being told he was being \"processed,\" and took him to a hospital. Reconstructed from Revolutionary Cause (August Twenty-Ninth Movement M-L), March 1977, which reported the case as part of the pattern of racist police frame-ups; the disposition of the charge against him has not been located.","state":"Illinois","race":"Black","gender":"Male","era":"1970s","released":true,"cases":[{"charges":"Assault — a white driver'"'"'s uninvestigated claim after a traffic collision, described by the movement press as a racist frame-up","arrest_date":"1977-01-31","incarceration_date":"1977-01-31","convicted":"Disposition not located — beaten in custody and released on bail; hospitalized after his release"}]}' || true

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$appendOnce = function ($p, string $marker, string $paragraph): void {
    if (! $p || str_contains((string) $p->description, $marker)) { return; }
    $p->description = trim((string) $p->description) . "\n\n" . $paragraph;
    $p->save();
    echo "DESC {$p->slug}\n";
};

$appendOnce($find("paul-skyhorse"), "punishment cell", "Movement reporting during the pretrial years (Revolutionary Cause, March 1977) described the two men held in solitary confinement in separate prisons and frequently beaten by guards: Skyhorse was beaten, stripped, and thrown shackled into a punishment cell with an inch of water on the floor, and both men were forcibly given \"prison pacifier\" drugs they later had to kick. In court they were \"subdued\" — beaten, choked and dragged out — when they tried to speak for themselves. The three original suspects found at the scene had been granted immunity as prosecution witnesses, and a jail witness reported one of them saying she would testify as wanted in order to get her immunity.");

$appendOnce($find("richard-mohawk"), "no longer straighten", "Movement reporting during the pretrial years (Revolutionary Cause, March 1977) reported that Mohawk could no longer straighten one arm after a beating in which he was chained to the bars for days with his arms cuffed up behind his back, and that both men were held in solitary in separate prisons, forcibly drugged, and \"subdued\" — beaten, choked and dragged out — in court when they tried to speak for themselves.");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

php artisan archive:add-revolutionary-cause-1977

echo
echo "Done. Revolutionary Cause March 1977 applied."
