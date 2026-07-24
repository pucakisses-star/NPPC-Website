#!/usr/bin/env bash
#
# Three small cleanups:
#   1) Remove the affiliation "Chilean Consulate Occupation" from every prisoner
#      that carries it (Nydia Esther Cuevas, Pablo Marcano Garcia), which also
#      drops it from the affiliation filter.
#   2) Remove the "Indian Territory" state by remapping it to Oklahoma (its
#      present-day equivalent) — currently only Chitto Harjo.
#   3) Change William Weinstone's slug from william-w-weinstone to
#      william-weinstone.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-weinstone-slug-and-taxonomy.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$P = \App\Models\Prisoner::class;

// 1) Drop the "Chilean Consulate Occupation" affiliation.
$aff = "Chilean Consulate Occupation";
$n = 0;
foreach ($P::withoutGlobalScopes()->whereJsonContains("affiliation", $aff)->get() as $p) {
    $p->affiliation = array_values(array_diff($p->affiliation ?? [], [$aff])) ?: null;
    $p->save();
    echo "1) Removed affiliation from {$p->name}.\n";
    $n++;
}
echo "1) affiliation removed from {$n} record(s).\n";

// 2) Indian Territory -> Oklahoma.
$m = 0;
foreach ($P::withoutGlobalScopes()->where("state", "Indian Territory")->get() as $p) {
    $p->state = "Oklahoma";
    $p->save();
    echo "2) State Indian Territory -> Oklahoma: {$p->name}.\n";
    $m++;
}
echo "2) state remapped on {$m} record(s).\n";

// 3) Weinstone slug.
$w = $P::withoutGlobalScopes()->where("slug", "william-w-weinstone")->first();
if ($w) {
    $conflict = $P::withoutGlobalScopes()->where("slug", "william-weinstone")->where("id", "!=", $w->id)->first();
    if ($conflict) {
        echo "3) CONFLICT: another record already uses slug william-weinstone (".$conflict->name.") — slug left unchanged.\n";
    } else {
        $w->slug = "william-weinstone";
        $w->save();
        echo "3) Slug changed to william-weinstone.\n";
    }
} elseif ($P::withoutGlobalScopes()->where("slug", "william-weinstone")->exists()) {
    echo "3) Slug is already william-weinstone.\n";
} else {
    echo "3) William Weinstone record not found by slug william-w-weinstone.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Affiliation, state and slug cleanups applied."
