#!/usr/bin/env bash
#
# Civil War political-detention audit (July 2026). Already in the database:
# Clement Vallandigham, John Merryman, Lambdin P. Milligan and Jefferson
# Davis. Judge William Matthew Merrick is deliberately NOT added, per the
# source review's own assessment (surveillance/de facto house arrest, not a
# confirmed formal detention).
#
#  1. Adds the five missing: D. D. Foley, Father John A. Cummings, Clement
#     C. Clay Jr., David Levy Yulee and Stephen R. Mallory — each framed
#     per the review (Foley and Cummings as political/legal-process and
#     religious-oath prisoners; the three Confederate officials as postwar
#     political/state-security detainees never brought to trial).
#  2. Fill-if-empty custody detail on the four already present.
#
# Idempotent: prisoner:add refuses duplicates (|| true keeps going).
#
# Run from the repo root:  bash database/data/add-civil-war-detainees.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"D. D. Foley","first_name":"D.","middle_name":"D.","last_name":"Foley","aka":"Dennis Foley","description":"D. D. Foley was a Washington attorney who represented the father of an underage Union soldier and personally served Judge William Merrick'"'"'s habeas-corpus writ on Provost Marshal Andrew Porter (United States ex rel. Murphy v. Porter). Porter first declared him under arrest but let him leave on parole; on October 21, 1861 Foley was arrested again and placed in the guardhouse. His offense was, in effect, performing lawful legal work against the military authorities. How long he was held is not established in the surviving sources.","state":"District of Columbia","gender":"Male","era":"1800s","released":true,"cases":[{"charges":"No criminal charge — detained by military authority for serving a habeas-corpus writ on the provost marshal","arrest_date":"1861-10-21","convicted":"No — never charged"}]}' || true

php artisan prisoner:add '{"name":"John A. Cummings","first_name":"John","middle_name":"A.","last_name":"Cummings","aka":"Father John A. Cummings","description":"Father John A. Cummings was a Missouri Catholic priest prosecuted for continuing to preach without taking the state'"'"'s sweeping anti-Confederate \"ironclad\" loyalty oath. Convicted in September 1865, he was fined $500 and committed to jail until the fine and costs were paid. The Missouri Supreme Court affirmed, but the U.S. Supreme Court reversed in January 1867 in Cummings v. Missouri, holding the oath provisions unconstitutional as bills of attainder and ex post facto laws. The record establishes the jail-commitment order, though not how many days he actually spent confined.","state":"Missouri","race":"White","gender":"Male","ideologies":["Religious liberty"],"era":"1800s","released":true,"cases":[{"charges":"Preaching without taking Missouri'"'"'s \"ironclad\" loyalty oath","convicted":"Yes — reversed by the Supreme Court (Cummings v. Missouri, January 1867)","sentence":"A $500 fine and commitment to jail until the fine and court costs were paid"}]}' || true

php artisan prisoner:add '{"name":"Clement C. Clay Jr.","first_name":"Clement","middle_name":"C.","last_name":"Clay","description":"Clement C. Clay Jr., a former U.S. senator from Alabama and Confederate agent in Canada, surrendered in May 1865 after the government accused him of involvement in the Lincoln assassination conspiracy and of organizing terrorist bands. He was imprisoned at Fort Monroe for about eleven months without any completed prosecution and released on April 17, 1866 on an oath and parole requiring him to appear if charges were ever prepared — they never were. A postwar political and state-security detention resting on unproved conspiracy allegations.","state":"Alabama","race":"White","gender":"Male","era":"1800s","released":true,"cases":[{"institution_name":"Fort Monroe","institution_city":"Hampton","institution_state":"Virginia","charges":"Accused of complicity in the Lincoln assassination and of organizing terrorist bands from Canada — never prosecuted","release_date":"1866-04-17","convicted":"No — released on oath and parole; no charges ever brought to trial","imprisoned_for_days":335}]}' || true

php artisan prisoner:add '{"name":"David Levy Yulee","first_name":"David","middle_name":"Levy","last_name":"Yulee","description":"David Levy Yulee, a former U.S. senator from Florida, was accused of having encouraged the seizure of federal installations while still holding his Senate seat and later of assisting Jefferson Davis'"'"'s escape. He was held at Fort Pulaski for roughly nine to ten months and released in March 1866; no treason trial was ever completed. A postwar political and state-security detention.","state":"Florida","race":"White","gender":"Male","era":"1800s","released":true,"cases":[{"institution_name":"Fort Pulaski","institution_city":"Savannah","institution_state":"Georgia","charges":"Accused of treason — encouraging seizure of federal installations and assisting Jefferson Davis'"'"'s flight; never tried","convicted":"No — released March 1866 without trial","imprisoned_for_days":285}]}' || true

php artisan prisoner:add '{"name":"Stephen R. Mallory","first_name":"Stephen","middle_name":"R.","last_name":"Mallory","description":"Stephen R. Mallory, a former U.S. senator from Florida and the Confederate secretary of the navy, was arrested on May 20, 1865 and confined at Fort Lafayette as a political prisoner, accused of treason and of organizing piratical expeditions. The prosecution was never pursued; he was paroled in March 1866 after about ten months and later pardoned. A postwar political and state-security detention.","state":"Florida","race":"White","gender":"Male","era":"1800s","released":true,"cases":[{"institution_name":"Fort Lafayette","institution_city":"New York","institution_state":"New York","charges":"Accused of treason and organizing piratical expeditions — never prosecuted","arrest_date":"1865-05-20","incarceration_date":"1865-05-20","convicted":"No — paroled March 1866; later pardoned","imprisoned_for_days":300}]}' || true

# --- Fill-if-empty custody detail on the four already present -------------
php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$fillCase = function ($p, array $fill): void {
    if (! $p || $p->cases()->count() !== 1) { if ($p) { echo "SKIP {$p->slug}\n"; } return; }
    $case = $p->cases()->first();
    $changed = false;
    foreach ($fill as $f => $v) {
        if (! empty($case->{$f})) { continue; }
        $case->{$f} = $v;
        $changed = true;
    }
    if ($changed) { $case->save(); echo "CASE {$p->slug}\n"; }
};

$fillCase($find("clement-vallandigham"), [
    "arrest_date" => "1863-05-05",
    "convicted" => "Yes — by military commission; sentence commuted by Lincoln to banishment through Union lines",
    "sentence" => "Close military confinement for the duration of the war (Fort Warren designated); commuted to banishment into the Confederacy",
]);
$fillCase($find("john-merryman"), [
    "arrest_date" => "1861-05-25",
    "convicted" => "No — indicted for treason but never tried; transferred to civil custody and bailed",
]);
$fillCase($find("lambdin-p-milligan"), [
    "arrest_date" => "1864-10-05",
    "release_date" => "1866-04-01",
    "convicted" => "Yes — by military commission, sentenced to hang; commuted to life, then voided by Ex parte Milligan (1866)",
    "imprisoned_for_days" => 540,
]);
$fillCase($find("jefferson-davis"), [
    "incarceration_date" => "1865-05-22",
    "release_date" => "1867-05-13",
    "convicted" => "No — indicted for treason but never tried; released on $100,000 bail, proceedings dropped after the December 1868 amnesty",
    "imprisoned_for_days" => 720,
]);

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Civil War detainee additions applied."
