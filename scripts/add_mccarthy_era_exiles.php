<?php

declare(strict_types=1);

/**
 * Bulk-add McCarthy-era forced exiles (Hollywood blacklist + HUAC):
 *
 *   Hollywood blacklist → exile abroad:
 *     1.  Joseph Losey      — UK exile from 1953
 *     2.  Jules Dassin      — France exile from 1953
 *     3.  Donald Ogden Stewart — UK exile from 1951
 *     4.  Ella Winter       — UK exile from 1951
 *     5.  Carl Foreman      — UK exile from 1952
 *     6.  Michael Wilson    — Paris/Mexico exile 1951-1965
 *     7.  John Berry        — France exile from 1951
 *     8.  Hugo Butler       — Mexico City exile from 1951
 *     9.  Jean Rouverol Butler — Mexico City exile 1951-1964
 *    10.  Albert Maltz      — Mexico exile (after Hollywood Ten prison term)
 *    11.  Dalton Trumbo     — Mexico City exile 1951-1954
 *
 *   Other:
 *    12.  Charles Chaplin   — re-entry permit revoked 1952; Switzerland exile
 *    13.  Hanns Eisler      — deported 1948 to East Germany
 *    14.  Bertolt Brecht    — fled to East Germany day after HUAC testimony
 *
 * Idempotent. For prisoners already present (Maltz, Trumbo from prior
 * Truman/Hollywood-Ten bulk adds), the exile case is attached as a new
 * row keyed by its own arrest_date.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

function addP(array $p): array {
    $existing = Prisoner::where('name', $p['name'])->first();
    if ($existing) return [$existing, false];
    $prisoner = Prisoner::create(array_filter([
        'name' => $p['name'], 'first_name' => $p['first_name'] ?? null, 'middle_name' => $p['middle_name'] ?? null,
        'last_name' => $p['last_name'] ?? null, 'aka' => $p['aka'] ?? null,
        'gender' => $p['gender'] ?? null, 'race' => $p['race'] ?? null, 'state' => $p['state'] ?? null,
        'birthdate' => $p['birthdate'] ?? null, 'death_date' => $p['death_date'] ?? null,
        'description' => $p['description'] ?? null, 'era' => $p['era'] ?? '1950s',
        'ideologies' => $p['ideologies'] ?? null, 'affiliation' => $p['affiliation'] ?? null,
        'in_custody' => $p['in_custody'] ?? false, 'released' => $p['released'] ?? true,
    ], fn ($v) => $v !== null));
    return [$prisoner, true];
}

function attachC(Prisoner $prisoner, array $c): bool {
    if (! empty($c['arrest_date'])) {
        $exists = $prisoner->cases()->where('arrest_date', $c['arrest_date'])->first();
        if ($exists) return false;
    }
    $institution = Institution::firstOrCreate(
        ['name' => $c['institution']['name']],
        ['city' => $c['institution']['city'] ?? null, 'state' => $c['institution']['state'] ?? null]
    );
    PrisonerCase::create([
        'prisoner_id' => $prisoner->id, 'institution_id' => $institution->id,
        'charges' => $c['charges'] ?? null, 'arrest_date' => $c['arrest_date'] ?? null,
        'incarceration_date' => $c['incarceration_date'] ?? null, 'release_date' => $c['release_date'] ?? null,
        'sentenced_date' => $c['sentenced_date'] ?? null, 'convicted' => $c['convicted'] ?? null,
        'sentence' => $c['sentence'] ?? null, 'prosecutor' => $c['prosecutor'] ?? null, 'judge' => $c['judge'] ?? null,
    ]);
    return true;
}

$tot = ['created' => 0, 'existed' => 0, 'cases' => 0];

$ukExile = ['name' => 'Hollywood blacklist exile — United Kingdom', 'city' => 'London', 'state' => 'England'];
$frExile = ['name' => 'Hollywood blacklist exile — France', 'city' => 'Paris', 'state' => 'France'];
$mxExile = ['name' => 'Hollywood blacklist exile — Mexico', 'city' => 'Mexico City', 'state' => 'Mexico'];
$gdrExile = ['name' => 'HUAC-era political exile — German Democratic Republic', 'city' => 'East Berlin', 'state' => 'East Germany'];
$chExile = ['name' => 'McCarthy-era political exile — Switzerland', 'city' => 'Vevey', 'state' => 'Switzerland'];

foreach ([
    [
        'p' => ['name' => 'Joseph Losey', 'first_name' => 'Joseph', 'middle_name' => 'Walton', 'last_name' => 'Losey',
            'birthdate' => '1909-01-14', 'death_date' => '1984-06-22', 'gender' => 'Male', 'race' => 'White',
            'state' => 'England (in exile)', 'affiliation' => ['Communist Party USA (former)'],
            'ideologies' => ['Marxism', 'Anti-fascism'],
            'description' => "Joseph Losey was an American film director (The Boy with Green Hair, M, The Prowler) who in 1951 was named before the House Un-American Activities Committee by Edward Dmytryk and Stanley Kramer's collaborator. While in Italy directing Stranger on the Prowl, he was subpoenaed; rather than return to face HUAC, he relocated to the United Kingdom in 1953 and never lived in the United States again. Working in England — initially under pseudonyms — he built one of the most distinguished post-war directorial careers, including The Servant (1963), Accident (1967), and the Cannes-Palme-d'Or-winning The Go-Between (1971). He died in London in 1984."],
        'c' => ['institution' => $ukExile,
            'charges' => 'HUAC subpoena (1951); blacklisted by Hollywood studios. Fled to UK rather than testify; lived and worked in London until his death.',
            'arrest_date' => '1953-01-01', 'sentence' => 'Effective political exile (~31 years; 1953-1984)',
            'convicted' => 'No — never returned to face HUAC'],
    ],
    [
        'p' => ['name' => 'Jules Dassin', 'first_name' => 'Jules', 'last_name' => 'Dassin',
            'birthdate' => '1911-12-18', 'death_date' => '2008-03-31', 'gender' => 'Male', 'race' => 'White',
            'state' => 'France (in exile)', 'affiliation' => ['Communist Party USA (1934-1939)'],
            'ideologies' => ['Marxism', 'Anti-fascism'],
            'description' => "Jules Dassin was an American film director (The Naked City, Brute Force, Thieves' Highway, Night and the City) who was named as a Communist before HUAC in 1951 by both Edward Dmytryk and Frank Tuttle. Blacklisted in Hollywood, he relocated to France in 1953 and rebuilt his career in Europe, directing the heist masterpiece Rififi (1955) — for which he won Best Director at Cannes — and Never on Sunday (1960), Topkapi (1964), and Promise at Dawn (1970). He married Greek actress Melina Mercouri in 1966 and lived primarily in Greece for the rest of his life. He died in Athens in 2008 at age 96."],
        'c' => ['institution' => $frExile,
            'charges' => 'Named by Edward Dmytryk and Frank Tuttle in HUAC testimony (1951); blacklisted by Hollywood studios. Fled to France 1953 to continue making films.',
            'arrest_date' => '1953-01-01', 'sentence' => 'Effective political exile (~55 years; 1953-2008)',
            'convicted' => 'No'],
    ],
    [
        'p' => ['name' => 'Donald Ogden Stewart', 'first_name' => 'Donald', 'middle_name' => 'Ogden', 'last_name' => 'Stewart',
            'birthdate' => '1894-11-30', 'death_date' => '1980-08-02', 'gender' => 'Male', 'race' => 'White',
            'state' => 'England (in exile)', 'affiliation' => ['League of American Writers', 'Hollywood Anti-Nazi League'],
            'ideologies' => ['Anti-fascism', 'Progressive', 'Marxism'],
            'description' => "Donald Ogden Stewart was a celebrated American humorist and screenwriter who won the 1940 Academy Award for Best Adapted Screenplay for The Philadelphia Story. President of the League of American Writers and a leader of the Hollywood Anti-Nazi League, he was blacklisted in 1950 after appearing on Red Channels. Rather than face HUAC subpoena, he and his wife Ella Winter moved to London in 1951; the State Department denied them passports for years thereafter. He never returned to live in the United States and died in London in 1980. His autobiography By a Stroke of Luck! was published the same year."],
        'c' => ['institution' => $ukExile,
            'charges' => 'Listed in Red Channels (1950); blacklisted by Hollywood studios; HUAC investigation pending. Emigrated to UK 1951 to avoid testimony.',
            'arrest_date' => '1951-01-01', 'sentence' => 'Effective political exile (~29 years; 1951-1980)',
            'convicted' => 'No'],
    ],
    [
        'p' => ['name' => 'Ella Winter', 'first_name' => 'Ella', 'last_name' => 'Winter',
            'birthdate' => '1898-03-17', 'death_date' => '1980-08-05', 'gender' => 'Female', 'race' => 'White',
            'state' => 'England (in exile)', 'affiliation' => ['League of American Writers'],
            'ideologies' => ['Marxism', 'Anti-fascism', 'Feminism'],
            'description' => "Ella Winter was an Australian-born American journalist, leftist activist, and author of Red Virtue (1933), one of the first sympathetic Western accounts of the Soviet Union. Widow of muckraker Lincoln Steffens (whose autobiography she edited after his 1936 death), she married Donald Ogden Stewart in 1939. Throughout the 1940s she was active in the Hollywood Anti-Nazi League and the League of American Writers. The State Department denied her a passport in 1948. After Stewart was blacklisted in 1950 the couple emigrated to London in 1951, where she continued writing for The Manchester Guardian and other publications. She died in London five days after her husband, in August 1980."],
        'c' => ['institution' => $ukExile,
            'charges' => 'State Department passport denial (1948); HUAC era surveillance. Emigrated to UK 1951 with husband Donald Ogden Stewart after his blacklisting.',
            'arrest_date' => '1951-01-01', 'sentence' => 'Effective political exile (~29 years; 1951-1980)',
            'convicted' => 'No — administrative passport denial'],
    ],
    [
        'p' => ['name' => 'Carl Foreman', 'first_name' => 'Carl', 'last_name' => 'Foreman',
            'birthdate' => '1914-07-23', 'death_date' => '1984-06-26', 'gender' => 'Male', 'race' => 'White',
            'state' => 'England (in exile)', 'affiliation' => ['Communist Party USA (1938-1942)'],
            'ideologies' => ['Anti-fascism', 'Liberalism'],
            'description' => "Carl Foreman was the screenwriter of High Noon (1952), the western widely understood as an allegory of the Hollywood blacklist. Subpoenaed by HUAC during the production of High Noon, he refused to name names and was declared an 'uncooperative witness.' His Stanley Kramer Productions partnership was dissolved and he was blacklisted. He emigrated to England in 1952, where he wrote The Bridge on the River Kwai (1957, Best Adapted Screenplay Oscar — credited only to Pierre Boulle until 1984) and produced and wrote The Guns of Navarone (1961) and Born Free (1966). He returned to the U.S. only in 1975 and died in California in 1984. The Academy retroactively added his and Michael Wilson's names to the River Kwai screenwriting credit in 1985."],
        'c' => ['institution' => $ukExile,
            'charges' => 'HUAC subpoena (September 1951) during production of High Noon; declared "uncooperative witness" for refusing to name names. Blacklisted by Hollywood studios.',
            'arrest_date' => '1952-01-01', 'sentence' => 'Effective political exile (~23 years; 1952-1975)',
            'convicted' => 'No'],
    ],
    [
        'p' => ['name' => 'Michael Wilson (screenwriter)', 'first_name' => 'Michael', 'last_name' => 'Wilson',
            'birthdate' => '1914-07-01', 'death_date' => '1978-04-09', 'gender' => 'Male', 'race' => 'White',
            'state' => 'France (in exile)', 'affiliation' => ['Communist Party USA'],
            'ideologies' => ['Marxism', 'Anti-fascism'],
            'description' => "Michael Wilson was an American screenwriter who won the 1951 Academy Award for Best Adapted Screenplay for A Place in the Sun. Subpoenaed by HUAC in September 1951 he invoked the Fifth Amendment and was blacklisted; the Academy responded by amending its bylaws in 1957 to bar Communists and Fifth-Amendment-invokers from receiving Oscars (a rule directly aimed at him). He moved to Paris in 1951 (with later periods in Mexico) and wrote, uncredited or under pseudonyms, The Bridge on the River Kwai (1957), Lawrence of Arabia (1962), and Salt of the Earth (1954) — the latter the only film made in defiance of the blacklist. He returned to the U.S. in 1965 and died in 1978. His credit on Bridge on the River Kwai was restored in 1985."],
        'c' => ['institution' => $frExile,
            'charges' => 'HUAC subpoena September 1951; invoked Fifth Amendment; blacklisted. Academy amended bylaws 1957 to bar him from Oscars (rule rescinded 1959).',
            'arrest_date' => '1951-09-01', 'release_date' => '1965-01-01',
            'sentence' => 'Effective political exile (~14 years; 1951-1965)', 'convicted' => 'No'],
    ],
    [
        'p' => ['name' => 'John Berry (director)', 'first_name' => 'John', 'last_name' => 'Berry',
            'birthdate' => '1917-09-04', 'death_date' => '1999-11-29', 'gender' => 'Male', 'race' => 'White',
            'state' => 'France (in exile)', 'affiliation' => ['Communist Party USA'],
            'ideologies' => ['Marxism', 'Anti-fascism'],
            'description' => "John Berry was an American film director (Casbah, He Ran All the Way) and former assistant to Orson Welles in the Mercury Theatre. Named by Edward Dmytryk in HUAC testimony in April 1951, he refused to cooperate and was blacklisted. He directed The Hollywood Ten (1950), a documentary on the imprisoned screenwriters, which ended his Hollywood career. He fled to France in 1951, directing Tamango (1958), Maya (1966), and films in French throughout the 1950s and 1960s. He returned to the U.S. in 1973 and continued to direct in theater and film until his death in Paris in 1999."],
        'c' => ['institution' => $frExile,
            'charges' => 'Named by Edward Dmytryk in HUAC testimony April 1951; blacklisted after directing The Hollywood Ten documentary. Fled to France 1951.',
            'arrest_date' => '1951-04-01', 'release_date' => '1973-01-01',
            'sentence' => 'Effective political exile (~22 years; 1951-1973)', 'convicted' => 'No'],
    ],
    [
        'p' => ['name' => 'Hugo Butler', 'first_name' => 'Hugo', 'last_name' => 'Butler',
            'birthdate' => '1914-05-04', 'death_date' => '1968-01-07', 'gender' => 'Male', 'race' => 'White',
            'state' => 'Mexico (in exile)', 'affiliation' => ['Communist Party USA'],
            'ideologies' => ['Marxism'],
            'description' => "Hugo Butler was a Canadian-born American screenwriter (Lassie Come Home, The Southerner, Edison the Man) and one of the most prolific Hollywood blacklist exiles in Mexico. Subpoenaed by HUAC in 1951 he refused to testify and fled to Mexico City with his wife Jean Rouverol Butler and their five children. In Mexico he wrote screenplays for Luis Buñuel (Robinson Crusoe, 1954; The Young One, 1960) under pseudonyms and the front of producer George Pepper. He was a close friend and Mexican exile peer of Albert Maltz, Dalton Trumbo, and Elizabeth Catlett. He returned to the U.S. in the early 1960s and died of a stroke in Hollywood in 1968 at age 53."],
        'c' => ['institution' => $mxExile,
            'charges' => 'HUAC subpoena (1951); blacklisted by Hollywood studios. Fled to Mexico City with family rather than testify.',
            'arrest_date' => '1951-01-01', 'release_date' => '1964-01-01',
            'sentence' => 'Effective political exile (~13 years; 1951-1964)', 'convicted' => 'No'],
    ],
    [
        'p' => ['name' => 'Jean Rouverol Butler', 'first_name' => 'Jean', 'last_name' => 'Rouverol Butler',
            'aka' => 'Jean Rouverol',
            'birthdate' => '1916-07-08', 'death_date' => '2017-03-24', 'gender' => 'Female', 'race' => 'White',
            'state' => 'Mexico (in exile)', 'affiliation' => ['Communist Party USA'],
            'ideologies' => ['Marxism', 'Feminism'],
            'description' => "Jean Rouverol Butler was an American actress, screenwriter, and memoirist of the Hollywood blacklist. She had appeared in W.C. Fields' It's a Gift (1934) and Stage Door (1937) before moving into screenwriting. After her husband Hugo Butler was subpoenaed by HUAC in 1951 — and she received her own subpoena while pregnant — the family fled to Mexico City, where they lived in exile until 1964. In Mexico she co-wrote screenplays with her husband for Luis Buñuel and others. Returning to the U.S., she wrote for soap operas (Guiding Light, As the World Turns) for two decades. Her 2000 memoir Refugees from Hollywood is one of the foundational accounts of the Mexican exile community. She died in Los Angeles in 2017 at age 100."],
        'c' => ['institution' => $mxExile,
            'charges' => 'HUAC subpoena (1951) issued while pregnant; blacklisted. Fled with husband Hugo Butler and children to Mexico City.',
            'arrest_date' => '1951-01-01', 'release_date' => '1964-01-01',
            'sentence' => 'Effective political exile (~13 years; 1951-1964)', 'convicted' => 'No'],
    ],
    [
        'p' => ['name' => 'Albert Maltz', 'first_name' => 'Albert', 'last_name' => 'Maltz',
            'birthdate' => '1908-10-28', 'death_date' => '1985-04-26', 'gender' => 'Male', 'race' => 'White',
            'state' => 'Mexico (in exile)', 'affiliation' => ['Communist Party USA', 'Hollywood Ten'],
            'ideologies' => ['Marxism', 'Anti-fascism'],
            'description' => "Albert Maltz was a member of the Hollywood Ten — the screenwriters who refused to answer HUAC's October 1947 questions about Communist Party affiliation. After serving ten months at the federal correctional institution at Mill Point, West Virginia (1950-1951), Maltz emigrated to Mexico City, where he lived from 1951 to 1962 alongside Dalton Trumbo, Hugo Butler, and the broader Hollywood blacklist exile community. He wrote screenplays under pseudonyms including The Robe (1953) front-credit and Two Mules for Sister Sara (later, after blacklist). He returned to the U.S. in 1962 and saw his name restored to credits beginning with Two Mules for Sister Sara (1970). His Hollywood Ten contempt-of-Congress conviction is recorded separately."],
        'c' => ['institution' => $mxExile,
            'charges' => 'Continued blacklist after serving 10-month Hollywood Ten contempt sentence at FCI Mill Point, WV. Relocated to Mexico City to write under pseudonyms.',
            'arrest_date' => '1951-04-01', 'release_date' => '1962-01-01',
            'sentence' => 'Effective political exile (~11 years; 1951-1962, post-prison)', 'convicted' => 'No (separate from his prior contempt conviction)'],
    ],
    [
        'p' => ['name' => 'Dalton Trumbo', 'first_name' => 'James', 'middle_name' => 'Dalton', 'last_name' => 'Trumbo',
            'birthdate' => '1905-12-09', 'death_date' => '1976-09-10', 'gender' => 'Male', 'race' => 'White',
            'state' => 'Mexico (in exile)', 'affiliation' => ['Communist Party USA', 'Hollywood Ten'],
            'ideologies' => ['Marxism'],
            'description' => "Dalton Trumbo was the most prolific and famous of the Hollywood Ten. After serving eleven months at federal prisons in Texarkana and Ashland, Kentucky for refusing to answer HUAC's October 1947 questions, he emigrated to Mexico City in 1951, joining Albert Maltz, Hugo Butler, Jean Rouverol Butler, and others in exile. From Mexico he wrote some thirty screenplays under pseudonyms, including Roman Holiday (1953, Oscar credited to Ian McLellan Hunter) and The Brave One (1956, Oscar credited to Robert Rich). Returning to the U.S. in 1954, he was openly credited again for Spartacus and Exodus in 1960, breaking the blacklist. He died in Los Angeles in 1976. His Hollywood Ten contempt-of-Congress conviction is recorded separately."],
        'c' => ['institution' => $mxExile,
            'charges' => 'Continued blacklist after serving 11-month Hollywood Ten contempt sentence at FCI Texarkana and FCI Ashland. Relocated to Mexico City 1951-1954 to write under pseudonyms (Robert Rich, Sam Jackson, et al.).',
            'arrest_date' => '1951-06-01', 'release_date' => '1954-01-01',
            'sentence' => 'Mexico exile (~3 years 1951-1954); broader blacklist 1947-1960', 'convicted' => 'No (separate from his prior contempt conviction)'],
    ],
    [
        'p' => ['name' => 'Charles Chaplin', 'first_name' => 'Charles', 'middle_name' => 'Spencer', 'last_name' => 'Chaplin',
            'aka' => 'Charlie Chaplin',
            'birthdate' => '1889-04-16', 'death_date' => '1977-12-25', 'gender' => 'Male', 'race' => 'White',
            'state' => 'Switzerland (in exile)', 'affiliation' => [],
            'ideologies' => ['Pacifism', 'Anti-fascism', 'Liberalism'],
            'description' => "Charlie Chaplin was a British-citizen filmmaker who lived in the United States from 1912 to 1952. The FBI under J. Edgar Hoover compiled a 1,900-page file on him, citing his pacifist film Monsieur Verdoux (1947), his association with Hanns Eisler, and his support for the Joint Anti-Fascist Refugee Committee. On September 19, 1952, while Chaplin was sailing to London for the premiere of Limelight, U.S. Attorney General James P. McGranery revoked his re-entry permit, citing 'moral turpitude' and Communist sympathies, and announcing he would face an Immigration and Naturalization Service hearing if he returned. Chaplin refused, settled with his family at Manoir de Ban in Vevey, Switzerland, and did not return to the United States until April 1972, when he received an Honorary Academy Award. He continued to make films in Europe (A King in New York, 1957; A Countess from Hong Kong, 1967) and died in Switzerland on Christmas Day 1977."],
        'c' => ['institution' => $chExile,
            'charges' => 'Re-entry permit revoked by U.S. Attorney General James P. McGranery, September 19, 1952, on grounds of "moral turpitude" and Communist sympathies. Returned to U.S. only in April 1972 to receive Honorary Oscar.',
            'arrest_date' => '1952-09-19', 'release_date' => '1972-04-10',
            'sentence' => 'Effective political exclusion from U.S. (~20 years; 1952-1972)',
            'convicted' => 'No — administrative immigration exclusion'],
    ],
    [
        'p' => ['name' => 'Hanns Eisler', 'first_name' => 'Hanns', 'last_name' => 'Eisler',
            'birthdate' => '1898-07-06', 'death_date' => '1962-09-06', 'gender' => 'Male', 'race' => 'White',
            'state' => 'East Germany (deported)', 'affiliation' => ['Communist Party of Germany'],
            'ideologies' => ['Marxism', 'Anti-fascism'],
            'description' => "Hanns Eisler was an Austrian-born composer, longtime collaborator with Bertolt Brecht (writing the music for The Measures Taken, Mother Courage, and Galileo), and a Hollywood film composer (Hangmen Also Die!, None But the Lonely Heart, So Well Remembered) who emigrated to the United States in 1938 to escape Nazism. Branded by HUAC's Robert Stripling as 'the Karl Marx of Communism in the field of music' in September 1947 hearings, he was the first Hollywood figure pursued by the committee. Threatened with deportation, he was permitted to leave 'voluntarily' in February 1948, sailing to Prague and then settling in East Berlin, where he composed the East German national anthem ('Auferstanden aus Ruinen'). He died in East Berlin in 1962."],
        'c' => ['institution' => $gdrExile,
            'charges' => 'HUAC investigation (September 1947) of "the Karl Marx of music"; deportation proceedings initiated. Permitted "voluntary" departure February 1948 to East Germany.',
            'arrest_date' => '1948-02-01', 'sentence' => 'Effective political deportation (~14 years; 1948-1962)',
            'convicted' => 'No — administrative deportation'],
    ],
    [
        'p' => ['name' => 'Bertolt Brecht', 'first_name' => 'Bertolt', 'last_name' => 'Brecht',
            'aka' => 'Eugen Berthold Friedrich Brecht',
            'birthdate' => '1898-02-10', 'death_date' => '1956-08-14', 'gender' => 'Male', 'race' => 'White',
            'state' => 'East Germany (in exile)', 'affiliation' => [],
            'ideologies' => ['Marxism', 'Anti-fascism'],
            'description' => "Bertolt Brecht was the German playwright (Mother Courage, The Threepenny Opera, The Caucasian Chalk Circle) who fled Nazi Germany in 1933 and lived in the United States from 1941 to 1947, working in Santa Monica alongside Thomas Mann, Lion Feuchtwanger, and Hanns Eisler. Subpoenaed by HUAC as one of the 'Hollywood Nineteen,' he testified on October 30, 1947, denying — accurately, in a literal sense — that he had ever been a member of the Communist Party. The day after his testimony he flew to Switzerland and never returned to the United States. He settled in East Berlin in 1949, founded the Berliner Ensemble with his wife Helene Weigel, and remained there until his death in 1956. His passport at death was Austrian (he had taken Austrian citizenship in 1950)."],
        'c' => ['institution' => $gdrExile,
            'charges' => 'HUAC subpoena as one of the "Hollywood Nineteen"; testified October 30, 1947 and flew to Switzerland the following day, never to return to the U.S.',
            'arrest_date' => '1947-10-31', 'sentence' => 'Effective political exile (~9 years; 1947-1956)',
            'convicted' => 'No'],
    ],
] as $row) {
    [$prisoner, $created] = addP($row['p']);
    $tot[$created ? 'created' : 'existed']++;
    if (attachC($prisoner, $row['c'])) $tot['cases']++;
}

echo "\nMcCarthy-era exiles load complete.\n";
echo sprintf("  Created:  %d new prisoners\n", $tot['created']);
echo sprintf("  Existed:  %d already present (case still attached if new arrest_date)\n", $tot['existed']);
echo sprintf("  Cases:    %d cases attached\n", $tot['cases']);
echo "\n";
