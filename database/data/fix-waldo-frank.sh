#!/usr/bin/env bash
#
# Sharpen the Waldo Frank record with the precise Terre Haute incarceration:
# arrested September 30, 1936 on arrival by train with Earl Browder's campaign
# party, held ~25 hours on a vagrancy charge, released October 1, 1936; all
# charges dropped, no trial/conviction, no bail.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-waldo-frank.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;

$p = Prisoner::withoutGlobalScopes()
    ->where("slug", "waldo-frank")
    ->orWhereRaw("LOWER(name) = ?", ["waldo frank"])
    ->first();
if (! $p) { echo "Waldo Frank not found.\n"; return; }

$p->description =
    "Waldo Frank (1889-1967) was an American novelist and essayist and the first chairman of the League of American Writers. He accompanied the Communist presidential candidate Earl Browder to Terre Haute, Indiana as a writer and correspondent during the Browder Midwestern campaign tour. On September 30, 1936, immediately after the party arrived by train, police arrested Browder, Frank, Seymour Waldman, Charles Stadtfeld and Andrew Remes on a vagrancy charge, in an effort to keep the campaign from speaking. Frank was held about 25 hours in the Terre Haute jail and released on October 1, 1936; all charges were dropped, with no trial or conviction and no bail required. The arrests of prominent literary and political figures drew national free-speech protest.";
$p->save();

$inst = Institution::firstOrCreate(["name" => "Terre Haute Jail"], ["city" => "Terre Haute", "state" => "Indiana"]);

$c = $p->cases()->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
$c->charges = "Arrested for vagrancy on September 30, 1936, immediately after arriving by train in Terre Haute, Indiana with the Earl Browder campaign party. All charges were dropped.";
$c->convicted = "Arrested September 30, 1936; all charges dropped (no trial or conviction)";
$c->sentence = "Held about 25 hours in the Terre Haute jail; released October 1, 1936. No bail required.";
$c->institution_id = $inst->id;
$c->setPartialDate("arrest_date", 1936, 9, 30);
$c->setPartialDate("incarceration_date", 1936, 9, 30);
$c->setPartialDate("release_date", 1936, 10, 1);
$c->save();

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Waldo Frank updated: 1936-09-30 to 1936-10-01, days={$c->imprisoned_for_days}.\n";
echo "Done.\n";
'

echo
echo "Done."
