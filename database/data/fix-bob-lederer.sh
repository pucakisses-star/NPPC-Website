#!/usr/bin/env bash
#
# Bob Lederer -- grand jury resister jailed alongside Terry Bisson in the
# same 1985 federal grand jury investigation.
#
#   Subpoena served              1985-03-27  (recorded as arrest_date)
#   Found in civil contempt      1985-06-28  (recorded as sentenced_date)
#   Ordered to surrender         1985-07-07  (noted in the sentence text)
#   Incarceration began          1985-07-08
#   Released                     1985-10-03  (87 days)
#
# Birth year 1954 (year precision). Sets his photo.
#
# Idempotent (force-replaces the photo). Run from the repo root:
#   bash database/data/fix-bob-lederer.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "bob-lederer")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Lederer%")->first();
if (! $p) { echo "NOT FOUND: Bob Lederer\n"; exit(1); }

$p->setPartialDate("birthdate", 1954, null, null);
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();
echo "{$p->name}: born 1954 (year precision).\n";

$c = $p->cases()->orderBy("created_at")->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
if (! $c->charges) {
    $c->charges = "Civil contempt for refusing to testify before a federal grand jury.";
}
if (! $c->convicted) {
    $c->convicted = "Never charged with an underlying offense — jailed for civil contempt";
}
$c->sentence = "Served with a grand jury subpoena on March 27, 1985; found in civil contempt on June 28, 1985 and ordered to surrender on July 7. Jailed from July 8 to October 3, 1985 -- 87 days.";
$c->setPartialDate("arrest_date", 1985, 3, 27);
$c->setPartialDate("sentenced_date", 1985, 6, 28);
$c->setPartialDate("incarceration_date", 1985, 7, 8);
$c->setPartialDate("release_date", 1985, 10, 3);
$c->save();
echo "  case: subpoena 1985-03-27, contempt 1985-06-28, jailed 1985-07-08 -> 1985-10-03, days={$c->imprisoned_for_days} (expected 87).\n";

$src = base_path("database/data/photos/bob-lederer.jpg");
$dstRel = "prisoners/{$p->slug}.jpg";
if (is_file($src)) {
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel;
    $p->save();
    echo "  photo set -> {$dstRel}\n";
} else {
    echo "  PHOTO SOURCE MISSING: database/data/photos/bob-lederer.jpg\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
