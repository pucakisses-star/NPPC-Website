#!/usr/bin/env bash
#
# Add the Rev. Frank Dukes (1931-2023), Birmingham civil-rights organizer and
# Miles College student-government president who drafted the December 1961
# "This We Believe" statement and led the 1962 Selective Buying Campaign, the
# downtown economic boycott that helped set the stage for the 1963 Birmingham
# Campaign. He was among those jailed for the Easter Sunday March of April 1963.
#
# His time incarcerated is recorded as 12 days (per the site owner). The Easter
# Sunday March of April 14, 1963 is used as the documented incarceration date;
# no release day is recorded (unconfirmed), so imprisoned_for_days is set
# directly to 12 rather than being derived from an invented release date.
#
# Photo: a later-life portrait of Rev. Dukes (a broadcast/YouTube frame from
# Birmingham Times obituary coverage), copyrighted and used at low resolution
# under the same non-commercial fair-use / memorial rationale as the other
# Birmingham civil-rights portraits in photos/nonfree/. See CREDITS-nonfree.md.
#
# Idempotent: finds Dukes by name (creating a stub only if absent), fills only
# empty biographical fields, replaces his single case, and sets the photo only
# if he has none. Run from the repo root:
#   bash database/data/add-frank-dukes.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

SRC="database/data/photos/nonfree/frank-dukes.png"
DST="storage/app/public/prisoners/frank-dukes.png"
mkdir -p "$(dirname "$DST")"
if [ -f "$SRC" ] && [ ! -f "$DST" ]; then
    cp "$SRC" "$DST"
    echo "copied frank-dukes.png"
fi

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("name", "Frank Dukes")->first();

$bio = "The Rev. Frank Dukes (1931-2023) was a Birmingham civil-rights organizer and Miles College student leader who helped lay the groundwork for the 1963 Birmingham Campaign. As president of the Miles College Student Government Association he drafted the December 1961 statement \"This We Believe\" and led the 1962 Selective Buying Campaign, urging Black Birmingham residents to stop shopping at downtown businesses that kept segregated facilities or refused to hire Black workers in meaningful jobs; the resulting economic pressure desegregated some stores before Martin Luther King Jr. and the SCLC launched their better-known campaign. A co-leader of the 1963 Easter Sunday March — for which he was jailed — Dukes was active in the Alabama Christian Movement for Human Rights, the SCLC and the NAACP, and volunteered as one of Dr. King'"'"'s Birmingham bodyguards. An Army veteran of the Korean War, he later became a minister, a vocational-rehabilitation counselor and a Miles College administrator. He died in Birmingham on November 11, 2023, aged 92.";

$isNew = ! $p;
if ($isNew) {
    $p = new \App\Models\Prisoner();
    $p->name = "Frank Dukes";
}

$fill = function (string $field, $value) use ($p) {
    $cur = $p->{$field};
    if ($cur === null || $cur === "" || (is_array($cur) && count($cur) === 0)) {
        $p->{$field} = $value;
    }
};
$fill("first_name", "Frank");
$fill("last_name", "Dukes");
$fill("description", $bio);
$fill("state", "Alabama");
$fill("race", "Black");
$fill("gender", "Male");
$fill("ideologies", ["Civil Rights"]);
$fill("affiliation", ["Alabama Christian Movement for Human Rights", "Southern Christian Leadership Conference", "NAACP"]);
$fill("era", "1960s");
if (empty($p->birthdate)) { $p->setPartialDate("birthdate", 1931); }   // year only: 92 in 2023 and "31 in 1962" both point to 1931
if (empty($p->death_date)) { $p->death_date = "2023-11-11"; }
$p->in_custody = false;
$p->released = true;
$p->save();

echo "Prisoner: {$p->name} (ID: {$p->id}, slug: {$p->slug}) [".($isNew ? "created" : "existing")."]\n";

// Single case: jailed for the Easter Sunday March, Birmingham, April 1963.
$p->cases()->delete();
$c = new \App\Models\PrisonerCase();
$c->prisoner_id = $p->id;
$inst = \App\Models\Institution::firstOrCreate(["name" => "Birmingham City Jail"], ["city" => "Birmingham", "state" => "Alabama"]);
$c->institution_id = $inst->id;
$c->charges = "Demonstrating for civil rights during the Birmingham campaign (Easter Sunday March, April 1963)";
$c->sentence = "Jailed following the Easter Sunday March of April 14, 1963; recorded as twelve days in custody (release day unconfirmed).";
$c->setPartialDate("incarceration_date", 1963, 4, 14);
$c->save();

// The model derives imprisoned_for_days from incarceration/release dates and
// nulls it when no release date is recorded; set the 12-day figure directly
// (bypassing that hook) so it is not tied to an invented release date.
\Illuminate\Support\Facades\DB::table("prisoner_cases")->where("id", $c->id)->update(["imprisoned_for_days" => 12]);
echo "Case: Birmingham City Jail, incarcerated ".($c->partialDateIso("incarceration_date") ?? "-").", imprisoned_for_days=12\n";

// Photo (fill-if-empty).
if (empty($p->photo) && is_file(storage_path("app/public/prisoners/frank-dukes.png"))) {
    $p->photo = "prisoners/frank-dukes.png";
    $p->save();
    echo "SET photo on {$p->name}.\n";
} elseif (! empty($p->photo)) {
    echo "Already has a photo ({$p->photo}) — leaving alone.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Frank Dukes added (12 days incarcerated) with portrait."
