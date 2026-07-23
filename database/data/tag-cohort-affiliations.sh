#!/usr/bin/env bash
#
# Give two case cohorts a shared group affiliation so they can be kept together
# in the database sort. Most defendant cohorts already have a group affiliation
# (Camden 28, Milwaukee 14, Panther 21, ...); these two were tagged only by
# ideology or free text:
#
#   - Stop Cop City / Defend the Atlanta Forest: the 61 records carrying the
#     "Stop Cop City" ideology get the affiliation "Defend the Atlanta Forest"
#     (3 already have it).
#   - Prairieland Defendants: the July 4, 2025 Prairieland ICE-detention-center
#     prosecution defendants (identified by description) get the affiliation
#     "Prairieland Defendants". People merely detained at that facility for
#     unrelated reasons (e.g. Leqaa Kordia) are excluded by the filter.
#
# The group affiliation is placed FIRST so the chronological sort clusters the
# cohort by it. Idempotent: skips records that already carry the tag. Run from
# the repo root:
#   bash database/data/tag-cohort-affiliations.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$copCity = 0; $prairie = 0; $changed = 0;
\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "description", "ideologies", "affiliation")
    ->chunk(500, function ($chunk) use (&$copCity, &$prairie, &$changed) {
    foreach ($chunk as $p) {
        $aff = array_values(array_filter((array) $p->affiliation, fn ($v) => trim((string) $v) !== ""));
        $dirty = false;

        $ideol = array_map(fn ($v) => strtolower(trim((string) $v)), (array) $p->ideologies);
        if (in_array("stop cop city", $ideol, true) && ! in_array("Defend the Atlanta Forest", $aff, true)) {
            $aff[] = "Defend the Atlanta Forest";
            $copCity++; $dirty = true;
        }

        $dl = strtolower((string) $p->description);
        $isPrairie = str_contains($dl, "prairieland")
            && (str_contains($dl, "july 4, 2025")
                || str_contains($dl, "prairieland defendant")
                || str_contains($dl, "prairieland prosecution"));
        if ($isPrairie && ! in_array("Prairieland Defendants", $aff, true)) {
            array_unshift($aff, "Prairieland Defendants");
            $prairie++; $dirty = true;
        }

        if ($dirty) {
            $p->affiliation = array_values(array_unique($aff));
            $p->save();
            $changed++;
        }
    }
});

echo "Tagged {$copCity} Cop City record(s) and {$prairie} Prairieland record(s); {$changed} record(s) updated.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Cohort affiliations tagged."
