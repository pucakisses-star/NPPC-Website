<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fifth batch from reading The Black Panther — drawn from the September 6, 1969
 * issue:
 *
 *  - Fred Hampton — the Illinois BPP chairman, imprisoned in 1969 over the
 *    trumped-up "$71 ice cream" robbery (released on appeal bond Aug 15, 1969)
 *    months before Chicago police killed him on Dec 4, 1969. A notable gap in
 *    the database until now.
 *  - The 1967 Plainfield, New Jersey rebellion (the killing of Officer John V.
 *    Gleason): George Merritt Jr. (convicted of murder, life; exonerated 1980),
 *    Gail Madden (convicted, life; reversed — State v. Madden, 1972), and Bobby
 *    Lee Williams (shot by Gleason, then charged with inciting; defended by
 *    William Kunstler). The trio of "Plainfield's Black Hostages."
 *  - Sal Candelaria — a leader of the San Jose Black Berets; his specific 1969
 *    case is documented only in The Black Panther (flagged in the bio).
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers5Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-5';

    protected $description = 'Add Black Panther newspaper prisoners from the Sept 1969 issue (Hampton, Plainfield, Candelaria)';

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
        // Plainfield-rebellion defendants share state/era/framing.
        $plainfield = function (string $name, string $first, string $last, string $desc, array $case, bool $female = false): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $female ? 'Female' : 'Male',
                'race' => 'Black',
                'state' => 'New Jersey',
                'era' => '1960s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => $desc,
                'cases' => [$case],
            ];
        };

        return [
            [
                'name' => 'Fred Hampton',
                'first_name' => 'Fred',
                'last_name' => 'Hampton',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Illinois',
                'era' => '1960s',
                'birthdate' => '1948-08-30',
                'death_date' => '1969-12-04',
                'ideologies' => ['Marxism-Leninism', 'Revolutionary socialism', 'Black Power'],
                'affiliation' => ['Black Panther Party', 'Rainbow Coalition'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Fred Hampton was chairman of the Illinois chapter of the Black Panther Party and deputy chairman of the national party, known for building the multiracial "Rainbow Coalition" in Chicago. In May 1969 he was convicted of robbery in the Circuit Court of Cook County over a July 1968 incident in which $71 worth of ice cream was taken from a Good Humor driver at a Maywood playground and handed out to neighborhood children — a conviction widely seen as political. Sentenced to two to five years, he was imprisoned at Menard and then released on appeal bond by the Illinois Supreme Court on August 15, 1969. Weeks later, on December 4, 1969, he was killed in his bed during a pre-dawn raid by Chicago police acting with the Cook County State\'s Attorney and the FBI — an operation later shown to be part of COINTELPRO.',
                'cases' => [[
                    'institution_name' => 'Menard Correctional Center',
                    'institution_city' => 'Menard',
                    'institution_state' => 'Illinois',
                    'charges' => 'Robbery — the alleged theft of $71 of ice cream from a Good Humor truck driver in Maywood, July 1968',
                    'release_date' => '1969-08-15',
                    'convicted' => 'Yes — convicted of robbery by a jury (May 1969); affirmed by the Illinois Supreme Court, Nov. 26, 1969',
                    'sentence' => 'Two to five years; imprisoned at Menard, released on appeal bond August 15, 1969',
                ]],
            ],
            $plainfield('George Merritt Jr.', 'George', 'Merritt',
                'George Merritt Jr. was convicted of the first-degree murder of Plainfield, New Jersey police officer John V. Gleason Jr., who was beaten to death by a crowd during the July 1967 Plainfield rebellion. His prosecution became a long-running cause célèbre: first convicted in December 1968 and sentenced to life, he was tried repeatedly as convictions were reversed and reinstated. In 1980 a federal court granted habeas relief (United States ex rel. Merritt v. Hicks) after his lawyers obtained suppressed evidence that the key identifying witness had drastically changed his account, and the indictment was dismissed. He is recognized as an exoneree by the National Registry of Exonerations after roughly twelve years imprisoned, much of it at Rahway State Prison.',
                [
                    'institution_name' => 'Rahway State Prison',
                    'institution_city' => 'Rahway',
                    'institution_state' => 'New Jersey',
                    'charges' => 'First-degree murder of Police Officer John V. Gleason Jr. (Plainfield rebellion, July 1967)',
                    'convicted' => 'Yes — convicted Dec. 1968 (life); reversed and retried repeatedly; conviction overturned and indictment dismissed via federal habeas in 1980 (recognized as an exoneree)',
                    'sentence' => 'Life imprisonment; served roughly twelve years before the 1980 exoneration',
                ]),
            $plainfield('Gail Madden', 'Gail', 'Madden',
                'Gail Madden was a co-defendant convicted of the first-degree murder of Plainfield, New Jersey police officer John V. Gleason Jr., who was beaten to death by a crowd during the July 16, 1967 Plainfield rebellion. Tried alongside George Merritt Jr., she was convicted in December 1968 and sentenced to life imprisonment. The New Jersey Appellate Division reversed the convictions over erroneous jury instructions on the degrees of murder, and in 1972 the New Jersey Supreme Court (State v. Madden, 61 N.J. 377) affirmed that reversal and remanded for retrial. She was one of the "Plainfield\'s Black Hostages" whose freedom a defense committee championed.',
                [
                    'institution_name' => 'New Jersey State Prison',
                    'institution_state' => 'New Jersey',
                    'charges' => 'First-degree murder of Police Officer John V. Gleason Jr. (Plainfield rebellion, July 1967)',
                    'convicted' => 'Yes — convicted Dec. 1968 (life); conviction reversed (State v. Madden, N.J. Supreme Court, 1972) and remanded for retrial',
                    'sentence' => 'Life imprisonment (reversed on appeal)',
                ], true),
            $plainfield('Bobby Lee Williams', 'Bobby', 'Williams',
                'Bobby Lee Williams was a young Black man who, during the Plainfield, New Jersey rebellion, was shot and seriously wounded by police officer John V. Gleason Jr. on July 16, 1967; the crowd that witnessed the shooting then beat Gleason to death. After recovering from his wounds, Williams was himself charged in connection with Gleason\'s death — accused of inciting the crowd, along with assault — and was defended by movement attorneys William Kunstler and George Mutnick. He was one of the three "Plainfield\'s Black Hostages," alongside Gail Madden and George Merritt Jr., around whom a defense campaign was organized.',
                [
                    'institution_state' => 'New Jersey',
                    'charges' => 'Inciting to murder and assault, in connection with the death of Officer John V. Gleason Jr.; Williams had himself been shot and wounded by Gleason on July 16, 1967',
                ]),
            [
                'name' => 'Sal Candelaria',
                'first_name' => 'Sal',
                'last_name' => 'Candelaria',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'California',
                'era' => '1960s',
                'ideologies' => ['Chicano nationalism', 'Chicano Movement'],
                'affiliation' => ['Black Berets'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Sal Candelaria was a leader and organizer of the Black Berets (Black Berets por La Justicia), a Chicano militant self-defense and community organization based in East San Jose, California. According to The Black Panther (Sept. 6, 1969), he was convicted in 1969 of resisting arrest, possession of a loaded firearm in public, and assault with a deadly weapon following a February 16, 1969 confrontation at the University of Santa Clara, and was sent to the California Medical Facility at Vacaville for evaluation before sentencing. He is independently documented as a figure in San Jose Black Berets / Chicano-movement history. NOTE: the specific 1969 charges and outcome are documented only in The Black Panther and are not independently corroborated.',
                'cases' => [[
                    'institution_name' => 'California Medical Facility, Vacaville',
                    'institution_city' => 'Vacaville',
                    'institution_state' => 'California',
                    'charges' => 'Resisting arrest; possession of a loaded firearm in public; assault with a deadly weapon — incident at the University of Santa Clara, Feb. 16, 1969 (per The Black Panther)',
                    'arrest_date' => '1969-02-16',
                    'convicted' => 'Yes — convicted 1969 per The Black Panther (sent to Vacaville for evaluation before sentencing); not independently corroborated',
                ]],
            ],
        ];
    }
}
