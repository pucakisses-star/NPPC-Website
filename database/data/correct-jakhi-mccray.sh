#!/usr/bin/env bash
#
# Correct the Jakhi McCray record. The existing entry described a 2020 George
# Floyd-protest case and stated he had already been sentenced — both wrong. The
# actual (Fire Ant-supported) case is the June 12, 2025 arson of NYPD vehicles
# in Bushwick, Brooklyn. Sources: DOJ EDNY, amNY, Patch, News 12 (April 2026).
#
#   Incident: June 12, 2025 (ten NYPD vehicles and a trailer, ~800,000 dollars
#             damage, in a secured lot in Bushwick, Brooklyn)
#   Surrender: July 21, 2025; briefly detained, then home detention in New Jersey
#   Guilty plea to federal arson: April 8, 2026 (EDNY)
#   Exposure: mandatory minimum 5 years, maximum 20 years
#   Status: free pending sentencing (no sentence as of late July 2026)
#
# Idempotent. Run from the repo root:
#   bash database/data/correct-jakhi-mccray.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "jakhi-mccray")->first();
if (! $p) { echo "jakhi-mccray not found.\n"; return; }

$p->description = "Jakhi McCray (also recorded as Jakhi Lodgson-McCray), a young activist from Brooklyn, was charged in the U.S. District Court for the Eastern District of New York over a fire that destroyed ten New York City Police Department vehicles and a trailer in a secured lot in Bushwick, Brooklyn, in the early morning of June 12, 2025; the blaze caused an estimated 800,000 dollars in damage. He surrendered to authorities on July 21, 2025, was briefly detained, and was then placed on home detention in New Jersey. On April 8, 2026 he pleaded guilty to a federal arson charge, which carries a mandatory minimum of five years and a maximum of twenty years in prison. As of mid-2026 he remained free pending sentencing; his support committee reported that he spent about eight months on home detention before winning a modification to a curfew. His case is supported by Fire Ant Movement Defense.";
$p->era = "2020s";
$p->state = "New York";
$p->in_custody = false;
$p->released = false;
$p->save();

$c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
$c->prisoner_id = $p->id;
$c->charges = "Federal arson (18 U.S.C. 844) — fire that destroyed ten NYPD vehicles and a trailer, Bushwick, Brooklyn, June 12, 2025";
$c->convicted = "Yes — pleaded guilty to federal arson (April 8, 2026, EDNY); awaiting sentencing (mandatory minimum 5 years, maximum 20 years)";
$c->setPartialDate("arrest_date", 2025, 7, 21);
$c->release_date = null;
$c->incarceration_date = null;
$c->sentenced_date = null;
$c->sentence = null;
$c->imprisoned_for_days = null;
$c->save();

echo "Corrected jakhi-mccray to the June 2025 NYPD-arson case.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Jakhi McCray record corrected."
