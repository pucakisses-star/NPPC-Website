#!/usr/bin/env bash
#
# Terry Bisson -- grand jury resister, John Brown Anti-Klan Committee.
#
# - Full name Terry Ballantine Bisson; born February 12, 1942, died
#   January 10, 2024.
# - Affiliation: John Brown Anti-Klan Committee (JBAKC).
#   Ideology: Anti-Imperialism.
# - Case: FBI grand jury subpoena served March 27, 1985; refused to testify
#   about political associates; jailed for civil contempt July 8, 1985;
#   released October 3, 1985 (87 days). Never charged with any bombing or
#   other illegal activity.
# - Replaces his photo with the Rosalie Winard headshot.
#
# Idempotent (force-replaces the photo). Run from the repo root:
#   bash database/data/fix-terry-bisson.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "terry-bisson")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Terry%Bisson%")->first();
if (! $p) { echo "NOT FOUND: Terry Bisson\n"; exit(1); }

$p->name = "Terry Bisson";
$p->first_name = "Terry";
$p->middle_name = "Ballantine";
$p->last_name = "Bisson";
$p->setPartialDate("birthdate", 1942, 2, 12);
$p->setPartialDate("death_date", 2024, 1, 10);

$affs = is_array($p->affiliation) ? $p->affiliation : [];
if (! in_array("John Brown Anti-Klan Committee", $affs, true)) { $affs[] = "John Brown Anti-Klan Committee"; }
$p->affiliation = array_values($affs);

$ideo = is_array($p->ideologies) ? $p->ideologies : [];
if (! in_array("Anti-Imperialism", $ideo, true)) { $ideo[] = "Anti-Imperialism"; }
$p->ideologies = array_values($ideo);

$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();
echo "{$p->name} (Terry Ballantine Bisson): 1942-02-12 - 2024-01-10, age {$p->age}.\n";
echo "  affiliation: ".implode(", ", $p->affiliation)." | ideologies: ".implode(", ", $p->ideologies)."\n";

$c = $p->cases()->orderBy("created_at")->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
$c->charges = "Civil contempt for refusing to testify before a federal grand jury. Served with an FBI grand jury subpoena on March 27, 1985, he refused to cooperate, stating that he would not provide information about political associates. He was never charged with participating in any bombing or other illegal activity -- his only offense was refusing to testify.";
$c->convicted = "Never charged with an underlying offense — jailed for civil contempt";
$c->sentence = "Held in civil contempt and jailed from July 8 to October 3, 1985 -- 87 days.";
$c->setPartialDate("arrest_date", 1985, 3, 27);
$c->setPartialDate("incarceration_date", 1985, 7, 8);
$c->setPartialDate("release_date", 1985, 10, 3);
$c->save();
echo "  case: 1985-07-08 -> 1985-10-03, days={$c->imprisoned_for_days} (expected 87).\n";

$src = base_path("database/data/photos/terry-bisson.jpg");
$dstRel = "prisoners/{$p->slug}.jpg";
if (is_file($src)) {
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel;
    $p->save();
    echo "  photo replaced -> {$dstRel}\n";
} else {
    echo "  PHOTO SOURCE MISSING: database/data/photos/terry-bisson.jpg\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
