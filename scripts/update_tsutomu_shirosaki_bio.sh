#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_tsutomu_shirosaki_bio.sh
#
# Replaces Tsutomu Shirosakis bio with the editor-supplied text
# covering his 1971 student-era arrest, the 1977 JAL Flight 472
# hostage-swap release to Algeria and onward exile in Lebanon,
# the May 14 1986 mortar attack on the U.S. Embassy in Jakarta
# attributed to AIIB, his 1996 arrest in Nepal and FBI extradition,
# and his 30-year U.S. federal sentence on framing-by-fingerprint
# allegations.
set -e

php artisan tinker --execute='
$p = App\Models\Prisoner::where("slug", "tsutomu-shirosaki")
    ->orWhere("name", "Tsutomu Shirosaki")
    ->first();
if (!$p) {
    echo "ERROR: Tsutomu Shirosaki not found\n";
    exit(1);
}

$bio = "Tsutomu Shirosaki was a Japanese university student who was arrested in 1971, and sentenced to ten years in prison for his participation in a string of robberies that aimed to secure funds for Japanese radical groups. In 1977 the Japanese Red Army hijacked Japan Airlines Flight 472 and demanded the Japanese government release political prisoners held in Japan in exchange for the passengers. Tsutomu was one of the prisoners released and flown to Algeria by the Japanese government to swap him for the hostages. Without a passport or the ability to travel, Tsutomu ended up being settled in Lebanon. On May 14, 1986, two mortar-styled rockets were fired into the U.S. Embassy compound in Jakarta, Indonesia, there were no injuries. A group calling itself the Anti-Imperialist International Brigade (AIIB) claimed responsibility for the attack. Seven weeks later, the Japanese government claimed that they had found a fingerprint of Tsutomu Shirosaki in the hotel room where the rockets were launched from. During the time of the attack, Tsutomu was still in Lebanon and could not leave the country making it impossible for him to have participated in it. After the 1993 Oslo Accords, Tsutomu was forced to flee Lebanon due to changing political climate. On September 21, 1996, local police in Nepal arrested Tsutomu after the National Security Agency tapped the phones of his friends to find his location. He was handed over to the FBI and extradited to the United States to stand trial for the Embassy attack. Tsutomu asserted his fingerprint had been planted at the scene from his arrest file which Japanese police had been known to do in order to frame suspects since the 1970s. He was convicted of the attack and sentenced to 30 years in prison.";

$p->description = $bio;
$p->save();
echo "Updated Tsutomu Shirosaki description (" . strlen($bio) . " chars).\n";
'

echo
echo "Tsutomu Shirosaki bio update complete."
