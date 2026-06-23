<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds four "borderline" names from the John Brown Anti-Klan Committee's
 * November 1984 "Stop the Grand Jury" roster — people on the authentic list who
 * do not cleanly fit "imprisoned political prisoner," added at the curator's
 * direction with deliberately honest framing so the records aren't misleading:
 *
 *   - Emile de Antonio   — filmmaker; resisted a 1975 grand jury subpoena for his
 *                          "Underground" footage but WON (subpoena withdrawn);
 *                          never charged or jailed.
 *   - Yuri Kochiyama     — iconic activist; documented as a supporter/defender of
 *                          grand jury resisters and political prisoners, not as a
 *                          subpoenaed/jailed resister herself.
 *   - José Luis Rodríguez — FALN co-defendant convicted of seditious conspiracy but
 *                          given a SUSPENDED sentence + probation (not imprisoned);
 *                          identity uncertain (very common name).
 *   - Naomi Burns        — listed as a 1975 grand jury resister on the roster; no
 *                          further documentation located (name + year only).
 *
 * Matches by name, fills only blank fields, and adds a case only when one is both
 * documented (present below) and missing on the record. Idempotent; --dry-run
 * previews.
 */
final class AddRosterBorderlinePrisoners extends Command
{
    protected $signature = 'prisoners:add-roster-borderline {--dry-run : Preview without saving}';

    protected $description = 'Add the 4 curator-approved borderline names from the 1984 JBAKC grand-jury roster (de Antonio, Kochiyama, J.L. Rodríguez, Burns)';

    /** @return array<int,array<string,mixed>> */
    private function people(): array
    {
        return [
            [
                'name' => 'Emile de Antonio',
                'first_name' => 'Emile',
                'last_name' => 'de Antonio',
                'aka' => 'Emile Francisco de Antonio',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1970s',
                'birthdate' => '1919-05-14',
                'death_date' => '1989-12-15',
                'ideologies' => ['Marxism', 'Anti-war', 'New Left'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Emile de Antonio (1919–1989) was a Marxist American documentary filmmaker known for '
                    .'Point of Order (1964), In the Year of the Pig (1968), and Millhouse (1971). In 1975, while making '
                    .'Underground with co-directors Haskell Wexler and Mary Lampson, he interviewed fugitive members of '
                    .'the Weather Underground; on May 28, 1975 the Department of Justice and a federal grand jury '
                    .'subpoenaed all three filmmakers, demanding their footage to help locate the fugitives. De Antonio '
                    .'resisted on First Amendment and journalistic-privilege grounds, backed by a public campaign of '
                    .'film-world figures (Jack Nicholson, Shirley MacLaine, Warren Beatty, Mel Brooks, Daniel Ellsberg '
                    .'and others), and the subpoena was withdrawn — a First Amendment victory in which no filmmaker was '
                    .'charged or jailed. He is listed among grand jury resisters on the John Brown Anti-Klan Committee\'s '
                    .'1984 "Stop the Grand Jury" roster.',
                'case' => [
                    'charges' => 'Subpoenaed (not charged) by a federal grand jury on May 28, 1975 to surrender footage '
                        .'from his documentary Underground about the Weather Underground.',
                    'convicted' => 'No — never charged; the grand jury subpoena was withdrawn after a First Amendment / '
                        .'journalist\'s-privilege challenge. He was never jailed.',
                ],
            ],
            [
                'name' => 'Yuri Kochiyama',
                'first_name' => 'Yuri',
                'last_name' => 'Kochiyama',
                'aka' => 'Mary Yuriko Nakahara',
                'gender' => 'Female',
                'race' => 'Asian',
                'state' => 'New York',
                'era' => '1970s',
                'birthdate' => '1921-05-19',
                'death_date' => '2014-06-01',
                'ideologies' => ['Black liberation', 'Anti-imperialism', 'Asian American movement', 'Political-prisoner advocacy'],
                'affiliation' => ['Organization of Afro-American Unity', 'Republic of New Afrika', 'Asian Americans for Action'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Yuri Kochiyama (born Mary Yuriko Nakahara) was a Japanese American human-rights '
                    .'activist based in Harlem, interned at the Jerome camp in Arkansas during World War II, an associate '
                    .'of Malcolm X (she was present at his 1965 assassination), and a tireless advocate for Black '
                    .'liberation, Puerto Rican independence, Asian American movements, and political prisoners. She is '
                    .'listed among grand jury resisters (1974–75) on the John Brown Anti-Klan Committee\'s 1984 "Stop '
                    .'the Grand Jury" roster; her documented role in that movement, however, was as a leading supporter '
                    .'and defender of grand jury resisters and political prisoners rather than as a subpoenaed witness '
                    .'herself. She and her husband Bill were also active in the Japanese American redress movement, and '
                    .'she took part in 1960s–70s direct actions including the 1977 occupation of the Statue of Liberty '
                    .'demanding freedom for Puerto Rican Nationalist prisoners.',
                // No case: she is documented as a supporter/defender, not as someone
                // jailed for grand jury resistance — recording a case would overstate it.
            ],
            [
                'name' => 'José Luis Rodríguez',
                'first_name' => 'José',
                'last_name' => 'Rodríguez',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Illinois',
                'era' => '1980s',
                'ideologies' => ['Puerto Rican independence'],
                'affiliation' => ['Puerto Rican independence movement'],
                'in_custody' => false,
                'released' => true,
                'description' => 'José Luis Rodríguez was one of four people arrested in the Chicago area on June 29, '
                    .'1983 in a major FALN case after months of FBI surveillance, in which the government alleged a plot '
                    .'targeting military installations. After a 1985 trial he was convicted of one count of seditious '
                    .'conspiracy (18 U.S.C. § 2384) but — unlike co-defendants Alberto Rodríguez, Edwin Cortés, and '
                    .'Alejandrina Torres, who received 35-year terms — was given a suspended sentence and five years\' '
                    .'probation; his conviction was affirmed by the Seventh Circuit in 1986 (United States v. Rodriguez, '
                    .'803 F.2d 318). At trial he affirmed support for Puerto Rican independence while denying FALN '
                    .'membership. (This identification is based on the Chicago FALN seditious-conspiracy case; the very '
                    .'common name makes certainty difficult.)',
                'case' => [
                    'charges' => 'Seditious conspiracy (18 U.S.C. § 2384), in the 1983 Chicago FALN case.',
                    'convicted' => 'Yes — convicted of seditious conspiracy in 1985; affirmed by the Seventh Circuit in '
                        .'1986 (United States v. Rodriguez, 803 F.2d 318).',
                    'arrest_date' => '1983-06-29',
                    'sentence' => 'Suspended sentence and five years\' probation (no long-term incarceration).',
                ],
            ],
            [
                'name' => 'Naomi Burns',
                'first_name' => 'Naomi',
                'last_name' => 'Burns',
                'gender' => 'Female',
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'description' => 'Naomi Burns is listed as a grand jury resister (1975) on the John Brown Anti-Klan '
                    .'Committee\'s November 1984 "Stop the Grand Jury" roster, grouped with resisters tied to the New '
                    .'Afrikan (Black) liberation and Puerto Rican independence movements of the mid-1970s. Beyond this '
                    .'primary-source listing, no further documentation of her case has been located.',
                // No case: nothing beyond the roster listing is documented.
            ],
        ];
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $added = 0;
        $enriched = 0;
        $unchanged = 0;

        $run = function () use ($dry, &$added, &$enriched, &$unchanged) {
            foreach ($this->people() as $spec) {
                $case = $spec['case'] ?? null;
                unset($spec['case']);

                $prisoner = Prisoner::withUnderReview()->where('name', $spec['name'])->first();

                if (! $prisoner) {
                    if ($dry) {
                        $this->line("  would create: {$spec['name']}".($case ? ' (+1 case)' : ''));
                        $added++;

                        continue;
                    }
                    $prisoner = Prisoner::create($spec);
                    if ($case) {
                        PrisonerCase::create(['prisoner_id' => $prisoner->id] + $case);
                    }
                    $this->info("  created: {$prisoner->name} (/{$prisoner->slug})");
                    $added++;

                    continue;
                }

                $changes = [];
                foreach ($spec as $key => $value) {
                    if ($key === 'name') {
                        continue;
                    }
                    $current = $prisoner->{$key};
                    $blank = is_array($current) ? empty($current) : ($current === null || $current === '');
                    if ($key === 'in_custody' || $key === 'released') {
                        $blank = $current === null;
                    }
                    if ($blank) {
                        $changes[$key] = $value;
                    }
                }
                $needsCase = $case && $prisoner->cases()->count() === 0;

                if (! $changes && ! $needsCase) {
                    $this->line("  unchanged: {$prisoner->name}");
                    $unchanged++;

                    continue;
                }

                if ($dry) {
                    $this->line("  would enrich: {$prisoner->name}"
                        .($changes ? ' ('.implode(', ', array_keys($changes)).')' : '')
                        .($needsCase ? ' +1 case' : ''));
                    $enriched++;

                    continue;
                }

                if ($changes) {
                    $prisoner->forceFill($changes)->save();
                }
                if ($needsCase) {
                    PrisonerCase::create(['prisoner_id' => $prisoner->id] + $case);
                }
                $this->info("  enriched: {$prisoner->name}");
                $enriched++;
            }
        };

        $dry ? $run() : DB::transaction($run);

        $this->info("\nDone".($dry ? ' (dry run)' : '').". added={$added} enriched={$enriched} unchanged={$unchanged}");

        return self::SUCCESS;
    }
}
