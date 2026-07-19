#!/usr/bin/env bash
#
# Three Red Scare / McCarran-era deportation cases from research
# supplied by the site owner (July 2026):
#
#  1. Henry Podolski — Detroit IWO Polonia Society journalist; 1949
#     INS deportation arrest (Alien Act of 1918 grounds), rearrested
#     without bail October 23, 1950 under the new McCarran Internal
#     Security Act, freed November 6, 1950 by Judge Frank A. Koscinski.
#     NEW record.
#  2. George Pirinsky — American Slav Congress leader; five custody
#     episodes 1930-1949 including 91 days on Ellis Island in 1949;
#     deported to Bulgaria, summer 1951. NEW record.
#  3. Joseph Kowalski — existing record (CPA Polish Federation
#     secretary) enriched: aka, the ~early-1920 deportation, the
#     December 1921 false-passport return, and the Atlanta federal
#     penitentiary term for unlawful reentry.
#
# Idempotent: prisoner:add refuses duplicates; the Kowalski edits are
# marker-guarded and fill-if-empty.
#
# Run from the repo root:  bash database/data/add-mccarran-deportees.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Henry Podolski","first_name":"Henry","last_name":"Podolski","aka":"Henryk Podolski","description":"Henry (Henryk) Podolski was a Polish-born socialist journalist and official of the Polonia Society of the International Workers Order in Detroit, and an editor of the Polish-language labor newspaper Trybuna Robotnicza, later Glos Ludowy. Immigration authorities initiated deportation proceedings against him in 1949 under the Alien Act of October 16, 1918, alleging that before entering the United States he had belonged to organizations advocating revolutionary overthrow. Arrested on an August 4, 1949 INS warrant — he was in custody by August 16, when the IWO condemned the arrest — he was released on 5,000 dollars bail. After the McCarran Internal Security Act became law, Attorney General J. Howard McGrath had him rearrested on the same warrant on October 23, 1950 and held without bail. On November 6, 1950, federal Judge Frank A. Koscinski ruled the denial of bail an abuse of the attorney general'"'"'s discretion and ordered his release on the reinstated 5,000-dollar bond — a documented second detention of about fourteen days. Podolski was eventually deported to Poland; the exact deportation date has not been located.","state":"Michigan","gender":"Male","ideologies":["Socialism","Labor movement"],"affiliation":["International Workers Order"],"era":"1950s","in_custody":false,"released":true,"cases":[{"charges":"Deportation proceedings under the Alien Act of October 16, 1918 — alleged pre-entry membership in organizations advocating revolutionary overthrow; arrested on an August 4, 1949 INS warrant, in custody by August 16, 1949","convicted":"No criminal conviction — civil immigration detention; released on $5,000 bail","arrest_date":"1949-08-15"},{"charges":"Rearrested on the same 1949 immigration warrant under McCarran Internal Security Act enforcement and held without bail on Attorney General McGrath'"'"'s order","arrest_date":"1950-10-23","release_date":"1950-11-06","imprisoned_for_days":14,"convicted":"No — Judge Frank A. Koscinski ruled the bail denial an abuse of discretion and ordered release on the reinstated $5,000 bond","judge":"Frank A. Koscinski"}]}' || true

php artisan prisoner:add '{"name":"George Pirinsky","first_name":"George","last_name":"Pirinsky","description":"George Pirinsky arrived in America from Bulgaria in 1923 with little money or English, and within a few years became the leader of the largest Macedonian movement in the country and, by the 1940s, a nationally known speaker, writer and organizer as executive secretary of the American Slav Congress. His work drew relentless pursuit from fascist organizations, the FBI, immigration officials and Congress. His record of custody spans two decades: arrested January 28, 1930 at a Pontiac, Michigan unemployment demonstration (dismissed); March 8, 1935 in South Chicago for disturbing the peace (acquitted); and January 8, 1937 in Detroit on an alleged-illegal-entry deportation warrant that was cancelled and dropped. On September 23, 1948 he was arrested in Chicago while preparing the American Slav Congress convention, accused of post-entry Communist Party membership, and freed on a 1,000-dollar bond. Rearrested July 8, 1949 and transferred to Ellis Island, he was first refused bail and then held under a 25,000-dollar bond he could not afford — until the Second Circuit ruled in United States ex rel. Pirinsky v. Shaughnessy (September 30, 1949) that the bond was arbitrary and excessive. He was released in early October 1949 after 91 days. Pirinsky was deported to Bulgaria in the summer of 1951; the exact departure date has not been located. His principal custody was civil immigration detention — he was never convicted.","state":"Michigan","gender":"Male","ideologies":["Communism"],"affiliation":["American Slav Congress"],"era":"1940s","in_custody":false,"released":true,"cases":[{"charges":"Arrested at a Pontiac, Michigan unemployment demonstration","arrest_date":"1930-01-28","convicted":"No — case dismissed"},{"charges":"Disturbing the peace — South Chicago","arrest_date":"1935-03-08","convicted":"No — acquitted"},{"charges":"Alleged illegal entry — Detroit deportation warrant","arrest_date":"1937-01-08","convicted":"No — deportation warrant cancelled and case dropped"},{"charges":"Deportation arrest in Chicago while preparing the American Slav Congress convention — alleged post-entry Communist Party membership","arrest_date":"1948-09-23","convicted":"No — released on $1,000 immigration bond"},{"institution_name":"Ellis Island Immigration Station","institution_city":"New York","institution_state":"New York","charges":"Rearrested in the same deportation case and held on Ellis Island — bail first refused, then set at an unaffordable $25,000, ruled arbitrary and excessive by the Second Circuit in United States ex rel. Pirinsky v. Shaughnessy (September 30, 1949)","arrest_date":"1949-07-08","release_date":"1949-10-07","imprisoned_for_days":91,"convicted":"No — civil immigration detention; deported to Bulgaria in summer 1951"}]}' || true

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$appendOnce = function ($p, string $marker, string $paragraph): void {
    if (! $p || str_contains((string) $p->description, $marker)) { return; }
    $p->description = trim((string) $p->description) . "\n\n" . $paragraph;
    $p->save();
    echo "DESC {$p->slug}\n";
};
$hasCase = function ($p, string $needle): bool {
    foreach ($p->cases as $c) {
        $ch = is_array($c->charges) ? implode(" ", $c->charges) : (string) $c->charges;
        if (stripos($ch, $needle) !== false) { return true; }
    }
    return false;
};

$p = $find("joseph-kowalski");
if ($p) {
    if (empty($p->aka)) { $p->aka = "Jozef Kowalski / Joe Kowalski / A. Gorny / Jan Gorny"; $p->save(); echo "AKA joseph-kowalski\n"; }
    $appendOnce($p, "December 1921", "Born in Lodz in 1890, Kowalski was released on 1,000 dollars bail after his September 1919 Red Scare arrest and deported — most plausibly to Poland, though a federal investigator later said Russia — in about early 1920, shortly after the Buford sailed. In December 1921 he secretly returned to the United States on a false passport. Federal authorities eventually located him in New York, and he was convicted of unlawful reentry before Judge Julian Mack and sent to the United States Penitentiary in Atlanta — contemporary reports say a one-year sentence, though later federal testimony recalled eighteen months. He was free and organizing publicly in Chicago by June 24, 1924, remained active in the United States through at least 1938 in Detroit despite further immigration arrests, and reportedly died in Lodz in 1960.");
    if (! $hasCase($p, "reentry")) {
        $p->cases()->create([
            "institution_name" => null,
            "charges" => "Unlawful reentry after deportation — returned from abroad in December 1921 on a false passport; convicted in federal court before Judge Julian Mack",
            "convicted" => "Yes — one year (contemporary reports; later testimony recalled eighteen months) at the United States Penitentiary, Atlanta; free by June 24, 1924",
            "judge" => "Julian Mack",
        ]);
        echo "CASE joseph-kowalski reentry\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. McCarran-era deportees applied."
