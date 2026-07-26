#!/usr/bin/env bash
#
# Remove the three duplicate colonial records surfaced by
# list-zero-sort-prisoners.sh: john-flanagan-2, robert-porter-2 and
# thomas-mccomb-2. Each is deleted ONLY if the original record (same name,
# base slug) exists; any cases the duplicate has that the original lacks
# (matched on charges text) are moved to the original first, so no data is
# lost. Prints exactly what it does.
#
# Idempotent. Run from the repo root:
#   bash database/data/remove-duplicate-colonial-records.sh
set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

foreach (["john-flanagan-2", "robert-porter-2", "thomas-mccomb-2"] as $dupSlug) {
    $dup = Prisoner::withoutGlobalScopes()->where("slug", $dupSlug)->first();
    if (! $dup) { echo "{$dupSlug}: already gone.\n"; continue; }

    $baseSlug = preg_replace("/-2$/", "", $dupSlug);
    $orig = Prisoner::withoutGlobalScopes()->where("slug", $baseSlug)->first();
    if (! $orig) { echo "{$dupSlug}: SKIPPED — no original at slug {$baseSlug}.\n"; continue; }
    if (strcasecmp(trim($orig->name), trim($dup->name)) !== 0) {
        echo "{$dupSlug}: SKIPPED — name mismatch ({$dup->name} vs {$orig->name}).\n"; continue;
    }

    $moved = 0;
    foreach ($dup->cases()->get() as $c) {
        $exists = $orig->cases()->where("charges", $c->charges)->exists();
        if ($exists) { $c->delete(); continue; }
        $c->prisoner_id = $orig->id;
        $c->save();
        $moved++;
    }
    $dup->delete();
    echo "{$dupSlug}: deleted duplicate (kept {$baseSlug}, moved {$moved} unique case(s)).\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
