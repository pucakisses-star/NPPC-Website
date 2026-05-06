#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/backfill_incarceration_dates.sh
#   cd /var/www/NPPC-Website && bash scripts/backfill_incarceration_dates.sh --dry-run
#
# Cross-database back-fill of missing incarceration_date.
#
# Pattern: case rows that have arrest_date but no incarceration_date
# leave imprisoned_for_days null, so the page-level Time Imprisoned
# counter does not render. Fix is to set incarceration_date = arrest_date.
#
# Safety rules: only auto-fill cases where there is independent evidence
# the defendant was actually held in custody, not merely cited / arrested
# and released. The signal: at least one of release_date, sentenced_date,
# or death_in_custody_date is also set on the case row. Cases with only
# arrest_date (and none of those other fields) get reported but not
# auto-filled - those are typically protest-citation cases or cases
# where the defendant was arrested and immediately released, and they
# need manual review.
#
# Pass --dry-run to preview without writing.
DRY=""
[ "${1:-}" = "--dry-run" ] && DRY="--dry-run"
echo "Mode: ${DRY:-WRITE}"
echo

php artisan tinker --execute='
$dryRun = strpos(implode(" ", $_SERVER["argv"] ?? []), "dry-run") !== false;
echo $dryRun ? "[DRY RUN] no changes will be written\n\n" : "[WRITING]\n\n";

// ---- Pattern A: arrest_date set, incarceration_date null, but at least
// one of release_date / sentenced_date / death_in_custody_date is set
$autoFill = App\Models\PrisonerCase::whereNotNull("arrest_date")
    ->whereNull("incarceration_date")
    ->where(function ($q) {
        $q->whereNotNull("release_date")
          ->orWhereNotNull("sentenced_date")
          ->orWhereNotNull("death_in_custody_date");
    })
    ->with("prisoner")
    ->get();

echo "=== Pattern A: arrest_date set, incarceration_date null, release/sentence/death-in-custody set (auto-fillable) ===\n";
echo "Found {$autoFill->count()} cases.\n\n";
$filled = 0;
foreach ($autoFill as $case) {
    $name = $case->prisoner?->name ?? "(unknown)";
    $arrest = $case->arrest_date->toDateString();
    if (! $dryRun) {
        $case->incarceration_date = $case->arrest_date;
        $case->save();
        $filled++;
        echo "FILLED: {$name} (case id={$case->id}) -> incarceration_date {$arrest}; imprisoned_for_days={$case->imprisoned_for_days}\n";
    } else {
        echo "WOULD FILL: {$name} (case id={$case->id}) -> incarceration_date {$arrest}\n";
    }
}
echo "\n";

// ---- Pattern B: arrest_date only, no other custody-end markers (manual review)
$reviewQueue = App\Models\PrisonerCase::whereNotNull("arrest_date")
    ->whereNull("incarceration_date")
    ->whereNull("release_date")
    ->whereNull("sentenced_date")
    ->whereNull("death_in_custody_date")
    ->with("prisoner")
    ->get();

echo "=== Pattern B: arrest_date only, no other end-of-custody markers (NEEDS MANUAL REVIEW) ===\n";
echo "Found {$reviewQueue->count()} cases.\n";
foreach ($reviewQueue as $case) {
    $name = $case->prisoner?->name ?? "(unknown)";
    echo "  - {$name} (case id={$case->id}) arrest={$case->arrest_date->toDateString()} charges=" . substr((string) $case->charges, 0, 80) . "...\n";
}
echo "\n";

echo "Summary: " . ($dryRun ? "would fill {$autoFill->count()}" : "filled {$filled}") . " cases. {$reviewQueue->count()} cases need manual review.\n";
' --no-interaction $DRY

echo
echo "Cross-database incarceration_date backfill complete."
