<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 17 — the final year — of the ILD Labor Defender mining, covering the
 * whole 1937 volume (Vol. XI/XIII, Jan–Dec), the magazine's last full year
 * before the ILD folded into the National Federation for Constitutional
 * Liberties. 1937 brought the CIO sit-down and Little Steel strikes, the last
 * Scottsboro trials, the freeing of Angelo Herndon (Herndon v. Lowry), the
 * Ponce Massacre in Puerto Rico, and the Chambers v. Florida coerced-confession
 * case.
 *
 * This adds the clearly-attested NEW prisoners of 1937. Marquee cases:
 *  - Chambers v. Florida (the "Little Scottsboro" tortured-confession case);
 *  - the Ponce Massacre / Puerto Rican Nationalist defendants;
 *  - the Galena, Kansas Mine-Mill murder case and the Birmingham Republic
 *    Steel strike;
 *  - the McKeesport anti-war case, the Tampa/Puentes deportation family, the
 *    Burlington NC textile case, and scattered Little Steel and frame-up cases.
 *
 * Cases already in the database are skipped: Scottsboro, Mooney/Billings,
 * McNamara/Schmidt, the Sacramento CAWIU, De Jonge, Herndon, the Gallup
 * miners, King-Ramsay-Conner, the Modesto pair (Silva/Stanfield/Vic Johnson),
 * Brown v. Mississippi, the Tampa flogging, Ned Cobb, Jess Hollins, Paul
 * Butash, Clyde Allen, Jack Barton, Charles Bock, Lawrence Simpson, Alfred
 * Miller, Pedro Albizu Campos, Raymond McSurley, and Ben Boloff/Kyle Pugh.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1937Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1937';

    protected $description = 'Add the 1937 Labor Defender class-war prisoners (Chambers v. Florida, the Ponce Massacre, the Galena KS and Birmingham steel cases, and the last of the ILD frame-ups)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── CHAMBERS v. FLORIDA ──────────────────────────────────────────
        $chambersBase = "Izell Chambers, Jack Williamson, Charlie Davis and Walter Woodard were among a group of Black men rounded up near Fort Lauderdale, Florida after the 1933 robbery-murder of an elderly white man and sentenced to death on confessions extracted in days of unbroken interrogation. Their appeal, argued by Thurgood Marshall, produced Chambers v. Florida (1940), in which the U.S. Supreme Court reversed the convictions and held that confessions wrung from prisoners by sustained coercion violate due process — a landmark decision.";
        foreach ([
            ['Izell Chambers', 'Izell', 'Chambers'],
            ['Jack Williamson', 'Jack', 'Williamson'],
            ['Charlie Davis', 'Charlie', 'Davis'],
            ['Walter Woodard', 'Walter', 'Woodard'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the four Black Florida defendants sentenced to death on coerced confessions whose case became the landmark Chambers v. Florida. ".$chambersBase,
                'state' => 'Florida', 'gender' => 'Male', 'race' => 'Black',
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of murder on confessions extracted by prolonged interrogation near Fort Lauderdale, Florida.',
                    'convicted' => 'Sentenced to death; reversed in Chambers v. Florida (1940)',
                    'sentence' => 'Death; conviction overturned by the Supreme Court.',
                    'institution_name' => 'Florida State Prison',
                    'institution_city' => 'Raiford', 'institution_state' => 'Florida',
                ]],
            ], []);
        }

        // ── PONCE MASSACRE / PUERTO RICAN NATIONALISTS ───────────────────
        foreach ([
            ['Julio Pinto Gandia', 'Julio', 'Pinto Gandia'],
            ['Lorenzo Pinero', 'Lorenzo', 'Pinero'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a leader of the Puerto Rican Nationalist Party prosecuted in 1937 in the wave of repression around the 21 March 1937 Ponce Massacre, when police fired on a Palm Sunday march at Ponce, killing some twenty and wounding hundreds; the ILD defended the jailed Nationalists.",
                'state' => 'Puerto Rico', 'gender' => 'Male',
                'ideologies' => ['Nationalism', 'Anti-imperialism'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Prosecuted for Puerto Rican Nationalist activity in the repression around the Ponce Massacre.',
                    'convicted' => 'Held / indicted, 1937',
                    'sentence' => 'Held for trial; defended by the ILD.',
                    'institution_state' => 'Puerto Rico',
                ]],
            ], ['arrest_date' => [1937, 3, 21]]);
        }

        // ── McKEESPORT, PA ANTI-WAR "RIOT" CASE ──────────────────────────
        $mckeesportBase = "In 1937 McKeesport, Pennsylvania prosecuted a group of workers for \"riot\" after an anti-war demonstration, a free-speech case the ILD took up.";
        foreach ([
            ['George Alexander', 'George', 'Alexander', 'Male', "a Greek-born worker convicted and also faced with deportation"],
            ['Carolyn Hart', 'Carolyn', 'Hart', 'Female', "a twenty-two-year-old sentenced to two years at the Muncy women's reformatory"],
            ['Goust Safos', 'Goust', 'Safos', 'Male', "a convicted defendant"],
            ['Lena Alexander', 'Lena', 'Alexander', 'Female', "a convicted defendant"],
        ] as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the McKeesport, Pennsylvania anti-war \"riot\" case of 1937. ".$mckeesportBase,
                'state' => 'Pennsylvania', 'gender' => $gender,
                'ideologies' => ['Communism', 'Anti-war'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted of "riot" after an anti-war demonstration at McKeesport, Pennsylvania.',
                    'convicted' => 'Convicted, 1937',
                    'sentence' => 'Held / imprisoned in the McKeesport case.',
                    'institution_city' => 'McKeesport', 'institution_state' => 'Pennsylvania',
                ]],
            ], ['arrest_date' => [1937, null, null]]);
        }

        // ── TAMPA / PUENTES DEPORTATION FAMILY ───────────────────────────
        foreach ([
            ['Lorenzo Puentes', 'Lorenzo', 'Puentes', "a former president of Cigar Makers' Local 500 held for deportation to Cuba as a Communist Party member"],
            ['Wilfredo Puentes', 'Wilfredo', 'Puentes', "Lorenzo's seventeen-year-old son, held for deportation with his father"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in the 1937 Tampa, Florida deportation case.",
                'state' => 'Florida', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Cigar Makers International Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held for deportation to Cuba for Communist and cigar-union activity at Tampa.',
                    'convicted' => 'Held for deportation, 1937',
                    'sentence' => 'Held pending deportation; defended by the ILD.',
                    'institution_city' => 'Tampa', 'institution_state' => 'Florida',
                ]],
            ], ['arrest_date' => [1937, null, null]]);
        }

        // ── GALENA, KANSAS MINE-MILL MURDER CASE ─────────────────────────
        foreach ([
            ['William Webb', 'William', 'Webb'],
            ['Ira Tackett', 'Ira', 'Tackett'],
            ['George Bankhead', 'George', 'Bankhead'],
            ['Ernest Honeywell', 'Ernest', 'Honeywell'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of ten CIO Mine, Mill and Smelter Workers charged with murder after a vigilante attack on their union hall at Galena, Kansas in 1937.",
                'state' => 'Kansas', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Mine, Mill and Smelter Workers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with murder after a vigilante attack on the Mine-Mill union hall at Galena, Kansas.',
                    'convicted' => 'Held on murder charges, 1937',
                    'sentence' => 'Held for trial; defended by the ILD.',
                    'institution_city' => 'Galena', 'institution_state' => 'Kansas',
                ]],
            ], ['arrest_date' => [1937, null, null]]);
        }

        // ── BIRMINGHAM REPUBLIC STEEL / THOMAS FURNACE STRIKE ────────────
        foreach ([
            ['John Catchings', 'John', 'Catchings'],
            ['L. C. Tate', 'L. C.', 'Tate'],
            ['George Porter', 'George', 'Porter'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a jailed leader of Mine-Mill Local 137 in the 1937 Thomas Furnace (Republic Steel) strike at Birmingham, Alabama.",
                'state' => 'Alabama', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Mine, Mill and Smelter Workers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for union activity in the Birmingham Republic Steel strike.',
                    'convicted' => 'Held, 1937',
                    'sentence' => 'Held during the steel strike.',
                    'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
                ]],
            ], ['arrest_date' => [1937, null, null]]);
        }

        // ── MODESTO CASE — remaining named defendants ────────────────────
        foreach ([
            ['Patsy Ciambrelli', 'Patsy', 'Ciambrelli'],
            ['Frank Fitzgerald', 'Frank', 'Fitzgerald'],
            ['James Burrows', 'James', 'Burrows'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was one of the eight \"Modesto Boys\" — maritime workers framed on a dynamite charge by Standard Oil agents after the 1935 tanker strike near Modesto, California and imprisoned at San Quentin or Folsom; pardons were recommended in 1937.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Marine Firemen, Oilers and Watertenders Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Framed on a dynamite-possession charge after the 1935 Modesto tanker strike.',
                    'convicted' => 'Convicted, July 1935; pardons recommended 1937',
                    'sentence' => 'Imprisoned at San Quentin/Folsom; pardoned.',
                    'institution_name' => 'San Quentin State Prison',
                    'institution_city' => 'San Quentin', 'institution_state' => 'California',
                ]],
            ], ['arrest_date' => [1935, 4, 21]]);
        }

        // ── BURLINGTON, N.C. TEXTILE DYNAMITE CASE ───────────────────────
        $mk([
            'name' => 'John L. Anderson', 'first_name' => 'John L.', 'last_name' => 'Anderson',
            'description' => "John L. \"Slim\" Anderson was the president of the Piedmont Council of the United Textile Workers, framed with four other Burlington, North Carolina textile workers on a charge of dynamiting the Holt mill during the September 1934 general textile strike and sentenced to a term on the North Carolina chain gang.",
            'state' => 'North Carolina', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Textile Workers'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed for dynamiting a mill during the 1934 Burlington, North Carolina textile strike.',
                'convicted' => 'Convicted; upheld by the NC Supreme Court',
                'sentence' => 'Two to ten years on the North Carolina chain gang.',
                'institution_name' => 'North Carolina State Prison',
                'institution_city' => 'Raleigh', 'institution_state' => 'North Carolina',
            ]],
        ], []);

        // ── LITTLE STEEL / OTHER STRIKE & FRAME-UP CASES ─────────────────
        $mk([
            'name' => 'Spartacio Alo', 'first_name' => 'Spartacio', 'last_name' => 'Alo',
            'description' => "Spartacio Alo, a lodge president in the Steel Workers Organizing Committee, was among the strikers arrested in the 1937 Little Steel strike that culminated in the Memorial Day Massacre at Republic Steel in Chicago.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Steel Workers Organizing Committee'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the 1937 Little Steel strike.',
                'convicted' => 'Held, 1937',
                'sentence' => 'Held during the steel strike.',
                'institution_city' => 'Chicago', 'institution_state' => 'Illinois',
            ]],
        ], ['arrest_date' => [1937, 5, 30]]);
        $mk([
            'name' => 'Edward Woodworth', 'first_name' => 'Edward', 'last_name' => 'Woodworth',
            'description' => "Edward Woodworth was a maritime striker imprisoned (inmate No. 19021) in the New Jersey State Penitentiary in 1937 for strike activity.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['International Seamen\'s Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Imprisoned for maritime-strike activity in New Jersey.',
                'convicted' => 'Imprisoned, 1937',
                'sentence' => 'Held in the New Jersey State Penitentiary.',
                'institution_name' => 'New Jersey State Penitentiary',
                'institution_state' => 'New Jersey',
            ]],
        ], []);
        $mk([
            'name' => 'Robert Webber', 'first_name' => 'Robert', 'last_name' => 'Webber',
            'description' => "Robert Webber was framed on a \"dynamite\" charge and sentenced to one to ten years at the Moundsville penitentiary in West Virginia for his part in a miners' strike.",
            'state' => 'West Virginia', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a dynamite charge in a West Virginia miners\' strike.',
                'convicted' => 'Convicted',
                'sentence' => 'One to ten years at the Moundsville penitentiary.',
                'institution_name' => 'West Virginia Penitentiary',
                'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
            ]],
        ], []);
        foreach ([
            ['Allen Randolph', 'Allen', 'Randolph'],
            ['Robert Scott', 'Robert', 'Scott'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a Missouri labor militant jailed on a strike / sedition charge in 1936–37, defended by the ILD.",
                'state' => 'Missouri', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed on a strike / sedition charge in Missouri.',
                    'convicted' => 'Held, 1936–37',
                    'sentence' => 'Held on the Missouri charge.',
                    'institution_state' => 'Missouri',
                ]],
            ], ['arrest_date' => [1936, null, null]]);
        }
        $mk([
            'name' => 'Q. B. McCain', 'first_name' => 'Q. B.', 'last_name' => 'McCain',
            'description' => "Q. B. McCain was the president of the Miami painters' union, jailed in 1937 amid the anti-labor drive in Florida.",
            'state' => 'Florida', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Brotherhood of Painters'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed as a union leader at Miami, Florida.',
                'convicted' => 'Held, 1937',
                'sentence' => 'Held in the Florida anti-labor drive.',
                'institution_city' => 'Miami', 'institution_state' => 'Florida',
            ]],
        ], ['arrest_date' => [1937, null, null]]);
        $mk([
            'name' => 'John McNeil', 'first_name' => 'John', 'last_name' => 'McNeil',
            'description' => "John McNeil was a Black worker of Harlem, New York framed in a 1937 case the ILD took up.",
            'state' => 'New York', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held on a frame-up charge in Harlem, New York.',
                'convicted' => 'Held, 1937',
                'sentence' => 'Held on the frame-up; defended by the ILD.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], []);

        // ── PRISONERS KILLED IN CUSTODY ──────────────────────────────────
        $mk([
            'name' => 'Dorothy Calhoun', 'first_name' => 'Dorothy', 'last_name' => 'Calhoun',
            'description' => "Dorothy Calhoun was a young worker who died in custody in Georgia, one of the cases of deaths in Southern jails the Labor Defender documented in 1937.",
            'state' => 'Georgia', 'gender' => 'Female',
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Held in a Georgia jail, where she died.',
                'convicted' => 'Died in custody',
                'sentence' => 'Died in a Georgia jail.',
                'institution_state' => 'Georgia',
            ]],
        ], []);
        $mk([
            'name' => 'Earl Barlow', 'first_name' => 'Earl', 'last_name' => 'Barlow',
            'description' => "Earl Barlow was a Fort Worth, Texas Unemployed Council leader arrested on 31 August 1933 and, the ILD charged, \"third-degreed to death\" in police custody.",
            'state' => 'Texas', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Unemployed Councils'],
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Arrested as an Unemployed Council leader at Fort Worth, Texas.',
                'convicted' => 'Died in custody, 1933',
                'sentence' => 'Beaten to death in police custody.',
                'institution_city' => 'Fort Worth', 'institution_state' => 'Texas',
            ]],
        ], ['arrest_date' => [1933, 8, 31]]);

        // ── OREGON CRIMINAL SYNDICALISM (historical, from the repeal) ────
        $mk([
            'name' => 'Joseph Laundy', 'first_name' => 'Joseph', 'last_name' => 'Laundy',
            'description' => "Joseph Laundy was an Industrial Workers of the World member convicted under Oregon's criminal-syndicalism law in 1919 and sentenced to three years — one of the earliest victims of the law repealed in 1937 after the De Jonge decision.",
            'state' => 'Oregon', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Industrial Workers of the World'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted under Oregon's criminal-syndicalism law as an IWW member.",
                'convicted' => 'Convicted, 1919',
                'sentence' => 'Three years.',
                'institution_state' => 'Oregon',
            ]],
        ], ['incarceration_date' => [1919, null, null]]);

        // ── INSERT ───────────────────────────────────────────────────────
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

        $this->info("\nDone. Processed {$added} of ".count($people)." 1937 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }
}
