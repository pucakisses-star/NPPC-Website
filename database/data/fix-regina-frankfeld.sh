#!/usr/bin/env bash
#
# Enrich Regina Frankfeld's record with her documented biography and the
# Maryland Smith Act case: arrested August 8, 1951; convicted 1952; two-year
# sentence and a 1,000-dollar fine (Judge Chestnut); entered sentence custody
# about January 26, 1953 and released October 5, 1954.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-regina-frankfeld.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()
    ->where("slug", "regina-frankfeld")
    ->orWhereRaw("LOWER(name) = ?", ["regina frankfeld"])
    ->first();
if (! $p) { echo "Regina Frankfeld not found.\n"; return; }

$p->description =
    "Regina Frankfeld was a Baltimore schoolteacher and Communist Party organizer prosecuted in the Maryland Smith Act case. She had already been fired from the Baltimore school system in 1948 after appearing before the school board to answer accusations of communist affiliation; she defended her beliefs, and the board declined to renew her contract and then barred Communist Party members from the school system. On August 8, 1951 she was arrested and charged under the Smith Act with advocating the violent overthrow of the United States government through her membership in the Communist Party. "
    ."She was the wife of Phillip Frankfeld, the former Maryland-D.C. Communist Party district chairman, who was arrested in the same sweep of so-called second string reds, along with Roy Wood, the former Maryland CIO leader George Meyers, Dorothy Rose Blumberg, and the attorney Maurice Braverman. Denied permission to leave Maryland, the Frankfelds rented an apartment in the Druid Hill Park neighborhood of Baltimore with Dorothy Rose Blumberg, where the Baltimore Sun photographed them being openly tailed by FBI agents. "
    ."At the 1952 trial former Communists testified against the six defendants as believers in revolution but named no specific acts; a jury nonetheless found all six guilty. Judge Chestnut fined each 1,000 dollars and imposed prison terms ranging from two years for Regina Frankfeld to five years for Phillip Frankfeld. Their appeals were quickly denied, and by early 1953 all had reported to prison. In prison Regina Frankfeld had privileges revoked after authorities alleged that her sister was passing her clandestine notes, while her husband was placed in solitary confinement for trying to recruit other inmates into the Communist Party. "
    ."The prosecution rested partly on the paid informant Mary Markward, who testified that the defendants would take up arms against the government even though she had earlier told the House Un-American Activities Committee that they never explicitly said so; the defendants were not given her FBI reports, an issue at the center of the Jencks decision on disclosure of informant reports.";
$p->gender = "Female";
$p->state = "Maryland";
$p->ideologies = ["Communism"];
$p->affiliation = ["Communist Party USA"];
$p->era = "1950s";
$p->in_custody = false;
$p->released = true;
$p->save();

$c = $p->cases()->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }
$c->charges = "Charged under the Smith Act on August 8, 1951 with advocating the violent overthrow of the United States government through membership in the Communist Party (the Maryland second string reds case).";
$c->convicted = "Convicted under the Smith Act, 1952";
$c->sentence = "Two years in prison and a 1,000-dollar fine (Judge Chestnut). Entered sentence custody about January 26, 1953 and released October 5, 1954.";
$c->setPartialDate("arrest_date", 1951, 8, 8);
$c->setPartialDate("incarceration_date", 1953, 1, 26);
$c->setPartialDate("release_date", 1954, 10, 5);
$c->save();

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Regina Frankfeld updated: arrest 1951-08-08, imprisoned_for_days={$c->imprisoned_for_days}.\n";
echo "Done.\n";
'

echo
echo "Done."
