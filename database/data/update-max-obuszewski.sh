#!/usr/bin/env bash
#
# Max Obuszewski — research update and data-bug fix.
#
# Fixes and additions (per the July 2026 deep-dive research):
#   - Name -> Maximilian J. "Max" Obuszewski (former name kept as aka).
#   - BUG: the 1999 Citizen Weapons Inspection case had an incarceration
#     date (1999-02-28) but NO release date, so the site computed an
#     open-ended ~27-year sentence. The actual sentence was 10 days; set
#     the served window to March 1-11, 1999 (10 days).
#   - Add the July 1994 / January 1995 APL leafleting case: 30 days in
#     jail (his longest verified term), reported MLK Day 1995.
#   - Add the 2005-2006 Maryland State Police covert-surveillance entry as
#     a no-criminal-charge case (labeled "Terrorism-Anti-War Protestors"
#     in a state intelligence database) — central to his significance.
#   - Suspended sentences (the 1990 one-day and the 2025 30-day, both
#     suspended) are deliberately NOT recorded as time served.
#   - Expand ideologies/affiliation; note the ~70-arrests-by-2007 figure
#     as his own estimate. Exact birthday left blank (only ~1944-1945 is
#     known), noted in the description.
#
# Idempotent (marker-guarded on the rewritten description).
# Run from the repo root:  bash database/data/update-max-obuszewski.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "max-obuszewski")->first();
if (! $p) {
    echo "max-obuszewski not found — nothing done.\n";
} elseif (str_contains((string) $p->description, "Maryland State Police")) {
    echo "Already updated — nothing done.\n";
} else {
    $p->name = "Maximilian J. \"Max\" Obuszewski";
    $p->first_name = "Maximilian";
    $p->last_name = "Obuszewski";
    $p->aka = "Max Obuszewski";
    $p->description = "Maximilian J. \"Max\" Obuszewski (born around 1944-1945) is a Baltimore-based peace activist arrested roughly seventy times — by his own estimate as of 2007 — across four decades of anti-war, anti-nuclear, draft-resistance, anti-death-penalty, Palestinian-solidarity and immigrant-rights actions. A former engineer and Peace Corps volunteer, he turned to organizing after the Kent State killings and moved to Baltimore in 1983 to work for Nuclear Free America, later joining the American Friends Service Committee, Maryland Peace Action and the Baltimore Nonviolence Center. He took part in five Plowshares-related disarmament actions, four of them alongside Philip Berrigan. Most of his arrests ended in citations, dismissals, probation or suspended sentences; his clearest jail terms were 30 days in 1995 and 10 days in 1999, both for actions at the Johns Hopkins University Applied Physics Laboratory. In 2005-2006 an undercover Maryland State Police officer infiltrated his groups and logged him in a regional intelligence database under classifications including \"Terrorism-Anti-War Protestors,\" making him a prominent example of post-September 11 surveillance treating nonviolent dissent as terrorism.";
    $ideo = $p->ideologies ?? [];
    foreach (["Anti-War", "Anti-nuclear", "Anti-death penalty", "Pro-Palestine movement", "Immigrant rights"] as $i) {
        if (! in_array($i, $ideo, true)) { $ideo[] = $i; }
    }
    $p->ideologies = $ideo;
    $aff = $p->affiliation ?? [];
    foreach (["Maryland Peace Action", "Baltimore Nonviolence Center", "American Friends Service Committee"] as $a) {
        if (! in_array($a, $aff, true)) { $aff[] = $a; }
    }
    $p->affiliation = $aff;
    $p->released = true;
    $p->in_custody = false;
    $p->save();

    // ---- fix the existing 1999 case (open-ended -> 10 days) ----
    $case = $p->cases()->get()->first(function ($c) {
        return str_contains(implode(" ", (array) $c->charges), "Citizen Weapons Inspection");
    });
    if ($case) {
        $case->charges = ["Trespass — Citizen Weapons Inspection at the Johns Hopkins University Applied Physics Laboratory (March 1, 1999)"];
        $case->arrest_date = "1999-03-01";
        $case->incarceration_date = "1999-03-01";
        $case->release_date = "1999-03-11";
        $case->imprisoned_for_days = 10;
        $case->convicted = "Yes";
        $case->sentence = "10 days in jail";
        $case->save();
        echo "FIXED 1999 case (10 days)\n";
    }

    // ---- add the 1994/1995 APL leafleting case (30 days, longest) ----
    $has1995 = $p->cases()->get()->contains(function ($c) {
        return str_contains(implode(" ", (array) $c->charges), "leaflet");
    });
    if (! $has1995) {
        \App\Models\PrisonerCase::create([
            "prisoner_id" => $p->id,
            "charges" => ["Distributing anti-weapons literature at the Johns Hopkins University Applied Physics Laboratory (July 20, 1994)"],
            "arrest_date" => "1994-07-20",
            "sentenced_date" => "1995-01-09",
            "incarceration_date" => "1995-01-16",
            "release_date" => "1995-02-15",
            "imprisoned_for_days" => 30,
            "convicted" => "Yes",
            "judge" => "Louis Becker",
            "sentence" => "30 days in jail (reported Martin Luther King Jr. Day 1995; fasted from solid food while confined) — his longest verified term",
        ]);
        echo "ADDED 1995 leafleting case (30 days)\n";
    }

    // ---- add the Maryland State Police surveillance entry ----
    $hasSurv = $p->cases()->get()->contains(function ($c) {
        return str_contains(implode(" ", (array) $c->charges), "surveillance");
    });
    if (! $hasSurv) {
        \App\Models\PrisonerCase::create([
            "prisoner_id" => $p->id,
            "charges" => ["No criminal charge — covert Maryland State Police surveillance (2005-2006); logged in a regional intelligence database under \"Terrorism-Anti Government\" and \"Terrorism-Anti-War Protestors\""],
            "arrest_date" => "2005-01-01",
            "convicted" => "No — never charged",
            "sentence" => "No prosecution; roughly 288 hours of undercover surveillance of nonviolent activists, later a major civil-liberties controversy examined by the ACLU and state officials",
        ]);
        echo "ADDED Maryland State Police surveillance entry\n";
    }

    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
    echo "UPDATED max-obuszewski\n";
}
echo "Done.\n";
'

echo
echo "Done. Max Obuszewski record corrected (1999 sentence fixed; 1995 term and surveillance entry added)."
