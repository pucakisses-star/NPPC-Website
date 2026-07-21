#!/usr/bin/env bash
#
# Add the Oberlin–Wellington Rescue custody roster and correct Charles
# Langston's release date.
#
# In September 1858 a crowd of Oberlin and Wellington, Ohio residents rescued
# John Price — a young man who had escaped slavery — from federal slave
# catchers acting under the Fugitive Slave Act of 1850. Thirty-seven men were
# indicted. This script adds the rescuers who were actually confined in the
# Cuyahoga County Jail (or given the nominal 24-hour sentences) and who are
# not yet in the database. The full record and durations live in
# database/data/oberlin-rescuers.json.
#
#   - 15 men refused bail and were held April 15 – July 6, 1859 (~82 days),
#     until the mass discharge that ended the affair.
#   - Boies, Wadsworth and Daniel Williams took bail after ~20 days.
#   - Shipherd and O. S. B. Wall were released after ~10 days when the court
#     found the grand jury had badly misstated their names.
#   - The elderly Matthew Gillett was allowed out after ~29 days.
#   - Five men (Mandeville, Niles, Cummings, De Wolfe, Loveland) drew nominal
#     24-hour sentences.
#   - William E. Lincoln also carries an earlier overnight detention
#     (Jan 14–15, 1859) as a second case.
#
# Charles Langston and Simeon Bushnell were already in the database. Langston
# is corrected here: his continuous confinement ran April 15 – June 1, 1859
# (about 47 days), when he was released after his formal 20-day sentence and
# the collapse of the prosecution — NOT the July 6 general release used by an
# earlier pass. Bushnell (held to July 11) is left as recorded.
#
# Idempotent: prisoners are keyed by name and skipped if already present;
# Langston's correction is a fixed assignment. Run from the repo root:
#   bash database/data/add-oberlin-rescuers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$path = base_path("database/data/oberlin-rescuers.json");
$records = json_decode(file_get_contents($path), true);
if (! is_array($records)) { echo "Could not read roster JSON\n"; return; }

$charges = "Aiding the 1858 Oberlin–Wellington Rescue of John Price, a fugitive from slavery, in violation of the Fugitive Slave Act of 1850.";
$added = 0; $skipped = 0;

foreach ($records as $rec) {
    $cases = $rec["cases"] ?? [];
    unset($rec["cases"]);

    $exists = \App\Models\Prisoner::withoutGlobalScopes()->where("name", $rec["name"])->exists();
    if ($exists) { echo "  skip {$rec["name"]} (already present)\n"; $skipped++; continue; }

    $rec["state"] = "Ohio";
    $rec["era"] = "1800s";
    $rec["affiliation"] = ["Oberlin–Wellington Rescue"];
    $rec["ideologies"] = ["Abolitionism"];
    $rec["released"] = true;
    $rec["in_custody"] = false;

    $prisoner = \App\Models\Prisoner::create($rec);

    foreach ($cases as $cd) {
        $cd["prisoner_id"] = $prisoner->id;
        if (empty($cd["charges"])) { $cd["charges"] = $charges; }
        \App\Models\PrisonerCase::create($cd);
    }
    echo "  added {$prisoner->name} (slug {$prisoner->slug}, ".count($cases)." case(s))\n";
    $added++;
}

echo "Added {$added}, skipped {$skipped}.\n";

// Correct Charles Langston: April 15 - June 1, 1859 (~47 days).
$lang = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "charles-langston")->first();
if ($lang && ($c = $lang->cases()->first())) {
    $c->setPartialDate("arrest_date", 1859, 4, 15);
    $c->setPartialDate("incarceration_date", 1859, 4, 15);
    $c->setPartialDate("release_date", 1859, 6, 1);
    $c->imprisoned_for_days = 47;
    $c->sentence = "Formal sentence: twenty days in jail and a fine. He entered the Cuyahoga County Jail on April 15, 1859 and was held continuously until his release on June 1, 1859 — about 47 days — after the prosecution collapsed.";
    $c->save();
    echo "CORRECTED charles-langston (Apr 15 - Jun 1 1859, ~47 days)\n";
} else {
    echo "charles-langston: not found; correction skipped\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Oberlin–Wellington Rescue roster added and Langston corrected."
