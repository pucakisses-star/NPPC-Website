#!/usr/bin/env bash
#
# Correct the gender of Nadarasa Yograrasa ("Yoga"), the LTTE material-support
# defendant in the 2006 Long Island weapons-purchase conspiracy, from Female to
# Male. The DOJ/FBI press releases on the case consistently refer to this
# defendant as male ("he").
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-yograrasa-gender.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = null;
foreach (["nadarasa-yograrasa", "yoga-nadarasa-yograrasa", "yoga"] as $s) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $s)->first();
    if ($p) break;
}
if (! $p) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()
        ->where("name", "like", "%Yograrasa%")
        ->first();
}

if (! $p) {
    echo "Nadarasa Yograrasa not found — give me the exact site slug.\n";
} elseif ($p->gender === "Male") {
    echo "{$p->name} is already Male — nothing to do.\n";
} else {
    $p->gender = "Male";
    $p->save();
    echo "SET gender=Male on {$p->name} (slug: {$p->slug}).\n";
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "Done.\n";
'

echo
echo "Done. Nadarasa Yograrasa gender corrected to Male."
