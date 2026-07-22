#!/usr/bin/env bash
#
# Set birth and death dates for several IWW / 1918 Chicago mass-trial
# defendants. Data supplied by the site owner:
#   William Dudley Haywood   1869-02-04 – 1928-05-18
#   J. H. "Jack" Beyer       1858 – 1922            (year precision)
#   Leo Laukki               1880-11-22 – 1938-09-15
#   Konstantin "Fred" Jaakkola 1886-08-05 – (death unknown)
#   George Iliev Andreytchine 1894-01-20 – 1950-04-20
# (Lossieff, McCutcheon, Perry and Rothfisher were left out — no dates known.)
#
# Records are matched by slug where known, otherwise by a distinctive surname.
# Dates store at their true precision (year vs day) via setPartialDate.
#
# Idempotent and non-destructive: each date is written ONLY where that field is
# currently empty. Run from the repo root:
#   bash database/data/set-iww-defendant-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/iww-defendant-dates.json")), true);
if (! is_array($rows)) { echo "Could not read JSON\n"; return; }

$apply = function ($p, $field, $data) {
    if (empty($data[$field]) || ! empty($p->{$field})) { return false; }
    $parts = explode("-", $data[$field]);
    $y = (int) ($parts[0] ?? 0); $m = (int) ($parts[1] ?? 1); $d = (int) ($parts[2] ?? 1);
    $prec = $data[$field . "_precision"] ?? "day";
    if ($prec === "year")  { $p->setPartialDate($field, $y); }
    elseif ($prec === "month") { $p->setPartialDate($field, $y, $m); }
    else { $p->setPartialDate($field, $y, $m, $d); }
    return true;
};

foreach ($rows as $r) {
    $q = \App\Models\Prisoner::withoutGlobalScopes();
    if (! empty($r["match_slug"])) { $q->where("slug", $r["match_slug"]); }
    else { $q->where("name", "like", "%".$r["match_name"]."%"); }
    $matches = $q->get();

    if ($matches->count() === 0) { echo "  no match: ".($r["match_slug"] ?? $r["match_name"])."\n"; continue; }
    if ($matches->count() > 1)  { echo "  AMBIGUOUS ".($r["match_slug"] ?? $r["match_name"]).": ".$matches->pluck("slug")->implode(", ")."\n"; continue; }

    $p = $matches->first();
    $changed = [];
    if ($apply($p, "birthdate", $r))  { $changed[] = "birth ".$r["birthdate"]; }
    if ($apply($p, "death_date", $r)) { $changed[] = "death ".$r["death_date"]; }
    if ($changed) { $p->save(); echo "  set {$p->slug}: ".implode(", ", $changed)."\n"; }
    else { echo "  {$p->slug}: already has date(s), nothing to do\n"; }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. IWW defendant birth/death dates set."
