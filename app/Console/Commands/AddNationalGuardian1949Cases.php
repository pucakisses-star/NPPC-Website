<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political-prisoner cases found by reading the National Guardian's 1949 issues
 * (52 issues, Jan–Dec 1949; OCR text extracted from the marxists.org scans).
 * The year's major cases the paper covered — the Foley Square Smith Act 12,
 * Harry Bridges, Judith Coplon, William Remington, the Hollywood Ten, the
 * Trenton Six, Willie McGee, and Rosa Lee Ingram — are all already in the
 * database. The genuine gaps surfaced by reading the issues:
 *   - Lester Tate    (Civil Rights Congress chain-gang / anti-extradition case)
 *   - Bennie Daniels and Lloyd Ray Daniels (NC cousins, coerced-confession
 *     capital frame-up; executed 1953)
 * Verified against Umbra/African American History, the NC Dept. of Adult
 * Correction execution records, ECU Joyner Library, and State v. Daniels.
 * Idempotent (skips by name).
 */
class AddNationalGuardian1949Cases extends Command {
    protected $signature = 'prisoners:add-ng-1949';
    protected $description = 'Add 1949 National Guardian cases (Lester Tate; the Daniels cousins)';

    private const DANIELS = "%s was one of two teenaged Black cousins — Bennie Daniels and Lloyd Ray Daniels — condemned to death in Pitt County, North Carolina for the February 1949 murder of William Benjamin O'Neal, a white cab driver, in a case the Civil Rights Congress and the National Guardian denounced as a frame-up. The only evidence against them was written confessions the youths said had been beaten and terrorized out of them; Lloyd Daniels described being driven handcuffed into the woods, threatened with death, and given three minutes to \"own\" the crime. Convicted at the May 1949 term of Pitt County Superior Court \"without recommendation of mercy,\" the cousins lost their appeals before the North Carolina Supreme Court and the U.S. Supreme Court and were executed on November 6, 1953.";

    private const DANIELS_CHARGES = 'The February 1949 murder of William Benjamin O\'Neal, a white cab driver, in Pitt County, North Carolina — a conviction resting solely on confessions the cousins said were beaten out of them; the Civil Rights Congress and the National Guardian called it a frame-up.';
    private const DANIELS_CONVICTED = 'Yes — convicted of first-degree murder at the May 1949 term of Pitt County Superior Court, "without recommendation of mercy," on coerced confessions; appeals to the North Carolina and U.S. Supreme Courts failed.';
    private const DANIELS_SENTENCE = 'Death; executed in North Carolina on November 6, 1953.';

    public function handle(): int {
        $centralPrison = Institution::firstOrCreate(
            ['name' => 'Central Prison'],
            ['city' => 'Raleigh', 'state' => 'North Carolina']
        );

        $cases = [
            [
                'name' => 'Lester Tate', 'first' => 'Lester', 'last' => 'Tate',
                'race' => 'Black', 'state' => 'California', 'era' => '1940s', 'released' => true, 'death' => null, 'institution_id' => null,
                'ideologies' => ['Labor', 'Civil rights'], 'affiliation' => ['Mine, Mill and Smelter Workers Union', 'Civil Rights Congress'],
                'bio' => 'Lester Tate — born Albert Lindsay Gee — was a Black Virginian who became a Civil Rights Congress cause célèbre after escaping a chain gang and rebuilding his life as a union man in California. In 1941 he and four others were arrested for the robbery of a grocery store near Norfolk, Virginia; tried without benefit of counsel, all five were sentenced to ten years on a Virginia chain gang. Tate escaped in 1943 — after enduring torture and seeing fellow prisoners shot by guards for complaining about the food — made his way to Los Angeles, took a new name, married, and became an active member of Local 700 of the Mine, Mill and Smelter Workers Union. When authorities discovered he was a Virginia fugitive and moved to extradite him back to the chain gang, the Civil Rights Congress waged a national defense campaign; the National Guardian reported in December 1949 that the fight against his extradition had been won.',
                'charges' => 'Robbery of a grocery store near Norfolk, Virginia (1941) — for which Tate and four others, tried without counsel, were convicted; he and his supporters maintained it was a frame-up.',
                'convicted' => 'Convicted in 1941 (tried without benefit of counsel) and sentenced to ten years on a Virginia chain gang; he escaped in 1943.',
                'sentence' => 'Ten years on a chain gang; he escaped in 1943, and the Civil Rights Congress later defeated Virginia\'s effort to extradite him from California.',
            ],
            [
                'name' => 'Bennie Daniels', 'first' => 'Bennie', 'last' => 'Daniels',
                'race' => 'Black', 'state' => 'North Carolina', 'era' => '1940s', 'released' => false, 'death' => '1953-11-06', 'institution_id' => $centralPrison->id,
                'ideologies' => [], 'affiliation' => [],
                'bio' => sprintf(self::DANIELS, 'Bennie Daniels, nineteen years old,'),
                'charges' => self::DANIELS_CHARGES, 'convicted' => self::DANIELS_CONVICTED, 'sentence' => self::DANIELS_SENTENCE,
            ],
            [
                'name' => 'Lloyd Ray Daniels', 'first' => 'Lloyd Ray', 'last' => 'Daniels',
                'race' => 'Black', 'state' => 'North Carolina', 'era' => '1940s', 'released' => false, 'death' => '1953-11-06', 'institution_id' => $centralPrison->id,
                'ideologies' => [], 'affiliation' => [],
                'bio' => sprintf(self::DANIELS, 'Lloyd Ray Daniels, eighteen years old,'),
                'charges' => self::DANIELS_CHARGES, 'convicted' => self::DANIELS_CONVICTED, 'sentence' => self::DANIELS_SENTENCE,
            ],
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
                    'gender'         => 'Male',
                    'race'           => $c['race'],
                    'death_date'     => $c['death'],
                    'state'          => $c['state'],
                    'era'            => $c['era'],
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => $c['released'],
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $c['institution_id'],
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
