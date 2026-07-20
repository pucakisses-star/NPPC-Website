#!/usr/bin/env bash
#
# Full names for three of the five Detroit workers jailed in 1930 for
# holding a workers' meeting (photographed surname-only in the July 1930
# Labor Defender):
#
#   - "Coperean"  -> Stefan "Steve" Cojerean (Labor Defender misprint;
#                    the May 22, 1930 Daily Worker names Powers, Raymond
#                    and Cojerean arrested together at a Briggs
#                    factory-gate meeting)
#   - "Raymond"   -> Philip Aaron Raymond, national organizer of the
#                    Auto Workers Union (b. New York City, Feb 4, 1899)
#   - "Powers"    -> George Edward Powers, secretary of the TUUL's
#                    Michigan District (b. Boston, Feb 15, 1892)
#
# Conn and Caravas remain surname-only: no period source yet connects a
# first name to either, and "Conn" may itself be a misspelling. Their
# records are left untouched.
#
# Slugs are intentionally NOT changed, so existing URLs keep working.
# Idempotent (marker-guarded).
#
# Run from the repo root:  bash database/data/fix-detroit-1930-names.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$find = fn ($slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

// ---- Coperean -> Stefan "Steve" Cojerean ----
$p = $find("coperean-detroit-1930");
if ($p && ! str_contains((string) $p->description, "Cojerean")) {
    $p->name = "Stefan Cojerean";
    $p->first_name = "Stefan";
    $p->last_name = "Cojerean";
    $p->aka = "Steve Cojerean; \"Coperean\" (as printed in Labor Defender)";
    $p->description = "Stefan \"Steve\" Cojerean was one of five Detroit workers sentenced to 90 days in jail for holding a workers'"'"' meeting, pictured in the July 1930 Labor Defender — which printed his surname as \"Coperean.\" A May 22, 1930 Daily Worker report names Powers, Raymond and Cojerean as arrested together at a factory-gate meeting outside the Briggs plant, and other period records give his name as Stefan (Steve) Cojerean of Detroit, a Daily Worker supporter as early as 1927. The Labor Defender caption supplied only surnames; this record now carries his identified full name, with the misprinted form preserved as an alias.";
    $p->save();
    echo "UPDATED cojerean\n";
}

// ---- Raymond -> Philip Aaron Raymond ----
$p = $find("raymond-detroit-1930");
if ($p && ! str_contains((string) $p->description, "Philip")) {
    $p->name = "Philip Raymond";
    $p->first_name = "Philip";
    $p->middle_name = "Aaron";
    $p->last_name = "Raymond";
    $p->aka = "Phil Raymond";
    $p->birthdate = "1899-02-04";
    $p->description = "Philip Aaron \"Phil\" Raymond was one of five Detroit workers sentenced to 90 days in jail for holding a workers'"'"' meeting, pictured (surname only) in the July 1930 Labor Defender. The Raymond arrested with Powers and Cojerean at the Briggs factory-gate meeting was almost certainly this Philip Raymond, identified in August 1930 as national organizer of the Auto Workers Union. Born in New York City on February 4, 1899, he became a Detroit Communist and labor organizer and ran for mayor of Detroit and for Congress on the Workers'"'"' ticket in 1930.";
    $aff = $p->affiliation ?? [];
    if (! in_array("Auto Workers Union", $aff, true)) { $aff[] = "Auto Workers Union"; $p->affiliation = $aff; }
    $p->save();
    echo "UPDATED raymond\n";
}

// ---- Powers -> George Edward Powers ----
$p = $find("powers-detroit-1930");
if ($p && ! str_contains((string) $p->description, "George")) {
    $p->name = "George Powers";
    $p->first_name = "George";
    $p->middle_name = "Edward";
    $p->last_name = "Powers";
    $p->aka = "George E. Powers";
    $p->birthdate = "1892-02-15";
    $p->description = "George Edward Powers was one of five Detroit workers sentenced to 90 days in jail for holding a workers'"'"' meeting, pictured (surname only) in the July 1930 Labor Defender. A contemporary Daily Worker report identifies George E. Powers as secretary of the Trade Union Unity League'"'"'s Michigan District, and the May 22, 1930 Daily Worker names Powers among those arrested at the Briggs factory-gate meeting. Born in Boston on February 15, 1892, he worked as a sheet-metal worker and labor organizer, lived for a time in Detroit, and ran as the Workers'"'"' Party candidate for United States senator from Michigan in 1930.";
    $aff = $p->affiliation ?? [];
    if (! in_array("Trade Union Unity League", $aff, true)) { $aff[] = "Trade Union Unity League"; $p->affiliation = $aff; }
    $p->save();
    echo "UPDATED powers\n";
}

// Conn and Caravas stay surname-only: no period source yet identifies them.

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Detroit 1930 names applied (Cojerean, Raymond, Powers; Conn and Caravas unchanged)."
