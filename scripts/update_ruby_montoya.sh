#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_ruby_montoya.sh
#
# Updates Ruby Montoya:
#   - Downloads The Intercepts 2019 portrait into
#     storage/app/public/prisoners/ruby-montoya.jpg and attaches it
#   - Sets birthdate (born 1990 in Phoenix, Arizona)
#   - Updates description with biographical details
#   - Adds release_date October 23, 2025 (per Radio Iowa coverage)
#   - Flips in_custody=false, released=true
#   - Strips "Indigenous solidarity" and "Anti-pipeline" from ideologies
#     per editorial decision to consolidate ideology tags
set -e

PHOTO_URL='https://theintercept.com/wp-content/uploads/2019/10/ruby-1570128597.jpg'
PHOTO_REL='prisoners/ruby-montoya.jpg'
PHOTO_ABS="storage/app/public/${PHOTO_REL}"

mkdir -p "$(dirname "$PHOTO_ABS")"
echo "Downloading ${PHOTO_URL} -> ${PHOTO_ABS}"
curl -fsSL -A "Mozilla/5.0 NPPC-archive" -o "$PHOTO_ABS" "$PHOTO_URL"
ls -la "$PHOTO_ABS"

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Ruby Montoya")->first();
if (!$p) {
    echo "ERROR: Ruby Montoya not found\n";
    exit(1);
}

$p->photo       = "prisoners/ruby-montoya.jpg";
$p->birthdate   = $p->birthdate ?: "1990-01-01"; // year-only known
$p->state       = $p->state ?: "Iowa";
$p->in_custody  = false;
$p->released    = true;
$p->description = "Ruby Montoya, born in 1990 and raised in Phoenix, Arizona, was a preschool teacher in Boulder, Colorado before quitting her job in 2016 to join the Mississippi Stand encampment against the Dakota Access Pipeline in southeast Iowa. With Jessica Reznicek - her friend and Des Moines Catholic Worker housemate - she set fire to oil-soaked rags and used oxyacetylene torches to disable construction equipment and burn through exposed pipeline valves at multiple Iowa sites between November 2016 and May 2017, attempting to halt completion of a pipeline that crosses Standing Rock Sioux water sources. The two publicly claimed responsibility at a July 2017 press conference at the Iowa Utilities Board. Montoya pleaded guilty in 2021 to conspiracy to damage an energy facility, was sentenced in September 2022 to 72 months in federal prison (with the federal terrorism enhancement applied) and roughly three million dollars in restitution joint with Reznicek. She was released from federal custody in October 2025.";

// Strip the deprecated tags
$ideologies = is_array($p->ideologies) ? $p->ideologies : [];
$ideologies = array_values(array_filter($ideologies, function ($i) {
    $lc = strtolower(trim((string) $i));
    return $lc !== "indigenous solidarity" && $lc !== "anti-pipeline";
}));
$p->ideologies = $ideologies;

$p->save();
echo "Updated prisoner: photo, birthdate=1990-01-01, ideologies=[" . implode(", ", $ideologies) . "]\n";

// Set release_date on her single case
$case = $p->cases()->orderByRaw("incarceration_date IS NULL, incarceration_date DESC")->first();
if ($case) {
    if (empty($case->release_date)) {
        $case->release_date = "2025-10-23";
        $case->save();
        echo "  case id={$case->id}: release_date=2025-10-23; imprisoned_for_days={$case->imprisoned_for_days}\n";
    } else {
        echo "  case id={$case->id}: release_date already set ({$case->release_date->toDateString()}); skipping.\n";
    }
}
'

echo
echo "Ruby Montoya update complete."
