<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fourth batch from reading The Black Panther (1967-1970) — the externally
 * documented political prisoners surfaced from the 1969 issues (most names in
 * those issues turned out to be newspaper-only and were excluded):
 *
 *  - The "Milwaukee Three" — Booker Collins, Jesse White and Earl Leverette,
 *    arrested September 1969 and charged with the attempted murder of a police
 *    officer. Collins and White got up to 30 years; Leverette jumped bail, was
 *    recaptured by the FBI in Cincinnati, and got 10 years. (Documented via the
 *    UWM WTMJ-TV archive, the Wisconsin State Journal, and Andrew Witt's
 *    history of the Milwaukee BPP.)
 *  - Harold Holmes — leader of the New Orleans NCCF (the BPP's organizing arm)
 *    during the 1970 Desire Housing Project standoff; arrested Nov 25, 1970 on
 *    a criminal-trespass charge (documented in "Showdown in Desire").
 *
 * Excluded as newspaper-only/unverifiable after research: Robert Bostick, the
 * Indianapolis "Bobby Lee Williams / 16 Panthers," the Oasis Theater names
 * (Gentry, Richard Smith, Watkins), and Carver "Chico" Neblett (who was
 * expelled from the Party, not imprisoned). Idempotent: skips existing names.
 */
final class AddBlackPantherPapers4Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-4';

    protected $description = 'Add documented 1969-70 Black Panther prisoners (Milwaukee Three + Harold Holmes)';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        // Shared scaffold for the Milwaukee Three (attempted murder, Sept 1969).
        $milwaukee = function (string $name, string $first, string $last, string $desc, string $sentence): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Wisconsin',
                'era' => '1960s',
                'ideologies' => ['Black Power', 'Black nationalism', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => $desc,
                'cases' => [[
                    'charges' => 'Attempted murder of a police officer (Milwaukee, September 1969); one of the "Milwaukee Three"',
                    'convicted' => 'Yes',
                    'sentence' => $sentence,
                ]],
            ];
        };

        return [
            $milwaukee('Booker Collins', 'Booker', 'Collins',
                'Booker T. Collins Jr. was a member of the Milwaukee, Wisconsin chapter of the Black Panther Party, founded in January 1969. In September 1969 he was arrested with Jesse White and Earl Leverette and charged with the attempted murder of a police officer; the three became known as the "Milwaukee Three." All three said they were severely beaten by police after their arrest. Collins was convicted and sentenced to up to 30 years in prison. The case was the most prominent prosecution of the early Milwaukee Panther chapter.',
                'Up to 30 years imprisonment'),
            $milwaukee('Jesse White', 'Jesse', 'White',
                'Jesse White (also spelled Jessie White) was a member of the Milwaukee, Wisconsin chapter of the Black Panther Party. In September 1969 he was arrested alongside Booker Collins and Earl Leverette and charged with the attempted murder of a police officer; the three became the "Milwaukee Three." The defendants alleged they were repeatedly beaten by police following their arrest. White was convicted and sentenced to up to 30 years in prison, and a public rally demanded the release of the three Milwaukee Panthers in early 1970. (Not to be confused with the Illinois politician of the same name.)',
                'Up to 30 years imprisonment'),
            $milwaukee('Earl Leverette', 'Earl', 'Leverette',
                'Earl Leverette (also spelled Levrettes) was a member of the Milwaukee, Wisconsin chapter of the Black Panther Party and one of the "Milwaukee Three," arrested in September 1969 and charged with the attempted murder of a police officer along with Booker Collins and Jesse White. In September 1970, after the first morning of his trial, Leverette jumped bail and disappeared; he was later captured by FBI agents in Cincinnati, Ohio, and returned to Milwaukee in October 1971 at age 26. He was convicted and received a 10-year sentence, lighter than his two co-defendants.',
                '10 years imprisonment'),
            [
                'name' => 'Harold Holmes',
                'first_name' => 'Harold',
                'last_name' => 'Holmes',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Louisiana',
                'era' => '1970s',
                'ideologies' => ['Black Power', 'Revolutionary socialism'],
                'affiliation' => ['Black Panther Party', 'National Committee to Combat Fascism'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Harold Holmes was a leader of the New Orleans National Committee to Combat Fascism (NCCF), the recruiting arm of the Black Panther Party, which established itself in the city\'s Desire Housing Project in 1970. He became the public face of the group\'s standoff with New Orleans police over an attempted eviction and a challenge to Louisiana\'s criminal-trespass law. On November 25, 1970 he was arrested as he and several carloads of supporters tried to leave the city, reportedly using vehicles rented by activist Jane Fonda to travel to a Washington, D.C. rally. He is profiled in Orissa Arend\'s history "Showdown in Desire: The Black Panthers Take a Stand in New Orleans." (A 1969 issue of The Black Panther separately reported a "Harold Holmes" jailed over a draft card; that earlier claim is not independently corroborated and is not confirmed to be the same person.)',
                'cases' => [[
                    'charges' => 'Criminal trespass, arising from the standoff at the Desire Housing Project in New Orleans',
                    'arrest_date' => '1970-11-25',
                ]],
            ],
        ];
    }
}
