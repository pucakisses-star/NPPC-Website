#!/usr/bin/env bash
#
# Sami Hamdi -- portrait, custody dates, facility, and what could and
# could not be learned of his biography.
#
# The case carried an arrest date but NO incarceration or release date,
# so a man held eighteen days showed a counter of zero.
#
#   Oct 26, 2025   detained by ICE at San Francisco International
#                  Airport, the day after speaking at a CAIR gala in
#                  Sacramento, after the State Department revoked his
#                  visa; held at the GOLDEN STATE ANNEX ICE facility in
#                  McFarland, California, with a brief transfer to
#                  Fresno around his early-November hearing
#   Nov 10, 2025   agreement at a hearing that he would depart
#                  voluntarily; the government stipulated he was not "a
#                  danger to the community or to national security"
#   Nov 13, 2025   released and flew to London
#                  = 18 days, matching every press account
#
# NO DATE OF BIRTH IS PUBLICLY DOCUMENTED. Press coverage of the
# detention gives his age as 35, nothing more precise, and being 35 in
# late October 2025 places his birth anywhere from late 1989 to late
# 1990 -- a window spanning two calendar years, which does not fit a
# year-precision birthdate field (the Barbara Katt rule). The AGE FIELD
# IS SET TO 35 with the as-of noted in the case text; with no birthdate
# behind it the figure will not advance on its own, and a real date of
# birth should replace it if one is ever published.
#
# THE PORTRAIT is cropped to him alone from the family photograph (him,
# his wife and their child) that his family provided to press coverage
# of his release.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-sami-hamdi.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "sami-hamdi")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: sami-hamdi\n";
    exit(1);
}

$p->first_name = "Sami";
$p->last_name = "Hamdi";
$p->gender = "Male";
$p->race = "Middle Eastern";
$p->age = 35;
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->ideologies = ["Pro-Palestine", "Press Freedom"];
$p->description = "Sami Hamdi is a British political commentator and journalist of Tunisian and Algerian descent, managing director and editor-in-chief of the consultancy The International Interest, and a regular analyst of Middle East politics on British television. Midway through a United States speaking tour on Palestinian rights, and a day after speaking at a CAIR gala in Sacramento, he was detained by ICE at San Francisco International Airport on October 26, 2025, after the State Department revoked his visa; the government said he had overstayed, while he, his lawyers and his supporters said the visa was valid and that he was targeted for his criticism of Israel and the war in Gaza. He was held at the Golden State Annex ICE facility in McFarland, California, with a brief transfer to Fresno around his hearing, and described overcrowded cells where fifteen men slept on yoga mats. At a hearing on November 10, 2025 the government agreed to his voluntary departure, stipulating that he was not a danger to the community or to national security, and on November 13 — after eighteen days — he was released and flew home to London, where he was reunited with his wife and child. He called the detention an attack on freedoms and has continued his commentary since.";
$p->save();

$src = database_path("data/photos/nonfree/sami-hamdi.jpg");
if (is_file($src)) {
    File::ensureDirectoryExists(storage_path("app/public/prisoners"));
    $dest = "prisoners/sami-hamdi.jpg";
    File::copy($src, storage_path("app/public/".$dest), true);
    touch(storage_path("app/public/".$dest));
    $p->photo = $dest;
    $p->save();
} else {
    echo "  photo file missing: {$src}\n";
}

$annex = Institution::firstOrCreate(
    ["name" => "Golden State Annex"],
    ["city" => "McFarland", "state" => "California"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $annex->id;
$case->charges = "Civil immigration detention after a State Department visa revocation — no criminal charge. ICE said he had overstayed his visa; he and his lawyers said the visa was valid and the detention was political retaliation for his commentary on Israel and Gaza.";
$case->sentence = "Eighteen days of civil detention, October 26 to November 13, 2025, at the Golden State Annex ICE facility in McFarland, California, with a brief transfer to Fresno around his hearing. At a hearing on November 10 the government agreed to his voluntary departure and stipulated that he was not a danger to the community or to national security; he was released on November 13 and flew to London the same day. Press coverage gave his age as 35 at the time — nothing more precise is published, and 35 in late October 2025 spans a birth window from late 1989 to late 1990, too wide for a birthdate field, so the stored age of 35 carries no birthdate behind it and will not advance on its own.";
$case->setPartialDate("arrest_date", 2025, 10, 26);
$case->setPartialDate("incarceration_date", 2025, 10, 26);
$case->setPartialDate("release_date", 2025, 11, 13);
$case->save();

$p->refresh()->load("cases");
$c = $p->cases->first();
echo "Sami Hamdi  [{$p->slug}]\n";
echo "  age ".($p->age ?? "-")."   (35 as reported at the detention; no public birthdate)\n";
echo "  incarcerated ".(optional($c->incarceration_date)->toDateString() ?: "-")."   released ".(optional($c->release_date)->toDateString() ?: "-")."\n";
echo "  imprisoned_for_days = ".($c->imprisoned_for_days ?? "null")."  (expect 18, was 0)\n";
echo "  photo ".($p->photo ?: "(none)")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
