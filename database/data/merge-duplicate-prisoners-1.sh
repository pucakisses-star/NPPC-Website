#!/usr/bin/env bash
#
# NINETEEN DUPLICATE PAIRS MERGED, AND ONE RECORD THAT IS TWO PEOPLE SPLIT.
#
# Found by prisoners:audit-duplicate-names, added in the same batch. Every
# pair below was then read by hand against both records; the audit ranks
# candidates, it does not decide them.
#
# THE RULE FOR WHICH RECORD SURVIVES, applied throughout: the one that
# would LOSE something if it went. In practice that is the record with the
# photograph and the fuller case dates. Prisoner slugs have no redirect
# map — SiteController has one for articles and nothing equivalent for
# people — so the surviving slug is the surviving URL.
#
# Any name the survivor lacks goes into middle_name, which does not touch
# the slug, because Prisoner::updating() only regenerates it when `name`
# is dirty. Same route as Moyer and Pettibone in batch 44 and Dr. Otis W.
# Smith in batch 38.
#
# ------------------------------------------------------------------
# THE SEPTEMBER 2019 HOUSTON SHIP CHANNEL CLUSTER -- ELEVEN PAIRS
# ------------------------------------------------------------------
#
# The same action was imported twice. One import is the "Greenpeace 28",
# carrying full middle names, the Texas state charge under Penal Code
# 424.052, and a September 13 release. The other is the "22 federally
# charged", carrying photographs, the federal aiding-and-abetting charge,
# and the September 14 release when a magistrate granted all 22
# personal-recognizance bonds.
#
# ELEVEN OF THE TWELVE IN THE FIRST IMPORT ARE IN THE SECOND. They match
# on reported age AND on state, every one:
#
#   Allen 20 MO   Bufford 32 GA   Clifford 21 OR   Gibson 28 NY
#   Herbert 36 MD   Kim 26 NY   McElvain 36 CO   Newman 42 KY
#   Schee 25 MO   Seiji 29 CA   Sisney 32 CA
#
#   KIM IRENE AND IRENE KIM ARE THE SAME PERSON WITH THE NAME REVERSED,
#   which is why no first-and-last-name matcher found her. Age 26 and New
#   York on both. She is the reason the audit command has a second pass
#   over name tokens in any order.
#
#   MARIAH DE LOS SANTOS, 23, of Texas is the twelfth and has NO match in
#   the federal 22. She is left completely alone.
#
# The federal record survives in each case: it has the photograph, and its
# September 14 release is the documented one. The state charge text on the
# deleted rows is quoted in this header so it is not lost silently:
#
#   "33 USC 403 obstruction of navigable waters (federal); Texas Penal
#    Code 424.052 impairing or interrupting operation of critical
#    infrastructure" — and the Texas grand jury DECLINED to return felony
#    indictments.
#
# ------------------------------------------------------------------
# THE OTHER EIGHT
# ------------------------------------------------------------------
#
#   robert-thompson <- robert-g-thompson
#       IDENTICAL BIRTH AND DEATH DATES, 1915-06-21 and 1965-10-16. CPUSA
#       New York chairman, Foley Square, Alcatraz. The survivor holds the
#       only case dates the pair has (1948-07-19 to 1960-12-14); the
#       deleted row had none at all, so the merge loses nothing and gains
#       the G.
#
#   richard-blackie-ford <- richard-ford
#       The DELETED row's own biography opens "Richard \"Blackie\" Ford".
#       Wheatland hop riot, 1913, Folsom. Survivor has the photo and the
#       release date.
#
#   john-boncore-hill <- john-hill
#       Dacajeweiah, Splitting the Sky, Attica. Survivor has the photo and
#       the conviction dates. THE DELETED ROW CARRIED A DEATH DATE of
#       2013-03-13 and the survivor had none, so it is brought across.
#
#   evan-welling-thomas <- evan-thomas
#       Norman Thomas-s younger brother, absolutist objector, Fort
#       Leavenworth. Survivor has the full custody dates and a precise
#       death date of 1974-12-15 against the other-s bare 1974.
#
#   daniel-baker <- daniel-alan-baker
#       The SURVIVOR-s own biography opens "Daniel Alan Baker, age 33".
#       It holds the birthdate and the custody dates; the deleted row had
#       neither.
#
#   virgil-j-stauffer <- j-virgil-stauffer
#       NAME REVERSED. Both are the WWI conscientious objector held at
#       Fort Leavenworth whom the Board of Inquiry examined in January
#       1919 and found sincere. Survivor has the release date and the
#       state.
#
#   harrison-george <- george-harrison
#       NAME REVERSED, AND THE CORRECTLY NAMED RECORD IS THE THIN ONE.
#       Harrison George was the IWW journalist convicted at the mass
#       Chicago trial, five years and a $30,000 fine. The deleted row is
#       his name backwards with the better biography attached, so THE
#       BIOGRAPHY IS CARRIED ONTO the correctly named record rather than
#       the other way round.
#
#   jose-roman-rivera <- jose-rivera
#       Identical case: arrested 2001-05-22, released 2001-08-21, ninety
#       days, Federal Detention Center Guaynabo, Vieques trespass. The
#       fuller name survives.
#
#       JOSE-PEREZ-RIVERA IS A DIFFERENT MAN and is not touched. He was
#       arrested 2001-04-26 and sentenced to 45 days. All three carry the
#       same templated Vieques biography, which is exactly why biography
#       similarity alone cannot decide a duplicate — the campaign ran for
#       four years and Rivera is a common surname in Puerto Rico.
#
# ------------------------------------------------------------------
# MICHAEL DOYLE IS NOT A DUPLICATE. HE IS TWO MEN IN ONE ROW.
# ------------------------------------------------------------------
#
# michael-doyle carried BOTH the affiliations "Camden 28" and "Molly
# Maguires", and BOTH cases: the August 1971 Camden draft-board raid, and
# a murder trial ending at Carbon County Prison on June 21, 1877. Its
# biography is about the priest. Its birthdate and death date, 1934-03-30
# and 2018-10-22, belong to neither man.
#
#   Father Michael Doyle of the Camden 28 died in 2022 and is already a
#   separate and correct record, father-michael-doyle, born 1934-11-03
#   and died 2022-11-23.
#
#   Michael Doyle the Molly Maguire was hanged at Mauch Chunk on June 21,
#   1877, one of ten men executed in Pennsylvania that day, tried for the
#   murder of the mine boss John P. Jones in the prosecutions run by the
#   Reading Railroad president Franklin B. Gowen on Pinkerton evidence.
#   The other eighteen Molly Maguires are already in this database.
#
# So michael-doyle is STRIPPED BACK to the Molly Maguire: the Camden 28
# affiliation goes, the 1971 case row goes, the two wrong vital dates are
# cleared, the death date is set to the execution, and the biography is
# rewritten. NOTHING IS DELETED — the priest already exists in full.
#
# ------------------------------------------------------------------
#
# SAFETY. No record is deleted before its survivor is written. The script
# REFUSES to delete a record that holds the ONLY photograph of the pair,
# since by this batch-s own rule that row should have been the survivor.
# Where both rows have a photograph -- Thompson, Baker and Harrison George
# all do -- nothing is lost and the run says so. Case rows cascade on
# delete.
#
# The prose carries apostrophes, so the payload lives in
# database/data/fixes/duplicate-merges-1.json.
#
# Idempotent: every field is compared before writing, carried fields are
# only written when the survivor has none, and a slug already gone is
# reported rather than treated as an error.
#
# Run from the repo root:
#   bash database/data/merge-duplicate-prisoners-1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/duplicate-merges-1.json")), true);

if (! $payload) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$merged = 0;
$skipped = 0;

foreach ($payload["merges"] as $m) {
    $keep = Prisoner::withoutGlobalScopes()->where("slug", $m["keep"])->first();
    $drop = Prisoner::withoutGlobalScopes()->where("slug", $m["drop"])->with("cases")->first();

    if (! $keep) {
        echo "  SURVIVOR MISSING: ", $m["keep"], " — skipped, nothing deleted\n";
        $skipped++;
        continue;
    }

    if (! $drop) {
        echo "  ", str_pad($m["keep"], 30), " duplicate ", $m["drop"], " already gone\n";
        continue;
    }

    // The risk is losing the only photograph of somebody. Where BOTH rows
    // have one -- Thompson, Baker and Harrison George all do -- nothing is
    // lost by keeping the survivor chosen for its case dates.
    if ($drop->photo && ! $keep->photo) {
        echo "  REFUSING: ", $m["drop"], " has the only photo of the pair — it should be the survivor. Merge by hand.\n";
        $skipped++;
        continue;
    }

    if ($drop->photo) {
        echo "  (both rows have a photo; the survivor keeps its own)\n";
    }

    $notes = [];

    if (! empty($m["middle"]) && $keep->middle_name !== $m["middle"]) {
        $keep->middle_name = $m["middle"];
        $notes[] = "middle_name=".$m["middle"];
    }

    // Carried fields are written ONLY where the survivor has nothing.
    foreach (($m["carry"] ?? []) as $field) {
        if (! $keep->{$field} && $drop->{$field}) {
            $keep->{$field} = $drop->{$field};
            $notes[] = "carried ".$field;
        }
    }

    if ($notes) {
        $keep->save();
    }

    $cases = $drop->cases->count();
    $drop->delete();
    $merged++;

    echo "  ", str_pad($m["keep"], 30), " <- ", str_pad($m["drop"], 32),
         " deleted (", $cases, " case row) ",
         ($notes ? implode(", ", $notes) : ""), "\n";
}

echo "\nMerged: {$merged}   Skipped: {$skipped}\n";

// ---- the record that was two people ------------------------------------

$s = $payload["split"] ?? null;

if ($s) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $s["slug"])->with("cases")->first();

    echo "\nSplitting ", $s["slug"], ":\n";

    if (! $p) {
        echo "  NOT FOUND — skipped\n";
    } else {
        $aff = array_values(array_diff((array) $p->affiliation, $s["strip_affiliation"]));
        if ($aff != (array) $p->affiliation) {
            $p->affiliation = $aff;
            echo "  affiliation now: ", implode(", ", $aff), "\n";
        }

        foreach ($s["clear"] as $field) {
            if ($p->{$field}) {
                echo "  cleared ", $field, " (was ", $p->{$field}->format("Y-m-d"), ")\n";
                $p->setPartialDate($field, null);
            }
        }

        [$y, $mo, $d] = $s["death_date"];
        $p->setPartialDate("death_date", $y, $mo, $d);
        echo "  death_date set to ", $p->death_date->format("Y-m-d"), " — the execution\n";

        $p->description = $s["description"];
        $p->save();
        echo "  biography rewritten\n";

        foreach ($p->cases as $case) {
            $year = null;
            foreach (["arrest_date", "incarceration_date", "release_date"] as $f) {
                if ($case->{$f}) {
                    $year = (int) $case->{$f}->format("Y");
                    break;
                }
            }

            if ($year === $s["strip_case_year"]) {
                $case->delete();
                echo "  removed the ", $year, " case row — it belongs to father-michael-doyle\n";
            }
        }

        $p->refresh();
        echo "  michael-doyle now has ", $p->cases()->count(), " case(s)\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
