#!/usr/bin/env bash
#
# ONE RECORD, TWO PEOPLE. christian-damian-cerno-camacho is named for one
# man and describes another.
#
# The record is titled “Christian Damian Cerno-Camacho”. Its case row
# charges assault on a federal officer (CBP), which is the right charge
# for that man. Its description, however, is somebody else entirely:
#
#   “20-year-old U.S. citizen and Walmart employee arrested June 17, 2025
#   in Pico Rivera, California ... bystander video circulated publicly
#   disputed the government’s account.”
#
# Every specific in that sentence -- the age, the job, the date, the
# town, the disputed video -- belongs to ADRIAN ANDREW MARTINEZ, a
# different defendant in a different Los Angeles County case. None of it
# belongs to Cerna-Camacho, who was 28, from Boyle Heights, and arrested
# on June 11.
#
# Two men, two arrests, six days and eleven miles apart, both caught up in
# the June 2025 federal immigration operation in Los Angeles. They were
# merged into a single row.
#
# THE NAME IS ALSO MISSPELLED: Cerno-Camacho should be Cerna-Camacho.
# Every source spells it Cerna, including the Department of Homeland
# Security release about his arrest and the US Attorney announcements.
#
#   NOTE THE SLUG CHANGES. The Prisoner model regenerates the slug when
#   the name is dirty, so /prisoner/christian-damian-cerno-camacho
#   becomes /prisoner/christian-damian-cerna-camacho and the old URL
#   stops resolving. That is accepted deliberately: a database of named
#   people should spell the names right, and the record carries no photo
#   file keyed to the old slug. The script prints both slugs.
#
# WHAT CERNA-CAMACHO ACTUALLY DID, and what was done to him. He was
# accused of punching a Customs and Border Protection officer during the
# June 7, 2025 protest against immigration raids in Paramount. He was not
# arrested there. Agents came for him four days later, on June 11, in
# Boyle Heights, pinning his car between two unmarked vehicles on
# Whittier Boulevard with guns drawn and deploying a chemical device
# while his wife and their two young children were inside. DHS then put
# its own high-resolution video of the operation on social media, calling
# him a “violent rioter” -- footage which showed him surrendering at
# once.
#
# THE OUTCOME IS THE OPPOSITE OF WHAT THE RECORD IMPLIED. The row says
# “Case pending as of 2026.” In fact he pleaded guilty on December 16,
# 2025 to felony assault on a federal officer, carrying up to eight
# years; at sentencing in 2026 a federal judge called the department’s
# publicity campaign a “vindictive effort” and “extrajudicial
# punishment”, the felony was reduced to a misdemeanor count of simple
# assault, and he received probation rather than prison.
#
# THE ONE SOFT DATE, disclosed rather than hidden. Reporting says he was
# released after about a week in custody, to house arrest with GPS
# monitoring; no source names the day. Bail was refused at his first
# appearance and a preliminary hearing was set for June 26. The release
# is therefore recorded as 2025-06-18, one week after the June 11 arrest,
# which yields the seven days the sources describe. It is the only date
# here not taken directly from a source, and it is a day-level guess
# inside a well-sourced week. Month precision cannot be used instead:
# it would resolve to June 1, before the arrest, and break the counter.
#
# NOT SET: sentenced_date. Sentencing was scheduled for March 25, 2026,
# but later reporting describes the GPS monitoring as running “prior to
# final sentencing”, so the operative date may be later and no source
# states it plainly. Left empty rather than guessed.
#
# NOT SET: age or birthdate for either man. Each has exactly one reported
# age against one date -- 28 for Cerna-Camacho in June 2025, 20 for
# Martinez in June 2025 -- and a single age anchor gives a two-year
# window, not a year. Both ages are stated in the prose with the date
# they attach to, so they cannot drift into looking current.
#
# MARTINEZ IS CREATED as his own record, in scope on the same footing as
# the rest of the June 2025 Los Angeles arrests already in the database.
# He was jailed for roughly three days for stopping to confront Border
# Patrol agents in a Walmart parking lot; the US Attorney publicly said
# he had punched an officer in the face; no assault charge was ever
# filed, and the felony he was indicted on in August 2025 is conspiracy
# to impede a federal officer. He is flagged released and awaiting trial,
# which is the state the last available reporting leaves him in.
#
# prisoner:add refuses to create a duplicate name, so re-running this is
# safe: the second run reports that Adrian Andrew Martinez already exists
# and changes nothing. The Cerna-Camacho half is guarded field by field
# and looks up both the old and the new slug, so it is idempotent too.
#
# The payloads use curly apostrophes and curly quotation marks
# throughout, so they contain no straight quotes and are safe inside
# single-quoted shell arguments.
#
# Run from the repo root:
#   bash database/data/split-cerna-camacho-martinez.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

echo "--- 1/2: correcting the Cerna-Camacho record"

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()
    ->whereIn("slug", ["christian-damian-cerno-camacho", "christian-damian-cerna-camacho"])
    ->with("cases")
    ->first();

if (! $p) {
    echo "NOT FOUND: neither cerno- nor cerna-camacho slug — nothing changed.\n";
    return;
}

echo "  found: ", $p->slug, "  (", $p->name, ")\n";

$bio = "Christian Damian Cerna-Camacho, a United States citizen from Boyle Heights in Los Angeles, was 28 when federal prosecutors accused him of punching a Customs and Border Protection officer during the June 7, 2025 protest against immigration raids in Paramount, California.\n\nHe was not arrested at the protest. Agents came for him four days later, on June 11, 2025, pinning his car between two unmarked vehicles on Whittier Boulevard in Boyle Heights with guns drawn and deploying a chemical device while his wife and their two young children were inside. The Department of Homeland Security then published its own high-resolution video of the operation on social media, calling him a “violent rioter” — footage which in fact showed him surrendering at once. Bail was refused at his first appearance, and he spent about a week in custody before being released to house arrest with GPS monitoring.\n\nHe pleaded guilty on December 16, 2025 to assault on a federal officer, a felony carrying up to eight years. At sentencing in 2026 the court rejected that framing. A federal judge described the department’s publicity campaign as a “vindictive effort” and “extrajudicial punishment”, the felony was reduced to a misdemeanor count of simple assault on a federal officer, and he received probation rather than prison.";

$notes = [];

if ($p->name !== "Christian Damian Cerna-Camacho") {
    $p->name = "Christian Damian Cerna-Camacho";
    $notes[] = "name Cerno -> Cerna (slug regenerates)";
}

if ($p->first_name !== "Christian") {
    $p->first_name = "Christian";
    $notes[] = "first_name";
}

if ($p->middle_name !== "Damian") {
    $p->middle_name = "Damian";
    $notes[] = "middle_name";
}

if ($p->last_name !== "Cerna-Camacho") {
    $p->last_name = "Cerna-Camacho";
    $notes[] = "last_name";
}

if ($p->description !== $bio) {
    $p->description = $bio;
    $notes[] = "description replaced (was Martinez)";
}

if ($notes) {
    $p->save();
}

echo "  prisoner: ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
echo "  slug now: ", $p->slug, "\n";

$case = $p->cases->first();

if (! $case) {
    echo "  NO CASE ROW — dates not corrected.\n";
} else {
    $caseNotes = [];

    if (! $case->arrest_date || $case->arrest_date->format("Y-m-d") !== "2025-06-11") {
        $case->setPartialDate("arrest_date", 2025, 6, 11);
        $caseNotes[] = "arrest -> 2025-06-11 (was Martinez date 06-17)";
    }

    if (! $case->incarceration_date) {
        $case->setPartialDate("incarceration_date", 2025, 6, 11);
        $caseNotes[] = "incarceration 2025-06-11";
    }

    if (! $case->release_date) {
        $case->setPartialDate("release_date", 2025, 6, 18);
        $caseNotes[] = "release 2025-06-18 (one week, see header)";
    }

    if ($case->convicted !== "Yes") {
        $case->convicted = "Yes";
        $caseNotes[] = "convicted Yes";
    }

    if (! $case->plead) {
        $case->plead = "Guilty";
        $caseNotes[] = "plead Guilty";
    }

    $charges = "Assault on a federal officer under 18 U.S.C. 111, for allegedly punching a Customs and Border Protection officer during the June 7, 2025 protest in Paramount, California. Reduced before sentencing to a misdemeanor count of simple assault on a federal officer.";

    if ($case->charges !== $charges) {
        $case->charges = $charges;
        $caseNotes[] = "charges expanded";
    }

    $sentence = "Probation, with no prison term. He pleaded guilty on December 16, 2025 to felony assault on a federal officer, which carried up to eight years. At sentencing in 2026 a federal judge described the Department of Homeland Security publicity campaign around his arrest as a “vindictive effort” and “extrajudicial punishment”; the felony was reduced to a misdemeanor count of simple assault, and he was sentenced to probation. He had been held for about a week after the June 11, 2025 arrest and was then on house arrest with GPS monitoring.";

    if ($case->sentence !== $sentence) {
        $case->sentence = $sentence;
        $caseNotes[] = "sentence text replaced (was: case pending)";
    }

    if ($caseNotes) {
        $case->save();
    }

    echo "  case:     ", ($caseNotes ? implode("; ", $caseNotes) : "already correct"), "\n";
    echo "  imprisoned_for_days: ", ($case->imprisoned_for_days === null ? "null" : $case->imprisoned_for_days), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "  cache cleared.\n";
'

echo
echo "--- 2/2: creating the Adrian Andrew Martinez record"

php artisan prisoner:add '{"name": "Adrian Andrew Martinez", "first_name": "Adrian", "middle_name": "Andrew", "last_name": "Martinez", "description": "Adrian Andrew Martinez was a 20-year-old United States citizen and Walmart employee in Pico Rivera, California, when Border Patrol agents arrested him in the parking lot of the Pico Rivera Towne Center on June 17, 2025.\n\nHe was on a break from work. Agents were detaining a man they believed to be in the country illegally; Martinez stopped his car, got out and confronted them. According to the indictment he positioned his vehicle so as to block the agents from leaving and moved a large trash can in front of it, while a crowd of other drivers also stopped and partly blocked the lanes of the parking lot. His own arrest was violent, and he was held for roughly three days before a federal magistrate released him on a $5,000 bond.\n\nBill Essayli, the United States Attorney for the Central District of California, said publicly that Martinez had punched a Border Patrol officer in the face. No assault charge was ever filed. His lawyers said the security footage showed him striking nobody, and a Border Patrol agent later said publicly that a false narrative had been pushed about the arrest. A federal grand jury indicted him on August 20, 2025 on a single count of conspiracy to impede a federal officer, which carries a statutory maximum of six years in federal prison. Asked afterwards whether he regretted intervening, he said he would do it again.\n\nHis case was for a time conflated in this database with that of Christian Damian Cerna-Camacho, a different man arrested six days earlier and eleven miles away in the same federal operation in Los Angeles County. The two records are now separate.", "state": "California", "race": "Hispanic", "gender": "Male", "era": "2020s", "ideologies": ["Anti-ICE", "Immigrant Rights"], "in_custody": false, "released": true, "awaiting_trial": true, "cases": [{"charges": "Conspiracy to impede a federal officer, 18 U.S.C. 372 — blocking Border Patrol agents who were making an arrest in a Pico Rivera parking lot. Prosecutors publicly alleged that he had punched an officer in the face, but no assault charge was ever filed.", "arrest_date": "2025-06-17", "incarceration_date": "2025-06-17", "release_date": "2025-06-20", "indicted": "Yes — single-count federal grand-jury indictment for conspiracy to impede a federal officer, Central District of California, returned August 20, 2025", "prosecutor": "Bill Essayli, United States Attorney for the Central District of California", "sentence": "Unresolved as of the latest available reporting; the single count carries a statutory maximum of six years in federal prison. He was held for roughly three days after his arrest and released on a $5,000 bond following a federal hearing."}]}'

echo
echo "Done."
