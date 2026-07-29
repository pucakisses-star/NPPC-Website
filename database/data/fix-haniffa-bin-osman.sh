#!/usr/bin/env bash
#
# HANIFFA BIN OSMAN -- his record described him as his co-defendant.
#
# Both Osman and Erick Wotulo carried the same descriptor, "retired
# Indonesian Marine Corps general". It belongs to Wotulo alone. Osman
# was a Singaporean civilian, an arms broker working for a commission,
# and not a military officer of any country.
#
# Sources, all U.S. Attorney for the District of Maryland releases plus
# Baltimore Sun coverage of the same 2006 sting:
#
#   - the charging release names the six defendants and identifies
#     Wotulo, and only Wotulo, as a retired Indonesian Marine Corps
#     general;
#   - the plea and sentencing releases for Osman are headlined
#     "Singapore Man", and describe him brokering between weapons
#     suppliers and the Liberation Tigers of Tamil Eelam;
#   - Osman flew to Baltimore in July 2006 to test-fire machine guns
#     and to Guam on September 26, 2006 to inspect machine guns,
#     sniper rifles, ammunition and two surface-to-air missiles,
#     discussing the commission he stood to earn. The buyers were
#     undercover agents.
#   - He pleaded guilty on April 5, 2007 and was sentenced to 37
#     months plus three years of supervised release. Wotulo got 30
#     months and deportation.
#
# The rewritten description ends by naming Wotulo as the general in the
# same case, so the two records cannot quietly swap identities again.
#
# The floating age ("57") comes out of the prose. No birth year is
# invented from it -- research turned up no stated date of birth for
# him, so the birthdate field stays empty, which is the rule here.
#
# GUARDED AND IDEMPOTENT: the rewrite only fires while the false
# descriptor is still present, so a second run reports nothing to do.
# Wotulo is checked but not modified -- his descriptor is correct.
#
# Run from the repo root:
#   bash database/data/fix-haniffa-bin-osman.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "haniffa-bin-osman")->first();
if (! $p) {
    echo "NOT FOUND: haniffa-bin-osman\n";
    exit(1);
}

$false = "retired Indonesian Marine Corps general";

if (! str_contains((string) $p->description, $false)) {
    echo "Already corrected — the co-defendant descriptor is gone. Nothing to do.\n";
} else {
    echo "Before: ", $p->description, "\n\n";

    $p->description = "Haniffa Bin Osman was a Singaporean arms broker who acted as a middleman between weapons suppliers and the Liberation Tigers of Tamil Eelam. He flew to Baltimore in July 2006 to test-fire machine guns, and on September 26, 2006 travelled to Guam to inspect machine guns, sniper rifles, ammunition and two surface-to-air missiles intended for the Tamil Tigers, discussing the commission he stood to earn on the sale; he wired a \$752,000 down payment for the weapons. The buyers were undercover federal agents. He pleaded guilty on April 5, 2007 to conspiracy to provide material support to a designated foreign terrorist organization and to money laundering, and was sentenced to 37 months in federal prison followed by three years of supervised release. He was not a member of the LTTE and held no military rank: the retired Indonesian Marine Corps general in the same case was his co-defendant Erick Wotulo, who was sentenced to 30 months and deportation.";

    $p->save();
    echo "After:  ", $p->description, "\n";
}

$w = Prisoner::withoutGlobalScopes()->where("slug", "erick-wotulo")->first();
if ($w) {
    echo "\nWotulo (unchanged, descriptor is his): ", $w->description, "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
