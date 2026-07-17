#!/usr/bin/env bash
#
# HUAC contempt-prisoner audit (July 2026): of the 34 people imprisoned for
# contempt of the House Un-American Activities Committee (plus Alger Hiss),
# eight were missing from the database — seven Joint Anti-Fascist Refugee
# Committee board members and George Marshall of the National Federation for
# Constitutional Liberties. This script adds them.
#
# Already present and verified: Barsky, Bradley, Bryan, Fast, Fleischman,
# Lustig (JAFRC); the entire Hollywood Ten; Dennis, Josephson, Barenblatt,
# Davis, McPhaul, Wilkinson, Braden; Klan leaders Shelton, J. Robert Jones
# and Scoggin; Alger Hiss; Gerhart Eisler (who fled before serving and is
# not counted among the 34).
#
# Also adds the missing 1950 JAFRC contempt case to James Lustig, whose
# record only covered his separate 1951 Phelps-Dodge prosecution.
#
# The JAFRC board members refused to surrender the committee's records to
# HUAC in 1946, were convicted of contempt of Congress in June 1947, and —
# after the Supreme Court declined review — began their sentences on
# June 7, 1950 (three months and a $500 fine each; chairman Barsky got six
# months).
#
# Idempotent: prisoner:add refuses duplicates (|| true keeps the script
# going), and the Lustig case is created only if absent.
#
# Run from the repo root:  bash database/data/add-huac-jafrc-gaps.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

JAFRC_CHARGES="Contempt of Congress — as an executive-board member of the Joint Anti-Fascist Refugee Committee, refused to surrender the organization records subpoenaed by the House Un-American Activities Committee"

php artisan prisoner:add '{"name":"Ruth Leider","first_name":"Ruth","last_name":"Leider","description":"Ruth Leider was an executive-board member of the Joint Anti-Fascist Refugee Committee (JAFRC), which aided refugees of Franco'"'"'s Spain. When the board refused to surrender the committee'"'"'s records to the House Un-American Activities Committee in 1946 she was cited for contempt of Congress with the rest of the board, convicted in June 1947, and — after the Supreme Court declined to hear the case — began a three-month federal sentence on June 7, 1950, alongside a $500 fine.","state":"New York","race":"White","gender":"Female","ideologies":["Anti-fascism","Civil liberties"],"affiliation":["Joint Anti-Fascist Refugee Committee"],"era":"1940s","released":true,"cases":[{"charges":"'"$JAFRC_CHARGES"'","incarceration_date":"1950-06-07","convicted":"Yes","sentence":"Three months and a $500 fine","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"Charlotte Stern","first_name":"Charlotte","last_name":"Stern","description":"Charlotte Stern was an executive-board member of the Joint Anti-Fascist Refugee Committee (JAFRC), which aided refugees of Franco'"'"'s Spain. Cited for contempt of Congress in 1946 with the rest of the board for refusing to surrender the committee'"'"'s records to the House Un-American Activities Committee and convicted in June 1947, she began a three-month federal sentence on June 7, 1950, alongside a $500 fine.","state":"New York","race":"White","gender":"Female","ideologies":["Anti-fascism","Civil liberties"],"affiliation":["Joint Anti-Fascist Refugee Committee"],"era":"1940s","released":true,"cases":[{"charges":"'"$JAFRC_CHARGES"'","incarceration_date":"1950-06-07","convicted":"Yes","sentence":"Three months and a $500 fine","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"Harry M. Justiz","first_name":"Harry","middle_name":"M.","last_name":"Justiz","description":"Harry M. Justiz was an executive-board member of the Joint Anti-Fascist Refugee Committee (JAFRC), which aided refugees of Franco'"'"'s Spain. Cited for contempt of Congress in 1946 with the rest of the board for refusing to surrender the committee'"'"'s records to the House Un-American Activities Committee and convicted in June 1947, he began a three-month federal sentence on June 7, 1950, alongside a $500 fine.","state":"New York","gender":"Male","ideologies":["Anti-fascism","Civil liberties"],"affiliation":["Joint Anti-Fascist Refugee Committee"],"era":"1940s","released":true,"cases":[{"charges":"'"$JAFRC_CHARGES"'","incarceration_date":"1950-06-07","convicted":"Yes","sentence":"Three months and a $500 fine","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"Louis Miller","first_name":"Louis","last_name":"Miller","description":"Dr. Louis Miller was a New York physician and an executive-board member of the Joint Anti-Fascist Refugee Committee (JAFRC), which aided refugees of Franco'"'"'s Spain. Cited for contempt of Congress in 1946 with the rest of the board for refusing to surrender the committee'"'"'s records to the House Un-American Activities Committee and convicted in June 1947, he began a three-month federal sentence on June 7, 1950, alongside a $500 fine.","state":"New York","race":"White","gender":"Male","ideologies":["Anti-fascism","Civil liberties"],"affiliation":["Joint Anti-Fascist Refugee Committee"],"era":"1940s","released":true,"cases":[{"charges":"'"$JAFRC_CHARGES"'","incarceration_date":"1950-06-07","convicted":"Yes","sentence":"Three months and a $500 fine","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"Marjorie Chodorov","first_name":"Marjorie","last_name":"Chodorov","description":"Marjorie Chodorov was an executive-board member of the Joint Anti-Fascist Refugee Committee (JAFRC), which aided refugees of Franco'"'"'s Spain. Cited for contempt of Congress in 1946 with the rest of the board for refusing to surrender the committee'"'"'s records to the House Un-American Activities Committee and convicted in June 1947, she began a three-month federal sentence on June 7, 1950, alongside a $500 fine.","state":"New York","race":"White","gender":"Female","ideologies":["Anti-fascism","Civil liberties"],"affiliation":["Joint Anti-Fascist Refugee Committee"],"era":"1940s","released":true,"cases":[{"charges":"'"$JAFRC_CHARGES"'","incarceration_date":"1950-06-07","convicted":"Yes","sentence":"Three months and a $500 fine","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"Manuel Magaña","first_name":"Manuel","last_name":"Magaña","description":"Manuel Magaña was an executive-board member of the Joint Anti-Fascist Refugee Committee (JAFRC), which aided refugees of Franco'"'"'s Spain. Cited for contempt of Congress in 1946 with the rest of the board for refusing to surrender the committee'"'"'s records to the House Un-American Activities Committee and convicted in June 1947, he began a three-month federal sentence on June 7, 1950, alongside a $500 fine.","state":"New York","gender":"Male","ideologies":["Anti-fascism","Civil liberties"],"affiliation":["Joint Anti-Fascist Refugee Committee"],"era":"1940s","released":true,"cases":[{"charges":"'"$JAFRC_CHARGES"'","incarceration_date":"1950-06-07","convicted":"Yes","sentence":"Three months and a $500 fine","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"Jacob Auslander","first_name":"Jacob","last_name":"Auslander","description":"Dr. Jacob Auslander was a New York physician and an executive-board member of the Joint Anti-Fascist Refugee Committee (JAFRC), which aided refugees of Franco'"'"'s Spain. Cited for contempt of Congress in 1946 with the rest of the board for refusing to surrender the committee'"'"'s records to the House Un-American Activities Committee and convicted in June 1947, he began a three-month federal sentence on June 7, 1950, alongside a $500 fine.","state":"New York","race":"White","gender":"Male","ideologies":["Anti-fascism","Civil liberties"],"affiliation":["Joint Anti-Fascist Refugee Committee"],"era":"1940s","released":true,"cases":[{"charges":"'"$JAFRC_CHARGES"'","incarceration_date":"1950-06-07","convicted":"Yes","sentence":"Three months and a $500 fine","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"George Marshall","first_name":"George","last_name":"Marshall","description":"George Marshall (1904–2000) was a civil-liberties activist, economist and conservationist — later a president of the Sierra Club and a council member of The Wilderness Society — who chaired the National Federation for Constitutional Liberties. Cited for contempt of Congress in 1946 for refusing to give the House Un-American Activities Committee the federation'"'"'s contributor records, he served three months in the federal prison at Ashland, Kentucky, from June to September 1950.","state":"New York","race":"White","gender":"Male","ideologies":["Civil liberties"],"affiliation":["National Federation for Constitutional Liberties"],"era":"1950s","released":true,"cases":[{"institution_name":"Federal Correctional Institution, Ashland","institution_city":"Ashland","institution_state":"Kentucky","charges":"Contempt of Congress — as chairman of the National Federation for Constitutional Liberties, refused to produce the organization contributor records subpoenaed by the House Un-American Activities Committee","convicted":"Yes","sentence":"Three months","imprisoned_for_days":90}]}' || true

# --- James Lustig: add the missing 1950 JAFRC contempt case ---------------
php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "james-lustig")->first();
if (! $p) {
    echo "MISS james-lustig\n";
} elseif ($p->cases()->where("charges", "like", "%Anti-Fascist Refugee Committee%")->exists()) {
    echo "james-lustig already has the JAFRC case — nothing to do.\n";
} else {
    $p->cases()->create([
        "charges" => "Contempt of Congress — as an executive-board member of the Joint Anti-Fascist Refugee Committee, refused to surrender the organization records subpoenaed by the House Un-American Activities Committee",
        "incarceration_date" => "1950-06-07",
        "convicted" => "Yes",
        "sentence" => "Three months and a $500 fine",
        "imprisoned_for_days" => 90,
    ]);
    if (! collect($p->affiliation ?? [])->contains("Joint Anti-Fascist Refugee Committee")) {
        $aff = $p->affiliation ?? [];
        $aff[] = "Joint Anti-Fascist Refugee Committee";
        $p->affiliation = $aff;
        $p->save();
    }
    echo "Added JAFRC contempt case to james-lustig.\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
'

echo
echo "Done. HUAC/JAFRC gap additions applied."
