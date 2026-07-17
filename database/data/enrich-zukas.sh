#!/usr/bin/env bash
#
# Bronislaus Joseph Zukas enrichment (July 2026). The record (joseph-zukas)
# already existed with the Yorty Committee contempt conviction described in
# prose; this fills the structured fields:
#
#  - aka "Bronislaus Joseph Zukas" (the full name the California Senate
#    fact-finding report indexes him under; he went by B. Joseph / Joseph),
#    plus first/last name fields;
#  - case: convicted, "60 days in jail and a $100 fine", 60 days served,
#    per the committee reports (conviction 1940; the Senate report states
#    he "was convicted and served a jail sentence").
#
# Fill-if-empty throughout; idempotent.
#
# Run from the repo root:  bash database/data/enrich-zukas.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "joseph-zukas")->first();
if (! $p) {
    echo "MISS joseph-zukas\n";
} else {
    $changed = false;
    if (empty($p->aka)) { $p->aka = "Bronislaus Joseph Zukas"; $changed = true; echo "SET aka\n"; }
    // first/last follow the display name he went by; the legal "Bronislaus"
    // is preserved in the alias above.
    if (empty($p->first_name)) { $p->first_name = "Joseph"; $changed = true; }
    if (empty($p->last_name)) { $p->last_name = "Zukas"; $changed = true; }
    if ($changed) { $p->save(); }

    $case = $p->cases()->first();
    if ($case) {
        $fill = [
            "convicted" => "Yes — contempt of committee",
            "sentence" => "60 days in jail and a $100 fine",
            "imprisoned_for_days" => 60,
        ];
        $caseChanged = false;
        foreach ($fill as $f => $v) {
            if (! empty($case->{$f})) { continue; }
            $case->{$f} = $v;
            $caseChanged = true;
        }
        if ($caseChanged) { $case->save(); echo "CASE enriched\n"; }
        else { echo "CASE already complete\n"; }
    }
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
    echo "Done.\n";
}
'
