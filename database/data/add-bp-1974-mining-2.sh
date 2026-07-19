#!/usr/bin/env bash
#
# Tail of the 1974 Black Panther mining: the four December issues
# (Dec 7, 14, 21, 28), completing the full 48-issue run.
#
#  - Adds the three Black Panther Party members newly named in the
#    December 1974 Lamp Post arrests: Larry Henson, Lonnie Darden and
#    George Robinson (Forbes and Heard already in the database).
#  - Adds Australia Poole, the wounded Black Army private trapped by
#    discharge red tape, classified a deserter, and held at Fort Knox
#    after voluntarily returning in June 1974.
#  - Appends Sarah Bad Heart Bull's November 15, 1974 parole to her
#    record (marker-guarded).
#
# Verified already present: Joan Little, John Artis, John Hill
# (Dacajeweiah), Charles Pernasilice, Carlos Feliciano, Morton Sobell,
# Sarah Bad Heart Bull, Armando Miramon and Jesse Lopez (acquitted,
# per the Dec 7 issue). Skipped: the Dallas police-brutality lawsuit
# complainants Jody P. Brown and June Page (no charge or political-case
# detail), the eleven pardoned but unnamed Puerto Rican union leaders,
# and Attica prosecution witnesses Leland Spear and William Rivers.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/add-bp-1974-mining-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Larry Henson","first_name":"Larry","last_name":"Henson","description":"Larry Henson was a member of the Black Panther Party in Oakland. In December 1974 he was among the Party members falsely arrested at the Lamp Post Bar and Restaurant and booked on robbery and assault-with-a-deadly-weapon charges — arrests The Black Panther reported as part of the Oakland police campaign against the Party that had begun with the July 30, 1974 plainclothes attack on Huey P. Newton and seven others at the Fox Restaurant. Reconstructed from The Black Panther, December 28, 1974; disposition not located.","state":"California","race":"Black","gender":"Male","affiliation":["Black Panther Party"],"ideologies":["Black liberation"],"era":"1970s","in_custody":false,"released":true,"cases":[{"charges":"Robbery and assault with a deadly weapon — booked in the December 1974 arrests of Black Panther Party members at the Lamp Post Bar and Restaurant, Oakland, reported by the Party as false charges","convicted":"Unknown — disposition not located"}]}' || true

php artisan prisoner:add '{"name":"Lonnie Darden","first_name":"Lonnie","last_name":"Darden","description":"Lonnie Darden was a member of the Black Panther Party in Oakland. In December 1974 he was among the Party members falsely arrested at the Lamp Post Bar and Restaurant and booked on robbery and assault-with-a-deadly-weapon charges — arrests The Black Panther reported as part of the Oakland police campaign against the Party. Reconstructed from The Black Panther, December 28, 1974; disposition not located.","state":"California","race":"Black","gender":"Male","affiliation":["Black Panther Party"],"ideologies":["Black liberation"],"era":"1970s","in_custody":false,"released":true,"cases":[{"charges":"Robbery and assault with a deadly weapon — booked in the December 1974 arrests of Black Panther Party members at the Lamp Post Bar and Restaurant, Oakland, reported by the Party as false charges","convicted":"Unknown — disposition not located"}]}' || true

php artisan prisoner:add '{"name":"George Robinson","first_name":"George","last_name":"Robinson","description":"George Robinson was a member of the Black Panther Party in Oakland. In December 1974 he was among the Party members falsely arrested at the Lamp Post Bar and Restaurant and booked on robbery and assault-with-a-deadly-weapon charges — arrests The Black Panther reported as part of the Oakland police campaign against the Party. Reconstructed from The Black Panther, December 28, 1974; disposition not located.","state":"California","race":"Black","gender":"Male","affiliation":["Black Panther Party"],"ideologies":["Black liberation"],"era":"1970s","in_custody":false,"released":true,"cases":[{"charges":"Robbery and assault with a deadly weapon — booked in the December 1974 arrests of Black Panther Party members at the Lamp Post Bar and Restaurant, Oakland, reported by the Party as false charges","convicted":"Unknown — disposition not located"}]}' || true

php artisan prisoner:add '{"name":"Australia Poole","first_name":"Australia","last_name":"Poole","description":"Australia Poole was a Black Army private from Indianapolis whose right arm was left partially paralyzed by a wound received during his second enlistment. Two days before his discharge date the Army told him a medical board could win him special discharge benefits; he signed one waiver extending his service to December 2, but when that date arrived and the board had still not convened, he took his discharge and refused to sign a second waiver. The Army — which never completed his separation paperwork — classified him a deserter. On June 6, 1974, on learning of the classification, Poole voluntarily returned to Fort Knox to clear the matter up and, despite his protests and the absence of any official document placing him in a holding status, was held there. The ACLU and National Council of Churches Clemency Information Center assigned attorneys Carol Wild Scott and Gerald Ortman to challenge the Army'"'"'s discharge red tape in his case. Reconstructed from The Black Panther, December 21, 1974; disposition not located.","state":"Indiana","race":"Black","gender":"Male","era":"1970s","in_custody":false,"released":true,"cases":[{"institution_name":"Fort Knox","institution_city":"Fort Knox","institution_state":"Kentucky","charges":"Classified a deserter after Army discharge bungling — held at Fort Knox after voluntarily returning on June 6, 1974, without any official holding order","convicted":"Unknown — his ACLU/NCC lawyers challenged the discharge red tape; disposition not located"}]}' || true

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "sarah-bad-heart-bull")->first();
if ($p && ! str_contains((string) $p->description, "November 15")) {
    $p->description = trim((string) $p->description) . "\n\nOn November 15, 1974, she was released on parole from the South Dakota Women'"'"'s Prison in Yankton, where she had been confined since June 1 — her release coming as acquittals mounted in the Wounded Knee-related trials, whose dismissals and acquittals then totaled 44 (The Black Panther, December 14, 1974).";
    $p->save();
    echo "DESC sarah-bad-heart-bull\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. December 1974 tail applied (4 prisoners + Bad Heart Bull parole)."
