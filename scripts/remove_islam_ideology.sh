#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/remove_islam_ideology.sh
#
# Strips "Islam" and "Islamism" (and Islamist / Islamic / Muslim
# variants if present as ideology tags) from every prisoners
# ideologies JSON array.
set -e

php artisan tinker --execute='
$drop = [
    "islam",
    "islamism",
    "islamist",
    "islamic",
];

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
        if (in_array(strtolower($norm), $drop, true)) {
            $changed = true;
            continue;
        }
        if (!in_array($norm, $rewritten, true)) {
            $rewritten[] = $norm;
        } else {
            $changed = true;
        }
    }
    if ($changed) {
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
echo "Islam / Islamism ideology removal complete."
