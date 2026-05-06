#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_xinachtli_dates.sh
#
# Diagnoses and fixes the inflated "Imprisoned For" total on Xinachtli
# (Alvaro Luna Hernandez). His public bio reports ~29 years (since his
# July 18, 1996 arrest), but the listing has been showing ~75 years
# implying a stale Airtable-import case row with an erroneous pre-1996
# incarceration_date is still attached.
#
# Logic:
#   1. Print every case row attached to him with id, dates, and the
#      stored imprisoned_for_days so the discrepancy is visible.
#   2. Delete any case row whose incarceration_date (or arrest_date,
#      or sentenced_date if the others are null) falls before
#      1996-01-01. He was born in 1952, so any pre-1996 incarceration
#      date is data-import noise, not a real prior period of custody.
#   3. If no canonical 1996 case remains after cleanup, create one
#      with the correct dates from the public bio.
#   4. Re-save the canonical case so the booted hook recomputes
#      imprisoned_for_days from the corrected dates.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Alvaro Luna Hernandez")
    ->orWhere("name", "like", "%Alvaro Luna Hernandez%")
    ->orWhere("name", "like", "%Alvaro%Hernandez%")
    ->orWhere("aka", "like", "%Xinachtli%")
    ->first();

if (!$p) {
    echo "ERROR: Xinachtli / Alvaro Luna Hernandez not found\n";
    exit(1);
}

echo "Found prisoner: {$p->name} (id={$p->id})\n";
echo "Case rows BEFORE cleanup:\n";
foreach ($p->cases as $case) {
    $arr = $case->arrest_date?->toDateString() ?? "null";
    $inc = $case->incarceration_date?->toDateString() ?? "null";
    $sen = $case->sentenced_date?->toDateString() ?? "null";
    $rel = $case->release_date?->toDateString() ?? "null";
    $days = $case->imprisoned_for_days ?? "null";
    echo "  id={$case->id}  arrest={$arr}  incarc={$inc}  sentenced={$sen}  release={$rel}  imprisoned_for_days={$days}\n";
}

$deleted = 0;
foreach ($p->cases as $case) {
    $start = $case->incarceration_date ?? $case->arrest_date ?? $case->sentenced_date;
    if ($start && \Carbon\Carbon::parse($start)->year < 1996) {
        echo "Deleting stale case id={$case->id} (start year " . \Carbon\Carbon::parse($start)->year . " < 1996)\n";
        $case->delete();
        $deleted++;
    }
}
echo "Deleted {$deleted} stale case row(s).\n";

$p->refresh()->load("cases");
if ($p->cases->isEmpty()) {
    echo "No cases remain - creating canonical 1996 case.\n";
    $inst = App\Models\Institution::firstOrCreate(
        ["name" => "Texas Department of Criminal Justice"],
        ["state" => "Texas"]
    );
    App\Models\PrisonerCase::create([
        "prisoner_id"        => $p->id,
        "institution_id"     => $inst->id,
        "charges"            => "Disarming an Alpine, Texas police officer (defense argued self-defense and police frame-up after sustained surveillance of his community organizing).",
        "arrest_date"        => "1996-07-18",
        "incarceration_date" => "1996-07-18",
        "sentenced_date"     => "1997-06-09",
        "sentence"           => "50 years in Texas state prison",
        "convicted"          => "Yes - Ector County, Texas, June 2-9, 1997",
    ]);
    echo "Created canonical 1996 case.\n";
}

// Re-save remaining cases so the booted hook recomputes
// imprisoned_for_days from the correct (post-cleanup) date set.
foreach ($p->fresh()->cases as $case) {
    if (!$case->incarceration_date) {
        $case->incarceration_date = "1996-07-18";
    }
    $case->save();
    $arr = $case->arrest_date?->toDateString() ?? "null";
    $inc = $case->incarceration_date?->toDateString() ?? "null";
    $rel = $case->release_date?->toDateString() ?? "null";
    echo "Re-saved case id={$case->id}: arrest={$arr} incarc={$inc} release={$rel} imprisoned_for_days={$case->imprisoned_for_days}\n";
}

$totalDays = $p->fresh()->cases->sum("imprisoned_for_days");
$years = intdiv($totalDays, 365);
echo "TOTAL imprisoned_for_days = {$totalDays} (~{$years} years)\n";
'

echo
echo "Xinachtli fix complete."
