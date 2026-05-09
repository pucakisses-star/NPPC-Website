#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/jonah_house_prisoner_updates.sh
#
# Apply updates and additions sourced from the Jonah House Plowshares
# prisoner-addresses archive page:
#   https://www.jonahhouse.org/archive/prisoner_addresses.htm
#
# That page provides BOP register numbers, addresses, and the Cuban
# Five roster. Existing NPPC records cross-referenced before running.
#
# - Updates: add inmate_number 58741-004 to Antonio Guerrero (missing).
# - Adds:    Gerardo Hernández Nordelo and Ramón Labañino Salazar.
#            (René González, Fernando González, Antonio Guerrero
#            already exist in the DB; Leonard Peltier, Mumia
#            Abu-Jamal, and Dr. Rafil Dhafir are already present with
#            register numbers matching the Jonah House page.)
# - Skipped: Lance Corporal Joe Glenton (UK military deserter held in
#            a UK Military Corrective Training Centre — not a U.S.
#            political prisoner case).
set -e

# ---- 1. Patch missing inmate_number on Antonio Guerrero ----

php artisan tinker --execute='
$p = App\Models\Prisoner::where("slug","antonio-guerrero")->orWhere("name","Antonio Guerrero")->first();
if ($p) {
    if (! $p->inmate_number) {
        $p->inmate_number = "58741-004";
        $p->save();
        echo "Patched: Antonio Guerrero inmate_number = 58741-004\n";
    } else {
        echo "Antonio Guerrero already has inmate_number = ".$p->inmate_number."\n";
    }
} else {
    echo "Antonio Guerrero not found.\n";
}
'

# ---- 2. Add Gerardo Hernández Nordelo (Cuban Five) ----

php artisan prisoner:add '{"name":"Gerardo Hernández Nordelo","first_name":"Gerardo","last_name":"Hernández","aka":"Manuel Viramontez","description":"Gerardo Hernández Nordelo was the leader of the Wasp Network (La Red Avispa), the Cuban intelligence cell that operated in South Florida from the early 1990s gathering information on right-wing Cuban-American exile groups including Alpha 66, Brothers to the Rescue, and Omega 7 — groups that Cuba accused of carrying out attacks on Cuban tourist infrastructure. Arrested in Miami on September 12, 1998 along with four other agents. Tried in U.S. District Court for the Southern District of Florida. He was convicted in June 2001 of conspiracy to commit espionage, acting as an unregistered foreign agent, and conspiracy to commit murder for his alleged role in providing intelligence that allowed the Cuban Air Force to shoot down two Brothers to the Rescue Cessnas on February 24, 1996, killing four. He was sentenced to two consecutive life sentences plus 15 years and held at USP Victorville and later USP Lompoc. He was released on December 17, 2014 in the U.S.-Cuba prisoner swap brokered by President Barack Obama and Pope Francis, and returned to a hero'"'"'s welcome in Havana.","state":"Florida","race":"Latino","gender":"Male","birthdate":"1965-06-04","ideologies":["Anti-imperialism","Cuban revolutionary"],"affiliation":["Cuban Intelligence Directorate","Wasp Network (La Red Avispa)","Cuban Five"],"era":"1990s","in_custody":false,"released":true,"cases":[{"institution_name":"USP Victorville","institution_city":"Adelanto","institution_state":"California","charges":"18 U.S.C. § 794(c) conspiracy to commit espionage; 18 U.S.C. § 951 acting as an unregistered foreign agent; 18 U.S.C. § 1117 conspiracy to commit murder (in connection with the Feb 24, 1996 shoot-down of two Brothers to the Rescue Cessnas).","arrest_date":"1998-09-12","sentenced_date":"2001-12-12","incarceration_date":"1998-09-12","release_date":"2014-12-17","sentence":"Two consecutive life sentences plus 15 years; sentence commuted by President Barack Obama on Dec 17, 2014 in the U.S.-Cuba prisoner swap.","convicted":"Yes — federal jury verdict June 8, 2001"}]}' || true

# ---- 3. Add Ramón Labañino Salazar (Cuban Five) ----

php artisan prisoner:add '{"name":"Ramón Labañino Salazar","first_name":"Ramón","last_name":"Labañino","aka":"Luís Medina","description":"Ramón Labañino Salazar was a senior officer of the Cuban Intelligence Directorate (DI) and a member of the Wasp Network (La Red Avispa) operating in South Florida in the 1990s under the cover identity Luís Medina. The cell gathered intelligence on right-wing Cuban-American exile organizations — Alpha 66, Brothers to the Rescue, Omega 7 — that the Cuban government accused of armed attacks on Cuban tourist infrastructure. Arrested in Miami on September 12, 1998. Tried in U.S. District Court for the Southern District of Florida and convicted in June 2001 of conspiracy to commit espionage and acting as an unregistered foreign agent. Originally sentenced to life plus 18 years; later resentenced on remand to 30 years. Held at USP Beaumont. He was released on December 17, 2014 in the U.S.-Cuba prisoner swap brokered by President Barack Obama and Pope Francis, and returned to Havana as a national hero.","state":"Florida","race":"Latino","gender":"Male","birthdate":"1963-06-09","ideologies":["Anti-imperialism","Cuban revolutionary"],"affiliation":["Cuban Intelligence Directorate","Wasp Network (La Red Avispa)","Cuban Five"],"era":"1990s","in_custody":false,"released":true,"cases":[{"institution_name":"USP Beaumont","institution_city":"Beaumont","institution_state":"Texas","charges":"18 U.S.C. § 794(c) conspiracy to commit espionage; 18 U.S.C. § 951 acting as an unregistered foreign agent.","arrest_date":"1998-09-12","sentenced_date":"2001-12-12","incarceration_date":"1998-09-12","release_date":"2014-12-17","sentence":"Life plus 18 years; resentenced on remand (Dec 8, 2009) to 30 years; sentence commuted by President Barack Obama on Dec 17, 2014 in the U.S.-Cuba prisoner swap.","convicted":"Yes — federal jury verdict June 8, 2001"}]}' || true

echo
echo "Jonah House cross-reference applied."
