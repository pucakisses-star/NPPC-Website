#!/usr/bin/env bash
#
# Add the three Restored Israel of YAHWEH tax-case defendants -- Inge "Rose"
# Donato, Joseph "Joe" Donato and Kevin McKee -- members of a small religious
# group who refused on conscientious grounds to pay taxes they believed funded
# war. Their ONLY surviving conviction was conspiracy: the employment-tax counts
# were vacated and dismissed (Inge was also acquitted of all failure-to-file
# counts), so employment-tax evasion is NOT recorded as a final conviction.
#
# Joseph and Kevin each get two cases: the three-night April 2004 pretrial
# detention at the Camden County Jail, and their later federal sentence. Inge had
# no pretrial jail time. Incarceration/release dates are documented, so
# imprisoned_for_days computes correctly.
#
# Idempotent. Run from the repo root:
#   bash database/data/add-restored-israel-yahweh.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;

$inst = fn (string $name, string $city, string $state) =>
    Institution::firstOrCreate(["name" => $name], ["city" => $city, "state" => $state]);

$fdcPhila = $inst("Federal Detention Center, Philadelphia", "Philadelphia", "Pennsylvania");
$camden   = $inst("Camden County Jail", "Camden", "New Jersey");
$fairton  = $inst("Federal Correctional Institution, Fairton", "Fairton", "New Jersey");
$schuyl   = $inst("Federal Prison Camp, Schuylkill", "Minersville", "Pennsylvania");

$base = [
    "state" => "New Jersey", "era" => "2000s",
    "ideologies" => ["Tax resistance", "Pacifism"],
    "affiliation" => ["Restored Israel of YAHWEH"],
    "in_custody" => false, "released" => true,
];

// Update the record if present (these three are already in the DB), else
// create. Existing cases are cleared so the authoritative cases below are
// rebuilt cleanly -- this also removes any stale case that listed the
// vacated employment-tax-evasion conviction.
$mkPerson = function (array $attrs) use ($base) {
    $data = array_merge($base, $attrs);
    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($attrs["name"])])->first();
    if ($p) {
        $p->fill($data)->save();
        $n = $p->cases()->count();
        $p->cases()->delete();
        echo "updated: {$p->name} (slug {$p->slug}); cleared {$n} old case(s) for rebuild\n";
        return $p;
    }
    $p = Prisoner::create($data);
    echo "created: {$p->name} (slug {$p->slug})\n";
    return $p;
};

$addCase = function (Prisoner $p, string $marker, array $fields, $inst, ?array $arr = null, ?array $inc = null, ?array $rel = null) {
    $dupe = $p->cases()->where(function ($q) use ($marker) {
        $q->where("charges", "like", "%{$marker}%")->orWhere("sentence", "like", "%{$marker}%");
    })->exists();
    if ($dupe) { echo "  case [{$marker}] already present for {$p->name}\n"; return; }
    $c = $p->cases()->make(); $c->prisoner_id = $p->id;
    foreach ($fields as $k => $v) { $c->{$k} = $v; }
    if ($inst) { $c->institution_id = $inst->id; }
    if ($arr) { $c->setPartialDate("arrest_date", $arr[0], $arr[1], $arr[2]); }
    if ($inc) { $c->setPartialDate("incarceration_date", $inc[0], $inc[1], $inc[2]); }
    if ($rel) { $c->setPartialDate("release_date", $rel[0], $rel[1], $rel[2]); }
    $c->save();
    echo "  added case [{$marker}] to {$p->name} (days={$c->imprisoned_for_days})\n";
};

// ── Inge "Rose" Donato ───────────────────────────────────────────────
$inge = $mkPerson([
    "name" => "Inge Donato", "first_name" => "Inge", "last_name" => "Donato", "aka" => "Rose",
    "gender" => "Female", "inmate_number" => "40885-050",
    "description" => "Inge Donato, known as Rose, was the bookkeeper for the McKee-Donato Construction Company and a member of the Restored Israel of YAHWEH, a small religious group whose members refused on conscientious grounds to pay taxes they believed funded war. The Justice Department gave her age as 44 in April 2004 (born about 1959-1960). She was arrested at her home on April 12, 2004 by IRS Criminal Investigation and released the same day on a 100,000-dollar personal-recognizance bond. A jury convicted her of conspiracy; the employment-tax counts were later vacated and dismissed and she was acquitted of all personal failure-to-file counts. She was sentenced to six months in prison, a 50,000-dollar fine and three years of supervised release, and served her term at the Federal Detention Center in Philadelphia from August 8, 2005 to February 6, 2006.",
]);
$addCase($inge, "six months", [
    "charges" => "Conspiracy to defraud the United States. The employment-tax counts were vacated and dismissed, and she was acquitted of all personal failure-to-file counts.",
    "convicted" => "Convicted of conspiracy",
    "sentence" => "Six months imprisonment, a 50,000-dollar fine and three years of supervised release.",
], $fdcPhila, [2004, 4, 12], [2005, 8, 8], [2006, 2, 6]);

// ── Joseph "Joe" Donato ──────────────────────────────────────────────
$joe = $mkPerson([
    "name" => "Joseph Donato", "first_name" => "Joseph", "last_name" => "Donato", "aka" => "Joe",
    "gender" => "Male", "inmate_number" => "40884-050",
    "description" => "Joseph Donato, known as Joe, was a co-owner of the McKee-Donato Construction Company and a member of the Restored Israel of YAHWEH, whose members refused on conscientious grounds to pay taxes they believed funded war. The Justice Department gave his age as 46 in April 2004 (born about 1957-1958). Arrested on April 12, 2004, he was held three nights in the Camden County Jail after initially refusing bail conditions requiring his return for court, and was released on April 15 after agreeing to the conditions and after supporters posted a 150,000-dollar bond. His only surviving conviction was conspiracy; the employment-tax counts were vacated. He was sentenced on July 1, 2005 to 27 months in prison, a 5,000-dollar fine and three years of supervised release. He reported to the Federal Correctional Institution at Fairton, New Jersey on February 21, 2006, transferred to a halfway house in October 2007, and completed his sentence on January 31, 2008.",
]);
$addCase($joe, "three nights pretrial", [
    "charges" => "Held three nights pretrial in the Camden County Jail after initially refusing bail conditions requiring his return for court.",
    "convicted" => "Pretrial detention, April 2004",
    "sentence" => "Three nights in the Camden County Jail; released April 15, 2004 on a 150,000-dollar bond.",
], $camden, [2004, 4, 12], [2004, 4, 12], [2004, 4, 15]);
$addCase($joe, "27 months", [
    "charges" => "Conspiracy to defraud the United States (the employment-tax counts were vacated).",
    "convicted" => "Convicted of conspiracy; sentenced July 1, 2005",
    "sentence" => "27 months imprisonment, a 5,000-dollar fine and three years of supervised release; transferred to a halfway house in October 2007 and completed the sentence January 31, 2008.",
], $fairton, [2004, 4, 12], [2006, 2, 21], [2008, 1, 31]);

// ── Kevin McKee ──────────────────────────────────────────────────────
$kevin = $mkPerson([
    "name" => "Kevin McKee", "first_name" => "Kevin", "last_name" => "McKee",
    "gender" => "Male", "inmate_number" => "40886-050",
    "description" => "Kevin McKee was a co-owner of the McKee-Donato Construction Company and a member of the Restored Israel of YAHWEH, whose members refused on conscientious grounds to pay taxes they believed funded war. Arrested on April 12, 2004, he was held three nights in the Camden County Jail after initially refusing bail conditions, and was released on April 15 after accepting court-appearance conditions and after a 150,000-dollar bond was posted. His only surviving conviction was conspiracy; the employment-tax counts were vacated. He was sentenced on July 1, 2005 to 24 months in prison, a 4,000-dollar fine and three years of supervised release, and was imprisoned at the Federal Prison Camp Schuylkill in Minersville, Pennsylvania from February 13, 2006 to November 5, 2007.",
]);
$addCase($kevin, "three nights pretrial", [
    "charges" => "Held three nights pretrial in the Camden County Jail after initially refusing bail conditions.",
    "convicted" => "Pretrial detention, April 2004",
    "sentence" => "Three nights in the Camden County Jail; released April 15, 2004 on a 150,000-dollar bond.",
], $camden, [2004, 4, 12], [2004, 4, 12], [2004, 4, 15]);
$addCase($kevin, "24 months", [
    "charges" => "Conspiracy to defraud the United States (the employment-tax counts were vacated).",
    "convicted" => "Convicted of conspiracy; sentenced July 1, 2005",
    "sentence" => "24 months imprisonment, a 4,000-dollar fine and three years of supervised release.",
], $schuyl, [2004, 4, 12], [2006, 2, 13], [2007, 11, 5]);

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
