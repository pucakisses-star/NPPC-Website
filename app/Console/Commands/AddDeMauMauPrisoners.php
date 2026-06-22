<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * "De Mau Mau" — an organization formed by Black servicemen in Vietnam to
 * combat racism in the U.S. military (later including Puerto Rican and Native
 * servicemen), some of whom continued it in civilian life in Chicago. In 1972,
 * amid a series of murders of white people in the Chicago suburbs (a family of
 * four in Barrington Hills on Aug. 4, a family of three in Monee on Sept. 3),
 * Black members of De Mau Mau — young men from Chicago's South Side attending
 * Malcolm X and Kennedy-King colleges — were arrested and prosecuted. The Black
 * Panther (Nov. 1972) reported the case as racist sensationalism tied to State's
 * Attorney Edward Hanrahan's re-election. The prosecutions were and remain
 * contested; several defendants were convicted in 1973.
 *
 * Recorded here are the six members The Black Panther named. Idempotent: skips
 * any name already present.
 */
final class AddDeMauMauPrisoners extends Command
{
    protected $signature = 'prisoners:add-de-mau-mau';

    protected $description = 'Add the De Mau Mau defendants named in The Black Panther (1972)';

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
                    // The case carries pseudo-fields (institution_*) that are not
                    // columns; drop them (these defendants have no named facility).
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
        $deMauMau = function (string $name, string $first, string $last, string $ageNote = ''): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Illinois',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['De Mau Mau'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was a young Black man from Chicago's South Side and a member of De Mau Mau, an organization formed by Black servicemen in Vietnam to resist racism in the U.S. military.{$ageNote} In 1972 he was among those arrested and prosecuted in a series of murders of white people in the Chicago suburbs (including a family of four in Barrington Hills and a family of three in Monee). The Black Panther (November 1972) reported the case as racist sensationalism — built without clear evidence and tied to State's Attorney Edward Hanrahan's re-election campaign — that had \"convicted\" the defendants in the press before trial. The prosecutions were heavily contested; several of the De Mau Mau defendants were convicted in 1973.",
                'cases' => [[
                    'institution_state' => 'Illinois',
                    'charges' => 'Murder (the 1972 Chicago-area / suburban murders, prosecuted as a member of De Mau Mau; a case The Black Panther and supporters described as a racially driven frame-up)',
                ]],
            ];
        };

        return [
            $deMauMau('Reuben Taylor', 'Reuben', 'Taylor', ' He was 22.'),
            $deMauMau('Donald Taylor', 'Donald', 'Taylor'),
            $deMauMau('Michael Clark', 'Michael', 'Clark', ' He was 21.'),
            $deMauMau('Nathaniel Burse', 'Nathaniel', 'Burse'),
            $deMauMau('Howard Moran', 'Howard', 'Moran', ' He was 23.'),
            $deMauMau('Robert Wilson', 'Robert', 'Wilson', ' He was 18.'),
        ];
    }
}
