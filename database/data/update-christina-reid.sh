#!/usr/bin/env bash
#
# Christina Reid -- update the REAL record (slug christina-reid), and delete
# the duplicate "Chris Reid" stub if it is still present.
#
# The display name is "Christina Reid"; the middle name Leigh is stored in the
# middle_name field rather than in the full name, which also keeps the public
# URL /prisoner/christina-reid intact.
#
# The stub and the real record are the same person, so this picks a survivor
# (preferring the record already named Christina Leigh Reid, otherwise the one
# with the most data), folds across anything only the other copy had -- photo,
# longer bio, institution, missing fields -- and deletes the loser. Podcast
# episodes and calendar entries are reassigned first.
#
# Applied to the survivor:
#   Gender        Female (the stub had Male)
#   Known as      Chris Reid (kept as an aka)
#   BOP number    84263-011
#   Born          c. 1964 (year precision)
#   Arrested      1989-07-12
#   Convicted     1990-06-18
#   Sentenced     1990-08-20 -- 41 months
#   Incarcerated  approximately 1991-06-11
#   Released      1994-06-21  (1,106 days, about 36 months of a 41-month
#                 sentence -- consistent with federal good-conduct credit)
#
# The existing slug is restored after saving as a safety net, in case the
# record was named something else; NEW_SLUG=1 accepts the regenerated slug.
#
# Idempotent. Run from the repo root:
#   bash database/data/update-christina-reid.sh
#   NEW_SLUG=1 bash database/data/update-christina-reid.sh   # adopt the new slug

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

// Matches the real record (slug christina-reid), the duplicate stub
// (chris-reid) and any already-renamed variant, by slug or by name.
$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["christina-reid", "christina-leigh-reid", "chris-reid"])
        ->orWhereRaw("LOWER(name) LIKE ? AND LOWER(name) LIKE ?", ["chris%", "%reid"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "ABORT: no Reid record found. Nothing updated.\n"; exit(1); }

// Survivor: the record already named Christina Leigh Reid, else the fullest.
$score = function (Prisoner $x) {
    $n = 0;
    foreach (["description", "photo", "birthdate", "inmate_number", "state", "era", "body", "gender", "race"] as $f) {
        if (! empty($x->{$f})) { $n++; }
    }

    return $n * 10 + $x->cases->count();
};
$real = $matches->first(fn ($m) => $m->slug === "christina-reid")
    ?? $matches->first(fn ($m) => strtolower($m->name) === "christina reid")
    ?? $matches->first(fn ($m) => strtolower($m->name) === "christina leigh reid")
    ?? $matches->sortByDesc($score)->first();
$dupes = $matches->reject(fn ($m) => $m->id === $real->id);

echo "real record: {$real->name}  [{$real->slug}]  (".$real->cases->count()." case(s))\n";

foreach ($dupes as $d) {
    echo "duplicate:   {$d->name}  [{$d->slug}]  (".$d->cases->count()." case(s))\n";

    if ($d->description && strlen($d->description) > strlen((string) $real->description)) {
        $real->description = $d->description;
        echo "    bio taken from the duplicate (longer)\n";
    }
    foreach (["gender", "race", "state", "address", "inmate_number", "era", "website", "twitter", "facebook", "instagram"] as $f) {
        if (empty($real->{$f}) && ! empty($d->{$f})) { $real->{$f} = $d->{$f}; echo "    {$f} <- {$d->{$f}}\n"; }
    }
    foreach (["ideologies", "affiliation"] as $f) {
        $a = is_array($real->{$f}) ? $real->{$f} : [];
        $b = is_array($d->{$f}) ? $d->{$f} : [];
        $merged = array_values(array_unique(array_merge($a, $b)));
        if ($merged !== $a) { $real->{$f} = $merged; echo "    {$f}: ".implode(", ", $merged)."\n"; }
    }
    if ($d->photo && empty($real->photo)) {
        $abs = storage_path("app/public/{$d->photo}");
        $real->photo = $d->photo;
        echo "    photo taken from the duplicate".(is_file($abs) ? "" : " (file missing on disk)")."\n";
    }
    // Institution, if the real record’s case lacks one.
    $rc = $real->cases()->orderBy("created_at")->first();
    $dc = $d->cases()->orderBy("created_at")->first();
    if ($rc && $dc && ! $rc->institution_id && $dc->institution_id) {
        $rc->institution_id = $dc->institution_id;
        $rc->save();
        echo "    institution copied to the surviving case\n";
    }

    \App\Models\PodcastEpisode::where("prisoner_id", $d->id)->update(["prisoner_id" => $real->id]);
    \App\Models\CalendarEntry::where("prisoner_id", $d->id)->update(["prisoner_id" => $real->id]);

    $n = $d->cases()->count();
    $d->delete();
    echo "    deleted duplicate and its {$n} case(s)\n";
}

// --- Apply the corrected details to the real record ---
// Display name stays "Christina Reid" -- the middle name lives in its own
// field, not in the full name -- which also keeps the slug christina-reid.
$real->name = "Christina Reid";
$real->first_name = "Christina";
$real->middle_name = "Leigh";
$real->last_name = "Reid";
$real->aka = "Chris Reid";
$real->gender = "Female";
$real->inmate_number = "84263-011";
if (! $real->state) { $real->state = "California"; }
if (! $real->era) { $real->era = "1990s"; }
$real->setPartialDate("birthdate", 1964, null, null);   // c. 1964 -- year only
$real->in_custody = false;
$real->awaiting_trial = false;
$real->released = true;
$real->description = "Christina Reid, known as Chris Reid, was an Irish republican prisoner held on United States federal charges relating to support for the Irish Republican Army, and imprisoned at FCI Pleasanton in California. Arrested on July 12, 1989, she was convicted on June 18, 1990 and sentenced on August 20, 1990 to 41 months. She entered custody in approximately June 1991 and was released on June 21, 1994. Her case was documented in the Prairie Fire Organizing Committee magazine Breakthrough.";
$oldSlug = $real->slug;
$real->save();   // renaming regenerates the slug
if ($oldSlug !== $real->slug && getenv("NEW_SLUG") !== "1") {
    // Keep the existing public URL (/prisoner/christina-reid) working.
    // Pass NEW_SLUG=1 to adopt the slug generated from the new name.
    $real->slug = $oldSlug;
    $real->save();
    echo "\nslug kept as {$oldSlug} so the existing URL keeps working (NEW_SLUG=1 to change it).\n";
}
echo "updated: {$real->name} (aka {$real->aka}), Female, BOP {$real->inmate_number}, born c.1964, slug {$real->slug}\n";

$inst = Institution::firstOrCreate(
    ["name" => "FCI Pleasanton"],
    ["city" => "Pleasanton", "state" => "California"],
);

$c = $real->cases()->orderBy("created_at")->first();
if (! $c) { $c = $real->cases()->make(); $c->prisoner_id = $real->id; }
$c->charges = "Federal charges relating to Irish republican activity — support for the Irish Republican Army.";
$c->institution_id = $inst->id;
$c->convicted = "Yes — convicted June 18, 1990";
$c->sentence = "Forty-one months, imposed August 20, 1990. Entered custody in approximately June 1991 and released June 21, 1994 -- about 36 months served, consistent with federal good-conduct credit.";
$c->setPartialDate("arrest_date", 1989, 7, 12);
$c->setPartialDate("sentenced_date", 1990, 8, 20);
$c->setPartialDate("incarceration_date", 1991, 6, 11);
$c->setPartialDate("release_date", 1994, 6, 21);
$c->save();
echo "  case: arrested 1989-07-12, sentenced 1990-08-20, custody 1991-06-11 -> 1994-06-21, days={$c->imprisoned_for_days} (expected 1106)\n";

// Photo path follows the regenerated slug.
if ($real->photo) {
    $newRel = "prisoners/{$real->slug}.jpg";
    $oldAbs = storage_path("app/public/{$real->photo}");
    if ($real->photo !== $newRel && is_file($oldAbs)) {
        File::copy($oldAbs, storage_path("app/public/{$newRel}"));
        $real->photo = $newRel;
        $real->save();
        echo "  photo re-pointed -> {$newRel}\n";
    }
} else {
    echo "  NOTE: no photo attached. A period portrait is said to exist -- send the URL and it can be cropped in.\n";
}

echo "  remaining cases: ".$real->cases()->count()."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
