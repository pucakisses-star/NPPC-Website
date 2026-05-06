#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/remove_indigenous_antipipeline_ideology.sh
#
# Strips the "Indigenous solidarity" and "Anti-pipeline" tags from
# every prisoners ideologies JSON array (case-insensitive; minor
# punctuation / hyphen variants matched).
set -e

php artisan tinker --execute='
$drop = [
    "indigenous solidarity",
    "indigenous-solidarity",
    "anti-pipeline",
    "antipipeline",
    "anti pipeline",
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
echo "Indigenous solidarity / Anti-pipeline ideology removal complete."
