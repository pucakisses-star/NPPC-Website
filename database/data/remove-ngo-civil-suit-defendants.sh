#!/usr/bin/env bash
#
# Remove the three Andy Ngo civil-suit defendants.
#
#   corbyn-belyea       Corbyn Belyea
#   joseph-evans        Joseph Evans (aka Sammich Overkill)
#   madison-lee-allen   Madison Lee Allen (aka Denny)
#
# None of the three was ever imprisoned. Their case rows say so in as
# many words -- "No criminal charges — civil suit by Andy Ngo alleging
# assault, battery, IIED" -- and carry no incarceration date and a zero
# imprisonment counter. What happened to them was a $100,000 CIVIL
# DEFAULT JUDGMENT in a private lawsuit, entered because they did not
# appear; the two companion defendants who did appear, Elizabeth Richter
# and John Hacker, won their jury verdicts.
#
# A money judgment in a private suit is not incarceration, and a
# defendant who never showed up to contest one is not a political
# prisoner. This is the same rule the database already applies to fines
# (the Garrett rule) and to entries with no prisoner in the story (the
# Letelier rule): the records go.
#
# The only date on each row is an arrest_date of 2019-05-01, which is
# the date of the protest named in the complaint rather than any actual
# arrest -- there was none, which is what "no criminal charges" means.
#
# THE OTHER DENNY RECORDS ARE NOT TOUCHED. Edward Denny (1930s) and
# Mack Denny (1910s) are unrelated men; this script deletes by slug, so
# it cannot reach them.
#
# Each record loses its cases first, then the record itself. A record
# with calendar entries or podcast episodes is NOT deleted -- it gets a
# warning instead, since linked content means someone has built on it by
# hand. Neither of the three has a photo, but the photo cleanup is kept
# in case one is added before this runs.
#
# Idempotent: a second run reports the records already gone. Run from
# the repo root:
#   bash database/data/remove-ngo-civil-suit-defendants.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$slugs = ["corbyn-belyea", "joseph-evans", "madison-lee-allen"];

$deleted = 0;
foreach ($slugs as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) {
        echo "  {$slug}: already gone\n";
        continue;
    }

    $calendar = DB::table("calendar_entries")->where("prisoner_id", $p->id)->count();
    $podcasts = DB::table("podcast_episodes")->where("prisoner_id", $p->id)->count();
    if ($calendar || $podcasts) {
        echo "  WARNING: {$slug} has {$calendar} calendar entr(ies) and {$podcasts} podcast episode(s) -- NOT deleted, resolve by hand\n";
        continue;
    }

    $cases = $p->cases()->count();
    $p->cases()->delete();

    if ($p->photo) {
        $shared = Prisoner::withoutGlobalScopes()
            ->where("photo", $p->photo)
            ->where("id", "!=", $p->id)
            ->exists();
        if (! $shared && Storage::disk("public")->exists($p->photo)) {
            Storage::disk("public")->delete($p->photo);
        }
    }

    echo "  deleted {$p->name}  [{$slug}]  sort {$p->sort_order}, {$cases} case(s)\n";
    $p->delete();
    $deleted++;
}

echo "\nDeleted {$deleted} record(s).\n";

$left = Prisoner::withoutGlobalScopes()
    ->where("description", "like", "%civil suit%")
    ->where("description", "like", "%Ngo%")
    ->count();
echo "Records still referencing the Ngo civil suit: {$left}  (expect 0)\n";

$total = Prisoner::withoutGlobalScopes()->count();
echo "Records now: {$total}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
