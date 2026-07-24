#!/usr/bin/env bash
#
# Clean Fred Suttle's contaminated description. His bio field was imported with a
# huge footnote/citation apparatus appended after the genuine three-sentence
# biography: a "NOTES" section, hundreds of "Calif. App." case citations, ACLU
# archive references, and a generic "Political Prisoners Who Died While
# Incarcerated" chapter blurb (which is doubly wrong -- Suttle was released, not
# killed in custody). This replaces the description with just the real bio,
# stored in database/data/text/fred-suttle-bio.txt.
#
# Idempotent: only replaces a description that still carries the citation dump.
# Run from the repo root:
#   bash database/data/clean-fred-suttle-bio.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()
    ->where("slug", "fred-suttle")
    ->orWhereRaw("LOWER(name) = ?", ["fred suttle"])
    ->first();

if (! $p) { echo "Fred Suttle not found.\n"; return; }

$clean = trim(file_get_contents(base_path("database/data/text/fred-suttle-bio.txt")));
$old = (string) $p->description;

echo "Old description length: ".strlen($old)."\n";

$contaminated = str_contains($old, "Calif. App.")
    || str_contains($old, "ACLU Archives")
    || str_contains($old, "Industrial Worker")
    || strlen($old) > strlen($clean) + 200;

if ($contaminated) {
    $p->description = $clean;
    $p->save();
    echo "Replaced description. New length: ".strlen($clean)."\n";
} else {
    echo "Description already clean; no change.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Fred Suttle bio cleaned."
