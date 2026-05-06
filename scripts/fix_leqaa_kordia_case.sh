#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_leqaa_kordia_case.sh
#
# Updates Leqaa Kordias existing case row with the release date
# the government finally let stand on March 16, 2026, after a year
# in ICE detention and three separate court orders directing her
# release. The earlier prisoner:add was a no-op for her because
# she already existed in the database from the Airtable import,
# so the case-level fields may be partial.
#
# Source-derived facts:
#   - Detained by ICE in Newark, NJ on March 13, 2025 at her routine
#     check-in.
#   - Transferred to the Prairieland ICE Detention Center in
#     Alvarado, Texas.
#   - The administration cited an alleged F-1 student visa overstay
#     (her visa lapsed in January 2022 after she left her academic
#     program) and later alleged material support to Hamas based on
#     money transfers to family members in Gaza; an immigration
#     judge rejected the latter claim in April 2025.
#   - Three federal court orders directed her release on bond; the
#     government appealed twice. The third order, in March 2026,
#     went unappealed; she was released on March 16, 2026.
#
# Sources: Wikipedia entry on her detention; Democracy Now!;
# Mondoweiss; Columbia Daily Spectator; ABC News; The Forward.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Leqaa Kordia")->first();
if (!$p) {
    echo "ERROR: Leqaa Kordia not found\n";
    exit(1);
}
echo "Found prisoner: {$p->name} (id={$p->id})\n";

$dirty = false;
if ($p->in_custody) { $p->in_custody = false; $dirty = true; }
if (!$p->released)  { $p->released   = true;  $dirty = true; }
if ($dirty) { $p->save(); echo "Updated prisoner-level flags.\n"; }

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Prairieland ICE Detention Center"],
    ["city" => "Alvarado", "state" => "Texas"]
);

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    $case = new App\Models\PrisonerCase(["prisoner_id" => $p->id]);
    echo "No existing case; creating one.\n";
}

$case->institution_id     = $inst->id;
$case->charges            = "ICE administrative detention - the administration cited an alleged F-1 student-visa overstay (her visa had lapsed in January 2022 after she left her academic program) and at one point alleged material support to Hamas based on money transfers to family members in Gaza. An immigration judge rejected the material-support claim in April 2025. No criminal charges.";
$case->arrest_date        = "2025-03-13";
$case->incarceration_date = "2025-03-13";
$case->release_date       = "2026-03-16";
$case->sentence           = "Held in ICE administrative detention for one year - March 13, 2025 to March 16, 2026 - mostly at the Prairieland ICE Detention Center in Alvarado, Texas. Three separate federal court orders directed her release on bond; the government appealed the first two. The third went unappealed and she was released on March 16, 2026.";
$case->convicted          = "No - ICE administrative detention; no criminal conviction";
$case->save();

echo "Updated case id={$case->id}: arrest=2025-03-13 release=2026-03-16 imprisoned_for_days={$case->imprisoned_for_days}\n";
'

echo
echo "Leqaa Kordia case fix complete."
