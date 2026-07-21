#!/usr/bin/env bash
#
# Fix Christopher Doyon's ("Commander X") case entry.
#
# Bug: Incarceration Date was 2022-06-27 — the same day as his Release /
# Sentencing date — implying he served no time. In fact he was arrested
# 2021-06-10, held without bail as a fugitive, and sentenced to TIME SERVED
# on 2022-06-27: roughly 382 days in custody (matches the source "Imprisoned
# For: 382"). So incarceration should be his 2021-06-10 detention date.
#
# Also: sharpen the bare "Hacking" charge, and (re)populate the exile period
# — the ~decade he spent as a fugitive before his 2021 arrest — in case the
# earlier exile script was never deployed.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-christopher-doyon-entry.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "christopher-doyon")->first();
if (! $p) { echo "christopher-doyon not found\n"; return; }
$c = $p->cases()->first();
if (! $c) { echo "no case\n"; return; }

// 1) Fix the incarceration date: he was in custody from his 2021-06-10
//    arrest, not from the 2022 sentencing day.
$inc = $c->incarceration_date instanceof \Carbon\Carbon ? $c->incarceration_date->format("Y-m-d") : (string) $c->incarceration_date;
if ($inc === "" || $inc === "2022-06-27") {
    $c->incarceration_date = "2021-06-10";
    echo "SET incarceration_date 2021-06-10 (was {$inc})\n";
}

// 2) Sharpen the charge text.
$charges = $c->charges;
if (is_array($charges)) { $charges = implode(" ", $charges); }
if (trim((string) $charges) === "" || strtolower(trim((string) $charges)) === "hacking") {
    $c->charges = "Computer hacking — 2010 DDoS attack that took down the Santa Cruz County website, as \"Commander X\" of the People'"'"'s Liberation Front / Anonymous";
    echo "SET charges (sharpened)\n";
}

// 3) Ensure the exile period is present.
if (empty($c->in_exile_since))  { $c->in_exile_since = "2011-01-29"; echo "SET in_exile_since 2011-01-29\n"; }
if (empty($c->end_of_exile))    { $c->end_of_exile = "2021-06-11"; echo "SET end_of_exile 2021-06-11\n"; }
if (empty($c->in_exile_for_days)) { $c->in_exile_for_days = 3786; echo "SET in_exile_for_days 3786\n"; }

if ($c->isDirty()) { $c->save(); echo "saved\n"; } else { echo "nothing to do\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Christopher Doyon entry fixed."
