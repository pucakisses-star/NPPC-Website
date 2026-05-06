#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/refine_jerry_texiero_dates.sh
#
# Refines Jerry Texieros release timeline with precise dates from
# People's World and contemporaneous press coverage:
#
#   - Aug 16, 2005: arrested at Marines request in Tarpon Springs, FL
#   - Aug 16, 2005 - Dec 16, 2005: held at Pinellas County Jail in
#     Clearwater, FL (~4 months in solitary confinement)
#   - Dec 16, 2005: transferred to the Marine Corps brig at Marine
#     Corps Base Camp Lejeune, North Carolina, for processing
#   - Jan 11, 2006: U.S. Marine Corps Command at Camp Lejeune
#     announced his release with an Other-Than-Honorable discharge in
#     lieu of prosecuting him for desertion
#
# Sources:
#   - https://www.peoplesworld.org/article/marines-throw-in-the-towel-discharge-40-year-awol-rather-than-court-martialling-him/
#   - https://www.peoplesworld.org/article/marine-refuser-from-40-years-ago-faces-court-martial/
#   - https://www.tampabay.com/archive/2005/08/18/dogged-pursuit-led-marines-to-alleged-deserter/
#   - https://www.banderasnews.com/0603/nw-desertnam.htm
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Jerry Texiero")
    ->orWhere("name", "like", "%Jerry Texiero%")
    ->orWhere("aka", "like", "%Gerome Conti%")
    ->first();

if (!$p) {
    echo "ERROR: Jerry Texiero not found\n";
    exit(1);
}

$case = $p->cases()->orderBy("created_at")->first();
if (!$case) {
    echo "ERROR: Jerry Texiero has no case row\n";
    exit(1);
}

$case->release_date = "2006-01-11";
$case->sentence     = "Held August 16 to December 16, 2005 (~4 months in solitary confinement) at the Pinellas County Jail in Clearwater, Florida, then transferred December 16, 2005 to the Marine Corps brig at Marine Corps Base Camp Lejeune, North Carolina. On January 11, 2006 the Marine Corps Command at Camp Lejeune announced his release with an Other-Than-Honorable discharge in lieu of prosecuting him for desertion. Total custody: ~5 months. Had he been court-martialed and convicted, he could have faced up to 3 years in the brig and a dishonorable discharge.";
$case->convicted    = "No - released January 11, 2006 with Other-Than-Honorable discharge in lieu of court-martial";
$case->save();

echo "Refined case id={$case->id}: arrest=2005-08-16 release=2006-01-11 imprisoned_for_days={$case->imprisoned_for_days}\n";

// Append precise dates to the prisoner description if not already present
if (strpos($p->description, "January 11, 2006") === false) {
    $p->description = preg_replace(
        "/given an Other-Than-Honorable discharge in January 2006/",
        "released on January 11, 2006 with an Other-Than-Honorable discharge",
        $p->description
    );
    $p->save();
    echo "Updated prisoner description to include January 11, 2006 release date.\n";
}
'

echo
echo "Jerry Texiero date refinement complete."
