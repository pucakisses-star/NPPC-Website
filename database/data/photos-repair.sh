#!/usr/bin/env bash
#
# Photo repair pass, from the photos-audit.sh findings (July 2026):
#
#  1. RELINK broken records — records whose photo path points at a
#     missing file, where a surviving file for the same person exists
#     in storage under a variant name (e.g. dhoruba-bin-wahad points
#     at a lost "-<uuid>.webp" while plain dhoruba-bin-wahad.webp sits
#     orphaned; eugene-debs points at eugene-v-debs.jpeg).
#     Matching is by slug: exact "<slug>.<ext>" first, then
#     "<slug>-…" prefixed variants; applied only when unambiguous.
#  2. ATTACH orphans to photo-less records — storage files named
#     exactly "<slug>.<ext>" whose record has no photo at all.
#  3. REPORT what remains broken (those photos' files are gone from
#     both server and repo and must be re-sourced) and how many
#     orphans remain. Nothing is deleted.
#
# Idempotent and non-destructive: only fills/repoints photo fields;
# never removes files or clears fields.
#
# Run from the repo root:  bash database/data/photos-repair.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$disk = \Illuminate\Support\Facades\Storage::disk("public");
$files = collect($disk->files("prisoners"));
$byBase = $files->keyBy(fn ($f) => strtolower(basename($f)));

$findFor = function (string $slug) use ($files, $byBase) {
    foreach (["jpg", "jpeg", "png", "webp"] as $ext) {
        $k = strtolower("{$slug}.{$ext}");
        if (isset($byBase[$k])) { return $byBase[$k]; }
    }
    $pref = strtolower($slug) . "-";
    $matches = $files->filter(fn ($f) => str_starts_with(strtolower(basename($f)), $pref))->values();
    return $matches->count() === 1 ? $matches->first() : null;
};

$relinked = 0; $attached = 0; $stillBroken = [];

$withPhoto = \App\Models\Prisoner::withoutGlobalScopes()
    ->whereNotNull("photo")->where("photo", "!=", "")->get();
foreach ($withPhoto as $p) {
    if ($disk->exists($p->photo)) { continue; }
    $found = $findFor($p->slug);
    if ($found) {
        $p->photo = $found;
        $p->save();
        $relinked++;
        echo "RELINK {$p->slug} -> {$found}\n";
    } else {
        $stillBroken[] = "{$p->slug} -> {$p->photo}";
    }
}

$noPhoto = \App\Models\Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereNull("photo")->orWhere("photo", ""))->get();
$used = \App\Models\Prisoner::withoutGlobalScopes()
    ->whereNotNull("photo")->where("photo", "!=", "")->pluck("photo")
    ->map(fn ($x) => strtolower(basename($x)))->flip();
foreach ($noPhoto as $p) {
    foreach (["jpg", "jpeg", "png", "webp"] as $ext) {
        $k = strtolower("{$p->slug}.{$ext}");
        if (isset($byBase[$k]) && ! isset($used[$k])) {
            $p->photo = $byBase[$k];
            $p->save();
            $attached++;
            echo "ATTACH {$p->slug} -> {$byBase[$k]}\n";
            break;
        }
    }
}

echo "\nRelinked broken records: {$relinked}\n";
echo "Attached orphans to photo-less records: {$attached}\n";
echo "Still broken (files gone, need re-sourcing): " . count($stillBroken) . "\n";
foreach ($stillBroken as $s) { echo "  {$s}\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Paste the output back for the re-sourcing batch."
