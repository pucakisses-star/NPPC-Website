<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 3 from the ILD's Labor Defender (1926-27, marxists.org/archive.org):
 * the well-documented named cases surfaced by the magazine that were missing
 * from the database. Each verified against the historical record.
 *
 * Covers: the missing Centralia prisoner (James McInerney — the other seven are
 * already recorded), the Hawaiian labor leader Pablo Manlapit, the Alcatraz
 * soldier-communists Crouch and Trumbull, the Woodlawn (Pa.) Flynn Sedition Act
 * trio, Passaic strike leaders Weisbord and Rubenstein, the NY furriers' leader
 * Ben Gold, the New York criminal-anarchy prisoners Larkin and Winitsky, the
 * WWI Canton workhouse anti-war prisoners Wagenknecht and Baker, the Los
 * Angeles Times case prisoners Schmidt and Caplan, and Blair Mountain miner
 * Edgar Combs.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefenderFamousCases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-famous';

    protected $description = 'Add 16 verified class-war prisoners from Labor Defender 1926-27 (Centralia, Manlapit, Alcatraz soldiers, Woodlawn, Passaic, furriers, NY anarchy, Canton, LA Times case, Blair Mountain)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'James McInerney',
                    'first_name' => 'James',
                    'last_name' => 'McInerney',
                    'description' => "James McInerney was one of the IWW loggers convicted of second-degree murder after the Centralia, Washington Armistice Day tragedy of 11 November 1919, when American Legion marchers attacked the IWW hall and the union men defended it with gunfire. Sentenced with his fellow workers to 25 to 40 years, he was held at the Washington State Penitentiary at Walla Walla (inmate no. 9410), where the ILD's Labor Defender printed his letters through the 1920s. He completes the group of Centralia prisoners already recorded in this database.",
                    'state' => 'Washington',
                    'gender' => 'Male',
                    'inmate_number' => '9410',
                    'ideologies' => ['Industrial unionism'],
                    'affiliation' => ['Industrial Workers of the World'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of second-degree murder in the Centralia (Armistice Day 1919) case against the IWW.',
                        'convicted' => 'Convicted, 1920',
                        'sentence' => '25 to 40 years; held at the Washington State Penitentiary, Walla Walla (no. 9410).',
                        'institution_name' => 'Washington State Penitentiary',
                        'institution_city' => 'Walla Walla',
                        'institution_state' => 'Washington',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1920, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Pablo Manlapit',
                    'first_name' => 'Pablo',
                    'last_name' => 'Manlapit',
                    'description' => "Pablo Manlapit, the pioneering Filipino labor leader in Hawaii, organized the plantation workers' movement and led the great 1924 sugar strike. After the Hanapepe massacre, in which police killed sixteen strikers, the territory prosecuted Manlapit rather than the killers: convicted on a subornation-of-perjury charge widely regarded as retaliation for the strike, he was imprisoned in the Oahu Penitentiary — where the ILD listed him among America's class-war prisoners — and was paroled in 1927 on condition of exile to California. He returned in 1932 and was ultimately banished from the territory for his organizing.",
                    'state' => 'Hawaii',
                    'gender' => 'Male',
                    'race' => 'Asian',
                    'ideologies' => ['Labor organizing'],
                    'affiliation' => ['Hawaii Laborers\' Association'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of subornation of perjury after leading the 1924 Hawaii sugar strike — a prosecution widely regarded as retaliation for the strike.',
                        'convicted' => 'Convicted, 1924',
                        'sentence' => 'Two to ten years; held at the Oahu Penitentiary; paroled in 1927 on condition of exile to California.',
                        'institution_name' => 'Oahu Penitentiary',
                        'institution_city' => 'Honolulu',
                        'institution_state' => 'Hawaii',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1924, null, null], 'release_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Paul Crouch',
                    'first_name' => 'Paul',
                    'last_name' => 'Crouch',
                    'description' => "Paul Crouch, a young U.S. Army private at Schofield Barracks, Hawaii, was court-martialed in 1925 for organizing communist activity in the ranks — his letters and the 'Hawaiian Communist League' were treated as military crimes. Sentenced to forty years, reduced on review to three, he was imprisoned at the Alcatraz military barracks, where the ILD campaigned for him as a class-war prisoner until his release in mid-1927. He later became a Communist Party official and, in the 1950s, a controversial professional witness.",
                    'state' => 'California',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Court-martialed for organizing communist activity as a soldier at Schofield Barracks, Hawaii.',
                        'convicted' => 'Convicted by court-martial, 1925',
                        'sentence' => 'Forty years, reduced to three on review; held at the Alcatraz military barracks; released in 1927.',
                        'institution_name' => 'Alcatraz Military Barracks',
                        'institution_city' => 'San Francisco',
                        'institution_state' => 'California',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1925, null, null], 'release_date' => [1927, 6, null]],
            ],
            [
                'payload' => [
                    'name' => 'Walter Trumbull',
                    'first_name' => 'Walter',
                    'last_name' => 'Trumbull',
                    'description' => "Walter M. Trumbull, a U.S. Army soldier at Schofield Barracks, Hawaii, was court-martialed with Paul Crouch in 1925 for communist organizing in the ranks. Sentenced to twenty-six years, reduced on review to one, he served his year at the Alcatraz military barracks and, on his release in February 1926, toured the country for the International Labor Defense as 'the rebel soldier.'",
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Court-martialed with Paul Crouch for communist organizing as a soldier at Schofield Barracks, Hawaii.',
                        'convicted' => 'Convicted by court-martial, 1925',
                        'sentence' => 'Twenty-six years, reduced to one on review; served at the Alcatraz military barracks; released February 1926.',
                        'institution_name' => 'Alcatraz Military Barracks',
                        'institution_city' => 'San Francisco',
                        'institution_state' => 'California',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1925, null, null], 'release_date' => [1926, 2, null]],
            ],
            [
                'payload' => [
                    'name' => 'Pete Muselin',
                    'first_name' => 'Pete',
                    'last_name' => 'Muselin',
                    'description' => "Pete Muselin, a Croatian-American barbers'-union secretary in the steel town of Woodlawn, Pennsylvania (a Jones & Laughlin company town), was seized in the Armistice Day 1926 red raid on a workers' meeting and prosecuted under Pennsylvania's Flynn Anti-Sedition Act for possessing radical literature. Convicted with Milan Resetar and Tom Zima — the 'Woodlawn Three' — he served five years in the Allegheny County Workhouse at Blawnox. His later account, 'The Steel Fist in a Pennsylvania Company Town,' became a classic description of company-town repression.",
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted under Pennsylvania's Flynn Anti-Sedition Act after the Armistice Day 1926 raid on a Woodlawn workers' meeting (the 'Woodlawn Three' case).",
                        'convicted' => 'Convicted, 1927',
                        'sentence' => 'Five years in the Allegheny County Workhouse at Blawnox.',
                        'institution_name' => 'Allegheny County Workhouse',
                        'institution_city' => 'Blawnox',
                        'institution_state' => 'Pennsylvania',
                    ]],
                ],
                'dates' => ['arrest_date' => [1926, 11, null]],
            ],
            [
                'payload' => [
                    'name' => 'Milan Resetar',
                    'first_name' => 'Milan',
                    'last_name' => 'Resetar',
                    'description' => "Milan Resetar, a Croatian-American steel worker in Woodlawn, Pennsylvania, was arrested in the Armistice Day 1926 red raid on a workers' meeting in the Jones & Laughlin company town and convicted under Pennsylvania's Flynn Anti-Sedition Act with Pete Muselin and Tom Zima — the 'Woodlawn Three.' Sentenced to five years in the Allegheny County Workhouse at Blawnox, he became gravely ill in prison; accounts of the case report that he died there, making him one of the era's prison martyrs.",
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party'],
                    'era' => '1920s',
                    'released' => false,
                    'cases' => [[
                        'charges' => "Convicted under Pennsylvania's Flynn Anti-Sedition Act after the Armistice Day 1926 Woodlawn raid (the 'Woodlawn Three' case).",
                        'convicted' => 'Convicted, 1927',
                        'sentence' => 'Five years in the Allegheny County Workhouse at Blawnox; accounts of the case report that he died in the workhouse.',
                        'institution_name' => 'Allegheny County Workhouse',
                        'institution_city' => 'Blawnox',
                        'institution_state' => 'Pennsylvania',
                    ]],
                ],
                'dates' => ['arrest_date' => [1926, 11, null]],
            ],
            [
                'payload' => [
                    'name' => 'Tom Zima',
                    'first_name' => 'Tom',
                    'last_name' => 'Zima',
                    'description' => "Tom Zima, a Woodlawn, Pennsylvania worker whose house — with its small collection of radical books — was the target of the Armistice Day 1926 red raid in the Jones & Laughlin company town, was convicted under Pennsylvania's Flynn Anti-Sedition Act with Pete Muselin and Milan Resetar as one of the 'Woodlawn Three,' and served five years in the Allegheny County Workhouse at Blawnox.",
                    'state' => 'Pennsylvania',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted under Pennsylvania's Flynn Anti-Sedition Act after the Armistice Day 1926 Woodlawn raid (the 'Woodlawn Three' case).",
                        'convicted' => 'Convicted, 1927',
                        'sentence' => 'Five years in the Allegheny County Workhouse at Blawnox.',
                        'institution_name' => 'Allegheny County Workhouse',
                        'institution_city' => 'Blawnox',
                        'institution_state' => 'Pennsylvania',
                    ]],
                ],
                'dates' => ['arrest_date' => [1926, 11, null]],
            ],
            [
                'payload' => [
                    'name' => 'Albert Weisbord',
                    'first_name' => 'Albert',
                    'last_name' => 'Weisbord',
                    'description' => "Albert Weisbord, the young Harvard-trained organizer who led the great 1926 Passaic, New Jersey textile strike of 15,000 mill workers — the first mass strike led by American communists — was arrested and jailed repeatedly through the strike year, held at one point under \$30,000 bail and facing multiple indictments (including one for 'predicting the overthrow of the government' in a speech). The prosecutions were used as a club against the strike and were dropped as it wound down. He later founded the Communist League of Struggle.",
                    'state' => 'New Jersey',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Workers (Communist) Party', 'United Front Committee of Textile Workers'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Arrested and jailed repeatedly while leading the 1926 Passaic textile strike; held under \$30,000 bail on multiple indictments used against the strike.",
                        'convicted' => 'Indictments eventually dropped as the strike ended',
                        'sentence' => 'Repeated short jailings during the strike year; never served a prison term.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1926, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Jack Rubenstein',
                    'first_name' => 'Jack',
                    'last_name' => 'Rubenstein',
                    'description' => "Jack Rubenstein, a young picket-line leader of the 1926 Passaic textile strike, was arrested repeatedly and beaten by police during the strike, held at one point under \$10,000 bail, and in 1927 was convicted on an assault-and-battery frame-up and served six months in the Bergen County jail — one of the era's emblematic strike prosecutions reported by the ILD's Labor Defender.",
                    'state' => 'New Jersey',
                    'gender' => 'Male',
                    'ideologies' => ['Communism', 'Labor organizing'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Arrested repeatedly as a Passaic strike picket leader; convicted in 1927 on an assault-and-battery charge the defense called a frame-up.',
                        'convicted' => 'Convicted, 1927',
                        'sentence' => 'Six months and \$500; served in the Bergen County jail.',
                        'institution_name' => 'Bergen County Jail',
                        'institution_city' => 'Hackensack',
                        'institution_state' => 'New Jersey',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1927, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Ben Gold',
                    'first_name' => 'Ben',
                    'last_name' => 'Gold',
                    'description' => "Ben Gold, the communist leader of the New York furriers and architect of the victorious 1926 fur workers' strike that won the 40-hour week, was the chief defendant when the state answered the strike with mass prosecutions — the ILD's Labor Defender reported some sixty union members jailed and nine, Gold at their head, convicted in 1927. He went on to lead the International Fur and Leather Workers Union for decades and was later prosecuted again in the McCarthy era over his Taft-Hartley affidavit.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Furriers Union', 'Communist Party USA'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Chief defendant in the mass prosecutions that followed the victorious 1926 New York furriers' strike; one of nine convicted (Labor Defender, June 1927).",
                        'convicted' => 'Convicted, 1927',
                        'sentence' => 'Jailed with some sixty fellow strikers during the prosecutions; sentence details per the era\'s press.',
                    ]],
                ],
                'dates' => ['arrest_date' => [1926, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'James Larkin',
                    'first_name' => 'James',
                    'last_name' => 'Larkin',
                    'aka' => 'Big Jim Larkin',
                    'description' => "James 'Big Jim' Larkin, the towering Irish labor leader and founder of the Irish Transport and General Workers' Union, was in the United States during the First Red Scare and was convicted in 1920 under New York's criminal anarchy law for his part in founding communist organizations. He served nearly three years in Sing Sing and Comstock until Governor Al Smith — calling the prosecution political — pardoned him in January 1923, after which he was deported to Ireland, where he remained a dominant figure of Irish labor.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Socialism', 'Syndicalism'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted under New York's criminal anarchy law (1920) for communist organizing during the First Red Scare.",
                        'convicted' => 'Convicted, 1920; pardoned by Governor Al Smith, January 1923',
                        'sentence' => 'Five to ten years; served nearly three years at Sing Sing and Comstock before his pardon and deportation to Ireland.',
                        'institution_name' => 'Sing Sing Correctional Facility',
                        'institution_city' => 'Ossining',
                        'institution_state' => 'New York',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1920, null, null], 'release_date' => [1923, 1, null]],
            ],
            [
                'payload' => [
                    'name' => 'Harry Winitsky',
                    'first_name' => 'Harry',
                    'last_name' => 'Winitsky',
                    'description' => "Harry Winitsky, executive secretary of the Communist Party of New York, was convicted in 1920 under the state's criminal anarchy law in the same Red Scare prosecutions that imprisoned Benjamin Gitlow and James Larkin. Sentenced to five to ten years, he served in Sing Sing until Governor Al Smith pardoned the criminal-anarchy prisoners in 1923.",
                    'state' => 'New York',
                    'gender' => 'Male',
                    'ideologies' => ['Communism'],
                    'affiliation' => ['Communist Party of America'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted under New York's criminal anarchy law (1920) as executive secretary of the Communist Party of New York.",
                        'convicted' => 'Convicted, 1920; pardoned by Governor Al Smith',
                        'sentence' => 'Five to ten years; served at Sing Sing until his 1923 pardon.',
                        'institution_name' => 'Sing Sing Correctional Facility',
                        'institution_city' => 'Ossining',
                        'institution_state' => 'New York',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1920, null, null], 'release_date' => [1923, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Alfred Wagenknecht',
                    'first_name' => 'Alfred',
                    'last_name' => 'Wagenknecht',
                    'description' => "Alfred Wagenknecht, Ohio state secretary of the Socialist Party and later a founder of American communism, was convicted in 1917 with Charles E. Ruthenberg and Charles Baker for anti-war and anti-conscription agitation in Cleveland, and served a year in the Canton, Ohio workhouse — the imprisonment Eugene Debs invoked in the Canton speech that sent Debs himself to prison. Wagenknecht went on to decades of communist and labor-defense organizing, including leading relief in the Passaic strike.",
                    'state' => 'Ohio',
                    'gender' => 'Male',
                    'ideologies' => ['Socialism', 'Communism'],
                    'affiliation' => ['Socialist Party of America', 'Communist Party USA'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted in 1917 with C. E. Ruthenberg and Charles Baker for anti-war, anti-conscription agitation in Cleveland.',
                        'convicted' => 'Convicted, 1917',
                        'sentence' => 'One year in the Canton, Ohio workhouse.',
                        'institution_name' => 'Canton Workhouse',
                        'institution_city' => 'Canton',
                        'institution_state' => 'Ohio',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1919, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Charles Baker',
                    'first_name' => 'Charles',
                    'last_name' => 'Baker',
                    'description' => "Charles Baker, an Ohio Socialist Party organizer, was convicted in 1917 with Charles E. Ruthenberg and Alfred Wagenknecht for anti-war and anti-conscription agitation in Cleveland, and served a year in the Canton, Ohio workhouse. It was outside that workhouse, at a rally for the three imprisoned socialists, that Eugene Debs gave the June 1918 Canton speech for which Debs was himself imprisoned under the Espionage Act.",
                    'state' => 'Ohio',
                    'gender' => 'Male',
                    'ideologies' => ['Socialism'],
                    'affiliation' => ['Socialist Party of America'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted in 1917 with C. E. Ruthenberg and Alfred Wagenknecht for anti-war, anti-conscription agitation in Cleveland.',
                        'convicted' => 'Convicted, 1917',
                        'sentence' => 'One year in the Canton, Ohio workhouse.',
                        'institution_name' => 'Canton Workhouse',
                        'institution_city' => 'Canton',
                        'institution_state' => 'Ohio',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1918, null, null], 'release_date' => [1919, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Matthew A. Schmidt',
                    'first_name' => 'Matthew',
                    'last_name' => 'Schmidt',
                    'aka' => 'Matt Schmidt',
                    'description' => "Matthew A. Schmidt, an anarchist carpenter and comrade of the McNamara brothers, was convicted in 1915 of murder for the 1910 Los Angeles Times building dynamiting and sentenced to life. He spent decades in San Quentin (no. 30704), where the ILD's Labor Defender printed his letters as one of the country's longest-serving class-war prisoners; he was finally paroled in 1939.",
                    'state' => 'California',
                    'gender' => 'Male',
                    'inmate_number' => '30704',
                    'ideologies' => ['Anarchism', 'Labor organizing'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of murder in 1915 for the 1910 Los Angeles Times building dynamiting (the McNamara case).',
                        'convicted' => 'Convicted, 1915',
                        'sentence' => 'Life; held at San Quentin (no. 30704); paroled in 1939.',
                        'institution_name' => 'San Quentin State Prison',
                        'institution_city' => 'San Quentin',
                        'institution_state' => 'California',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1915, 2, null], 'release_date' => [1939, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'David Caplan',
                    'first_name' => 'David',
                    'last_name' => 'Caplan',
                    'description' => "David Caplan, an anarchist comrade of Matthew Schmidt, evaded arrest for five years after the 1910 Los Angeles Times dynamiting before being captured and convicted of manslaughter at a 1916 retrial. Sentenced to ten years, he served at San Quentin and was released in the early 1920s — one of the last prisoners of the Times case beside Schmidt and J. B. McNamara.",
                    'state' => 'California',
                    'gender' => 'Male',
                    'ideologies' => ['Anarchism'],
                    'era' => '1910s',
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of manslaughter (1916 retrial) in connection with the 1910 Los Angeles Times dynamiting.',
                        'convicted' => 'Convicted, 1916',
                        'sentence' => 'Ten years at San Quentin; released in the early 1920s.',
                        'institution_name' => 'San Quentin State Prison',
                        'institution_city' => 'San Quentin',
                        'institution_state' => 'California',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1916, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Edgar Combs',
                    'first_name' => 'Edgar',
                    'last_name' => 'Combs',
                    'description' => "Edgar Combs, a union miner in the West Virginia mine wars, was the only man to serve a long sentence out of the more than eight hundred indicted after the 1921 armed miners' march on Logan County (the Battle of Blair Mountain). Convicted of the murder of a deputy in the Sharples raid that preceded the march, he was sentenced to life — later reduced to eleven years — at the Moundsville penitentiary (no. 13381). The ILD carried his letters in Labor Defender until Governor Gore's reprieve freed him on 5 January 1927.",
                    'state' => 'West Virginia',
                    'gender' => 'Male',
                    'inmate_number' => '13381',
                    'ideologies' => ['Industrial unionism'],
                    'affiliation' => ['United Mine Workers of America'],
                    'era' => '1920s',
                    'released' => true,
                    'cases' => [[
                        'charges' => "Convicted of murder of a deputy in the prosecutions following the 1921 armed miners' march on Logan County (Battle of Blair Mountain) — the only one of 800+ indicted to serve a long term.",
                        'convicted' => 'Convicted, 1922',
                        'sentence' => 'Life, reduced to eleven years; held at Moundsville (no. 13381); freed by reprieve on 5 January 1927.',
                        'institution_name' => 'West Virginia State Penitentiary',
                        'institution_city' => 'Moundsville',
                        'institution_state' => 'West Virginia',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1922, null, null], 'release_date' => [1927, 1, 5]],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
