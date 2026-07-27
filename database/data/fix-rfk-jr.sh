#!/usr/bin/env bash
#
# Robert F. Kennedy Jr. -- Vieques civil-disobedience record.
#
# Name  "RFK Jr. (Robert F. Kennedy Jr.)"  ->  "Robert F. Kennedy Jr.",
#       with RFK Jr. kept as an aka so the short form still finds him.
#
# Custody, in two distinct periods:
#   Arrested / taken into military custody   April 28, 2001
#   Released on 3,000 dollar bail            April 30, 2001   (2 days)
#   Tried, convicted and sentenced           July 6, 2001     -- 30 days
#   Returned to MDC Guaynabo                 July 6, 2001
#   Released on completing the sentence      August 1, 2001   (26 days)
#
# Those are recorded as two cases so both stints show, totalling 28 days
# against the 30-day sentence -- the two days already served on the April
# arrest were credited.
#
# Photo: his 2007 portrait from Wikimedia Commons (CC BY-SA 4.0), cropped to
# a portrait frame. Chosen over the current Wikipedia lead image, which is a
# 2025 official government portrait, because 2007 is far closer to the
# environmental-lawyer period this record covers.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-rfk-jr.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["rfk-jr", "rfk-jr-robert-f-kennedy-jr", "robert-f-kennedy-jr"])
        ->orWhereRaw("LOWER(name) LIKE ?", ["%kennedy%jr%"]))
    ->first();
if (! $p) { echo "NOT FOUND: Robert F. Kennedy Jr.\n"; exit(1); }
echo "record: {$p->name} [{$p->slug}]\n";

$oldName = $p->name;
$p->name = "Robert F. Kennedy Jr.";
$p->first_name = "Robert";
$p->middle_name = "Francis";
$p->last_name = "Kennedy";
$akas = array_filter(array_map("trim", explode(";", (string) $p->aka)));
foreach (["RFK Jr."] as $a) { if (! in_array($a, $akas, true)) { $akas[] = $a; } }
$p->aka = implode("; ", $akas);
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
if (! $p->state) { $p->state = "Puerto Rico"; }
$p->save();
echo "  renamed: {$oldName} -> {$p->name} (aka {$p->aka}), slug {$p->slug}\n";

$inst = Institution::firstOrCreate(
    ["name" => "MDC Guaynabo"],
    ["city" => "Guaynabo", "state" => "Puerto Rico"],
);

// --- Sentence served, July 6 to August 1, 2001 ---
$main = $p->cases()->where("charges", "not like", "%initial%")->orderBy("created_at")->first();
if (! $main) { $main = $p->cases()->make(); $main->prisoner_id = $p->id; }
$main->charges = "Trespassing on the United States Navy bombing range at Vieques, Puerto Rico, during the civil-disobedience wave that followed the killing of David Sanes.";
$main->institution_id = $inst->id;
$main->convicted = "Yes — convicted and sentenced July 6, 2001";
$main->sentence = "Thirty days, imposed July 6, 2001. Returned to MDC Guaynabo the same day and released on completing the sentence on August 1, 2001. The two days served after the April arrest were credited.";
$main->setPartialDate("arrest_date", 2001, 4, 28);
$main->setPartialDate("sentenced_date", 2001, 7, 6);
$main->setPartialDate("incarceration_date", 2001, 7, 6);
$main->setPartialDate("release_date", 2001, 8, 1);
$main->save();
echo "  sentence case: 2001-07-06 -> 2001-08-01, days={$main->imprisoned_for_days} (expected 26)\n";

// --- Initial military custody, April 28 to 30, 2001 ---
$initial = $p->cases()->where("charges", "like", "%initial%")->first();
if (! $initial) {
    $initial = $p->cases()->create([
        "charges" => "Initial military custody after the April 28, 2001 arrest for trespassing on the Vieques bombing range.",
    ]);
}
$initial->convicted = "Released on 3,000 dollar bail April 30, 2001, pending trial";
$initial->sentence = "Held from April 28 to April 30, 2001 before release on bail; the two days were credited against the later 30-day sentence.";
$initial->setPartialDate("arrest_date", 2001, 4, 28);
$initial->setPartialDate("incarceration_date", 2001, 4, 28);
$initial->setPartialDate("release_date", 2001, 4, 30);
$initial->save();
echo "  initial case:  2001-04-28 -> 2001-04-30, days={$initial->imprisoned_for_days} (expected 2)\n";
echo "  total custody: ".$p->cases()->sum("imprisoned_for_days")." days\n";

$src = base_path("database/data/photos/robert-f-kennedy-jr.jpg");
$dstRel = "prisoners/{$p->slug}.jpg";
if (is_file($src)) {
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel;
    $p->save();
    echo "  photo set -> {$dstRel}\n";
} else {
    echo "  PHOTO SOURCE MISSING: database/data/photos/robert-f-kennedy-jr.jpg\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
