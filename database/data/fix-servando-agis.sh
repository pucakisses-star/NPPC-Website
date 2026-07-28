#!/usr/bin/env bash
#
# Servando F. Agis -- corrected name and expanded biography.
#
#   Name   "Sirvando T. Agis"  ->  "Servando F. Agis"
#
# The record was one of the placeholder entries built from a roster: it said
# only that he is "named in contemporary rosters of Mexican revolutionaries
# associated with the Partido Liberal Mexicano" and that the roster does not
# establish individual dates. The new text gives him an actual life.
#
# Also set from that text, since the record had neither:
#   State         Texas -- Bridgeport and San Antonio
#   Affiliation   Partido Liberal Mexicano
#   Gender        Male
#
# NOT CHANGED. The case still says the roster records months in jail without
# establishing individual dates, because that is still true -- the new
# biography ends on the same point. No arrest, incarceration or release date is
# invented, so he continues to contribute nothing to the day totals.
#
# The era stays at 1900s. His described activity runs from about 1908 to the
# January 1911 appointment, straddling the boundary, and nothing in the new
# text settles it.
#
# The slug follows the corrected spelling, so the public URL moves from
# /prisoner/sirvando-t-agis to /prisoner/servando-f-agis. That is the point --
# the old one embeds the misspelling -- but KEEP_SLUG=1 holds the existing URL.
# The old name is kept as an AKA either way, so a search for "Sirvando" still
# finds him.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-servando-agis.sh
#   KEEP_SLUG=1 bash database/data/fix-servando-agis.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

KEEP_SLUG="${KEEP_SLUG:-0}" php artisan tinker --execute='
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["sirvando-t-agis", "servando-f-agis"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["sirvando t. agis", "servando f. agis"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Sirvando T. Agis / Servando F. Agis\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();
$oldName = $p->name;
$oldSlug = $p->slug;

$p->name = "Servando F. Agis";
$p->first_name = "Servando";
$p->middle_name = "F.";
$p->last_name = "Agis";
if (strcasecmp($oldName, "Servando F. Agis") !== 0) { $p->aka = $oldName; }
$p->gender = "Male";
$p->state = "Texas";
$p->affiliation = ["Partido Liberal Mexicano"];
$p->description = "Servando F. Agis was a Mexican journalist and organizer associated with the Partido Liberal Mexicano’s anti-Díaz exile movement. He corresponded with prominent Mexican Liberal activists before traveling to Texas around 1908. Agis participated in the Club Liberal Juárez y Lerdo in Bridgeport and later organized a Liberal group in San Antonio, where the Mexican consulate placed him under surveillance. He contributed articles to Regeneración in 1910 and was appointed a general delegate of the PLM organizing junta in January 1911. He was jailed or prosecuted under U.S. neutrality laws.";
$p->save();   // renaming regenerates the slug

if (getenv("KEEP_SLUG") === "1" && $p->slug !== $oldSlug) {
    $p->slug = $oldSlug;
    $p->save();
    echo "slug held at {$oldSlug} (KEEP_SLUG=1)\n";
}

echo "{$oldName} [{$oldSlug}]  ->  {$p->name} [{$p->slug}]\n";
echo "  aka:          ".($p->aka ?: "-")."\n";
echo "  state:        {$p->state}\n";
echo "  affiliation:  ".implode(", ", $p->affiliation)."\n";
echo "  ideologies:   ".implode(", ", $p->ideologies ?: [])."   (unchanged)\n";
echo "  era:          {$p->era}   (unchanged -- his activity straddles 1908 to 1911)\n";
foreach ($p->cases as $c) {
    echo "  case (unchanged): days=".($c->imprisoned_for_days ?? "null")
        ."  ".substr((string) $c->sentence, 0, 70)."...\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
