#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/normalize_antiwar_ideology.sh
#
# Same pattern as the Antifascism normalization. The ideologies JSON
# column has had "Anti-war" and "Anti-War" treated as two separate
# values, splitting the same political category across two filter
# buckets. Script rewrites every prisoners ideologies array so all
# case-insensitive matches of "Anti-war" / "Antiwar" are stored as
# "Anti-War" (canonical form), deduplicating if both were present.
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
        if (
            strcasecmp($norm, "Anti-war") === 0 ||
            strcasecmp($norm, "Antiwar")  === 0 ||
            strcasecmp($norm, "Anti war") === 0
        ) {
            if ($norm !== "Anti-War") {
                $norm = "Anti-War";
                $changed = true;
            }
        }
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
echo "Anti-War normalization complete."
