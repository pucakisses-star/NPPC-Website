#!/usr/bin/env bash
#
# Harry Eisman — full research update.
#
# Replaces the vague single case ("Arrested and persecuted from 1928...")
# with the documented arrest-by-arrest record, and fills in biography:
#
#   Harry Eduardovich Eisman, b. November 26, 1913, Kishinev/Chisinau
#   (then Russian Empire); d. May 6, 1979, Moscow; buried Donskoye
#   Cemetery. (Some sources give December 2, 1915, but the Soviet military
#   record gives 1913 and a 1929 Children's Court report described him as
#   fourteen.)
#
#   Arrests: May 1 1928 (PS 61 May Day leafleting), Feb 14 1929 (concealed-
#   weapon booking over a reported jack handle), Feb 25 1929 (dressmakers'
#   strike picketing), May 1 1929 (May Day), May 18 1929 (Workers Center
#   protest; overnight via SPCC, released to his brother May 19), June 27
#   1929 (fur workers' strike; released ~July 1-2), July 20 1929 (Samaria
#   Boy Scout jamboree protest; sentenced Aug 2 1929 to six months at the
#   Hawthorne Jewish Reform School for Boys, released early January 1930),
#   and March 6 1930 (Union Square unemployment demonstration; recommitted
#   to Hawthorne under a commitment reported as five years or more,
#   released November 15, 1930; sailed for the Soviet Union November 17).
#
# Idempotent (marker-guarded on the rewritten description).
#
# Run from the repo root:  bash database/data/update-harry-eisman.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "harry-eisman")->first();
if (! $p) {
    echo "harry-eisman not found — nothing done.\n";
} elseif (str_contains((string) $p->description, "Hawthorne")) {
    echo "Already updated — nothing done.\n";
} else {
    $p->aka = "Harry Eduardovich Eisman";
    $p->birthdate = "1913-11-26";
    $p->death_date = "1979-05-06";
    $p->description = "Harry Eduardovich Eisman, born November 26, 1913 in Kishinev (Chisinau), then in the Russian Empire, was a Bronx schoolboy and Young Pioneer whose repeated arrests made him the best-known child political prisoner in America. Between May Day 1928 and March 1930 he was arrested at least eight times: distributing May Day literature outside Public School 61, a felony concealed-weapon booking over what was reportedly an automobile-jack handle, mass picketing in the dressmakers'"'"' and fur workers'"'"' strikes, Young Pioneer May Day demonstrations, a police-brutality protest at the Workers Center, and a waterfront demonstration against Boy Scouts sailing aboard the Samaria to an international jamboree, for which the Children'"'"'s Court sent him to six months in the Hawthorne Jewish Reform School for Boys. After the March 6, 1930 Union Square unemployment demonstration he was recommitted to Hawthorne under a commitment reported as five years or more, or until his twenty-first birthday. Released on November 15, 1930 under an arrangement allowing him to leave the country, he sailed for the Soviet Union via Germany on November 17, 1930. He died in Moscow on May 6, 1979 and is buried at Donskoye Cemetery. Some sources give December 2, 1915 as his birth date, but his Soviet military record gives November 26, 1913, and a 1929 Children'"'"'s Court report describing him as fourteen supports the 1913 date.";
    $p->save();

    $p->cases()->delete();

    $hawthorne = \App\Models\Institution::firstOrCreate(
        ["name" => "Hawthorne Jewish Reform School for Boys"],
        ["city" => "Hawthorne", "state" => "New York"]
    );

    $mk = function (array $data) use ($p) {
        $data["prisoner_id"] = $p->id;
        \App\Models\PrisonerCase::create($data);
    };

    $mk([
        "charges" => "No formal criminal charges — Children'"'"'s Court juvenile proceedings (May Day leafleting outside Public School 61, the Bronx)",
        "arrest_date" => "1928-05-01",
        "release_date" => "1928-05-01",
        "sentence" => "Briefly held via the Society for the Prevention of Cruelty to Children; released May 1, 1928 or shortly afterward",
    ]);
    $mk([
        "charges" => "Felony concealed-weapon booking (the object was reportedly an automobile-jack handle; no prosecution located)",
        "arrest_date" => "1929-02-14",
        "sentence" => "No jail or reformatory sentence located",
    ]);
    $mk([
        "charges" => "No formal criminal charges recovered — mass picketing in the New York dressmakers'"'"' strike",
        "arrest_date" => "1929-02-25",
        "sentence" => "Brief police detention followed by juvenile proceedings",
    ]);
    $mk([
        "charges" => "No formal criminal charges recovered — Children'"'"'s Court juvenile case (Young Pioneer May Day demonstration)",
        "arrest_date" => "1929-05-01",
    ]);
    $mk([
        "charges" => "No formal criminal charges recovered — Young Pioneer demonstration against police brutality at the Workers Center, Union Square",
        "arrest_date" => "1929-05-18",
        "release_date" => "1929-05-19",
        "sentence" => "Held overnight via the Society for the Prevention of Cruelty to Children; released into his brother'"'"'s custody about 3:30 p.m., May 19, 1929",
    ]);
    $mk([
        "charges" => "No formal criminal charges recovered — picketing in the fur workers'"'"' strike with Young Pioneers and adult workers",
        "arrest_date" => "1929-06-27",
        "release_date" => "1929-07-01",
        "sentence" => "Released by approximately July 1-2, 1929",
    ]);
    $mk([
        "charges" => "Disorderly conduct; interfering with a police officer (Children'"'"'s Court, July 22, 1929 — waterfront protest against Boy Scouts departing aboard the Samaria)",
        "arrest_date" => "1929-07-20",
        "convicted" => "Yes — Children'"'"'s Court",
        "sentenced_date" => "1929-08-02",
        "incarceration_date" => "1929-08-02",
        "sentence" => "Six months at the Hawthorne Jewish Reform School for Boys (sentenced August 2, 1929); released early, January 1930 — out by February 4",
        "imprisoned_for_days" => 150,
        "institution_id" => $hawthorne->id,
    ]);
    $mk([
        "charges" => "Juvenile delinquency and truancy proceedings (March 6, 1930 Union Square unemployment demonstration)",
        "arrest_date" => "1930-03-06",
        "convicted" => "Yes — recommitted to Hawthorne",
        "release_date" => "1930-11-15",
        "sentence" => "Recommitment reported as five years (variously five and a half, nearly six, or until his twenty-first birthday); served about six months; released November 15, 1930 under an arrangement to leave for the Soviet Union — sailed November 17, 1930 via Germany",
        "imprisoned_for_days" => 184,
        "institution_id" => $hawthorne->id,
    ]);

    echo "UPDATED harry-eisman: bio + 8 cases (replaced 1 vague case)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Harry Eisman record updated."
