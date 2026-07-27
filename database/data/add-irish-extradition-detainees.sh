#!/usr/bin/env bash
#
# Two earlier Irish republican extradition cases in the United States:
#
#   Desmond Mackin           -- extended federal and immigration detention
#                               during litigation over a British extradition
#                               request.
#   Peter John Gabriel McMullen -- arrested and held for extradition on
#                               December 24, 1986, after earlier immigration
#                               proceedings.
#
# Only what the source states is recorded. Neither has a documented release
# date, so release_date is left null rather than invented, and the records are
# flagged released (not in custody) so no count-to-today duration appears on
# the site. McMullen gets the documented 1986-12-24 detention start; Mackin
# has no exact date on record, so his case carries the narrative only.
#
# Idempotent -- updates the records if they already exist. Run from the repo
# root:
#   bash database/data/add-irish-extradition-detainees.sh
# then place them in the sort order:
#   php artisan prisoners:auto-place-zero-sort

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// [name, first, last, description, arrest [y,m,d] or null, sentence-note]
$people = [
    [
        "Desmond Mackin", "Desmond", "Mackin",
        "Desmond Mackin was an Irish republican held in extended United States federal and immigration detention while the courts considered a British request for his extradition. His litigation, like other Irish republican extradition cases of the period, turned on whether the offence alleged against him was political in character and therefore outside the reach of the extradition treaty.",
        null,
        "Held in federal and immigration detention during extradition litigation. No release date is documented in the available source.",
    ],
    [
        "Peter John Gabriel McMullen", "Peter", "McMullen",
        "Peter John Gabriel McMullen was an Irish republican held in United States custody in connection with a British extradition request. After earlier immigration proceedings, he was arrested and held for extradition on December 24, 1986. His case was among the extradition fights that tested whether Irish republican offences would be treated as political offences by United States courts.",
        [1986, 12, 24],
        "Arrested and held for extradition on December 24, 1986, following earlier immigration proceedings. No release date is documented in the available source.",
    ],
];

foreach ($people as [$name, $first, $last, $desc, $arrest, $note]) {
    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first()
        ?? Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) LIKE ?", ["%".strtolower($last)."%"])->first();

    if (! $p) {
        $p = Prisoner::create([
            "name" => $name, "first_name" => $first, "last_name" => $last,
            "gender" => "Male", "era" => "1980s",
            "ideologies" => ["Irish Republicanism", "Anti-Imperialism"],
            "affiliation" => ["Irish Republican Army"],
            "in_custody" => false, "released" => true,
            "description" => $desc,
        ]);
        echo "created {$p->name} (slug {$p->slug})\n";
    } else {
        echo "updating {$p->name} (slug {$p->slug})\n";
        if (! $p->description) { $p->description = $desc; }
        if (! $p->gender) { $p->gender = "Male"; }
        if (! $p->era) { $p->era = "1980s"; }
    }

    $p->first_name = $first;
    $p->last_name = $last;
    // No documented release, but these are long-closed 1980s cases: flag them
    // released so the counter does not run to today.
    $p->in_custody = false;
    $p->awaiting_trial = false;
    $p->released = true;
    $p->save();

    $c = $p->cases()->where("charges", "like", "%xtradition%")->first();
    if (! $c) {
        $c = $p->cases()->create([
            "charges" => "United States detention on a British extradition request arising from Irish republican activity.",
        ]);
    }
    $c->convicted = "No United States conviction — held pending extradition proceedings";
    $c->sentence = $note;
    if ($arrest) {
        $c->setPartialDate("arrest_date", $arrest[0], $arrest[1], $arrest[2]);
        $c->setPartialDate("incarceration_date", $arrest[0], $arrest[1], $arrest[2]);
    }
    $c->save();
    echo "  case: inc=".($c->incarceration_date ?: "-")." rel=".($c->release_date ?: "-")." days=".($c->imprisoned_for_days ?? "null")."\n";
    if ($p->sort_order == 0) { echo "  sort_order 0 -- run prisoners:auto-place-zero-sort\n"; }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
