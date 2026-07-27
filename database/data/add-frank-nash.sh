#!/usr/bin/env bash
#
# Frank Nash -- IWW member imprisoned under Washington criminal-syndicalism
# law, Washington State Penitentiary at Walla Walla, inmate number 9516.
#
#   Incarcerated  September 28, 1921
#   Released      September 28, 1926 (expiration of the five-year sentence)
#   Custody       1,826 days -- exactly five years
#   Probable DOB  February 21, 1870
#
# Photo: his W.S.P. mugshot from the Washington State Digital Archives,
# cropped to the frontal view (the placard reading W.S.P. 9516 confirms the
# identification).
#
# Creates the record if missing, otherwise updates it. Idempotent. Run from
# the repo root:
#   bash database/data/add-frank-nash.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "frank-nash")->first()
    ?? Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["frank nash"])->first();

if (! $p) {
    $p = Prisoner::create([
        "name" => "Frank Nash", "first_name" => "Frank", "last_name" => "Nash",
        "gender" => "Male", "state" => "Washington", "era" => "1920s",
        "ideologies" => ["Labor Organizing"], "affiliation" => ["Industrial Workers of the World"],
        "inmate_number" => "9516",
        "in_custody" => false, "released" => true,
        "description" => "Frank Nash was a member of the Industrial Workers of the World imprisoned under Washington criminal-syndicalism law, which made membership in an organisation advocating industrial or political change through unlawful means a felony and was used across the Pacific Northwest to jail Wobblies for their organising. He served a five-year sentence at the Washington State Penitentiary in Walla Walla as inmate number 9516, entering on September 28, 1921 and released on September 28, 1926 at the expiration of his term.",
    ]);
    echo "created {$p->name} (slug {$p->slug}).\n";
} else {
    echo "updating {$p->name} (slug {$p->slug}).\n";
}

if (! $p->inmate_number) { $p->inmate_number = "9516"; }
if (! $p->state) { $p->state = "Washington"; }
if (! $p->era) { $p->era = "1920s"; }
if (! $p->gender) { $p->gender = "Male"; }

$affs = is_array($p->affiliation) ? $p->affiliation : [];
if (! in_array("Industrial Workers of the World", $affs, true)) { $affs[] = "Industrial Workers of the World"; }
$p->affiliation = array_values($affs);

// February 21, 1870 is the probable date of birth -- the day and month are
// documented, the year inferred, so it is stored as a full date and flagged
// as probable in the bio rather than silently presented as certain.
$p->setPartialDate("birthdate", 1870, 2, 21);
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();
echo "  born 1870-02-21 (probable), inmate #{$p->inmate_number}, age {$p->age}\n";

$inst = Institution::firstOrCreate(
    ["name" => "Washington State Penitentiary"],
    ["city" => "Walla Walla", "state" => "Washington"],
);

$c = $p->cases()->orderBy("created_at")->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
$c->charges = "Violation of Washington criminal-syndicalism law.";
$c->institution_id = $inst->id;
$c->convicted = "Yes — sentenced to five years";
$c->sentence = "Five years at the Washington State Penitentiary, Walla Walla, as inmate number 9516. Served in full, from September 28, 1921 to release at expiration of sentence on September 28, 1926.";
$c->setPartialDate("incarceration_date", 1921, 9, 28);
$c->setPartialDate("release_date", 1926, 9, 28);
$c->save();
echo "  case: 1921-09-28 -> 1926-09-28, days={$c->imprisoned_for_days} (expected 1826), {$inst->name}\n";

$src = base_path("database/data/photos/frank-nash.jpg");
$dstRel = "prisoners/{$p->slug}.jpg";
if (is_file($src)) {
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel;
    $p->save();
    echo "  photo set -> {$dstRel}\n";
} else {
    echo "  PHOTO SOURCE MISSING: database/data/photos/frank-nash.jpg\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
