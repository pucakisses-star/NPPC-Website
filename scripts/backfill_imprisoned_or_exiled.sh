#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/backfill_imprisoned_or_exiled.sh
#
# After updating the Prisoner model's saving() hook to auto-derive
# imprisoned_or_exiled from in_custody and currently_in_exile, this
# script re-saves every prisoner so the new hook fires and the
# stored imprisoned_or_exiled column gets brought into sync.
#
# Why: the column was previously hand-set on data import and on
# admin form save, and could drift out of sync with in_custody/
# currently_in_exile. Released prisoners (e.g. Larry Bushart) would
# still surface in any list filtering on imprisoned_or_exiled because
# the column was stuck at 1.
set -e

php artisan tinker --execute='
$total = App\Models\Prisoner::count();
$changed = 0;
$now_active = 0;
$now_inactive = 0;
foreach (App\Models\Prisoner::cursor() as $p) {
    $derived = ($p->in_custody || $p->currently_in_exile) ? 1 : 0;
    $stored  = (int) $p->imprisoned_or_exiled;
    if ($derived !== $stored) {
        $p->saveQuietly();
        $changed++;
        if ($derived) { $now_active++; } else { $now_inactive++; }
    }
}
echo "Total prisoners: {$total}\n";
echo "Records corrected: {$changed}\n";
echo "  flipped to active (1):   {$now_active}\n";
echo "  flipped to inactive (0): {$now_inactive}\n";
'

echo
echo "imprisoned_or_exiled backfill complete."
