#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/normalize_antifascism_ideology.sh
#
# The ideologies JSON column on prisoners has been treating
# "Antifascist" and "Antifascism" as two distinct values, splitting
# the same political category across two filter buckets. This script
# rewrites every prisoners ideologies array so any "Antifascist"
# entry is replaced by "Antifascism" (deduplicating if the prisoner
# already had both).
set -e

php artisan tinker --execute='
$updated = 0;
$total   = 0;
foreach (App\Models\Prisoner::cursor() as $p) {
    $total++;
    $ideologies = $p->ideologies;
    if (!is_array($ideologies) || empty($ideologies)) {
        continue;
    }
    $rewritten = [];
    $changed   = false;
    foreach ($ideologies as $i) {
        $norm = trim((string) $i);
        if (strcasecmp($norm, "Antifascist") === 0 || strcasecmp($norm, "Anti-fascist") === 0) {
            $norm = "Antifascism";
            $changed = true;
        }
        if (strcasecmp($norm, "Anti-fascism") === 0) {
            $norm = "Antifascism";
            $changed = true;
        }
        // dedupe
        if (!in_array($norm, $rewritten, true)) {
            $rewritten[] = $norm;
        } else {
            $changed = true;
        }
    }
    if ($changed && $rewritten !== $ideologies) {
        $p->ideologies = $rewritten;
        $p->save();
        $updated++;
        echo "  {$p->name} -> [" . implode(", ", $rewritten) . "]\n";
    }
}
echo "Total prisoners scanned: {$total}\n";
echo "Records updated:         {$updated}\n";
'

echo
echo "Antifascism normalization complete."
