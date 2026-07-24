#!/usr/bin/env bash
#
# Record Dorothy Ray Healey's six documented custody periods (site owner's
# research). Her two existing cases were inaccurate duplicates of the 1951-52
# Smith Act episode (one used the 1957 reversal date as a "release"), so they
# are replaced by the six periods below. Date precision is preserved where the
# exact day is unknown; where a period has no confirmed release day, the release
# is left blank rather than invented.
#
#   1) May 1-~15, 1930       Oakland May Day arrest (juvenile detention, ~2 wks)
#   2) January 1934          Imperial Valley strike arrest (pretrial, ~1 wk)
#   3) May 14 - Nov 14, 1934 Imperial County Jail, six months served
#   4) ~June 23-24, 1949     Grand-jury contempt (one day/night; bailed)
#   5) Jul 26 - ~Dec 7, 1951 Smith Act pretrial detention (~4.5 months)
#   6) Aug 8 - 30, 1952      Post-sentencing remand (22 days; bailed)
#
# The five-year Smith Act sentence is NOT entered as time served (reversed by
# Yates v. United States, 1957; only the ~3-week 1952 remand was actually served).
#
# Idempotent (deletes and recreates her cases each run). Run from the repo root:
#   bash database/data/dorothy-healey-custody-periods.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "dorothy-healey")->first();
if (! $p) { echo "dorothy-healey not found.\n"; return; }

$p->in_custody = false;
$p->released = true;
$p->save();

// [incarc, release, [instName,instCity,instState]|null, [charges...], convicted, sentence]
$periods = [
    [
        [1930,5,1], [1930,5,15],
        ["Juvenile Detention Home, Oakland","Oakland","California"],
        ["Arrested at the Oakland May Day unemployment demonstration"],
        "Juvenile; released on probation",
        "Held about two weeks in a juvenile detention home after police broke up the May Day meeting, then released on probation. Exact release day unconfirmed.",
    ],
    [
        [1934,1,null], null,
        ["Imperial County Jail","El Centro","California"],
        ["Unlawful assembly, inciting to riot, vagrancy and rout (Imperial Valley lettuce strike)"],
        "Pretrial detention; bailed about a week after arrest",
        "Arrested with Stanley Hancock during the Imperial Valley lettuce strike; booked at Brawley, then held at the Imperial County Jail. Bailed about a week after arrest. Exact arrest and release days unconfirmed.",
    ],
    [
        [1934,5,14], [1934,11,14],
        ["Imperial County Jail","El Centro","California"],
        ["Disturbing the peace (Imperial Valley strike organizing)"],
        "Convicted with Stanley Hancock; appeal failed",
        "Returned to the Imperial County Jail after her appeal failed and served the full six months, losing the good-time credit that let the male defendants leave after five.",
    ],
    [
        [1949,6,23], [1949,6,24],
        ["Federal custody, Los Angeles","Los Angeles","California"],
        ["Criminal contempt of court (refused federal grand-jury questions on Communist Party membership and records)"],
        "Sentenced to 18 months for contempt",
        "Held roughly one day and one night around June 23-24, 1949; Judge Pierson Hall denied bail but an appellate bail order was obtained almost immediately. The 18-month sentence was not served. Exact booking and release dates unconfirmed.",
    ],
    [
        [1951,7,26], [1951,12,7],
        ["Los Angeles County Jail","Los Angeles","California"],
        ["Smith Act conspiracy (California roundup, 1951)"],
        "Pretrial detention; initial bail set at 50,000 dollars",
        "Detained before trial in the California Smith Act roundup because bail was set at 50,000 dollars, held in the Los Angeles County Jail until the appellate court ordered reduced bail on December 7, 1951 (bond possibly completed shortly afterward).",
    ],
    [
        [1952,8,8], [1952,8,30],
        ["Los Angeles County Jail","Los Angeles","California"],
        ["Smith Act conspiracy"],
        "Convicted; sentenced to five years and fined 10,000 dollars",
        "Remanded immediately after sentencing and held 22 days until appellate bail; released by August 30, 1952. The five-year term was never served, the California Smith Act convictions being reversed by the Supreme Court (Yates v. United States) in 1957.",
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

echo "\nRecorded {$n} custody periods for Dorothy Ray Healey.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Dorothy Ray Healey custody periods recorded."
