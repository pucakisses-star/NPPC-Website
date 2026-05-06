#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_briana_waters_bop.sh
#
# Corrects Briana Waterss release_date to the BOP-confirmed
# date and adds her federal register number.
#
# Per the Federal Bureau of Prisons inmate locator:
#   - Release date: 2013-06-27
#   - Register number: 36432-086
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Briana Waters")->first();
if (!$p) {
    echo "ERROR: Briana Waters not found\n";
    exit(1);
}

if (!$p->inmate_number || $p->inmate_number !== "36432-086") {
    $p->inmate_number = "36432-086";
    $p->save();
    echo "Set inmate_number=36432-086\n";
}

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    echo "ERROR: Briana Waters has no case row\n";
    exit(1);
}

$case->release_date = "2013-06-27";
$case->save();
echo "Updated case id={$case->id}: release_date=2013-06-27 (BOP confirmed); imprisoned_for_days={$case->imprisoned_for_days}\n";
'

echo
echo "Briana Waters BOP correction complete."
