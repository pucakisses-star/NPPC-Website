#!/usr/bin/env bash
#
# Merge the duplicate records for Morrie Prestons Goldfield co-defendant:
# "Joseph W. Smith" is folded into "Joseph William Smith" and deleted.
#
# The surviving record keeps the correct custody dates -- incarceration begins
# at his ARREST on 1907-03-12, not his 1907-05-09 conviction, giving the
# documented 4 years, 8 months, 2 days to his 1911-11-14 parole. The duplicate
# had wrongly used the conviction date as the start, which is why it showed
# 4 years, 6 months, 5 days (58 days of pretrial detention missing).
#
# The merged record is displayed as "Joseph W. Smith" with middle name Wilson;
# "Joseph William Smith" is retained as an aka and the slug becomes
# joseph-w-smith (freed by deleting the duplicate first).
#
# Moved across from the duplicate: the drawing, the fuller biography, the
# institution (Nevada State Prison), and any field the survivor lacks
# (gender, state, inmate number, socials, ideologies, affiliations). The
# duplicate cases are removed with it, since the survivor case now holds the
# authoritative dates.
#
# Idempotent -- reports "already merged" if the duplicate is gone. Run from the
# repo root:
#   bash database/data/merge-joseph-w-smith.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$keep = Prisoner::withoutGlobalScopes()->where("slug", "joseph-william-smith")->first();
$dupe = Prisoner::withoutGlobalScopes()->where("slug", "joseph-w-smith")->first();

if (! $keep) { echo "ABORT: survivor joseph-william-smith not found.\n"; exit(1); }
if (! $dupe) {
    // Already merged; still make sure the naming stuck.
    $already = Prisoner::withoutGlobalScopes()->where("slug", "joseph-w-smith")->first() ?? $keep;
    $already->name = "Joseph W. Smith";
    $already->first_name = "Joseph";
    $already->middle_name = "Wilson";
    $already->last_name = "Smith";
    $already->save();
    echo "Already merged. Naming enforced: {$already->name} (middle name Wilson, slug {$already->slug}).\n";
    exit(0);
}

echo "survivor:  {$keep->name} (sort {$keep->sort_order})\n";
echo "duplicate: {$dupe->name} (sort {$dupe->sort_order})\n";

// --- Biography: take the duplicate fuller text ---
if ($dupe->description && strlen($dupe->description) > strlen((string) $keep->description)) {
    $keep->description = $dupe->description;
    echo "  bio replaced with the longer version (".strlen($dupe->description)." chars)\n";
}
if ($dupe->body && ! $keep->body) { $keep->body = $dupe->body; echo "  page body copied\n"; }

// --- Collect every name variant; the primary name is filtered out below ---
$akas = array_filter(array_map("trim", explode(";", (string) $keep->aka)));
foreach ([$keep->name, $dupe->name] as $n) {
    if ($n && ! in_array($n, $akas, true)) { $akas[] = $n; }
}
if ($dupe->aka) {
    foreach (array_map("trim", explode(";", $dupe->aka)) as $a) {
        if ($a !== "" && ! in_array($a, $akas, true)) { $akas[] = $a; }
    }
}

// --- Fill any scalar field the survivor is missing ---
foreach (["gender", "race", "state", "address", "inmate_number", "era", "website", "twitter", "facebook", "instagram", "lat", "lng"] as $f) {
    if (empty($keep->{$f}) && ! empty($dupe->{$f})) {
        $keep->{$f} = $dupe->{$f};
        echo "  {$f} <- {$dupe->{$f}}\n";
    }
}

// --- Union the taxonomies ---
foreach (["ideologies", "affiliation"] as $f) {
    $a = is_array($keep->{$f}) ? $keep->{$f} : [];
    $b = is_array($dupe->{$f}) ? $dupe->{$f} : [];
    $merged = array_values(array_unique(array_merge($a, $b)));
    if ($merged !== $a) { $keep->{$f} = $merged; echo "  {$f}: ".implode(", ", $merged)."\n"; }
}

// --- Drawing ---
if ($dupe->photo && empty($keep->photo)) {
    $srcAbs = storage_path("app/public/{$dupe->photo}");
    $dstRel = "prisoners/{$keep->slug}.jpg";
    if (is_file($srcAbs)) {
        File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
        File::copy($srcAbs, storage_path("app/public/{$dstRel}"));
        $keep->photo = $dstRel;
        echo "  photo copied -> {$dstRel}\n";
    } else {
        $keep->photo = $dupe->photo;   // file missing locally; reuse the path
        echo "  photo re-pointed -> {$dupe->photo} (source file not on disk)\n";
    }
}

// --- Institution from the duplicate case, if the survivor lacks one ---
$keepCase = $keep->cases()->orderBy("created_at")->first();
$dupeCase = $dupe->cases()->orderBy("created_at")->first();
if ($keepCase && $dupeCase) {
    if (! $keepCase->institution_id && $dupeCase->institution_id) {
        $keepCase->institution_id = $dupeCase->institution_id;
        $keepCase->save();
        echo "  institution copied to the surviving case\n";
    }
    foreach (["prosecutor", "judge"] as $f) {
        if (empty($keepCase->{$f}) && ! empty($dupeCase->{$f})) {
            $keepCase->{$f} = $dupeCase->{$f};
            $keepCase->save();
            echo "  case {$f} <- {$dupeCase->{$f}}\n";
        }
    }
}

// --- Reassign anything else that points at the duplicate ---
$moved = \App\Models\PodcastEpisode::where("prisoner_id", $dupe->id)->update(["prisoner_id" => $keep->id]);
if ($moved) { echo "  moved {$moved} podcast episode(s)\n"; }
$moved = \App\Models\CalendarEntry::where("prisoner_id", $dupe->id)->update(["prisoner_id" => $keep->id]);
if ($moved) { echo "  moved {$moved} calendar entr(ies)\n"; }

// --- Delete the duplicate (its cases cascade) ---
$dupeCases = $dupe->cases()->count();
$dupe->delete();
echo "  deleted duplicate joseph-w-smith and its {$dupeCases} case(s)\n";

// --- Display name: Joseph W. Smith, middle name Wilson. Done after the
// delete so the joseph-w-smith slug is free for the regenerated slug. ---
$oldPhoto = $keep->photo;
$keep->name = "Joseph W. Smith";
$keep->first_name = "Joseph";
$keep->middle_name = "Wilson";
$keep->last_name = "Smith";
$keep->aka = implode("; ", array_values(array_filter($akas, fn ($a) => $a !== $keep->name)));
$keep->save();   // regenerates the slug from the new name
echo "  renamed to {$keep->name} (slug {$keep->slug}), middle name Wilson\n";
echo "  aka: {$keep->aka}\n";

// Photo path follows the regenerated slug.
if ($oldPhoto) {
    $newRel = "prisoners/{$keep->slug}.jpg";
    $oldAbs = storage_path("app/public/{$oldPhoto}");
    if ($oldPhoto !== $newRel && is_file($oldAbs)) {
        File::copy($oldAbs, storage_path("app/public/{$newRel}"));
        $keep->photo = $newRel;
        $keep->save();
        echo "  photo re-pointed -> {$newRel}\n";
    }
}

$keep->refresh();
$c = $keep->cases()->orderBy("created_at")->first();
echo "\nresult: {$keep->name}\n";
echo "  aka:     {$keep->aka}\n";
echo "  photo:   ".($keep->photo ?: "(none)")."\n";
if ($c) {
    echo "  case:    ".($c->incarceration_date ?: "-")." -> ".($c->release_date ?: "-")." days=".($c->imprisoned_for_days ?? "null")." (expected 1708)\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
