#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/remove_post_911_ideology_tag.sh
#
# Strips the "Targeted by post-9/11 terrorism prosecution" tag (and
# minor casing/punctuation variants) from every prisoners
# ideologies JSON array.
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
        $key  = strtolower(preg_replace("/[^a-z0-9]/i", "", $norm));
        if ($key === "targetedbypost911terrorismprosecution") {
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
echo "Post-9/11 terrorism-prosecution tag removal complete."
