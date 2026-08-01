#!/usr/bin/env bash
#
# Jeff Hogg -- corrections.
#
#   Arrested / incarcerated  May 17, 2006  ->  May 18, 2006
#   Released                 November 14   ->  November 15, 2006  (181 days)
#   Charge                   Civil contempt of court
#   Institution              Norfolk County Jail, Dedham, Massachusetts
#                            ->  Josephine County Jail, Grants Pass, Oregon
#   Affiliation              Earth First! added
#   Description              was a truncated fragment ("Jeff Hogg was an
#                            Oregon environmental activist and nursing
#                            student") -- replaced with a full biography
#                            (added in batch 60; the description update
#                            only fires when the fragment or an empty
#                            bio is found, so a curator-edited bio is
#                            never overwritten)
#
# The wrong institution is only detached from this case, never deleted -- other
# prisoners may legitimately be held at Norfolk County Jail.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-jeff-hogg.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "jeff-hogg")->first()
    ?? Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) LIKE ?", ["%hogg%"])->first();
if (! $p) { echo "NOT FOUND: Jeff Hogg\n"; exit(1); }
echo "record: {$p->name} [{$p->slug}], ".$p->cases()->count()." case(s)\n";

$inst = Institution::firstOrCreate(
    ["name" => "Josephine County Jail"],
    ["city" => "Grants Pass", "state" => "Oregon"],
);
// Fill in the location if the institution already existed without one.
if (! $inst->city || ! $inst->state) {
    $inst->city = $inst->city ?: "Grants Pass";
    $inst->state = $inst->state ?: "Oregon";
    $inst->save();
}

$c = $p->cases()->orderBy("created_at")->first();
if (! $c) { $c = $p->cases()->make(); $c->prisoner_id = $p->id; }

$oldInst = $c->institution_id ? optional($c->institution)->name : "(none)";
$oldInc = $c->incarceration_date ?: "-";
$oldRel = $c->release_date ?: "-";

$c->charges = "Civil contempt of court — for refusing to testify before a federal grand jury.";
$c->institution_id = $inst->id;
$c->setPartialDate("arrest_date", 2006, 5, 18);
$c->setPartialDate("incarceration_date", 2006, 5, 18);
$c->setPartialDate("release_date", 2006, 11, 15);
if (! $c->convicted) { $c->convicted = "Never charged with an underlying offence — jailed for civil contempt"; }
$c->save();

$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
if (! $p->state) { $p->state = "Oregon"; }

// Affiliation: Earth First! -- added without disturbing any existing entries.
$affs = is_array($p->affiliation) ? $p->affiliation : [];
if (! in_array("Earth First!", $affs, true)) { $affs[] = "Earth First!"; }
$p->affiliation = array_values($affs);

// The stored biography was a truncated fragment. Replace it only when the
// fragment (or nothing) is found, so a later curator edit is never clobbered.
$bio = "Jeff Hogg was an Oregon environmental activist and nursing student from Eugene who became the longest-jailed grand jury resister of the Green Scare era. Subpoenaed in 2006 to the federal grand jury in Eugene investigating the Operation Backfire arson prosecutions of Earth Liberation Front and Animal Liberation Front activists, he refused to testify, and on May 18, 2006 he was jailed for civil contempt. He was held at the Josephine County Jail in Grants Pass for 181 days, until November 15, 2006, when the court concluded that further confinement would not coerce his testimony. He was never charged with any crime.";
$frag = "Jeff Hogg was an Oregon environmental activist and nursing student";
if (! $p->description || trim($p->description) === $frag) {
    $p->description = $bio;
    echo "  description: replaced the truncated fragment\n";
} elseif ($p->description !== $bio) {
    echo "  description: left alone (already differs from the known fragment)\n";
}

$p->save();

echo "  institution: {$oldInst} -> {$inst->name}, {$inst->city}, {$inst->state}\n";
echo "  incarcerated: {$oldInc} -> ".$c->incarceration_date."\n";
echo "  released:     {$oldRel} -> ".$c->release_date."\n";
echo "  charge:       {$c->charges}\n";
echo "  affiliation:  ".implode(", ", $p->affiliation)."\n";
echo "  days={$c->imprisoned_for_days} (expected 181)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
