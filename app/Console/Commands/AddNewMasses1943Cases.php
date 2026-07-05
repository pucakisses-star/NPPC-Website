<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 11 — 1943.
 *
 * Deep in WWII, with the CPUSA all-in on the war effort, New Masses turns almost
 * entirely to war news and to demands that domestic fascists and Axis agents be
 * prosecuted. Domestic class-war prisoner density is very low, and the year's
 * marquee case — Morris Schappes, jailed for perjury out of the Rapp-Coudert
 * probe — is already in the database, as are the Oklahoma City criminal-
 * syndicalism defendants (Robert & Ina Wood, Eli Jaffe, Alan Shaw), William
 * Schneiderman's denaturalization case, Harry Bridges's deportation fight, and
 * Earl Browder (freed 1942). Juan Antonio Corretjer and William Wellman were
 * added in the 1942 batch. All of those are skipped.
 *
 * This adds the genuinely-new US class-war and racial-frame-up prisoners of
 * 1943: Michigan state senator Stanley Nowak, indicted in a political perjury/
 * denaturalization frame-up; two Black men caught in Southern "rape"-frame
 * extradition fights (George A. Burrows and Thomas Mattox); the two Black Army
 * privates given life by a New Caledonia court-martial (Frank Fisher Jr. and
 * Edward R. Lowry); and Henry Leyvas, the lead defendant in the Sleepy Lagoon
 * mass frame-up, sentenced to life at San Quentin.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1943Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1943';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1943 (Stanley Nowak, George A. Burrows, Thomas Mattox, the New Caledonia court-martial pair, and Sleepy Lagoon\'s Henry Leyvas)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── MICHIGAN — STANLEY NOWAK PERJURY FRAME-UP ───────────────────
        $mk([
            'name' => 'Stanley Nowak', 'first_name' => 'Stanley', 'last_name' => 'Nowak',
            'description' => "Stanley Nowak was a Polish-born Michigan state senator and a leading UAW-CIO organizer of Detroit's foreign-born workers. In 1942 he was arrested on Attorney General Francis Biddle's orders and indicted for perjury — the government's theory being that he had sworn falsely at his 1937 naturalization that he was 'not a disbeliever in organized government,' since as an alleged Communist he supposedly disbelieved in it. New Masses treated the denaturalization frame-up as a political attack on a labor leader; Biddle himself later conceded the prosecution was an 'error in judgment.'",
            'state' => 'Michigan', 'gender' => 'Male',
            'ideologies' => ['Communism', 'Labor organizing'],
            'affiliation' => ['United Automobile Workers', 'American Slav Congress'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Indicted for perjury in a denaturalization frame-up tied to alleged Communist Party membership.',
                'convicted' => 'Indicted, 1942',
                'sentence' => 'Prosecuted; the government later conceded it was a mistake.',
                'institution_city' => 'Detroit', 'institution_state' => 'Michigan',
            ]],
        ], ['arrest_date' => [1942, null, null]]);

        // ── MISSISSIPPI — GEORGE A. BURROWS EXTRADITION FRAME-UP ────────
        $mk([
            'name' => 'George A. Burrows', 'first_name' => 'George', 'last_name' => 'Burrows',
            'description' => "George A. Burrows was a Black cook in Harrison County, Mississippi who, after a dispute over wages, was framed on a charge of 'attempted rape' of a white woman and of shooting two white men — though the sheriff admitted he had not even touched the woman. Burrows fled north to New York, but in March 1943 Governor Thomas E. Dewey signed his extradition back to Mississippi over the protests of many organizations who feared he would be lynched.",
            'state' => 'Mississippi', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Civil rights'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Framed on an 'attempted rape' charge after a wage dispute; extradited from New York.",
                'convicted' => 'Extradited, 1943',
                'sentence' => 'Returned to Mississippi custody over lynching fears.',
                'institution_city' => 'Gulfport', 'institution_state' => 'Mississippi',
            ]],
        ], ['incarceration_date' => [1943, 3, null]]);

        // ── PENNSYLVANIA / GEORGIA — THOMAS MATTOX EXTRADITION FIGHT ────
        $mk([
            'name' => 'Thomas Mattox', 'first_name' => 'Thomas', 'last_name' => 'Mattox',
            'description' => "Thomas Mattox was a seventeen-year-old Black youth held in Philadelphia in 1943 as a fugitive wanted in Georgia. Governor James of Pennsylvania had ordered his extradition, but Common Pleas Judge Clare P. Fenerty overrode the order, declaring he would not send a man back to be lynched. New Masses cited the case as a rare instance of a Northern court blocking a racial-frame-up extradition to the South.",
            'state' => 'Pennsylvania', 'gender' => 'Male', 'race' => 'Black',
            'ideologies' => ['Civil rights'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Held as a fugitive wanted in Georgia; a racial-frame-up extradition case.',
                'convicted' => 'Held, 1943',
                'sentence' => 'Extradition to Georgia blocked by the court.',
                'institution_city' => 'Philadelphia', 'institution_state' => 'Pennsylvania',
            ]],
        ], ['arrest_date' => [1943, null, null]]);

        // ── U.S. ARMY — NEW CALEDONIA COURT-MARTIAL PAIR ────────────────
        $ncBase = "was a Black US Army private stationed in New Caledonia who in 1943 was convicted of 'rape' by a general court-martial and sentenced to life imprisonment. The International Labor Defense and Congressman Vito Marcantonio denounced the case as a racial frame-up — the encounter was in fact a paid transaction, and the original investigating officer had recommended dropping the charges before prejudiced white officers pressed a court-martial. New Masses headlined it 'Another Scottsboro?'";
        foreach ([
            ['Frank Fisher Jr.', 'Frank', 'Fisher'],
            ['Edward R. Lowry', 'Edward', 'Lowry'],
        ] as [$name, $first, $last]) {
            $mk([
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => "{$name} {$ncBase}",
                'gender' => 'Male', 'race' => 'Black',
                'ideologies' => ['Civil rights'],
                'affiliation' => ['United States Army'],
                'era' => '1940s', 'in_custody' => false, 'released' => true,
                'cases' => [[
                    'charges' => "Convicted of 'rape' by a US Army general court-martial in a racial frame-up.",
                    'convicted' => 'Convicted, 1943',
                    'sentence' => 'Life imprisonment.',
                    'institution_name' => 'U.S. Army general court-martial, New Caledonia',
                ]],
            ], ['incarceration_date' => [1943, null, null]]);
        }

        // ── CALIFORNIA — SLEEPY LAGOON CASE ─────────────────────────────
        $mk([
            'name' => 'Henry Leyvas', 'first_name' => 'Henry', 'last_name' => 'Leyvas',
            'description' => "Henry Leyvas was the twenty-year-old lead defendant in the Sleepy Lagoon case, the Los Angeles mass frame-up in which seventeen young Mexican-American men were convicted of conspiracy in the August 1942 death of José Díaz after a Hearst-press-driven, racially charged trial. Cast as the ringleader, Leyvas was convicted of murder in January 1943 and sentenced to life at San Quentin. The Citizens' Committee for the Defense of Mexican-American Youth won reversal of the convictions on appeal in 1944, and New Masses tied the case to the zoot-suit riots.",
            'state' => 'California', 'gender' => 'Male',
            'ideologies' => ['Civil rights'],
            'era' => '1940s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of murder in the Sleepy Lagoon mass frame-up of Mexican-American youths.',
                'convicted' => 'Convicted, 1943; reversed on appeal, 1944',
                'sentence' => 'Life at San Quentin.',
                'institution_name' => 'San Quentin State Prison',
                'institution_city' => 'San Quentin', 'institution_state' => 'California',
            ]],
        ], ['incarceration_date' => [1943, 1, null]]);

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

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1943 prisoner(s).");

        return self::SUCCESS;
    }
}
