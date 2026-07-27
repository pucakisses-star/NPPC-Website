#!/usr/bin/env bash
#
# Remove the Chris Reid record (Irish republican prisoner said to have been
# held at FCI Pleasanton, sourced from PFOC Breakthrough magazine).
#
# Deletes the prisoner and its cases. Refuses to act if the name matches more
# than one record, so a loose match cannot take out the wrong person; the
# record is printed in full before anything is removed.
#
# Set REVIEW=1 to hide it from the public site instead of deleting
# (under_review = true), which is reversible from the admin.
#
#   bash database/data/remove-chris-reid.sh            # delete
#   REVIEW=1 bash database/data/remove-chris-reid.sh   # hide, keep the data

set -euo pipefail
cd "$(dirname "$0")/../.."

REVIEW="${REVIEW:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$review = getenv("REVIEW") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "chris-reid")->orWhereRaw("LOWER(name) = ?", ["chris reid"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "Not found: Chris Reid (already removed?).\n"; exit(0); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();
echo "record: {$p->name}  [{$p->slug}]\n";
echo "  sort_order:  {$p->sort_order}\n";
echo "  affiliation: ".(is_array($p->affiliation) ? implode(", ", $p->affiliation) : "-")."\n";
echo "  ideologies:  ".(is_array($p->ideologies) ? implode(", ", $p->ideologies) : "-")."\n";
echo "  cases:       ".$p->cases->count()."\n";
echo "  photo:       ".($p->photo ?: "(none)")."\n";

if ($review) {
    $p->under_review = true;
    $p->save();
    echo "\nHidden from the public site (under_review = true). Data kept; reverse it in the admin.\n";
} else {
    $caseCount = $p->cases->count();
    $p->delete();
    echo "\nDeleted the record and its {$caseCount} case(s).\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
