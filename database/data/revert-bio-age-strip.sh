#!/usr/bin/env bash
#
# REVERT prisoners:strip-bio-ages entirely.
#
# The command was wrong in a way its own dry run finally made visible:
# it matched LIFE-EVENT ages, not just press-report ones. "At age 17,
# he was sentenced as an adult" lost its age and became "At, he was
# sentenced"; "a vegan since age 15" became "a vegan since,"; and worse
# than the mangled prose, those ages were anchored to whatever year
# stood nearby, deriving birth years that are simply false — Kevin
# Olliff, born 1987, was assigned c. 2001 from "vegan since age 15".
# The 131 reported conflicts were not bad stored data: spot-checking
# them shows the stored years (King 1929, George Jackson 1941, Steimer
# 1897) are correct and the derivations are the error.
#
# THE RESTORE SOURCE is a full API snapshot taken after batch 1 and
# BEFORE the strip ran, shipped at
# database/data/revert/bio-age-strip.json: the 694 affected slugs with
# their pre-strip descriptions, and for each the derived birth year the
# command may have written into an empty birthdate field.
#
# WHAT IT DOES, per record:
#   - description is restored to the snapshot text, verbatim
#   - the birthdate is cleared ONLY when all three hold: the payload
#     marks it as command-filled, the stored year equals the year the
#     command derived, and the precision is year. A date set by hand or
#     by any other script fails the match and is left alone.
#
# Records already matching the snapshot are skipped, so this is
# idempotent and safe on a database where the strip never ran.
#
# strip-bio-ages is REMOVED from run-batch-2.sh in the same change, so
# re-running that batch cannot reapply the damage. The derived-age
# approach is retired; birth years for these records get filled by
# researching the people individually instead.
#
# Run from the repo root:
#   bash database/data/revert-bio-age-strip.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

$payload = json_decode(File::get(database_path("data/revert/bio-age-strip.json")), true);
if (! is_array($payload) || ! count($payload)) {
    echo "PAYLOAD MISSING OR EMPTY\n";
    exit(1);
}

$restored = 0;
$cleared = 0;
$already = 0;
$missing = 0;
$keptBirth = 0;

foreach ($payload as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->first();
    if (! $p) {
        $missing++;
        continue;
    }

    $dirty = false;

    if ((string) $p->description !== (string) $row["description"]) {
        $p->description = $row["description"];
        $dirty = true;
        $restored++;
    }

    if (! empty($row["clear_birth_year"])) {
        if ($p->birthdate) {
            $year = (int) Carbon::parse($p->birthdate)->year;
            $prec = $p->datePrecisionFor("birthdate");
            if ($year === (int) $row["clear_birth_year"] && $prec === "year") {
                $p->setPartialDate("birthdate", null);
                $p->age = null;
                $dirty = true;
                $cleared++;
            } else {
                // A different value than the command derived — someone
                // set this on purpose. Keep it.
                $keptBirth++;
            }
        }
    }

    if ($dirty) {
        $p->save();
    } else {
        $already++;
    }
}

echo "Descriptions restored:              {$restored}\n";
echo "Command-written birthdates cleared: {$cleared}\n";
echo "Birthdates kept (value did not match the derivation — set on purpose): {$keptBirth}\n";
echo "Already matching the snapshot:      {$already}\n";
echo "Slugs not found:                    {$missing}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
