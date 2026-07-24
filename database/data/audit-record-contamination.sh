#!/usr/bin/env bash
#
# READ-ONLY audit for the contamination fingerprint found on Martin Luther
# King Jr.'s record (Eric Brandt / inmate 191131 / Trinidad Correctional
# Facility, Model, Colorado). Reports three categories of suspect records so
# they can be reviewed before any fix. It does NOT modify anything.
#
#   A) Deceased but still flagged in custody  — the bug that made King show
#      "AGE 97" / "IMPRISONED FOR 58 YEARS" (age/time counted to today).
#   B) Colorado / Trinidad / Brandt strings in a prisoner address or inmate
#      number.
#   C) Cases at a Trinidad / Colorado institution, or with "Civil Disorder"
#      charges — flagged when the prisoner is not actually a Colorado record
#      (i.e. a probable mislink).
#
# Run from the repo root:
#   bash database/data/audit-record-contamination.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$P = \App\Models\Prisoner::class;

echo "=== A) Deceased but flagged in custody ===\n";
$a = $P::withoutGlobalScopes()->whereNotNull("death_date")->where("in_custody", true)->get();
echo "count: ".$a->count()."\n";
foreach ($a as $p) { echo "  {$p->slug} | {$p->name} | died {$p->death_date} | in_custody=true\n"; }

echo "\n=== B) Colorado/Trinidad/Brandt string in prisoner address or inmate number ===\n";
$b = $P::withoutGlobalScopes()->where(function ($q) {
    foreach (["Trinidad","191131","US-350","US 350","Model, CO","Model,CO","Brandt"] as $t) { $q->orWhere("address","like","%{$t}%"); }
    $q->orWhere("inmate_number","like","%191131%");
})->get();
echo "count: ".$b->count()."\n";
foreach ($b as $p) { echo "  {$p->slug} | {$p->name} | state=".var_export($p->state,true)." | inmate=".var_export($p->inmate_number,true)." | address=".var_export($p->address,true)."\n"; }

echo "\n=== C) Cases at a Trinidad/Colorado institution or with Civil Disorder charges ===\n";
$instIds = \App\Models\Institution::where("name","like","%Trinidad%")->orWhere("state","Colorado")->pluck("id")->all();
$cases = \App\Models\PrisonerCase::where(function ($q) use ($instIds) {
    $q->where("charges","like","%Civil Disorder%");
    if ($instIds) { $q->orWhereIn("institution_id", $instIds); }
})->get();
echo "count: ".$cases->count()."\n";
foreach ($cases as $c) {
    $p = $P::withoutGlobalScopes()->find($c->prisoner_id);
    $inst = $c->institution;
    $flag = ($p && strcasecmp((string) $p->state, "Colorado") !== 0) ? "   <-- prisoner state is not Colorado (probable mislink)" : "";
    echo "  case {$c->id} | prisoner=".($p ? $p->name : "?")." (state=".var_export($p?->state,true).") | inst=".var_export($inst?->name,true)."/".var_export($inst?->state,true)." | charges=".var_export($c->charges,true).$flag."\n";
}

echo "\n=== Summary ===\n";
echo "A deceased-but-in-custody: ".$a->count()."\n";
echo "B address/inmate fingerprint: ".$b->count()."\n";
echo "C Colorado/Trinidad/Civil-Disorder cases: ".$cases->count()."\n";
echo "\nRead-only audit — nothing was modified.\n";
'

echo
echo "Done. Contamination audit complete (read-only)."
