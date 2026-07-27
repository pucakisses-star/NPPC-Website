#!/usr/bin/env bash
#
# Kevin Artt -- correct the surname and record his middle name.
#
#   Name         "Kevin Art"  ->  "Kevin Artt"
#   Middle name  Barry John   (so the full name reads Kevin Barry John Artt)
#
# One of the Maze prison escapers fought over in the United States extradition
# cases, released on bail in January 1996 alongside Pol Brennan and Terence
# Damien Kirby.
#
# The slug follows the corrected spelling, so the public URL moves from
# /prisoner/kevin-art to /prisoner/kevin-artt. That is the point -- the old
# one embeds the misspelling -- but pass KEEP_SLUG=1 to hold the existing URL
# if anything already links to it.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-kevin-artt-name.sh
#   KEEP_SLUG=1 bash database/data/fix-kevin-artt-name.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["kevin-art", "kevin-artt"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["kevin art", "kevin artt"]))
    ->first();
if (! $p) { echo "NOT FOUND: Kevin Art / Kevin Artt\n"; exit(1); }

$oldName = $p->name;
$oldSlug = $p->slug;
$oldPhoto = $p->photo;

$p->name = "Kevin Artt";
$p->first_name = "Kevin";
$p->middle_name = "Barry John";
$p->last_name = "Artt";
$p->save();   // renaming regenerates the slug

if (getenv("KEEP_SLUG") === "1" && $p->slug !== $oldSlug) {
    $p->slug = $oldSlug;
    $p->save();
    echo "slug held at {$oldSlug} (KEEP_SLUG=1)\n";
}

echo "{$oldName} [{$oldSlug}]  ->  {$p->name} [{$p->slug}]\n";
echo "  full name: Kevin Barry John Artt (middle name stored separately)\n";

// Photo follows the regenerated slug so the image does not 404.
if ($oldPhoto) {
    $newRel = "prisoners/{$p->slug}.jpg";
    $oldAbs = storage_path("app/public/{$oldPhoto}");
    if ($oldPhoto !== $newRel && is_file($oldAbs)) {
        File::copy($oldAbs, storage_path("app/public/{$newRel}"));
        $p->photo = $newRel;
        $p->save();
        echo "  photo re-pointed -> {$newRel}\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
