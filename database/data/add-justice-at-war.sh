#!/usr/bin/env bash
#
# "Justice at War" review audit (July 2026). Already present: MLK, Ralph
# Abernathy, A. D. King, Fred Shuttlesworth, Wyatt T. Walker, Mario Savio,
# Bettina Aptheker, Emma Goldman, Alexander Berkman, and all four Japanese
# American incarceration test-case figures (Yasui, Hirabayashi, Korematsu,
# Endo) — plus the whole Hollywood Ten.
#
#  1. Adds the seven missing: the three lesser-known Walker v. City of
#     Birmingham petitioners (J. W. Hayes, T. L. Fisher, John Thomas
#     Porter), Free Speech Movement figures Jack Weinberg and Suzanne
#     Goldberg Savio, Jose Padilla (framed per the review as a disputed
#     national-security detainee), and Mohammed Rafiq Butt (post-9/11
#     detainee who died in custody).
#  2. Adds the Walker v. Birmingham criminal-contempt case (five days +
#     $50, affirmed by the Supreme Court in 1967) to the five petitioners
#     already in the database, guarded so it is only created when no
#     contempt case exists on the record.
#  3. Fills the documented FSM jail sentences on Mario Savio (120 days)
#     and Bettina Aptheker (45 days), fill-if-empty.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/add-justice-at-war.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

WALKER_CASE='{"institution_name":"Birmingham City Jail","institution_city":"Birmingham","institution_state":"Alabama","charges":"Criminal contempt of court — for proceeding with the Good Friday and Easter 1963 marches in defiance of the Birmingham anti-demonstration injunction (Walker v. City of Birmingham)","convicted":"Yes — affirmed by the Supreme Court, 5-4 (1967)","sentence":"Five days in jail and a $50 fine","imprisoned_for_days":5}'

php artisan prisoner:add '{"name":"J. W. Hayes","first_name":"J.","middle_name":"W.","last_name":"Hayes","description":"The Rev. J. W. Hayes was a Birmingham movement minister and one of the eight petitioners in Walker v. City of Birmingham, convicted of criminal contempt for going forward with the Good Friday and Easter 1963 marches against segregation in defiance of a state-court injunction. The Supreme Court upheld the convictions 5-4 in 1967, and the eight served five-day jail terms that fall — the case that produced Martin Luther King Jr.'"'"'s final jailing.","state":"Alabama","race":"Black","gender":"Male","ideologies":["Civil rights"],"affiliation":["Southern Christian Leadership Conference"],"era":"1960s","released":true,"cases":['"$WALKER_CASE"']}' || true

php artisan prisoner:add '{"name":"T. L. Fisher","first_name":"T.","middle_name":"L.","last_name":"Fisher","description":"The Rev. T. L. Fisher was a Birmingham movement minister and one of the eight petitioners in Walker v. City of Birmingham, convicted of criminal contempt for going forward with the Good Friday and Easter 1963 marches against segregation in defiance of a state-court injunction. The Supreme Court upheld the convictions 5-4 in 1967, and the eight served five-day jail terms that fall.","state":"Alabama","race":"Black","gender":"Male","ideologies":["Civil rights"],"affiliation":["Southern Christian Leadership Conference"],"era":"1960s","released":true,"cases":['"$WALKER_CASE"']}' || true

php artisan prisoner:add '{"name":"John Thomas Porter","first_name":"John","middle_name":"Thomas","last_name":"Porter","aka":"J. T. Porter","description":"The Rev. John Thomas Porter (1931–2006), pastor of Sixth Avenue Baptist Church in Birmingham and once an assistant to Martin Luther King Jr. at Dexter Avenue Baptist in Montgomery, was one of the eight petitioners in Walker v. City of Birmingham, convicted of criminal contempt for going forward with the Good Friday and Easter 1963 marches against segregation in defiance of a state-court injunction. The Supreme Court upheld the convictions 5-4 in 1967, and the eight served five-day jail terms that fall.","state":"Alabama","race":"Black","gender":"Male","ideologies":["Civil rights"],"affiliation":["Southern Christian Leadership Conference"],"era":"1960s","released":true,"cases":['"$WALKER_CASE"']}' || true

php artisan prisoner:add '{"name":"Jack Weinberg","first_name":"Jack","last_name":"Weinberg","description":"Jack Weinberg was the Berkeley Free Speech Movement organizer whose October 1, 1964 arrest at a CORE table on Sproul Plaza — and the 32-hour student blockade of the police car holding him — ignited the movement, and who coined the era'"'"'s slogan \"Don'"'"'t trust anyone over 30.\" Convicted with the other leaders of the December 1964 Sproul Hall occupation demanding the right to organize politically on campus, he served a 120-day jail sentence.","state":"California","race":"White","gender":"Male","birthdate":"1940-04-04","ideologies":["Free speech","Civil rights"],"affiliation":["Free Speech Movement","Congress of Racial Equality (CORE)"],"era":"1960s","released":true,"cases":[{"charges":"Trespass and resisting arrest — the December 2-3, 1964 Sproul Hall sit-in (and the October 1, 1964 Sproul Plaza arrest that sparked the Free Speech Movement)","arrest_date":"1964-12-03","convicted":"Yes","sentence":"120 days","imprisoned_for_days":120}]}' || true

php artisan prisoner:add '{"name":"Suzanne Goldberg Savio","first_name":"Suzanne","last_name":"Goldberg","aka":"Suzanne Goldberg","description":"Suzanne Goldberg was a graduate-student leader of the Berkeley Free Speech Movement and a member of its steering committee, later married to Mario Savio. Convicted with the other leaders of the December 1964 Sproul Hall occupation demanding the right to organize politically on campus, she served a 45-day jail sentence.","state":"California","race":"White","gender":"Female","ideologies":["Free speech","Civil rights"],"affiliation":["Free Speech Movement"],"era":"1960s","released":true,"cases":[{"charges":"Trespass — the December 2-3, 1964 Sproul Hall sit-in","arrest_date":"1964-12-03","convicted":"Yes","sentence":"45 days","imprisoned_for_days":45}]}' || true

php artisan prisoner:add '{"name":"Jose Padilla","first_name":"Jose","last_name":"Padilla","aka":"José Padilla","description":"Jose Padilla is a U.S. citizen arrested at Chicago O'"'"'Hare on May 8, 2002 and, a month later, transferred to military custody as an \"enemy combatant\" — held roughly three and a half years in the Charleston naval brig without ordinary criminal charge, in isolation conditions his lawyers said destroyed his mental health, while his case (Rumsfeld v. Padilla) tested the limits of executive detention of citizens. Moved to civilian court in January 2006 as the Supreme Court neared review, he was convicted in 2007 of terrorism-conspiracy charges and resentenced in 2014 to 21 years. He is best described as a disputed national-security detainee rather than a prisoner of conscience: the arbitrary military detention of a citizen is the civil-liberties core of his case, while the government alleged, and a jury found, substantial al-Qaeda involvement.","race":"Hispanic","gender":"Male","birthdate":"1970-10-18","era":"2000s","released":false,"cases":[{"institution_name":"Naval Consolidated Brig, Charleston","institution_city":"Charleston","institution_state":"South Carolina","charges":"Held without charge as an \"enemy combatant\" (June 2002 - January 2006); then convicted of conspiracy to murder, kidnap and maim persons abroad and material support (2007)","arrest_date":"2002-05-08","convicted":"Yes — convicted in civilian court (2007) after 3.5 years of military detention without charge","sentence":"21 years (resentenced 2014; originally 17 years, 4 months)"}]}' || true

php artisan prisoner:add '{"name":"Mohammed Rafiq Butt","first_name":"Mohammed","middle_name":"Rafiq","last_name":"Butt","description":"Mohammed Rafiq Butt was a 55-year-old Pakistani man swept up in the post-September 11 dragnet on September 19, 2001, after a tip that proved baseless — the FBI found no connection between him and the attacks. Cleared of any terrorism suspicion, he was nonetheless kept confined over a visa overstay, and on October 23, 2001 he died of a heart attack in the Hudson County Correctional Center in New Jersey, having seen no lawyer and received no medical attention for his heart condition. He had engaged in no political activity; his case stands as an emblem of the arbitrary, discriminatory national-security detentions that followed September 11.","state":"New Jersey","race":"Asian","gender":"Male","death_date":"2001-10-23","era":"2000s","released":false,"cases":[{"institution_name":"Hudson County Correctional Center","institution_city":"Kearny","institution_state":"New Jersey","charges":"No criminal charge — detained September 19, 2001 in the post-9/11 sweep, cleared by the FBI, then held on an immigration visa overstay","arrest_date":"2001-09-19","incarceration_date":"2001-09-19","death_in_custody_date":"2001-10-23","convicted":"No — never charged; died in detention","imprisoned_for_days":34}]}' || true

# --- Walker v. Birmingham contempt case for the five already present -------
php artisan tinker --execute='
$jail = \App\Models\Institution::firstOrCreate(
    ["name" => "Birmingham City Jail"],
    ["city" => "Birmingham", "state" => "Alabama"]
);
foreach (["martin-luther-king-jr", "ralph-abernathy", "a-d-king", "fred-shuttlesworth", "wyatt-t-walker"] as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    if ($p->cases()->where("charges", "like", "%contempt%")->exists()) {
        echo "SKIP {$slug} (already has a contempt case)\n";
        continue;
    }
    $fields = [
        "institution_id" => $jail->id,
        "charges" => "Criminal contempt of court — for proceeding with the Good Friday and Easter 1963 marches in defiance of the Birmingham anti-demonstration injunction (Walker v. City of Birmingham)",
        "convicted" => "Yes — affirmed by the Supreme Court, 5-4 (1967)",
        "sentence" => "Five days in jail and a $50 fine",
        "imprisoned_for_days" => 5,
    ];
    if ($slug === "martin-luther-king-jr") {
        $fields["incarceration_date"] = "1967-10-30";   // his final jailing
    }
    $p->cases()->create($fields);
    echo "CASE created {$slug}\n";
}

// --- FSM sentences (fill-if-empty, single-case guard) ---------------------
foreach (["mario-savio" => "120 days", "bettina-aptheker" => "45 days"] as $slug => $sentence) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p || $p->cases()->count() !== 1) { continue; }
    $case = $p->cases()->first();
    $changed = false;
    if (empty($case->sentence)) { $case->sentence = $sentence; $changed = true; }
    if (empty($case->imprisoned_for_days)) { $case->imprisoned_for_days = (int) $sentence; $changed = true; }
    if ($changed) { $case->save(); echo "CASE {$slug}\n"; }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Justice at War additions applied."
