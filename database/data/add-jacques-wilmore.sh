#!/usr/bin/env bash
#
# Add Jacques E. Wilmore, the fourth Lincoln University student arrested in the
# January 11, 1950 Oxford Theatre desegregation protest, who was missing from
# the database. The other three (Archibald Scales, Luther Manning, Vernell
# Dieudonne) are already present; this mirrors their records.
#
# All four were arrested Jan 11, 1950 for refusing segregated theater seating;
# the disorderly-conduct charges were dropped and they were discharged Jan 13,
# 1950 (about two days in custody). They later won a 1953 civil-rights damages
# suit.
#
# Idempotent: skips if a "Jacques E. Wilmore" with the Lincoln University
# affiliation already exists. Run from the repo root:
#   bash database/data/add-jacques-wilmore.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$name = "Jacques E. Wilmore";
$exists = \App\Models\Prisoner::withoutGlobalScopes()
    ->whereRaw("LOWER(name) = ?", [strtolower($name)])->get()
    ->contains(function ($m) {
        return in_array("lincoln university", array_map("strtolower", (array) $m->affiliation), true);
    });
if ($exists) { echo "Jacques E. Wilmore already present; nothing to do.\n"; return; }

$p = new \App\Models\Prisoner();
$p->name = $name;
$p->first_name = "Jacques";
$p->last_name = "Wilmore";
$p->aka = "Jaques Wilmore";
$p->state = "Pennsylvania";
$p->era = "1950s";
$p->gender = "Male";
$p->race = "Black";
$p->ideologies = ["Civil rights"];
$p->affiliation = ["Lincoln University"];
$p->in_custody = false;
$p->released = true;
$p->description = "Jacques E. Wilmore (sometimes spelled Jaques Wilmore) was a Lincoln University student arrested on January 11, 1950 at the Oxford Theatre for refusing segregated theater seating. The disorderly-conduct charges were dropped and the students were discharged on January 13, 1950, after about two days in custody. He and his fellow students won a civil-rights damages suit in 1953.";
$p->save();

$c = new \App\Models\PrisonerCase();
$c->prisoner_id = $p->id;
$c->charges = "Disorderly conduct (Oxford Theatre desegregation protest); charges dropped January 13, 1950.";
$c->convicted = "No — charges dropped; the students later won a 1953 civil-rights damages suit.";
$c->sentence = "Discharged after about two days; prevailed in a 1953 civil-rights damages suit.";
$c->setPartialDate("arrest_date", 1950, 1, 11);
$c->setPartialDate("release_date", 1950, 1, 13);
$c->imprisoned_for_days = 2;
$c->save();

echo "Created {$p->name} ({$p->slug}) with one case.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Jacques E. Wilmore added."
