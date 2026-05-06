#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cecily_mcmillan_case.sh
#
# Adds a case row to Cecily McMillans existing prisoner record.
# She was an Airtable import with no attached case data, so the
# public page had no Imprisoned For section.
#
# Source-derived facts:
#   - Arrested March 17, 2012 at Zuccotti Park during the six-month
#     anniversary of the Occupy Wall Street encampment, when NYPD
#     cleared the plaza. She elbowed an officer in what her defense
#     described as an instinctive reaction to a breast-grab from
#     behind by an officer who later was the prosecutions main
#     witness.
#   - Convicted May 5 2014 of felony second-degree assault on a
#     police officer in New York Supreme Court, New York County.
#   - Sentenced May 19 2014 by Judge Ronald A. Zweibel to 90 days
#     in jail (including time served) plus 5 years probation.
#   - Held at Rose M. Singer Center on Rikers Island; released on
#     July 2 2014 after serving 58 days.
#
# Sources: Wikipedia (Cecily McMillan); The Village Voice; Democracy
# Now!; Common Dreams; New York Daily News.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Cecily McMillan")->first();
if (!$p) {
    echo "ERROR: Cecily McMillan not found\n";
    exit(1);
}

if ($p->cases()->exists()) {
    echo "Cecily McMillan already has cases (count={$p->cases()->count()}); skipping.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Rose M. Singer Center, Rikers Island"],
    ["city" => "East Elmhurst", "state" => "New York"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "New York state felony second-degree assault on a police officer (NYPD), arising from her March 17, 2012 arrest at Zuccotti Park during the six-month anniversary of the Occupy Wall Street encampment. McMillans defense argued the elbow was an instinctive reaction to a breast-grab from behind by an undercover officer who later was the prosecutions main witness; the jury convicted on May 5, 2014.",
    "arrest_date"        => "2012-03-17",
    "incarceration_date" => "2014-05-05",
    "sentenced_date"     => "2014-05-19",
    "release_date"       => "2014-07-02",
    "sentence"           => "90 days at the Rose M. Singer Center on Rikers Island (including time served) plus 5 years probation. Held in custody from the May 5, 2014 conviction; released July 2, 2014 after serving 58 days.",
    "convicted"          => "Yes - New York Supreme Court, New York County jury verdict, May 5, 2014",
    "judge"              => "Ronald A. Zweibel",
]);

echo "Inserted Cecily McMillan case (2012-2014, NY state assault, Rikers).\n";
'

echo
echo "Cecily McMillan case add complete."
