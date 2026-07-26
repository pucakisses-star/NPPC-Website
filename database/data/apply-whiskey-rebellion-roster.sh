#!/usr/bin/env bash
#
# Whiskey Rebellion (1794-95) custody roster: updates the twenty existing
# Washington/convoy prisoners with documented dates and ADDS the missing
# people -- the Bedford County four, the rest of the Philadelphia convoy,
# the later federal commitments and the escape/parole cases.
#
# Date policy (per the source audit):
#   - exact dates only where documented ("strongest exact entries" table);
#   - month precision where the source gives a window (Feb 25-27; by Feb 19);
#   - Philadelphia commitment day (1794-12-25) as custody start where the
#     western arrest date is unknown (custody actually began earlier);
#   - NO release date where none was located -- narrative goes in the case
#     notes instead, and released/in_custody flags are set so no bogus
#     count-to-today duration appears.
#
# Idempotent. Run from the repo root:
#   bash database/data/apply-whiskey-rebellion-roster.sh
# then place the new records in the sort order:
#   php artisan prisoners:place-zero-sort
#
set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$tpl = Prisoner::withoutGlobalScopes()->where("slug", "john-hamilton")->first();
$era = $tpl && $tpl->era ? $tpl->era : "1790s";
$ideo = $tpl && is_array($tpl->ideologies) ? $tpl->ideologies : [];
$affil = $tpl && is_array($tpl->affiliation) ? $tpl->affiliation : [];

$instPhl  = Institution::firstOrCreate(["name" => "Philadelphia Gaol"], ["city" => "Philadelphia", "state" => "Pennsylvania"]);
$instWash = Institution::firstOrCreate(["name" => "Washington, Pennsylvania Jail"], ["city" => "Washington", "state" => "Pennsylvania"]);

// grp: phl = Philadelphia convoy commitment; bed = Bedford County; fed = later
// federal commitment; wash = short-term Washington PA; oth = other custody.
// [label, slug, aka, grp, inc [y,m,d], rel [y,m,d], convicted, note]
$entries = [
    // -- Philadelphia convoy: existing records --
    ["James Kerr", "james-kerr", null, "phl", [1794,11,15], [1795,1,13], null,
     "In custody at Washington, Pennsylvania by November 15, 1794; marched east with the convoy and committed at Philadelphia December 25, 1794; released on bail January 13, 1795."],
    ["John Hamilton", "john-hamilton", null, "phl", [1794,11,11], [1795,2,20], "No — discharged on bail after habeas corpus proceedings",
     "Voluntarily surrendered and was immediately arrested November 11, 1794; committed at Philadelphia December 25, 1794; ordered discharged on bail February 20, 1795."],
    ["John Laughery", "john-laughery", null, "phl", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; committed at Philadelphia December 25, 1794; exact release date not located."],
    ["John Corbly", "john-corbly", null, "phl", [1794,11,15], [1795,3,4], "No — released on recognizance; the remaining misdemeanor case was abandoned in April 1796",
     "In custody by November 15, 1794; committed at Philadelphia December 25, 1794; released on recognizance March 4, 1795."],
    ["Thomas Sedgwick", "thomas-sedgwick", null, "phl", [1794,11,15], [1795,2,null], null,
     "In custody by November 15, 1794; committed at Philadelphia December 25, 1794; released on bail during February 25 to 27, 1795 -- the precise day has not been isolated."],
    ["William Crawford", "william-crawford", null, "phl", [1794,11,15], null, "No — the grand jury refused to indict for treason",
     "In custody by November 15, 1794; committed at Philadelphia December 25, 1794; exact release date not located."],
    ["Robert Porter", "robert-porter", null, "phl", [1794,11,15], [1795,5,18], "No — acquitted May 18, 1795",
     "Initially recorded as William Porter but identified as Captain Robert Porter. Committed at Philadelphia December 25, 1794; acquitted May 18, 1795 and released that day or shortly afterward."],

    // -- Philadelphia convoy: new records --
    ["John Black", "john-black", null, "phl", [1794,12,25], null, null,
     "Western arrest date unknown; committed at Philadelphia December 25, 1794 after the thirty-day forced march; exact release date not located."],
    ["David Bolton", "david-bolton", null, "phl", [1794,12,25], [1795,10,16], "No — acquitted October 16, 1795",
     "Committed at Philadelphia December 25, 1794; acquitted October 16, 1795 and released that day or shortly afterward. It is unclear whether he remained continuously jailed throughout the intervening months."],
    ["James Stewart", "james-stewart", "James Steward", "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not established. His prosecution continued into the 1795 court sessions."],
    ["John Barnett", "john-barnett", "John Barnet", "phl", [1794,12,25], [1795,5,20], "No — acquitted May 20, 1795",
     "Committed at Philadelphia December 25, 1794; acquitted May 20, 1795 and released that day or shortly afterward."],
    ["Thomas Miller", "thomas-miller", null, "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not located."],
    ["Samuel Nye", "samuel-nye", null, "phl", [1794,12,25], null, null,
     "Arrested during the western crackdown, released in Pittsburgh, then rearrested after insulting cavalry officers; committed at Philadelphia December 25, 1794; released on bail in early 1795, exact date not located."],
    ["Philip Vigol", "philip-vigol", "Philip Wigle; Philip Weigel", "phl", [1794,12,25], [1795,11,2], "Convicted of treason; pardoned by President Washington on November 2, 1795",
     "Committed at Philadelphia December 25, 1794; pardoned November 2, 1795 and released that day or shortly afterward."],
    ["Joseph Posey", "joseph-posey", null, "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not located."],
    ["Marmaduke Curtis", "marmaduke-curtis", null, "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not located."],
    ["Thomas Burney", "thomas-burney", null, "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not located."],
    ["Joseph Scott", "joseph-scott", null, "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not located."],
    ["Caleb Mountz", "caleb-mountz", null, "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not located."],
    ["Isaac Walker", "isaac-walker", null, "phl", [1794,12,25], null, null,
     "Committed at Philadelphia December 25, 1794; exact release date not located."],

    // -- Bedford County four (new) --
    ["Herman Husband", "herman-husband", null, "bed", [1794,10,20], [1795,6,4], "No — acquitted June 4, 1795",
     "Captured in the Bedford County militia operation October 20, 1794; committed at Philadelphia October 29, 1794; acquitted and released June 4, 1795 or immediately afterward."],
    ["Robert Philson", "robert-philson", null, "bed", [1794,10,20], [1795,6,4], "No — acquitted June 4, 1795",
     "Captured in the Bedford County militia operation October 20, 1794; committed at Philadelphia October 29, 1794; acquitted and released June 4, 1795 or immediately afterward."],
    ["George Wisegarver", "george-wisegarver", "George Wisecarver", "bed", [1794,10,20], [1795,2,null], null,
     "Captured in the Bedford County militia operation October 20, 1794; committed at Philadelphia October 29, 1794; released on bail during February 25 to 27, 1795."],
    ["George Lucas", "george-lucas", null, "bed", [1794,10,20], [1795,2,null], null,
     "Captured in the Bedford County militia operation October 20, 1794; committed at Philadelphia October 29, 1794; had secured release on bail by February 19, 1795 -- exact day unknown."],

    // -- Later federal commitments (new) --
    ["John Mitchell", "john-mitchell", null, "fed", [1795,1,19], [1795,11,2], "Convicted of treason; pardoned by President Washington on November 2, 1795",
     "Sent east under federal authority January 19, 1795; documented federal custody of about 287 days, ending with the pardon of November 2, 1795."],
    ["John Cresswell", "john-cresswell", null, "fed", null, null, null,
     "Philadelphia commitment date not legible in the surviving published records; exact release date not located."],
    ["William Bonham", "william-bonham", null, "fed", null, null, "No treason indictment — the grand jury returned a lesser misdemeanor charge",
     "Exact commitment and release dates not located."],

    // -- Short-term Washington, Pennsylvania prisoners (existing) --
    ["John Powers", "john-powers", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["John Munn", "john-munn", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["John Flanagan", "john-flanagan", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["John Crawford Jr.", "john-crawford", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["John Gaston", "john-gaston", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["John Husy", "john-husy", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["John McGill", "john-mcgill", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["Robert Martin", "robert-martin", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["Nathaniel Martin", "nathaniel-martin", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["Thomas McComb", "thomas-mccomb", null, "wash", [1794,11,15], null, null,
     "Listed once as David McComb. In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["James Robinson", "james-robinson", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["William Johnson", "william-johnson", null, "wash", [1794,11,15], null, null,
     "In custody at Washington, Pennsylvania by November 15, 1794; released in western Pennsylvania between November 15 and December 25, 1794 -- exact date unknown."],
    ["David Lock", "david-lock", "David Locke", "wash", [1794,11,15], [1794,11,24], "Never tried — escaped November 24, 1794",
     "In custody at Washington, Pennsylvania by November 15, 1794; escaped November 24, 1794. The escape was announced in the proclamation of General Henry Lee dated November 24."],

    // -- Other documented custody (new) --
    ["Peter Lisle", "peter-lisle", "Peter Lyle", "oth", null, [1794,11,24], "Never tried — escaped November 24, 1794",
     "Starting date of custody unknown; escaped federal detention November 24, 1794. The escape was announced in the proclamation of General Henry Lee dated November 24."],
    ["Thomas Lapsley", "thomas-lapsley", null, "oth", [1794,12,5], null, null,
     "Documented as jailed by December 5, 1794; later permitted to testify for the government; physical release date unknown."],
    ["Benjamin Parkinson", "benjamin-parkinson", null, "oth", null, null, null,
     "Surrendered but was paroled rather than jailed long-term; parole date unknown. He later avoided further custody."],
    ["John Holcroft", "john-holcroft", null, "oth", null, null, "Formally discharged June 5, 1795 after appearing voluntarily in court",
     "Surrendered and cooperated with prosecutors; no confirmed long Philadelphia jail term. June 5, 1795 is a legal discharge date, not necessarily release from physical confinement."],
];

$charges = [
    "phl" => "Treason and related federal charges arising from the Whiskey Rebellion",
    "bed" => "Treason and related federal charges arising from the Whiskey Rebellion (Bedford County arrests)",
    "fed" => "Treason and related federal charges arising from the Whiskey Rebellion",
    "wash" => "Detained at Washington, Pennsylvania during the federal crackdown on the Whiskey Rebellion",
    "oth" => "Federal custody arising from the Whiskey Rebellion",
];

$created = 0; $updated = 0;
foreach ($entries as [$label, $slug, $aka, $grp, $inc, $rel, $conv, $note]) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first()
        ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%".$label."%")->first();

    if (! $p) {
        $parts = preg_split("/\\s+/", trim(str_replace(" Jr.", "", $label)));
        $first = $parts[0];
        $last = count($parts) > 1 ? $parts[count($parts) - 1] : null;
        $p = Prisoner::create([
            "name" => $label, "first_name" => $first, "last_name" => $last,
            "aka" => $aka, "gender" => "Male", "state" => "Pennsylvania",
            "era" => $era, "ideologies" => $ideo, "affiliation" => $affil,
            "in_custody" => false, "released" => true,
            "description" => $label." was among the western Pennsylvania men taken into federal custody during the suppression of the Whiskey Rebellion of 1794. ".$note,
        ]);
        $created++;
        echo "created {$p->name} (slug {$p->slug})\n";
    } else {
        if ($aka && ! $p->aka) { $p->aka = $aka; }
        $p->in_custody = false;
        $p->awaiting_trial = false;
        $p->released = true;
        $p->save();
        $updated++;
        echo "updating {$p->name}\n";
    }

    $c = $p->cases()->where(fn ($q) => $q
            ->where("charges", "like", "%hiskey%")
            ->orWhere("charges", "like", "%reason%")
            ->orWhere("charges", "like", "%nsurrection%"))->first()
        ?? $p->cases()->orderBy("created_at")->first();
    if (! $c) {
        $inst = in_array($grp, ["phl", "bed", "fed"]) ? $instPhl : ($grp === "wash" ? $instWash : null);
        $c = $p->cases()->create(array_filter([
            "charges" => $charges[$grp],
            "institution_id" => $inst ? $inst->id : null,
        ]));
    }
    if ($inc) { $c->setPartialDate("incarceration_date", $inc[0], $inc[1], $inc[2]); }
    if ($rel) { $c->setPartialDate("release_date", $rel[0], $rel[1], $rel[2]); }
    if ($conv) { $c->convicted = $conv; }
    if (! $c->sentence) { $c->sentence = $note; }
    $c->save();
    echo "  case: inc=".($c->incarceration_date ?: "-")." rel=".($c->release_date ?: "-")." days=".($c->imprisoned_for_days ?? "null")."\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done. Created {$created}, updated {$updated}.\n";
echo "Now run: php artisan prisoners:place-zero-sort to place the new records.\n";
'

echo
echo "Done."
