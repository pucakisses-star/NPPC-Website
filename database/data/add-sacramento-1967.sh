#!/usr/bin/env bash
#
# The May 2, 1967 Black Panther demonstration at the California State
# Capitol against the Mulford Act — custody outcomes (July 2026).
#
# Chronology, from Bobby Seale's account and contemporaneous reporting:
# Seale was released on bail around midnight after the arrest; most adult
# Panthers were bailed out about 6 p.m. on May 4; Bobby Hutton and four
# other minors were still in juvenile custody when the adults left, and
# Hutton was free by May 22, 1967 (when he was rearrested in a separate
# firearms incident) — so his Capitol confinement lasted at least about
# two days but fewer than twenty. In the negotiated resolution, Seale and
# Warren Tucker received the longest sentences, six months each; Seale
# entered jail August 8, 1967 and was released December 8, 1967. Other
# defendants received shorter misdemeanor terms.
#
#  1. Adds Warren Tucker (missing entirely).
#  2. Adds the Sacramento case row to Bobby Seale (his record held only
#     the New Haven case) and to Bobby Hutton (his record had no case
#     rows at all despite the description mentioning the arrest).
#  3. Fills the arrest date on Mark Comfort's existing Sacramento case.
#
# Idempotent: prisoner:add refuses duplicates; case rows are created only
# when no existing row mentions the Capitol case; fills are fill-if-empty.
#
# Run from the repo root:  bash database/data/add-sacramento-1967.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Warren Tucker","first_name":"Warren","last_name":"Tucker","description":"Warren Tucker was one of the Black Panthers arrested after the May 2, 1967 demonstration at the California State Capitol in Sacramento, where the party walked armed into the statehouse to protest the Mulford Act, the bill drafted to outlaw the Panthers'"'"' armed patrols by banning the public carrying of loaded firearms. In the negotiated resolution of the Capitol charges, Tucker and party chairman Bobby Seale received the longest sentences — six months each on misdemeanor charges of disrupting the legislative session — while other defendants received shorter terms. Documented in Bobby Seale'"'"'s account of the case; further biographical details have not been located.","state":"California","race":"Black","gender":"Male","ideologies":["Black liberation"],"affiliation":["Black Panther Party"],"era":"1960s","released":true,"cases":[{"charges":"Misdemeanor disruption of the legislative session — May 2, 1967 armed Black Panther protest against the Mulford Act at the California State Capitol","arrest_date":"1967-05-02","convicted":"Yes — negotiated plea","sentence":"Six months, the longest sentence in the case alongside Bobby Seale"}]}' || true

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$hasCapitolCase = function ($p): bool {
    foreach ($p->cases as $c) {
        $ch = is_array($c->charges) ? implode(" ", $c->charges) : (string) $c->charges;
        if (stripos($ch, "Mulford") !== false || stripos($ch, "Sacramento") !== false || stripos($ch, "Capitol") !== false) { return true; }
    }
    return false;
};

// Bobby Seale: add the Sacramento case alongside New Haven.
$p = $find("bobby-seale");
if ($p && ! $hasCapitolCase($p)) {
    $p->cases()->create([
        "charges" => "Misdemeanor disruption of the legislative session — May 2, 1967 armed Black Panther protest against the Mulford Act at the California State Capitol",
        "arrest_date" => "1967-05-02",
        "incarceration_date" => "1967-08-08",
        "release_date" => "1967-12-08",
        "convicted" => "Yes — negotiated plea; released on bail around midnight after the arrest, he later entered jail August 8, 1967",
        "sentence" => "Six months (the longest in the case, with Warren Tucker); served August 8 to December 8, 1967",
        "imprisoned_for_days" => 122,
    ]);
    echo "CASE bobby-seale\n";
}

// Bobby Hutton: his record had no case rows at all.
$p = $find("bobby-hutton");
if ($p && $p->cases()->count() === 0) {
    $p->cases()->create([
        "charges" => "Arrested at the May 2, 1967 armed Black Panther protest against the Mulford Act at the California State Capitol; as a sixteen-year-old he was held in juvenile custody",
        "arrest_date" => "1967-05-02",
        "incarceration_date" => "1967-05-02",
        "convicted" => "Held in juvenile custody after the adult Panthers were bailed out on May 4; free by May 22, 1967 — his confinement lasted at least about two days but fewer than twenty. No record of a later custodial sentence from the Capitol case has been located.",
    ]);
    echo "CASE bobby-hutton\n";
}

// Mark Comfort: fill the arrest date on his existing Sacramento case.
$p = $find("mark-comfort");
if ($p) {
    foreach ($p->cases as $c) {
        $ch = is_array($c->charges) ? implode(" ", $c->charges) : (string) $c->charges;
        if (stripos($ch, "Sacramento") !== false && empty($c->arrest_date)) {
            $c->arrest_date = "1967-05-02";
            $c->save();
            echo "CASE mark-comfort arrest date\n";
        }
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sacramento 1967 Capitol case applied."
