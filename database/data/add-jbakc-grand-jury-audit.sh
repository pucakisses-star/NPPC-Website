#!/usr/bin/env bash
#
# Audit against the John Brown Anti-Klan Committee's "Stop the Grand Jury!"
# newsletter (November 1984). Of the ~75 people it names, nearly all were
# already in the database. This script:
#
#  1. Merges four duplicate pairs the audit surfaced:
#     elmer-geronimo-pratt <= elmer-pratt, patricia-gros-levasseur <= pat-gros,
#     iya-fulani-sunni-ali <= fulani-sunni-ali (both records say "born Cynthia
#     Boston in New Rochelle in 1948"), and federico-cintron-fiallo <=
#     federico-cintron-fiallo-3 (the 1975 MOU-arrest stub).
#  2. Adds three missing people:
#     - Norberto Cintrón Fiallo — the 1982 Brooklyn grand-jury civil-contempt
#       resister who until now existed only as an alias on his brother
#       Federico's record.
#     - Sondra Clark — Battle Creek, MI grand-jury resister jailed in 1984
#       while breast-feeding; her seven-month-old child died during the
#       jailing (documented in the newsletter itself).
#     - Yvette Kelly — the one New York 8+ defendant missing while her seven
#       co-defendants all have records.
#  3. Fixes federico-cintron-fiallo's aka (his brother Norberto is a distinct
#     person, not an alias) and appends sourced context to the DC grand-jury
#     four (Rico, Nalibov, Burke, Roland — all jailed as resisters in 1985),
#     Larry Guy (Battle Creek Coalition leader) and Cameron Bishop (1984
#     Boston grand-jury resistance).
#
# Not added, with reasons: Ivette Alfonso, Raymond Soto, Susan Tipograph,
# Shoshona Rihn and Varnell Pratt appear in the newsletter as resisters or
# signers but no confinement could be verified; the "Larry Mack, grand jury
# resister 1983-84" listing could not be confirmed as the Panther 21 Larry
# Mack already in the database, so nothing was appended to that record.
#
# Idempotent: prisoner:add refuses duplicates (|| true), merges no-op once
# applied, description appends are marker-guarded, scalar fills are
# fill-if-empty.
#
# Run from the repo root:  bash database/data/add-jbakc-grand-jury-audit.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=elmer-geronimo-pratt,patricia-gros-levasseur,iya-fulani-sunni-ali,federico-cintron-fiallo --apply

php artisan prisoner:add '{"name":"Norberto Cintrón Fiallo","first_name":"Norberto","last_name":"Cintrón Fiallo","description":"Norberto Cintrón Fiallo is a Puerto Rican independentista who was subpoenaed from Puerto Rico to a federal grand jury in Brooklyn investigating the clandestine independence movement. Refusing on principle to testify, he was jailed for civil contempt in 1982 and held in New York; he and fellow resisters Carlos Noya and Ricarte Montes García served terms of nine to eighteen months without ever being charged with a crime. His brother Federico Cintrón Fiallo was later convicted of criminal contempt for the same refusal before the same investigation. Documented in the John Brown Anti-Klan Committee newspaper Stop the Grand Jury! (November 1984).","state":"Puerto Rico","gender":"Male","ideologies":["Puerto Rican independence"],"era":"1980s","released":true,"cases":[{"charges":"Civil contempt — refusing to testify before a federal grand jury in Brooklyn investigating the Puerto Rican independence movement","convicted":"No — jailed for civil contempt, never charged with a crime","sentence":"Jailed for the life of the grand jury; served between nine and eighteen months (1982–83)"}]}' || true

php artisan prisoner:add '{"name":"Sondra Clark","first_name":"Sondra","last_name":"Clark","description":"Sondra Clark was a Black resident of Battle Creek, Michigan, caught up in the 1984 Calhoun County state grand jury that subpoenaed as many as 200 people from the Black community — children among them — in a campaign against the Coalition to End Police Brutality and Racism and its leader Larry Guy. Clark refused to collaborate and was jailed for contempt even though she was still breast-feeding her seven-month-old child. The child died during her jailing; the movement press reported that the judge had ignored the pleas of her doctors in ordering her held. Reconstructed from the John Brown Anti-Klan Committee newspaper Stop the Grand Jury! (November 1984); further case details have not been located.","state":"Michigan","race":"Black","gender":"Female","era":"1980s","released":true,"cases":[{"charges":"Contempt — refusing to testify before the Calhoun County grand jury investigating the Coalition to End Police Brutality and Racism","arrest_date":"1984-01-01","convicted":"No — jailed for contempt, never charged with a crime"}]}' || true

php artisan prisoner:add '{"name":"Yvette Kelly","first_name":"Yvette","last_name":"Kelly","description":"Yvette Kelly was a Black liberation activist charged as a New York 8+ defendant in the October 1984 federal RICO indictment alleging a conspiracy to free Sundiata Acoli, commit armed bank expropriations, and bomb federal buildings. She was arrested on October 18, 1984, when 400 FBI agents staged pre-dawn raids just three days after the new federal preventive-detention law took effect — prosecutors immediately sought to hold the defendants without bail on the political nature of the charges, making the case an early test of the 1984 Bail Reform Act. In August 1985 the jury acquitted the New York 8 of all RICO and conspiracy counts. Friends and family of the defendants, including a 15-year-old, were subpoenaed to a grand jury and refused to collaborate.","state":"New York","race":"Black","gender":"Female","ideologies":["Black liberation"],"era":"1980s","released":true,"cases":[{"charges":"RICO conspiracy; conspiracy to commit armed bank robberies, bombings, and prison escape (New York 8+ case)","arrest_date":"1984-10-18","convicted":"Acquitted of all RICO / conspiracy counts (August 1985)"}]}' || true

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

// Sondra Clark: the 1984 jailing date is year-precision only.
$p = $find("sondra-clark");
if ($p && $p->cases()->count() === 1) {
    $case = $p->cases()->first();
    if ($case && $case->arrest_date && $case->formatPartialDate("arrest_date") !== "1984") {
        $case->date_precision = array_merge($case->date_precision ?? [], ["arrest_date" => "year"]);
        $case->save();
        echo "PRECISION sondra-clark\n";
    }
}

// Federico: his brother Norberto is a distinct person, not an alias.
$p = $find("federico-cintron-fiallo");
if ($p && str_contains((string) $p->aka, "Norberto")) {
    $parts = array_values(array_filter(array_map("trim", explode("/", (string) $p->aka)), fn ($a) => $a !== "" && ! str_contains($a, "Norberto")));
    $p->aka = implode(" / ", $parts);
    $p->save();
    echo "AKA federico-cintron-fiallo\n";
}

$appendOnce = function ($p, string $marker, string $paragraph): void {
    if (! $p || str_contains((string) $p->description, $marker)) { return; }
    $p->description = trim((string) $p->description) . "\n\n" . $paragraph;
    $p->save();
    echo "DESC {$p->slug}\n";
};

// The DC grand-jury four (John Brown Anti-Klan Committee).
$dcFour = [
    "christine-rico" => "Christine Rico",
    "julie-nalibov" => "Julie Nalibov",
    "steven-burke" => "Steven Burke",
    "sandra-roland" => "Sandra Roland",
];
foreach ($dcFour as $slug => $name) {
    $appendOnce($find($slug), "John Brown Anti-Klan Committee", "{$name} was one of the four current and former members of the John Brown Anti-Klan Committee — Steven Burke, Julie Nalibov, Christine Rico and Sandra Roland — subpoenaed in late 1984 to a federal grand jury in Washington, D.C. investigating the November 1983 Capitol bombing and related armed actions. All four publicly refused to testify or cooperate in any way, declaring they would not become informers on the movement, and all four were jailed as grand-jury resisters in 1985 without ever being charged with a crime (Washington Post, January 16, 1985; JBAKC, Stop the Grand Jury!, November 1984; Breakthrough, Spring/Summer 1985).");
}
$p = $find("sandra-roland");
if ($p && empty($p->middle_name)) {
    $p->middle_name = "Gayle";
    $p->save();
    echo "MIDDLE sandra-roland\n";
}

// Larry Guy: Battle Creek Coalition context.
$appendOnce($find("larry-guy"), "Coalition to End Police Brutality", "Guy was the leader of the Coalition to End Police Brutality and Racism in Battle Creek, Michigan, and a citizen of the Republic of New Afrika. A 1984 Calhoun County grand jury that subpoenaed as many as 200 people from the Black community — children as young as six among them — was aimed, in the movement'"'"'s assessment, at destroying the Coalition and obtaining indictments against Guy in particular, after a four-year campaign that included death threats, frame-up charges, SWAT raids and cross-burnings (JBAKC, Stop the Grand Jury!, November 1984).");

// Cameron Bishop: 1984 Boston grand-jury resistance.
$appendOnce($find("cameron-bishop"), "Boston grand jury", "In 1984 Bishop — by then an elected school-board member, Little League coach and sheep farmer in Dixmont, Maine — was subpoenaed to a federal grand jury in Boston, part of the FBI'"'"'s Operation BOSLUC manhunt for eleven political fugitives, one of them his brother-in-law. He publicly refused to collaborate, declaring he would go to jail rather than inform on the movement, and joined the public campaign denouncing the operation'"'"'s tactics, including the FBI'"'"'s circulation of photographs of the fugitives'"'"' children (JBAKC, Stop the Grand Jury!, November 1984).");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. JBAKC grand-jury audit applied."
