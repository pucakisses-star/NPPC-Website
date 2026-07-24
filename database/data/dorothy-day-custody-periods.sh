#!/usr/bin/env bash
#
# Add Dorothy Day (1897-1980) and record her ten documented custody periods
# (site owner's research). Dorothy Day was not previously in the database; this
# script creates her prisoner record and attaches one case per jailing.
#
# Date precision is preserved where the exact day is unknown, and where a period
# has no confirmed release day the release is left blank rather than invented:
#
#   1)  Nov 10 - ~28, 1917     Silent Sentinels suffrage picket; Occoquan (~15 days after sentencing)
#   2)  c. 1919-1921           Chicago IWW-rooming-house raid; ~2 nights (date & release unconfirmed)
#   3)  Jun 15 - 16, 1955      Operation Alert refusal, NYC (~24 hours)
#   4)  Jul 20 - 21, 1956      Operation Alert refusal, NYC (~8 hours, bailed)
#   5)  Jan 15 - ~19/20, 1957  Sentence served for the July 1956 protest (~5 days)
#   6)  Jul 12 - ~Aug 5/6,1957 Operation Alert protest; ~25 days of a 30-day term
#   7)  May 6, 1958            Picketing the Atomic Energy Commission; several hours, sentence suspended
#   8)  Apr 17, 1959           Operation Alert arrest, NYC (brief; release time unconfirmed)
#   9)  Apr 24 - ~May 3/4,1959 Ten days' jail chosen over a $25 fine
#   10) Aug 2 - 13, 1973       UFW grape-strike picket line, near Fresno; ~11 days (her last arrest)
#
# Suspended sentences (e.g. the 1958 AEC case) are recorded as the few hours
# actually held, not as the nominal term.
#
# Idempotent (finds Dorothy Day by name, then deletes and recreates her cases
# each run). Run from the repo root:
#   bash database/data/dorothy-day-custody-periods.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("name", "Dorothy Day")->first();

$bio = "Dorothy Day (1897-1980) was an American journalist, social activist, and co-founder — with Peter Maurin — of the Catholic Worker Movement in 1933. A convert to Catholicism who fused Christian faith with anarchism and pacifism, she spent nearly half a century practicing voluntary poverty, running houses of hospitality, and courting arrest through nonviolent civil disobedience. Her jailings ran from the 1917 suffrage pickets outside the White House, through the 1950s refusals to take shelter during New York'"'"'s Operation Alert civil-defense drills, to her final arrest at age 75 on a United Farm Workers picket line in California in 1973. The Catholic Church opened her cause for canonization in 2000, naming her a Servant of God.";

$isNew = ! $p;
if ($isNew) {
    $p = new \App\Models\Prisoner();
    $p->name = "Dorothy Day";
}

// Fill only empty fields so an already-curated record is not clobbered;
// the custody flags are always corrected to match the periods below.
$fill = function (string $field, $value) use ($p) {
    $cur = $p->{$field};
    if ($cur === null || $cur === "" || (is_array($cur) && count($cur) === 0)) {
        $p->{$field} = $value;
    }
};
$fill("first_name", "Dorothy");
$fill("last_name", "Day");
$fill("description", $bio);
$fill("state", "New York");
$fill("race", "White");
$fill("gender", "Female");
$fill("birthdate", "1897-11-08");
$fill("death_date", "1980-11-29");
$fill("ideologies", ["Anarchism", "Pacifism", "Christian Pacifism", "Anti-Nuclear", "Civil Disobedience"]);
$fill("affiliation", ["Catholic Worker"]);
$fill("era", "1910s");
$p->in_custody = false;
$p->released = true;
$p->save();

echo "Prisoner: {$p->name} (ID: {$p->id}, slug: {$p->slug}) [".($isNew ? "created" : "existing")."]\n";

// [incarc, release|null, [instName,instCity,instState]|null, charges, convicted, sentence-note]
$periods = [
    [
        [1917,11,10], [1917,11,28],
        ["Occoquan Workhouse","Occoquan","Virginia"],
        "Obstructing sidewalk traffic (picketing the White House with the National Woman'"'"'s Party \"Silent Sentinels\")",
        "Convicted; sentenced to 30 days",
        "Arrested November 10, 1917 and committed to the Occoquan Workhouse on November 14; joined the suffragist hunger strike and was released about November 27-28 when the group'"'"'s sentences were remitted, roughly fifteen days after sentencing.",
    ],
    [
        [1920,null,null], null,
        ["Chicago police lockup","Chicago","Illinois"],
        "Booked during a police raid on an IWW-associated rooming house (held on a disorderly-house pretext)",
        "No charge sustained; released",
        "Falsely booked when Chicago police raided the IWW-linked rooming house where she was staying; held about two nights and one full day before release. Exact date unconfirmed (probably 1919-1921).",
    ],
    [
        [1955,6,15], [1955,6,16],
        ["Women'"'"'s House of Detention","New York","New York"],
        "Refusing to take shelter during New York'"'"'s Operation Alert civil-defense drill (State Defense Emergency Act)",
        "Arrested; arraigned the next day",
        "Arrested about 2:05 p.m. on June 15, 1955 for refusing to take cover in the first Operation Alert drill; held roughly 24 hours and arraigned June 16.",
    ],
    [
        [1956,7,20], [1956,7,21],
        ["Women'"'"'s House of Detention","New York","New York"],
        "Refusing to participate in the Operation Alert civil-defense drill",
        "Arrested; released the same night",
        "Arrested about 4:10 p.m. on July 20, 1956 for refusing shelter during Operation Alert; released around midnight July 20-21 after about eight hours.",
    ],
    [
        [1957,1,15], [1957,1,19],
        ["Women'"'"'s House of Detention","New York","New York"],
        "Serving the sentence imposed for the July 1956 Operation Alert protest",
        "Sentenced for the 1956 refusal",
        "Served about five days, January 15 to roughly January 19-20, 1957, for the July 1956 Operation Alert protest.",
    ],
    [
        [1957,7,12], [1957,8,6],
        ["Women'"'"'s House of Detention","New York","New York"],
        "Refusing to take shelter during the Operation Alert civil-defense drill",
        "Convicted; sentenced to 30 days",
        "Jailed July 12, 1957 and released about August 5-6 after serving roughly 25 days of a 30-day sentence for the Operation Alert protest.",
    ],
    [
        [1958,5,6], [1958,5,6],
        ["Washington, D.C. police custody","Washington","District of Columbia"],
        "Picketing the U.S. Atomic Energy Commission",
        "Convicted; sentence suspended",
        "Arrested May 6, 1958 while picketing the Atomic Energy Commission; held several hours and released with a suspended sentence.",
    ],
    [
        [1959,4,17], null,
        ["Women'"'"'s House of Detention","New York","New York"],
        "Refusing to take shelter during the Operation Alert civil-defense drill",
        "Arrested; released pending sentencing",
        "Arrested April 17, 1959 in the Operation Alert protest; briefly processed and released pending court. Exact release time unconfirmed.",
    ],
    [
        [1959,4,24], [1959,5,4],
        ["Women'"'"'s House of Detention","New York","New York"],
        "Serving jail time in lieu of a 25-dollar fine for the Operation Alert protest",
        "Chose ten days'"'"' jail rather than pay a 25-dollar fine",
        "Served about ten days, April 24 to roughly May 3-4, 1959, having chosen jail over a 25-dollar fine for the civil-defense protest.",
    ],
    [
        [1973,8,2], [1973,8,13],
        ["Fresno County Jail","Fresno","California"],
        "Defying a court injunction against mass picketing during the United Farm Workers grape strike",
        "Arrested for unlawful assembly / contempt of the injunction",
        "Her final arrest, at age 75: jailed August 2, 1973 near Fresno for joining United Farm Workers picket lines in defiance of an anti-picketing injunction; released about August 13 after roughly eleven days.",
    ],
];

$p->cases()->delete();

$n = 0;
foreach ($periods as $per) {
    [$inc, $rel, $inst, $charges, $convicted, $sentence] = $per;
    $c = new \App\Models\PrisonerCase();
    $c->prisoner_id = $p->id;
    if ($inst !== null) {
        $institution = \App\Models\Institution::firstOrCreate(["name" => $inst[0]], ["city" => $inst[1] ?? null, "state" => $inst[2] ?? null]);
        $c->institution_id = $institution->id;
    }
    $c->charges = $charges;
    $c->convicted = $convicted;
    $c->sentence = $sentence;
    $c->setPartialDate("incarceration_date", $inc[0], $inc[1] ?? null, $inc[2] ?? null);
    if ($rel !== null) { $c->setPartialDate("release_date", $rel[0], $rel[1] ?? null, $rel[2] ?? null); }
    $c->save();
    $n++;
    echo "  period {$n}: ".($c->partialDateIso("incarceration_date") ?? "-")." -> ".($c->partialDateIso("release_date") ?? "-")." ({$c->imprisoned_for_days} days)\n";
}

echo "\nRecorded {$n} custody periods for Dorothy Day.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Dorothy Day added with her ten documented custody periods."
