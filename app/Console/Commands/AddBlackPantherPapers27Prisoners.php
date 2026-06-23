<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Twenty-seventh batch from reading The Black Panther — the February 15, 1975
 * issue (Vol. 12 No. 30), whose article "Thirty-Eight Indians Charged in Abbey
 * Takeover" covers the Menominee Warrior Society's 34-day armed occupation of the
 * vacant Alexian Brothers Novitiate near Gresham, Wisconsin (January 1 – February
 * 3, 1975). The Warriors demanded the unused estate be deeded to the impoverished,
 * recently-restored Menominee tribe for a hospital/school; ~39 were arrested when
 * they surrendered to the National Guard. Five leaders were charged with felonies
 * (burglary/robbery and related counts) carrying up to 90 years; three of the
 * five — including Sturdevant — ultimately served prison time.
 *
 * Added here are the five named felony defendants: Michael Sturdevant, John
 * Waubanascum Jr., Doreen Dixon, John Perrote and Robert Chevalier.
 *
 * Idempotent: skips any name already present.
 */
final class AddBlackPantherPapers27Prisoners extends Command
{
    protected $signature = 'prisoners:add-black-panther-papers-27';

    protected $description = 'Add Menominee Warrior Society takeover defendants from the Feb 15, 1975 Black Panther, batch 27';

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
        $context = 'In the early hours of January 1, 1975, members of the Menominee Warrior Society — an armed group '
            .'of younger Menominee men inspired by the American Indian Movement\'s actions at Alcatraz and Wounded Knee '
            .'— seized the vacant Alexian Brothers Novitiate, a former Catholic monastery near Gresham, Wisconsin. '
            .'Coming barely a year after the Menominee Restoration Act reversed the tribe\'s 1950s "termination," they '
            .'demanded the unused estate be deeded to the impoverished tribe for use as a hospital or school, citing '
            .'treaty and reversion rights. The 34-day standoff drew some 800–2,200 Wisconsin National Guard troops '
            .'(deployed by Governor Patrick Lucey, who refused to order an assault) and the support of actor Marlon '
            .'Brando, who called the occupiers "prisoners of war." About 39 Warriors surrendered on February 3, 1975 '
            .'after the Alexian Brothers agreed to transfer the property for $1; the tribe never gained lasting '
            .'possession of it.';

        $member = function (string $name, string $first, string $last, string $gender, string $detail, string $charges, ?string $convicted = null, array $opts = []) use ($context): array {
            $rec = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => $gender,
                'race' => 'Native American',
                'state' => 'Wisconsin',
                'era' => '1970s',
                'ideologies' => ['Native American sovereignty', 'Indigenous rights'],
                'affiliation' => ['Menominee Warrior Society'],
                'in_custody' => false,
                'released' => $opts['released'] ?? true,
                'awaiting_trial' => false,
                'description' => "{$name} was a member of the Menominee Warrior Society and one of the leaders "
                    ."prosecuted after its 1975 takeover of the Alexian Brothers Novitiate. {$detail} {$context}",
                'cases' => [[
                    'charges' => $charges,
                ]],
            ];
            foreach (['aka', 'death_date'] as $f) {
                if (! empty($opts[$f])) {
                    $rec[$f] = $opts[$f];
                }
            }
            if ($convicted !== null) {
                $rec['cases'][0]['convicted'] = $convicted;
            }

            return $rec;
        };

        // Of the five charged felony leaders, only three (including Sturdevant) are documented to have served
        // prison time; the individual dispositions of Dixon, Perrote and Robert Chevalier are not on record.
        $undocumented = 'One of five Menominee Warrior Society leaders charged with felonies for the takeover (counts '
            .'prosecutors said could carry more than 90 years each). Three of the five — including Sturdevant — '
            .'ultimately served prison time; the disposition of this defendant\'s individual case is not documented.';

        return [
            $member('Michael Sturdevant', 'Michael', 'Sturdevant', 'Male',
                'Nicknamed "the General," he led the occupation. The Wisconsin Supreme Court affirmed his conviction '
                .'for aiding and abetting burglary (Sturdevant v. State, 1977), rejecting his argument that the state '
                .'lacked jurisdiction to try an enrolled Menominee; he was sentenced to eight years and was one of '
                .'only three of the charged leaders to serve prison time. He had earlier been a federal defendant from '
                .'the 1973 Wounded Knee occupation and was acquitted in that case. Born around 1944, he later worked as '
                .'a Menominee tribal tax commissioner and a reporter for the Menominee Tribal News, and died in 2005.',
                'Aiding and abetting burglary (Wis. Stat. §§ 943.10(1)(a), 939.05), among the felony charges for the January–February 1975 occupation of the Alexian Brothers Novitiate near Gresham, Wisconsin.',
                'Yes — convicted of aiding and abetting burglary; conviction affirmed by the Wisconsin Supreme Court in 1977. Sentenced to eight years and served prison time (one of three charged leaders to do so).',
                ['aka' => 'Michael Eugene Sturdevant']),
            $member('John Waubanascum Jr.', 'John', 'Waubanascum', 'Male',
                'A Vietnam veteran described as a "lieutenant" of the occupation, he was one of the five leaders '
                .'charged with the most serious felonies and gave a first-person account of the takeover ("So I '
                .'Started Fighting For My People," 1976). He never stood trial: while free on bond, he was shot and '
                .'killed by a county/tribal police officer on February 3, 1976 — a year to the day after the '
                .'occupation ended — reportedly in front of his wife and children.',
                'Armed robbery, armed burglary, false imprisonment and endangering life, for the January–February 1975 occupation of the Alexian Brothers Novitiate.',
                'Never tried — he was killed on February 3, 1976, before his case went to trial, so there was no verdict.',
                ['aka' => 'John Waubunascum Jr.', 'death_date' => '1976-02-03', 'released' => false]),
            $member('Doreen Dixon', 'Doreen', 'Dixon', 'Female',
                'She was the one woman among the five Warrior Society leaders charged with the most serious felonies.',
                'Felony charges (burglary/robbery and related counts) for the January–February 1975 occupation of the Alexian Brothers Novitiate.',
                $undocumented),
            $member('John Perrote', 'John', 'Perrote', 'Male',
                'He was one of the five Warrior Society leaders charged with the most serious felonies.',
                'Felony charges (burglary/robbery and related counts) for the January–February 1975 occupation of the Alexian Brothers Novitiate.',
                $undocumented),
            $member('Robert Chevalier', 'Robert', 'Chevalier', 'Male',
                'He was one of the five Warrior Society leaders charged with the most serious felonies.',
                'Felony charges (burglary/robbery and related counts) for the January–February 1975 occupation of the Alexian Brothers Novitiate.',
                $undocumented),
        ];
    }
}
