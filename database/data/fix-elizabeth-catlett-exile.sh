#!/usr/bin/env bash
#
# Elizabeth Catlett was EXILED, not imprisoned for 12 years. Her record stored
# the 1959-1971 barring from the United States as a ~12-year incarceration,
# which shows as "IMPRISONED FOR 12 YEARS". This reclassifies that span as
# exile (in_exile_since 1959 -> end_of_exile 1971) and clears the bogus
# incarceration dates, leaving only the brief 1959 arrest in Mexico as an
# (undated-duration) detention. Also repairs apostrophes lost from her bio.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-elizabeth-catlett-exile.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()
    ->where("slug", "elizabeth-catlett")
    ->orWhereRaw("LOWER(name) = ?", ["elizabeth catlett"])
    ->first();
if (! $p) { echo "Elizabeth Catlett not found.\n"; return; }

// Exile, not current custody. She returned/could visit after 1971 and died in
// Mexico in 2012, so she is not CURRENTLY in exile.
$p->in_custody = false;
$p->currently_in_exile = false;
$p->in_exile = true;
$p->released = true;

// Repair apostrophes stripped from the bio (chr(39) = single quote).
$q = chr(39);
if ($p->description) {
    $p->description = str_replace(
        ["OHiggins", "governments crackdown", "mothers funeral"],
        ["O{$q}Higgins", "government{$q}s crackdown", "mother{$q}s funeral"],
        $p->description
    );
}
$p->save();

$c = $p->cases()->first();
if ($c) {
    // Move the 12-year span from imprisonment to exile.
    $c->incarceration_date = null;   // clear the bogus 12-year incarceration
    $c->release_date = null;
    $c->setPartialDate("arrest_date", 1959, null, null);        // brief 1959 arrest
    $c->setPartialDate("in_exile_since", 1959, null, null);
    $c->setPartialDate("end_of_exile", 1971, null, null);
    $c->charges = "Branded an undesirable alien and barred from returning to the United States during the McCarthy era; briefly arrested and held by Mexican authorities in 1959 during the crackdown on the railway workers strike and the Mexican Communist Party.";
    $c->convicted = "Barred from the United States; briefly detained in Mexico, 1959";
    $c->sentence = "Barred from setting foot in the United States until 1971, when the State Department lifted the prohibition. She was prevented from attending the funeral of her mother and became a Mexican citizen in 1962.";
    $c->save();
    echo "Catlett: imprisoned_for_days={$c->imprisoned_for_days}, in_exile_for_days={$c->in_exile_for_days}\n";
} else {
    echo "Catlett has no case row.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
