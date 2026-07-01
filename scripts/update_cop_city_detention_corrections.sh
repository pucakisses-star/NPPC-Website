#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_cop_city_detention_corrections.sh
# Corrects pre-trial detention durations for two January 21, 2023 defendants:
#   Ivan Ferguson: 2 days → 25 days (bond set Jan 23 but not posted until Feb 15)
#   Emily Murphy:  several weeks → 103 days (ACDC then Fulton County Jail, released May 4)
set +e

cat > /tmp/update_cop_city_detentions.php << 'PHPEOF'
<?php

// Ivan Ferguson: correcting from 2 days to 25 days
$ferguson = App\Models\Prisoner::withoutGlobalScopes()
    ->where('slug', 'ivan-ferguson')
    ->firstOrFail();
$ferguson->description = 'Ivan James Ferguson, 23, is a classical clarinetist from Henderson, Nevada who graduated from the San Francisco Conservatory of Music in 2021 and had performed as a soloist with the Henderson Symphony Orchestra and Berkeley Symphony. On January 21, 2023, he was arrested during a protest march through downtown Atlanta called in response to the police killing of Manuel "Tortuguita" Paez Terán three days earlier. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Ferguson was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. He was held in Fulton County Jail; though bond of over $355,000 was set on January 23, he could not immediately post the amount and was not released until February 15, 2023 — 25 days after his arrest. He was released on ankle monitor with a 24-hour curfew, with exceptions for school, work, legal meetings, and religious services, and ordered to have no contact with co-defendants. In September 2023 he was included in a 61-person RICO indictment; the RICO charge was dismissed December 30, 2025. His domestic terrorism and arson charges remained pending as of July 2026.';
$ferguson->save();
$fergusonCase = $ferguson->cases()->first();
$fergusonCase->imprisoned_for_days = 25;
$fergusonCase->release_date = '2023-02-15';
$fergusonCase->sentence = 'No conviction; 25 days pre-trial detention; bond set January 23 but not posted until February 15, 2023';
$fergusonCase->save();
echo 'Updated: ' . $ferguson->name . PHP_EOL;

// Emily Murphy: correcting from "several weeks" to 103 days (released May 4, 2023)
$murphy = App\Models\Prisoner::withoutGlobalScopes()
    ->where('slug', 'emily-murphy')
    ->firstOrFail();
$murphy->description = 'Emily Murphy, 37, is an activist from Grosse Ile, Michigan. On January 21, 2023, she was arrested during a protest march through downtown Atlanta called in response to the police killing of Manuel "Tortuguita" Paez Terán three days earlier. Demonstrators vandalized the Atlanta Police Foundation building, smashed windows at a Wells Fargo branch, and set an APD patrol car on fire. Murphy was the oldest of the six arrested and was charged with domestic terrorism, first-degree arson, second-degree criminal damage to property, interference with government property, rioting, unlawful assembly, obstruction of a law enforcement officer, and pedestrian in a roadway. She was denied bond on January 23, 2023 and held first at the Atlanta City Detention Center — the subject of a Vice article reporting on her detention there — before being transferred to Fulton County Jail. A consent bond was agreed to on April 24, 2023, but Murphy voluntarily chose to remain jailed in order to force her preliminary hearing to proceed. She was released on May 4, 2023 — 103 days after her arrest — after Judge Ashley Drake upheld all eight charges, including domestic terrorism, at the preliminary hearing. In September 2023 she was included in a 61-person RICO indictment; the RICO charge was dismissed December 30, 2025. Her domestic terrorism and arson charges remained pending as of July 2026.';
$murphy->save();
$murphyCase = $murphy->cases()->first();
$murphyCase->imprisoned_for_days = 103;
$murphyCase->release_date = '2023-05-04';
$murphyCase->sentence = 'No conviction; 103 days pre-trial detention (Atlanta City Detention Center, then Fulton County Jail); released May 4, 2023';
$murphyCase->save();
echo 'Updated: ' . $murphy->name . PHP_EOL;
PHPEOF

echo "Applying detention corrections via tinker..."
php artisan tinker --execute="require '/tmp/update_cop_city_detentions.php';"
echo "Done."
