#!/usr/bin/env bash
#
# Add a court/custody case to 1970s-era prisoner records that currently have
# NONE. A full-database audit found 472 records with no case attached; this
# covers the 1970s cohort — 125 cases, mostly American Indian Movement (AIM)
# and Black Panther-era defendants sourced from The Black Panther newspaper
# (Wounded Knee/Pine Ridge, Attica, courthouse and prison-rebellion cases).
# Many are acquittals or dropped charges, recorded with the outcome in the
# case. Structured from each record's own description and refined against
# court records where possible. The data lives in
# database/data/cases-1970s-batch1.json.
#
# Approximate dates are stored at their true precision (year/month/day) via the
# case's setPartialDate(). Institutions/courts are matched or created by name.
#
# Idempotent: a case is added ONLY to a prisoner who still has zero cases, so
# re-running never double-adds and never touches records that already have a
# case. Run from the repo root:
#   bash database/data/add-cases-2020s-batch1.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/cases-1970s-batch1.json")), true);
if (! is_array($rows)) { echo "Could not read cases JSON\n"; return; }

$applyDate = function ($case, $field, $data) {
    if (empty($data[$field])) { return; }
    $parts = explode("-", $data[$field]);
    $y = (int) ($parts[0] ?? 0); $m = (int) ($parts[1] ?? 1); $d = (int) ($parts[2] ?? 1);
    $prec = $data[$field . "_precision"] ?? "day";
    if ($prec === "year")  { $case->setPartialDate($field, $y); }
    elseif ($prec === "month") { $case->setPartialDate($field, $y, $m); }
    else { $case->setPartialDate($field, $y, $m, $d); }
};

$added = 0; $skipHasCase = 0; $missing = 0;

foreach ($rows as $r) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $r["slug"])->first();
    if (! $p) { echo "  not found: {$r["slug"]}\n"; $missing++; continue; }
    if ($p->cases()->count() > 0) { $skipHasCase++; continue; }

    $institutionId = null;
    if (! empty($r["institution_name"])) {
        $inst = \App\Models\Institution::firstOrCreate(
            ["name" => $r["institution_name"]],
            array_filter([
                "city"  => $r["institution_city"] ?? null,
                "state" => $r["institution_state"] ?? null,
            ])
        );
        $institutionId = $inst->id;
    }

    $c = new \App\Models\PrisonerCase();
    $c->prisoner_id = $p->id;
    $c->institution_id = $institutionId;
    $c->charges = $r["charges"] ?? null;
    if (! empty($r["convicted"])) { $c->convicted = $r["convicted"]; }
    if (! empty($r["sentence"]))  { $c->sentence = $r["sentence"]; }
    if (isset($r["imprisoned_for_days"])) { $c->imprisoned_for_days = (int) $r["imprisoned_for_days"]; }
    $applyDate($c, "arrest_date", $r);
    $applyDate($c, "incarceration_date", $r);
    $applyDate($c, "release_date", $r);
    $c->save();
    echo "  case added: {$p->slug}\n";
    $added++;
}

echo "Added {$added} case(s); skipped {$skipHasCase} that already had one; {$missing} not found.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. 1970s caseless records enriched."
