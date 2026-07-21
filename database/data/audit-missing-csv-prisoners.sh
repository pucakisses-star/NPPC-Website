#!/usr/bin/env bash
#
# READ-ONLY diagnostic. For the 7 names from the CSV audit that don't show
# up in the public API, report each one's true current state in the DB:
# whether it still exists, whether it's hidden via under_review, and the
# created_at / updated_at timestamps. For records still present but hidden,
# updated_at approximates when they were flagged. Records that are simply
# gone were hard-deleted (the model has no soft-deletes, so no delete
# timestamp is recoverable).
#
# Writes nothing. Run from the repo root:
#   bash database/data/audit-missing-csv-prisoners.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$targets = [
    "Yunseo Chung",
    "William Banks",
    "James \"Angry Bird\" White",
    "Sgt. Frank \"Greg\" Ford",
    "David Darst",
    "Matthew Hale",
    "William Edward Burghardt Du Bois",
];

foreach ($targets as $name) {
    // Match on a slugified name OR a loose name LIKE, ignoring scopes so we
    // can see under_review rows too.
    $slug = \Illuminate\Support\Str::slug($name);
    $last = \Illuminate\Support\Str::slug(preg_replace("/\".*\"/", "", $name));
    $q = \App\Models\Prisoner::withoutGlobalScopes()
        ->where("slug", $slug)
        ->orWhere("slug", "like", "%".preg_replace("/[^a-z0-9]+/i", "-", trim(preg_replace("/\".*\"/", "", $name)))."%")
        ->orWhere("name", "like", "%".trim(explode("\"", $name)[0])."%");
    $rows = $q->get(["id","name","slug","under_review","created_at","updated_at"]);

    if ($rows->isEmpty()) {
        echo str_pad($name, 36)." | GONE (hard-deleted — no timestamp)\n";
        continue;
    }
    foreach ($rows as $r) {
        $state = $r->under_review ? "HIDDEN (under_review)" : "VISIBLE";
        echo str_pad($name, 36)." | {$state} | slug={$r->slug} | created={$r->created_at} | updated={$r->updated_at}\n";
    }
}
echo "Done.\n";
'
