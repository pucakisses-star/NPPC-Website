#!/usr/bin/env bash
#
# Verified enrichment pass over the colonial / early-republic and Baptist /
# 1741 batches, applying a round of sourced corrections and life dates:
#
#   * Life dates (birth / death) for the men now documented, at year, month or
#     day precision -- never a defaulted day the source does not support.
#   * Shays Rebellion: conviction date corrected from April 22 to April 9, 1787
#     for the four men confirmed by the Massachusetts Archives verdict
#     (Parmenter, Ludington, White, Colton). McCulloch and Wheeler are left at
#     the earlier date pending confirmation.
#   * Whiskey Rebellion identity fixes: William Porter was Captain Robert Porter
#     (acquitted May 18, 1795); John Flannigin was John Flanagan; David McComb
#     was probably Thomas McComb. (The source roster script is updated to match.)
#   * NC Regulators: James Pugh identification flagged as disputed.
#   * Baptist ministers: documented durations added for Shackelford (Essex jail,
#     ~8 days) and Tinsley (~4 months 16 days); three well-documented jailed
#     ministers added (Lewis Craig, Elijah Craig, James Ireland).
#   * 1741 New York conspiracy: executed defendants Cook, Albany and Curacao
#     Dick added with their reported execution dates; Captain Jack added as an
#     imprisoned informer whose final fate is unrecorded.
#   * St. Augustine four: authenticated portraits attached.
#
# Run AFTER add-colonial-early-republic-prisoners.sh and
# add-baptists-and-1741-roster.sh. Idempotent: life dates are only set on empty
# fields, notes are appended once, renamed/added records are skipped if present.
# Run from the repo root:
#   bash database/data/enrich-colonial-verified.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

# --- stage the St. Augustine portraits into the public disk (version-controlled
#     source lives in database/data/audit-photos) ---
mkdir -p storage/app/public/prisoners
cp -n database/data/audit-photos/christopher-gadsden.jpg storage/app/public/prisoners/christopher-gadsden.jpg 2>/dev/null || true
cp -n database/data/audit-photos/thomas-heyward.jpg       storage/app/public/prisoners/thomas-heyward-jr.jpg 2>/dev/null || true
cp -n database/data/audit-photos/arthur-middleton.jpg     storage/app/public/prisoners/arthur-middleton.jpg 2>/dev/null || true
cp -n database/data/audit-photos/edward-rutledge.jpg      storage/app/public/prisoners/edward-rutledge.jpg 2>/dev/null || true

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$find = function (string $name) {
    return Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();
};
$parts = function (?string $iso): ?array {
    if (! $iso) { return null; }
    $l = strlen($iso); $y = (int) substr($iso, 0, 4);
    if ($l === 4) { return [$y, null, null]; }
    if ($l === 7) { return [$y, (int) substr($iso, 5, 2), null]; }
    return [$y, (int) substr($iso, 5, 2), (int) substr($iso, 8, 2)];
};
$mkInst = function (string $n, ?string $c, ?string $s) {
    return Institution::firstOrCreate(["name" => $n], ["city" => $c, "state" => $s]);
};
$report = [];

// ---------- 1. Life dates on existing records (set only when empty) ----------
$life = [
    ["Henry McCulloch", "1751-03-03", "1819"],
    ["Jason Parmenter", "1734-01-01", null],
    ["Daniel Ludington", "1749", "1824-08-21"],
    ["Alpheus Colton", "1765", "1823"],
    ["John Hamilton", "1754-11-25", "1837-08-22"],
    ["John Corbly", "1733-02-23", "1803-06-09"],
    ["John Waller", "1741-12-23", "1802-07-04"],
    ["James Childs", null, "1784"],
    ["Joseph Craig", null, "1819"],
    ["John Weatherford", null, "1833"],
    ["John Shackelford", "1750", null],
    ["William Webber", null, "1808-02-29"],
    ["Joseph Anthony", null, "1806"],
    ["David Tinsley", "1749", null],
    ["Christopher Gadsden", "1724-02-16", "1805-08-28"],
    ["Thomas Heyward Jr.", "1746-07-28", "1809-04-22"],
    ["Arthur Middleton", "1742-06-26", "1787-01-01"],
    ["Edward Rutledge", "1749-11-23", "1800-01-23"],
];
foreach ($life as [$nm, $b, $d]) {
    $p = $find($nm);
    if (! $p) { $report[] = "life MISSING: ".$nm; continue; }
    $changed = false;
    if ($b && empty($p->birthdate)) { [$y, $m, $dd] = $parts($b); $p->setPartialDate("birthdate", $y, $m, $dd); $changed = true; }
    if ($d && empty($p->death_date)) { [$y, $m, $dd] = $parts($d); $p->setPartialDate("death_date", $y, $m, $dd); $changed = true; }
    if ($changed) { $p->save(); $report[] = "life set: ".$nm." b=".($p->partialDateIso("birthdate") ?? "-")." d=".($p->partialDateIso("death_date") ?? "-"); }
    else { $report[] = "life unchanged: ".$nm; }
}

// ---------- 2a. Shays conviction date April 22 -> April 9, 1787 (4 confirmed) ----------
foreach (["Jason Parmenter", "Daniel Ludington", "James White", "Alpheus Colton"] as $nm) {
    $p = $find($nm);
    if (! $p) { $report[] = "shays MISSING: ".$nm; continue; }
    $p->description = str_replace("April 22, 1787", "April 9, 1787", (string) $p->description);
    $p->save();
    foreach ($p->cases as $c) {
        $c->sentence = str_replace("April 22, 1787", "April 9, 1787", (string) $c->sentence);
        $c->setPartialDate("sentenced_date", 1787, 4, 9);
        $c->save();
    }
    $report[] = "shays date corrected: ".$nm;
}

// ---------- 2b. Whiskey Rebellion identity corrections ----------
$renames = [
    ["William Porter", "Robert Porter", "Robert", "Porter", "Captain Robert Porter"],
    ["John Flannigin", "John Flanagan", "John", "Flanagan", "John Flanagan"],
    ["David McComb", "Thomas McComb", "Thomas", "McComb", "Thomas McComb"],
];
foreach ($renames as [$old, $new, $first, $last, $aka]) {
    $p = $find($old);
    if (! $p) { $report[] = "rename skip (already ".$new."?): ".$old; continue; }
    $p->name = $new; $p->first_name = $first; $p->last_name = $last; $p->aka = $aka;
    $p->description = str_replace($old, $aka, (string) $p->description);
    $p->save();
    $report[] = "renamed: ".$old." -> ".$new;
}

// Robert Porter: acquitted May 18, 1795
$p = $find("Robert Porter");
if ($p) {
    if (strpos((string) $p->description, "acquitted") === false) {
        $p->description = rtrim((string) $p->description)." He was acquitted on May 18, 1795.";
        $p->save();
    }
    foreach ($p->cases as $c) {
        $c->sentence = "Marched to Philadelphia in chains in late 1794; acquitted at the federal treason proceedings on May 18, 1795";
        $c->setPartialDate("release_date", 1795, 5, 18);
        $c->save();
    }
    $report[] = "porter acquittal recorded";
}

// Thomas McComb: Founders Online identification
$p = $find("Thomas McComb");
if ($p && strpos((string) $p->description, "Robert Johnson") === false) {
    $p->description = rtrim((string) $p->description)." Founders Online identifies him as probably the man involved in the 1791 attack on the revenue officer Robert Johnson.";
    $p->save();
    $report[] = "mccomb note added";
}

// ---------- 2c. James Pugh disputed identification ----------
$p = $find("James Pugh");
if ($p && strpos((string) $p->description, "disputed") === false) {
    $p->description = rtrim((string) $p->description)." Some later genealogical research disputes this identification, and it is possible that accounts merged two different members of the Pugh family.";
    $p->save();
    $report[] = "pugh disputed note added";
}

// ---------- 3. Documented durations for two ministers ----------
$p = $find("John Shackelford");
if ($p) {
    $essex = $mkInst("Essex County jail", "Tappahannock", "Virginia");
    foreach ($p->cases as $c) {
        $c->institution_id = $essex->id;
        $c->sentence = "Held about eight days in the Essex County jail for preaching without a license";
        $c->save();
    }
    $report[] = "shackelford: Essex jail, eight days";
}
$p = $find("David Tinsley");
if ($p) {
    foreach ($p->cases as $c) {
        $c->sentence = "Imprisoned four months and sixteen days in the Chesterfield County jail for preaching without a license";
        $c->save();
    }
    $report[] = "tinsley: four months sixteen days";
}

// ---------- 4. New records: three ministers + four 1741 defendants ----------
// [name, first, last, race, gender, state, era, desc, fate, [inst,city,state]|null,
//  charges, sentence, inc_iso, end_iso|null, birth_iso|null, death_iso|null]
$news = [
    ["Lewis Craig", "Lewis", "Craig", "White", "Male", "Virginia", "Colonial America",
        "Lewis Craig was a leading Separate Baptist preacher. In June 1768 he was seized with John Waller and others and jailed in the Spotsylvania County jail at Fredericksburg for preaching without a license, one of the episodes that helped turn Virginia opinion toward religious liberty.",
        "released", ["Spotsylvania County jail", "Fredericksburg", "Virginia"],
        "Preaching without a license and disturbing the peace", "Jailed about a month at Fredericksburg in 1768",
        "1768-06", "1768-07", "1737", "1825"],
    ["Elijah Craig", "Elijah", "Craig", "White", "Male", "Virginia", "Colonial America",
        "Elijah Craig was a Separate Baptist preacher and brother of Lewis Craig. He was imprisoned in the Culpeper County jail for preaching without a license and continued to preach to the crowds who gathered outside the prison.",
        "released", ["Culpeper County jail", "Culpeper", "Virginia"],
        "Preaching without a license and disturbing the peace", "Imprisoned in the Culpeper County jail, duration not documented",
        "1768", null, "1745", "1808-05-18"],
    ["James Ireland", "James", "Ireland", "White", "Male", "Virginia", "Colonial America",
        "James Ireland was a Separate Baptist preacher imprisoned in the Culpeper County jail from late 1769 into 1770 for preaching without a license. He preached through the grate of his cell and later wrote a well-known account of his persecution.",
        "released", ["Culpeper County jail", "Culpeper", "Virginia"],
        "Preaching without a license and disturbing the peace", "Imprisoned in the Culpeper County jail from late 1769 into 1770",
        "1769", "1770", "1748", "1806"],
    ["Cook (Comfort)", "Cook (Comfort)", "", "Black", "Male", "New York", "Colonial America",
        "Cook, an enslaved man held by Comfort, was among the defendants condemned in the 1741 New York conspiracy panic. He was executed in New York City, reportedly on June 9, 1741.",
        "executed", ["New York City Hall", "New York", "New York"],
        "Alleged participation in the 1741 New York conspiracy", "Condemned and executed, reportedly June 9, 1741",
        "1741", "1741-06-09", null, null],
    ["Albany", "Albany", "", "Black", "Male", "New York", "Colonial America",
        "Albany, an enslaved man, was among the defendants condemned in the 1741 New York conspiracy panic. He was executed in New York City, reportedly on June 12, 1741.",
        "executed", ["New York City Hall", "New York", "New York"],
        "Alleged participation in the 1741 New York conspiracy", "Condemned and executed, reportedly June 12, 1741",
        "1741", "1741-06-12", null, null],
    ["Curacao Dick", "Curacao Dick", "", "Black", "Male", "New York", "Colonial America",
        "Curacao Dick, an enslaved man, was among the defendants condemned in the 1741 New York conspiracy panic. He was executed in New York City, reportedly on June 12, 1741.",
        "executed", ["New York City Hall", "New York", "New York"],
        "Alleged participation in the 1741 New York conspiracy", "Condemned and executed, reportedly June 12, 1741",
        "1741", "1741-06-12", null, null],
    ["Captain Jack", "Captain Jack", "", "Black", "Male", "New York", "Colonial America",
        "Captain Jack, also called Comfort Jack, was an enslaved defendant in the 1741 New York conspiracy panic. Rather than being executed on the scheduled date he offered to give information in exchange for his life; his final fate is not recorded.",
        "unknown", ["New York City Hall", "New York", "New York"],
        "Alleged participation in the 1741 New York conspiracy", "Imprisoned in 1741; turned informer, final disposition unrecorded",
        "1741", null, null, null],
];
$created = 0;
foreach ($news as $r) {
    [$nm, $fn, $ln, $race, $gender, $state, $era, $desc, $fate, $inst, $charges, $sentence, $inc, $end, $bd, $dd] = $r;
    if ($find($nm)) { $report[] = "new exists: ".$nm; continue; }
    $p = new Prisoner();
    $p->name = $nm; $p->first_name = $fn; $p->last_name = $ln ?: null;
    $p->description = $desc; $p->race = $race; $p->gender = $gender; $p->state = $state; $p->era = $era;
    $p->in_custody = false; $p->awaiting_trial = false;
    if ($fate === "executed" || $fate === "died") {
        if ($end) { [$y, $m, $d2] = $parts($end); $p->setPartialDate("death_date", $y, $m, $d2); }
        $p->released = false;
    } else {
        $p->released = ($fate === "released");
    }
    if ($bd) { [$y, $m, $d2] = $parts($bd); $p->setPartialDate("birthdate", $y, $m, $d2); }
    if ($dd && empty($p->death_date)) { [$y, $m, $d2] = $parts($dd); $p->setPartialDate("death_date", $y, $m, $d2); }
    $p->save();

    $institution = $inst ? $mkInst($inst[0], $inst[1], $inst[2]) : null;
    $c = new PrisonerCase();
    $c->prisoner_id = $p->id;
    if ($institution) { $c->institution_id = $institution->id; }
    $c->charges = $charges; $c->sentence = $sentence;
    if ($ip = $parts($inc)) { $c->setPartialDate("incarceration_date", $ip[0], $ip[1], $ip[2]); }
    if ($fate === "executed" || $fate === "died") {
        if ($ep = $parts($end)) { $c->setPartialDate("death_in_custody_date", $ep[0], $ep[1], $ep[2]); }
    } elseif ($end) {
        [$y, $m, $d2] = $parts($end); $c->setPartialDate("release_date", $y, $m, $d2);
    }
    $c->save();
    $created++;
    $report[] = "new created: ".$p->slug." | ".$p->name;
}

// ---------- 5. St. Augustine portraits ----------
$photos = [
    ["Christopher Gadsden", "prisoners/christopher-gadsden.jpg"],
    ["Thomas Heyward Jr.", "prisoners/thomas-heyward-jr.jpg"],
    ["Arthur Middleton", "prisoners/arthur-middleton.jpg"],
    ["Edward Rutledge", "prisoners/edward-rutledge.jpg"],
];
foreach ($photos as [$nm, $path]) {
    $p = $find($nm);
    if ($p && empty($p->photo) && is_file(storage_path("app/public/".$path))) {
        $p->photo = $path; $p->save(); $report[] = "photo set: ".$nm;
    } else {
        $report[] = "photo skip: ".$nm.($p ? ($p->photo ? " (has photo)" : " (file missing)") : " (record missing)");
    }
}

echo implode("\n", $report)."\n\n=== New records created: {$created} ===\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Verified colonial enrichment applied."
