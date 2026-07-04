<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 1 — the monthly years, 1926–1933.
 *
 * After exhausting the ILD Labor Defender run (1926–1937), the pipeline was
 * pointed at New Masses, the Communist-aligned literary/cultural monthly
 * (later weekly) published 1926–1948. New Masses is far lower in
 * prisoner-density than the Labor Defender — most of its class-war coverage is
 * of the marquee cases already in the database (Sacco & Vanzetti, Mooney &
 * Billings, the McNamara/Schmidt Times case, Gastonia, Scottsboro, the Atlanta
 * Six, Harlan/Kentucky, the Ford Hunger March, the WWI conscientious
 * objectors) — so this batch adds only the genuinely NEW, individually named
 * US class-war prisoners surfaced across the 1926–1933 volumes and confirmed
 * absent from the existing commands.
 *
 * Veins mined here:
 *  - the 1926 Passaic textile strike bomb frame-up (Wisnesski);
 *  - 1920s IWW / coalfield labor (Ashleigh, Howat, Svanum, Bingula, Sablich);
 *  - the 1925 Hawaii Communist-soldier affair co-defendants (Domagalsky, Ebert);
 *  - the March 6 1930 unemployment demonstrations and the ILD (Eiseman, Engdahl);
 *  - the Atlanta Six insurrection case (Joseph Carr);
 *  - a Southern criminal-syndicalism arrest (Stephen Graham);
 *  - the LA Times bombing union-secretary brother (John J. McNamara);
 *  - the 1932 Harlan/Bell coal war and Ford Hunger March dragnet (Arnold
 *    Johnson, Charles Ellis);
 *  - a WWI draft-resistance Leavenworth prisoner (Otto Wangerin);
 *  - a 1933 Wisconsin anti-eviction farm case (Max Cichon).
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded (so the many overlapping marquee cases
 * are harmless if re-encountered).
 */
final class AddNewMasses1926to1933Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1926-1933';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1926-1933 (Passaic, 1920s IWW/coalfields, the Hawaii soldiers, March 6 1930, the Atlanta Six, Harlan, the Ford Hunger March, and a Wisconsin farm case)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── 1926 PASSAIC TEXTILE STRIKE BOMB FRAME-UP ────────────────────
        $mk([
            'name' => 'Adolf Wisnesski', 'first_name' => 'Adolf', 'last_name' => 'Wisnesski',
            'description' => "Adolf Wisnesski was a striker in the 1926 Passaic, New Jersey textile strike who was accused in the strike's \"bomb frame-up\" and sentenced to not less than 5 nor more than 20 years in a New Jersey prison — one of some eight men imprisoned in the frame-up that followed the United Front Committee walkout led by Albert Weisbord.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Front Committee of Textile Workers'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Accused in the Passaic textile-strike bomb frame-up.',
                'convicted' => 'Convicted, 1927',
                'sentence' => 'Not less than 5 nor more than 20 years.',
                'institution_state' => 'New Jersey',
            ]],
        ], ['incarceration_date' => [1927, null, null]]);

        // ── 1920s IWW / WWI ESPIONAGE ACT ────────────────────────────────
        $mk([
            'name' => 'Charles Ashleigh', 'first_name' => 'Charles', 'last_name' => 'Ashleigh',
            'description' => "Charles Ashleigh (1888–1974) was an English-born poet and Industrial Workers of the World organizer who was convicted in the 1918 Chicago mass trial of the IWW under the wartime Espionage Act and sentenced to Leavenworth, where he served about five years. After his release he was deported to England, where he became news editor of the Sunday Worker in London.",
            'state' => 'Illinois', 'gender' => 'Male',
            'ideologies' => ['Labor organizing', 'Anti-war'],
            'affiliation' => ['Industrial Workers of the World'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted in the 1918 Chicago IWW mass trial under the Espionage Act for opposing the war.',
                'convicted' => 'Convicted, 1918',
                'sentence' => 'Served about five years at Leavenworth; then deported to England.',
                'institution_name' => 'United States Penitentiary, Leavenworth',
                'institution_city' => 'Leavenworth', 'institution_state' => 'Kansas',
            ]],
        ], ['incarceration_date' => [1918, null, null]]);

        $mk([
            'name' => 'Alexander Howat', 'first_name' => 'Alexander', 'last_name' => 'Howat',
            'description' => "Alexander Howat (1876–1945) was the long-time president of the Kansas district of the United Mine Workers who was jailed for leading outlaw strikes in defiance of the Kansas Industrial Court, the state's compulsory anti-strike arbitration law. While he was in jail John L. Lewis moved to depose and expel him from the union; he became a symbol of the fight against anti-strike legislation.",
            'state' => 'Kansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Mine Workers of America'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed for leading outlaw coal strikes in defiance of the Kansas Industrial Court anti-strike law.',
                'convicted' => 'Jailed, early 1920s',
                'sentence' => 'Served a jail sentence; expelled from the UMW while imprisoned.',
                'institution_state' => 'Kansas',
            ]],
        ], []);

        $mk([
            'name' => 'Kristen Svanum', 'first_name' => 'Kristen', 'last_name' => 'Svanum',
            'description' => "Kristen Svanum was a Danish-born Industrial Workers of the World organizer, secretary-treasurer of the IWW's Coal Miners' Industrial Union and a lead figure in the 1927 Colorado coal strike. He was arrested twice during the strike — held eight days in the Las Animas County jail before making bond, then re-arrested with a group of fellow organizers under Colorado's anti-picketing law.",
            'state' => 'Colorado', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Industrial Workers of the World'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested twice as an IWW organizer in the 1927 Colorado coal strike.',
                'convicted' => 'Held / arrested, 1927',
                'sentence' => 'Held eight days in the Las Animas County jail before bond; re-arrested.',
                'institution_name' => 'Las Animas County Jail',
                'institution_state' => 'Colorado',
            ]],
        ], ['arrest_date' => [1927, null, null]]);

        $mk([
            'name' => 'George Bingula', 'first_name' => 'George', 'last_name' => 'Bingula',
            'description' => "George Bingula was a union coal miner of Harmarville, Pennsylvania, arrested several times by the coal-and-iron police during a Pittsburgh-district coal strike. He was jailed after a picket-line clash with a deputy and, in a later incident, shot through the leg by a drunken deputy — one of the many rank-and-file miners caught up in the coal-field violence of the late 1920s.",
            'state' => 'Pennsylvania', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['National Miners Union'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested repeatedly by the coal-and-iron police during a coal strike; jailed after a picket-line clash.',
                'convicted' => 'Arrested / jailed, 1927',
                'sentence' => 'Jailed; bailed out by the mining community.',
                'institution_city' => 'Harmarville', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1927, null, null]]);

        $mk([
            'name' => 'Milka Sablich', 'first_name' => 'Milka', 'last_name' => 'Sablich',
            'description' => "Milka Sablich — known as \"Flaming Milka\" — was a young picket leader in the 1927–28 Colorado coal miners' strike in the northern Colorado coalfield. Arrested on the picket line, she was jailed for five weeks and refused to accept bail, becoming one of the strike's best-known figures.",
            'state' => 'Colorado', 'gender' => 'Female',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Industrial Workers of the World'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested as a picket leader in the 1927-28 Colorado coal strike.',
                'convicted' => 'Jailed, 1928',
                'sentence' => 'Jailed five weeks; refused bail.',
                'institution_state' => 'Colorado',
            ]],
        ], ['arrest_date' => [1928, null, null]]);

        // ── 1925 HAWAII COMMUNIST-SOLDIER AFFAIR (co-defendants) ─────────
        $mk([
            'name' => 'Steve Domagalsky', 'first_name' => 'Steve', 'last_name' => 'Domagalsky',
            'description' => "Steve Domagalsky was a soldier caught up in the 1925 Hawaii Communist-soldier affair at Schofield Barracks — the 21st Infantry court-martial that also jailed Paul Crouch and Walter Trumbull. A former Red Army soldier, he was arrested and held and, by the account carried in New Masses, threatened with twenty years for refusing to testify for the government.",
            'state' => 'Hawaii', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested and held in the 1925 Hawaii Communist-soldier affair, Schofield Barracks.',
                'convicted' => 'Held, 1925',
                'sentence' => 'Held; threatened with twenty years for refusing to testify.',
                'institution_name' => 'Schofield Barracks',
                'institution_state' => 'Hawaii',
            ]],
        ], ['arrest_date' => [1925, null, null]]);

        $mk([
            'name' => 'Roy F. Ebert', 'first_name' => 'Roy', 'last_name' => 'Ebert',
            'description' => "Roy F. Ebert was a US Army corporal arrested in the 1925 Hawaii Communist-soldier affair at Schofield Barracks — the same case (the Crouch/Trumbull court-martial) that the ILD publicized as an attack on radical soldiers. He was held in solitary confinement for two weeks.",
            'state' => 'Hawaii', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1920s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the 1925 Hawaii Communist-soldier affair, Schofield Barracks.',
                'convicted' => 'Held, 1925',
                'sentence' => 'Held two weeks in solitary confinement.',
                'institution_name' => 'Schofield Barracks',
                'institution_state' => 'Hawaii',
            ]],
        ], ['arrest_date' => [1925, null, null]]);

        // ── MARCH 6 1930 UNEMPLOYMENT DEMONSTRATIONS / ILD ───────────────
        $mk([
            'name' => 'Harry Eiseman', 'first_name' => 'Harry', 'last_name' => 'Eiseman',
            'description' => "Harry Eiseman was a teenage Young Pioneer leader sentenced to a reformatory for his part in the March 6, 1930 International Unemployment Day demonstration at Union Square, New York City — the youngest of the demonstrators seized when police attacked the crowd of some 35,000. His juvenile sentence became a cause of the International Labor Defense.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['Young Pioneers of America'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested at the March 6, 1930 Union Square unemployment demonstration.',
                'convicted' => 'Sentenced, 1930',
                'sentence' => 'Committed to a reformatory.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1930, 3, 6]]);

        $mk([
            'name' => 'J. Louis Engdahl', 'first_name' => 'Louis', 'last_name' => 'Engdahl',
            'description' => "J. Louis Engdahl (1884–1932) was a Socialist and later Communist editor who chaired the International Labor Defense and was arrested at a New York City Hall demonstration of the unemployed in 1930. Earlier convicted with other Socialist editors under the wartime Espionage Act, he led the ILD's Scottsboro campaign and died in Moscow in 1932 while on an international tour for the Scottsboro defense.",
            'state' => 'New York', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Socialism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested at a New York City Hall demonstration of the unemployed.',
                'convicted' => 'Arrested, 1930',
                'sentence' => 'Held; released.',
                'institution_city' => 'New York', 'institution_state' => 'New York',
            ]],
        ], ['arrest_date' => [1930, null, null]]);

        // ── ATLANTA SIX INSURRECTION CASE ───────────────────────────────
        $mk([
            'name' => 'Joseph Carr', 'first_name' => 'Joseph', 'last_name' => 'Carr',
            'description' => "Joseph Carr was one of the \"Atlanta Six\" — organizers arrested in Atlanta in 1930 and charged with \"inciting to insurrection\" under an old Georgia slave-insurrection statute that carried the death penalty. Held without bail, the six became an early ILD test case against the Georgia insurrection law later used against Angelo Herndon.",
            'state' => 'Georgia', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'affiliation' => ['International Labor Defense'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Charged with inciting to insurrection under the Georgia statute; held without bail.',
                'convicted' => 'Held / indicted, 1930',
                'sentence' => 'Held without bail; capital charge.',
                'institution_city' => 'Atlanta', 'institution_state' => 'Georgia',
            ]],
        ], ['arrest_date' => [1930, null, null]]);

        // ── SOUTHERN CRIMINAL SYNDICALISM ───────────────────────────────
        $mk([
            'name' => 'Stephen Graham', 'first_name' => 'Stephen', 'last_name' => 'Graham',
            'description' => "Stephen Graham was a Southern labor organizer who, after being acquitted twice, was re-arrested on a charge of advocating the violent overthrow of the government and faced deportation — one of the wave of criminal-syndicalism prosecutions the ILD fought across the South during the early Depression.",
            'state' => 'Georgia', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Re-arrested for advocating the violent overthrow of the government after two acquittals.',
                'convicted' => 'Arrested, 1930',
                'sentence' => 'Held; faced deportation.',
                'institution_state' => 'Georgia',
            ]],
        ], ['arrest_date' => [1930, null, null]]);

        // ── LOS ANGELES TIMES BOMBING (union-secretary brother) ─────────
        $mk([
            'name' => 'John J. McNamara', 'first_name' => 'John', 'last_name' => 'McNamara',
            'description' => "John J. McNamara (1876–1941) was the secretary-treasurer of the International Association of Bridge and Structural Iron Workers and the brother of James B. McNamara. In the 1910–11 Los Angeles Times bombing case the brothers pleaded guilty; John was sentenced for the related dynamiting of the Llewellyn Iron Works and served about a decade at San Quentin. His case, prosecuted by the state and defended by Clarence Darrow, was a landmark of the class-war labor conflicts of the era.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['International Association of Bridge and Structural Iron Workers'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Pleaded guilty in the Los Angeles Times bombing case (Llewellyn Iron Works dynamiting).',
                'convicted' => 'Convicted, 1911',
                'sentence' => 'Sentenced to fifteen years; served about a decade at San Quentin.',
                'institution_name' => 'San Quentin State Prison',
                'institution_city' => 'San Quentin', 'institution_state' => 'California',
            ]],
        ], ['incarceration_date' => [1911, null, null]]);

        // ── 1932 HARLAN/BELL COAL WAR + FORD HUNGER MARCH DRAGNET ────────
        $mk([
            'name' => 'Arnold Johnson', 'first_name' => 'Arnold', 'last_name' => 'Johnson',
            'description' => "Arnold Johnson was a young civil-liberties worker — a theological student investigating conditions in the Kentucky coalfields for the American Civil Liberties Union and the National Committee for the Defense of Political Prisoners — who was arrested in May 1932 during the Harlan–Bell County coal war and charged with criminal syndicalism. He was held for weeks in the Harlan County jail alongside ILD field representative Jessie Wakefield, and later had a long career as a civil-liberties and left-wing organizer.",
            'state' => 'Kentucky', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['American Civil Liberties Union'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested for criminal syndicalism during the Harlan-Bell County coal war.',
                'convicted' => 'Held, 1932',
                'sentence' => 'Held for weeks in the Harlan County jail; later freed.',
                'institution_name' => 'Harlan County Jail',
                'institution_state' => 'Kentucky',
            ]],
        ], ['arrest_date' => [1932, 5, null]]);

        $mk([
            'name' => 'Charles Ellis', 'first_name' => 'Charles', 'last_name' => 'Ellis',
            'description' => "Charles Ellis was a Detroit worker swept up in the police terror after the Ford Hunger March massacre of March 7, 1932 (\"Bloody Monday\"), when Dearborn police and Ford security killed four marchers. When Ellis went to Receiving Hospital to visit his wounded roommate, Detroit police arrested him, searched his room without a warrant, and seized \"Communist literature\" as a pretext for holding him — one of the many roundups the ILD publicized in the wake of the march.",
            'state' => 'Michigan', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Arrested in the Detroit dragnet following the Ford Hunger March massacre.',
                'convicted' => 'Held, 1932',
                'sentence' => 'Held on a "Communist literature" pretext.',
                'institution_city' => 'Detroit', 'institution_state' => 'Michigan',
            ]],
        ], ['arrest_date' => [1932, 3, null]]);

        // ── WWI DRAFT RESISTANCE (Leavenworth) ──────────────────────────
        $mk([
            'name' => 'Otto Wangerin', 'first_name' => 'Otto', 'last_name' => 'Wangerin',
            'description' => "Otto H. Wangerin (1881–1975) was a railroad machinist and left-wing Socialist — later a charter member of the Communist Party — who resisted the draft during World War I and was sentenced to fifteen years at the United States Penitentiary at Leavenworth, where he helped lead a struggle among the inmates to observe May Day. After his release he ran Chicago's Modern Bookstore for decades.",
            'state' => 'Kansas', 'gender' => 'Male',
            'ideologies' => ['Socialism', 'Anti-war'],
            'era' => '1910s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of resisting the draft during World War I.',
                'convicted' => 'Convicted, WWI era',
                'sentence' => 'Fifteen years at Leavenworth.',
                'institution_name' => 'United States Penitentiary, Leavenworth',
                'institution_city' => 'Leavenworth', 'institution_state' => 'Kansas',
            ]],
        ], []);

        // ── 1933 WISCONSIN ANTI-EVICTION FARM CASE ──────────────────────
        $mk([
            'name' => 'Max Cichon', 'first_name' => 'Max', 'last_name' => 'Cichon',
            'description' => "Max Cichon was a farmer near Elkhorn, Wisconsin whose home was besieged during a mid-winter 1933 farm eviction, when the sheriff and some twenty deputies tear-gassed and fired on the house; Cichon and his wife returned fire with shotguns before surrendering. He was jailed and charged with assault with intent to kill, and his wife was also jailed — a case from the farm-holiday anti-eviction resistance of the Depression.",
            'state' => 'Wisconsin', 'gender' => 'Male',
            'ideologies' => ['Farm organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Jailed and charged with assault with intent to kill for resisting a farm eviction.',
                'convicted' => 'Jailed, 1933',
                'sentence' => 'Jailed pending trial.',
                'institution_city' => 'Elkhorn', 'institution_state' => 'Wisconsin',
            ]],
        ], ['arrest_date' => [1933, null, null]]);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1926-1933 prisoner(s).");

        return self::SUCCESS;
    }
}
