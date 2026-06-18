<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds four early Communist Party of America figures named in the October
 * 1919 Bureau of Investigation surveillance report "A Visit to Communist
 * Party Headquarters, Chicago" (added to the archive separately), who were
 * each prosecuted, jailed, or held for deportation for their political
 * activity during the post-WWI Red Scare:
 *
 *   - Louis Fraina   — 1917 anti-draft conviction; 1920 CPA indictment;
 *                       1950s McCarran Act deportation order (died first).
 *   - Joseph Stilson — Espionage Act conviction, 3 years, affirmed by the
 *                       U.S. Supreme Court (Stilson v. United States, 1919).
 *   - Dennis Batt    — arrested on the CPA convention floor (Sept 2, 1919)
 *                       under the Illinois Sedition Act.
 *   - Alexander Stoklitsky — subject of a documented 1919 federal
 *                       deportation case; left for Soviet Russia in 1920
 *                       (outcome undocumented).
 *
 * Era/flags follow the convention used for the other first-Red-Scare figures
 * already in the database (era "1910s", released). Idempotent: skips any
 * person whose name already exists. Unknown birth/death dates and case
 * details are intentionally left blank rather than guessed.
 */
final class AddCpa1919Prisoners extends Command
{
    protected $signature = 'prisoners:add-cpa-1919';

    protected $description = 'Add early CPA figures (Fraina, Stilson, Batt, Stoklitsky) from the 1919 Red Scare';

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
                    $instName = $c['institution'] ?? null;
                    unset($c['institution']);
                    if ($instName) {
                        $c['institution_id'] = Institution::firstOrCreate(['name' => $instName])->id;
                    }
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
        return [
            [
                'name' => 'Louis Fraina',
                'first_name' => 'Louis',
                'last_name' => 'Fraina',
                'aka' => 'Lewis Corey',
                'gender' => 'Male',
                'race' => 'White',
                'birthdate' => '1892-10-07',
                'death_date' => '1953-09-16',
                'state' => 'New York',
                'era' => '1910s',
                'ideologies' => ['Communism', 'Marxism'],
                'affiliation' => ['Communist Party of America'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Louis C. Fraina (1892–1953) was an Italian-born American Marxist theorist, journalist, and editor who emigrated to the United States as a small child. A political opponent of conscription, he was arrested in 1917 for anti-draft activity, convicted, and served a 30-day jail sentence in 1919. In September 1919 he co-founded the Communist Party of America and became its first International Secretary; in January 1920 he was indicted with 84 other CPA leaders during the Palmer Red Scare on a charge of conspiracy to cause armed revolution, though he was abroad on a Communist International mission and was never tried on it. He later broke with Communism and reemerged in the 1930s as the prominent economist and writer Lewis Corey; the U.S. government moved to deport him under the McCarran Act in 1950–1952, but he died in 1953 before the order was carried out.',
                'cases' => [
                    [
                        'charges' => 'Anti-conscription activity (1917): conspiracy to obstruct the draft / Selective Service Act',
                        'convicted' => 'Yes — convicted 1917',
                        'sentence' => '30 days, served in 1919. (Separately indicted in the January 1920 CPA mass indictment for conspiracy to cause armed revolution, but abroad and never tried; ordered deported under the McCarran Act in 1952, but died in 1953 before removal.)',
                    ],
                ],
            ],
            [
                'name' => 'Joseph Stilson',
                'first_name' => 'Joseph',
                'last_name' => 'Stilson',
                'aka' => 'Juozas Stilsonas',
                'gender' => 'Male',
                'race' => 'White',
                'era' => '1910s',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party of America'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Joseph Stilson (Lithuanian: Juozas Stilsonas) was a Lithuanian-American radical who served as Translator-Secretary of the Lithuanian Socialist Federation and, after the 1919 split, of its Communist successor, becoming the highest-ranking Lithuanian-American on the Communist Party of America's Central Executive Committee. During World War I he was prosecuted under the Espionage Act for an anti-conscription campaign waged through the Lithuanian-language newspaper Kova (\"Struggle\") and circulars such as \"Let Us Not Go to the Army.\" Convicted in federal court in Pennsylvania, he was sentenced to three years' imprisonment, and the U.S. Supreme Court affirmed his conviction in Stilson v. United States, 250 U.S. 583, on November 10, 1919. He chaired the credentials committee at the founding CPA convention in Chicago in September 1919 and was named \"under indictment\" in an October 1919 Bureau of Investigation surveillance report of party headquarters.",
                'cases' => [
                    [
                        'charges' => 'Conspiracy to violate the Espionage Act of 1917 (anti-conscription); conspiracy to violate the Selective Service Act of 1917',
                        'convicted' => 'Yes — conviction affirmed by the U.S. Supreme Court (Stilson v. United States, 250 U.S. 583, Nov. 10, 1919)',
                        'sentence' => "Three years' imprisonment",
                    ],
                ],
            ],
            [
                'name' => 'Dennis Batt',
                'first_name' => 'Dennis',
                'middle_name' => 'Elihu',
                'last_name' => 'Batt',
                'gender' => 'Male',
                'race' => 'White',
                'birthdate' => '1886-05-02',
                'death_date' => '1941-01-20',
                'state' => 'Michigan',
                'era' => '1910s',
                'ideologies' => ['Communism', 'Marxism'],
                'affiliation' => ['Communist Party of America', 'Proletarian Party of America'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Dennis Elihu Batt (1886–1941) was a Detroit tool-and-die maker turned Marxist journalist who became a leading figure of the left wing of the Socialist Party of Michigan. In 1919 he was named national secretary of the Organizing Committee of the Communist Party of America and was the first editor of its weekly organ, The Communist. On September 2, 1919, he was arrested on the floor of the CPA's founding convention in Chicago and held under Illinois's newly enacted state Sedition Act because of his editorship of that paper; the disposition of the charge is undocumented. In early 1920 Batt and the Michigan group broke from the CPA and founded the Proletarian Party of America; he later became a mainstream Detroit labor-movement functionary before his death in 1941.",
                'cases' => [
                    [
                        'charges' => 'Violation of the Illinois Sedition Act (1919), as editor of the CPA organ The Communist',
                        'arrest_date' => '1919-09-02',
                        'convicted' => 'Unknown — disposition of the 1919 sedition charge is undocumented',
                    ],
                ],
            ],
            [
                'name' => 'Alexander Stoklitsky',
                'first_name' => 'Alexander',
                'last_name' => 'Stoklitsky',
                'aka' => 'Aleksandr Stoklitskii',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Illinois',
                'era' => '1910s',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party of America'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Alexander Stoklitsky (also Aleksandr Stoklitskii) was a Russian-American radical who served as Translator-Secretary of the Russian Socialist Federation and became one of the most prominent leaders of the Communist Party of America's disciplined Russian Federation at its 1919 founding. During the post-WWI Red Scare he became the subject of a federal deportation case \"pending in Washington,\" which both a Bureau of Investigation surveillance report and the CPA's own application to the Communist International noted in late 1919. In mid-1920 the party sent him with Louis Fraina to Moscow as its delegate to the Second Congress of the Communist International, and he appears not to have returned to the United States; the outcome of his deportation case is undocumented. His birth and death dates are not recorded in available historical sources.",
                'cases' => [
                    [
                        'charges' => "Federal deportation proceedings (alien-radical / Communist Party membership grounds) for leadership of the Communist Party of America's Russian Federation",
                        'convicted' => 'Deportation case pending in 1919; left the United States for Soviet Russia as a Communist International delegate in 1920 (outcome undocumented)',
                    ],
                ],
            ],
        ];
    }
}
