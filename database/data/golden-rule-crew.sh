#!/usr/bin/env bash
#
# Golden Rule crew enrichment (July 2026). The five men who tried to sail the
# ketch Golden Rule into the Eniwetok nuclear-test zone in 1958 — Albert
# Bigelow, William Huntington, George Willoughby, Orion Sherwood and James
# Peck — all served 60 days in Honolulu Jail after the June 4, 1958 second
# sailing attempt (Bigelow was arrested ashore; the other four at sea).
#
#  1. Attaches portraits (fill-if-empty) via prisoners:attach-golden-rule-photos.
#  2. Backfills birth/death dates (fill-if-empty). Huntington gets a
#     year-precision death date (1990; no exact date is published). Orion
#     Sherwood is still living and has no published birthdate — left alone.
#  3. Dedupes James Peck's verbatim-duplicated WWII Danbury case rows.
#  4. Ensures every crew member has the 1958 Golden Rule contempt case with
#     arrest/incarceration dates, the 60-day sentence, Judge Jon Wiig, and
#     Honolulu Jail; existing rows are enriched fill-if-empty, and the case is
#     created where missing (Bigelow and Peck lacked it entirely).
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/golden-rule-crew.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:attach-golden-rule-photos

php artisan tinker --execute='
// --- 1. Birth/death dates (fill-if-empty) --------------------------------
$dates = [
    "albert-bigelow"     => ["birthdate" => "1906-05-01", "death_date" => "1993-10-06"],
    "george-willoughby"  => ["birthdate" => "1914-12-09", "death_date" => "2010-01-05"],
    "james-peck"         => ["birthdate" => "1914-12-19", "death_date" => "1993-07-12"],
    "william-huntington" => ["birthdate" => "1907-01-28"],
];
foreach ($dates as $slug => $fields) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    foreach ($fields as $f => $v) {
        if (! empty($p->{$f})) { continue; }
        $p->{$f} = $v;
        echo "SET  {$slug}.{$f} = {$v}\n";
    }
    $p->save();
}

// Huntington died in 1990 but no exact date is published — store the year
// with year precision so the site displays just "1990".
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "william-huntington")->first();
if ($p && empty($p->death_date)) {
    $p->death_date = "1990-01-01";
    $p->date_precision = array_merge($p->date_precision ?? [], ["death_date" => "year"]);
    $p->save();
    echo "SET  william-huntington.death_date = 1990 (year precision)\n";
}

// --- 2. Dedupe James Peck duplicate case rows (verbatim copies) ----------
$peck = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "james-peck")->first();
if ($peck) {
    $seen = [];
    foreach ($peck->cases()->orderBy("created_at")->get() as $c) {
        $key = implode("|", [$c->institution_id, $c->charges, $c->sentence, $c->arrest_date, $c->incarceration_date, $c->release_date]);
        if (isset($seen[$key])) { $c->delete(); echo "DEDUP james-peck case {$c->id}\n"; continue; }
        $seen[$key] = true;
    }
}

// --- 3. The 1958 Golden Rule contempt case for all five crew -------------
$jail = \App\Models\Institution::firstOrCreate(
    ["name" => "Honolulu Jail"],
    ["city" => "Honolulu", "state" => "Hawaii"]
);
$fill = [
    "arrest_date"         => "1958-06-04",
    "incarceration_date"  => "1958-06-04",
    "sentence"            => "60 days in jail",
    "imprisoned_for_days" => 60,
    "convicted"           => "Yes — criminal contempt",
    "judge"               => "Jon Wiig",
];
foreach (["albert-bigelow", "george-willoughby", "orion-sherwood", "william-huntington", "james-peck"] as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    $case = $p->cases()->where("charges", "like", "%Golden Rule%")->first();
    if (! $case) {
        $case = $p->cases()->create([
            "charges" => "Contempt of court — defying the federal injunction against the Golden Rule voyage into the Pacific nuclear-test zone",
        ]);
        echo "CASE created for {$slug}\n";
    }
    $changed = false;
    foreach ($fill as $f => $v) {
        if (! empty($case->{$f})) { continue; }
        $case->{$f} = $v;
        $changed = true;
    }
    if (empty($case->institution_id)) { $case->institution_id = $jail->id; $changed = true; }
    if ($changed) { $case->save(); echo "CASE enriched for {$slug}\n"; }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Golden Rule crew photos, dates and cases applied."
