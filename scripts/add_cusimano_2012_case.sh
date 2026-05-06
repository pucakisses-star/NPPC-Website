#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cusimano_2012_case.sh
#
# Adds Theresa Cusimano's 2012 SOA Watch federal sentence as a second
# case row alongside her existing 2007 case. Drawn from the Nuclear
# Resister letter dated July 20, 2012:
#   https://www.nukeresister.org/2012/07/20/a-letter-from-theresa-cusimano-soa-watch-prisoner-of-conscience/
#
# Source-derived facts:
#   - Action: SOA Watch line-crossing onto Fort Benning, Georgia at
#     the November 2011 annual vigil commemorating the 1989 UCA
#     Jesuit massacre (the line-crossing weekend that year was the
#     20th anniversary of the closing of the Maryknoll-led vigils).
#   - Sentenced January 13, 2012 by U.S. Magistrate Judge Stephen
#     Hyles, Middle District of Georgia (Columbus), to 6 months
#     federal prison for trespass at WHINSEC.
#   - Held across five federal facilities during transfers; finally
#     released July 11, 2012 from the Federal Medical Center,
#     Carswell, in Fort Worth, Texas.
#   - BOP register number 93611-020 (same as on her 2007 record).
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Theresa Cusimano")->first();
if (!$p) {
    echo "ERROR: Theresa Cusimano not found\n";
    exit(1);
}

$existing = $p->cases()
    ->whereYear("incarceration_date", 2012)
    ->orWhere(function ($q) {
        $q->where("charges", "like", "%2011%")
          ->orWhere("sentence", "like", "%Carswell%");
    })
    ->first();

if ($existing) {
    echo "2012 SOA case already exists for Cusimano (id={$existing->id}); skipping.\n";
    exit(0);
}

$inst = App\Models\Institution::firstOrCreate(
    ["name" => "Federal Medical Center, Carswell"],
    ["city" => "Fort Worth", "state" => "Texas"]
);

App\Models\PrisonerCase::create([
    "prisoner_id"        => $p->id,
    "institution_id"     => $inst->id,
    "charges"            => "Federal trespass on a U.S. Army installation - line-crossing onto Fort Benning, Georgia at the November 2011 annual SOA Watch vigil in protest of the U.S. Army School of the Americas / Western Hemisphere Institute for Security Cooperation (WHINSEC). The annual vigil commemorates the December 1989 assassination of six Jesuit priests, their housekeeper Elba Ramos, and her daughter Celina at the University of Central America in San Salvador by Salvadoran soldiers trained at the SOA.",
    "arrest_date"        => "2011-11-20",
    "incarceration_date" => "2012-01-13",
    "sentenced_date"     => "2012-01-13",
    "release_date"       => "2012-07-11",
    "sentence"           => "6 months federal prison. Held across five federal facilities during transfers; released from the Federal Medical Center, Carswell, in Fort Worth, Texas on July 11, 2012. BOP register number 93611-020.",
    "convicted"          => "Yes - bench verdict before U.S. Magistrate Judge Stephen Hyles, Middle District of Georgia (Columbus), January 13, 2012",
    "judge"              => "Stephen Hyles (U.S. Magistrate Judge)",
]);

echo "Inserted Theresa Cusimano 2012 SOA case (Jan 13 - Jul 11, 2012, FMC Carswell).\n";
'

echo
echo "Cusimano 2012 case add complete."
