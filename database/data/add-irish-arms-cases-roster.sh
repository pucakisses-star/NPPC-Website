#!/usr/bin/env bash
#
# Irish republican arms-case defendants missing from the database, across eight
# United States prosecutions:
#
#   Brooklyn arms smuggling, 1982-83   Andrew Duggan, Eamon Meehan, Colm Meehan
#   Noel O Murchu case, 1986-88        Ciarin Hughes
#   Florida Stinger sting, 1990-93     Kevin McKinley, Seamus Moley
#   NORAID arms case, 1970-78          Neil Byrne, Daniel Cahalane
#   Grady-Jankowski case, 1971-76      Frank Grady, John Jankowski
#   North Carolina gun-running, 1980   Howard B. Brutton Jr., Robert Ferraro,
#                                      George DeMeo
#   Valhalla / Marita Ann, 1984-87     Joseph Paul Murray Jr., Patrick Nee,
#                                      Robert Andersen
#   Maze-escape extraditions           James Joseph Smyth, Pol Brennan,
#                                      Terence Damien Kirby
#
# DATE POLICY: only the dates the source documents are entered. The three
# extradition detainees have datable custody (Smyth from 1992, Brennan January
# 1993 to January 1996, Kirby February 1994 to January 1996, at the precision
# stated); everyone else has a sentence on record but no documented custody
# dates, so their cases carry the sentence text with NO dates rather than a
# guess. Every record is flagged released so a missing release date cannot
# produce a count-to-today duration.
#
# Idempotent -- updates rather than duplicates. Run from the repo root:
#   bash database/data/add-irish-arms-cases-roster.sh
# then place the new records:
#   php artisan prisoners:auto-place-zero-sort

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// [name, aka, first, last, era, charges, sentence,
//  inc [y,m,d]|null, rel [y,m,d]|null, bio]
$roster = [
    // --- Brooklyn arms-smuggling case, 1982-83 ---
    ["Andrew Duggan", null, "Andrew", "Duggan", "1980s",
     "Conspiracy to smuggle arms to the Irish Republican Army — the Brooklyn arms case.",
     "Concurrent terms totalling three years.", null, null,
     "Andrew Duggan was convicted in the Brooklyn arms-smuggling case of 1982-83 alongside Gabriel Megahey, Eamon Meehan and Colm Meehan, for conspiring to obtain and ship weapons to the Irish Republican Army. He received concurrent terms totalling three years; Megahey received five."],
    ["Eamon Meehan", null, "Eamon", "Meehan", "1980s",
     "Conspiracy to smuggle arms to the Irish Republican Army — the Brooklyn arms case.",
     "Concurrent terms totalling three years.", null, null,
     "Eamon Meehan was convicted in the Brooklyn arms-smuggling case of 1982-83 alongside Gabriel Megahey, Andrew Duggan and Colm Meehan, for conspiring to obtain and ship weapons to the Irish Republican Army. He received concurrent terms totalling three years."],
    ["Colm Meehan", null, "Colm", "Meehan", "1980s",
     "Conspiracy to smuggle arms to the Irish Republican Army — the Brooklyn arms case.",
     "Two years.", null, null,
     "Colm Meehan was convicted in the Brooklyn arms-smuggling case of 1982-83 alongside Gabriel Megahey, Andrew Duggan and Eamon Meehan, for conspiring to obtain and ship weapons to the Irish Republican Army. He received a two-year sentence."],

    // --- Noel O Murchu case, 1986-88 ---
    ["Ciarin Hughes", "Ciaran Hughes", "Ciarin", "Hughes", "1980s",
     "Conspiracy to export weapons for use by the Irish Republican Army, and federal firearms offences.",
     "Convicted on the conspiracy and firearms counts.", null, null,
     "Ciarin Hughes, also rendered Ciaran Hughes, was the co-defendant of Noel O Murchu in the 1986-88 prosecution over an arms pipeline to the Irish Republican Army. He was convicted of conspiracy to export weapons for IRA use and of federal firearms offences."],

    // --- Florida Stinger-missile sting, 1990-93 ---
    ["Kevin McKinley", null, "Kevin", "McKinley", "1990s",
     "Conspiracy to obtain and export a Stinger missile and other weapons for use against British helicopters in Northern Ireland.",
     "Fifty-one months. The appellate court vacated the sentences of all three defendants and ordered resentencing.", null, null,
     "Kevin McKinley was convicted with Seamus Moley and Joseph McColgan in the Florida Stinger-missile sting of 1990-93, for conspiring to obtain and export a Stinger missile and other weapons for use against British helicopters in Northern Ireland. All three initially received 51 months, though the appellate court vacated those sentences and ordered resentencing."],
    ["Seamus Moley", null, "Seamus", "Moley", "1990s",
     "Conspiracy to obtain and export a Stinger missile and other weapons for use against British helicopters in Northern Ireland.",
     "Fifty-one months. The appellate court vacated the sentences of all three defendants and ordered resentencing.", null, null,
     "Seamus Moley was convicted with Kevin McKinley and Joseph McColgan in the Florida Stinger-missile sting of 1990-93, for conspiring to obtain and export a Stinger missile and other weapons for use against British helicopters in Northern Ireland. All three initially received 51 months, though the appellate court vacated those sentences and ordered resentencing."],

    // --- Pennsylvania-New York NORAID arms case, 1970-78 ---
    ["Neil Byrne", null, "Neil", "Byrne", "1970s",
     "Conspiracy to export rifles and ammunition to Northern Ireland without a licence.",
     "Convicted and sentenced on the export-conspiracy charge.", null, null,
     "Neil Byrne was convicted with Daniel Cahalane in the Pennsylvania and New York arms case of the 1970s, for conspiring to export rifles and ammunition to Northern Ireland without a licence. The evidence involved approximately 360 weapons and more than 100,000 rounds of ammunition."],
    ["Daniel Cahalane", null, "Daniel", "Cahalane", "1970s",
     "Conspiracy to export rifles and ammunition to Northern Ireland without a licence.",
     "Convicted and sentenced on the export-conspiracy charge.", null, null,
     "Daniel Cahalane was convicted with Neil Byrne in the Pennsylvania and New York arms case of the 1970s, for conspiring to export rifles and ammunition to Northern Ireland without a licence. The evidence involved approximately 360 weapons and more than 100,000 rounds of ammunition."],

    // --- Grady-Jankowski arms case, 1971-76 ---
    ["Frank Grady", null, "Frank", "Grady", "1970s",
     "Firearms-records offences and conspiracy to export weapons to Northern Ireland.",
     "Two years, with all but four months suspended.", null, null,
     "Frank Grady was prosecuted with John Jankowski in the arms case of 1971-76 over the export of weapons to Northern Ireland. He received two years, with all but four months suspended."],
    ["John Jankowski", null, "John", "Jankowski", "1970s",
     "Firearms-records offences and conspiracy to export weapons to Northern Ireland.",
     "Concurrent three-year sentences on the firearms-record and export-conspiracy counts.", null, null,
     "John Jankowski was prosecuted with Frank Grady in the arms case of 1971-76 over the export of weapons to Northern Ireland. He received concurrent three-year sentences on the firearms-record and export-conspiracy counts."],

    // --- North Carolina / New York gun-running case, 1980 ---
    ["Howard B. Brutton Jr.", null, "Howard", "Brutton", "1980s",
     "Conspiracy to transport weapons to Northern Ireland, with related firearms offences.",
     "A prison sentence in the range of five to ten years.", null, null,
     "Howard B. Brutton Jr. was convicted with Robert Ferraro and George DeMeo in the 1980 North Carolina and New York gun-running case, for conspiring to transport weapons to Northern Ireland. The three received prison sentences of five to ten years, with related firearms offences also involved."],
    ["Robert Ferraro", null, "Robert", "Ferraro", "1980s",
     "Conspiracy to transport weapons to Northern Ireland, with related firearms offences.",
     "A prison sentence in the range of five to ten years.", null, null,
     "Robert Ferraro was convicted with Howard B. Brutton Jr. and George DeMeo in the 1980 North Carolina and New York gun-running case, for conspiring to transport weapons to Northern Ireland. The three received prison sentences of five to ten years, with related firearms offences also involved."],
    ["George DeMeo", null, "George", "DeMeo", "1980s",
     "Conspiracy to transport weapons to Northern Ireland, with related firearms offences.",
     "A prison sentence in the range of five to ten years.", null, null,
     "George DeMeo was convicted with Howard B. Brutton Jr. and Robert Ferraro in the 1980 North Carolina and New York gun-running case, for conspiring to transport weapons to Northern Ireland. The three received prison sentences of five to ten years, with related firearms offences also involved."],

    // --- Valhalla / Marita Ann operation, 1984-87 ---
    ["Joseph Paul Murray Jr.", null, "Joseph", "Murray", "1980s",
     "Arms trafficking — the American end of the 1984 Valhalla and Marita Ann shipment to the Irish Republican Army.",
     "Ten years.", null, null,
     "Joseph Paul Murray Jr. organised the American end of the 1984 arms shipment carried by the trawler Valhalla and transferred to the Marita Ann off the Irish coast, the largest attempted arms delivery to the Irish Republican Army from the United States. He received ten years; his co-defendants Patrick Nee and Robert Andersen received four years each."],
    ["Patrick Nee", null, "Patrick", "Nee", "1980s",
     "Arms trafficking — the American end of the 1984 Valhalla and Marita Ann shipment to the Irish Republican Army.",
     "Four years.", null, null,
     "Patrick Nee was convicted over the American end of the 1984 arms shipment carried by the trawler Valhalla and transferred to the Marita Ann off the Irish coast. He received four years, as did Robert Andersen; Joseph Paul Murray Jr. received ten."],
    ["Robert Andersen", "Robert Anderson", "Robert", "Andersen", "1980s",
     "Arms trafficking — the American end of the 1984 Valhalla and Marita Ann shipment to the Irish Republican Army.",
     "Four years.", null, null,
     "Robert Andersen was convicted over the American end of the 1984 arms shipment carried by the trawler Valhalla and transferred to the Marita Ann off the Irish coast. He received four years, as did Patrick Nee; Joseph Paul Murray Jr. received ten. His surname is generally reported as Andersen rather than Anderson."],

    // --- Maze-escape extradition cases (datable custody) ---
    ["James Joseph Smyth", null, "James", "Smyth", "1990s",
     "Held for extradition to the United Kingdom following the 1983 escape from the Maze prison.",
     "Held without bail throughout the extradition litigation, which began in 1992. No release date is documented in the available source.",
     [1992, null, null], null,
     "James Joseph Smyth was one of the Maze prison escapers pursued by British extradition requests in the United States. He was held without bail from 1992 while his extradition litigation proceeded."],
    ["Pol Brennan", "Paul Brennan", "Pol", "Brennan", "1990s",
     "Held for extradition to the United Kingdom following the 1983 escape from the Maze prison.",
     "Arrested in January 1993 and held until released on bail in January 1996, alongside Terence Damien Kirby and Kevin Artt.",
     [1993, 1, null], [1996, 1, null],
     "Pol Brennan was one of the Maze prison escapers pursued by British extradition requests in the United States. Arrested in January 1993, he was detained until he, Terence Damien Kirby and Kevin Artt were released on bail in January 1996."],
    ["Terence Damien Kirby", null, "Terence", "Kirby", "1990s",
     "Held for extradition to the United Kingdom following the 1983 escape from the Maze prison.",
     "Arrested in February 1994 and held until released on bail in January 1996, alongside Pol Brennan and Kevin Artt.",
     [1994, 2, null], [1996, 1, null],
     "Terence Damien Kirby was one of the Maze prison escapers pursued by British extradition requests in the United States. Arrested in February 1994, he was detained until he, Pol Brennan and Kevin Artt were released on bail in January 1996."],
];

$created = 0; $updated = 0;
foreach ($roster as [$name, $aka, $first, $last, $era, $charges, $sentence, $inc, $rel, $bio]) {
    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();

    if (! $p) {
        $p = Prisoner::create([
            "name" => $name, "first_name" => $first, "last_name" => $last, "aka" => $aka,
            "gender" => "Male", "era" => $era,
            "ideologies" => ["Irish Republicanism", "Anti-Imperialism"],
            "affiliation" => ["Irish Republican Army"],
            "in_custody" => false, "released" => true,
            "description" => $bio,
        ]);
        $created++;
        echo "created  {$p->name}  [{$p->slug}]\n";
    } else {
        if ($aka && ! $p->aka) { $p->aka = $aka; }
        if (! $p->description) { $p->description = $bio; }
        if (! $p->era) { $p->era = $era; }
        if (! $p->gender) { $p->gender = "Male"; }
        $updated++;
        echo "updated  {$p->name}  [{$p->slug}]\n";
    }
    $p->first_name = $first;
    $p->last_name = $last;
    $p->in_custody = false;
    $p->awaiting_trial = false;
    $p->released = true;
    $p->save();

    // One case per person, matched on the charges text so re-runs update it.
    $c = $p->cases()->orderBy("created_at")->first();
    if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
    $c->charges = $charges;
    $c->sentence = $sentence;
    if (! $c->convicted) { $c->convicted = "Yes"; }
    if ($inc) { $c->setPartialDate("incarceration_date", $inc[0], $inc[1], $inc[2]); }
    if ($rel) { $c->setPartialDate("release_date", $rel[0], $rel[1], $rel[2]); }
    $c->save();
    echo "    case inc=".($c->incarceration_date ?: "-")." rel=".($c->release_date ?: "-")." days=".($c->imprisoned_for_days ?? "null")."\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. Created {$created}, updated {$updated}.\n";
echo "Run: php artisan prisoners:auto-place-zero-sort to position the new records.\n";
'

echo
echo "Done."
