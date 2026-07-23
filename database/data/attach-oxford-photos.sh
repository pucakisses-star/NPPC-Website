#!/usr/bin/env bash
#
# Two photo fixes:
#
# 1. Re-crop Anthony Mullaney (Milwaukee 14): the attached 1968 UMass portrait
#    still had its white archival border and a handwritten "8" in the corner.
#    This installs a tightened crop over the same record.
#
# 2. Attach 1950 Lincoln University yearbook senior portraits to three of the
#    four Oxford Theatre desegregation-protest students. Each portrait is the
#    labeled senior photo from the official yearbook (public domain, 1950),
#    matched by caption:
#      - Luther R. Manning, Jr.   (yearbook p.32 / PDF p.40)
#      - Vernel Dieudonne         (yearbook p.25 / PDF p.33)
#      - Jacques E. Wilmore       (yearbook p.44 / PDF p.52; his bio names
#                                  "Oxford, Penna.")
#    Archibald Seale has no senior portrait in the 1950 book (underclassman),
#    so he is left without one.
# Source: https://www.lincoln.edu/.../Yearbook_1950.pdf
#
# Mullaney is overwritten deliberately (recrop); the Oxford photos are set only
# where the record has none, and are matched by name + Lincoln University
# affiliation so they hit the right person. Idempotent. Run from the repo root:
#   bash database/data/attach-oxford-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

# --- Mullaney recrop: overwrite the existing file in place ---
mkdir -p storage/app/public/prisoners/milwaukee-14
cp -f database/data/photos/milwaukee-14/anthony-mullaney.jpg \
      storage/app/public/prisoners/milwaukee-14/anthony-mullaney.jpg
echo "Installed re-cropped Mullaney photo."

# --- Oxford yearbook portraits ---
mkdir -p storage/app/public/prisoners/oxford
cp -f database/data/photos/oxford/*.jpg storage/app/public/prisoners/oxford/ 2>/dev/null || true
echo "Copied Oxford portraits into storage."

php artisan tinker --execute='
// 1. Mullaney recrop — point the record at the (now re-cropped) file.
$m = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "anthony-mullaney")->first();
if ($m) {
    $m->photo = "prisoners/milwaukee-14/anthony-mullaney.jpg";
    $m->save();
    echo "Mullaney photo set to the re-cropped image.\n";
}

// 2. Oxford yearbook portraits — match by name + Lincoln University affiliation.
$rows = [
    ["Luther Manning", "luther-manning.jpg"],
    ["Vernell Dieudonne", "vernell-dieudonne.jpg"],
    ["Jacques E. Wilmore", "jacques-wilmore.jpg"],
];
$set = 0; $skip = 0; $missing = 0;
foreach ($rows as $r) {
    [$name, $file] = $r;
    $cands = \App\Models\Prisoner::withoutGlobalScopes()
        ->whereRaw("LOWER(name) = ?", [strtolower($name)])->get();
    $p = null;
    foreach ($cands as $c) {
        $aff = array_map("strtolower", (array) $c->affiliation);
        if (in_array("lincoln university", $aff, true)) { $p = $c; break; }
    }
    if (! $p) { echo "  not found: {$name}\n"; $missing++; continue; }
    if (! empty($p->photo)) { $skip++; continue; }
    $rel = "prisoners/oxford/" . $file;
    if (! is_file(storage_path("app/public/" . $rel))) { echo "  file missing: {$file}\n"; continue; }
    $p->photo = $rel;
    $p->save();
    echo "  set {$p->slug}\n";
    $set++;
}
echo "Oxford: set {$set}; skipped {$skip}; not found {$missing}.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Mullaney re-cropped and Oxford yearbook portraits attached."
