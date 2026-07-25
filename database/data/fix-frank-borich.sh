#!/usr/bin/env bash
#
# Enrich the Frank Borich record (Croatian-born general secretary of the
# National Miners Union) with his documented biography, an approximate birth
# year, and his repeated deportation detentions from 1932 to 1953. Dates are set
# only where documented; where a release day is unverified it is left blank
# (so imprisoned_for_days stays null rather than being invented).
#
# Cases:
#   * 1932-03-31  Pittsburgh NMU office raid; held for deportation; released on
#                 heavy bond (release day unknown).
#   * 1949-07-11 to 1949-07-16  held for deportation; $5,000 bail took five days.
#   * 1950-10 to 1950-11  about one month at Ellis Island.
#   * 1952 to early 1953  about four months at Ellis Island, then indefinite
#                 supervisory parole (release day unverified).
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-frank-borich.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;

$b = Prisoner::withoutGlobalScopes()
    ->where("slug", "frank-borich")
    ->orWhereRaw("LOWER(name) = ?", ["frank borich"])
    ->first();

if (! $b) { echo "Frank Borich not found.\n"; return; }

$b->description =
    "Frank Borich was a Croatian-born coal miner and Communist labor leader who served as general secretary of the National Miners Union, the militant union that led the 43,000-strong coal strike across Pennsylvania, Ohio and West Virginia in 1931. He immigrated to the United States around 1913 as a teenager; sources place his age on arrival at 13 to 15, so he was probably born about 1898 to 1900. "
    ."From the early 1930s the federal government repeatedly sought to deport him for his labor activity. On March 31, 1932 immigration officers raided the National Miners Union office in Pittsburgh and held him for deportation to Yugoslavia; he was released on a heavy cash bond. He was detained again in July 1949, held for about a month at Ellis Island in the autumn of 1950, re-arrested soon afterward when the government refused to recognize bail posted through the Civil Rights Congress, and from 1952 into early 1953 confined at Ellis Island for roughly four months while no country would admit him after Yugoslavia declined. "
    ."He was finally released under indefinite, so-called lifetime supervisory parole that at first required weekly and later monthly reporting to Ellis Island and interfered with his ability to hold steady work. Borich was a named appellant in Nukk, Borich, Siminoff v. Shaughnessy, the Supreme Court challenge to the supervisory-parole provisions of the immigration law. A 1933 profile reported that he had already been arrested more than a score of times for strike and unemployment organizing, including an arrest in Chicago on robbery, weapons and sedition allegations. His exact birth and death dates have not been documented.";
$b->gender = "Male";
$b->state = "Pennsylvania";
$b->ideologies = ["Communism", "Labor organizing"];
$b->affiliation = ["National Miners Union", "Trade Union Unity League"];
$b->era = "1930s";
$b->in_custody = false;
$b->released = true;
$b->setPartialDate("birthdate", 1899, null, null); // approximate (c. 1898-1900)
$b->save();

$ellis = Institution::firstOrCreate(["name" => "Ellis Island Immigration Station"], ["city" => "New York", "state" => "New York"]);

// 1932 Pittsburgh raid — update the existing case.
$c32 = $b->cases()->where("charges", "like", "%March 31, 1932%")->first() ?: $b->cases()->first();
if ($c32) {
    $c32->charges = "Held for deportation to Yugoslavia after immigration officers raided the National Miners Union office in Pittsburgh on March 31, 1932; he had led the 43,000-strong 1931 coal strike.";
    $c32->convicted = "Held for deportation, 1932";
    $c32->sentence = "Held for deportation under the 1932 Doak deportation drive; released on a heavy cash bond (exact release date not documented).";
    $c32->setPartialDate("arrest_date", 1932, 3, 31);
    $c32->setPartialDate("incarceration_date", 1932, 3, 31);
    $c32->release_date = null;
    $c32->save();
}

$ensure = function (string $marker, array $data, array $inc, ?array $rel) use ($b, $ellis) {
    $c = $b->cases()->where("charges", "like", "%{$marker}%")->first();
    if (! $c) { $c = $b->cases()->make(); $c->prisoner_id = $b->id; }
    foreach ($data as $k => $v) { $c->{$k} = $v; }
    $c->institution_id = $ellis->id;
    $c->setPartialDate("incarceration_date", $inc[0], $inc[1], $inc[2]);
    if ($rel !== null) { $c->setPartialDate("release_date", $rel[0], $rel[1], $rel[2]); } else { $c->release_date = null; }
    $c->save();
};

$ensure("July 1949", [
    "charges" => "Arrested and held for deportation, July 1949; the government demanded a 5,000-dollar bail.",
    "convicted" => "Held for deportation, July 1949",
    "sentence" => "Held about five days at Ellis Island until the 5,000-dollar bail was assembled.",
], [1949, 7, 11], [1949, 7, 16]);

$ensure("autumn of 1950", [
    "charges" => "Taken from his New York home at about 2 a.m. in the autumn of 1950 and confined at Ellis Island.",
    "convicted" => "Held for deportation, 1950",
    "sentence" => "Held about one month at Ellis Island (precise arrest and release days unverified).",
], [1950, 10, null], [1950, 11, null]);

$ensure("1952 into early 1953", [
    "charges" => "Returned to Ellis Island from 1952 into early 1953 after his deportation appeals were exhausted, while no country would admit him.",
    "convicted" => "Held for deportation, 1952-1953",
    "sentence" => "Detained about four months at Ellis Island, then released under indefinite so-called lifetime supervisory parole (exact dates unverified).",
], [1952, null, null], null);

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Frank Borich enriched: bio, birth c.1899, and ".$b->cases()->count()." case(s).\n";
echo "Done.\n";
'

echo
echo "Done."
