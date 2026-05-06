#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/apply_bop_dry_run_patches.sh
#
# Applies the patches that came out of the prisoners:lookup-bop-by-name
# dry-run, without re-querying the BOP locator (avoids the WAF rate
# limit). Each patch is keyed on the canonical prisoner name.
#
# Skipped (false positives in the dry-run output):
#   - Edward Snowden   - never been in BOP custody (locator matched
#                        a different inmate of the same name).
#   - Keith LaMar      - Ohio state death row, not federal.
#
# Idempotent: each patch only writes a field if the current value is
# empty (or, for released/in_custody flags, only flips when the BOP
# record affirmatively indicates released).
set -e

php artisan tinker --execute='
$patches = [
    ["name" => "Abdulrahman Odeh", "inmate_number" => "26548-050"],
    ["name" => "Abdulrahman Odeh Odeh", "inmate_number" => "26548-050", "release_date" => "2021-08-20"],
    ["name" => "Adham Hassoun", "inmate_number" => "72433-004"],
    ["name" => "Adolfo Matos", "inmate_number" => "88968-024"],
    ["name" => "Akram Abdallah", "inmate_number" => "88821-008"],
    ["name" => "Alam Zeb", "inmate_number" => "36932-053", "release_date" => "1995-10-18"],
    ["name" => "Alan Berkman", "inmate_number" => "35049-066"],
    ["name" => "Ali Al-Timimi", "inmate_number" => "48054-083"],
    ["name" => "Ana Montes", "inmate_number" => "25037-016"],
    ["name" => "Andrés Santos Ortiz", "inmate_number" => "25534-069"],
    ["name" => "Armin Harcevic", "inmate_number" => "20321-111", "release_date" => "2024-01-25"],
    ["name" => "Augustine Roddy", "inmate_number" => "93648-020", "release_date" => "2008-04-30"],
    ["name" => "Axel Mora Meléndez", "inmate_number" => "21699-069", "release_date" => "2002-05-10"],
    ["name" => "Benamar Benatta", "inmate_number" => "51430-054", "release_date" => "2002-04-23"],
    ["name" => "Bernardine Dohrn", "inmate_number" => "04556-054", "released" => true],
    ["name" => "Branden Wolfe", "inmate_number" => "22425-041"],
    ["name" => "Brian DiPippa", "inmate_number" => "66590-510"],
    ["name" => "Burson Augustin", "inmate_number" => "76734-004"],
    ["name" => "Carol Ann Manning", "inmate_number" => "10375-016", "release_date" => "1993-02-05"],
    ["name" => "Casey Goonan", "inmate_number" => "24611-511"],
    ["name" => "Charles Booker-Hirsch", "inmate_number" => "90961-020", "release_date" => "2002-12-06"],
    ["name" => "Chelsea Gerlach", "inmate_number" => "69097-065"],
    ["name" => "Ciaron OReilly", "inmate_number" => "03810-052", "released" => true],
    ["name" => "Claude Marks", "inmate_number" => "38771-079"],
    ["name" => "Cliff Frazier", "inmate_number" => "03056-087", "release_date" => "2008-05-23"],
    ["name" => "Craig \"Critter\" Marshall", "inmate_number" => "17424-112"],
    ["name" => "Daniel Duggan", "inmate_number" => "53696-074", "in_custody" => false, "released" => true, "release_date" => "2023-11-09"],
    ["name" => "Dean Hammer", "inmate_number" => "01267-052"],
    ["name" => "Derrlyn Tom", "inmate_number" => "91362-020", "release_date" => "2003-12-05"],
    ["name" => "Dorothy M. Hennessey", "inmate_number" => "90287-020", "release_date" => "2002-01-14"],
    ["name" => "Dorothy Pagosa", "inmate_number" => "91387-020", "release_date" => "2003-07-02"],
    ["name" => "Douglas Joshua Ellerman", "inmate_number" => "06378-081"],
    ["name" => "Douglas Kasper", "inmate_number" => "91395-020", "release_date" => "2003-05-08"],
    ["name" => "Dustin Stevens", "inmate_number" => "08969-280", "release_date" => "2012-07-27"],
    ["name" => "Dylan Robinson", "inmate_number" => "45802-013"],
    ["name" => "Dylcia Pagan", "inmate_number" => "88971-024"],
    ["name" => "Eddie Hatcher", "inmate_number" => "11759-056"],
    ["name" => "Edith Balot", "inmate_number" => "91320-020", "release_date" => "2003-07-03"],
    ["name" => "Edward Cummings", "inmate_number" => "48919-066"],
    ["name" => "Elizabeth Ann Duke", "inmate_number" => "35048-066", "release_date" => "1985-07-31"],
    ["name" => "Elizabeth Anne McKenzie", "inmate_number" => "90291-020", "release_date" => "2002-01-14"],
    ["name" => "Ellie Brett", "inmate_number" => "14822-509"],
    ["name" => "Elmer Maas", "inmate_number" => "10016-014"],
    ["name" => "Emadeddin Muntasser", "inmate_number" => "80512-038"],
    ["name" => "Emily Harris", "inmate_number" => "59470-510"],
    ["name" => "Erin Sieber", "inmate_number" => "27024-083"],
    ["name" => "Felton Davis", "inmate_number" => "10921-050"],
    ["name" => "Freddie Hilton", "inmate_number" => "00010-099", "in_custody" => false, "released" => true, "release_date" => "2006-01-23"],
    ["name" => "Gavin Weslee Perry", "inmate_number" => "60251-177", "released" => true, "release_date" => "2021-03-19"],
    ["name" => "Gerhard Fischer", "inmate_number" => "89759-020", "release_date" => "2000-11-03"],
    ["name" => "Harold A. Penner", "inmate_number" => "21471-069"],
    ["name" => "Hassan Abu-Jihaad", "inmate_number" => "86496-008"],
    ["name" => "Hazel Tulecke", "inmate_number" => "90295-020", "release_date" => "2001-10-12"],
    ["name" => "Irfan Khan", "inmate_number" => "57803-112", "release_date" => "2012-03-26"],
    ["name" => "Israel Medina Colón", "inmate_number" => "25857-069"],
    ["name" => "Izhar Khan", "inmate_number" => "95229-004"],
    ["name" => "Jaan Karl Laaman", "inmate_number" => "10372-016", "in_custody" => false, "released" => true, "release_date" => "2021-05-15"],
    ["name" => "Jacob Conroy", "inmate_number" => "93501-011", "release_date" => "2010-05-04"],
    ["name" => "James Burmeister", "inmate_number" => "32672-037"],
    ["name" => "James Cromitie", "inmate_number" => "70658-054"],
    ["name" => "Jamshid Muhtorov", "inmate_number" => "42383-424", "release_date" => "2021-06-18"],
    ["name" => "Janice Sevre-Duszynska", "inmate_number" => "91104-020"],
    ["name" => "Janye Waller", "release_date" => "2024-11-27"],
    ["name" => "Jean Gump", "inmate_number" => "03789-045", "released" => true],
    ["name" => "Jennifer Rose", "inmate_number" => "24253-510"],
    ["name" => "Jessica Carr", "inmate_number" => "91389-020", "release_date" => "2003-07-03"],
    ["name" => "Joel Kilgour", "inmate_number" => "90290-020", "release_date" => "2001-08-13"],
    ["name" => "John Anthony Robles", "inmate_number" => "27333-298", "released" => true, "release_date" => "2013-12-30"],
    ["name" => "John Bachman", "inmate_number" => "22732-037"],
    ["name" => "John Dear", "inmate_number" => "01107-049"],
    ["name" => "John Ewers", "inmate_number" => "90284-020", "release_date" => "2002-01-14"],
    ["name" => "John Heid", "inmate_number" => "13815-016", "release_date" => "2003-04-07"],
    ["name" => "John Honeck", "inmate_number" => "89761-020", "release_date" => "2000-11-03"],
    ["name" => "John Kiriakou", "inmate_number" => "79637-083"],
    ["name" => "John LaForge", "inmate_number" => "03213-090"],
    ["name" => "John Schuchardt", "inmate_number" => "11409-083"],
    ["name" => "Jorge Enrique Rodríguez Mendieta", "inmate_number" => "09778-004", "in_custody" => false, "released" => true, "release_date" => "1986-05-27"],
    ["name" => "Joseph Austin Gaskins", "inmate_number" => "53707-511", "release_date" => "2025-10-30"],
    ["name" => "Joseph Buddenberg", "inmate_number" => "12746-111", "release_date" => "2018-02-07"],
    ["name" => "Joseph DeRaymond", "inmate_number" => "92569-020", "release_date" => "2006-07-06"],
    ["name" => "Joseph Dibee", "inmate_number" => "98288-011", "release_date" => "2019-12-20"],
    ["name" => "Joshua Schulte", "inmate_number" => "79471-054"],
    ["name" => "José Meléndez Cotto", "inmate_number" => "21615-069"],
    ["name" => "José R. Rivera Santana", "inmate_number" => "21607-069"],
    ["name" => "Joy Mitchell", "inmate_number" => "06893-031"],
    ["name" => "Juan Segarra Palmer", "inmate_number" => "15357-077"],
    ["name" => "Judith Clark", "inmate_number" => "08627-054"],
    ["name" => "Judith Kelly", "inmate_number" => "91372-020", "release_date" => "2003-07-25"],
    ["name" => "Judy Bierbaum", "inmate_number" => "89756-020", "release_date" => "2003-11-17"],
    ["name" => "Justin Samuel", "inmate_number" => "04742-090"],
    ["name" => "Kamau Sadiki", "inmate_number" => "00010-099", "in_custody" => false, "released" => true, "release_date" => "2006-01-23"],
    ["name" => "Katherine Ann Power", "inmate_number" => "19724-038"],
    ["name" => "Kathleen Boylan", "inmate_number" => "20047-016", "release_date" => "2002-12-06"],
    ["name" => "Kathleen Desautels", "inmate_number" => "90966-020", "release_date" => "2003-03-07"],
    ["name" => "Kathleen Fisher", "inmate_number" => "18450-379", "release_date" => "2014-10-02"],
    ["name" => "Kevin Mitnick", "inmate_number" => "89950-012", "release_date" => "2000-01-21"],
    ["name" => "Khalif Miller", "in_custody" => false, "released" => true, "release_date" => "2025-02-14"],
    ["name" => "Kimberly Rivera", "inmate_number" => "05981-033"],
    ["name" => "Laguerre Payen", "inmate_number" => "85165-054"],
    ["name" => "Laura MacDonald", "inmate_number" => "90959-020", "release_date" => "2002-10-08"],
    ["name" => "Laura Slattery", "inmate_number" => "91364-020", "release_date" => "2003-07-24"],
    ["name" => "Lauren Gazzola", "inmate_number" => "93497-011", "release_date" => "2010-08-13"],
    ["name" => "Lauren Handy", "inmate_number" => "93984-007"],
    ["name" => "Leeanne Clausen", "inmate_number" => "93651-020", "release_date" => "2008-05-01"],
    ["name" => "Lois Putzier", "inmate_number" => "90292-020", "release_date" => "2002-01-15"],
    ["name" => "Luz Maria Berrios-Berrios", "inmate_number" => "24582-004"],
    ["name" => "Lyglenson Lemorin", "inmate_number" => "58225-019"],
    ["name" => "Mahmoud Khalil", "inmate_number" => "13875-112"],
    ["name" => "Maliki Shakur Latine", "inmate_number" => "04838-067"],
    ["name" => "Margaret Knapke", "inmate_number" => "89762-020", "release_date" => "2000-10-27"],
    ["name" => "Marie Salupo", "inmate_number" => "91330-020", "release_date" => "2003-07-01"],
    ["name" => "Mariel Torres Lara", "inmate_number" => "25533-069"],
    ["name" => "Mark Siljander", "inmate_number" => "20611-045", "release_date" => "2013-01-11"],
    ["name" => "Matthew DeHart", "inmate_number" => "06813-036", "release_date" => "2019-10-03"],
    ["name" => "Matthew Rupert", "inmate_number" => "55013-424"],
    ["name" => "Mauro Simpson", "inmate_number" => "25511-069", "release_date" => "2002-10-25"],
    ["name" => "Mehrdad Moein Ansari", "inmate_number" => "48033-480"],
    ["name" => "Michael Billington", "inmate_number" => "15194-083", "released" => true],
    ["name" => "Michael Sprong", "inmate_number" => "11416-047"],
    ["name" => "Miriam Spencer", "inmate_number" => "90294-020", "release_date" => "2002-02-07"],
    ["name" => "Mohamed Albanna", "inmate_number" => "11947-055"],
    ["name" => "Montez Lee", "inmate_number" => "22429-041"],
    ["name" => "Mubarak Hamed", "inmate_number" => "19704-045"],
    ["name" => "Murugesu Vinayagamoorthy", "inmate_number" => "64077-053"],
    ["name" => "Nachimuthu Socrates", "inmate_number" => "16922-014"],
    ["name" => "Nadarasa Yogarasa", "inmate_number" => "64074-053"],
    ["name" => "Narseal Batiste", "inmate_number" => "76736-004"],
    ["name" => "Nasser Ahmed", "inmate_number" => "42963-054", "release_date" => "1999-11-29"],
    ["name" => "Nathan Block", "inmate_number" => "36359-086", "release_date" => "2012-10-29"],
    ["name" => "Naudimar Herrera", "inmate_number" => "76731-004"],
    ["name" => "Nazael Montalvo Rodríguez", "inmate_number" => "21698-069", "release_date" => "2002-05-10"],
    ["name" => "Nihad Rosic", "inmate_number" => "12622-028", "release_date" => "2023-12-05"],
    ["name" => "Noureddine Malki", "inmate_number" => "63740-053"],
    ["name" => "Onta Williams", "inmate_number" => "83614-054"],
    ["name" => "Palmer Legare", "inmate_number" => "91097-020", "release_date" => "2002-12-06"],
    ["name" => "Pamela McBride", "inmate_number" => "91359-020", "release_date" => "2003-10-03"],
    ["name" => "Patrick Abraham", "inmate_number" => "76737-004"],
    ["name" => "Patrick Lincoln", "inmate_number" => "91400-020", "release_date" => "2003-12-08"],
    ["name" => "Paul Kabat", "inmate_number" => "03229-045"],
    ["name" => "Paul Magno", "inmate_number" => "03829-018", "release_date" => "1986-02-21"],
    ["name" => "Peter DeMott", "inmate_number" => "10891-083"],
    ["name" => "Piratheepan Nadarajah", "inmate_number" => "81874-053"],
    ["name" => "Pratheepan Thavaraja", "inmate_number" => "74737-053"],
    ["name" => "Rachel Montgomery", "inmate_number" => "91367-020", "release_date" => "2003-10-24"],
    ["name" => "Rae Kramer", "inmate_number" => "91096-020", "release_date" => "2003-03-07"],
    ["name" => "Ramanan Mylvaganam", "inmate_number" => "77608-053"],
    ["name" => "Raymond Luc Levasseur", "inmate_number" => "10376-016", "release_date" => "2004-11-04"],
    ["name" => "Rebecca Kanner", "inmate_number" => "90278-020", "release_date" => "2002-01-14"],
    ["name" => "Renea Goddard", "inmate_number" => "22810-509", "release_date" => "2025-01-03"],
    ["name" => "Rev. Al Sharpton", "inmate_number" => "21458-069"],
    ["name" => "Ricardo Santos Ortiz", "inmate_number" => "25535-069"],
    ["name" => "Richard Hunsinger", "inmate_number" => "16066-509"],
    ["name" => "Richard John Kinane", "inmate_number" => "90279-020", "release_date" => "2002-01-14"],
    ["name" => "Robert Thaxton", "inmate_number" => "26285-014"],
    ["name" => "Robin Long", "inmate_number" => "11004-064"],
    ["name" => "Roger Ver", "inmate_number" => "99722-111"],
    ["name" => "Roy Bourgeois", "inmate_number" => "83274-020"],
    ["name" => "Sabri Benkahla", "inmate_number" => "46867-083"],
    ["name" => "Sahilal Sabaratnam", "inmate_number" => "64073-053"],
    ["name" => "Sami Al-Arian", "inmate_number" => "40939-018", "release_date" => "2008-04-11"],
    ["name" => "Santos Rubén Hernández García", "inmate_number" => "21700-069", "release_date" => "2002-05-10"],
    ["name" => "Sathajhan Sarachandran", "inmate_number" => "64076-053"],
    ["name" => "Scott Schaeffer-Duffy", "inmate_number" => "91332-020", "release_date" => "2003-04-23"],
    ["name" => "Seamus Moley", "inmate_number" => "27800-004", "release_date" => "1996-07-16"],
    ["name" => "Shamar N. Betts", "inmate_number" => "22080-043"],
    ["name" => "Sharon Scranage", "inmate_number" => "13087-083", "release_date" => "1987-05-01"],
    ["name" => "Sonja Andreas", "inmate_number" => "91328-020", "release_date" => "2003-08-01"],
    ["name" => "Sr. Caryl Hartjes", "inmate_number" => "91376-020", "release_date" => "2003-07-03"],
    ["name" => "Sr. Moira Kenny", "inmate_number" => "91369-020", "release_date" => "2003-10-03"],
    ["name" => "Stanislas Meyerhoff", "inmate_number" => "11121-084"],
    ["name" => "Stanley Grant Phanor", "inmate_number" => "64959-004"],
    ["name" => "Stephen Kim", "inmate_number" => "33315-016"],
];

$applied = 0; $missing = 0; $noChange = 0;
foreach ($patches as $patch) {
    // Try canonical name; for OReilly entry, also accept apostrophe form
    $name = $patch["name"];
    $p = App\Models\Prisoner::where("name", $name)
        ->orWhere("name", "Ciaron O\u{2019}Reilly")
        ->orWhere("name", "Ciaron O\x27Reilly")
        ->first();
    // Re-narrow if we hit the OReilly fallback for a different patch
    if ($p && stripos($name, "OReilly") === false && stripos($p->name, "OReilly") !== false) {
        $p = App\Models\Prisoner::where("name", $name)->first();
    }
    if (!$p) {
        echo "missing: {$name}\n";
        $missing++;
        continue;
    }

    $changed = false;

    if (!empty($patch["inmate_number"]) && empty($p->inmate_number)) {
        $p->inmate_number = $patch["inmate_number"];
        $changed = true;
    }
    if (array_key_exists("released", $patch) && $patch["released"] === true && !$p->released) {
        $p->released = true;
        $changed = true;
    }
    if (array_key_exists("in_custody", $patch) && $patch["in_custody"] === false && $p->in_custody) {
        $p->in_custody = false;
        $changed = true;
    }
    if ($changed) $p->save();

    $caseChanged = false;
    if (!empty($patch["release_date"])) {
        $case = $p->cases()->orderByRaw("incarceration_date IS NULL, incarceration_date DESC")->first();
        if ($case && empty($case->release_date)) {
            $case->release_date = $patch["release_date"];
            $case->save();
            $caseChanged = true;
        }
    }

    if ($changed || $caseChanged) {
        $applied++;
        echo "patched: {$p->name}\n";
    } else {
        $noChange++;
    }
}
echo "\nTotal patches:           " . count($patches) . "\n";
echo "Applied:                 {$applied}\n";
echo "No change (already set): {$noChange}\n";
echo "Prisoner not found:      {$missing}\n";
'

echo
echo "BOP dry-run patches applied."
