#!/usr/bin/env bash
#
# Enrich the existing Therese Patricia Okoumou record (Statue of Liberty
# climber, July 4, 2018 — the record already exists with charges and
# sentence; this fills the gaps):
#
#   - Ideologies: Immigrant rights
#   - Affiliation: Rise and Resist (the group whose Liberty Island banner
#     protest she took part in before climbing alone)
#   - Judge on the case: U.S. Magistrate Judge Gabriel W. Gorenstein
#     (S.D.N.Y.), who convicted her at a December 17, 2018 bench trial and
#     sentenced her on March 19, 2019
#   - Case sentenced date: 2019-03-19
#
# Idempotent (each field only set when empty / marker not present).
#
# Run from the repo root:  bash database/data/enrich-okoumou.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "therese-patricia-okoumou")->first();
if (! $p) {
    echo "Okoumou record not found — nothing done.\n";
} else {
    $dirty = false;

    if (empty($p->ideologies)) {
        $p->ideologies = ["Immigrant rights"];
        $dirty = true;
    }
    if (empty($p->affiliation)) {
        $p->affiliation = ["Rise and Resist"];
        $dirty = true;
    }
    if ($dirty) {
        $p->save();
        echo "UPDATED prisoner tags\n";
    }

    $case = $p->cases()->first();
    if ($case) {
        $caseDirty = false;
        if (empty($case->judge)) {
            $case->judge = "Gabriel W. Gorenstein (U.S. Magistrate Judge, S.D.N.Y.)";
            $caseDirty = true;
        }
        if (empty($case->sentenced_date)) {
            $case->sentenced_date = "2019-03-19";
            $caseDirty = true;
        }
        if ($caseDirty) {
            $case->save();
            echo "UPDATED case (judge, sentenced date)\n";
        }
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Okoumou record enriched (ideology, affiliation, judge, sentencing date)."
