#!/usr/bin/env bash
#
# Record Martin Luther King Jr.'s fourteen documented jail/custody episodes
# (site owner's research) as his complete set of cases. This also finishes the
# record cleanup: it sets his death date (April 4, 1968), clears the in-custody
# flag and any contaminated address/inmate/coordinates, and detaches the bogus
# Colorado (Eric Brandt / Trinidad) case before rebuilding his cases.
#
# The March 22, 1956 boycott conviction is folded into episode 2 (the fine was
# suspended pending appeal, so it was not a separate jailing); the May 4, 1960
# driver-license citation was not a documented jail stay and is omitted.
#
# Idempotent (his cases are rebuilt each run). Run from the repo root:
#   bash database/data/set-mlk-custody-episodes.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$mlk = \App\Models\Prisoner::withoutGlobalScopes()->where("slug","martin-luther-king-jr")->first();
if (! $mlk) { $mlk = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) IN (?,?)", ["martin luther king jr.","martin luther king"])->first(); }
if (! $mlk) { echo "Martin Luther King Jr. not found.\n"; return; }

// Record cleanup: he died April 4, 1968 and was not in custody.
if (empty($mlk->death_date)) { $mlk->death_date = "1968-04-04"; }
$mlk->in_custody = false;
$mlk->released = true;
$contam = function ($v) { return $v !== null && $v !== "" && preg_match("/brandt|trinidad|191131|model,?\\s*co|us-?350/i", (string) $v); };
if ($contam($mlk->address) || $contam($mlk->inmate_number)) { $mlk->address = null; $mlk->inmate_number = null; $mlk->lat = null; $mlk->lng = null; echo "Cleared contaminated address/inmate/coords.\n"; }
$mlk->save();

// Preserve the mislinked Colorado case if a real Eric Brandt record exists; else it is dropped in the rebuild.
$brandt = \App\Models\Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["eric brandt"])->first();
if ($brandt && $brandt->id !== $mlk->id) {
    foreach ($mlk->cases()->get() as $c) {
        $inst = $c->institution;
        if (stripos((string) $c->charges, "civil disorder") !== false || ($inst && (stripos((string) $inst->name, "trinidad") !== false || strcasecmp((string) $inst->state, "Colorado") === 0))) {
            $c->prisoner_id = $brandt->id; $c->save(); echo "Reassigned mislinked Colorado case to Eric Brandt.\n";
        }
    }
}

$mlk->cases()->delete();

// inst, city, state, charges, convicted|null, note, inc[y,m,d], rel[y,m,d]
$episodes = [
  ["Montgomery City Jail","Montgomery","Alabama","Speeding — driving 30 mph in a 25 mph zone during the bus boycott",null,
   "Arrested during the Montgomery bus boycott; spent part of the day in the Montgomery City Jail and was released. The precise hour and release terms are not in the surviving court summary.",[1956,1,26],[1956,1,26]],
  ["Montgomery County Jail","Montgomery","Alabama","Alabama anti-boycott law — for leading the Montgomery bus boycott","Convicted March 22, 1956; \$500 fine or 386 days, jail sentence suspended pending appeal",
   "Surrendered after being indicted with the boycott leaders, was fingerprinted and photographed, and was released on bond the same day (February 23, 1956).",[1956,2,23],[1956,2,23]],
  ["Montgomery City Jail","Montgomery","Alabama","Loitering — while attempting to attend an arraignment over an assault on Ralph Abernathy",null,
   "Arrested outside Recorders Court in Montgomery and released a short time later on \$100 bond.",[1958,9,3],[1958,9,3]],
  ["Montgomery City Jail","Montgomery","Alabama","Disobeying a police officer","Convicted; fined \$14",
   "Chose 14 days in jail rather than pay the fine, but Police Commissioner Clyde Sellers paid it and King was released almost immediately.",[1958,9,5],[1958,9,5]],
  ["Fulton County Jail","Atlanta","Georgia","Perjury on Alabama state tax returns (held on an Alabama warrant)",null,
   "Taken into custody at the Fulton County courthouse in Atlanta, arraigned, and released the same day on \$2,000 bond.",[1960,2,17],[1960,2,17]],
  ["Montgomery County Jail","Montgomery","Alabama","Alabama tax-perjury case (formal surrender)",null,
   "Formally surrendered to Alabama authorities in the tax-perjury case and was released the same day on \$4,000 bail.",[1960,2,29],[1960,2,29]],
  ["Georgia State Prison","Reidsville","Georgia","Sit-in at the Rich department store, Atlanta — held as a probation violation from an earlier driver-license case","Sentenced October 25, 1960 to four months hard labor",
   "After the other demonstrators were released, King was held on the probation-violation theory, transferred to the Georgia State Prison at Reidsville before dawn on October 26, and freed October 27 on \$2,000 bond.",[1960,10,19],[1960,10,27]],
  ["Sumter County Jail","Americus","Georgia","Parading without a permit, obstructing traffic, and congregating on the sidewalk — Albany prayer march",null,
   "Arrested after leading a prayer march to Albany City Hall and held at the Sumter County Jail in Americus; negotiations produced the release of King and the other demonstrators two days later.",[1961,12,16],[1961,12,18]],
  ["Albany City Jail","Albany","Georgia","Convicted for the December 1961 Albany demonstration","Fine or 45 days; chose jail",
   "Chose jail over the fine, but an unidentified person paid it, causing his release after two days.",[1962,7,10],[1962,7,12]],
  ["Albany City Jail","Albany","Georgia","Prayer vigil, Albany","Suspended 60-day sentence and \$200 fine (August 10, 1962)",
   "Arrested during a prayer vigil and held about two weeks; on August 10 he and Ralph Abernathy received suspended 60-day sentences and \$200 fines and were released.",[1962,7,27],[1962,8,10]],
  ["Birmingham City Jail","Birmingham","Alabama","Defying an injunction against demonstrations — the Good Friday march",null,
   "Arrested with Ralph Abernathy, initially held in solitary confinement, and wrote the Letter from Birmingham Jail. Released April 20, 1963 (one Stanford chronology lists April 19, apparently a bond-arrangement or one-day dating discrepancy).",[1963,4,12],[1963,4,20]],
  ["St. Johns County Jail","St. Augustine","Florida","Seeking service at the segregated Monson Motor Lodge restaurant",null,
   "Arrested in St. Augustine, spent one night in the St. Johns County Jail, and was released the following day.",[1964,6,11],[1964,6,12]],
  ["Selma City Jail","Selma","Alabama","Parading without a permit — voting-rights march toward the Dallas County courthouse",null,
   "Arrested with hundreds of voting-rights demonstrators in Selma; contemporary reports and photographs document his release on February 5, 1965.",[1965,2,1],[1965,2,5]],
  ["Jefferson County Jail (Birmingham)","Birmingham","Alabama","Criminal contempt — for the 1963 Birmingham marches in defiance of the anti-demonstration injunction (Walker v. City of Birmingham)","Yes — affirmed by the U.S. Supreme Court, 5-4 (1967)",
   "King and Abernathy returned to serve five-day contempt sentences after their Supreme Court appeal failed; admitted October 30, 1967. The five-day term is confirmed; the November 4 release is calculated from the sentence (no original discharge record located).",[1967,10,30],[1967,11,4]],
];

$n = 0;
foreach ($episodes as $e) {
    [$inst,$city,$state,$charges,$conv,$note,$inc,$rel] = $e;
    $institution = \App\Models\Institution::firstOrCreate(["name"=>$inst], ["city"=>$city,"state"=>$state]);
    $c = new \App\Models\PrisonerCase();
    $c->prisoner_id = $mlk->id;
    $c->institution_id = $institution->id;
    $c->charges = $charges;
    if ($conv !== null) { $c->convicted = $conv; }
    $c->sentence = $note;
    $c->setPartialDate("arrest_date", $inc[0], $inc[1], $inc[2]);
    $c->setPartialDate("incarceration_date", $inc[0], $inc[1], $inc[2]);
    $c->setPartialDate("release_date", $rel[0], $rel[1], $rel[2]);
    $c->save();
    $n++;
    echo str_pad($n,3).$c->partialDateIso("incarceration_date")." -> ".$c->partialDateIso("release_date")." (".$c->imprisoned_for_days." d)  {$inst}\n";
}

echo "\nRecorded {$n} custody episodes for Martin Luther King Jr.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Martin Luther King Jr. custody episodes recorded."
