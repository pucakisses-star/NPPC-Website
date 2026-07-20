#!/usr/bin/env bash
#
# Seed the /timeline page.
#
# The production `timelines` table is empty, so the timeline page renders
# nothing. This creates sixteen entries (one per year, 1798-2025) tracing
# the history of political imprisonment in the United States, and copies
# two repo images into storage so their /storage/ URLs resolve.
#
# All prisoners/* and history/* image paths were verified to return 200
# from http://104.238.162.40/storage/ before inclusion. No usable image
# was found for 1895 (eugene-v-debs 404s in every variant), so that
# entry is created without one.
#
# Idempotent: skips any year that already has a row, and cp -n never
# overwrites existing files.
#
# Run from the repo root:  bash database/data/seed-timeline.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

echo "Copying timeline images into storage/app/public/timeline..."
mkdir -p storage/app/public/timeline
cp -n public/images/articles/haymarket-hanging-clipping.jpg \
      public/images/articles/iww-deportation-1917.jpg \
      storage/app/public/timeline/ || true

php artisan tinker --execute='
$add = function (int $year, string $title, string $text, ?string $image = null) {
    if (\App\Models\Timeline::where("year", $year)->exists()) {
        echo "SKIP    {$year} — already exists\n";
        return;
    }
    \App\Models\Timeline::create([
        "year" => $year,
        "title" => $title,
        "text" => $text,
        "image" => $image,
    ]);
    echo "CREATED {$year} — {$title}\n";
};

$add(1798, "The Sedition Act",
    "Congressman Matthew Lyon of Vermont became the first person jailed under the Sedition Act of 1798, convicted for criticizing President John Adams in print. He ran his re-election campaign from a cell in Vergennes and won by a landslide, returning to Congress as proof that the prosecutions had backfired.",
    "prisoners/matthew-lyon.jpg");

$add(1887, "The Haymarket Executions",
    "After a bomb killed police at a labor rally in Chicago'"'"'s Haymarket Square, eight anarchists were convicted with no evidence tying them to the blast. On November 11, 1887, four of them — Albert Parsons, August Spies, Adolph Fischer, and George Engel — were hanged, becoming martyrs of the international labor movement.",
    "timeline/haymarket-hanging-clipping.jpg");

$add(1895, "Debs at Woodstock",
    "Eugene V. Debs served six months in the Woodstock, Illinois jail for defying a federal injunction against the Pullman railway strike. He entered as a union leader and emerged a socialist, and would later run for president five times — the last time from a prison cell.");

$add(1907, "The Haywood Trial",
    "Union leader Big Bill Haywood was seized in Denver without extradition proceedings and carried by special train to Idaho to stand trial for the assassination of former governor Frank Steunenberg. Defended by Clarence Darrow, he was acquitted in a verdict that stunned the mine owners who had engineered the prosecution.",
    "prisoners/bill-haywood.jpg");

$add(1917, "Espionage Act and Mass Repression",
    "Congress passed the Espionage Act, the law still used against political defendants a century later. That same year suffragists picketing the White House were jailed at the Occoquan Workhouse, and 1,200 striking miners in Bisbee, Arizona were forced into cattle cars at gunpoint and deported into the New Mexico desert.",
    "timeline/iww-deportation-1917.jpg");

$add(1918, "The Abrams Case",
    "The Sedition Act of 1918 criminalized nearly all criticism of the war effort. Mollie Steimer and her fellow Abrams defendants, young Russian immigrants who threw anti-war leaflets from a New York rooftop, drew sentences of up to twenty years — and provoked Justice Holmes'"'"'s famous dissent on the free trade in ideas.",
    "prisoners/mollie-steimer.jpg");

$add(1927, "Sacco and Vanzetti",
    "Italian anarchists Nicola Sacco and Bartolomeo Vanzetti were executed in Massachusetts for a robbery and murder that much of the world believed they did not commit. Their seven-year case drew protests on every continent and remains a byword for prejudice in the American courtroom.",
    "prisoners/bartolomeo-vanzetti.jpg");

$add(1931, "The Scottsboro Nine",
    "Nine Black teenagers pulled from a freight train in Alabama were convicted of rape on fabricated testimony, and eight were sentenced to death within weeks. Their long fight through the courts produced landmark Supreme Court rulings on the right to counsel and the exclusion of Black jurors.",
    "prisoners/haywood-patterson.png");

$add(1932, "The Bonus Army",
    "Tens of thousands of unemployed World War I veterans camped in Washington to demand early payment of their promised service bonus. On July 28, 1932, Army troops under Douglas MacArthur drove them out with tanks, bayonets, and tear gas, burning their camp to the ground.",
    "history/bonus-army.jpg");

$add(1949, "The Smith Act Trial",
    "Eleven leaders of the Communist Party, Eugene Dennis among them, were convicted at Foley Square of conspiring to advocate the overthrow of the government — a prosecution aimed at their politics rather than any act. The Supreme Court upheld the convictions in Dennis v. United States, opening the way for dozens more Smith Act prosecutions.",
    "prisoners/eugene-dennis.jpg");

$add(1953, "The Rosenberg Executions",
    "Julius and Ethel Rosenberg were executed at Sing Sing on June 19, 1953, after a conspiracy trial for atomic espionage held at the height of McCarthyism. The case against Ethel rested chiefly on testimony from her brother, who admitted decades later that he had lied on the stand.",
    "prisoners/ethel-rosenberg.jpg");

$add(1963, "Letter from Birmingham Jail",
    "Arrested for marching without a permit, Martin Luther King Jr. answered his critics from a Birmingham cell in a letter begun in the margins of a smuggled newspaper. Its argument — that there is a moral duty to disobey unjust laws — became the defining text of the civil rights movement.",
    "prisoners/martin-luther-king-jr.jpg");

$add(1970, "Free Angela",
    "Philosophy professor Angela Davis was placed on the FBI'"'"'s Ten Most Wanted list and arrested in New York after guns registered in her name were used in a Marin County courtroom raid. Her sixteen months in jail sparked a worldwide campaign for her freedom, and in 1972 a jury acquitted her of every charge.",
    "prisoners/angela-davis.jpg");

$add(1977, "Leonard Peltier",
    "American Indian Movement activist Leonard Peltier was convicted of murdering two FBI agents in the 1975 Pine Ridge shootout, in a trial marked by coerced testimony and withheld ballistics evidence. He spent nearly half a century behind bars before his sentence was commuted to home confinement in 2025.",
    "prisoners/leonard-peltier.jpg");

$add(2013, "Chelsea Manning",
    "Army analyst Chelsea Manning was sentenced to 35 years — the longest sentence ever imposed for leaking to the press — for giving war logs and diplomatic cables to WikiLeaks. President Obama commuted the sentence in 2017 after she had served seven years.",
    "prisoners/chelsea-manning.jpg");

$add(2025, "Mahmoud Khalil",
    "Columbia graduate Mahmoud Khalil, a lawful permanent resident, was seized by immigration agents over his role in campus protests against the war in Gaza and jailed for more than three months in Louisiana. His was the first of the visa-revocation cases targeting non-citizens for protected political speech.",
    "prisoners/mahmoud-khalil.jpg");

echo "Timeline rows: " . \App\Models\Timeline::count() . "\n";
'

echo
echo "Done. Timeline seeded — check /timeline."
