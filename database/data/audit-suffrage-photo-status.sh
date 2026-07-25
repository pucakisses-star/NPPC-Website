#!/usr/bin/env bash
#
# READ-ONLY audit: for each name in the LOC "Gallery of Suffrage Prisoners"
# list (with its mnwp item id), find the matching prisoner and report whether
# it currently has a photo. Prints a summary count of how many are WITHOUT a
# photo (the answer to "how many of these lack photos?"). Changes nothing.
#
#   bash database/data/audit-suffrage-photo-status.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

// LOC name => mnwp item id
$names = [
  "Pauline Adams"=>"mnwp000068","Edith Ainge"=>"mnwp000072","Annie Arniel"=>"mnwp000073",
  "Berthe Arnold"=>"mnwp000074","Virginia Arnold"=>"mnwp000075","Lillian Ascough"=>"mnwp000005",
  "Abby Scott Baker"=>"mnwp000006","Catherine Boyle"=>"mnwp000091","Lucy Gwynne Branham"=>"mnwp000304",
  "Eunice Dana Brannan"=>"mnwp000249","Lucy Burns"=>"mnwp000011","Iris Calderhead"=>"mnwp000110",
  "Sarah Tarleton Colvin"=>"mnwp000355","Gertrude Crocker"=>"mnwp000357","Mary Dubrow"=>"mnwp000299",
  "Julia Emory"=>"mnwp000013","Lucy Ewing"=>"mnwp000370","Mary Gertrude Fendall"=>"mnwp000373",
  "Catherine M. Flanagan"=>"mnwp000228","Janet Fotheringham"=>"mnwp000380","Matilda Hall Gardner"=>"mnwp000383",
  "Betty Gram"=>"mnwp000387","Natalie Gray"=>"mnwp000390","Ernestine Hara"=>"mnwp000392",
  "Louisine Havemeyer"=>"mnwp000248","Kate Heffelfinger"=>"mnwp000022","Minnie Hennessy"=>"mnwp000398",
  "Elsie Hill"=>"mnwp000253","Florence Bayard Hilles"=>"mnwp000400","Alison Turnbull Hopkins"=>"mnwp000249",
  "Julia Hurlbut"=>"mnwp000213","Hazel Hunkins"=>"mnwp000403","Elizabeth Green Kalb"=>"mnwp000097",
  "Dora Kelly Lewis"=>"mnwp000229","Anne Martin"=>"mnwp000032","Nell Mercer"=>"mnwp000128",
  "Vida Milholland"=>"mnwp000252","Bertha Moller"=>"mnwp000297","Agnes H. Morey"=>"mnwp000132",
  "Katharine A. Morey"=>"mnwp000036","Mary A. Nolan"=>"mnwp000040","Alice Paul"=>"mnwp000146",
  "Mrs. R. B. Quay"=>"mnwp000155","Elizabeth Selden Rogers"=>"mnwp000159","Nina Samarodin"=>"mnwp000162",
  "Caroline E. Spencer"=>"mnwp000055","Doris Stevens"=>"mnwp000249","Elizabeth Stuyvesant"=>"mnwp000171",
  "Mabel Vernon"=>"mnwp000287","Mrs. William Upton Watson"=>"mnwp000228","Helena Hill Weed"=>"mnwp000060",
  "Sue Shelton White"=>"mnwp000185","Margaret Whittemore"=>"mnwp000187","Anna Kelton Wiley"=>"mnwp000188",
  "Rose Winslow"=>"mnwp000190","Joy Young"=>"mnwp000065","Matilda Young"=>"mnwp000066",
];

$titles = ["mrs","mr","dr","miss","rev"];
$keyOf = function (string $n) use ($titles): string {
    $n = strtolower(str_replace([".",","], "", $n));
    $t = preg_split("/\s+/", trim($n));
    while (count($t) > 1 && in_array($t[0], $titles, true)) { array_shift($t); }
    $t = array_map(fn ($x) => $x === "katharine" ? "katherine" : $x, $t);
    if (! $t) { return $n; }
    return count($t) === 1 ? $t[0] : $t[0]." ".end($t);
};

// Preload scoped suffrage records for tolerant matching.
$scoped = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("era", "1910s")
        ->orWhere("affiliation", "like", "%Woman%Party%")
        ->orWhere("affiliation", "like", "%Silent Sentinel%"))
    ->get(["id","name","slug","photo"]);
$exact = []; $norm = [];
foreach ($scoped as $p) { $exact[strtolower($p->name)] = $p; $norm[$keyOf($p->name)] ??= $p; }
// husband-form synonyms (LOC gives a personal name the record lacks)
$syn = ["mrs r b quay" => "Mrs. R. B. Quay"]; // matched via last-name fallback below

$withPhoto = []; $withoutPhoto = []; $notFound = [];
foreach ($names as $name => $loc) {
    $l = strtolower($name);
    $p = $exact[$l] ?? ($norm[$keyOf($name)] ?? null);
    if (! $p) {
        // last-name fallback if unique among scoped records
        $nt = preg_split("/\s+/", trim(preg_replace("/[.,]/", "", $name)));
        $last = strtolower((string) end($nt));
        $cand = $scoped->filter(function ($x) use ($last) {
            $xt = preg_split("/\s+/", trim($x->name));
            return strtolower((string) end($xt)) === $last;
        });
        if ($cand->count() === 1) { $p = $cand->first(); }
    }
    if (! $p) { $notFound[] = "{$name}  ({$loc})"; continue; }
    if (empty($p->photo)) { $withoutPhoto[] = "{$name}  ->  {$p->name}  ({$loc})"; }
    else { $withPhoto[] = "{$name}  ->  {$p->name}"; }
}

echo "\n=== WITHOUT photo (".count($withoutPhoto)."): ===\n";
foreach ($withoutPhoto as $r) { echo "  {$r}\n"; }
echo "\n=== WITH photo (".count($withPhoto)."): ===\n";
foreach ($withPhoto as $r) { echo "  {$r}\n"; }
echo "\n=== NOT FOUND in DB (".count($notFound)."): ===\n";
foreach ($notFound as $r) { echo "  {$r}\n"; }
echo "\nSUMMARY: ".count($names)." LOC names -> ".count($withoutPhoto)." without photo, ".count($withPhoto)." with photo, ".count($notFound)." not found.\n";
'
