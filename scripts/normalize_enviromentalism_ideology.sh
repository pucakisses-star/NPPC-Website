#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/normalize_enviromentalism_ideology.sh
#
# Catches the misspelled variants the previous environmental
# normalization missed (the ones without the second n -
# "Enviromentalism", "Enviromental", "Enviromentalist") and folds
# them into the canonical "Environmental Activism".
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
            strcasecmp($norm, "Enviromentalism") === 0 ||
            strcasecmp($norm, "Enviromental")    === 0 ||
            strcasecmp($norm, "Enviromentalist") === 0
        ) {
            if ($norm !== "Environmental Activism") {
                $norm = "Environmental Activism";
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
echo "Enviromentalism (misspelled) normalization complete."
