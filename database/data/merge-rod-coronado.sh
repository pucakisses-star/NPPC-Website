#!/usr/bin/env bash
#
# Merge the two Rod Coronado records into one.
#
# Unlike the Preston and Smith merges, BOTH CASES ARE KEPT here: they are
# separate imprisonments, not duplicates --
#
#   1995  Michigan State University mink-research arson: sentenced to four
#         years and nine months after a 1994 arrest.
#   2010  Returned to federal prison on September 16, 2010 for accepting a
#         Facebook friend request, held to violate the probation terms from
#         the earlier ALF/ELF convictions.
#
# So the imprisonment counter should sum them; only genuinely identical cases
# would need collapsing afterwards with prisoners:dedupe-cases.
#
# The surviving record is displayed as "Rod Coronado" -- the name he is known
# by and the one on the BOP record -- with "Rodney Coronado" kept as an aka
# and Rodney as the first name. Everything either record had is folded in:
# BOP number, birthday, bios (both paragraphs kept, longest first), photo,
# ideologies and affiliations.
#
# Idempotent. Run from the repo root:
#   bash database/data/merge-rod-coronado.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$matches = Prisoner::withoutGlobalScopes()
    ->whereRaw("LOWER(name) LIKE ?", ["%coronado%"])
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "ABORT: no Coronado record found.\n"; exit(1); }

echo "matched ".$matches->count()." record(s):\n";
foreach ($matches as $m) {
    echo sprintf("  %-20s [%-20s] cases=%d bio=%d chars photo=%s bop=%s\n",
        $m->name, $m->slug, $m->cases->count(), strlen((string) $m->description),
        $m->photo ? "yes" : "no", $m->inmate_number ?: "-");
}
if ($matches->count() < 2) { echo "\nOnly one record -- nothing to merge.\n"; exit(0); }

$score = function (Prisoner $x) {
    $n = 0;
    foreach (["description", "photo", "birthdate", "inmate_number", "state", "era", "body", "gender", "race"] as $f) {
        if (! empty($x->{$f})) { $n++; }
    }

    return $n * 1000 + strlen((string) $x->description) + $x->cases->count();
};
$keep = $matches->sortByDesc($score)->first();
$dupes = $matches->reject(fn ($m) => $m->id === $keep->id);
echo "\nsurvivor: {$keep->name} [{$keep->slug}]\n";

$akas = array_filter(array_map("trim", explode(";", (string) $keep->aka)));

foreach ($dupes as $d) {
    // Keep BOTH bios -- they cover different parts of his history. The
    // survivor text goes first, the other is appended if it is not already
    // contained in it.
    $a = trim((string) $keep->description);
    $b = trim((string) $d->description);
    if ($b !== "" && $a !== "" && ! str_contains($a, $b) && ! str_contains($b, $a)) {
        $keep->description = $a."\n\n".$b;
        echo "  bio: appended the other record text (".strlen($b)." chars)\n";
    } elseif ($b !== "" && $a === "") {
        $keep->description = $b;
    }

    if ($d->body && ! $keep->body) { $keep->body = $d->body; }
    foreach ([$d->name, $d->aka] as $n) {
        foreach (array_map("trim", explode(";", (string) $n)) as $x) {
            if ($x !== "" && ! in_array($x, $akas, true)) { $akas[] = $x; }
        }
    }
    foreach (["gender", "race", "state", "address", "inmate_number", "era", "birthdate", "date_precision", "website", "twitter", "facebook", "instagram"] as $f) {
        if (empty($keep->{$f}) && ! empty($d->{$f})) { $keep->{$f} = $d->{$f}; echo "  {$f} taken from {$d->slug}\n"; }
    }
    foreach (["ideologies", "affiliation"] as $f) {
        $x = is_array($keep->{$f}) ? $keep->{$f} : [];
        $y = is_array($d->{$f}) ? $d->{$f} : [];
        $merged = array_values(array_unique(array_merge($x, $y)));
        if ($merged !== $x) { $keep->{$f} = $merged; echo "  {$f}: ".implode(", ", $merged)."\n"; }
    }
    if ($d->photo && empty($keep->photo)) { $keep->photo = $d->photo; echo "  photo taken from {$d->slug}\n"; }

    // Both imprisonments are real -- move the cases across, do not drop them.
    $moved = $d->cases()->update(["prisoner_id" => $keep->id]);
    if ($moved) { echo "  moved {$moved} case(s) from {$d->slug} (kept, not merged)\n"; }
    \App\Models\PodcastEpisode::where("prisoner_id", $d->id)->update(["prisoner_id" => $keep->id]);
    \App\Models\CalendarEntry::where("prisoner_id", $d->id)->update(["prisoner_id" => $keep->id]);

    $d->delete();
    echo "  deleted duplicate record {$d->slug}\n";
}

$keep->name = "Rod Coronado";
$keep->first_name = "Rodney";
$keep->last_name = "Coronado";
$keep->aka = implode("; ", array_values(array_filter($akas, fn ($x) => $x !== "Rod Coronado")));
$keep->save();

echo "\nresult: {$keep->name} [{$keep->slug}]\n";
echo "  aka:        ".($keep->aka ?: "(none)")."\n";
echo "  BOP:        ".($keep->inmate_number ?: "(none)")."\n";
echo "  born:       ".($keep->birthdate ? $keep->birthdate->toDateString()." (".$keep->datePrecisionFor("birthdate").")" : "-")."\n";
echo "  ideologies: ".(is_array($keep->ideologies) ? implode(", ", $keep->ideologies) : "-")."\n";
echo "  affiliation:".(is_array($keep->affiliation) ? implode(", ", $keep->affiliation) : "-")."\n";

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

echo "  cases (".$keep->cases()->count()."):\n";
foreach ($keep->cases()->orderBy("incarceration_date")->get() as $c) {
    echo "    ".substr($c->id, 0, 8)."  inc=".($c->incarceration_date ?: "-")." rel=".($c->release_date ?: "-")
        ." days=".($c->imprisoned_for_days ?? "null")."  ".substr((string) $c->charges, 0, 50)."\n";
}
echo "  (both imprisonments are genuine, so the counter sums them --\n";
echo "   check with: bash database/data/audit-duplicate-cases.sh)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
