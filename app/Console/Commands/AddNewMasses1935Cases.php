<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 3 — 1935.
 *
 * 1935 was heavily covered by the ILD Labor Defender, so most of the year's
 * cases are already in the database (the Gallup, New Mexico defendants; the
 * Sacramento CAWIU group; Scottsboro; Herndon; Mooney & Billings; the Bremen
 * anti-Nazi case; Stella Petrosky and the German deportation detainees; the
 * Burlington NC textile six; Ward Rodgers; Powers Hapgood; Ferrero & Sallitto;
 * Ed Sears) — all skipped here.
 *
 * This adds the genuinely-new, individually named US class-war prisoners of
 * 1935 that had no record: the Arkansas Southern Tenant Farmers' Union terror,
 * a Wisconsin and an Arizona relief-strike case, the North Dakota/Montana
 * "penny-sale" federal farm prosecution, the Alabama Black-Belt "Red" terror,
 * the Newark raid on a performance of "Waiting for Lefty," the Wilkes-Barre
 * anthracite dynamite frame-up, a South Carolina textile-strike case, the
 * Brooklyn May's Department Store conspiracy indictments (under New York's
 * revived 1836 anti-conspiracy statute), and the "March of the Cripples" of
 * the League for the Physically Handicapped.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1935Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1935';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1935 (Arkansas STFU, the ND/MT penny-sale farmers, Alabama terror, the Waiting for Lefty raid, the Brooklyn May\'s conspiracy case, and more)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── ARKANSAS — SOUTHERN TENANT FARMERS' UNION TERROR ────────────
        $mk([
            'name' => 'Claude C. Williams', 'first_name' => 'Claude', 'last_name' => 'Williams',
            'description' => "The Rev. Claude C. Williams (1895–1979) was a radical Presbyterian minister and Southern Tenant Farmers' Union organizer, earlier driven from his church at Paris, Arkansas for his union work. He was arrested during the 1935 Fort Smith (Sebastian County) FERA relief-workers' strike and charged with 'barratry,' one of the best-known Southern labor-radical preachers of the Depression.",
            'state' => 'Arkansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested and charged with 'barratry' in the Fort Smith relief-workers strike.",
                'convicted' => 'Arrested, 1935',
                'sentence' => 'Held; defended by the ILD.',
                'institution_city' => 'Fort Smith', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        $mk([
            'name' => 'L. M. Mills', 'first_name' => 'L. M.', 'last_name' => 'Mills',
            'description' => "L. M. Mills was an organizer for the Southern Tenant Farmers' Union at Tyronza, Arkansas. In early 1935 he was convicted of 'interfering with labor,' fined $100, and imprisoned for lack of bail — jailed amid the Arkansas planter terror against the STFU.",
            'state' => 'Arkansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Convicted of 'interfering with labor' as an STFU organizer.",
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Fined $100; jailed for lack of bail.',
                'institution_city' => 'Tyronza', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        $mk([
            'name' => 'A. R. Brookins', 'first_name' => 'A. R.', 'last_name' => 'Brookins',
            'description' => "The Rev. A. R. Brookins was a Black preacher and Southern Tenant Farmers' Union member jailed at Marked Tree, Arkansas in the 1935 planter raids on the STFU — the same terror campaign that produced the Ward Rodgers conviction.",
            'state' => 'Arkansas', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the raids on the Southern Tenant Farmers\' Union.',
                'convicted' => 'Jailed, 1935',
                'sentence' => 'Jailed.',
                'institution_city' => 'Marked Tree', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        $mk([
            'name' => 'R. L. Butler', 'first_name' => 'R. L.', 'last_name' => 'Butler',
            'description' => "R. L. Butler was a Black schoolteacher jailed at Marked Tree, Arkansas in the 1935 raids on the Southern Tenant Farmers' Union.",
            'state' => 'Arkansas', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the raids on the Southern Tenant Farmers\' Union.',
                'convicted' => 'Jailed, 1935',
                'sentence' => 'Jailed.',
                'institution_city' => 'Marked Tree', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        $mk([
            'name' => 'T. F. Schultz', 'first_name' => 'T. F.', 'last_name' => 'Schultz',
            'description' => "T. F. Schultz was a white relief worker jailed at Marked Tree, Arkansas in the 1935 raids on the Southern Tenant Farmers' Union.",
            'state' => 'Arkansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed in the raids on the Southern Tenant Farmers\' Union.',
                'convicted' => 'Jailed, 1935',
                'sentence' => 'Jailed.',
                'institution_city' => 'Marked Tree', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        // ── WISCONSIN — HORLICK'S STRIKE ────────────────────────────────
        $mk([
            'name' => 'Sam Herman', 'first_name' => 'Sam', 'last_name' => 'Herman',
            'description' => "Sam Herman was a Communist Party organizer at Racine, Wisconsin during the 1934–35 Horlick's malted-milk strike. After being kidnapped and beaten by vigilantes, he was himself arrested on a 'criminal libel' charge with bail set at $2,500.",
            'state' => 'Wisconsin', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested on a 'criminal libel' charge during the Horlick's strike.",
                'convicted' => 'Arrested, 1935',
                'sentence' => 'Held on $2,500 bail.',
                'institution_city' => 'Racine', 'institution_state' => 'Wisconsin',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        // ── ARIZONA — PHOENIX FERA RELIEF STRIKE ────────────────────────
        $mk([
            'name' => 'Clay Naff', 'first_name' => 'Clay', 'last_name' => 'Naff',
            'description' => "Clay Naff was a militant leader of the 1935 Phoenix, Arizona FERA relief-workers' strike. Arrested and charged with 'riot,' he was convicted and sentenced to one to two years in the state penitentiary at Florence, Arizona.",
            'state' => 'Arizona', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Charged with 'riot' in the Phoenix relief-workers strike.",
                'convicted' => 'Convicted, 1935',
                'sentence' => 'One to two years at the Arizona State Penitentiary, Florence.',
                'institution_name' => 'Arizona State Penitentiary',
                'institution_city' => 'Florence', 'institution_state' => 'Arizona',
            ]],
        ], ['incarceration_date' => [1935, null, null]]);

        // ── NORTH DAKOTA / MONTANA — "PENNY-SALE" FEDERAL FARM CASE ──────
        $ndBase = "one of the Westby, North Dakota / Plentywood, Montana farmers of the United Farmers' League arrested by U.S. Marshals in 1935 and charged federally with 'conspiracy to defraud the United States government' for organizing 'penny sales' and blocking foreclosure auctions. The indictment was later thrown out as defective, but the men were held on bond.";
        foreach ([
            ['Victor Nielsen', 'Victor', 'Nielsen', 'North Dakota', 'Westby', "Victor Nielsen was seized by a U.S. Marshal and was {$ndBase}"],
            ['Thorvald Nielsen', 'Thorvald', 'Nielsen', 'North Dakota', 'Westby', "Thorvald Nielsen, brother of Victor, was held five days in a Minot cell — {$ndBase}"],
            ['Alfred Hjelm', 'Alfred', 'Hjelm', 'North Dakota', 'Westby', "Alfred Hjelm was {$ndBase}"],
            ['Ed Ferguson', 'Ed', 'Ferguson', 'North Dakota', 'Westby', "Ed Ferguson was {$ndBase}"],
            ['Elmer Dodin', 'Elmer', 'Dodin', 'North Dakota', 'Westby', "Elmer Dodin was {$ndBase}"],
            ['Carl Christofferson', 'Carl', 'Christofferson', 'North Dakota', 'Westby', "Carl Christofferson was {$ndBase}"],
            ['Simon Swanson', 'Simon', 'Swanson', 'Montana', 'Plentywood', "Simon Swanson was {$ndBase}"],
        ] as [$name, $first, $last, $state, $city, $bio]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $bio,
                'state' => $state, 'gender' => 'Male',
                'ideologies' => ['Farm organizing'],
                'affiliation' => ["United Farmers' League"],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Charged federally with 'conspiracy to defraud the United States government' for anti-foreclosure 'penny sales.'",
                    'convicted' => 'Arrested / held on bond, 1935',
                    'sentence' => 'Held on bond; the federal indictment was dismissed as defective.',
                    'institution_city' => $city, 'institution_state' => $state,
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }

        // ── ALABAMA — BLACK-BELT "RED" TERROR ───────────────────────────
        $mk([
            'name' => 'Israel Berlin', 'first_name' => 'Israel', 'last_name' => 'Berlin',
            'description' => "Israel Berlin was a white Communist worker in Birmingham, Alabama imprisoned in 1935 on a framed 'literature' charge under the state's ban on radical publications. He was held in solitary confinement in the basement of the Birmingham city jail, his health broken, with roughly ten months still to serve.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Framed on a 'literature' charge under the Alabama ban on radical publications.",
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Held in solitary confinement in the Birmingham city jail.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        $mk([
            'name' => 'Robert Washington', 'first_name' => 'Robert', 'last_name' => 'Washington',
            'description' => "Robert Washington was a Black leader in Selma, Alabama, jailed for four days in 1935 on a pretextual 'aggravated vagrancy' charge amid the Black-Belt anti-labor terror. On his release he was seized at gunpoint on the jail steps by eight vigilantes, driven into the country, stripped, and lashed unconscious — an atrocity the ILD publicized in 'Terror in Alabama.'",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed on a pretextual 'aggravated vagrancy' charge.",
                'convicted' => 'Jailed, 1935',
                'sentence' => 'Held four days; flogged by vigilantes on release.',
                'institution_city' => 'Selma', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        // ── NEW JERSEY — "WAITING FOR LEFTY" RAID ───────────────────────
        $mk([
            'name' => 'Joe Gilbert', 'first_name' => 'Joe', 'last_name' => 'Gilbert',
            'description' => "Joe Gilbert was a militant leader of the 1934 New York City taxi drivers' strike. On June 2, 1935 he was arrested in Newark, New Jersey as chairman of a banned performance of Clifford Odets's strike play 'Waiting for Lefty' at Ukrainian Hall; one of nine people seized in the police raid, he was held without bail over the weekend.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested in the police raid on a performance of 'Waiting for Lefty.'",
                'convicted' => 'Arrested, 1935',
                'sentence' => 'Held without bail over the weekend.',
                'institution_city' => 'Newark', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1935, 6, 2]]);

        // ── PENNSYLVANIA — WILKES-BARRE ANTHRACITE FRAME-UP ─────────────
        $mk([
            'name' => 'E. P. Jennings', 'first_name' => 'E. P.', 'last_name' => 'Jennings',
            'description' => "E. P. Jennings was a Wilkes-Barre, Pennsylvania printer and civic leader who helped organize the Luzerne County Unemployed League and circulated a petition to impeach the anti-labor 'injunction judge' W. Alfred Valentine during the 1935 Glen Alden Coal Co. strike. He was arrested and framed on a charge of dynamiting the automobile of the judge's daughter after being lured to a hotel by a vanishing state informer.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on a dynamiting charge during the anthracite unemployed movement.',
                'convicted' => 'Arrested, 1935',
                'sentence' => 'Held.',
                'institution_city' => 'Wilkes-Barre', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        // ── SOUTH CAROLINA — PELZER TEXTILE STRIKE ──────────────────────
        $mk([
            'name' => 'George Washington Henson', 'first_name' => 'George Washington', 'last_name' => 'Henson',
            'description' => "George Washington Henson was a 65-year-old union textile worker at Pelzer, South Carolina. During the 1935 textile strike he was first arrested for the murder of striker Gertrude Kelley — a charge dropped when the fatal bullet proved not to be from his shotgun — then indicted with eighteen others for rioting and jailed a week before making bond. Only strikers were jailed; none of those who fired on the strikers were arrested.",
            'state' => 'South Carolina', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for murder (charge dropped), then indicted for rioting in the textile strike.',
                'convicted' => 'Jailed, 1935',
                'sentence' => 'Jailed a week before making bond.',
                'institution_city' => 'Pelzer', 'institution_state' => 'South Carolina',
            ]],
        ], ['arrest_date' => [1935, null, null]]);

        // ── NEW YORK — MAY'S DEPARTMENT STORE CONSPIRACY (Brooklyn) ─────
        $maysBase = "was a striker with Local 1250 of the Department Store Employees Union in the December 1935 strike against May's Department Store in Brooklyn. Amid roughly 102 picket-line arrests, District Attorney William Geoghan revived New York's 1836 anti-conspiracy statute and indicted five strike leaders for criminal conspiracy, each held on $1,000 bail and facing up to three years' imprisonment.";
        foreach ([
            ['Irving Aarons', 'Irving', 'Aarons', 'Male', "Irving Aarons was an organizer for Local 1250 of the Department Store Employees Union and one of five leaders indicted for criminal conspiracy in the December 1935 strike against May's Department Store in Brooklyn, under New York's revived 1836 anti-conspiracy statute; he was held on $1,000 bail facing up to three years."],
            ['Pearl Edison', 'Pearl', 'Edison', 'Female', "Pearl Edison {$maysBase}"],
            ['Marcia Silver', 'Marcia', 'Silver', 'Female', "Marcia Silver {$maysBase}"],
            ['Elsie Monakian', 'Elsie', 'Monakian', 'Female', "Elsie Monakian was dragged from her bed and jailed at 1 a.m. by police, and {$maysBase}"],
        ] as [$name, $first, $last, $gender, $bio]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $bio,
                'state' => 'New York', 'gender' => $gender,
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Department Store Employees Union Local 1250'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Indicted for criminal conspiracy under New York's revived 1836 statute in the May's Department Store strike.",
                    'convicted' => 'Indicted / held, 1935',
                    'sentence' => 'Held on $1,000 bail; faced up to three years.',
                    'institution_city' => 'Brooklyn', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1935, 12, null]]);
        }

        // ── NEW YORK — "MARCH OF THE CRIPPLES" ──────────────────────────
        $crippleBase = "was one of the named participants in the 1935 'March of the Cripples' — a sit-in by disabled unemployed activists of the League for the Physically Handicapped at the New York City relief administration, who were carried out on stretchers, clubbed by police, and then arrested and charged with 'assaulting the police.'";
        foreach ([
            ['Pauline Portugalo', 'Pauline', 'Portugalo', 'Female'],
            ['Hyman Abramowitz', 'Hyman', 'Abramowitz', 'Male'],
            ['Morris Dolinsky', 'Morris', 'Dolinsky', 'Male'],
        ] as [$name, $first, $last, $gender]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} {$crippleBase}",
                'state' => 'New York', 'gender' => $gender,
                'ideologies' => ['Unemployed movement'],
                'affiliation' => ['League for the Physically Handicapped'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Arrested and charged with 'assaulting the police' at the League for the Physically Handicapped sit-in.",
                    'convicted' => 'Arrested, 1935',
                    'sentence' => 'Held.',
                    'institution_city' => 'New York', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1935, null, null]]);
        }

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

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1935 prisoner(s).");

        return self::SUCCESS;
    }
}
