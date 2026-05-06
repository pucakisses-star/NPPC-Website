#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_ron_porambo_bio.sh
#
# Replaces Ron Porambo's description with the editor-supplied text
# covering his 1967 Newark Riots reporting, two attempts on his
# life, and his bribery conviction for paying a police officer $50
# for autopsy photos used in his reporting.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Ron Porambo")
    ->orWhere("name", "like", "%Porambo%")
    ->first();

if (!$p) {
    echo "ERROR: Ron Porambo not found\n";
    exit(1);
}

$bio = "Ron Porambo was a journalist who covered the 1967 Newark Riots which began when police pulled over a cab driver for flashing his high beams to a police cruiser and then brutally beat him. Over the course of the next five days police used over 13,319 rounds of live ammunition against civilians and killed 23 people. Porambo interviewed witnesses who testified that civilians killed by police had been shot indiscriminately without cause or warning. Shortly after publishing a book on the riots Porambo was the victim of a drive by shooting. A month later he shot twice in the chest by an unknown assailant while leaving a bar. Police refused to investigate the attempts on Porambos life and a police spokesperson claimed without evidence that they were publicity stunt by Porambo to promote his book. After surviving the second assassination attempt the police arrested Porambo on bribery charges for paying a police officer \$50 for autopsy photos from victims of the 1967 Newark riots which he used to show that physical injuries on the bodies differed from what was claimed in police reports. Porambo was convicted on the bribery charges and sentenced to 3 months in jail.";

$p->description = $bio;
$p->save();
echo "Updated Ron Porambo description (" . strlen($bio) . " chars).\n";
'

echo
echo "Ron Porambo bio update complete."
