#!/usr/bin/env bash
#
# Resolve the five conflicts flagged by prisoners:strip-bio-dates, from
# source research.
#
# CARMEN TROTTA -- the bio was right, the field had a month typo.
#   Wikipedia infobox: born November 9, 1962. Field said 1962-10-09.
#   Field corrected to 1962-11-09; parenthetical stripped.
#
# CARRIE DANN -- keep the precise field; note the uncertainty.
#   She had no birth certificate. Obituaries said 1932 (died at 88, or
#   86-88); the Nevada Women's History Project biography gives the full
#   date, December 9, 1933, Eureka County. The field keeps 1933-12-09 and
#   the bio gains a sentence recording the disagreement. Her death on
#   January 1, 2021 is confirmed -- a genuine New Year's death, not a
#   placeholder January 1.
#
# ELLEN MOVES CAMP -- keep the precise field; note the variant.
#   Wikipedia and the Warrior Women Project give the year only, 1931
#   ("aged 76-77" at death). The field's full date, September 25, 1930,
#   is consistent with age 77 at her death on April 5, 2008. Kept, with a
#   bio note for the published 1931 variant.
#
# TEDDY "JAH" HEATH -- bio year wrong, field off by two days.
#   The "1946-2001" of the Jericho Movement page TITLE is contradicted by
#   the detailed biography (newafrikan77), which gives September 16, 1943
#   and death in prison on January 21, 2001 -- he died of cancer at
#   Coxsackie after twenty-eight years, two years after the parole board
#   turned him down. Field corrected 1943-09-14 -> 1943-09-16; death
#   upgraded from year-only 2001 to January 21, 2001.
#
# WILLIAM COFFIN -- not a date error: TWO DIFFERENT MEN merged.
#   The record is the Everett Massacre Wobbly -- one of the 74 IWW members
#   held in the Snohomish County jail in 1916, aged 34, born in
#   California. Onto it had been grafted a second paragraph and a dateless
#   conspiracy case for the Reverend WILLIAM SLOANE COFFIN (1924-2006) of
#   the Boston Five. The 2006 death date belongs to the chaplain, and the
#   1882 birthdate on the server is someone deriving 1916 minus 34 for the
#   Wobbly.
#
#   This script un-merges: the Sloane Coffin paragraph and his conspiracy
#   case are removed, and both date fields are cleared -- the Wobbly's own
#   birth (~1881-82) and death are not established, and a derived year is
#   not set (the Barbara Katt rule; the "aged 34" stays in the prose).
#
#   NO SLOANE COFFIN RECORD IS CREATED. He was convicted in 1968 but
#   remained free pending appeal, the convictions were reversed in 1969,
#   and he SERVED NO TIME -- prosecution without custody, the same ground
#   on which Sid Hatfield was removed. If you want him anyway as a
#   prosecuted-but-never-jailed figure, say so and he can be added
#   separately.
#
# Idempotent. Run from the repo root, then re-run the strip command:
#   bash database/data/fix-bio-date-conflicts.sh
#   php artisan prisoners:strip-bio-dates
#
set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$get = fn (string $slug) => Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();

// ---- Carmen Trotta ---------------------------------------------------------
if ($p = $get("carmen-trotta")) {
    $p->setPartialDate("birthdate", 1962, 11, 9);
    $p->description = str_replace(" (born November 9, 1962)", "", (string) $p->description);
    $p->save();
    echo "carmen-trotta      birth 1962-10-09 -> ".$p->birthdate->toDateString()."  (Wikipedia infobox; field had a month typo)\n";
} else { echo "NOT FOUND: carmen-trotta\n"; }

// ---- Carrie Dann -----------------------------------------------------------
if ($p = $get("carrie-dann")) {
    $p->setPartialDate("birthdate", 1933, 12, 9);
    $p->setPartialDate("death_date", 2021, 1, 1);
    $p->description = str_replace(" (1932–2021)", "", (string) $p->description);
    $note = "She had no birth certificate, and sources differ on her year of birth — obituaries said 1932 or 1934 — but the Nevada Women’s History Project biography records December 9, 1933, in Eureka County. She died at home on New Year’s Day 2021.";
    if (! str_contains($p->description, "no birth certificate")) {
        $p->description = rtrim($p->description)." ".$note;
    }
    $p->save();
    echo "carrie-dann        birth kept ".$p->birthdate->toDateString()."  death ".$p->death_date->toDateString()."  (uncertainty noted in bio)\n";
} else { echo "NOT FOUND: carrie-dann\n"; }

// ---- Ellen Moves Camp ------------------------------------------------------
if ($p = $get("ellen-moves-camp")) {
    $p->setPartialDate("birthdate", 1930, 9, 25);
    $p->setPartialDate("death_date", 2008, 4, 5);
    $p->description = str_replace(" (1931–2008)", "", (string) $p->description);
    $note = "Most published accounts give her birth year only as 1931; the full date recorded here, September 25, 1930, is consistent with her reported age of 77 when she died on April 5, 2008.";
    if (! str_contains($p->description, "September 25, 1930")) {
        $p->description = rtrim($p->description)." ".$note;
    }
    $p->save();
    echo "ellen-moves-camp   birth kept ".$p->birthdate->toDateString()."  (1931 variant noted in bio)\n";
} else { echo "NOT FOUND: ellen-moves-camp\n"; }

// ---- Teddy Jah Heath -------------------------------------------------------
if ($p = $get("teddy-heath")) {
    $p->setPartialDate("birthdate", 1943, 9, 16);
    $p->setPartialDate("death_date", 2001, 1, 21);
    $p->description = str_replace(" (1946–2001)", "", (string) $p->description);
    $note = "The 1946 birth year that appears in some movement materials is an error: he was born September 16, 1943, and died of cancer in prison on January 21, 2001, twenty-eight years into his sentence and two years after the parole board turned him down.";
    if (! str_contains($p->description, "September 16, 1943")) {
        $p->description = rtrim($p->description)." ".$note;
    }
    $p->save();
    echo "teddy-heath        birth 1943-09-14 -> ".$p->birthdate->toDateString()."  death 2001 -> ".$p->death_date->toDateString()."\n";
} else { echo "NOT FOUND: teddy-heath\n"; }

// ---- William Coffin: un-merge the two men ----------------------------------
if ($p = $get("william-coffin")) {
    $desc = (string) $p->description;
    $marker = "The Reverend William Sloane Coffin";
    $pos = strpos($desc, $marker);
    if ($pos !== false) {
        $desc = rtrim(substr($desc, 0, $pos));
        $p->description = $desc;
        echo "william-coffin     removed the Sloane Coffin paragraph from the bio\n";
    } else {
        echo "william-coffin     Sloane Coffin paragraph already gone\n";
    }

    $p->setPartialDate("birthdate", null);
    $p->setPartialDate("death_date", null);
    $p->save();
    echo "william-coffin     cleared birthdate (was a derived 1882) and death_date (2006 was Sloane Coffin’s)\n";

    $removed = 0;
    foreach ($p->cases as $c) {
        if (stripos((string) $c->charges, "draft") !== false || stripos((string) $c->charges, "Conspiracy to counsel") !== false) {
            echo "william-coffin     deleting grafted case: ".substr((string) $c->charges, 0, 70)."...\n";
            $c->delete();
            $removed++;
        }
    }
    if (! $removed) { echo "william-coffin     no grafted conspiracy case found (already removed?)\n"; }
    echo "william-coffin     NOTE: no Sloane Coffin record created — convicted 1968, reversed 1969, served no time (the Hatfield rule).\n";
} else { echo "NOT FOUND: william-coffin\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. Re-run php artisan prisoners:strip-bio-dates — all five conflicts should be gone.\n";
'

echo
echo "Done."
