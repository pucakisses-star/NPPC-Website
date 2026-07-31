#!/usr/bin/env bash
#
# SAMUEL GREEN -- replace the biography with the curator supplied text.
#
# Applied verbatim as given. Nothing else on the record is touched: the
# two case rows, the dates, the flags and the engraving all stay exactly
# as batch 40 left them, and the new text agrees with every one of them.
#
#   April 4, 1857 arrest ..... the text says spring 1857
#   acquittal ................ the text says acquitted, then tried again
#   May 14, 1857 conviction .. the text says convicted in May 1857
#   ten-year sentence ........ the text says ten years
#   April 21, 1862 release ... the text gives the same day
#   1,799 days stored ........ the text says nearly five years
#
# It also keeps the batch 40 correction intact. The trial was for the
# documents found in his house, not for the Dover Eight escape itself --
# the state attorney concluded there was not enough evidence to charge
# him with that, and he was never tried for it. The new text is phrased
# so the arrest FOLLOWS the escape without saying he was tried for it.
#
# WHAT THE SHORTER TEXT DROPS, listed so it is a decision and not an
# accident. All of it is recoverable from git history and from the two
# case rows, which are unchanged:
#
#   - his dates, circa 1802 to February 28, 1877
#   - that he was born enslaved at East New Market, and later freed his
#     wife Catherine "Kitty" Green
#   - that his house was a stop for Harriet Tubman
#   - that he was a licensed lay exhorter rather than an ordained
#     minister, and was called Reverend historically
#   - the acquittal date of April 25 and the judge reasoning that
#     documents useful for escape fell outside the statute
#   - that ten years was the STATUTORY MINIMUM, not a harsh sentence
#   - Maryland Penitentiary prisoner number 5146, entered May 18, 1857
#
# The prose carries ordinary apostrophes, so it lives in
# database/data/fixes/samuel-green-bio.json rather than inline.
#
# Guarded and idempotent: the text is compared before writing, so a
# second run reports it as already correct and writes nothing.
#
# Run from the repo root:
#   bash database/data/fix-samuel-green-bio.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/samuel-green-bio.json")), true);

if (! $payload || empty($payload["slug"]) || empty($payload["description"])) {
    echo "Could not read the payload — nothing changed.\n";
    return;
}

$p = Prisoner::withoutGlobalScopes()->where("slug", $payload["slug"])->first();

if (! $p) {
    echo "NOT FOUND: ", $payload["slug"], "\n";
    return;
}

if ($p->description === $payload["description"]) {
    echo "  ", $p->slug, " — biography already correct, nothing written.\n";
    return;
}

$before = strlen((string) $p->description);
$p->description = $payload["description"];
$p->save();

echo "  ", $p->slug, " — biography replaced (", $before, " chars -> ", strlen($p->description), ").\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
