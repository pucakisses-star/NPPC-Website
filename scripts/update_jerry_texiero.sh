#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_jerry_texiero.sh
#
# Updates Jerry Texiero's prisoner record and case with details from
# the June 2006 Banderas News story:
#   https://www.banderasnews.com/0603/nw-desertnam.htm
#
# Source-derived facts:
#   - Marine Corporal who deserted in summer 1965 from a California
#     base in opposition to the Vietnam War.
#   - Lived under the assumed name "Gerome Conti" for 40 years; sold
#     boats for a living.
#   - In 1998 (under his assumed identity) was convicted of fraud and
#     theft and placed on probation - the booking that put his
#     fingerprints into the national database.
#   - When the Marine Corps reopened his desertion file and ran the
#     fingerprints, the match surfaced. He was taken into custody by
#     Tarpon Springs (Florida) police in summer 2005, age 65.
#   - Held about 5 months at Pinellas County Jail in Clearwater (4 of
#     those months in solitary confinement).
#   - Discharged in January 2006 with an Other-Than-Honorable
#     discharge, without a court-martial. Had a court-martial gone
#     forward and convicted him, he could have faced up to 3 years
#     in the brig and a dishonorable discharge.
#   - Returned to his boat-selling job (his employer had held it
#     open) after release.
#   - Quoted in the article: "I thought they couldnt possibly be
#     looking for me anymore. I would think they would have stopped
#     looking for anybody who had been gone as long as I had."
#
# This script also downloads the photo from the article
# (images/desertnam.jpg) and attaches it to his profile.
set -e

# ---- 1. Download the photo to storage/app/public/prisoners ----
PHOTO_URL="https://www.banderasnews.com/0603/images/desertnam.jpg"
PHOTO_REL="prisoners/jerry-texiero.jpg"
PHOTO_ABS="storage/app/public/${PHOTO_REL}"

mkdir -p "$(dirname "$PHOTO_ABS")"
echo "Downloading ${PHOTO_URL} -> ${PHOTO_ABS}"
if ! curl -fsSL -A "Mozilla/5.0 NPPC-archive" -o "$PHOTO_ABS" "$PHOTO_URL"; then
    echo "WARNING: photo download failed; continuing without photo update."
    PHOTO_REL=""
fi

# ---- 2. Update the prisoner + case via tinker ----
php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Jerry Texiero")
    ->orWhere("name", "like", "%Jerry Texiero%")
    ->orWhere("aka", "like", "%Gerome Conti%")
    ->first();

if (!$p) {
    echo "ERROR: Jerry Texiero not found\n";
    exit(1);
}

$p->aka         = "Gerome Conti";
$p->birthdate   = $p->birthdate ?: "1940-01-01"; // he was 65 in summer 2005; year approximate
$p->state       = $p->state ?: "Florida";
$p->ideologies  = ["Anti-war", "Conscientious objector"];
$p->in_custody  = false;
$p->released    = true;
$p->description = "Jerry Texiero was a Vietnam-era Marine Corps Corporal who deserted from his California base in the summer of 1965 in opposition to the war and lived underground for 40 years under the assumed name Gerome Conti. He worked selling boats for a living. In 1998 he was convicted of fraud and theft under his assumed name and placed on probation - the booking that put his fingerprints into the national database. When the Marine Corps later reopened his desertion file and ran his prints, the match surfaced and he was taken into custody by police in Tarpon Springs, Florida on August 16, 2005, at age 65. He was held for approximately five months at the Pinellas County Jail in Clearwater - four of those months in solitary confinement - and was given an Other-Than-Honorable discharge in January 2006 without a court-martial. (Had a court-martial gone forward and convicted him, he could have faced up to three years in the brig and a dishonorable discharge.) After release he returned to his boat-selling job, which his employer had kept open for him. As he told Banderas News: I thought they couldnt possibly be looking for me anymore. I would think they would have stopped looking for anybody who had been gone as long as I had. (Source: Banderas News, June 2006.)";

$photoRel = "'"$PHOTO_REL"'";
if ($photoRel !== "" && !$p->photo) {
    $p->photo = $photoRel;
}

$p->save();
echo "Updated prisoner: {$p->name} (aka={$p->aka}, photo={$p->photo})\n";

// Now update the case row
$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Pinellas County Jail"],
    ["city" => "Clearwater", "state" => "Florida"]
);

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    $case = new App\Models\PrisonerCase(["prisoner_id" => $p->id]);
}

$case->institution_id     = $inst->id;
$case->charges            = "Desertion (Vietnam era) - Marine Corps Corporal who deserted from his California base in summer 1965 in opposition to the Vietnam War. Lived underground for 40 years under the assumed name Gerome Conti before fingerprint match in the national database (from a 1998 fraud/theft booking under the assumed identity) led the Marine Corps to reopen his file and locate him in Tarpon Springs, Florida.";
$case->arrest_date        = "2005-08-16";
$case->incarceration_date = "2005-08-16";
$case->release_date       = "2006-01-16";
$case->sentence           = "Held approximately 5 months at the Pinellas County Jail in Clearwater, Florida (4 months in solitary confinement) before being given an Other-Than-Honorable discharge in January 2006 without a court-martial. Had he been court-martialed and convicted he could have faced up to 3 years in the brig and a dishonorable discharge.";
$case->convicted          = "No - discharged Other-Than-Honorable without court-martial, January 2006";
$case->save();

echo "Updated case id={$case->id}: arrest=2005-08-16 incarc=2005-08-16 release=2006-01-16 imprisoned_for_days={$case->imprisoned_for_days}\n";
'

echo
echo "Jerry Texiero update complete."
