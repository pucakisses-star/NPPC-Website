#!/usr/bin/env bash
#
# Links garland9.org — the support site for the nine pro-life rescuers
# convicted under the FACE Act for the October 2020 Washington
# Surgi-Clinic blockade and sentenced by Judge Colleen Kollar-Kotelly
# to 24-57 months — on all nine records. Fill-if-empty: Lauren Handy
# keeps her existing paaunow.org support page.
#
# Run from the repo root:  bash database/data/set-garland9-website.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$slugs = [
    "lauren-handy", "jonathan-darnel", "john-hinshaw", "herb-geraghty",
    "william-goodman", "joan-bell", "paula-harlow", "jean-marshall",
    "heather-idoni",
];
foreach ($slugs as $slug) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "missing {$slug}\n"; continue; }
    if (empty($p->website)) {
        $p->website = "https://www.garland9.org/";
        $p->save();
        echo "WEBSITE {$slug}\n";
    } else {
        echo "kept existing on {$slug}: {$p->website}\n";
    }
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Garland 9 website linked."
