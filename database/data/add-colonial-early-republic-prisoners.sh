#!/usr/bin/env bash
#
# Add named colonial / early-republic political prisoners that were missing from
# the database, with documented dates (year/month precision where the exact day
# is uncertain). Four groups:
#   * Fort Randolph diplomatic hostages (1777) -- Cornstalk, his son Elinipsico
#     and Red Hawk, detained as hostages and murdered in custody on 1777-11-10.
#   * North Carolina Regulators (1771) -- Robert Messer, Robert Matear, James
#     Pugh, hanged at Hillsborough on 1771-06-19 after the Battle of Alamance.
#   * Shays Rebellion leaders (1787) -- six men convicted of high treason on
#     1787-04-22, sentenced to death, later reprieved/pardoned (~1788).
#   * Whiskey Rebellion prisoners (1794) -- the twenty men marched in chains to
#     Philadelphia in late 1794 and held into 1795.
#   * St. Augustine detainees (1780-1781) -- Gadsden, Heyward, Middleton,
#     Rutledge, civilian leaders exiled after the fall of Charleston (POW/parole
#     borderline; noted in each bio).
#
# Idempotent: a prisoner whose name already exists is skipped and reported (so a
# same-name existing record is never silently duplicated or merged). Run from the
# repo root:
#   bash database/data/add-colonial-early-republic-prisoners.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$mkInst = function (?string $name, ?string $city, ?string $state) {
    if (! $name) { return null; }
    return Institution::firstOrCreate(["name" => $name], ["city" => $city, "state" => $state]);
};

// Each record: name, first, last, race, gender, state, era, desc,
// fate ("died"|"released"), death (Y-M-D for died), inst [name,city,state]|null,
// charges, sentence, sentenced (Y-M-D|null), inc [y,m,d], end [y,m,d] (released).
$R = [];

// --- Fort Randolph hostages (1777) ---
$fr = ["Fort Randolph", "Point Pleasant", "West Virginia"];
$R[] = ["Cornstalk","Cornstalk",null,"Native American","Male","West Virginia","American Revolution",
  "Hokoleskwa, known as Cornstalk, was a leader of the Shawnee. In October 1777 he came to Fort Randolph, at present-day Point Pleasant, to warn American officers that war was likely. Captain Matthew Arbuckle detained him and his companions as hostages. Cornstalk, his son Elinipsico, the Delaware leader Red Hawk and a fourth captive were murdered by militiamen on November 10, 1777.",
  "died","1777-11-10",$fr,"Held as a diplomatic hostage","Murdered by militiamen at Fort Randolph, November 10, 1777",null,[1777,10,null],null];
$R[] = ["Elinipsico","Elinipsico",null,"Native American","Male","West Virginia","American Revolution",
  "Elinipsico was the son of the Shawnee leader Cornstalk. He came to Fort Randolph seeking his father, who was being held hostage, and was murdered together with him by militiamen on November 10, 1777.",
  "died","1777-11-10",$fr,"Held as a diplomatic hostage","Murdered by militiamen at Fort Randolph, November 10, 1777",null,[1777,11,9],null];
$R[] = ["Red Hawk","Red","Hawk","Native American","Male","West Virginia","American Revolution",
  "Red Hawk was a Delaware leader who accompanied Cornstalk to Fort Randolph in October 1777 to warn American officers of the likelihood of war. He was detained as a hostage and murdered by militiamen on November 10, 1777.",
  "died","1777-11-10",$fr,"Held as a diplomatic hostage","Murdered by militiamen at Fort Randolph, November 10, 1777",null,[1777,10,null],null];

// --- North Carolina Regulators (1771) ---
$hb = ["Hillsborough Jail", "Hillsborough", "North Carolina"];
foreach ([["Robert Messer","Robert","Messer","Captain Robert Messer"],
          ["Robert Matear","Robert","Matear","Robert Matear"],
          ["James Pugh","James","Pugh","James Pugh"]] as $m) {
    $R[] = [$m[0],$m[1],$m[2],"White","Male","North Carolina","Colonial America",
      $m[3]." was one of the North Carolina Regulators. After the Battle of Alamance in May 1771 he was captured and tried for treason at Hillsborough between June 15 and 19, 1771. He was one of six men hanged on June 19, 1771. His exact date of capture is uncertain.",
      "died","1771-06-19",$hb,"Treason (Regulator movement)","Convicted at the Hillsborough treason trials; hanged June 19, 1771",null,[1771,5,null],null];
}

// --- Shays Rebellion leaders (1787) ---
foreach ([["Henry McCulloch","Henry","McCulloch"],["Jason Parmenter","Jason","Parmenter"],
          ["Daniel Ludington","Daniel","Ludington"],["Alpheus Colton","Alpheus","Colton"],
          ["James White","James","White"],["John Wheeler","John","Wheeler"]] as $m) {
    $R[] = [$m[0],$m[1],$m[2],"White","Male","Massachusetts","Early Republic",
      $m[0]." was one of the leaders of Shays Rebellion in Massachusetts. He was convicted of high treason on April 22, 1787, and sentenced to death, but was later reprieved and pardoned. Exact admission and release dates are uncertain.",
      "released",null,null,"High treason","Convicted of high treason April 22, 1787; sentenced to death, later reprieved and pardoned","1787-04-22",[1787,2,null],[1788,null,null]];
}

// --- Whiskey Rebellion prisoners (1794) ---
$wj = ["Walnut Street Jail", "Philadelphia", "Pennsylvania"];
$whiskey = [
  ["John Hamilton","John","Hamilton","Colonel John Hamilton"],
  ["William Crawford","William","Crawford","Colonel William Crawford"],
  ["John Powers","John","Powers","Major John Powers"],
  ["John Corbly","John","Corbly","Reverend John Corbly"],
  ["Thomas Sedgwick","Thomas","Sedgwick","Thomas Sedgwick"],
  ["James Kerr","James","Kerr","James Kerr"],
  ["John Laughery","John","Laughery","John Laughery"],
  ["David Lock","David","Lock","David Lock"],
  ["John Munn","John","Munn","John Munn"],
  ["William Porter","William","Porter","William Porter"],
  ["John Flannigin","John","Flannigin","John Flannigin"],
  ["John Crawford","John","Crawford","John Crawford, son of Colonel Crawford"],
  ["John Gaston","John","Gaston","John Gaston"],
  ["John Husy","John","Husy","John Husy"],
  ["John McGill","John","McGill","John McGill"],
  ["Robert Martin","Robert","Martin","Robert Martin"],
  ["Nathaniel Martin","Nathaniel","Martin","Nathaniel Martin"],
  ["David McComb","David","McComb","David McComb"],
  ["James Robinson","James","Robinson","James Robinson"],
  ["William Johnson","William","Johnson","William Johnson"],
];
foreach ($whiskey as $m) {
    $R[] = [$m[0],$m[1],$m[2],"White","Male","Pennsylvania","Early Republic",
      $m[3]." was one of twenty men arrested during the Whiskey Rebellion in western Pennsylvania and marched in chains to Philadelphia in late 1794, reaching the city around Christmas. He was held awaiting the federal treason proceedings and released in 1795.",
      "released",null,$wj,"High treason (Whiskey Rebellion)","Marched to Philadelphia in chains in late 1794 and held for the 1795 federal treason proceedings; released 1795",null,[1794,12,null],[1795,null,null]];
}

// --- St. Augustine detainees (1780-1781) ---
$sa = ["Castillo de San Marcos", "St. Augustine", "Florida"];
$R[] = ["Christopher Gadsden","Christopher","Gadsden","White","Male","South Carolina","American Revolution",
  "Christopher Gadsden, a South Carolina revolutionary leader, was seized after the fall of Charleston in 1780 and exiled to St. Augustine. When he refused parole he was held for forty-two weeks in solitary confinement in the Castillo de San Marcos, until he was exchanged in July 1781. British authorities treated him as a prisoner of war, but he was selected for political removal.",
  "released",null,$sa,"Political detention after the fall of Charleston","Held about forty-two weeks in solitary confinement, exchanged July 1781",null,[1780,9,null],[1781,7,null]];
$R[] = ["Thomas Heyward Jr.","Thomas","Heyward","White","Male","South Carolina","American Revolution",
  "Thomas Heyward Jr., a South Carolina signer of the Declaration of Independence, was seized after the fall of Charleston in 1780 and held at St. Augustine for about eleven months, until the exchange of July 1781. British authorities treated him as a prisoner of war, but he was selected for political removal.",
  "released",null,$sa,"Political detention after the fall of Charleston","Held about eleven months at St. Augustine, exchanged July 1781",null,[1780,9,null],[1781,7,null]];
$R[] = ["Arthur Middleton","Arthur","Middleton","White","Male","South Carolina","American Revolution",
  "Arthur Middleton, a South Carolina signer of the Declaration of Independence, was seized after the fall of Charleston in 1780 and held at St. Augustine until the exchange of July 1781. British authorities treated him as a prisoner of war, but he was selected for political removal.",
  "released",null,$sa,"Political detention after the fall of Charleston","Held at St. Augustine until the exchange of July 1781",null,[1780,9,null],[1781,7,null]];
$R[] = ["Edward Rutledge","Edward","Rutledge","White","Male","South Carolina","American Revolution",
  "Edward Rutledge, a South Carolina signer of the Declaration of Independence, was seized after the fall of Charleston in 1780 and held at St. Augustine until the exchange of July 1781. British authorities treated him as a prisoner of war, but he was selected for political removal.",
  "released",null,$sa,"Political detention after the fall of Charleston","Held at St. Augustine until the exchange of July 1781",null,[1780,9,null],[1781,7,null]];

$created = 0; $skipped = 0; $collisions = [];
foreach ($R as $r) {
    [$name,$first,$last,$race,$gender,$state,$era,$desc,$fate,$death,$inst,$charges,$sentence,$sentenced,$inc,$end] = $r;

    $exists = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();
    if ($exists) {
        $skipped++;
        $collisions[] = "  ".$name." (slug ".$exists->slug.")";
        continue;
    }

    $p = new Prisoner();
    $p->name = $name; $p->first_name = $first; $p->last_name = $last;
    $p->description = $desc; $p->race = $race; $p->gender = $gender;
    $p->state = $state; $p->era = $era;
    $p->in_custody = false; $p->awaiting_trial = false;
    if ($fate === "died") { $p->death_date = $death; $p->released = false; }
    else { $p->released = true; }
    $p->save();

    $institution = $inst ? $mkInst($inst[0], $inst[1], $inst[2]) : null;

    $c = new PrisonerCase();
    $c->prisoner_id = $p->id;
    if ($institution) { $c->institution_id = $institution->id; }
    $c->charges = $charges;
    $c->sentence = $sentence;
    if ($sentenced) { $sp = explode("-", $sentenced); $c->setPartialDate("sentenced_date", (int) $sp[0], (int) ($sp[1] ?? 0) ?: null, (int) ($sp[2] ?? 0) ?: null); }
    $c->setPartialDate("incarceration_date", $inc[0], $inc[1], $inc[2]);
    if ($fate === "died") {
        $dp = explode("-", $death);
        $c->setPartialDate("death_in_custody_date", (int) $dp[0], (int) $dp[1], (int) $dp[2]);
    } else {
        $c->setPartialDate("release_date", $end[0], $end[1], $end[2]);
    }
    $c->save();

    $created++;
    $len = $c->imprisoned_for_days;
    echo "created ".$p->slug." | ".$p->name." | ".($c->partialDateIso("incarceration_date") ?? "?")." -> ".($c->partialDateIso("release_date") ?? "?")." (".($len !== null ? $len." d" : "n/a").")\n";
}

echo "\n=== Summary ===\n";
echo "Created: {$created}\n";
echo "Skipped (name already exists -- verify these are not the same person): {$skipped}\n";
if ($collisions) { echo implode("\n", $collisions)."\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Colonial / early-republic prisoners added."
