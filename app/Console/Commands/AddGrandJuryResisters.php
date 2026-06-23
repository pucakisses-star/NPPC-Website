<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds seven documented political prisoners from the John Brown Anti-Klan
 * Committee's November 1984 "Stop the Grand Jury" support roster (adapted from
 * Breakthrough) who were not yet in the database — all jailed for resisting
 * federal grand juries (or, for Al-Jundi, an Attica-uprising prisoner):
 *
 *   - Akil Al-Jundi        — Attica 1971 uprising leader; murder/life 1961, paroled 1976
 *   - Richard Delaney      — civil contempt ~18mo, 1981 Brink's/BLA grand jury (SDNY)
 *   - Aisha Buckner        — civil contempt ~18mo, same Brink's grand jury
 *   - Federico Cintrón Fiallo — criminal contempt, 2 yrs, FALN grand jury (Brooklyn)
 *   - Carlos Noya          — resisted twice (17mo + 2 yrs), FALN grand juries
 *   - Phil Shinnick        — contempt ~2mo, SLA/Patty Hearst grand jury (PA)
 *   - Jay Weiner           — contempt ~4mo, SLA/Patty Hearst grand jury (PA)
 *
 * Sourced to federal court records (In re Sunni-Ali, 565 F. Supp. 1035;
 * United States v. Weiner, 418 F. Supp. 941; Al-Jundi v. Rockefeller/Mancusi;
 * United States v. Rodriguez), UPI wire reports (1984-04-09), the Freedom
 * Archives copy of the JBAKC roster, and the prisoners' own statements/memoirs.
 *
 * Idempotent and safe to re-run: matches by name, fills only blank fields, and
 * adds a documented case only when the record has none. --dry-run previews.
 */
final class AddGrandJuryResisters extends Command
{
    protected $signature = 'prisoners:add-grand-jury-resisters {--dry-run : Preview without saving}';

    protected $description = 'Add 7 documented grand-jury resisters / political prisoners (Al-Jundi, Delaney, Buckner, Cintrón Fiallo, Noya, Shinnick, Weiner)';

    /** @return array<int,array<string,mixed>> */
    private function people(): array
    {
        return [
            [
                'name' => 'Akil Al-Jundi',
                'first_name' => 'Akil',
                'last_name' => 'Al-Jundi',
                'aka' => 'Herbert Scott Dean',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Black liberation', "Prisoners' rights"],
                'affiliation' => ['Attica Brothers'],
                'in_custody' => false,
                'released' => true,
                'death_date' => '1997-08-13',
                'description' => 'Akil Al-Jundi (born Herbert Scott Dean in St. Croix, U.S. Virgin Islands) was '
                    .'sentenced to life for murder in 1961 and sent to Attica, where he became a leader during the '
                    .'September 1971 prison uprising and was among those guarding the hostages in D-Yard; he was shot in '
                    .'the hand and face during the New York State Police retaking on September 13, 1971. He became the '
                    .'lead named plaintiff in the landmark civil-rights class action Al-Jundi v. Rockefeller (later '
                    .'Al-Jundi v. Mancusi), filed in 1974 on behalf of roughly 1,200 Attica survivors, which settled in '
                    .'2000 for $8 million to the plaintiff class. After his 1976 parole he became a respected paralegal '
                    .'and prisoners\' rights advocate. He died of complications from diabetes on August 13, 1997.',
                'case' => [
                    'charges' => 'Murder',
                    'convicted' => 'Yes — convicted of murder in 1961.',
                    'sentence' => 'Life imprisonment; sent to Attica, where he became a leader of the 1971 uprising. '
                        .'Paroled in 1976.',
                    'incarceration_date' => '1961-01-01',
                    'release_date' => '1976-01-01',
                ],
            ],
            [
                'name' => 'Richard Delaney',
                'first_name' => 'Richard',
                'last_name' => 'Delaney',
                'gender' => 'Male',
                'state' => 'New York',
                'era' => '1980s',
                'ideologies' => ['Black liberation', 'Anti-imperialism'],
                'affiliation' => ['Black Acupuncture Advisory Association of North America (BAAANA)'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Richard Delaney was a grand jury resister jailed for civil contempt in 1982–83, one '
                    .'of seven people named in In re Sunni-Ali (565 F. Supp. 1035, S.D.N.Y. 1983) held at New York\'s '
                    .'Metropolitan Correctional Center for refusing to testify before the federal grand jury '
                    .'investigating the October 20, 1981 Brink\'s armored-truck robbery in Nyack, New York — an action '
                    .'of the Black Liberation Army and Republic of New Afrika. Per Black Liberation Army defendant '
                    .'Kuwasi Balagoon\'s trial statement, Delaney and his co-resisters were jailed "for 18 months or '
                    .'more," imprisoned not for any charged crime but for refusing to cooperate with the grand jury. He '
                    .'was associated with the Black Acupuncture Advisory Association of North America (BAAANA), the '
                    .'Harlem clinic at the center of the investigation.',
                'case' => [
                    'charges' => 'Civil contempt (28 U.S.C. § 1826) for refusing to testify before the federal grand '
                        .'jury investigating the 1981 Brink\'s robbery (Black Liberation Army / Republic of New Afrika).',
                    'convicted' => 'No — held in civil contempt (coercive confinement to compel testimony), not '
                        .'criminally convicted.',
                    'sentence' => 'Incarcerated at the Metropolitan Correctional Center, New York, for the life of the '
                        .'grand jury; co-resisters were described as held 18 months or more.',
                ],
            ],
            [
                'name' => 'Aisha Buckner',
                'first_name' => 'Aisha',
                'last_name' => 'Buckner',
                'aka' => 'Aiysha Buckner',
                'gender' => 'Female',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1980s',
                'ideologies' => ['Black liberation', 'New Afrikan independence', 'Anti-imperialism'],
                'affiliation' => ['Republic of New Afrika', 'Black Acupuncture Advisory Association of North America (BAAANA)'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Aisha (Aiysha) Buckner was one of "six Black grand jury resisters" jailed for civil '
                    .'contempt in 1983 for refusing to testify before the federal grand jury in the Southern District of '
                    .'New York investigating the October 1981 Brink\'s armored-car robbery and the associated Black '
                    .'Liberation Army / Republic of New Afrika network (the same probe behind United States v. Shakur). '
                    .'She was held at the Metropolitan Correctional Center in Manhattan, and supporters described the '
                    .'group as held "18 months or more." Movement literature notes she was a patient at BAAANA, the '
                    .'Harlem acupuncture clinic that was a focus of the investigation. She is named as a contemnor in '
                    .'In re Sunni-Ali, 565 F. Supp. 1035 (S.D.N.Y. 1983).',
                'case' => [
                    'charges' => 'Civil contempt (28 U.S.C. § 1826) for refusing to testify before the federal grand '
                        .'jury investigating the 1981 Brink\'s robbery (Black Liberation Army / Republic of New Afrika).',
                    'convicted' => 'No — held in civil contempt (coercive confinement), not criminally convicted.',
                    'sentence' => 'Incarcerated at the Metropolitan Correctional Center, New York, for the life of the '
                        .'grand jury; the group was described as held 18 months or more.',
                ],
            ],
            [
                'name' => 'Federico Cintrón Fiallo',
                'first_name' => 'Federico',
                'last_name' => 'Cintrón Fiallo',
                'aka' => 'Federico Cintrón',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1980s',
                'ideologies' => ['Puerto Rican independence', 'Socialism'],
                'affiliation' => ['Fuerzas Armadas de Resistencia Popular (FARP)'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Federico Cintrón Fiallo, a Puerto Rican independence activist and socialist from '
                    .'Arecibo (and brother of Macheteros commander Norberto Cintrón Fiallo), refused — with Carlos Noya '
                    .'— to testify before a federal grand jury in Brooklyn investigating the FALN in 1983, telling the '
                    .'court their refusal stemmed from "a struggle of liberation." After an August 1983 mistrial he was '
                    .'convicted of criminal contempt in October 1983, and on April 9, 1984 U.S. District Judge Eugene '
                    .'Nickerson sentenced him to two years in prison; he was imprisoned until a judge ordered his '
                    .'release in 1985. He is identified as a leader of the Fuerzas Armadas de Resistencia Popular (FARP) '
                    .'and later earned a doctorate and became a university professor.',
                'case' => [
                    'charges' => 'Criminal contempt — refusing to testify before a federal grand jury in Brooklyn '
                        .'investigating the FALN.',
                    'convicted' => 'Yes — criminal contempt, October 1983 (after an August 1983 mistrial).',
                    'sentenced_date' => '1984-04-09',
                    'sentence' => 'Two years in prison, imposed April 9, 1984 by U.S. District Judge Eugene Nickerson; '
                        .'imprisoned 1984 until released in 1985.',
                    'judge' => 'Eugene Nickerson',
                ],
            ],
            [
                'name' => 'Carlos Noya',
                'first_name' => 'Carlos',
                'last_name' => 'Noya',
                'aka' => 'Carlos Noya Murati',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1980s',
                'ideologies' => ['Puerto Rican independence', 'Socialism', 'Anti-imperialism'],
                'affiliation' => ['Liga Socialista Puertorriqueña'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Carlos Noya, a leading member of the Liga Socialista Puertorriqueña from San Juan, was '
                    .'a grand jury resister twice. He first served 17 months for resisting a federal grand jury '
                    .'investigating the clandestine Puerto Rican independence movement and was released in March 1982; '
                    .'then in 1983 he and Federico Cintrón Fiallo refused to testify before a Brooklyn federal grand '
                    .'jury investigating the FALN. After an August 1983 mistrial and an October 1983 criminal-contempt '
                    .'conviction, Judge Eugene Nickerson sentenced each to two years on April 9, 1984. Noya authored the '
                    .'movement essay "Judgment of the Grand Jury."',
                'case' => [
                    'charges' => 'Criminal contempt — refusing to testify before a federal grand jury in Brooklyn '
                        .'investigating the FALN (after an earlier 17-month term for resisting a related grand jury).',
                    'convicted' => 'Yes — criminal contempt, October 1983 (after an August 1983 mistrial).',
                    'sentenced_date' => '1984-04-09',
                    'sentence' => 'Two years in prison, imposed April 9, 1984 by U.S. District Judge Eugene Nickerson. '
                        .'He had earlier served 17 months for resisting a related grand jury, released in March 1982.',
                    'judge' => 'Eugene Nickerson',
                ],
            ],
            [
                'name' => 'Phil Shinnick',
                'first_name' => 'Philip',
                'last_name' => 'Shinnick',
                'aka' => 'Philip Kent Shinnick',
                'gender' => 'Male',
                'state' => 'Pennsylvania',
                'era' => '1970s',
                'birthdate' => '1943-04-21',
                'ideologies' => ['Anti-war', 'Anti-imperialism'],
                'affiliation' => ['Athletes United for Peace'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Phil Shinnick (Philip Kent Shinnick) was an Olympic long jumper — he competed at the '
                    .'1964 Tokyo Games and set a since-recognized world long-jump record in 1963 — who became an '
                    .'anti-war activist and ally of Black athletes. In 1976 a federal grand jury in Pennsylvania '
                    .'investigating the farmhouse where Patty Hearst had hidden before her 1975 capture (the Symbionese '
                    .'Liberation Army harboring case) subpoenaed him; he refused to provide hair and handwriting '
                    .'samples, was held in contempt, and served about two months at Allenwood federal prison. He later '
                    .'became an acupuncturist.',
                'case' => [
                    'charges' => 'Civil contempt of a federal grand jury (refusal to provide hair and handwriting '
                        .'samples) — the grand jury investigating the harboring of SLA fugitive Patty Hearst.',
                    'convicted' => 'No — held in civil contempt.',
                    'sentence' => 'About two months\' imprisonment at Allenwood federal prison, Pennsylvania (1976).',
                ],
            ],
            [
                'name' => 'Jay Weiner',
                'first_name' => 'Jay',
                'last_name' => 'Weiner',
                'gender' => 'Male',
                'state' => 'Pennsylvania',
                'era' => '1970s',
                'ideologies' => ['Anti-imperialism', 'New Left'],
                'in_custody' => false,
                'released' => true,
                'description' => 'Jay Weiner was a roughly 20-year-old aspiring sportswriter and Temple University '
                    .'student who was taken to the Pennsylvania farmhouse where Symbionese Liberation Army fugitive '
                    .'Patty Hearst had been hiding. Subpoenaed on May 19, 1976 before the federal grand jury in Scranton '
                    .'investigating the harboring of Hearst and William and Emily Harris, he refused to testify and was '
                    .'jailed for contempt — by his own account about four months in federal prison (United States v. '
                    .'Weiner, 418 F. Supp. 941). He went on to a long career as a Minneapolis Star Tribune sportswriter '
                    .'and wrote a memoir, A More Dangerous Game.',
                'case' => [
                    'charges' => 'Civil contempt of a federal grand jury (refusal to testify) — the grand jury '
                        .'investigating the harboring of SLA fugitive Patty Hearst.',
                    'convicted' => 'No — held in civil contempt.',
                    'sentence' => 'About four months in federal prison (per his memoir), 1976.',
                ],
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
                $case = $spec['case'] ?? [];
                unset($spec['case']);

                $prisoner = Prisoner::withUnderReview()->where('name', $spec['name'])->first();

                if (! $prisoner) {
                    if ($dry) {
                        $this->line("  would create: {$spec['name']} (+1 case)");
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

                // Already exists — fill only blank fields and add a case if none.
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
