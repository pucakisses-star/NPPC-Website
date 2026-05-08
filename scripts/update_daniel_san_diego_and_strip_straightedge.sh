#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_daniel_san_diego_and_strip_straightedge.sh
#
# Three tasks in one script:
#   1. Remove "Straight edge" from every prisoner's ideologies array.
#   2. Download an MSNBC press photo of Daniel Andreas San Diego and
#      attach it to his prisoner record.
#   3. Update his record to reflect:
#        - ~21-year exile (Oct 6, 2003 -> Nov 26, 2024) while on the
#          run as the first domestic-terror suspect on the FBI Most
#          Wanted Terrorists list, after the Aug 28 / Sep 26, 2003
#          biotech / Shaklee bombings;
#        - UK detention since Nov 26, 2024 in Wales pending US
#          extradition (Westminster Magistrates' Court approved
#          extradition Feb 6, 2026).
set -e

PHOTO_URL="https://media-cldnry.s-nbcnews.com/image/upload/t_fit-1000w,f_auto,q_auto:best/MSNBC/Components/Photo/_new/090421-sandiego-vmed-7a.jpg"
PHOTO_REL="prisoners/daniel-andreas-san-diego.jpg"
PHOTO_ABS="storage/app/public/${PHOTO_REL}"

mkdir -p "$(dirname "$PHOTO_ABS")"
echo "Downloading ${PHOTO_URL}"
if ! curl -fsSL -A "Mozilla/5.0 NPPC-archive (https://nppc.org)" -o "$PHOTO_ABS" "$PHOTO_URL"; then
    echo "  WARNING: photo download failed; continuing without photo update."
    PHOTO_REL=""
fi

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;
use App\Models\PrisonerCase;

// ---- 1. Remove "Straight edge" from every ideologies array ----
$updated = 0;
Prisoner::whereNotNull("ideologies")->chunkById(200, function ($chunk) use (&$updated) {
    foreach ($chunk as $p) {
        $current = $p->ideologies;
        if (! is_array($current) || empty($current)) continue;
        $filtered = array_values(array_filter($current, fn ($v) => ! is_string($v) || $v !== "Straight edge"));
        if (count($filtered) === count($current)) continue;
        $p->ideologies = $filtered ?: null;
        $p->save();
        $updated++;
    }
});
echo "Removed Straight edge from {$updated} prisoners\n";

// ---- 2 & 3. Daniel Andreas San Diego: photo, exile case, UK custody case ----
$daniel = Prisoner::where("name", "Daniel Andreas San Diego")
    ->orWhere("name", "like", "%Daniel Andreas San Diego%")
    ->orWhere("aka", "like", "%Daniel Andreas San Diego%")
    ->first();

if (! $daniel) {
    echo "Daniel Andreas San Diego: NOT FOUND - creating record\n";
    $daniel = Prisoner::create([
        "name"        => "Daniel Andreas San Diego",
        "first_name"  => "Daniel",
        "middle_name" => "Andreas",
        "last_name"   => "San Diego",
        "gender"      => "Male",
        "race"        => "White",
        "state"       => "California",
        "birthdate"   => "1978-02-09",
        "era"         => "2000s",
        "affiliation" => ["Animal Liberation Front (ALF)", "Revolutionary Cells - Animal Liberation Brigade (RCALB)"],
        "ideologies"  => ["Animal liberation", "Anti-vivisection"],
        "in_custody"  => true,
        "released"    => false,
        "description" => "Daniel Andreas San Diego is an American animal-rights activist and the first domestic-terror suspect added to the FBI Most Wanted Terrorists list. Federal authorities allege he planted two pipe bombs that detonated on August 28, 2003 at the Chiron Corporation biotechnology campus in Emeryville, California, and a nail-bomb on September 26, 2003 at the Shaklee Corporation in Pleasanton, California - both companies linked by activists to Huntingdon Life Sciences, a UK animal-testing laboratory. The Revolutionary Cells - Animal Liberation Brigade claimed responsibility for both bombings. The FBI had San Diego under surveillance on October 6, 2003 when he parked his car near downtown San Francisco and disappeared into a transit station; he was on the run for over 21 years. He was arrested by UK National Crime Agency officers near Conwy, North Wales on November 26, 2024, after surveillance traced him to a converted barn where he had been living. On February 6, 2026, Judge Samuel Goozee of Westminster Magistrates Court approved his extradition to the United States. He remains in UK custody pending transfer.",
    ]);
}

if ("'"$PHOTO_REL"'" !== "" && ! $daniel->photo) {
    $daniel->photo = "'"$PHOTO_REL"'";
}
$daniel->in_custody = true;
$daniel->released = false;
$daniel->save();
echo "Daniel Andreas San Diego (id={$daniel->id}): photo={$daniel->photo}\n";

// ---- Exile case (Oct 6, 2003 -> Nov 26, 2024) ----
$exileInst = Institution::firstOrCreate(
    ["name" => "Federal fugitive / FBI Most Wanted Terrorists list - in hiding (UK)"],
    ["city" => "Conwy", "state" => "Wales, United Kingdom"]
);

$exileCase = $daniel->cases()
    ->where("institution_id", $exileInst->id)
    ->orWhere(function ($q) use ($daniel) {
        $q->where("prisoner_id", $daniel->id)->where("arrest_date", "2003-10-06");
    })
    ->first();

if (! $exileCase) {
    $exileCase = new PrisonerCase(["prisoner_id" => $daniel->id]);
}
$exileCase->institution_id = $exileInst->id;
$exileCase->charges = "Federal indictment, U.S. v. San Diego, N.D. Cal. - 18 U.S.C. ss 844(d), 844(i), 844(n) (use of explosives in commerce; arson/bombing of property used in interstate commerce; conspiracy) and 18 U.S.C. ss 924(c) (use of destructive device in furtherance of crimes of violence). Two pipe-bomb attacks on Chiron Corporation (Emeryville, CA) Aug 28, 2003 and a nail-bomb on Shaklee Corporation (Pleasanton, CA) Sep 26, 2003, claimed by Revolutionary Cells - Animal Liberation Brigade. San Diego went underground Oct 6, 2003 after slipping FBI surveillance and remained in exile / on the run for over 21 years.";
$exileCase->arrest_date = "2003-10-06";
$exileCase->incarceration_date = "2003-10-06";
$exileCase->release_date = "2024-11-26";
$exileCase->convicted = "Indicted; never tried during exile";
$exileCase->sentence = "~21 years in exile / on the run (Oct 6, 2003 - Nov 26, 2024)";
$exileCase->save();
echo "Daniel Andreas San Diego: exile case set, days=" . var_export($exileCase->fresh()->imprisoned_for_days, true) . "\n";

// ---- UK custody case (Nov 26, 2024 -> present, pending extradition) ----
$ukInst = Institution::firstOrCreate(
    ["name" => "UK custody / Westminster Magistrates Court - extradition pending"],
    ["city" => "London", "state" => "England, United Kingdom"]
);

$ukCase = $daniel->cases()
    ->where("institution_id", $ukInst->id)
    ->orWhere(function ($q) use ($daniel) {
        $q->where("prisoner_id", $daniel->id)->where("arrest_date", "2024-11-26");
    })
    ->first();

if (! $ukCase) {
    $ukCase = new PrisonerCase(["prisoner_id" => $daniel->id]);
}
$ukCase->institution_id = $ukInst->id;
$ukCase->charges = "Arrested by UK National Crime Agency officers near Conwy, North Wales on November 26, 2024 on a U.S. extradition warrant. Westminster Magistrates Court (Judge Samuel Goozee) approved extradition February 6, 2026.";
$ukCase->arrest_date = "2024-11-26";
$ukCase->incarceration_date = "2024-11-26";
$ukCase->release_date = null;
$ukCase->convicted = "Pending - extradition approved Feb 6, 2026";
$ukCase->sentence = "Pending";
$ukCase->judge = "Hon. Samuel Goozee (Westminster Magistrates Court)";
$ukCase->save();
echo "Daniel Andreas San Diego: UK custody case set, days=" . var_export($ukCase->fresh()->imprisoned_for_days, true) . "\n";
'

echo "Done."
