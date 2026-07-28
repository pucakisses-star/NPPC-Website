#!/usr/bin/env bash
#
# The Abrams v. United States defendants -- corrected custody, life dates
# and exile.
#
# All four records counted imprisonment from the October 1918 sentencing,
# but the defendants were free on appellate bail from late 1918: the men
# jumped bail after the Supreme Court affirmed (November 10, 1919), tried
# to leave for Mexico from New Orleans, were recaptured in December 1919,
# and entered Atlanta on December 26 or 27, 1919. The counters overstated
# custody by more than a year each (1,126-1,128 days shown; ~699 true).
#
#   corrected, all four:
#     arrest         1918-08-22 -> 1918-08-23
#     sentenced      1918-10-22/24 -> 1918-10-25 (the commonly given date)
#     release        1921-11-23 -> 1921-11-24 (pardon papers were handed
#                    over on the 23rd; they sailed on the Estonia on the
#                    24th, the confirmed last day in US custody)
#     exile          in_exile_since 1921-11-24 (conditional pardons
#                    requiring permanent departure; Libau, Latvia,
#                    December 4, then Soviet Russia)
#
#   the three men (Abrams, Lachowsky, Lipman):
#     incarceration  1919-12-26 (PROBABLE -- Dec 26 or 27; the case text
#                    carries the marker), U.S. Penitentiary Atlanta,
#                    transferred to Ellis Island late October 1921
#                    = 699 days
#
#   Steimer -- split into two cases:
#     New York case  Blackwell*s Island Penitentiary, Oct 30, 1919 -
#                    Apr 29, 1920 = 182 days (a separate state
#                    prosecution; it flowed directly into the federal
#                    sentence)
#     federal case   incarceration ~May 1, 1920 (approximate; marked) at
#                    the Missouri State Penitentiary at Jefferson City,
#                    which held female federal prisoners under contract;
#                    Ellis Island late Oct 1921; released/deported
#                    Nov 24, 1921 = 572 days
#                    Continuous confinement Oct 30, 1919 - Nov 24, 1921
#                    = 756 days, which the text states; earlier short
#                    detentions (eight days in the Tombs, Ellis Island
#                    holds) are noted as uncounted.
#     her fine is DISPUTED: most modern biographies say \$500, one
#     American Jewish Archives study says \$5,000 -- recorded as disputed.
#
# LIFE DATES (per the researched dossier):
#   Abrams     born January 24, 1886 (the 1883 in some references is
#              weaker); died June 10, 1953 in Mexico -- replacing a wrong
#              stored 1953-12-15. Exile 1921-11-24 to 1953-06-10; the
#              temporary medical readmission shortly before death was not
#              a permanent return.
#   Lachowsky  born 1894 (year precision). Death CLEARED: the stored
#              1944-06-15 was invented -- accounts conflict between
#              c. 1961 (Zimmer, from INS/FBI files) and murder by the
#              Nazis in Minsk after 1941, so no date is entered and the
#              bio explains. End of exile likewise left null.
#   Lipman     birth left NULL: Zimmer gives 1888 but contemporary
#              descriptions call him twenty-one in 1918 (~1896-97) --
#              disputed, so not entered. Death CLEARED: the stored
#              1937-12-15 was invented; he was shot in the Great Purge in
#              the late 1930s, exact date unknown. End of exile null.
#   Steimer    born 1897-11-21, died 1980-07-23 (already correct; kept).
#              Exile 1921-11-24 to 1980-07-23 -- about 58 years and eight
#              months; expelled from Soviet Russia September 1923, died
#              at Cuernavaca, Mexico, never readmitted to the US.
#
# FIVE, NOT FOUR: the Supreme Court reviewed five convictions. Hyman
# Rosansky, who cooperated, drew three years; Gabriel Prober was
# acquitted; Jacob Schwartz died after the arrests. The charges text on
# each record now says so.
#
# The Blackwell*s Island institution name contains a straight apostrophe,
# which a single-quoted tinker block cannot contain -- it is built with
# chr(39) so the EXISTING institution row is matched instead of creating
# a duplicate.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-abrams-defendants.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$get = fn (string $slug) => Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();

$fiveNote = " He was one of five defendants whose convictions the Supreme Court reviewed under the four-count indictment: Hyman Rosansky, who cooperated with the prosecution, received three years; Gabriel Prober was acquitted; Jacob Schwartz died after the arrests before the case was completed.";

$atlanta = Institution::firstOrCreate(
    ["name" => "U.S. Penitentiary Atlanta"],
    ["city" => "Atlanta", "state" => "Georgia"],
);

// ---- the three men ---------------------------------------------------------
$men = [
    ["jacob-abrams", "20 years and a \$1,000 fine"],
    ["hyman-lachowsky", "20 years and a \$1,000 fine"],
    ["samuel-lipman", "20 years and a \$1,000 fine"],
];
foreach ($men as [$slug, $penalty]) {
    $p = $get($slug);
    if (! $p) { echo "NOT FOUND: {$slug}\n"; continue; }
    $case = $p->cases->first();
    $case->institution_id = $atlanta->id;
    $case->charges = "Conspiracy to violate the Sedition Act of 1918 (amending the Espionage Act of 1917) — four counts, for printing and distributing two leaflets on August 22-23, 1918: one denouncing President Wilson and American intervention in Russia, the other calling munitions workers to a general strike against producing weapons that might be used against revolutionary Russia. Abrams v. United States, 250 U.S. 616 (1919), affirmed 7-2 on November 10, 1919.".$fiveNote;
    $case->sentence = "Sentenced October 25, 1918 to {$penalty}, and released on appellate bail in late 1918. After the Supreme Court affirmed, he jumped bail with his co-defendants and attempted to leave for Mexico from New Orleans; recaptured in December 1919, he entered the Atlanta Federal Penitentiary on December 26 or 27, 1919 — December 26 is the best-supported single date and is the one recorded, marked probable. Transferred to Ellis Island in late October 1921, he was handed a conditional presidential pardon on about November 23, 1921 — requiring permanent departure, not vindicating the conviction — and sailed for Libau, Latvia aboard the Estonia on November 24, 1921, the confirmed last day in United States custody: about 699 days of continuous federal penal and immigration detention.";
    $case->setPartialDate("arrest_date", 1918, 8, 23);
    $case->setPartialDate("sentenced_date", 1918, 10, 25);
    $case->setPartialDate("incarceration_date", 1919, 12, 26);
    $case->setPartialDate("release_date", 1921, 11, 24);
    $case->setPartialDate("in_exile_since", 1921, 11, 24);
    $case->save();
    $p->refresh()->load("cases");
    echo str_pad($slug, 18)." days=".$p->cases->first()->imprisoned_for_days."  (expect 699)\n";
}

// ---- Abrams life dates and exile end ---------------------------------------
if ($p = $get("jacob-abrams")) {
    $p->setPartialDate("birthdate", 1886, 1, 24);
    $p->setPartialDate("death_date", 1953, 6, 10);
    $note = " He was born January 24, 1886 — the 1883 given in some references is the weaker reading — and died in Mexico on June 10, 1953; a temporary medical readmission to the United States shortly before his death did not end the exile his conditional pardon imposed.";
    if (! str_contains((string) $p->description, "January 24, 1886")) { $p->description = rtrim((string) $p->description).$note; }
    $c = $p->cases->first();
    $c->setPartialDate("end_of_exile", 1953, 6, 10);
    $c->save();
    $p->save();
    $p->refresh();
    echo "jacob-abrams       born ".$p->formatPartialDate("birthdate")."  died ".$p->formatPartialDate("death_date")."  exile days=".($p->cases()->first()->in_exile_for_days ?? "null")."\n";
}

// ---- Lachowsky: year birth, cleared death ----------------------------------
if ($p = $get("hyman-lachowsky")) {
    $p->setPartialDate("birthdate", 1894);
    $p->setPartialDate("death_date", null);
    $p->age = null;
    $note = " Born in 1894, his death is unresolved: Kenyon Zimmer’s reconstruction from INS and FBI files puts it about 1961, while earlier accounts say he was probably murdered by the Nazis in Minsk after the German occupation began in 1941. Because the accounts conflict, no death date is recorded.";
    if (! str_contains((string) $p->description, "death is unresolved")) { $p->description = rtrim((string) $p->description).$note; }
    $p->save();
    echo "hyman-lachowsky    born ".$p->formatPartialDate("birthdate")."  death cleared (was an invented 1944-06-15)\n";
}

// ---- Lipman: disputed birth left null, cleared death -----------------------
if ($p = $get("samuel-lipman")) {
    $p->setPartialDate("birthdate", null);
    $p->setPartialDate("death_date", null);
    $p->age = null;
    $note = " His birth year is disputed — Zimmer’s INS-file reconstruction gives 1888 at Pinsk, while contemporary descriptions calling him twenty-one in 1918 imply about 1896-97 — so no birthdate is recorded. In the Soviet Union he was arrested and shot during the Great Purge in the late 1930s; the exact date has not been established, so no death date is recorded either. At Atlanta he worked in the prison tailor shop despite serious trouble with his eyesight, and he was at Ellis Island by November 2, 1921, when he wrote to his attorney about the conditions there.";
    if (! str_contains((string) $p->description, "Great Purge")) { $p->description = rtrim((string) $p->description).$note; }
    $p->save();
    echo "samuel-lipman      birth and death left null (both disputed/undated; was 1937-12-15)\n";
}

// ---- Steimer: two cases ----------------------------------------------------
if ($p = $get("mollie-steimer")) {
    $p->ideologies = ["Anarchism", "Anti-War", "Anti-Militarism"];
    $p->save();

    $jefferson = Institution::firstOrCreate(
        ["name" => "Missouri State Penitentiary"],
        ["city" => "Jefferson City", "state" => "Missouri"],
    );
    $blackwell = Institution::firstOrCreate(
        ["name" => "Blackwell".chr(39)."s Island Penitentiary"],
        ["city" => "New York", "state" => "New York"],
    );

    $federal = $p->cases->first(fn ($c) => stripos((string) $c->charges, "Sedition") !== false) ?? $p->cases->first();
    $federal->institution_id = $jefferson->id;
    $federal->charges = "Conspiracy to violate the Sedition Act of 1918 — the Abrams case leaflets of August 22-23, 1918. Abrams v. United States, 250 U.S. 616 (1919), affirmed 7-2 on November 10, 1919. She was one of five defendants whose convictions the Court reviewed: Hyman Rosansky, who cooperated, received three years; Gabriel Prober was acquitted; Jacob Schwartz died after the arrests.";
    $federal->sentence = "Fifteen years, imposed October 25, 1918, with a fine reported as \$500 by most modern biographies and \$5,000 by one substantial American Jewish Archives study — the figure is disputed until the original judgment is obtained. Released on appellate bail, she began the federal sentence about May 1, 1920 (the date is approximate), directly from her separate Blackwell".chr(39)."s Island term, at the Missouri State Penitentiary in Jefferson City, which held female federal prisoners under contract because no federal women".chr(39)."s penitentiary yet existed. Transferred to Ellis Island in late October 1921, she was deported under conditional pardon aboard the Estonia on November 24, 1921. Counting the New York term, she was continuously confined from October 30, 1919 to November 24, 1921 — 756 days — of which only the portion from about May 1, 1920 belongs to the fifteen-year Abrams sentence. Earlier short detentions, including eight days in the Tombs and immigration holds at Ellis Island, are not counted here.";
    $federal->setPartialDate("arrest_date", 1918, 8, 23);
    $federal->setPartialDate("sentenced_date", 1918, 10, 25);
    $federal->setPartialDate("incarceration_date", 1920, 5, 1);
    $federal->setPartialDate("release_date", 1921, 11, 24);
    $federal->setPartialDate("in_exile_since", 1921, 11, 24);
    $federal->setPartialDate("end_of_exile", 1980, 7, 23);
    $federal->save();

    $ny = $p->cases->first(fn ($c) => stripos((string) $c->charges, "New York prosecution") !== false);
    if (! $ny) { $ny = $p->cases()->create([]); }
    $ny->institution_id = $blackwell->id;
    $ny->charges = "Separate New York prosecution — the state case that put her in the Blackwell".chr(39)."s Island penitentiary from October 30, 1919 to April 29, 1920, while the federal Abrams appeal was pending. Her federal sentence began directly from this term.";
    $ny->sentence = "Served October 30, 1919 to April 29, 1920 on Blackwell".chr(39)."s Island — 182 days — flowing directly into the federal Abrams sentence, so that her final continuous confinement in the United States ran from October 30, 1919 until she sailed into exile on November 24, 1921.";
    $ny->setPartialDate("incarceration_date", 1919, 10, 30);
    $ny->setPartialDate("release_date", 1920, 4, 29);
    $ny->save();

    $p->refresh()->load("cases");
    $total = 0;
    foreach ($p->cases as $c) { $total += (int) $c->imprisoned_for_days; }
    echo "mollie-steimer     total days=".$total."  (expect 754: 572 federal + 182 New York)\n";
    echo "                   exile days=".($federal->refresh()->in_exile_for_days ?? "null")."  (expect ~21426: Nov 24 1921 - Jul 23 1980)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
