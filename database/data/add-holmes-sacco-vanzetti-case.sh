#!/usr/bin/env bash
#
# Add Ailene Holmes's earlier arrest as a separate case: at the 1930 Van Etten
# trial she testified she had previously been arrested and convicted for
# picketing outside a Boston courthouse during the campaign for clemency for
# Nicola Sacco and Bartolomeo Vanzetti (most likely the 1927 final clemency
# effort). No court record has been located, so the exact date, charge and
# sentence are unknown; the year is recorded at year precision (~1927) and the
# uncertainty is stated in the charge text. No institution is attached (whether
# she was jailed is undocumented), so this case adds no imprisonment days.
#
# Idempotent: skips if a Sacco/Vanzetti case already exists for her. Run from
# the repo root:
#   bash database/data/add-holmes-sacco-vanzetti-case.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "ailene-holmes")
    ->orWhereRaw("LOWER(name) = ?", ["ailene holmes"])
    ->first();

if (! $p) { echo "Ailene Holmes not found — nothing to add.\n"; return; }

$exists = $p->cases()->where("charges", "like", "%Sacco%")->exists();
if ($exists) { echo "Sacco/Vanzetti case already present — skipping.\n"; return; }

$case = $p->cases()->create([
    "charges" => "Arrested and convicted for picketing outside a Boston courthouse during the campaign for clemency for Nicola Sacco and Bartolomeo Vanzetti (about 1927). The exact date, specific charge and sentence are not documented.",
    "convicted" => "Convicted (Boston, about 1927)",
]);
$case->setPartialDate("arrest_date", 1927, null, null);
$case->save();

echo "Added Sacco/Vanzetti picketing case to {$p->name} (~1927).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
