#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/list_caseless_prisoners.sh
#
# Diagnostic: prints every prisoner that has zero attached case rows.
# These are most often prisoners who pre-existed in the database via
# the Airtable import; subsequent prisoner:add scripts hit the
# duplicate-name guard and the case data never landed.
#
# This script is read-only - it does not modify any data. Use the
# output to decide which prisoners need targeted case-add scripts.
set -e

php artisan tinker --execute='
$caseless = App\Models\Prisoner::whereDoesntHave("cases")
    ->orderBy("name")
    ->get();

echo "Prisoners with zero attached cases: " . $caseless->count() . "\n";
echo str_repeat("-", 70) . "\n";
foreach ($caseless as $p) {
    $era    = $p->era ?: "(no era)";
    $state  = $p->state ?: "(no state)";
    echo sprintf("  %-40s  %-10s  %s\n", $p->name, $era, $state);
}
echo str_repeat("-", 70) . "\n";
echo "Done.\n";
'
