#!/usr/bin/env bash
#
# THE IWW DUPLICATES -- 31 merges across 28 curator-identified clusters,
# almost all of them one pattern: A NAMED ROSTER IMPORTED TWICE.
#
# Most pairs are one fuller biographical record against one thin row from
# the National Civil Liberties Bureau's March 1919 compilation "War-Time
# Prosecutions and Mob Violence", or from the International Labor
# Defense's class-war-prisoner rosters — the roster row carrying a
# variant spelling (Jakkola, Avilla, McCarty, Plahn, O'Hair, Santelli,
# Freehan, Johanson/Johansen) or bare initials (H. B., A. G., C. H.,
# W. D., V. V.). The curator supplied the clusters; every one was then
# checked against both records before it went in this file.
#
# Every decision and every conflict is in the payload notes,
# database/data/fixes/duplicate-merges-2.json, which the run prints as it
# goes. The ones that are more than routine:
#
# JACOB TORI'S PHOTO WAS JACOB RIIS. The famous journalist. And Riis's
# exact vital dates — born May 3, 1849, died May 26, 1914 — were sitting
# in the record's birthdate and death date fields, which is how the man
# came to die four years before his own conviction. Some earlier
# enrichment matched "Jacob Tori" to the wrong famous Jacob. The dates
# are cleared, and the photo is replaced by the Leavenworth double
# mugshot (no. 13633) that the duplicate louis-tori row carried. The Riis
# portrait file stays on disk.
#
# THE TWO BEYER PHOTOS ARE NOT THE SAME PHOTOGRAPH, although the two
# records are the same man (the survivor's AKA already reads J. H.
# Byers). One is a Houghton Library studio cabinet card of a YOUNG man in
# a wing collar; the other is a prison mugshot, no. 4914, of a man in his
# fifties — and the record itself says Beyer was 56 at the Everett
# Massacre. The mugshot wins; the cabinet card was very likely never him.
#
# TWO MERGES MOVE CASE ROWS instead of letting them cascade away,
# because the duplicate held an episode the survivor lacked:
#
#   vincent-saint-john  gains the 1907 Goldfield conspiracy arrest
#   j-h-beyer           gains the Everett Massacre detention
#   manuel-rey-y-garcia gains the only DATED custody span of his pair
#
# A THIRD HAYWOOD appeared while applying the pair the curator named:
# william-d-haywood, a Steunenberg-only record duplicating bill-haywood's
# first case date for date. All three collapse into bill-haywood.
#
# CONFLICTS RESOLVED RATHER THAN PAPERED OVER, each recorded in its note:
# Stewart's release (ILD roster 1927-03-21 vs record 1927-03-24), Nef's
# death (1937-01-01 vs 1959-06-01), Baldazzi's Leavenworth span (the
# Zimmer INS-file dates stand), MacDonald's commutation month.
#
# TEXT IS NEVER SILENTLY DISCARDED. Where the duplicate held prose the
# survivor lacked, it is composed in (Antonov, Feehan) or appended
# (MacDonald's clemency-refusal poem, Johanson's Leavenworth stints,
# Beyer's Everett account). Where the survivor already contained
# everything the duplicate said (Whitehead), the duplicate just goes.
#
# RICHARD "BLACKIE" FORD, on the curator's list, was ALREADY MERGED in
# batch 45 and is not repeated here.
#
# SAFETY. Same rules as merge-duplicate-prisoners-1: the survivor is
# written before anything is deleted, and a duplicate holding the only
# photograph of its pair is refused unless the merge explicitly takes the
# photo. Case rows cascade on delete, which is why the three episodes
# above are moved first.
#
# Idempotent: fields are compared before writing, appends check for
# their own text first, moved cases are matched by date so a second run
# finds them already moved, and a slug already gone is reported rather
# than treated as an error.
#
# Run from the repo root:
#   bash database/data/merge-duplicate-prisoners-2.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/duplicate-merges-2.json")), true);

if (! $payload || empty($payload["merges"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$merged = 0;
$skipped = 0;

foreach ($payload["merges"] as $m) {
    $keep = Prisoner::withoutGlobalScopes()->where("slug", $m["keep"])->first();
    $drop = Prisoner::withoutGlobalScopes()->where("slug", $m["drop"])->with("cases")->first();

    echo "\n", $m["keep"], " <- ", $m["drop"], "\n";
    if (! empty($m["note"])) {
        echo "    ", str_replace("\n", "\n    ", wordwrap($m["note"], 66)), "\n";
    }

    if (! $keep) {
        echo "    SURVIVOR MISSING — skipped, nothing deleted\n";
        $skipped++;
        continue;
    }

    if (! $drop) {
        echo "    duplicate already gone\n";
        continue;
    }

    if ($drop->photo && ! $keep->photo && empty($m["take_photo"])) {
        echo "    REFUSING: duplicate has the only photo of the pair and the merge does not take it\n";
        $skipped++;
        continue;
    }

    $notes = [];

    if (! empty($m["middle"]) && $keep->middle_name !== $m["middle"]) {
        $keep->middle_name = $m["middle"];
        $notes[] = "middle_name";
    }

    if (! empty($m["aka"]) && $keep->aka !== $m["aka"]) {
        $notes[] = "aka".($keep->aka ? " (was: ".$keep->aka.")" : "");
        $keep->aka = $m["aka"];
    }

    if (! empty($m["description"]) && $keep->description !== $m["description"]) {
        $keep->description = $m["description"];
        $notes[] = "description composed";
    }

    if (! empty($m["description_append"]) && ! str_contains((string) $keep->description, substr($m["description_append"], 0, 60))) {
        $keep->description = trim((string) $keep->description)."\n\n".$m["description_append"];
        $notes[] = "description appended";
    }

    if (! empty($m["clear_vitals"])) {
        foreach (["birthdate", "death_date"] as $f) {
            if ($keep->{$f}) {
                $notes[] = "cleared {$f} (was ".$keep->{$f}->format("Y-m-d").")";
                $keep->setPartialDate($f, null);
            }
        }
    }

    if (! empty($m["take_photo"]) && $drop->photo && $keep->photo !== $drop->photo) {
        $notes[] = "photo taken from duplicate".($keep->photo ? " (replaced ".$keep->photo.")" : "");
        $keep->photo = $drop->photo;
    }

    foreach (($m["carry"] ?? []) as $field) {
        if (! $keep->{$field} && $drop->{$field}) {
            $keep->{$field} = $drop->{$field};
            $notes[] = "carried ".$field;
        }
    }

    if ($notes) {
        $keep->save();
    }

    $movedCases = 0;

    if (! empty($m["move_cases"])) {
        foreach ($drop->cases as $case) {
            $case->prisoner_id = $keep->id;
            $case->save();
            $movedCases++;
        }
    }

    $left = $drop->cases()->count();
    $drop->delete();
    $merged++;

    echo "    MERGED",
         ($movedCases ? " — {$movedCases} case row(s) moved across" : ""),
         ($left && ! $movedCases ? " — {$left} duplicate case row(s) went with it" : ""),
         ($notes ? "\n    " . implode("; ", $notes) : ""), "\n";

    $keep->refresh();
    echo "    survivor now: ", $keep->cases()->count(), " case(s), photo ",
         ($keep->photo ? "yes" : "no"), "\n";
}

echo "\nMerged: {$merged}   Skipped: {$skipped}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
