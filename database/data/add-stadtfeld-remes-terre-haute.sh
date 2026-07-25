#!/usr/bin/env bash
#
# Add the September 30, 1936 Terre Haute "vagrancy" case (Earl Browder campaign
# party) to Charles Stadtfeld and Andrew Remes, the two co-arrestees not yet
# carrying it. Charles Stadtfeld is created fresh; Andrew Remes already exists
# (1957 Cleveland Taft-Hartley case) and is very likely the same Ohio Communist,
# so the Terre Haute case is added to his record and his bio gets a short note.
#
# Idempotent: the Terre Haute case is added only if not already present.
# Run from the repo root:
#   bash database/data/add-stadtfeld-remes-terre-haute.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;

$inst = Institution::firstOrCreate(["name" => "Terre Haute Jail"], ["city" => "Terre Haute", "state" => "Indiana"]);

$addCase = function (Prisoner $p) use ($inst) {
    if ($p->cases()->where("charges", "like", "%Terre Haute%")->exists()) {
        echo "  Terre Haute case already present for {$p->name}.\n";
        return;
    }
    $c = $p->cases()->make();
    $c->prisoner_id = $p->id;
    $c->charges = "Arrested for vagrancy on September 30, 1936, immediately after arriving by train in Terre Haute, Indiana with the Earl Browder campaign party. All charges were dropped.";
    $c->convicted = "Arrested September 30, 1936; all charges dropped (no trial or conviction)";
    $c->sentence = "Held about 25 hours in the Terre Haute jail; released October 1, 1936. No bail required.";
    $c->institution_id = $inst->id;
    $c->setPartialDate("arrest_date", 1936, 9, 30);
    $c->setPartialDate("incarceration_date", 1936, 9, 30);
    $c->setPartialDate("release_date", 1936, 10, 1);
    $c->save();
    echo "  added Terre Haute case to {$p->name} (days={$c->imprisoned_for_days}).\n";
};

// Charles Stadtfeld — create fresh if absent.
$stadt = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["charles stadtfeld"])->first();
if (! $stadt) {
    $stadt = Prisoner::create([
        "name" => "Charles Stadtfeld", "first_name" => "Charles", "last_name" => "Stadtfeld",
        "description" => "Charles Stadtfeld was a member of the Communist presidential candidate Earl Browder campaign party arrested at Terre Haute, Indiana on September 30, 1936. Police jailed the whole party — Browder, the writers Waldo Frank and Seymour Waldman, Stadtfeld and Andrew Remes — on a vagrancy charge in an effort to keep the campaign from speaking; all were held about 25 hours and released the next day with the charges dropped.",
        "state" => "Indiana", "gender" => "Male",
        "ideologies" => ["Communism"], "affiliation" => ["Communist Party USA"],
        "era" => "1930s", "in_custody" => false, "released" => true,
    ]);
    echo "created Charles Stadtfeld (slug: {$stadt->slug}).\n";
} else {
    echo "Charles Stadtfeld already exists (slug: {$stadt->slug}).\n";
}
$addCase($stadt);

// Andrew Remes — add the case to his existing record (or create if absent).
$remes = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["andrew remes"])->first();
if (! $remes) {
    $remes = Prisoner::create([
        "name" => "Andrew Remes", "first_name" => "Andrew", "last_name" => "Remes",
        "description" => "Andrew Remes was a Communist arrested at Terre Haute, Indiana on September 30, 1936 with the Earl Browder presidential campaign party (Browder, the writers Waldo Frank and Seymour Waldman, Charles Stadtfeld and Remes), jailed on a vagrancy charge and released the next day with all charges dropped.",
        "state" => "Ohio", "gender" => "Male",
        "ideologies" => ["Communism"], "affiliation" => ["Communist Party USA"],
        "era" => "1930s", "in_custody" => false, "released" => true,
    ]);
    echo "created Andrew Remes (slug: {$remes->slug}).\n";
} else {
    $note = " He was also among the Earl Browder campaign party jailed on a vagrancy charge at Terre Haute, Indiana on September 30, 1936 and released the next day with the charges dropped.";
    if ($remes->description && ! str_contains($remes->description, "Terre Haute")) {
        $remes->description = rtrim($remes->description)." ".ltrim($note);
        $remes->save();
        echo "appended Terre Haute note to existing Andrew Remes bio.\n";
    }
}
$addCase($remes);

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
