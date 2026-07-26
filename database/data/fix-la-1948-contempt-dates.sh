#!/usr/bin/env bash
#
# Set the documented contempt-custody dates (October 26 - November 4, 1948) and
# birth/death dates for the three Los Angeles defendants jailed for refusing to
# answer a federal grand jury investigating Communism: Ben Dobbs, Henry
# Steinberg and Samuel H. Kashinowitz. The grand-jury contempt case (about nine
# days in custody) is added to Dobbs and Steinberg (who otherwise carry only
# their 1952 Smith Act case) and updated on Kashinowitz. Age auto-computes from
# birth + death.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-la-1948-contempt-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$ensureContempt = function (Prisoner $p) {
    $c = $p->cases()->where("charges", "like", "%grand jury%")->first();
    if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
    $c->charges = "Contempt of a federal grand jury investigating Communism in Los Angeles, for refusing to answer its questions.";
    $c->convicted = "Jailed for contempt, 1948";
    $c->sentence = "Held from October 26 to November 4, 1948.";
    $c->setPartialDate("incarceration_date", 1948, 10, 26);
    $c->setPartialDate("release_date", 1948, 11, 4);
    $c->save();
    return $c->imprisoned_for_days;
};

// [find-callback, birth [y,m,d], death [y,m,d]]
$targets = [
    ["Ben Dobbs",  fn () => Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["ben dobbs"])->first(), [1912, 2, 23], [1993, 3, 21]],
    ["Henry Steinberg", fn () => Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["henry steinberg"])->first(), [1912, 8, 12], [1979, 9, 15]],
    ["Samuel H. Kashinowitz", fn () => Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) LIKE ?", ["%nowitz%"])->first(), [1914, 1, 14], [2011, 8, 23]],
];

foreach ($targets as [$label, $finder, $b, $d]) {
    $p = $finder();
    if (! $p && $label === "Samuel H. Kashinowitz") {
        $p = Prisoner::create([
            "name" => "Samuel H. Kashinowitz", "first_name" => "Samuel", "last_name" => "Kashinowitz",
            "gender" => "Male", "state" => "California", "era" => "1940s",
            "ideologies" => ["Communism"], "affiliation" => ["Communist Party USA"],
            "in_custody" => false, "released" => true,
            "description" => "Samuel H. Kashinowitz was a Los Angeles Communist who, with Ben Dobbs and Henry Steinberg, was jailed for contempt after refusing to answer a federal grand jury investigating Communism in Los Angeles.",
        ]);
        echo "created {$p->name}\n";
    }
    if (! $p) { echo "NOT FOUND: {$label}\n"; continue; }

    $p->setPartialDate("birthdate", $b[0], $b[1], $b[2]);
    $p->setPartialDate("death_date", $d[0], $d[1], $d[2]);
    $p->save();
    $days = $ensureContempt($p);
    echo "{$p->name}: born ".sprintf("%04d-%02d-%02d", $b[0], $b[1], $b[2]).", died ".sprintf("%04d-%02d-%02d", $d[0], $d[1], $d[2]).", age {$p->age}, contempt days={$days}\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
