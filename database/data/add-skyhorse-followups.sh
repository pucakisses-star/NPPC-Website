#!/usr/bin/env bash
#
# Follow-ups to the Skyhorse-Mohawk case from two sources supplied by the
# site owner (July 2026): the Los Angeles Times of December 10, 1996
# ("Indian Activist Wins Round in Fight Over Peyote," Scott Hadly) and
# the Freedom Socialist of Spring 1980 ("The bitter fight of Native
# American Julie Evening Lilly," Maxine Reigel).
#
#  1. Adds the previously unrecorded 1984 Los Angeles federal bank-robbery
#     convictions to both records: Paul Skyhorse Durant sentenced to eight
#     years, Richard Mohawk to twenty. Stated plainly — the LA Times
#     reports it as an ordinary criminal conviction.
#  2. Adds Paul Skyhorse's November 22, 1996 Ventura County peyote arrest
#     (10,000 buttons found at a traffic stop; charges not filed after he
#     and Buzz Berry proved tribal and Native American Church membership,
#     though the DA reserved refiling and kept the peyote).
#  3. Adds Buzz Berry — arrested in the same stop, same outcome.
#  4. Adds Julie Evening Lilly — the Native American feminist activist
#     whose dormant Virginia custody-"kidnapping" warrant was revived by
#     California police after she joined the Skyhorse-Mohawk defense; she
#     was arrested, freed, and saved from extradition when Governor Brown
#     refused it after a mailgram campaign.
#  5. Fills Paul Skyhorse's aka with his court name, Paul Skyhorse Durant.
#
# Idempotent: prisoner:add refuses duplicates; case rows are created only
# when no existing row matches; aka is fill-if-empty.
#
# Run from the repo root:  bash database/data/add-skyhorse-followups.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Julie Evening Lilly","first_name":"Julie","last_name":"Evening Lilly","description":"Julie Evening Lilly was a Native American feminist and radical activist caught for years in what she called \"judicial terror.\" Awarded custody of her daughter Erin by California in 1972, she saw the child taken to Virginia by the girl'"'"'s white father; when Virginia'"'"'s courts refused to honor her custody or even her visitation rights, she took her daughter back — and Virginia charged her, not the father, with kidnapping and sought extradition. California police who first served the warrant declared it invalid. But after she threw herself into legal defense work for Paul Skyhorse and Richard Mohawk, the warrant was revived and she was arrested. Freed, she and her supporters generated a mailgram campaign that persuaded Governor Jerry Brown to refuse extradition — though the Virginia warrant continued to threaten her whenever she left California, forcing her to travel under its shadow to raise funds to quash it. \"They treat me like an Indian uprising for not staying nice and quiet,\" she said. Reconstructed from the Freedom Socialist, Spring 1980; the later disposition of the warrant has not been located.","state":"California","gender":"Female","ideologies":["Native American rights","Feminism"],"era":"1970s","released":true,"cases":[{"charges":"Arrested in California on Virginia'"'"'s custody-\"kidnapping\" warrant — revived, per the movement press, after she joined the Skyhorse-Mohawk defense","convicted":"No — freed; Governor Jerry Brown refused extradition after a mailgram campaign"}]}' || true

php artisan prisoner:add '{"name":"Buzz Berry","first_name":"Buzz","last_name":"Berry","description":"Buzz Berry was arrested with Paul Skyhorse Durant in Ventura County on November 22, 1996 when sheriff'"'"'s deputies found 10,000 peyote buttons — some 250 pounds — openly carried in their van during a routine traffic stop. \"It'"'"'s not as if we were hiding it or anything,\" he said. The two maintained the peyote was sacrament for legitimate Native American Church ceremonies, and at a December 9 arraignment preceded by ceremonial drumming outside the Ventura courthouse, prosecutors declined to file charges after the men produced proof of tribal membership and Native American Church affiliation — while reserving the right to refile and keeping the confiscated peyote. Reconstructed from the Los Angeles Times, December 10, 1996.","state":"California","gender":"Male","ideologies":["Native American rights","Religious liberty"],"era":"1990s","released":true,"cases":[{"charges":"Peyote possession (10,000 buttons) — November 22, 1996 Ventura County traffic stop; the peyote was sacrament for Native American Church ceremonies","arrest_date":"1996-11-22","convicted":"No — prosecutors declined to file charges on December 9, 1996 after proof of tribal and Native American Church membership, reserving the right to refile"}]}' || true

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$hasCase = function ($p, string $needle): bool {
    foreach ($p->cases as $c) {
        $ch = is_array($c->charges) ? implode(" ", $c->charges) : (string) $c->charges;
        if (stripos($ch, $needle) !== false) { return true; }
    }
    return false;
};

$p = $find("paul-skyhorse");
if ($p) {
    if (empty($p->aka)) { $p->aka = "Paul Skyhorse Durant / Paul Durant Skyhorse"; $p->save(); echo "AKA paul-skyhorse\n"; }
    if (! $hasCase($p, "bank robbery")) {
        $p->cases()->create([
            "charges" => "Bank robbery — convicted in Los Angeles federal court in 1984, with Richard Mohawk",
            "convicted" => "Yes (1984)",
            "sentence" => "Eight years in federal prison",
        ]);
        echo "CASE paul-skyhorse 1984\n";
    }
    if (! $hasCase($p, "eyote")) {
        $p->cases()->create([
            "charges" => "Peyote possession (10,000 buttons, ~250 lbs) — November 22, 1996 Ventura County traffic stop; he argued the peyote was sacrament protected by the Religious Freedom Restoration Act",
            "arrest_date" => "1996-11-22",
            "convicted" => "No — prosecutors declined to file charges on December 9, 1996 after proof of tribal and Native American Church membership, reserving the right to refile; the peyote was not returned",
        ]);
        echo "CASE paul-skyhorse 1996\n";
    }
}

$p = $find("richard-mohawk");
if ($p && ! $hasCase($p, "bank robbery")) {
    $p->cases()->create([
        "charges" => "Bank robbery — convicted in Los Angeles federal court in 1984, with Paul Skyhorse Durant",
        "convicted" => "Yes (1984)",
        "sentence" => "Twenty years in federal prison",
    ]);
    echo "CASE richard-mohawk 1984\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Skyhorse follow-ups applied."
