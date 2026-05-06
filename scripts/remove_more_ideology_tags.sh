#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/remove_more_ideology_tags.sh
#
# Strips the following tags (case-insensitive, hyphen/space variants
# included) from every prisoners ideologies JSON array:
#   - Government Accountability
#   - Free Speech
#   - Forest Defender
#   - Mutual Aid
#   - Bail Fund
#   - Anti-capitalist
#   - Open Rescue
#   - Vegan
set -e

php artisan tinker --execute='
$drop = [
    "government accountability",
    "free speech",
    "forest defender",
    "forest defense",
    "mutual aid",
    "bail fund",
    "anti-capitalist",
    "anticapitalist",
    "anti capitalist",
    "open rescue",
    "vegan",
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
        $lc   = strtolower($norm);
        if (in_array($lc, $drop, true)) {
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
echo "Ideology cleanup (8 tags) complete."
