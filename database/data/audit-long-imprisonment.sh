#!/usr/bin/env bash
#
# READ-ONLY audit: list every prisoner whose TOTAL recorded imprisonment reads as
# more than 60 years. Over 60 years almost always means a false length like the
# Martin Luther King Jr. bug — an open-ended case (incarceration date, no release
# date) on a prisoner still flagged in custody, so the day count runs to today;
# or a deceased prisoner still flagged in custody. Each entry shows the cases and
# flags so real long-timers can be told apart from bugs. Modifies nothing.
#
# Run from the repo root:
#   bash database/data/audit-long-imprisonment.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$threshold = 60 * 365; // days
$rows = [];
foreach (\App\Models\Prisoner::withoutGlobalScopes()->with(["cases.institution"])->get() as $p) {
    $days = $p->cases->pluck("imprisoned_for_days")->filter(function ($d) { return $d !== null; });
    if ($days->isEmpty()) { continue; }
    $total = (int) $days->sum();
    if ($total <= $threshold) { continue; }
    $rows[] = [$total, $p];
}
usort($rows, function ($a, $b) { return $b[0] <=> $a[0]; });

echo "Prisoners reading over 60 years total: ".count($rows)."\n\n";
foreach ($rows as [$total, $p]) {
    $yrs = round($total / 365.25, 1);
    $why = [];
    if ($p->in_custody) { $why[] = "in_custody"; }
    if ($p->awaiting_trial) { $why[] = "awaiting_trial"; }
    if (! empty($p->death_date)) { $why[] = "died ".$p->death_date; }
    echo "{$p->slug} | {$p->name} | ~{$yrs} yrs ({$total} d)".($why ? "  [".implode(", ", $why)."]" : "")."\n";
    foreach ($p->cases as $c) {
        $inc = $c->partialDateIso("incarceration_date");
        $rel = $c->partialDateIso("release_date");
        $open = ($inc && ! $rel) ? "  <-- OPEN (no release date)" : "";
        echo "    ".($inc ?? "-")." -> ".($rel ?? "(none)")." (".($c->imprisoned_for_days ?? "n/a")." d) | ".($c->institution?->name ?? "-").$open."\n";
    }
}
echo "\nRead-only audit — nothing was modified.\n";
'

echo
echo "Done. Long-imprisonment audit complete (read-only)."
