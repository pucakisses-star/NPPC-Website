#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_william_lee_brent_case.sh
#
# Adds a case row to William Lee Brent (Black Panther Party) covering
# his June 17, 1969 hijacking of TWA Flight 154 from Oakland to Havana
# and his subsequent 37-year exile in Cuba until his death from
# pneumonia on November 4, 2006. Brent was awaiting trial on November
# 1968 San Francisco shootout charges (Panther newspaper-distribution
# dispute) when he hijacked the flight. Cuba detained him for ~22
# months on the hijacking, then granted political asylum. He never
# returned to the US and was never extradited.
#
# Source: William Lee Brent, Long Time Gone (1996, autobiography);
# William Lee Brent obituary, New York Times, November 21, 2006.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "William Lee Brent")
    ->orWhere("name", "like", "%William Lee Brent%")
    ->orWhere("name", "like", "%William Brent%")
    ->first();

if (!$p) {
    echo "ERROR: William Lee Brent not found\n";
    exit(1);
}

// Always backfill missing prisoner-level dates and flags. Brent
// died in Havana of pneumonia on November 4, 2006 after 37 years
// in Cuban exile.
$dirty = false;
if (!$p->birthdate) { $p->birthdate = "1930-09-13"; $dirty = true; }
if (!$p->death_date) { $p->death_date = "2006-11-04"; $dirty = true; }
if ($p->in_custody) { $p->in_custody = false; $dirty = true; }
// Never returned, died in exile - so currently_in_exile is false
// (he is no longer alive) but in_exile (historical) should be true.
if (!$p->in_exile) { $p->in_exile = true; $dirty = true; }
if ($p->currently_in_exile) { $p->currently_in_exile = false; $dirty = true; }
if ($dirty) {
    $p->save();
    echo "Updated William Lee Brent prisoner record (birthdate=1930-09-13, death_date=2006-11-04, exile flags).\n";
}

if ($p->cases()->exists()) {
    echo "William Lee Brent already has cases (count={$p->cases()->count()}); skipping case insert.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Republic of Cuba (political asylum)"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"          => $p->id,
    "institution_id"       => $inst->id,
    "charges"              => "Federal aircraft piracy (49 U.S.C. § 1472(i)) for the June 17, 1969 hijacking of TWA Flight 154 from Oakland International Airport, diverted at gunpoint to Jose Marti Airport in Havana. Underlying California state charges (San Francisco County): assault with intent to commit murder of three SFPD officers in a November 18, 1968 shootout that began as a dispute over Black Panther newspaper distribution. Brent was out on bail awaiting trial on the SF charges when he hijacked the flight to escape prosecution.",
    "arrest_date"          => "1968-11-18",
    "incarceration_date"   => "1969-06-17",
    "in_exile_since"       => "1969-06-17",
    "end_of_exile"         => "2006-11-04",
    "death_in_custody_date"=> "2006-11-04",
    "convicted"            => "Never tried in the US - fled before trial on the November 1968 SF charges; federal hijacking indictment never adjudicated; Cuba refused all US extradition requests.",
    "sentence"             => "Detained by Cuban authorities for approximately 22 months following the hijacking, then granted political asylum in 1971. Lived in exile in Havana for 37 years (June 17, 1969 - November 4, 2006), working as a journalist and English teacher and writing the memoir Long Time Gone (1996). Died in Havana of pneumonia on November 4, 2006, age 76."
]);

echo "Inserted William Lee Brent case (1969-2006 hijacking + Cuba exile, 37 years).\n";
'

echo
echo "William Lee Brent case add complete."
