#!/usr/bin/env bash
#
# Attach Karunakaran Kandasamy's photo and fill in his (currently empty) case.
#
# Kandasamy ("Karuna") was director of the American branch of the Liberation
# Tigers of Tamil Eelam (LTTE / Tamil Tigers), running the World Tamil
# Coordinating Committee front in Queens, NY. His NPPC case record had no
# charges/dates/sentence at all; this fills it from DOJ (EDNY) releases and
# court coverage:
#   - Arrested April 25, 2007 (EDNY, Brooklyn), held at MDC Brooklyn.
#   - Charged with conspiring to provide material support to a designated FTO
#     (18 U.S.C. 2339B).
#   - Pleaded guilty June 9, 2009 before Chief Judge Raymond J. Dearie.
#   - Sentenced May 12, 2012 to time served (~5 years / 1,844 days) after the
#     judge rejected the government's 20-year request as excessive; released.
#
# The photo (a booking-style portrait supplied by the site owner) is committed
# at database/data/audit-photos/karunakaran-kandasamy.jpg.
#
# Idempotent: sets the photo only if empty and writes the authoritative case
# fields. Run from the repo root:
#   bash database/data/update-kandasamy.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/audit-photos/karunakaran-kandasamy.jpg"
DST="storage/app/public/prisoners/karunakaran-kandasamy.jpg"
mkdir -p "$(dirname "$DST")"
cp -f "$SRC" "$DST"
echo "Installed $DST."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "karunakaran-kandasamy")->first();
if (! $p) { echo "karunakaran-kandasamy not found\n"; return; }

if (empty($p->photo) && is_file(storage_path("app/public/prisoners/karunakaran-kandasamy.jpg"))) {
    $p->photo = "prisoners/karunakaran-kandasamy.jpg";
    $p->save();
    echo "SET photo\n";
}

$c = $p->cases()->first();
if (! $c) { $c = new \App\Models\PrisonerCase(); $c->prisoner_id = $p->id; }

$inst = \App\Models\Institution::firstOrCreate(
    ["name" => "Metropolitan Detention Center, Brooklyn"],
    ["city" => "Brooklyn", "state" => "New York"]
);
$c->institution_id = $inst->id;
$c->charges = "Conspiring to provide material support to a designated Foreign Terrorist Organization (the Liberation Tigers of Tamil Eelam / Tamil Tigers), 18 U.S.C. 2339B.";
$c->convicted = "Yes — pleaded guilty June 9, 2009 before Chief U.S. District Judge Raymond J. Dearie (EDNY, Brooklyn).";
$c->plead = "Guilty plea to conspiring to provide material support to the LTTE, entered June 9, 2009.";
$c->sentence = "Sentenced May 12, 2012 to time served (about five years / 1,844 days in pretrial detention). Chief Judge Dearie rejected the 20-year term sought by prosecutors as excessive and released him.";
$c->imprisoned_for_days = 1844;
$c->setPartialDate("arrest_date", 2007, 4, 25);
$c->setPartialDate("incarceration_date", 2007, 4, 25);
$c->setPartialDate("sentenced_date", 2012, 5, 12);
$c->setPartialDate("release_date", 2012, 5, 12);
$c->save();
echo "Updated case (arrested 2007-04-25, released 2012-05-12, time served).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Kandasamy photo attached and case corrected."
