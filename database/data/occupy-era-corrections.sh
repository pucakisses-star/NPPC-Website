#!/usr/bin/env bash
#
# Occupy-era corrections + one addition, from case info supplied by the site
# owner (cross-checked against DOJ/press where possible). Fixes the date fields
# so the "time in custody" counters compute correctly (most were misfiring
# because a sentencing date had been stored as the incarceration date), sets
# accurate sentences, and adds the one missing defendant (Cameron Rose).
#
# Dates that are month-precision or estimated are marked as such. Where a
# release date is genuinely unconfirmed (Cameron Rose, Cesar Aguirre) no custody
# duration is asserted. Idempotent. Run from the repo root:
#   bash database/data/occupy-era-corrections.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$setDate = function ($c, $field, $d) {
    if ($d === null) { return; }
    $c->setPartialDate($field, $d[0], $d[1] ?? null, $d[2] ?? null);
};

// [slug, arrest, incarceration, sentenced, release, sentence, convicted]
// each date is [y,m,d] (m/d may be null) or null.
$data = [
    // Cleveland (federal) — known/estimated releases
    ["connor-stevens",       [2012,4,30], [2012,4,30], [2012,11,20], [2019,7,null],  "97 months (8 years 1 month) in federal prison, followed by lifetime supervised release", "Yes — pleaded guilty to conspiracy and attempted use of a weapon of mass destruction"],
    ["brandon-baxter",       [2012,4,30], [2012,4,30], [2012,11,20], [2021,1,null],  "117 months (9 years 9 months) in federal prison, plus lifetime supervised release; released approx. 2021 (estimated)", "Yes — pleaded guilty to conspiracy and attempted use of a weapon of mass destruction (FBI-supplied inert device)"],
    ["anthony-hayne",        [2012,4,30], [2012,4,30], [2012,11,30], [2016,6,null],  "72 months (6 years) in federal prison; pleaded guilty and cooperated, the shortest sentence of the five; released approx. 2016 (estimated)", "Yes — pleaded guilty and cooperated with prosecutors"],
    ["joshua-stafford",      [2012,4,30], [2012,4,30], [2013,10,null], [2021,4,null], "120 months (10 years) in federal prison, plus lifetime supervised release; convicted at a bench trial (June 2013) after declining to plead; released approx. 2021 (estimated)", "Yes — convicted at a bench trial (declined to plead guilty); 120 months, the statutory minimum"],
    ["douglas-l-wright",     [2012,4,30], [2012,4,30], [2012,11,20], [2022,8,null],  "138 months (11 years 6 months) in federal prison, plus lifetime supervised release; released approx. 2022 (estimated)", "Yes — pleaded guilty to conspiracy and attempted use of a weapon of mass destruction"],
    // NATO 3 / NATO 5 (Illinois state)
    ["brian-church",         [2012,5,16], [2012,5,16], [2014,4,25], [2014,11,null],  "Convicted of mob action and possessing an incendiary device (acquitted of terrorism); reported 5 years; released approx. November 2014 after 709+ days already served", "Partial — acquitted of terrorism; convicted of mob action and possessing an incendiary device"],
    ["brent-betterly",       [2012,5,16], [2012,5,16], [2014,4,25], [2015,4,null],   "6 years in Illinois state prison (acquitted of terrorism; convicted of possessing an incendiary device and mob action); 709 days already served at sentencing; paroled April 2015", "Partial — acquitted of terrorism; convicted of possessing an incendiary device and mob action"],
    ["jared-chase",          [2012,5,16], [2012,5,16], [2014,4,25], [2020,1,null],   "8 years in Illinois state prison plus an added 1 year (April 2016) for an in-custody incident (acquitted of terrorism; convicted of possessing an incendiary device and mob action); more than 8 years served, released 2020", "Partial — acquitted of terrorism; convicted of possessing an incendiary device and mob action"],
    ["sebastian-senakiewicz",[2012,5,17], [2012,5,17], [2012,11,6], [2013,8,14],     "4 years in Illinois state prison (impact-incarceration/boot-camp recommended); completed the custodial portion and was deported to Poland on August 14, 2013", "Yes — pleaded guilty to falsely making a terrorist threat"],
    ["mark-neiweem",         [2012,5,null], [2012,5,null], [2013,4,11], [2013,11,null], "3 years in Illinois state prison; released approx. late 2013 (estimated)", "Yes — pleaded guilty to solicitation and attempted possession of an explosive or incendiary device"],
    // Occupy Wall Street
    ["mark-adams",           [2011,12,17], [2012,6,18], [2012,6,18], [2012,8,2],      "45 days at Rikers Island", "Yes — convicted of trespass, attempted criminal mischief, and attempted possession of burglary tools"],
    ["cecily-mcmillan",      [2014,3,17], [2014,5,5], [2014,5,19], [2014,7,2],        "90 days plus 5 years probation (58 days of continuous post-conviction confinement served)", "Yes — convicted of assaulting a police officer"],
    // Seattle grand-jury resisters (civil contempt, no criminal conviction)
    ["matt-duran",           null, [2012,9,14], null, [2013,2,28],  "Civil contempt (grand-jury resistance) — jailed for refusing to testify; about 5.5 months, much of it in restrictive/solitary conditions; not convicted of any crime", "No — civil contempt (no criminal conviction)"],
    ["katherine-olejnik",    null, [2012,9,27], null, [2013,2,28],  "Civil contempt (grand-jury resistance) — about 5 months; released when a judge found further confinement would not compel cooperation", "No — civil contempt (no criminal conviction)"],
    ["leah-lynn-plante",     null, [2012,10,10], null, [2012,10,17],"Civil contempt (grand-jury resistance) — 7 days; released without any underlying charge", "No — civil contempt (no criminal conviction)"],
    ["maddy-pfeiffer",       null, [2012,12,26], null, [2013,4,11], "Civil contempt (grand-jury resistance) — about 3.5 months; last of the Seattle resisters jailed and released", "No — civil contempt (no criminal conviction)"],
];

$done = 0;
foreach ($data as $row) {
    [$slug, $arr, $inc, $sen, $rel, $sentence, $convicted] = $row;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "  not found: {$slug}\n"; continue; }
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $setDate($c, "arrest_date", $arr);
    $setDate($c, "incarceration_date", $inc);
    $setDate($c, "sentenced_date", $sen);
    if ($rel !== null) { $setDate($c, "release_date", $rel); }
    $c->sentence = $sentence;
    $c->convicted = $convicted;
    $c->save();
    $p->in_custody = false; $p->released = true; $p->save();
    echo "  {$slug} corrected\n"; $done++;
}

// --- Cesar Aguirre: 6-month sentence imposed, freed on bail pending appeal;
//     actual post-appeal time served not confirmed (no incarceration asserted). ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "cesar-aguirre")->first();
if ($p) {
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->setPartialDate("arrest_date", 2011, 11, 3);
    $c->setPartialDate("sentenced_date", 2012, 9, 11);
    $c->incarceration_date = null; $c->release_date = null; $c->imprisoned_for_days = null;
    $c->sentence = "6-month county-jail sentence imposed for felony vandalism; allowed to remain free on bail pending appeal, so actual time served after the appeal is not confirmed";
    $c->convicted = "Yes — convicted of felony vandalism";
    $c->save();
    $p->in_custody = false; $p->released = true; $p->save();
    echo "  cesar-aguirre corrected (custody unconfirmed)\n";
}

// --- Douglas Wright: he was NOT the oldest of the five (Hayne, 35, was; Wright was 26). ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "douglas-l-wright")->first();
if ($p && $p->description) {
    $d = str_replace("the oldest of the five young Occupy Cleveland activists", "one of the five young Occupy Cleveland activists", $p->description);
    if ($d !== $p->description) { $p->description = $d; $p->save(); echo "  douglas-l-wright: oldest claim corrected\n"; }
}

// --- Roberto Rivera: corrected facts (NY-licensed, reportedly unemployed; the
//     Charging Bull plan he reportedly abandoned; 25-year sentence in 2019). ---
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "roberto-rivera")->first();
if ($p) {
    $p->description = "Roberto Rivera was a New York-licensed physician, reportedly unemployed at the time, who was inspired by the Occupy Wall Street movement. Prosecutors said he contemplated using an explosion to topple the Charging Bull statue on Wall Street; Rivera reportedly said he abandoned the plan after determining that it could not be carried out safely. He was arrested on November 16-17, 2012 after police learned he had acquired a quantity of chemicals and raided his home. A 25-year sentence was imposed years later, reportedly in February 2019.";
    $p->in_custody = true; $p->released = false; $p->save();
    $c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
    $c->prisoner_id = $p->id;
    $c->setPartialDate("arrest_date", 2012, 11, 16);
    $c->setPartialDate("incarceration_date", 2019, 2, null);
    $c->setPartialDate("sentenced_date", 2019, 2, null);
    $c->release_date = null;
    $c->sentence = "25 years";
    $c->convicted = "Yes";
    $c->save();
    echo "  roberto-rivera corrected\n";
}

// --- Cameron Rose (NEW): Occupy Oakland; held pretrial on a 130,000-dollar bond
//     he could not post; convicted, imposition of sentence suspended, 5 years
//     probation and no additional jail. Release date not publicly confirmed, so
//     no custody duration is asserted. ---
$slug = "cameron-rose";
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
if (! $p) {
    $p = \App\Models\Prisoner::create([
        "name" => "Cameron Rose", "first_name" => "Cameron", "last_name" => "Rose",
        "description" => "Cameron Rose was arrested about a month after a December 2011 confrontation at an Occupy Oakland teepee vigil outside Oakland City Hall, in which he was accused of striking a police officer with a folding chair. Taken into custody on January 22, 2012, he was held in pretrial detention because he could not post the unusually high 130,000-dollar bond. A jury convicted him of resisting an executive officer and misdemeanor assault on a peace officer while acquitting him of felony assault with a deadly weapon; the judge suspended imposition of a prison sentence and placed him on five years probation, with no additional jail. His exact release date is not publicly confirmed; he was released upon that disposition. His case is better understood as pretrial political detention than a prison sentence.",
        "state" => "California", "era" => "2010s",
        "ideologies" => ["Police Accountability"], "affiliation" => ["Occupy Movement"],
        "in_custody" => false, "released" => true,
    ]);
    echo "  created cameron-rose\n";
} else {
    echo "  cameron-rose already present\n";
}
$c = $p->cases()->first() ?: (new \App\Models\PrisonerCase(["prisoner_id" => $p->id]));
$c->prisoner_id = $p->id;
$c->setPartialDate("arrest_date", 2012, 1, 22);
$c->charges = "Resisting an executive officer; misdemeanor assault on a peace officer (acquitted of felony assault with a deadly weapon)";
$c->convicted = "Yes — convicted of resisting an executive officer and misdemeanor assault; imposition of sentence suspended, 5 years probation, no additional jail";
$c->sentence = "5 years probation (imprisonment suspended); held pretrial on a 130,000-dollar bond, release date unconfirmed";
$c->save();

echo "\nCorrected {$done} record(s) plus Aguirre, Wright, Rivera, and Cameron Rose.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Occupy-era corrections applied."
