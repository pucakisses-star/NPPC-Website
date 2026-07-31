#!/usr/bin/env bash
#
# JOSEPH R. DUNLOP -- vital dates.
#
# Born about 1848 or 1849; died 1926. Neither is settled to a day, and the
# birth is not settled even to a year.
#
# THE BIRTH YEAR USES CIRCA PRECISION, which is the right tool rather than
# a compromise. HasPartialDates defines circa as "a year that may be off
# by one", which is exactly the shape of "approximately 1848-1849". It
# renders as "c. 1848".
#
#   This is a different case from the birthdates left EMPTY in batches 34
#   and 32. Rebecca Winsor Evans is given as 1877 or 1879 -- two years
#   apart, which circa cannot express -- and Belle Sheinberg has two
#   candidate DAYS within a settled year. Neither fits a plus-or-minus-one
#   band. This one does.
#
# THE DEATH YEAR is stored at year precision: 1926, no month or day.
#
# THE AGE COLUMN FOLLOWS AUTOMATICALLY. The Prisoner saving hook computes
# it from birthdate to death date, giving 78, which the guard accepts. It
# is an artefact of both dates resolving to January 1 and should be read
# as approximate, like the birth year it derives from.
#
# NOTHING ELSE CHANGES. His custody is already recorded and consistent:
# committed to Joliet on May 4, 1897 and released February 10, 1899, which
# is 647 days against the two-year sentence.
#
# Guarded and idempotent: dates are compared before writing.
#
# Run from the repo root:
#   bash database/data/fix-joseph-r-dunlop.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/joseph-r-dunlop.json")), true);

if (! $payload || empty($payload["records"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$applyDate = function ($model, string $field, $spec): bool {
    if ($spec === null) {
        if ($model->{$field} === null) {
            return false;
        }
        $model->setPartialDate($field, null);

        return true;
    }
    [$y, $m, $d, $approx] = array_pad($spec, 4, null);
    $was = $model->{$field} ? $model->{$field}->format("Y-m-d") : null;
    $wasPrec = $model->datePrecisionFor($field);
    $model->setPartialDate($field, $y, $m, $d, (bool) $approx);

    return $was !== ($model->{$field} ? $model->{$field}->format("Y-m-d") : null)
        || $wasPrec !== $model->datePrecisionFor($field);
};

foreach ($payload["records"] as $row) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $row["slug"])->first();

    if (! $p) {
        echo "  NOT FOUND: ", $row["slug"], "\n";
        continue;
    }

    $notes = [];

    if (array_key_exists("description", $row) && $p->description !== $row["description"]) {
        $p->description = $row["description"];
        $notes[] = "description";
    }

    foreach (["birthdate", "death_date"] as $field) {
        if (array_key_exists($field, $row) && $applyDate($p, $field, $row[$field])) {
            $notes[] = $field." ".$p->formatPartialDate($field);
        }
    }

    if ($notes) {
        $p->save();
    }

    $p->refresh();
    echo "  ", str_pad($p->slug, 18), " ", ($notes ? implode("; ", $notes) : "already correct"), "\n";
    echo "      born ", $p->formatPartialDate("birthdate"),
         "  died ", $p->formatPartialDate("death_date"),
         "  age ", ($p->age === null ? "null" : $p->age), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
