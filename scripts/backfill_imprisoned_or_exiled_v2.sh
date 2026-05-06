#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/backfill_imprisoned_or_exiled_v2.sh
#
# Corrects an earlier backfill script that used saveQuietly() to
# trigger the Prisoner saving() hook. saveQuietly() suppresses model
# events, so the hook never fired and the imprisoned_or_exiled column
# was never recomputed. As a result prisoners like Bill Haywood (who
# died in 1928 and is neither in_custody nor currently_in_exile but
# was historically in_exile) remained stuck with imprisoned_or_exiled
# = 1 and kept appearing on the public "In Custody or Exiled" list.
#
# This script fixes the column for every prisoner in a single SQL
# statement, with no event/hook indirection: the public list is keyed
# off the stored column, so this is the source of truth.
set -e

php artisan tinker --execute='
$affected = DB::update("
    UPDATE prisoners
    SET imprisoned_or_exiled = CASE
        WHEN in_custody = 1 OR currently_in_exile = 1 THEN 1
        ELSE 0
    END
");
$active = DB::table("prisoners")->where("imprisoned_or_exiled", 1)->count();
$total  = DB::table("prisoners")->count();
echo "Updated rows: {$affected}\n";
echo "Now flagged active (imprisoned_or_exiled=1): {$active}/{$total}\n";
'

echo
echo "imprisoned_or_exiled v2 backfill complete."
