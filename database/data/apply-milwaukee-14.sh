#!/usr/bin/env bash
#
# Attach photos and fill in birth/death dates for the Milwaukee Fourteen.
#
# The Milwaukee 14 were Catholic anti-Vietnam-War activists who, on September
# 24, 1968, removed roughly 10,000 draft records from a Milwaukee Selective
# Service office and burned them with homemade napalm in Cathedral Square, then
# waited to be arrested. This backfills the cohort:
#
#   - Photos for 12 of the 14 (the other two -- Michael Cullen and Bob Graf,
#     both living -- have no freely-licensed solo image and are left without
#     one rather than attaching a blurry group crop or a misattributed face).
#   - Birth/death dates where a reliable source exists.
#
# Photo provenance (openly-accessible, no commercial-agency images):
#   - Lawrence Rosebaugh: Wikimedia Commons, CC BY-SA 4.0.
#   - Jim Forest: his own family site (jimandnancyforest.com), cropped.
#   - Fred Ojile: Star Tribune obituary portrait.
#   - The remaining nine: UMass Amherst Special Collections, Liberation News
#     Service Records (MS 546) -- an open academic archive, cross-checked
#     against the archive's own left-to-right identifications; group shots were
#     cropped to the labeled individual.
# The full per-person basis is in database/data/milwaukee-14.json.
#
# Anthony Mullaney's entry also REPLACES a misattributed namesake mugshot (the
# file containing "b7d981f3") if it is still present.
#
# Idempotent: sets a photo only where the record has none (except the Mullaney
# override) and writes dates only where the field is still empty. Run from the
# repo root:
#   bash database/data/apply-milwaukee-14.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRCDIR="database/data/photos/milwaukee-14"
DSTDIR="storage/app/public/prisoners/milwaukee-14"
mkdir -p "$DSTDIR"
cp -f "$SRCDIR"/*.jpg "$DSTDIR"/ 2>/dev/null || true
echo "Copied Milwaukee 14 photos into $DSTDIR."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/milwaukee-14.json")), true);
if (! is_array($rows)) { echo "Could not read milwaukee-14.json\n"; return; }

$photoSet = 0; $photoSkip = 0; $dateSet = 0; $missing = 0;
foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }

    // Photo
    if (! empty($r["photo"])) {
        $rel = "prisoners/milwaukee-14/" . $r["photo"];
        $exists = is_file(storage_path("app/public/" . $rel));
        $override = isset($r["overwrite_if_contains"]) && ! empty($p->photo)
            && str_contains((string) $p->photo, $r["overwrite_if_contains"]);
        if ($exists && (empty($p->photo) || $override)) {
            $p->photo = $rel;
            $photoSet++;
            echo "  photo {$p->slug}" . ($override ? " (replaced wrong)\n" : "\n");
        } else {
            $photoSkip++;
        }
    }

    // Dates (only where empty)
    $touched = false;
    if (! empty($r["birth"]) && empty($p->birthdate)) {
        $b = $r["birth"];
        $p->setPartialDate("birthdate", $b[0] ?? null, $b[1] ?? null, $b[2] ?? null);
        $touched = true;
    }
    if (! empty($r["death"]) && empty($p->death_date)) {
        $d = $r["death"];
        $p->setPartialDate("death_date", $d[0] ?? null, $d[1] ?? null, $d[2] ?? null);
        $touched = true;
    }
    if ($touched) { $dateSet++; }

    if ($p->isDirty()) { $p->save(); }
}

echo "Photos set: {$photoSet}; photos skipped (present/absent): {$photoSkip}; date records updated: {$dateSet}; not found: {$missing}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Milwaukee 14 photos and dates applied."
