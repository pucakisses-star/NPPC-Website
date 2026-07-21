#!/usr/bin/env bash
#
# Add the 1831 Cherokee-mission prosecution roster (Worcester v. Georgia).
#
# In December 1830 Georgia enacted a law forbidding white persons to live in
# the Cherokee Nation without a state license and an oath of allegiance. The
# Georgia Guard arrested missionaries and residents through the spring and
# summer of 1831; eleven men were convicted on September 15, 1831 and
# sentenced to four years at hard labor. Nine accepted clemency and were
# discharged about September 22, 1831. Samuel A. Worcester and Dr. Elizur
# Butler refused the terms and were imprisoned at the Georgia Penitentiary in
# Milledgeville until Governor Lumpkin's discharge order of January 14, 1833,
# after the U.S. Supreme Court's decision in Worcester v. Georgia (1832).
#
# This adds all eleven convicted defendants (with their earlier Georgia Guard
# detentions recorded as additional cases) plus two ministers who were
# detained in 1831 but not among those sentenced — Rev. John Thompson and
# Rev. William McLeod. The full roster and durations live in
# database/data/worcester-cherokee-defendants.json.
#
# Idempotent: prisoners are keyed by name and created only if absent; each
# case is keyed by its incarceration/arrest date and added only if missing.
# Run from the repo root:
#   bash database/data/add-worcester-cherokee-defendants.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$path = base_path("database/data/worcester-cherokee-defendants.json");
$records = json_decode(file_get_contents($path), true);
if (! is_array($records)) { echo "Could not read roster JSON\n"; return; }

$added = 0; $skipped = 0; $casesAdded = 0;

foreach ($records as $rec) {
    $cases = $rec["cases"] ?? [];
    unset($rec["cases"]);

    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("name", $rec["name"])->first();
    if (! $p) {
        $rec["state"] = "Georgia";
        $rec["era"] = "1800s";
        $rec["ideologies"] = ["Native American rights", "Religious liberty"];
        $rec["released"] = true;
        $rec["in_custody"] = false;
        $p = \App\Models\Prisoner::create($rec);
        echo "  added {$p->name} (slug {$p->slug})\n";
        $added++;
    } else {
        echo "  exists {$p->name} (augmenting cases)\n";
        $skipped++;
    }

    foreach ($cases as $cd) {
        $key = $cd["incarceration_date"] ?? $cd["arrest_date"] ?? null;
        if ($key) {
            $dup = $p->cases()
                ->where(function ($q) use ($key) {
                    $q->whereDate("incarceration_date", $key)->orWhereDate("arrest_date", $key);
                })->exists();
            if ($dup) { continue; }
        }

        $instName = $cd["institution_name"] ?? null;
        $institutionId = null;
        if ($instName) {
            $inst = \App\Models\Institution::firstOrCreate(
                ["name" => $instName],
                array_filter([
                    "city"  => $cd["institution_city"] ?? null,
                    "state" => $cd["institution_state"] ?? null,
                ])
            );
            $institutionId = $inst->id;
        }
        unset($cd["institution_name"], $cd["institution_city"], $cd["institution_state"]);

        $cd["prisoner_id"] = $p->id;
        $cd["institution_id"] = $institutionId;
        \App\Models\PrisonerCase::create($cd);
        $casesAdded++;
    }
}

echo "Prisoners added {$added}, already present {$skipped}; cases added {$casesAdded}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Worcester v. Georgia / Cherokee-mission roster added."
