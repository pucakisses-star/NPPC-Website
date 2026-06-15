<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political-prisoner cases surfaced from a review of the National Guardian
 * archive (the US radical weekly, 1948–61) — the Cold War-era frame-up,
 * death-penalty, and Smith Act cases the paper and the Civil Rights Congress
 * built their name on. Cross-checked as NOT already in the database (the
 * Rosenbergs, Sobell, and the Bradens are already present):
 *   - Trenton Six (1948 NJ frame-up; 4 acquitted, 2 convicted)
 *   - Martinsville Seven (executed VA 1951; pardoned 2021)
 *   - Willie McGee (executed MS 1951)
 *   - Wesley Robert Wells (CA; death for a thrown cuspidor; commuted)
 *   - Junius Scales (only person jailed under the Smith Act membership clause)
 * Sourced to Wikipedia, the Zinn Education Project, the Library of Virginia,
 * NCpedia, Scales v. United States (1961), and the CRC record. Idempotent.
 */
class AddNationalGuardianCases extends Command {
    protected $signature = 'prisoners:add-national-guardian';
    protected $description = 'Add National Guardian / Civil Rights Congress-era cases (Trenton Six, Martinsville Seven, McGee, Wells, Scales)';

    private const TRENTON = "%s was one of the \"Trenton Six,\" six Black men arrested in Trenton, New Jersey in 1948 and charged with the robbery and murder of William Horner, a white furniture-store owner, in a case civil-rights advocates called a \"Northern lynching.\" Convicted by an all-white jury in August 1948 on confessions the police had coerced, all six were sentenced to death. The Civil Rights Congress and the NAACP took up their appeals; the New Jersey Supreme Court overturned the convictions in 1949, and at the 1951 retrial four of the men were acquitted while two were convicted and sentenced to life. %s";

    private const TRENTON_CHARGES = 'The 1948 robbery and murder of William Horner, a white Trenton furniture dealer — a charge built on police-coerced confessions, in a frame-up of six Black men that the Civil Rights Congress publicized as a Northern "legal lynching."';

    private const MARTINSVILLE = "%s was one of the \"Martinsville Seven,\" seven Black men convicted by all-white juries of the rape of Ruby Stroud Floyd, a white woman, in Martinsville, Virginia in 1949 and executed in Virginia's electric chair in 1951. The convictions rested on confessions the defendants said were coerced, and the case became a landmark of racial injustice — every person executed for rape in Virginia's history was Black. The Civil Rights Congress mounted a national and international campaign to stop the executions, even picketing the White House, but four of the men were electrocuted on February 2, 1951 and the remaining three on February 5. In 2021 the Governor of Virginia granted all seven posthumous pardons, citing the racial bias and denial of due process in their trials.";

    private const MARTINSVILLE_CHARGES = 'The 1949 rape of Ruby Stroud Floyd in Martinsville, Virginia — a conviction by all-white juries, on confessions the men said were coerced, that became a symbol of the racially discriminatory use of the death penalty for rape in the Jim Crow South.';
    private const MARTINSVILLE_CONVICTED = 'Yes — convicted of rape and sentenced to death; executed in 1951. Posthumously pardoned by Virginia in 2021 on grounds of racial bias and denial of due process.';
    private const MARTINSVILLE_SENTENCE = 'Death; executed in Virginia\'s electric chair in February 1951.';

    public function handle(): int {
        $trentonAcquitted = 'No — convicted and sentenced to death in 1948, but acquitted at the 1951 retrial after the original convictions were overturned.';
        $trentonAcquittedSentence = 'Death (1948), overturned on appeal; acquitted at the 1951 retrial.';

        $vaPen = ['name' => 'Virginia State Penitentiary', 'city' => 'Richmond', 'state' => 'Virginia'];

        $cases = [
            // --- Trenton Six (1948 New Jersey) ---
            ['name' => 'McKinley Forrest', 'first' => 'McKinley', 'last' => 'Forrest', 'race' => 'Black', 'state' => 'New Jersey', 'era' => '1940s',
             'bio' => sprintf(self::TRENTON, 'McKinley Forrest', 'Forrest was among the four men acquitted at the 1951 retrial.'),
             'charges' => self::TRENTON_CHARGES, 'convicted' => $trentonAcquitted, 'sentence' => $trentonAcquittedSentence, 'released' => true],
            ['name' => 'John McKenzie', 'first' => 'John', 'last' => 'McKenzie', 'race' => 'Black', 'state' => 'New Jersey', 'era' => '1940s',
             'bio' => sprintf(self::TRENTON, 'John McKenzie', 'McKenzie was among the four men acquitted at the 1951 retrial.'),
             'charges' => self::TRENTON_CHARGES, 'convicted' => $trentonAcquitted, 'sentence' => $trentonAcquittedSentence, 'released' => true],
            ['name' => 'James Henry Thorpe Jr.', 'first' => 'James', 'last' => 'Thorpe', 'race' => 'Black', 'state' => 'New Jersey', 'era' => '1940s',
             'bio' => sprintf(self::TRENTON, 'James Henry Thorpe Jr.', 'Thorpe was among the four men acquitted at the 1951 retrial.'),
             'charges' => self::TRENTON_CHARGES, 'convicted' => $trentonAcquitted, 'sentence' => $trentonAcquittedSentence, 'released' => true],
            ['name' => 'Horace Wilson', 'first' => 'Horace', 'last' => 'Wilson', 'race' => 'Black', 'state' => 'New Jersey', 'era' => '1940s',
             'bio' => sprintf(self::TRENTON, 'Horace Wilson — the one defendant from whom the police obtained no confession', 'Wilson was among the four men acquitted at the 1951 retrial.'),
             'charges' => self::TRENTON_CHARGES, 'convicted' => $trentonAcquitted, 'sentence' => $trentonAcquittedSentence, 'released' => true],
            ['name' => 'Ralph Cooper', 'first' => 'Ralph', 'last' => 'Cooper', 'race' => 'Black', 'state' => 'New Jersey', 'era' => '1940s',
             'bio' => sprintf(self::TRENTON, 'Ralph Cooper', 'Cooper was one of the two men reconvicted at the 1951 retrial and sentenced to life imprisonment; he was later freed.'),
             'charges' => self::TRENTON_CHARGES,
             'convicted' => 'Convicted and sentenced to death in 1948; reconvicted at the 1951 retrial and sentenced to life imprisonment; later freed.',
             'sentence' => 'Death (1948); life imprisonment at the retrial; later released.', 'released' => true],
            ['name' => 'Collis English', 'first' => 'Collis', 'last' => 'English', 'race' => 'Black', 'state' => 'New Jersey', 'era' => '1940s',
             'bio' => sprintf(self::TRENTON, 'Collis English', 'English was one of the two men reconvicted at the 1951 retrial and sentenced to life imprisonment; he died in prison in 1952.'),
             'charges' => self::TRENTON_CHARGES,
             'convicted' => 'Convicted and sentenced to death in 1948; reconvicted at the 1951 retrial and sentenced to life imprisonment.',
             'sentence' => 'Death (1948); life imprisonment at the retrial; died in prison in 1952.', 'released' => false],

            // --- Martinsville Seven (1949 crime; executed February 1951) ---
            ['name' => 'Francis DeSales Grayson', 'first' => 'Francis', 'last' => 'Grayson', 'race' => 'Black', 'state' => 'Virginia', 'era' => '1950s', 'institution' => $vaPen,
             'bio' => sprintf(self::MARTINSVILLE, 'Francis DeSales Grayson'), 'charges' => self::MARTINSVILLE_CHARGES, 'convicted' => self::MARTINSVILLE_CONVICTED, 'sentence' => self::MARTINSVILLE_SENTENCE, 'released' => false],
            ['name' => 'Frank Hairston Jr.', 'first' => 'Frank', 'last' => 'Hairston', 'race' => 'Black', 'state' => 'Virginia', 'era' => '1950s', 'institution' => $vaPen,
             'bio' => sprintf(self::MARTINSVILLE, 'Frank Hairston Jr.'), 'charges' => self::MARTINSVILLE_CHARGES, 'convicted' => self::MARTINSVILLE_CONVICTED, 'sentence' => self::MARTINSVILLE_SENTENCE, 'released' => false],
            ['name' => 'Howard Hairston', 'first' => 'Howard', 'last' => 'Hairston', 'race' => 'Black', 'state' => 'Virginia', 'era' => '1950s', 'institution' => $vaPen,
             'bio' => sprintf(self::MARTINSVILLE, 'Howard Hairston'), 'charges' => self::MARTINSVILLE_CHARGES, 'convicted' => self::MARTINSVILLE_CONVICTED, 'sentence' => self::MARTINSVILLE_SENTENCE, 'released' => false],
            ['name' => 'James Luther Hairston', 'first' => 'James', 'last' => 'Hairston', 'race' => 'Black', 'state' => 'Virginia', 'era' => '1950s', 'institution' => $vaPen,
             'bio' => sprintf(self::MARTINSVILLE, 'James Luther Hairston'), 'charges' => self::MARTINSVILLE_CHARGES, 'convicted' => self::MARTINSVILLE_CONVICTED, 'sentence' => self::MARTINSVILLE_SENTENCE, 'released' => false],
            ['name' => 'Joe Henry Hampton', 'first' => 'Joe', 'last' => 'Hampton', 'race' => 'Black', 'state' => 'Virginia', 'era' => '1950s', 'institution' => $vaPen,
             'bio' => sprintf(self::MARTINSVILLE, 'Joe Henry Hampton'), 'charges' => self::MARTINSVILLE_CHARGES, 'convicted' => self::MARTINSVILLE_CONVICTED, 'sentence' => self::MARTINSVILLE_SENTENCE, 'released' => false],
            ['name' => 'Booker T. Millner', 'first' => 'Booker', 'last' => 'Millner', 'race' => 'Black', 'state' => 'Virginia', 'era' => '1950s', 'institution' => $vaPen,
             'bio' => sprintf(self::MARTINSVILLE, 'Booker T. Millner'), 'charges' => self::MARTINSVILLE_CHARGES, 'convicted' => self::MARTINSVILLE_CONVICTED, 'sentence' => self::MARTINSVILLE_SENTENCE, 'released' => false],
            ['name' => 'John Clabon Taylor', 'first' => 'John', 'last' => 'Taylor', 'race' => 'Black', 'state' => 'Virginia', 'era' => '1950s', 'institution' => $vaPen,
             'bio' => sprintf(self::MARTINSVILLE, 'John Clabon Taylor'), 'charges' => self::MARTINSVILLE_CHARGES, 'convicted' => self::MARTINSVILLE_CONVICTED, 'sentence' => self::MARTINSVILLE_SENTENCE, 'released' => false],

            // --- Individual cases ---
            ['name' => 'Willie McGee', 'first' => 'Willie', 'last' => 'McGee', 'race' => 'Black', 'state' => 'Mississippi', 'era' => '1950s', 'death' => '1951-05-08', 'released' => false,
             'bio' => 'Willie McGee was an African American man from Laurel, Mississippi, sentenced to death in 1945 for the alleged rape of a white housewife, Willette Hawkins — an encounter McGee said was a years-long consensual affair, an explosive claim in the Jim Crow South. His all-white-jury trial lasted a day and the jury deliberated only minutes. The Civil Rights Congress, with the young attorney Bella Abzug arguing his appeals, won two retrials and repeated stays of execution over six years, making McGee one of the great causes of the postwar civil-rights left. He was convicted each time, and on May 8, 1951 he was executed in a portable electric chair on the courthouse lawn in Laurel before a crowd of roughly a thousand people.',
             'charges' => 'The alleged 1945 rape of a white woman in Laurel, Mississippi — which McGee maintained was a consensual relationship; a capital conviction widely condemned as a racially driven "legal lynching."',
             'convicted' => 'Yes — convicted and sentenced to death in 1945, and reconvicted at two retrials won by his defense.',
             'sentence' => 'Death; executed on May 8, 1951 in Laurel, Mississippi.'],
            ['name' => 'Wesley Robert Wells', 'first' => 'Wesley', 'last' => 'Wells', 'race' => 'Black', 'state' => 'California', 'era' => '1950s', 'released' => true,
             'institution' => ['name' => 'San Quentin State Prison', 'city' => 'San Quentin', 'state' => 'California'],
             'bio' => 'Wesley Robert Wells was a Black California prisoner who became a national symbol of racist injustice after he was sentenced to death in 1947 for throwing a cuspidor — a metal spittoon — at a prison guard who had taunted him. No one was injured, but because Wells was already serving a life term as a "habitual criminal," assaulting a guard carried a mandatory death sentence. The Civil Rights Congress took up his case in 1948, with the radical attorney Charles Garry (later counsel to the Black Panther Party), winning a string of stays. In March 1954, two weeks before his scheduled execution, Governor Goodwin Knight commuted the death sentence to life. Wells spent almost his entire life behind bars before his release in 1974, and died about a year and a half later.',
             'charges' => 'Assault on a prison guard — throwing a cuspidor at a San Quentin officer in 1947 which, because Wells was already serving a life term, was treated as a capital offense under California\'s habitual-criminal law.',
             'convicted' => 'Yes — sentenced to death in 1947; the sentence was commuted to life by Governor Goodwin Knight in 1954.',
             'sentence' => 'Death, commuted to life imprisonment (1954); released in 1974 after a lifetime in prison.'],
            ['name' => 'Junius Scales', 'first' => 'Junius', 'last' => 'Scales', 'race' => null, 'state' => 'North Carolina', 'era' => '1950s', 'released' => true,
             'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA'],
             'institution' => ['name' => 'United States Penitentiary, Lewisburg', 'city' => 'Lewisburg', 'state' => 'Pennsylvania'],
             'bio' => 'Junius Irving Scales was the chairman of the North Carolina Communist Party and the only American ever imprisoned under the "membership clause" of the Smith Act, which made mere membership in the Communist Party a felony. Arrested in 1954, he fought his case for seven years and reached the U.S. Supreme Court twice; in Scales v. United States (1961) the Court upheld his conviction, and he entered the Lewisburg federal penitentiary in October 1961 under a six-year sentence, insisting he had never advocated the violent overthrow of the government — and having by then publicly broken with the Communist Party. President John F. Kennedy commuted his sentence on Christmas Eve 1962, after he had served about fifteen months, making Scales the last Smith Act prisoner.',
             'charges' => 'Membership in the Communist Party, under the membership clause of the Smith Act — Scales was the only person ever imprisoned under that provision (Scales v. United States, 1961).',
             'convicted' => 'Yes — convicted under the Smith Act membership clause and sentenced to six years; the conviction was upheld by the Supreme Court in 1961.',
             'sentence' => 'Six years; served about fifteen months at Lewisburg before President Kennedy commuted his sentence on Christmas Eve 1962.'],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $instId = null;
                if (! empty($c['institution'])) {
                    $inst = Institution::firstOrCreate(
                        ['name' => $c['institution']['name']],
                        ['city' => $c['institution']['city'], 'state' => $c['institution']['state']]
                    );
                    $instId = $inst->id;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => 'Male',
                    'race'           => $c['race'] ?? null,
                    'death_date'     => $c['death'] ?? null,
                    'state'          => $c['state'],
                    'era'            => $c['era'],
                    'ideologies'     => $c['ideologies'] ?? [],
                    'affiliation'    => $c['affiliation'] ?? [],
                    'in_custody'     => false,
                    'released'       => $c['released'],
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $instId,
                    'charges'        => $c['charges'],
                    'convicted'      => $c['convicted'],
                    'sentence'       => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
