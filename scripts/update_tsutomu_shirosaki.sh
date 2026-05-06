#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_tsutomu_shirosaki.sh
#
# Sets birthdate, death date, and birthplace metadata on the existing
# Tsutomu Shirosaki prisoner record per the Japanese-language source:
#   - Born December 5, 1947 in Shimoniikawa District, Toyama
#     Prefecture, Japan
#   - Died July 20, 2024, age 76
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("slug", "tsutomu-shirosaki")
    ->orWhere("name", "Tsutomu Shirosaki")
    ->orWhere("name", "like", "%Shirosaki%")
    ->first();

if (!$p) {
    echo "ERROR: Tsutomu Shirosaki not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

$dirty = false;
if (!$p->birthdate || $p->birthdate->toDateString() !== "1947-12-05") {
    $p->birthdate = "1947-12-05";
    $dirty = true;
}
if (!$p->death_date || $p->death_date->toDateString() !== "2024-07-20") {
    $p->death_date = "2024-07-20";
    $dirty = true;
}
if (!$p->state) {
    $p->state = "Toyama Prefecture, Japan";
    $dirty = true;
}
if (!$p->address) {
    $p->address = "Shimoniikawa District, Toyama Prefecture, Japan";
    $dirty = true;
}
if ($p->in_custody) { $p->in_custody = false; $dirty = true; }
if (!$p->released)  { $p->released   = true;  $dirty = true; }

if ($dirty) {
    $p->save();
    echo "Updated: birthdate=1947-12-05, death_date=2024-07-20, state/address set.\n";
} else {
    echo "No changes needed.\n";
}
'

echo
echo "Tsutomu Shirosaki update complete."
