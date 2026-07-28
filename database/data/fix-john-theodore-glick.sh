#!/usr/bin/env bash
#
# John Theodore "Ted" Glick -- expanded and corrected record.
#
# WHAT WAS WRONG
#   The record had one case, dated incarceration January 11, 1971 to release
#   December 14, 1972 -- 703 days, rendered as "Imprisoned For 1 years
#   11 months 3 days". Both dates were wrong and they were not even from the
#   same event: January 1971 is roughly when the original Harrisburg indictment
#   came down, and Glick was not taken into custody on it. He was already in
#   prison, on a different conviction, and the Harrisburg charge against him
#   was never tried at all.
#
#   The bio also said he "previously served eleven months in federal prison for
#   draft resistance". The eleven months were served on the Rochester federal
#   building conviction, not on a separate draft-refusal sentence.
#
# THE ROCHESTER CASE -- the one he actually served time for
#   September 6, 1970    with seven others, entered the federal building at
#                        Rochester, New York and ransacked federal offices and
#                        records, Selective Service material among them
#   December 1, 1970     convicted with all seven co-defendants; he represented
#                        himself
#   December 3, 1970     sentenced to concurrent terms of up to eighteen months
#   October 28, 1971     released on appeal bond -- 329 days, about eleven
#                        months
#   June 29, 1972        the Second Circuit REVERSED the conviction, holding
#                        that the trial judge had prejudicially communicated
#                        with the jury during deliberations outside his presence
#
#   He was held at the federal youth institution at Ashland, Kentucky, at FCI
#   Danbury and at the federal medical centre at Springfield, Missouri. A case
#   row holds one institution, so it carries Danbury, where he joined the
#   August 1971 hunger strike and was put in solitary; the rest is in the
#   sentence text.
#
# THE HARRISBURG CASE -- indicted, never tried, never held
#   Added to the superseding indictment on April 30, 1971 while already in
#   prison. Severed because he insisted on representing himself. The government
#   never brought the severed prosecution to trial. Recorded as its own case
#   with an INDICTED date and no custody dates, because there was none: it
#   contributes nothing to his day count, which is the point.
#
# After this the counter reads 329 days instead of 703.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-john-theodore-glick.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->whereIn("slug", ["john-theodore-glick", "ted-glick"])
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["john theodore glick", "ted glick"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: John Theodore Glick\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
foreach ($p->cases as $c) {
    echo "  case  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

// ---- the man ---------------------------------------------------------------
$p->first_name = "John";
$p->middle_name = "Theodore";
$p->last_name = "Glick";
$p->aka = "Ted Glick";
$p->gender = "Male";
$p->state = "New York";
$p->era = "1970s";
$p->ideologies = ["Anti-War", "Anti-Militarism", "Draft Resistance"];
$p->affiliation = ["Catholic Left"];
$p->setPartialDate("birthdate", 1949, 10, 9);
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->description = "John Theodore “Ted” Glick, born October 9, 1949 in Huntingdon, Pennsylvania, is an American antiwar, progressive and climate-justice activist. After leaving Grinnell College in 1969 he publicly refused induction into the United States military and became active in the Catholic Left campaign of nonviolent resistance to the Vietnam War. On September 6, 1970 he and seven others entered the federal building in Rochester, New York and ransacked federal offices and government records, Selective Service material among them, intending to disrupt the draft. Representing himself at trial, he was convicted with all seven co-defendants on December 1, 1970 and sentenced on December 3 to concurrent terms of up to eighteen months. He served roughly eleven months in federal custody, at the federal youth institution at Ashland, Kentucky, at FCI Danbury in Connecticut and at the federal facility at Springfield, Missouri; at Danbury he joined a prison hunger strike in August 1971 and was put in solitary confinement. He was released on appeal bond on October 28, 1971. While he was still imprisoned he was added, on April 30, 1971, to the superseding indictment in the Harrisburg Eight case, which alleged a conspiracy to kidnap Henry Kissinger, damage federal heating tunnels, possess explosives and destroy government records; because he insisted on representing himself his case was severed from the others, and the government never brought the severed prosecution to trial. On June 29, 1972 the United States Court of Appeals for the Second Circuit reversed the Rochester conviction, holding that the trial judge had prejudicially communicated with the jury outside Glick’s presence during its deliberations. He has continued since as a progressive organizer, writer, antiwar activist and climate-justice campaigner.";
$p->save();

// ---- the Rochester case ----------------------------------------------------
$danbury = Institution::firstOrCreate(
    ["name" => "FCI Danbury"],
    ["city" => "Danbury", "state" => "Connecticut"],
);

$rochester = $p->cases->first(fn ($c) => stripos((string) $c->charges, "Rochester") !== false)
    ?? $p->cases->first()
    ?? $p->cases()->create([]);
$rochester->institution_id = $danbury->id;
$rochester->charges = "Destruction of government records and related federal charges — the September 6, 1970 action in which Glick and seven others entered the federal building at Rochester, New York and ransacked federal offices and records, Selective Service material among them, to disrupt the draft.";
$rochester->plead = "Not guilty — he represented himself at trial";
$rochester->convicted = "Convicted with all seven co-defendants on December 1, 1970 — REVERSED by the Second Circuit on June 29, 1972, which held the trial judge had prejudicially communicated with the jury outside his presence during deliberations.";
$rochester->sentence = "Concurrent terms of up to eighteen months, imposed December 3, 1970. He served about eleven months, at the federal youth institution at Ashland, Kentucky, at FCI Danbury and at the federal facility at Springfield, Missouri; at Danbury he joined the August 1971 hunger strike and was placed in solitary confinement. Released on appeal bond on October 28, 1971, and the conviction was reversed outright the following June. The case row carries Danbury of the three facilities.";
$rochester->setPartialDate("arrest_date", 1970, 9, 6);
$rochester->setPartialDate("sentenced_date", 1970, 12, 3);
$rochester->setPartialDate("incarceration_date", 1970, 12, 3);
$rochester->setPartialDate("release_date", 1971, 10, 28);
$rochester->save();

// ---- the Harrisburg case: indicted, never tried, no custody ----------------
$harrisburg = $p->cases()->where("charges", "like", "%Harrisburg%")->first()
    ?? $p->cases()->create([]);
$harrisburg->institution_id = null;
$harrisburg->charges = "Conspiracy to kidnap Henry Kissinger, to damage federal heating tunnels, to possess explosives and to destroy government records — the Harrisburg Eight prosecution. He was added to the superseding indictment on April 30, 1971 while already serving the Rochester sentence.";
$harrisburg->indicted = "April 30, 1971 — added to the superseding indictment";
$harrisburg->plead = "Not guilty";
$harrisburg->convicted = "No — his case was severed from the other defendants because he insisted on representing himself, and the government never brought the severed prosecution to trial.";
$harrisburg->sentence = "None. No custody arose from this indictment: he was already in federal prison on the Rochester conviction when it was returned, and the severed case was never tried. No incarceration date is recorded here, so it adds nothing to his day count.";
$harrisburg->setPartialDate("arrest_date", null);
$harrisburg->setPartialDate("incarceration_date", null);
$harrisburg->setPartialDate("release_date", null);
$harrisburg->save();

// ---- receipt ---------------------------------------------------------------
$p->refresh()->load("cases.institution");
echo "\nAFTER\n";
echo "  {$p->name}  [{$p->slug}]  AKA {$p->aka}   born ".$p->formatPartialDate("birthdate")."   age {$p->age}\n";
$total = 0;
foreach ($p->cases->sortBy(fn ($c) => (string) ($c->arrest_date ?: $c->incarceration_date)) as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  case  inc=".str_pad((string) ($c->formatPartialDate("incarceration_date") ?: "-"), 14)
        ." rel=".str_pad((string) ($c->formatPartialDate("release_date") ?: "-"), 14)
        ." days=".str_pad((string) ($c->imprisoned_for_days ?? "null"), 5)
        ." ".($c->institution->name ?? "no institution")
        .($c->indicted ? "   indicted ".$c->indicted : "")."\n";
}
echo "  counter: {$total} days (was 703 -- 1 year 11 months 3 days)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
