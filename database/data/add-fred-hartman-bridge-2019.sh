#!/usr/bin/env bash
#
# The 22 Greenpeace activists federally charged after the September 12, 2019
# Fred Hartman Bridge action over the Houston Ship Channel.
#
#   Held            September 12, 2019
#   Released        September 14, 2019 -- a federal magistrate released all 22
#                   on personal-recognizance bonds (2 days)
#   Charge          Aiding and abetting obstruction of navigable waters
#   Outcome         No prison sentence for anyone; the state and federal cases
#                   were eventually resolved through agreements leading to
#                   dismissal.
#
# BIRTH YEARS are derived from the ages reported at the time of arrest, so
# they are stored at YEAR precision and are approximate: someone reported as
# 29 in September 2019 was born in 1989 or 1990 depending on their birthday.
# The reported age is kept in the bio so the underlying fact is preserved.
#
# Jayden Allen is deliberately left without a birth year -- the mugshot
# caption says 29 while the Justice Department list says 20, and there is no
# way to tell which is right from the source.
#
# Mugshots come from the KPRC Click2Houston gallery of the Harris County
# booking photos, cropped out of their pillarboxed 16:9 frames. Sydney
# Clifford has no mugshot: she was on the to-be-warranted list rather than in
# custody at the time the gallery was published.
#
# Idempotent -- updates rather than duplicating. Run from the repo root:
#   bash database/data/add-fred-hartman-bridge-2019.sh
# then place the new records:
#   php artisan prisoners:auto-place-zero-sort

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

// [name, first, last, age at arrest (null = do not derive), city, state, photo slug]
$roster = [
    ["Zeph Fishlyn", "Zeph", "Fishlyn", 52, "Oakland", "California", "zeph-fishlyn"],
    ["Tamura R. Seiji", "Tamura", "Seiji", 29, "Oakland", "California", "tamura-seiji"],
    ["Richard A. Sisney", "Richard", "Sisney", 32, "Oakland", "California", "richard-sisney"],
    ["Cole Asher Taylor-Martin", "Cole", "Taylor-Martin", 35, "Fullerton", "California", "cole-taylor-martin"],
    ["Jayden Allen", "Jayden", "Allen", null, "Warrensburg", "Missouri", "jayden-allen"],
    ["Dakota P. Schee", "Dakota", "Schee", 25, "Kansas City", "Missouri", "dakota-schee"],
    ["Jonathan Butler", "Jonathan", "Butler", 29, "Washington", "District of Columbia", "jonathan-butler"],
    ["Tracye Redd", "Tracye", "Redd", 28, "Washington", "District of Columbia", "tracye-redd"],
    ["Ryan Harris", "Ryan", "Harris", 41, "Olympia", "Washington", "ryan-harris"],
    ["Piper Werle", "Piper", "Werle", 29, "Port Orchard", "Washington", "piper-werle"],
    ["Brianna Gibson", "Brianna", "Gibson", 28, "Brooklyn", "New York", "brianna-gibson"],
    ["Irene Kim", "Irene", "Kim", 26, "Jericho", "New York", "irene-kim"],
    ["Julie A. McElvain", "Julie", "McElvain", 36, "Steamboat Springs", "Colorado", "julie-mcelvain"],
    ["Chelcee Price", "Chelcee", "Price", 23, "Denver", "Colorado", "chelcee-price"],
    ["Christian Bufford", "Christian", "Bufford", 32, "Ellenwood", "Georgia", "christian-bufford"],
    ["Sydney Clifford", "Sydney", "Clifford", 21, "Portland", "Oregon", null],
    ["Michael Herbert", "Michael", "Herbert", 36, "Hyattsville", "Maryland", "michael-herbert"],
    ["Tyler N. McFarland", "Tyler", "McFarland", 27, "Dover", "New Hampshire", "tyler-mcfarland"],
    ["Sarah Newman", "Sarah", "Newman", 42, "Lexington", "Kentucky", "sarah-newman"],
    ["Heidi Nybroten", "Heidi", "Nybroten", 26, "Minneapolis", "Minnesota", "heidi-nybroten"],
    ["Shavone Torres", "Shavone", "Torres", 39, "Pennsauken", "New Jersey", "shavone-torres"],
    ["Heather Doyle", "Heather", "Doyle", 35, "Albuquerque", "New Mexico", "heather-doyle"],
];

$created = 0; $updated = 0; $photos = 0;

foreach ($roster as [$name, $first, $last, $age, $city, $state, $photoSlug]) {
    $bio = $name." was one of 22 Greenpeace activists federally charged after the September 12, 2019 blockade of the Houston Ship Channel from the Fred Hartman Bridge, a protest against fossil-fuel exports in which climbers suspended themselves from the span. ";
    $bio .= "Held from September 12, they were released on September 14, 2019 when a federal magistrate granted all 22 personal-recognizance bonds. ";
    if ($age) { $bio .= "Reported as ".$age." years old at the time of the arrest, and resident in ".$city.", ".$state.". "; }
    else { $bio .= "Resident in ".$city.", ".$state.". "; }
    $bio .= "No one charged received a prison sentence; the state and federal cases were eventually resolved through agreements leading to dismissal.";

    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();

    if (! $p) {
        $p = Prisoner::create([
            "name" => $name, "first_name" => $first, "last_name" => $last,
            "state" => $state, "era" => "2010s",
            "ideologies" => ["Environmental Activism"],
            "affiliation" => ["Greenpeace"],
            "in_custody" => false, "released" => true,
            "minor_case" => true,
            "description" => $bio,
        ]);
        $created++;
        echo "created  {$p->name}  [{$p->slug}]\n";
    } else {
        if (! $p->description) { $p->description = $bio; }
        if (! $p->state) { $p->state = $state; }
        if (! $p->era) { $p->era = "2010s"; }
        $updated++;
        echo "updated  {$p->name}  [{$p->slug}]\n";
    }

    $p->first_name = $first;
    $p->last_name = $last;
    $affs = is_array($p->affiliation) ? $p->affiliation : [];
    if (! in_array("Greenpeace", $affs, true)) { $affs[] = "Greenpeace"; }
    $p->affiliation = array_values($affs);
    $ideo = is_array($p->ideologies) ? $p->ideologies : [];
    if (! in_array("Environmental Activism", $ideo, true)) { $ideo[] = "Environmental Activism"; }
    $p->ideologies = array_values($ideo);

    // Year precision only -- derived from the reported age, so it can be a
    // year out depending on the birthday.
    if ($age && ! $p->birthdate) {
        $p->setPartialDate("birthdate", 2019 - $age, null, null);
    }

    $p->in_custody = false;
    $p->awaiting_trial = false;
    $p->released = true;
    $p->save();

    $c = $p->cases()->where("charges", "like", "%navigable waters%")->first();
    if (! $c) {
        $c = $p->cases()->create([
            "charges" => "Aiding and abetting obstruction of navigable waters — the Fred Hartman Bridge blockade of the Houston Ship Channel.",
        ]);
    }
    $c->convicted = "No prison sentence — the state and federal cases were resolved through agreements leading to dismissal";
    $c->sentence = "Held from September 12, 2019 until September 14, 2019, when a federal magistrate released all 22 defendants on personal-recognizance bonds.";
    $c->setPartialDate("arrest_date", 2019, 9, 12);
    $c->setPartialDate("incarceration_date", 2019, 9, 12);
    $c->setPartialDate("release_date", 2019, 9, 14);
    $c->save();

    if ($photoSlug) {
        $src = base_path("database/data/photos/greenpeace-2019/{$photoSlug}.jpg");
        $dstRel = "prisoners/{$p->slug}.jpg";
        if (is_file($src) && empty($p->photo)) {
            File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
            File::copy($src, storage_path("app/public/{$dstRel}"));
            $p->photo = $dstRel;
            $p->save();
            $photos++;
        }
    }
    echo "    born=".($p->birthdate ? $p->birthdate->year." (year precision)" : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")
        ."  photo=".($p->photo ? "yes" : "no")."\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. Created {$created}, updated {$updated}, photos attached {$photos}.\n";
echo "Run: php artisan prisoners:auto-place-zero-sort to position the new records.\n";
'

echo
echo "Done."
