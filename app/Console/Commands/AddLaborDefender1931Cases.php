<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 11 of the ILD Labor Defender mining, covering the whole 1931 volume
 * (Vol. VI/VII, Jan–Dec). 1931 was the ILD's defining year: the Scottsboro
 * case broke in the spring and became the organization's central campaign,
 * and the coal wars — Harlan County, Kentucky and the Pennsylvania–Ohio–West
 * Virginia strike — plus the Alabama Share Croppers Union filled the Southern
 * and Appalachian jails.
 *
 * This adds the clearly-attested NEW prisoners of 1931. Marquee cases:
 *  - the nine Scottsboro defendants;
 *  - the Harlan County "Battle of Evarts" coal-war defendants (National
 *    Miners' Union), incl. the death-sentence bloc and the jailed roster;
 *  - the Camp Hill / Tallapoosa County, Alabama sharecroppers;
 *  - the Portland, Oregon criminal-syndicalism prisoners;
 *  - the Paterson, N.J. silk-strike murder frame-up;
 *  - the Lawrence, Mass. textile strike and the 1931 deportation drive.
 *
 * Cases already in the database are skipped (Imperial Valley, Atlanta Six,
 * Mooney/Billings, McNamara/Schmidt, Centralia, the 1928–30 rosters, Guido
 * Serio, Harry Eisman, Leon Mabille, Ailene Holmes & Mabel Husa, Paul
 * Crouch, John Dos Passos). Deliberately omitted: an OCR-garbled, unlabeled
 * "10-year terms" bloc in the Nov 1931 roster whose case attribution could
 * not be established, and thin surname-only deportation clusters.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefender1931Cases extends Command
{
    protected $signature = 'prisoners:add-labor-defender-1931';

    protected $description = 'Add the 1931 Labor Defender class-war prisoners (Scottsboro, Harlan County coal war, Camp Hill sharecroppers, Oregon CS, Paterson silk strike, deportation drive)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── SCOTTSBORO ────────────────────────────────────────────────────
        $scottsboroBase = "On 25 March 1931 nine Black youths were pulled off a freight train near Paint Rock, Alabama and accused of raping two white women, Victoria Price and Ruby Bates, who had been riding the same train. Tried at Scottsboro within two weeks before all-white juries, eight of the nine were convicted and sentenced to death in April 1931; the youngest, thirteen-year-old Roy Wright, drew a mistrial when jurors deadlocked. The International Labor Defense took over the defense, and the \"Scottsboro Boys\" became the most famous American political case of the decade, producing two landmark Supreme Court decisions — Powell v. Alabama (right to counsel) and Norris v. Alabama (exclusion of Black jurors). Medical evidence showed no assault had occurred, and Ruby Bates later recanted; all nine eventually went free after years in Alabama prisons.";
        $scottsboro = [
            ['Haywood Patterson', 'Haywood', 'Patterson', 18, "Haywood Patterson, of Chattanooga, was tried four times over the Scottsboro case and sentenced to death three times; he later escaped from an Alabama prison and wrote the memoir \"Scottsboro Boy.\""],
            ['Clarence Norris', 'Clarence', 'Norris', 18, "Clarence Norris was sentenced to death in the Scottsboro case; his appeal produced Norris v. Alabama (1935), which struck down the systematic exclusion of Black citizens from Alabama juries. He was the last surviving Scottsboro defendant and was pardoned in 1976."],
            ['Charlie Weems', 'Charlie', 'Weems', 19, "Charlie Weems, the oldest of the nine, was sentenced to death in the Scottsboro case and served the longest term of any of the defendants before being paroled in 1943."],
            ['Ozie Powell', 'Ozie', 'Powell', 15, "Ozie Powell was sentenced to death in the Scottsboro case; his appeal produced Powell v. Alabama (1932), establishing the right to effective counsel in capital cases. He was shot and permanently injured by a deputy in 1936."],
            ['Olen Montgomery', 'Olen', 'Montgomery', 17, "Olen Montgomery, who was nearly blind, was sentenced to death in the Scottsboro case and held in the death house at Kilby Prison before charges against him were dropped in 1937."],
            ['Willie Roberson', 'Willie', 'Roberson', 17, "Willie Roberson, who was severely ill with venereal disease and could barely walk at the time of the alleged assault, was sentenced to death in the Scottsboro case; charges against him were dropped in 1937."],
            ['Eugene Williams', 'Eugene', 'Williams', 13, "Eugene Williams, thirteen at the time of his arrest, was sentenced to death in the Scottsboro case; the conviction was later set aside because of his age and charges were dropped in 1937."],
            ['Andy Wright', 'Andy', 'Wright', 19, "Andy Wright was sentenced to death in the Scottsboro case and was the last of the nine to leave prison, paroled in 1950 after nearly two decades behind bars."],
            ['Roy Wright', 'Roy', 'Wright', 13, "Roy Wright, thirteen and the youngest of the nine, was held at the county jail in Birmingham; his trial ended in a mistrial when jurors who agreed on his guilt deadlocked over whether to impose death on a child. Charges were later dropped."],
        ];
        foreach ($scottsboro as [$name, $first, $last, $age, $bio]) {
            $death = $first === 'Roy' ? false : true;
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $bio.' '.$scottsboroBase,
                'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Falsely accused of raping two white women aboard a freight train near Paint Rock, Alabama.',
                    'convicted' => $death ? 'Convicted and sentenced to death, April 1931 (later overturned)' : 'Mistrial when jurors deadlocked over the death penalty for a child',
                    'sentence' => $death ? 'Death; conviction overturned after years of appeals and imprisonment.' : 'Held awaiting retrial; charges eventually dropped.',
                    'institution_name' => $first === 'Roy' ? 'Jefferson County Jail' : 'Kilby Prison',
                    'institution_city' => $first === 'Roy' ? 'Birmingham' : 'Montgomery',
                    'institution_state' => 'Alabama',
                ]],
            ], ['arrest_date' => [1931, 3, 25]]);
        }

        // ── HARLAN COUNTY KY COAL WAR — death-sentence / murder defendants ─
        $harlanBase = "The Harlan County, Kentucky coal war of 1931 pitted the Communist-led National Miners' Union against the operators, Sheriff John Henry Blair's deputies, and the state militia. After the 5 May 1931 \"Battle of Evarts,\" in which a gun battle left several dead, more than a hundred miners were jailed: dozens were indicted for murder and faced the electric chair, dozens more charged with \"banding and confederating\" or criminal syndicalism. Clarence Darrow and the ILD led the defense.";
        $harlanDeath = [
            'Chas. Shadrick', 'Elbert Shadrick', 'Wm. Hudson', 'Pless Thomas',
            'Ganzie Banks', 'Henry Oliver', 'E. Phillips', 'Andrew Hinch',
        ];
        foreach ($harlanDeath as $full) {
            [$first, $last] = $this->splitName($full);
            $mk([
                'name' => $full, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$full} was one of the Harlan County, Kentucky coal miners indicted for murder and held facing the electric chair after the 5 May 1931 Battle of Evarts, on framed-up charges arising from the National Miners' Union strike. ".$harlanBase,
                'state' => 'Kentucky', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Indicted for murder after the Battle of Evarts during the Harlan County coal strike.',
                    'convicted' => 'Held facing the death penalty on framed murder charges, 1931',
                    'sentence' => 'Held in the Harlan County jail facing the electric chair.',
                    'institution_name' => 'Harlan County Jail',
                    'institution_city' => 'Harlan', 'institution_state' => 'Kentucky',
                ]],
            ], ['arrest_date' => [1931, 5, 5]]);
        }

        // Harlan union leaders & the jailed roster (National Miners' Union).
        $harlanRoster = [
            ['W. B. Jones', "the secretary of the miners' union at Evarts, charged with murder"],
            ['W. M. Hightower', "the president of the miners' union at Evarts, charged with murder"],
            ['C. O. Chamblee', "an Evarts-area miner jailed in the Harlan roundup"],
            ['W. M. Burnett', "an Evarts miner jailed in the Harlan roundup"],
            ['A. L. Benson', "an Evarts miner charged with murder in the Harlan case"],
            ['Asa Cusick', "an Evarts miner charged with murder in the Harlan case"],
            ['F. M. Bratcher', "an Evarts miner jailed in the Harlan roundup"],
            ['Andy Vaughn', "an Evarts miner jailed in the Harlan roundup"],
            ['Walter Camp', "a Gulston miner jailed in the Harlan roundup"],
            ['Hugh Lester', "an Evarts miner jailed in the Harlan roundup"],
            ['John Lester', "an Evarts miner jailed in the Harlan roundup"],
            ['Willie Echols', "a Benham miner jailed in the Harlan roundup"],
            ['Carl Williams', "an Evarts miner jailed in the Harlan roundup"],
            ['Chester Poore', "a Harlan miner held awaiting trial in the Clark County jail at Winchester"],
            ['Roscoe Dameron', "a Harlan miner held awaiting trial"],
            ['Floyd Murphy', "an Evarts miner jailed in the Harlan roundup"],
            ['Jim Reynolds', "a Clover Fork miner held awaiting trial"],
            ['Otto Mills', "a Harlan miner held awaiting trial at Mt. Sterling"],
            ['Alex Reed', "a Harlan miner jailed in the roundup"],
            ['Morris Hansford', "an Evarts miner jailed in the roundup"],
            ['Robert Smith', "a Kitts miner jailed in the roundup"],
            ['William Duncan', "a Harlan miner and National Miners' Union organizer bailed on criminal-syndicalism charges"],
        ];
        foreach ($harlanRoster as [$full, $who]) {
            [$first, $last] = $this->splitName($full);
            $mk([
                'name' => $full, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$full} was {$who} after the 5 May 1931 Battle of Evarts, one of the more than a hundred National Miners' Union miners and sympathizers jailed in the Harlan County, Kentucky coal war. ".$harlanBase,
                'state' => 'Kentucky', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed on murder, "banding and confederating," or criminal-syndicalism charges after the Battle of Evarts in the Harlan County coal strike.',
                    'convicted' => 'Held in the Harlan coal-war roundup, 1931',
                    'sentence' => 'Held in jail pending trial during the Harlan County coal war.',
                    'institution_state' => 'Kentucky',
                ]],
            ], ['arrest_date' => [1931, 5, 5]]);
        }
        $mk([
            'name' => 'Jessie Wakefield', 'first_name' => 'Jessie', 'last_name' => 'Wakefield',
            'description' => "Jessie London Wakefield was an International Labor Defense field representative sent to Harlan County, Kentucky to defend the jailed coal miners. Her car was dynamited, and she was herself arrested and held in the Harlan jail under $10,000 bail on two charges of criminal syndicalism for representing the ILD. ".$harlanBase,
            'state' => 'Kentucky', 'gender' => 'Female',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with two counts of criminal syndicalism for representing the ILD in the Harlan County coal war.',
                'convicted' => 'Jailed under $10,000 bail, 1931',
                'sentence' => 'Held in the Harlan County jail.',
                'institution_name' => 'Harlan County Jail',
                'institution_city' => 'Harlan', 'institution_state' => 'Kentucky',
            ]],
        ], ['arrest_date' => [1931, null, null]]);

        // ── CAMP HILL / TALLAPOOSA COUNTY, AL SHARECROPPERS ───────────────
        $campHillBase = "In July 1931 a sheriff's posse attacked a meeting of the newly-formed, Communist-led Share Croppers' Union near Camp Hill in Tallapoosa County, Alabama, killing organizer Ralph Gray and touching off mass arrests of Black sharecroppers. The International Labor Defense won the release of most of the jailed men.";
        foreach ([
            ['Thomas Gray', 'Thomas', 'Gray'],
            ['James Gray', 'James', 'Gray'],
            ['Jasper Canada', 'Jasper', 'Canada'],
            ['Will Drake', 'Will', 'Drake'],
            ['Taft Holmes', 'Taft', 'Holmes'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a Black sharecropper jailed after the July 1931 attack on the Share Croppers' Union at Camp Hill, Alabama; the frame-up charges were fought by the International Labor Defense. ".$campHillBase,
                'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['Share Croppers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed after the Camp Hill attack on the Share Croppers\' Union.',
                    'convicted' => 'Held after the Camp Hill raids, 1931',
                    'sentence' => 'Jailed at Camp Hill; released on ILD defense.',
                    'institution_county' => 'Tallapoosa County', 'institution_state' => 'Alabama',
                ]],
            ], ['arrest_date' => [1931, 7, 16]]);
        }

        // ── PORTLAND, OREGON CRIMINAL SYNDICALISM ────────────────────────
        $oregonBase = "In the winter of 1930–31, Portland, Oregon revived its criminal-syndicalism law in a wave of raids on the Communist Party and Young Communist League, prosecuting members for their political affiliation. Ben Boloff was the first tried and drew a ten-year term; a mass defense campaign won acquittals for several of the others.";
        $oregon = [
            ['Ben Boloff', 'Ben', 'Boloff', "the first worker tried for criminal syndicalism in Oregon, sentenced to ten years in the penitentiary for membership in the Communist Party; he contracted tuberculosis in prison"],
            ['Dan Stoeff', 'Dan', 'Stoeff', "one of the Portland workers charged with criminal syndicalism"],
            ['Abe Ozeranski', 'Abe', 'Ozeranski', "one of the Portland workers charged with criminal syndicalism"],
            ['Ellis Bjorkman', 'Ellis', 'Bjorkman', "one of the Portland workers charged with criminal syndicalism"],
            ['Rubin Sandstrom', 'Rubin', 'Sandstrom', "one of the Portland workers charged with criminal syndicalism"],
            ['Bill Worral', 'Bill', 'Worral', "one of the Portland workers charged with criminal syndicalism"],
            ['Jim Howell', 'Jim', 'Howell', "one of the Portland workers charged with criminal syndicalism"],
            ['John Torrko', 'John', 'Torrko', "one of the Portland workers charged with criminal syndicalism"],
            ['Fred Walker', 'Fred', 'Walker', "a Young Communist League district organizer tried for criminal syndicalism and acquitted after a mass protest"],
            ['Paul Munter', 'Paul', 'Munter', "a defendant in the Oregon criminal-syndicalism cases"],
            ['John Moore', 'John', 'Moore', "a Portland criminal-syndicalism defendant, acquitted in April 1931"],
            ['Ed Levitt', 'Ed', 'Levitt', "one of the Portland workers charged with criminal syndicalism"],
        ];
        foreach ($oregon as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}. ".$oregonBase,
                'state' => 'Oregon', 'gender' => 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party USA'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with criminal syndicalism at Portland, Oregon for Communist Party / Young Communist League membership.',
                    'convicted' => 'Prosecuted for criminal syndicalism, 1930–31',
                    'sentence' => $first === 'Ben' ? 'Ten years in the Oregon penitentiary.' : 'Held for trial on the criminal-syndicalism charge.',
                    'institution_state' => 'Oregon',
                ]],
            ], ['arrest_date' => [1930, 9, 3]]);
        }
        $mk([
            'name' => 'Mike Kulikoff', 'first_name' => 'Mike', 'last_name' => 'Kulikoff',
            'description' => "Mike Kulikoff was an eighteen-year-old Portland high-school student and Young Communist League member who, after his deportation to the USSR was blocked, was committed to the Salem, Oregon insane asylum on the theory that his radical activity was \"a manifestation of an unsound mind.\" He was paroled after a protest campaign.",
            'state' => 'Oregon', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Young Communist League'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Committed to an insane asylum for radical political activity.',
                'convicted' => 'Committed to the Oregon State Hospital, 1930',
                'sentence' => 'Held in the Salem insane asylum; paroled after protest.',
                'institution_name' => 'Oregon State Hospital',
                'institution_city' => 'Salem', 'institution_state' => 'Oregon',
            ]],
        ], []);

        // ── PATERSON, N.J. SILK STRIKE MURDER FRAME-UP ────────────────────
        $patersonBase = "During the 1931 silk strike in Paterson, New Jersey, five members of the National Textile Workers' Union were charged with first-degree murder after the struck mill-owner Max Urban died following a February 1931 fight outside his shop. Held without bail and facing the electric chair, they were, the ILD argued, framed to break the strike — several had provable alibis.";
        $paterson = [
            ['Benjamin Lieb', 'Benjamin', 'Lieb', 'Male', "a forty-six-year-old veteran organizer and father of three"],
            ['Louis Harris', 'Louis', 'Harris', 'Male', "who was at work in another shop at the time of the fight"],
            ['Lewis Bart', 'Lewis', 'Bart', 'Male', "a father of three who was several blocks from the scene"],
            ['Albert Kalzenbuch', 'Albert', 'Kalzenbuch', 'Male', "one of the five silk workers charged"],
            ['Helen Gershonowitz', 'Helen', 'Gershonowitz', 'Female', "the sole support of three children and a husband disabled in the mills"],
        ];
        foreach ($paterson as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was one of five National Textile Workers' Union members charged with first-degree murder in the Paterson, New Jersey silk strike of 1931. ".$patersonBase,
                'state' => 'New Jersey', 'gender' => $gender,
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Textile Workers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with the first-degree murder of mill-owner Max Urban during the Paterson silk strike.',
                    'convicted' => 'Held without bail on the murder charge, 1931',
                    'sentence' => 'Held in the Passaic County jail facing the electric chair.',
                    'institution_name' => 'Passaic County Jail',
                    'institution_city' => 'Paterson', 'institution_state' => 'New Jersey',
                ]],
            ], ['arrest_date' => [1931, 3, 20]]);
        }

        // ── LAWRENCE, MASS. TEXTILE STRIKE & DEPORTATION HOLDS ────────────
        $lawrenceBase = "In the 1931 strike of 23,000 textile workers at Lawrence, Massachusetts, the National Textile Workers' Union leadership was mass-arrested and several foreign-born organizers were held for deportation to break the strike.";
        $lawrence = [
            ['Edith Berkman', 'Edith', 'Berkman', 'Female', "an NTWU organizer choked and beaten in a raid on strike headquarters, held without bail for deportation to fascist Poland"],
            ['Pat Devine', 'Pat', 'Devine', 'Male', "the acting secretary of the NTWU, jailed for a year and facing deportation to Ireland"],
            ['Bill Murdock', 'Bill', 'Murdock', 'Male', "an NTWU leader whose skull was fractured by police and who was held for deportation to Scotland"],
            ['Alex Danilevich', 'Alex', 'Danilevich', 'Male', "a strike-committee member held under $40,000 bail on four charges"],
            ['John Czarnecki', 'John', 'Czarnecki', 'Male', "a strike-committee member held under $40,000 bail on four charges"],
            ['Fred Biedenkapp', 'Fred', 'Biedenkapp', 'Male', "a strike leader held at the East Boston immigration detention station"],
            ['Krasevich', 'Krasevich', '', 'Male', "a Lawrence strike organizer held at the East Boston detention station in grave danger of deportation"],
            ['Donegian', 'Donegian', '', 'Male', "a citizen of Soviet Armenia slated for deportation to Turkey, committed for \"observation\" and subjected to spinal punctures and forced injections"],
        ];
        foreach ($lawrence as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last ?: $first,
                'description' => "{$name} was {$who} in the 1931 Lawrence, Massachusetts textile strike. ".$lawrenceBase,
                'state' => 'Massachusetts', 'gender' => $gender,
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Textile Workers Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in the Lawrence textile strike and held for deportation or on strike-related charges.',
                    'convicted' => 'Held / faced deportation, 1931',
                    'sentence' => 'Held during the Lawrence strike; defended by the ILD.',
                    'institution_state' => 'Massachusetts',
                ]],
            ], ['arrest_date' => [1931, null, null]]);
        }

        // ── 1931 DEPORTATION DRIVE ───────────────────────────────────────
        $deport = [
            ['August Yokinen', 'August', 'Yokinen', 'Finland', "a Finnish worker convicted at a Communist Party public trial in New York for \"white chauvinism\" — who then pledged to fight for Black–white equality — and railroaded toward deportation to Finland; his was a celebrated ILD case of 1931"],
            ['Tao Li', 'Tao', 'Li', 'China', "a Chinese worker in New York ordered deported to China, whose case the ILD fought to the Department of Labor; a stay was won when police clubbed an ILD demonstration at the Barge Office"],
            ['Eduardo Machado', 'Eduardo', 'Machado', 'Venezuela', "a Venezuelan arrested at the Trade Union Unity League office and held for deportation to the dictatorship in Venezuela"],
            ['Benjamin Saul', 'Benjamin', 'Saul', 'United States', "a Boston worker who led the 25 February 1931 unemployment demonstration and was held for deportation"],
            ['Goldie Waldman', 'Goldie', 'Waldman', 'United States', "a Boston worker who helped lead the 25 February 1931 unemployment demonstration and was held for deportation"],
            ['John Peltzer', 'John', 'Peltzer', 'Germany', "a German seaman taken off his ship at Galveston, Texas and held for deportation"],
            ['Ed Wing', 'Ed', 'Wing', 'China', "a Chinese worker held nine months in a Los Angeles jail and then denied a passport for the \"voluntary deportation\" he had accepted"],
            ['Tony Krizon', 'Tony', 'Krizon', 'United States', "a Butte, Montana coal miner held 158 days in the Miles City county jail on a deportation charge after riding the Northern Pacific railroad without paying"],
            ['Leon Glaser', 'Leon', 'Glaser', 'Russia', "a Russian-born militant held by the immigration department at Seattle for deportation"],
            ['Michael Sakasagsky', 'Michael', 'Sakasagsky', 'Russia', "a Russian-born militant held by the immigration department at Seattle for deportation"],
            ['Vladamir Wolck', 'Vladamir', 'Wolck', 'Russia', "a Russian-born militant held by the immigration department at Seattle for deportation"],
            ['Rocco D\'Alessandro', 'Rocco', 'D\'Alessandro', 'Italy', "an Italian anti-fascist facing deportation whom the ILD instead helped send to the USSR rather than to Mussolini's Italy"],
        ];
        foreach ($deport as [$name, $first, $last, $origin, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} during the intensified deportation drive of 1931 under Secretary of Labor William Doak.",
                'gender' => $first === 'Goldie' ? 'Female' : 'Male',
                'ideologies' => ['Communism'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Held for deportation for radical labor activity during the 1931 deportation drive.',
                    'convicted' => 'Held for deportation, 1931',
                    'sentence' => 'Held pending deportation; defended by the ILD.',
                ]],
            ], ['arrest_date' => [1931, null, null]]);
        }

        // ── OHIO CRIMINAL SYNDICALISM ────────────────────────────────────
        $mk([
            'name' => 'Paul Kassay', 'first_name' => 'Paul', 'last_name' => 'Kassay',
            'description' => "Paul Kassay was a mechanic at the Goodyear Zeppelin works in Akron, Ohio — then building the dirigible Akron — who was arrested in March 1931 on a Department of Justice agent's word and charged under the Ohio criminal-syndicalism law with sabotaging the airship by \"spitting on the rivets.\" Held on $40,000 bail, he was acquitted, and the court declared the Ohio criminal-syndicalism law unconstitutional.",
            'state' => 'Ohio', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged under the Ohio criminal-syndicalism law with sabotaging the dirigible Akron.',
                'convicted' => 'Acquitted; Ohio criminal-syndicalism law ruled unconstitutional',
                'sentence' => 'Held on $40,000 bail; acquitted.',
                'institution_city' => 'Akron', 'institution_state' => 'Ohio',
            ]],
        ], ['arrest_date' => [1931, 3, 19]]);
        $mk([
            'name' => 'Roy Mahoney', 'first_name' => 'Roy', 'last_name' => 'Mahoney',
            'description' => "Roy Mahoney was a Black worker of East Liverpool, Ohio, a leader of the February 1931 unemployed demonstrations, arrested and tried under the Ohio criminal-syndicalism law and acquitted after a mass protest.",
            'state' => 'Ohio', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with criminal syndicalism for leading unemployed demonstrations at East Liverpool, Ohio.',
                'convicted' => 'Acquitted after mass protest, 1931',
                'sentence' => 'Held on the criminal-syndicalism charge; acquitted.',
                'institution_city' => 'East Liverpool', 'institution_state' => 'Ohio',
            ]],
        ], ['arrest_date' => [1931, null, null]]);

        // ── NORFOLK, VIRGINIA ─────────────────────────────────────────────
        $mk([
            'name' => 'Archie Gibbs', 'first_name' => 'Archie', 'last_name' => 'Gibbs',
            'description' => "Archie Gibbs was a twenty-two-year-old seaman and member of the Marine Workers Industrial Union who served forty-two days in the Norfolk, Virginia jail in 1931 for distributing leaflets calling young workers to a Young Communist League meeting. Put in solitary and hauled before a prison \"kangaroo court,\" he was given twenty-five lashes.",
            'state' => 'Virginia', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['Marine Workers Industrial Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for distributing Young Communist League leaflets at Norfolk, Virginia.',
                'convicted' => 'Served 42 days, 1931',
                'sentence' => 'Forty-two days in the Norfolk jail, with twenty-five lashes.',
                'institution_city' => 'Norfolk', 'institution_state' => 'Virginia',
            ]],
        ], ['arrest_date' => [1931, null, null]]);
        $mk([
            'name' => 'Ollie Dawson', 'first_name' => 'Ollie', 'last_name' => 'Dawson',
            'description' => "Ollie Dawson was a Black worker held in the Norfolk, Virginia jail — in the cell next to Archie Gibbs — and, the Labor Defender reported, railroaded to the electric chair on a framed murder charge in a trial rushed through in a couple of days before a hostile jury; he was electrocuted a few days after Gibbs's release.",
            'state' => 'Virginia', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Convicted of murder in a rushed trial the ILD denounced as a frame-up.',
                'convicted' => 'Convicted and executed, 1931',
                'sentence' => 'Death; electrocuted.',
                'institution_city' => 'Norfolk', 'institution_state' => 'Virginia',
            ]],
        ], []);

        // ── DALLAS, TEXAS — HURST-CODER CASE ─────────────────────────────
        foreach ([
            ['Lewis Hurst', 'Lewis', 'Hurst', 'Male', 'White', "a white leader of the Dallas unemployment-insurance fight against peonage and segregation"],
            ['Charles Coder', 'Charles', 'Coder', 'Male', 'White', "a white co-leader of the Dallas unemployment movement"],
            ['William Grove', 'William', 'Grove', 'Male', 'Black', "a militant Black worker jailed with the movement's leaders"],
        ] as [$name, $first, $last, $gender, $race, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} in Dallas, Texas in 1931; after a demonstration of four thousand for unemployment insurance, he was jailed and then handed over to the Ku Klux Klan, flogged, and left for dead before being rescued by Black farmers.",
                'state' => 'Texas', 'gender' => $gender, 'race' => $race,
                'ideologies' => ['Communism', 'Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for leading an unemployment-insurance demonstration at Dallas, Texas.',
                    'convicted' => 'Jailed and delivered to a Klan flogging, 1931',
                    'sentence' => 'Jailed at Dallas, then kidnapped and flogged.',
                    'institution_city' => 'Dallas', 'institution_state' => 'Texas',
                ]],
            ], ['arrest_date' => [1931, null, null]]);
        }

        // ── NEW YORK — "NESSIN CASE" (Oct 16 1930 delegation) ────────────
        foreach ([
            ['Sam Nessin', 'Sam', 'Nessin', "whose jaw was broken by police"],
            ['Milton Stone', 'Milton', 'Stone', "who was beaten and permanently scarred"],
            ['Robert Lealess', 'Robert', 'Lealess', "who was beaten and permanently scarred"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was a member of the delegation of the unemployed that went to the New York City Board of Estimate on 16 October 1930; beaten on Mayor Walker's order and charged with unlawful assembly, {$who}. Tried without a jury, the delegates were acquitted.",
                'state' => 'New York', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged with unlawful assembly for the 16 October 1930 unemployed delegation to the New York Board of Estimate.',
                    'convicted' => 'Tried and acquitted',
                    'sentence' => 'Beaten and charged; acquitted at trial.',
                    'institution_city' => 'New York', 'institution_state' => 'New York',
                ]],
            ], ['arrest_date' => [1930, 10, 16]]);
        }

        // ── PENNSYLVANIA / OHIO / WV COAL STRIKE 1931 ────────────────────
        $triState = [
            ['Adam Getto', 'Adam', 'Getto', 'Male', "a National Miners' Union organizer clubbed and arrested at a march on the Ellsworth mine in Pennsylvania"],
            ['Leo Thompson', 'Leo', 'Thompson', 'Male', "a young strike leader arrested for addressing three thousand workers at the St. Clairsville, Ohio courthouse, charged with attempted murder and held on $50,000 bail"],
            ['Edward Sherwood', 'Edward', 'Sherwood', 'Male', "an eighteen-year-old Pittsburgh striker beaten by coal-company guards and arrested"],
            ['Mike Sklovski', 'Mike', 'Sklovski', 'Male', "a Gilmore striker beaten and arrested"],
            ['William Parson', 'William', 'Parson', 'Male', "a Slovan striker shot in the arm and arrested"],
            ['Anna Rasefsky', 'Anna', 'Rasefsky', 'Female', "a miner's wife jailed at Canonsburg, Pennsylvania in the strike"],
            ['Stella Rasefsky', 'Stella', 'Rasefsky', 'Female', "a miner's daughter jailed at Canonsburg, Pennsylvania in the strike"],
        ];
        foreach ($triState as [$name, $first, $last, $gender, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who} during the National Miners' Union coal strike that swept Pennsylvania, Ohio and West Virginia in the summer of 1931, when more than a thousand strikers, their wives and children were arrested.",
                'state' => 'Pennsylvania', 'gender' => $gender,
                'ideologies' => ['Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in the 1931 Pennsylvania–Ohio–West Virginia coal strike.',
                    'convicted' => 'Jailed in the tri-state coal strike, 1931',
                    'sentence' => 'Jailed during the strike; defended by the ILD.',
                    'institution_state' => 'Pennsylvania',
                ]],
            ], ['arrest_date' => [1931, 7, null]]);
        }

        // ── INDIVIDUAL FRAME-UPS & FREE-SPEECH CASES ─────────────────────
        $mk([
            'name' => 'Willie Peterson', 'first_name' => 'Willie', 'last_name' => 'Peterson',
            'description' => "Willie Peterson was a tubercular Black World War veteran of Birmingham, Alabama framed in 1931 on charges of murdering two white \"society girls.\" While in jail awaiting trial he was shot and wounded in his cell by Dent Williams, a wealthy white man ushered in by the authorities. His case became a major ILD Southern defense alongside Scottsboro.",
            'state' => 'Alabama', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Framed on charges of murdering two white women at Birmingham, Alabama.',
                'convicted' => 'Held on framed murder charges, 1931',
                'sentence' => 'Held facing death; shot in his cell by a vigilante.',
                'institution_city' => 'Birmingham', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1931, null, null]]);
        $mk([
            'name' => 'Ben Irby', 'first_name' => 'Ben', 'last_name' => 'Irby',
            'description' => "Ben Irby of Selma, Alabama was arrested in 1931 for possessing International Labor Defense literature and charged with criminal syndicalism.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with criminal syndicalism for possessing ILD literature at Selma, Alabama.',
                'convicted' => 'Charged with criminal syndicalism, 1931',
                'sentence' => 'Held on the criminal-syndicalism charge.',
                'institution_city' => 'Selma', 'institution_state' => 'Alabama',
            ]],
        ], ['arrest_date' => [1931, null, null]]);
        $mk([
            'name' => 'Orphan Jones', 'first_name' => 'Orphan', 'last_name' => 'Jones',
            'aka' => 'Euel Lee',
            'description' => "Orphan Jones — also known as Euel Lee — was an elderly Black farm hand jailed at Snow Hill, Maryland in 1931 and accused of murdering a white farm family. A mob broke into the jail to lynch him and Snow Hill police beat him to force a \"confession\"; the International Labor Defense removed him to Baltimore and took up his defense in a case that ran to the Maryland Court of Appeals.",
            'state' => 'Maryland', 'gender' => 'Male', 'race' => 'Black',
            'era' => '1930s', 'in_custody' => false, 'released' => false,
            'cases' => [[
                'charges' => 'Accused of murdering a white family at Snow Hill, Maryland; the ILD charged a coerced confession.',
                'convicted' => 'Held and beaten for a confession, 1931',
                'sentence' => 'Held for trial; defended by the ILD.',
                'institution_city' => 'Snow Hill', 'institution_state' => 'Maryland',
            ]],
        ], ['arrest_date' => [1931, null, null]]);
        $mk([
            'name' => 'Fred Firestone', 'first_name' => 'Fred', 'last_name' => 'Firestone',
            'description' => "Fred Firestone was serving a sentence in a Los Angeles jail in 1931 for speaking at a factory-gate meeting.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for speaking at a factory-gate meeting in Los Angeles.',
                'convicted' => 'Jailed, 1931',
                'sentence' => 'Serving a term in a Los Angeles jail.',
                'institution_city' => 'Los Angeles', 'institution_state' => 'California',
            ]],
        ], []);
        $mk([
            'name' => 'T. Luesse', 'first_name' => 'T.', 'last_name' => 'Luesse',
            'description' => "T. Luesse was a young worker of Indianapolis sentenced to a year in prison in 1931 for selling the Daily Worker.",
            'state' => 'Indiana', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced for selling the Daily Worker at Indianapolis.',
                'convicted' => 'Convicted, 1931',
                'sentence' => 'One year.',
                'institution_city' => 'Indianapolis', 'institution_state' => 'Indiana',
            ]],
        ], ['incarceration_date' => [1931, null, null]]);
        $mk([
            'name' => 'L. Stokes', 'first_name' => 'L.', 'last_name' => 'Stokes',
            'description' => "L. Stokes was sentenced to six months on Welfare Island in 1931 for selling the Daily Worker in New York City.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Sentenced for selling the Daily Worker in New York City.',
                'convicted' => 'Convicted, 1931',
                'sentence' => 'Six months on Welfare Island.',
                'institution_name' => 'Welfare Island',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['incarceration_date' => [1931, null, null]]);
        $mk([
            'name' => 'Irving Keith', 'first_name' => 'Irving', 'last_name' => 'Keith',
            'description' => "Irving Keith was a young Boston worker arrested in 1931 for defying Massachusetts authorities intent on halting workers' right of free speech.",
            'state' => 'Massachusetts', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in a Boston free-speech fight.',
                'convicted' => 'Arrested, 1931',
                'sentence' => 'Jailed in the free-speech case.',
                'institution_city' => 'Boston', 'institution_state' => 'Massachusetts',
            ]],
        ], ['arrest_date' => [1931, null, null]]);

        // ── FORT LOGAN / DENVER, CO — ANARCHY-SEDITION ───────────────────
        foreach ([['Shantzek', 'Shantzek'], ['Greenberg', 'Greenberg']] as [$name, $last]) {
            $mk([
                'name' => $name, 'first_name' => $name, 'last_name' => $last,
                'description' => "{$name} was one of two young workers arrested near the Fort Logan army camp in Colorado in 1931 for distributing anti-war leaflets to soldiers; held incommunicado nine days in the Denver county jail and charged under the state's anarchy-sedition act, which carried up to twenty years, he was released on $2,000 bond awaiting trial.",
                'state' => 'Colorado', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Anti-war'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Charged under Colorado\'s anarchy-sedition act for distributing anti-war leaflets to soldiers near Fort Logan.',
                    'convicted' => 'Held for trial, 1931',
                    'sentence' => 'Held nine days incommunicado; released on $2,000 bond.',
                    'institution_name' => 'Denver County Jail',
                    'institution_city' => 'Denver', 'institution_state' => 'Colorado',
                ]],
            ], ['arrest_date' => [1931, null, null]]);
        }

        // ── LOS ANGELES 1930 UNEMPLOYMENT-ARREST HONOR ROLL ──────────────
        $laRoll = [
            ['George Kiosz', 'George', 'Kiosz', '1 year'],
            ['Richard Drake', 'Richard', 'Drake', '6 months'],
            ['Harry Schneiderman', 'Harry', 'Schneiderman', '6 months'],
            ['Alfred Fugelvic', 'Alfred', 'Fugelvic', '6 months'],
            ['Lillian Silverman', 'Lillian', 'Silverman', '6 months'],
            ['Goldie Katz', 'Goldie', 'Katz', '6 months'],
            ['George Haka', 'George', 'Haka', '1 year'],
        ];
        foreach ($laRoll as [$name, $first, $last, $term]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was listed on the Labor Defender's class-war prisoner honor roll as serving {$term} in the Los Angeles jails after the unemployment demonstrations of 1930.",
                'state' => 'California', 'gender' => in_array($first, ['Lillian', 'Goldie']) ? 'Female' : 'Male',
                'ideologies' => ['Communism'],
                'affiliation' => ['Unemployed Councils'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed after the 1930 unemployment demonstrations in Los Angeles.',
                    'convicted' => 'Convicted, 1930',
                    'sentence' => ucfirst($term).' in the Los Angeles jails.',
                    'institution_city' => 'Los Angeles', 'institution_state' => 'California',
                ]],
            ], ['incarceration_date' => [1930, null, null]]);
        }

        // ── PHILIPPINES — Tayug / Communist Party (US colony, 1931) ──────
        $phBase = "In 1931 the American colonial administration of the Philippines outlawed the Communist Party and jailed its leaders after the Tayug peasant uprising in Pangasinan; the leaders were sentenced to prison and years of internal exile.";
        foreach ([
            ['Crisanto Evangelista', 'Crisanto', 'Evangelista', "a printer and the general secretary of the Communist Party of the Philippines and the Proletarian Labor Congress, arrested repeatedly in 1931 and sentenced to eighteen months' imprisonment and eight years' exile"],
            ['Jacinto Manahan', 'Jacinto', 'Manahan', "the president of the Philippine National Confederation of Peasants, sentenced alongside Evangelista"],
            ['Dominador Ambrosio', 'Dominador', 'Ambrosio', "the acting secretary of the Proletarian Labor Congress, sentenced alongside the other Philippine labor leaders"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} was {$who}. ".$phBase,
                'state' => 'Philippines', 'gender' => 'Male',
                'ideologies' => ['Communism', 'Labor organizing', 'Anti-imperialism'],
                'affiliation' => ['Communist Party of the Philippines'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Jailed for Communist and peasant-union organizing after the Tayug uprising in the American-ruled Philippines.',
                    'convicted' => 'Convicted, 1931',
                    'sentence' => 'Imprisonment and years of exile.',
                    'institution_state' => 'Philippines',
                ]],
            ], ['arrest_date' => [1931, null, null]]);
        }

        // ── DREISER COMMITTEE — indicted at Harlan ───────────────────────
        foreach ([
            ['Theodore Dreiser', 'Theodore', 'Dreiser', "the novelist and chairman of the National Committee for the Defense of Political Prisoners"],
        ] as [$name, $first, $last, $who]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name}, {$who}, was indicted for criminal syndicalism by a Harlan County, Kentucky grand jury after leading a delegation to hold free-speech test meetings at Straight Creek and Wallins Creek in support of the jailed coal miners in 1931.",
                'state' => 'Kentucky', 'gender' => 'Male',
                'ideologies' => ['Labor organizing'],
                'era' => '1930s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => 'Indicted for criminal syndicalism for holding free-speech meetings in support of the Harlan miners.',
                    'convicted' => 'Indicted, 1931',
                    'sentence' => 'Indicted; never brought to trial.',
                    'institution_state' => 'Kentucky',
                ]],
            ], ['arrest_date' => [1931, 11, null]]);
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

        $this->info("\nDone. Processed {$added} of ".count($people)." 1931 Labor Defender prisoner(s).");

        return self::SUCCESS;
    }

    /**
     * Split a printed name like "W. B. Jones" or "Chas. Shadrick" into a
     * first-name (all but the last token) and a last-name (final token).
     */
    private function splitName(string $full): array
    {
        $parts = preg_split('/\s+/', trim($full));
        $last = array_pop($parts);
        $first = $parts ? implode(' ', $parts) : $last;

        return [$first, $last];
    }
}
