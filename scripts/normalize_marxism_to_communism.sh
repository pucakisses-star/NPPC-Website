#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/normalize_marxism_to_communism.sh
#
# Same pattern as the other ideology normalizations. Folds "Marxism"
# (and Marxist / Marxist-Leninist variants) into "Communism".
set -e

php artisan tinker --execute='
$matches = [
    "marxism",
    "marxist",
    "marxist-leninist",
    "marxism-leninism",
    "marxism leninism",
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
        if (in_array(strtolower($norm), $matches, true)) {
            if ($norm !== "Communism") {
                $norm = "Communism";
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
echo "Marxism -> Communism normalization complete."
