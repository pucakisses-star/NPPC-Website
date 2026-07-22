#!/usr/bin/env bash
#
# Fill in the Race field for prisoners who were missing it, inferred from each
# record's own description (stated ethnicity/nationality, a racially-defined
# movement/affiliation, self-identification, or documented biography) — not
# from names alone. Of 1,077 records missing race, 945 were classified with
# high or medium confidence; the rest were left blank as too ambiguous.
#
# Values use the site's existing taxonomy: White, Black, Hispanic, Asian,
# Native American, Middle Eastern. Care was taken that a person's solidarity
# work does not decide their race (a white activist doing Palestine/Puerto
# Rico/Black-liberation solidarity stays White). The per-record basis and
# confidence are kept in database/data/race-inferred.json for auditability.
#
# Idempotent and non-destructive: writes Race ONLY where the field is still
# empty. Run from the repo root:
#   bash database/data/set-inferred-race.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/race-inferred.json")), true);
if (! is_array($rows)) { echo "Could not read JSON\n"; return; }

$set = 0; $skip = 0; $missing = 0; $by = [];
foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { $missing++; continue; }
    if (! empty($p->race)) { $skip++; continue; }
    $p->race = $r["race"];
    $p->save();
    $by[$r["race"]] = ($by[$r["race"]] ?? 0) + 1;
    $set++;
}

echo "Set race on {$set}; skipped {$skip} that already had one; {$missing} not found.\n";
foreach ($by as $race => $n) { echo "  {$race}: {$n}\n"; }
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Inferred race filled in for records that were missing it."
