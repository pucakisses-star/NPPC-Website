#!/usr/bin/env bash
#
# Photo hunt over the next 50 photoless prisoners in the site's sort order
# (July 2026) — almost entirely 2020s protest defendants, the hardest photo
# category. One verified portrait found, one case outcome corrected, and
# two duplicate pairs surfaced and merged.
#
#  - Briana Boston: Polk County booking photograph (public record, image
#    alt-labeled with her name in the Gray Media article); her record's
#    case also gains the outcome — the charge was dropped.
#  - Merges: aline-espinosa-villegas <= angel-espinosa-villegas (same
#    Little Rock 2020 arson defendant, entered again under her goes-by
#    name) and andrew-augustyniak-duncan <= andrew-duncan-augustyniak
#    (same Pittsburgh defendant, hyphenated surname in both orders).
#
# Fill-if-empty; idempotent.
#
# Run from the repo root:  bash database/data/photos-sort-order-50.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=aline-espinosa-villegas,andrew-augustyniak-duncan --apply

php artisan tinker --execute='
\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$p = \App\Models\Prisoner::withUnderReview()->where("slug", "briana-boston")->first();
if ($p) {
    if (empty($p->photo) && is_file(database_path("data/photos/nonfree/briana-boston.jpg"))) {
        \Illuminate\Support\Facades\Storage::disk("public")->put("prisoners/briana-boston.jpg", (string) file_get_contents(database_path("data/photos/nonfree/briana-boston.jpg")));
        $p->photo = "prisoners/briana-boston.jpg";
        $p->save();
        echo "PHOTO briana-boston\n";
    }
    $case = $p->cases()->first();
    if ($case && empty($case->convicted)) {
        $case->convicted = "No — the charge was dropped";
        $case->save();
        echo "CASE briana-boston outcome\n";
    }
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Sort-order batch 2 applied."
