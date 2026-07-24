#!/usr/bin/env bash
#
# Set Carl Haessler's birth (August 5, 1888) and death (December 8, 1972) dates.
#
# Idempotent. Run from the repo root:
#   bash database/data/set-carl-haessler-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()
    ->where("slug", "carl-haessler")
    ->orWhereRaw("LOWER(name) = ?", ["carl haessler"])
    ->first();

if (! $p) { echo "Carl Haessler not found.\n"; return; }

$p->birthdate = "1888-08-05";
$p->death_date = "1972-12-08";
$p->save();

echo "{$p->name}: born {$p->birthdate}, died {$p->death_date}\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Carl Haessler birth/death dates set."
