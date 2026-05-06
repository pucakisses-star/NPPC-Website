#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_daniel_sicken.sh
#
# Daniel Sicken (Minuteman III Plowshares, Aug 6 1998 action at
# Minuteman III silo N-8 near Greeley, CO) was reading as imprisoned
# for 20+ years because his case had no release_date set. He was
# released from federal custody on October 16, 2001. His federal
# inmate number is 28360-013.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Daniel Sicken")->first();
if (!$p) {
    echo "ERROR: Daniel Sicken not found\n";
    exit(1);
}

$dirty = false;
if (!$p->inmate_number) { $p->inmate_number = "28360-013"; $dirty = true; }
if ($p->in_custody)     { $p->in_custody    = false;       $dirty = true; }
if (!$p->released)      { $p->released      = true;        $dirty = true; }
if ($dirty) {
    $p->save();
    echo "Updated prisoner: inmate_number=28360-013, in_custody=false, released=true.\n";
}

foreach ($p->cases as $case) {
    if (!$case->release_date) {
        $case->release_date = "2001-10-16";
        $case->save();
        echo "  case id={$case->id}: release_date=2001-10-16; imprisoned_for_days={$case->imprisoned_for_days}\n";
    } else {
        echo "  case id={$case->id}: release_date already set ({$case->release_date->toDateString()}); skipping.\n";
    }
}
'

echo
echo "Daniel Sicken fix complete."
