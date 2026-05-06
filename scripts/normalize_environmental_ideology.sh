#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/normalize_environmental_ideology.sh
#
# Same pattern as the Antifascism / Anti-War normalizations. The
# ideologies JSON column on prisoners has had "Environmental" and
# "Environmentalism" treated as two distinct values, splitting the
# same political category across two filter buckets. Script rewrites
# every prisoners ideologies array so both (case-insensitive) become
# "Environmental Activism", deduplicating if multiple forms were
# present.
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
            strcasecmp($norm, "Environmental")        === 0 ||
            strcasecmp($norm, "Environmentalism")     === 0 ||
            strcasecmp($norm, "Environmentalist")     === 0
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
echo "Environmental Activism normalization complete."
