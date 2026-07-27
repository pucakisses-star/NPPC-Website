#!/usr/bin/env bash
#
# Merge the duplicate Richard Johnson records -- the Boston arms-technology
# defendant convicted August 20, 1990 with Christina Reid and Martin Quigley.
#
# The two cards differ by a year in AGE. Age is derived from birthdate, and a
# year-only birthdate is stored as January 1 of that year, so a record with
# "1948" reads a year older than one with a full date later in 1948. This
# script prints both stored birthdates WITH THEIR PRECISION so the cause is
# visible, then keeps the most precise one (day beats month beats year). If
# the two disagree on the YEAR as well, it says so and keeps neither silently
# -- that needs a documented date.
#
# Survivor: the record with the most data. It is then displayed as "Richard
# Johnson" with the middle name Clark in its own field (matching how Joseph W.
# Smith is handled), keeping "Richard Clark Johnson" as an aka.
#
# The portrait from the "Richard Johnson" card is kept regardless of which
# record survives. Everything only the duplicate had is folded across first --
# BOP number, affiliation, ideologies, bio, socials -- and its cases are moved to
# the survivor rather than deleted, so no case data is lost.
#
# Idempotent. Run from the repo root:
#   bash database/data/merge-richard-clark-johnson.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["richard-johnson", "richard-clark-johnson"])
        ->orWhereRaw("LOWER(name) LIKE ? AND LOWER(name) LIKE ?", ["richard%", "%johnson"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "ABORT: no Richard Johnson record found.\n"; exit(1); }

echo "matched ".$matches->count()." record(s):\n";
foreach ($matches as $m) {
    $prec = $m->birthdate ? $m->datePrecisionFor("birthdate") : "-";
    echo sprintf(
        "  %-26s [%-24s] born=%-12s precision=%-6s age=%-4s cases=%d bio=%d chars\n",
        $m->name, $m->slug,
        $m->birthdate ? $m->birthdate->toDateString() : "-",
        $prec, $m->age ?? "-", $m->cases->count(), strlen((string) $m->description),
    );
}
if ($matches->count() < 2) { echo "\nOnly one record -- nothing to merge.\n"; exit(0); }

$score = function (Prisoner $x) {
    $n = 0;
    foreach (["description", "photo", "birthdate", "inmate_number", "state", "era", "body", "gender", "race"] as $f) {
        if (! empty($x->{$f})) { $n++; }
    }

    return $n * 100 + strlen((string) $x->description) + $x->cases->count();
};
$keep = $matches->sortByDesc($score)->first();
$dupes = $matches->reject(fn ($m) => $m->id === $keep->id);
echo "\nsurvivor: {$keep->name} [{$keep->slug}]\n";

// The portrait on the "Richard Johnson" card is the one to keep, whichever
// record survives the merge. Captured before anything is deleted.
$preferred = $matches->first(fn ($m) => $m->slug === "richard-johnson")
    ?? $matches->first(fn ($m) => strtolower($m->name) === "richard johnson");
$preferredPhoto = $preferred && $preferred->photo ? $preferred->photo : null;
if ($preferredPhoto) { echo "  photo to keep: {$preferredPhoto} (from {$preferred->slug})\n"; }

// --- Birthdate: keep the most precise; flag a genuine year disagreement ---
$rank = ["day" => 3, "month" => 2, "year" => 1];
$dated = $matches->filter(fn ($m) => (bool) $m->birthdate);
if ($dated->count() > 1) {
    $years = $dated->map(fn ($m) => (int) $m->birthdate->year)->unique()->values();
    $best = $dated->sortByDesc(fn ($m) => $rank[$m->datePrecisionFor("birthdate")] ?? 0)->first();
    if ($years->count() > 1) {
        echo "  WARNING: the records disagree on the birth YEAR (".implode(", ", $years->all())."),\n";
        echo "           not just precision. Keeping ".$best->birthdate->toDateString()."\n";
        echo "           (precision ".$best->datePrecisionFor("birthdate").") -- verify against a source.\n";
    } else {
        echo "  birthdate: same year, different precision -- keeping ".$best->birthdate->toDateString();
        echo " (".$best->datePrecisionFor("birthdate").") which resolves the age mismatch\n";
    }
    if ($best->id !== $keep->id) {
        $keep->birthdate = $best->birthdate;
        $prec = is_array($keep->date_precision) ? $keep->date_precision : [];
        $bestPrec = $best->datePrecisionFor("birthdate");
        if ($bestPrec === "day") { unset($prec["birthdate"]); } else { $prec["birthdate"] = $bestPrec; }
        $keep->date_precision = $prec ?: null;
    }
} elseif ($dated->count() === 1 && ! $keep->birthdate) {
    $keep->birthdate = $dated->first()->birthdate;
    $keep->date_precision = $dated->first()->date_precision;
    echo "  birthdate taken from the duplicate\n";
}

// --- Fold everything else across ---
$akas = array_filter(array_map("trim", explode(";", (string) $keep->aka)));
foreach ($dupes as $d) {
    if ($d->description && strlen($d->description) > strlen((string) $keep->description)) {
        $keep->description = $d->description;
        echo "  bio taken from {$d->slug} (longer)\n";
    }
    if ($d->body && ! $keep->body) { $keep->body = $d->body; }
    foreach ([$d->name, $d->aka] as $n) {
        foreach (array_map("trim", explode(";", (string) $n)) as $a) {
            if ($a !== "" && ! in_array($a, $akas, true)) { $akas[] = $a; }
        }
    }
    foreach (["gender", "race", "state", "address", "inmate_number", "era", "website", "twitter", "facebook", "instagram"] as $f) {
        if (empty($keep->{$f}) && ! empty($d->{$f})) { $keep->{$f} = $d->{$f}; echo "  {$f} <- {$d->{$f}}\n"; }
    }
    foreach (["ideologies", "affiliation"] as $f) {
        $a = is_array($keep->{$f}) ? $keep->{$f} : [];
        $b = is_array($d->{$f}) ? $d->{$f} : [];
        $merged = array_values(array_unique(array_merge($a, $b)));
        if ($merged !== $a) { $keep->{$f} = $merged; echo "  {$f}: ".implode(", ", $merged)."\n"; }
    }
    if (! $preferredPhoto && $d->photo && empty($keep->photo)) { $keep->photo = $d->photo; echo "  photo taken from {$d->slug}\n"; }

    // Move cases rather than dropping them -- overlapping copies are collapsed
    // afterwards by prisoners:dedupe-cases.
    $moved = $d->cases()->update(["prisoner_id" => $keep->id]);
    if ($moved) { echo "  moved {$moved} case(s) from {$d->slug}\n"; }
    \App\Models\PodcastEpisode::where("prisoner_id", $d->id)->update(["prisoner_id" => $keep->id]);
    \App\Models\CalendarEntry::where("prisoner_id", $d->id)->update(["prisoner_id" => $keep->id]);

    $d->delete();
    echo "  deleted duplicate {$d->slug}\n";
}

// --- Name: display without the middle name, middle name in its own field ---
$keep->name = "Richard Johnson";
$keep->first_name = "Richard";
$keep->middle_name = "Clark";
$keep->last_name = "Johnson";
$keep->aka = implode("; ", array_values(array_filter($akas, fn ($a) => $a !== "Richard Johnson")));
$keep->in_custody = false;
$keep->awaiting_trial = false;
$keep->released = true;
$keep->save();

echo "\nresult: {$keep->name} (middle name Clark) [{$keep->slug}]\n";
echo "  aka:       ".($keep->aka ?: "(none)")."\n";
echo "  BOP:       ".($keep->inmate_number ?: "(none)")."\n";
echo "  born:      ".($keep->birthdate ? $keep->birthdate->toDateString()." (".$keep->datePrecisionFor("birthdate").")" : "-")." age {$keep->age}\n";
echo "  ideology:  ".(is_array($keep->ideologies) ? implode(", ", $keep->ideologies) : "-")."\n";
echo "  affil:     ".(is_array($keep->affiliation) ? implode(", ", $keep->affiliation) : "-")."\n";
echo "  cases:     ".$keep->cases()->count()."\n";

// Use the Richard Johnson portrait, copied to the survivor slug path.
if ($preferredPhoto) {
    $srcAbs = storage_path("app/public/{$preferredPhoto}");
    $dstRel = "prisoners/{$keep->slug}.jpg";
    if (is_file($srcAbs)) {
        File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
        File::copy($srcAbs, storage_path("app/public/{$dstRel}"));
        $keep->photo = $dstRel;
        echo "  photo set from the Richard Johnson card -> {$dstRel}\n";
    } else {
        $keep->photo = $preferredPhoto;   // file not on disk here; keep the path
        echo "  photo set from the Richard Johnson card -> {$preferredPhoto} (file not found on disk)\n";
    }
    $keep->save();
}

// Photo path follows the slug.
if ($keep->photo) {
    $newRel = "prisoners/{$keep->slug}.jpg";
    $oldAbs = storage_path("app/public/{$keep->photo}");
    if ($keep->photo !== $newRel && is_file($oldAbs)) {
        File::copy($oldAbs, storage_path("app/public/{$newRel}"));
        $keep->photo = $newRel;
        $keep->save();
        echo "  photo re-pointed -> {$newRel}\n";
    }
}

if ($keep->cases()->count() > 1) {
    echo "\nNOTE: the merged record now has ".$keep->cases()->count()." cases, which will double the\n";
    echo "imprisonment counter if they describe the same conviction. Collapse them with:\n";
    echo "  php artisan prisoners:dedupe-cases --slug={$keep->slug} --mode=all\n";
    echo "  php artisan prisoners:dedupe-cases --slug={$keep->slug} --mode=all --apply\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
