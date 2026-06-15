<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Supplement to the 1948 National Guardian review, adding two cases requested
 * for inclusion:
 *   - The Rosa Lee Ingram family (Georgia, 1948) — the Civil Rights Congress's
 *     first major campaign for African Americans. (The case is not mentioned in
 *     the 1948 Guardian issues themselves — the paper took it up later — but it
 *     is a major 1948 case, included here at the editor's request.)
 *   - The Colorado Communist Party contempt jailings (Denver, 1948), reported
 *     in the Guardian's first issue (Oct 18, 1948): Arthur Bary and others
 *     jailed for refusing to name CP members before a federal grand jury.
 * Sourced to the New Georgia Encyclopedia / BlackPast (Ingram) and the National
 * Guardian, Oct 18, 1948, with Arthur Bary and Judge J. Foster Symes
 * independently corroborated (Bary v. United States). Idempotent.
 */
class AddNg1948Supplement extends Command {
    protected $signature = 'prisoners:add-ng-1948-supplement';
    protected $description = 'Add the Ingram family (1948 GA) and the Colorado CP contempt jailings (1948)';

    private const INGRAM = "%s In November 1947, near Ellaville, Georgia, a white neighbor named John Stratford confronted Rosa Lee Ingram — a Black widow and sharecropper — over stray livestock and, according to testimony, sexually threatened her; as she fought him off, her sons came to her defense with farm tools and Stratford was killed. In February 1948 an all-white jury convicted Rosa Lee Ingram and two of her sons of murder and sentenced all three to death. The case became the Civil Rights Congress's first major national defense campaign on behalf of African Americans, with a National Committee to Free the Ingram Family led by the veteran clubwoman Mary Church Terrell. The death sentences were reduced to life imprisonment on appeal in 1948, and after years of campaigning the Ingrams were paroled in 1959.";

    private const INGRAM_CHARGES = 'The November 1947 killing of John Stratford, a white neighbor, near Ellaville, Georgia — which the family said occurred as Rosa Lee Ingram\'s sons defended her from his assault; all three were convicted of murder by an all-white jury.';
    private const INGRAM_CONVICTED = 'Yes — convicted and sentenced to death by an all-white jury in February 1948; the death sentences were reduced to life imprisonment on appeal.';
    private const INGRAM_SENTENCE = 'Death, reduced to life imprisonment (1948); paroled in 1959 after a long Civil Rights Congress campaign.';

    private const COLORADO = "%s was among the Colorado Communists jailed for contempt in Denver in 1948 for refusing to cooperate with a federal grand jury investigating the Communist Party. As the National Guardian reported in its first issue, U.S. District Judge J. Foster Symes ordered Arthur Bary, the chairman of the Colorado Communist Party, to name every member of the party's clubs and cells in the state; when he refused, Bary was jailed to be held indefinitely until he complied, and others were imprisoned alongside him — some for refusing, on Fifth Amendment grounds, to answer self-incriminating questions — with trial by jury denied. The episode was part of the federal pursuit of the Colorado party that would later produce Smith Act prosecutions (Bary v. United States) in the 1950s.";

    public function handle(): int {
        $cases = [
            // --- Ingram family (Georgia, 1948) ---
            ['name' => 'Rosa Lee Ingram', 'first' => 'Rosa Lee', 'last' => 'Ingram', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Georgia',
             'bio' => sprintf(self::INGRAM, 'Rosa Lee Ingram was the Black Georgia sharecropper widow at the center of one of the defining civil-rights cases of 1948.'),
             'charges' => self::INGRAM_CHARGES, 'convicted' => self::INGRAM_CONVICTED, 'sentence' => self::INGRAM_SENTENCE,
             'ideologies' => [], 'affiliation' => []],
            ['name' => 'Wallace Ingram', 'first' => 'Wallace', 'last' => 'Ingram', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Georgia',
             'bio' => sprintf(self::INGRAM, 'Wallace Ingram was sixteen years old when he was sentenced to death alongside his mother and younger brother in the Ingram case.'),
             'charges' => self::INGRAM_CHARGES, 'convicted' => self::INGRAM_CONVICTED, 'sentence' => self::INGRAM_SENTENCE,
             'ideologies' => [], 'affiliation' => []],
            ['name' => 'Sammie Lee Ingram', 'first' => 'Sammie Lee', 'last' => 'Ingram', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Georgia',
             'bio' => sprintf(self::INGRAM, 'Sammie Lee Ingram was fourteen years old when he was sentenced to death alongside his mother and older brother in the Ingram case.'),
             'charges' => self::INGRAM_CHARGES, 'convicted' => self::INGRAM_CONVICTED, 'sentence' => self::INGRAM_SENTENCE,
             'ideologies' => [], 'affiliation' => []],

            // --- Colorado CP contempt jailings (Denver, 1948) ---
            ['name' => 'Arthur Bary', 'first' => 'Arthur', 'last' => 'Bary', 'gender' => 'Male', 'race' => null, 'state' => 'Colorado',
             'bio' => sprintf(self::COLORADO, 'Arthur Bary, the chairman of the Communist Party of Colorado,'),
             'charges' => 'Contempt — for refusing, before a federal grand jury in Denver in 1948, to name the members of the Communist Party of Colorado.',
             'convicted' => 'Jailed for contempt in 1948, to be held until he complied; trial by jury was denied.',
             'sentence' => 'Imprisoned for contempt in Denver, 1948 — held indefinitely pending compliance.',
             'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA']],
            ['name' => 'Paul Kleinbord', 'first' => 'Paul', 'last' => 'Kleinbord', 'gender' => 'Male', 'race' => null, 'state' => 'Colorado',
             'bio' => sprintf(self::COLORADO, 'Paul Kleinbord, a Communist Party leader in Colorado,'),
             'charges' => 'Contempt — for refusing, before a federal grand jury in Denver in 1948, to name members of the Communist Party of Colorado.',
             'convicted' => 'Jailed for contempt in 1948; trial by jury was denied.',
             'sentence' => 'Imprisoned for contempt in Denver, 1948.',
             'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA']],
            ['name' => 'Jane Rogers', 'first' => 'Jane', 'last' => 'Rogers', 'gender' => 'Female', 'race' => null, 'state' => 'Colorado',
             'bio' => sprintf(self::COLORADO, 'Jane Rogers'),
             'charges' => 'Contempt — for refusing, on Fifth Amendment grounds, to answer questions before a federal grand jury in Denver investigating the Communist Party of Colorado (1948).',
             'convicted' => 'Jailed for contempt in 1948 after declining to answer self-incriminating questions; appeal pending.',
             'sentence' => 'Imprisoned for contempt in Denver, 1948.',
             'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA']],
            ['name' => 'Nancy Wertheimer', 'first' => 'Nancy', 'last' => 'Wertheimer', 'gender' => 'Female', 'race' => null, 'state' => 'Colorado',
             'bio' => sprintf(self::COLORADO, 'Nancy Wertheimer'),
             'charges' => 'Contempt — for refusing, on Fifth Amendment grounds, to answer questions before a federal grand jury in Denver investigating the Communist Party of Colorado (1948).',
             'convicted' => 'Jailed for contempt in 1948 after declining to answer self-incriminating questions; appeal pending.',
             'sentence' => 'Imprisoned for contempt in Denver, 1948.',
             'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA']],
            ['name' => 'Irving Blau', 'first' => 'Irving', 'last' => 'Blau', 'gender' => 'Male', 'race' => null, 'state' => 'Colorado',
             'bio' => sprintf(self::COLORADO, 'Irving Blau'),
             'charges' => 'Contempt — for refusing, on Fifth Amendment grounds, to answer questions before a federal grand jury in Denver investigating the Communist Party of Colorado (1948).',
             'convicted' => 'Jailed for contempt in 1948 after declining to answer self-incriminating questions; appeal pending.',
             'sentence' => 'Imprisoned for contempt in Denver, 1948.',
             'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA']],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => $c['gender'],
                    'race'           => $c['race'],
                    'state'          => $c['state'],
                    'era'            => '1940s',
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => $c['charges'],
                    'convicted'   => $c['convicted'],
                    'sentence'    => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
