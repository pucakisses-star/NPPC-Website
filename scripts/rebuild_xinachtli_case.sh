#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/rebuild_xinachtli_case.sh
#
# Resets Alvaro Luna Hernandez (Xinachtli) cases entirely:
#   1. Lists every existing case and its imprisoned_for_days
#   2. Deletes them all
#   3. Creates a single canonical case for the July 18, 1996 Alpine,
#      Texas arrest at TDCJ, with no release_date (still in custody
#      as of last public information)
#
# This eliminates whatever stale Airtable-import row was driving the
# 75-year imprisoned counter (the previous fix only deleted cases
# whose start year was clearly pre-1996; a row with a different bad
# date pattern survived).
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

echo "Existing cases BEFORE rebuild:\n";
foreach ($p->cases as $case) {
    $arr = $case->arrest_date?->toDateString() ?? "null";
    $inc = $case->incarceration_date?->toDateString() ?? "null";
    $rel = $case->release_date?->toDateString() ?? "null";
    $days = $case->imprisoned_for_days ?? "null";
    echo "  id={$case->id}  arrest={$arr}  incarc={$inc}  release={$rel}  imprisoned_for_days={$days}\n";
}

$count = $p->cases()->count();
$p->cases()->delete();
echo "Deleted {$count} stale case row(s).\n";

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Texas Department of Criminal Justice"],
    ["state" => "Texas"]
);

$case = App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Disarming an Alpine, Texas police officer (defense argued self-defense and police frame-up after sustained surveillance of his community organizing in the Aldape Guerra Defense Committee).",
    "arrest_date"        => "1996-07-18",
    "incarceration_date" => "1996-07-18",
    "sentenced_date"     => "1997-06-09",
    "sentence"           => "50 years in Texas state prison",
    "convicted"          => "Yes - Ector County, Texas, June 2-9, 1997",
]);

echo "Created canonical case id={$case->id}: arrest=1996-07-18 imprisoned_for_days={$case->imprisoned_for_days}\n";

// Also clear any stored years_in_prison integer that might be
// driving a fallback display value.
if ($p->years_in_prison && (int) $p->getRawOriginal("years_in_prison") > 0) {
    \DB::table("prisoners")->where("id", $p->id)->update(["years_in_prison" => null]);
    echo "Cleared stored years_in_prison fallback.\n";
}

$totalDays = $p->fresh()->cases->sum("imprisoned_for_days");
$years = intdiv($totalDays, 365);
echo "TOTAL imprisoned_for_days = {$totalDays} (~{$years} years)\n";
'

echo
echo "Xinachtli case rebuild complete."
