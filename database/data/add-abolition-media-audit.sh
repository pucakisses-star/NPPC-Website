#!/usr/bin/env bash
#
# Abolition Media archive audit (July 2026). Of the 113 U.S.-linked names in
# the archive's political-prisoner coverage, ~97 were already in the
# database (including all the historical Black-liberation, San Quentin Six,
# Holy Land Five, Prairieland-16 and exile figures). This script covers the
# gaps:
#
#  1. Merges the Mohammad / Mohamed El-Mezain duplicate pair.
#  2. Adds eleven missing people (current prisoners, recent Prairieland
#     defendants, and historical figures), each framed per the sources.
#  3. Alias fixes: Malik Fard Muhammad gains the archive's "Farrad"
#     spelling; Joshua Harper gains "Josh Harper"; the three new
#     Prairieland defendants gain the committee website.
#
# Not added, with reasons noted in the PR: Farhan Ahmed and Hector "Bori"
# Rodriguez (no documented political element to the underlying cases),
# and the first-name-only defendants (Charley, Krystal, Peppy, Wednesday).
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/add-abolition-media-audit.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=mohammad-el-mezain --apply

php artisan prisoner:add '{"name":"Brian Simpson","first_name":"Brian","last_name":"Simpson","aka":"Hakiym Sha'"'"'ir","description":"Brian \"Hakiym Sha'"'"'ir\" Simpson is a Black father, wildland firefighter, community leader and poet imprisoned in Oregon for defending himself against a racist attack. At his fire company'"'"'s barracks he was pursued and punched first by a coworker shouting racial slurs — as the attacker himself testified — and Simpson'"'"'s return punches broke the man'"'"'s nose and jaw. He was convicted of second-degree assault and sentenced to 70 months, a prosecution his supporters compare to other cases of survivors of racist violence punished for self-defense. He has continued writing poetry from prison.","state":"Oregon","race":"Black","gender":"Male","era":"2020s","in_custody":true,"cases":[{"charges":"Assault in the second degree — for defending himself against a coworker'"'"'s racist attack","convicted":"Yes","sentence":"70 months"}]}' || true

php artisan prisoner:add '{"name":"Julio Zuniga","first_name":"Julio","last_name":"Zuniga","aka":"Comrade Z","description":"Julio A. \"Comrade Z\" Zuniga, from San Antonio, is an anarchist prisoner-organizer in the Texas prison system, an Incarcerated Workers Organizing Committee member who has organized work stoppages against unpaid prison labor, written widely from inside, and spent long stretches in solitary confinement in retaliation — much of his 2018-2023 time at the Darrington/Memorial Unit was spent in isolation before a transfer to the Ramsey Unit. In April 2024 the parole board denied his release, setting him back roughly four and a half more years.","state":"Texas","race":"Hispanic","gender":"Male","ideologies":["Anarchism","Prison abolition"],"affiliation":["Incarcerated Workers Organizing Committee (IWOC)"],"era":"2020s","in_custody":true,"cases":[{"charges":"Serving a lengthy Texas sentence (underlying conviction predates his politicization); repeatedly placed in solitary confinement in retaliation for prison organizing","convicted":"Yes"}]}' || true

php artisan prisoner:add '{"name":"Monsour Owolabi","first_name":"Monsour","last_name":"Owolabi","description":"Monsour Owolabi is a New Afrikan poet, writer and prisoner-organizer serving life without parole in Texas since 2011 on a murder conviction he contests. Politicized inside, he has organized around prison slave labor and solidarity campaigns, published essays and poetry (Scalawag, Texas Letters), and been held in prolonged isolation that supporters describe as retaliation for his organizing.","state":"Texas","race":"Black","gender":"Male","ideologies":["New Afrikan independence","Prison abolition"],"era":"2010s","in_custody":true,"cases":[{"charges":"Murder (conviction contested); held in prolonged solitary confinement that supporters attribute to his prison organizing","convicted":"Yes","sentence":"Life without parole","incarceration_date":"2011-01-01"}]}' || true

php artisan prisoner:add '{"name":"Mohammed Sabry Soliman","first_name":"Mohammed","middle_name":"Sabry","last_name":"Soliman","description":"Mohammed Sabry Soliman is an Egyptian national charged in the June 1, 2025 firebomb attack on a Run for Their Lives hostage-remembrance walk in Boulder, Colorado, in which more than a dozen people were burned and 82-year-old Karen Diamond later died of her injuries. He faces federal hate-crime counts and state murder charges. He is included here as the advocacy press covers his pretrial detention; the record states the violence plainly — this was an attack on civilians, and the political dimension is the prosecution'"'"'s hate-crime framing and his stated motive.","state":"Colorado","gender":"Male","era":"2020s","in_custody":true,"awaiting_trial":true,"cases":[{"charges":"Federal hate-crime counts and Colorado murder charges — the June 1, 2025 Boulder firebomb attack that injured more than a dozen and killed Karen Diamond","arrest_date":"2025-06-01","incarceration_date":"2025-06-01","convicted":"Pretrial"}]}' || true

php artisan prisoner:add '{"name":"Hasan Shakur","first_name":"Hasan","last_name":"Shakur","aka":"Derrick Wayne Frazier","description":"Hasan Shakur, born Derrick Wayne Frazier, was executed by Texas on August 31, 2006 for the 1997 double murder of Betsy and Cody Nutt — a conviction his defenders contested to the end, resting on a confession his supporters describe as coerced, with no physical evidence placing him at the scene, before a nearly all-white jury. On death row he transformed himself into the Minister of Human Rights of the New Afrikan Black Panther Party, organizing and writing until his execution; his last words were of the struggle.","state":"Texas","race":"Black","gender":"Male","death_date":"2006-08-31","ideologies":["New Afrikan independence"],"affiliation":["New Afrikan Black Panther Party"],"era":"2000s","released":false,"cases":[{"charges":"Capital murder (1997 double murder; conviction contested — coerced confession, no physical evidence)","convicted":"Yes — executed August 31, 2006","sentence":"Death"}]}' || true

php artisan prisoner:add '{"name":"Khatari Gaulden","first_name":"Khatari","last_name":"Gaulden","aka":"Jeffrey Gaulden","description":"Jeffrey \"Khatari\" Gaulden was a comrade of George Jackson in the San Quentin wing of the Black prison movement and a leader of its study and organizing formations after Jackson'"'"'s death. On August 1, 1978 he died at San Quentin after a head injury on the prison yard went hours without adequate medical care — a death fellow prisoners charged to deliberate neglect. Black August, the month of study, fasting and discipline observed in the prison movement ever since, was founded in large part in his memory alongside Jackson'"'"'s.","state":"California","race":"Black","gender":"Male","death_date":"1978-08-01","ideologies":["Black liberation"],"era":"1970s","released":false,"cases":[{"institution_name":"San Quentin State Prison","institution_city":"San Quentin","institution_state":"California","charges":"Imprisoned in the California system, where he became a leader of the Black prison movement","convicted":"Yes","death_in_custody_date":"1978-08-01"}]}' || true

php artisan prisoner:add '{"name":"Khalid Raheem","first_name":"Khalid","last_name":"Raheem","description":"Khalid Raheem (died February 14, 2026) joined the Philadelphia chapter of the Black Panther Party in 1970 and was arrested and incarcerated for more than ten years. After his release he became a fixture of Pittsburgh community life — counselor, youth worker, author of Toward a New Afrikan Revolution, and an organizer across the New Afrikan Independence Party, the National Council for Urban Peace and Justice, the Jericho Movement and the gang-truce movement.","state":"Pennsylvania","race":"Black","gender":"Male","death_date":"2026-02-14","ideologies":["Black liberation","New Afrikan independence"],"affiliation":["Black Panther Party","Jericho Movement"],"era":"1970s","released":true,"cases":[{"charges":"Prosecuted as a Philadelphia Black Panther in the early 1970s; imprisoned more than ten years","convicted":"Yes","sentence":"More than ten years served"}]}' || true

php artisan prisoner:add '{"name":"Hybachi LeMar","first_name":"Hybachi","last_name":"LeMar","description":"Hybachi LeMar is a Chicago anarchist organizer and writer — active with the Incarcerated Workers Organizing Committee, the Black Autonomy Federation and Englewood mutual-aid projects — who came to anarchism through a letter from the South Chicago Anarchist Black Cross during more than a year in solitary confinement in an earlier imprisonment. Arrested again in May 2023, he was held in Illinois and then extradited to the Pennsylvania prison system, maxed out his sentence, and was released in 2026, returning to organizing in Chicago. He is the author of three collections of essays.","state":"Illinois","race":"Black","gender":"Male","ideologies":["Anarchism","Prison abolition"],"affiliation":["Incarcerated Workers Organizing Committee (IWOC)","Black Autonomy Federation"],"era":"2020s","released":true,"cases":[{"charges":"Held in Illinois and Pennsylvania state custody from his May 2023 arrest until maxing out his sentence","arrest_date":"2023-05-01","convicted":"Yes — served to max-out; released 2026"}]}' || true

php artisan prisoner:add '{"name":"Dario Sanchez","first_name":"Dario","last_name":"Sanchez","description":"Dario Sanchez is one of the later-arrested defendants in the Prairieland prosecutions that grew out of the July 4, 2025 noise demonstration at the Prairieland ICE detention center in Alvarado, Texas. Not alleged to have been present that night, he is accused of evidence tampering for removing a person from Signal and Discord chats, and has been arrested three times in connection with the case.","state":"Texas","gender":"Male","era":"2020s","awaiting_trial":true,"website":"https://prairielanddefendants.com/","cases":[{"charges":"Evidence tampering — accused of removing a person from group chats after the July 4, 2025 Prairieland demonstration; not alleged to have been present at the protest","convicted":"Pretrial"}]}' || true

php artisan prisoner:add '{"name":"Janette Goering","first_name":"Janette","last_name":"Goering","description":"Janette Goering is the eighteenth person arrested in the Prairieland prosecutions that grew out of the July 4, 2025 noise demonstration at the Prairieland ICE detention center in Alvarado, Texas. Not alleged to have been present that night, she is charged with aiding in the commission of terrorism for giving someone a Faraday signal-blocking bag weeks before the action.","state":"Texas","gender":"Female","era":"2020s","awaiting_trial":true,"website":"https://prairielanddefendants.com/","cases":[{"charges":"Aiding in the commission of terrorism — for giving a co-defendant a Faraday bag weeks before the July 4, 2025 Prairieland demonstration; not alleged to have been present","convicted":"Pretrial"}]}' || true

php artisan prisoner:add '{"name":"Lucy Fowlkes","first_name":"Lucy","last_name":"Fowlkes","description":"Lucy Fowlkes, of Weatherford, Texas, was arrested at her home on January 5, 2026 — the nineteenth Prairieland defendant — in an operation involving FBI agents although she faces only state charges. Not alleged to have participated in the July 4, 2025 noise demonstration at the Prairieland ICE detention center, she was charged with hindering the prosecution of terrorism after refusing to assist prosecutors.","state":"Texas","gender":"Female","era":"2020s","awaiting_trial":true,"website":"https://prairielanddefendants.com/","cases":[{"charges":"Hindering prosecution of terrorism — charged after refusing to assist prosecutors in the Prairieland case; not alleged to have participated in the July 4, 2025 demonstration","arrest_date":"2026-01-05","convicted":"Pretrial"}]}' || true

# --- Alias and precision fixes --------------------------------------------
php artisan tinker --execute='
$aliases = [
    "malik-fard-muhammad" => "Malik Farrad Muhammad",
    "joshua-harper" => "Josh Harper",
];
foreach ($aliases as $slug => $aka) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if ($p && empty($p->aka)) { $p->aka = $aka; $p->save(); echo "AKA {$slug}\n"; }
}
// Monsour Owolabi: incarcerated since 2011, exact date unknown — year precision.
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "monsour-owolabi")->first();
if ($p) {
    $case = $p->cases()->first();
    if ($case && $case->incarceration_date && $case->formatPartialDate("incarceration_date") !== "2011") {
        $case->date_precision = array_merge($case->date_precision ?? [], ["incarceration_date" => "year"]);
        $case->save();
        echo "CASE monsour-owolabi incarceration precision = year\n";
    }
}
// Hybachi LeMar: arrested May 2023, exact day unknown — month precision.
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "hybachi-lemar")->first();
if ($p) {
    $case = $p->cases()->first();
    if ($case && $case->arrest_date && $case->formatPartialDate("arrest_date") !== "May 2023") {
        $case->date_precision = array_merge($case->date_precision ?? [], ["arrest_date" => "month"]);
        $case->save();
        echo "CASE hybachi-lemar arrest precision = month\n";
    }
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Abolition Media audit changes applied."
