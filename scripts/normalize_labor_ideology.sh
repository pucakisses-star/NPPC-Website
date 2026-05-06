#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/normalize_labor_ideology.sh
#
# Same pattern as the other ideology normalizations. Folds the
# generic "Labor" tag into "Labor Rights Activism" on every prisoner
# record, deduplicating if both were present.
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
        if (strcasecmp($norm, "Labor") === 0) {
            if ($norm !== "Labor Rights Activism") {
                $norm = "Labor Rights Activism";
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
echo "Labor Rights Activism normalization complete."
