<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds imprisoned National Woman's Party "Silent Sentinels" from the 1917
 * White House suffrage-picketing campaign (and the November 1917 "Night of
 * Terror" at the Occoquan Workhouse). Names already in the database — Alice
 * Cosu, Annie Arniel, Alison Turnbull Hopkins, Dora Lewis, Dorothy Day,
 * Florence Bayard Hilles, Katherine Morey, Mabel Vernon, Matilda Hall
 * Gardner — are intentionally omitted; Dorothy Day's existing record is
 * enriched with her Occoquan case instead of being duplicated.
 *
 * Following the source caution, incarceration/release dates (which drive the
 * auto-computed imprisoned_for_days) are set ONLY where an actual discharge is
 * documented; women with a stated sentence but no known release get an arrest
 * date only, so no imprisonment length is invented.
 *
 * Idempotent: each person is matched by first+last name and skipped if present.
 */
final class AddSuffragePrisoners extends Command
{
    protected $signature = 'prisoners:add-suffrage {--dry-run : List planned adds without writing}';

    protected $description = 'Add 1917 National Woman\'s Party suffrage prisoners (Silent Sentinels)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Shared base fields.
        $base = [
            'ideologies' => ['Women\'s suffrage'],
            'affiliation' => ['National Woman\'s Party', 'Silent Sentinels'],
            'era' => '1910s',
            'gender' => 'Female',
            'state' => 'District of Columbia',
            'in_custody' => false,
            'released' => true,
        ];
        $OCC = ['institution_name' => 'Occoquan Workhouse', 'institution_city' => 'Occoquan', 'institution_state' => 'Virginia'];
        $JAIL = ['institution_name' => 'District of Columbia Jail', 'institution_city' => 'Washington', 'institution_state' => 'District of Columbia'];

        $people = [];
        // $add(name, first, last, bio, caseExtra[], dates[], extra[])
        $add = function (string $name, string $first, string $last, string $bio, array $case, array $dates = [], array $extra = []) use (&$people, $base) {
            $payload = array_merge($base, $extra, [
                'name' => $name, 'first_name' => $first, 'last_name' => $last,
                'description' => $bio,
                'cases' => [$case],
            ]);
            $people[] = ['payload' => $payload, 'dates' => $dates];
        };

        // ── Major documented prisoners ───────────────────────────────────
        $add('Alice Paul', 'Alice', 'Paul',
            "Alice Paul was the founder and leader of the National Woman's Party and the principal strategist of the militant wing of the American suffrage movement. Arrested on October 20, 1917 for picketing the Wilson White House, she was sentenced on October 22 to seven months in the District of Columbia Jail. She led a hunger strike, was forcibly fed, and was confined in the jail's psychiatric ward in an attempt to discredit her. She was released about November 27-28, 1917, after roughly five weeks. She went on to author the Equal Rights Amendment.",
            array_merge($JAIL, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage; charged with obstructing traffic.',
                'convicted' => 'Convicted, October 22, 1917',
                'sentence' => 'Seven months in the District of Columbia Jail; hunger-struck, force-fed and held in the jail psychiatric ward. Released after about five weeks.',
            ]),
            ['arrest_date' => [1917, 10, 20], 'incarceration_date' => [1917, 10, 22], 'release_date' => [1917, 11, 28]]);

        $add('Rose Winslow', 'Rose', 'Winslow',
            "Rose Winslow, born Ruza Wenclawska, was a Polish-born mill worker and organizer who became one of the best-known hunger strikers of the suffrage campaign. Sentenced on October 15, 1917 to seven months in the District of Columbia Jail, she joined Alice Paul's hunger strike and was repeatedly force-fed despite poor health and tuberculosis. Her smuggled prison notes became a widely reprinted account of the force-feeding. She was released about November 27-28, 1917.",
            array_merge($JAIL, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, October 1917',
                'sentence' => 'Seven months in the District of Columbia Jail; joined the hunger strike and was repeatedly force-fed. Released after about six weeks.',
            ]),
            ['incarceration_date' => [1917, 10, 15], 'release_date' => [1917, 11, 28]],
            ['aka' => 'Ruza Wenclawska']);

        $add('Lucy Burns', 'Lucy', 'Burns',
            "Lucy Burns was co-founder of the National Woman's Party with Alice Paul and served more jail time than any other American suffragist. Arrested repeatedly through 1917-1919, she was among the thirty-one women seized on November 10, 1917. During the Night of Terror at the Occoquan Workhouse guards chained her hands above her head for the night; she was later force-fed. She was released with the other November prisoners about November 27-28, 1917.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, November 1917',
                'sentence' => 'Six months at the Occoquan Workhouse; chained by the wrists overnight during the Night of Terror and later force-fed. Released after about two weeks.',
            ]),
            ['arrest_date' => [1917, 11, 10], 'incarceration_date' => [1917, 11, 14], 'release_date' => [1917, 11, 28]]);

        $add('Mary A. Nolan', 'Mary', 'Nolan',
            "Mary A. Nolan, a 73-year-old suffragist from Florida, was the oldest prisoner in the November 1917 group. Arrested on November 10 and sentenced to six days, she was committed to the Occoquan Workhouse on November 14, where guards threw her into a cell during the Night of Terror. She completed the six-day term and was released about November 20, 1917.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, November 1917',
                'sentence' => 'Six days at the Occoquan Workhouse; thrown into a cell during the Night of Terror.',
            ]),
            ['arrest_date' => [1917, 11, 10], 'incarceration_date' => [1917, 11, 14], 'release_date' => [1917, 11, 20]]);

        $add('Julia Emory', 'Julia', 'Emory',
            "Julia Emory was a Maryland Silent Sentinel arrested on November 10, 1917 and sentenced to thirty days. She was assaulted by Occoquan superintendent Raymond Whittaker during the Night of Terror. She was released with the other November prisoners about November 27-28, and served further short terms in August 1918 and January 1919.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, November 1917',
                'sentence' => 'Thirty days at the Occoquan Workhouse; assaulted by the superintendent during the Night of Terror.',
            ]),
            ['arrest_date' => [1917, 11, 10], 'incarceration_date' => [1917, 11, 14], 'release_date' => [1917, 11, 28]]);

        $add('Lavinia Lloyd Dock', 'Lavinia', 'Dock',
            "Lavinia Lloyd Dock was a nationally prominent nurse and public-health reformer and one of the first six suffragists imprisoned in the White House campaign. She served three days in the District of Columbia Jail in June 1917 and further short terms later that summer and in November 1917.",
            array_merge($JAIL, [
                'charges' => 'Arrested among the first six picketers of the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, June 1917',
                'sentence' => 'Three days in the District of Columbia Jail (June 1917); additional short terms later in 1917.',
            ]),
            ['arrest_date' => [1917, 6, 27], 'incarceration_date' => [1917, 6, 27], 'release_date' => [1917, 6, 30]]);

        $add('Maud Jamison', 'Maud', 'Jamison',
            "Maud Jamison was a Virginia Silent Sentinel and one of the movement's longest-serving repeat prisoners. She was among the first six picketers jailed for three days in June 1917; sentenced in October 1917 to seven months but released after forty-four days; and served a further five-day term in January 1919.",
            array_merge($JAIL, [
                'charges' => 'Arrested among the first six picketers of the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, June 1917',
                'sentence' => 'Three days in the District of Columbia Jail (June 1917); later a seven-month sentence served in part (released after 44 days).',
            ]),
            ['arrest_date' => [1917, 6, 27], 'incarceration_date' => [1917, 6, 27], 'release_date' => [1917, 6, 30]]);

        $add('Edith Ainge', 'Edith', 'Ainge',
            "Edith Ainge was a New York Silent Sentinel who served five terms for suffrage picketing, beginning with sixty days at the Occoquan Workhouse in September 1917, followed by fifteen days in August 1918 and three short District Jail terms in January 1919.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, September 1917',
                'sentence' => 'Sixty days at the Occoquan Workhouse (September 1917); further terms in 1918-1919.',
            ]),
            ['arrest_date' => [1917, 9, null]]);

        $add('Pauline Adams', 'Pauline', 'Adams',
            "Pauline Adams was a Norfolk, Virginia suffragist and attorney arrested on September 4, 1917 and sentenced to sixty days at the Occoquan Workhouse. She was arrested again at a February 1919 watchfire demonstration but released for lack of evidence.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, September 1917',
                'sentence' => 'Sixty days at the Occoquan Workhouse.',
            ]),
            ['arrest_date' => [1917, 9, 4]]);

        $add('Maud Malone', 'Maud', 'Malone',
            "Maud Malone was a New York librarian and longtime suffragist arrested on September 4, 1917 and sentenced to sixty days at the Occoquan Workhouse.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, September 1917',
                'sentence' => 'Sixty days at the Occoquan Workhouse.',
            ]),
            ['arrest_date' => [1917, 9, 4]]);

        $add('Helena Hill Weed', 'Helena', 'Weed',
            "Helena Hill Weed was a geologist and former national officer of the Daughters of the American Revolution. Arrested on July 4, 1917, she served three days for carrying a banner quoting the Declaration of Independence; she served twenty-four hours for applauding prisoners in court and, after an August 1918 Lafayette Square protest, fifteen days.",
            array_merge($JAIL, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage, carrying a banner quoting the Declaration of Independence.',
                'convicted' => 'Convicted, July 1917',
                'sentence' => 'Three days in the District of Columbia Jail (July 1917); later short terms in 1918.',
            ]),
            ['arrest_date' => [1917, 7, 4], 'incarceration_date' => [1917, 7, 4], 'release_date' => [1917, 7, 7]]);

        $add('Doris Stevens', 'Doris', 'Stevens',
            "Doris Stevens was a National Woman's Party organizer and author of Jailed for Freedom (1920), the principal firsthand roster of the suffrage prisoners. Arrested on July 14, 1917, she received a nominal sixty-day Occoquan sentence but was pardoned by President Wilson after three days. She was arrested again in New York in March 1919 but not imprisoned in that case.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, July 14, 1917',
                'sentence' => 'Nominal sixty days at the Occoquan Workhouse; pardoned by President Wilson after three days.',
            ]),
            ['arrest_date' => [1917, 7, 14], 'incarceration_date' => [1917, 7, 14], 'release_date' => [1917, 7, 17]]);

        // ── First six (June 27, 1917) not already in the database ────────
        $add('Virginia Arnold', 'Virginia', 'Arnold',
            "Virginia Arnold was a North Carolina teacher, National Woman's Party organizer and one of the first six suffragists imprisoned in the White House campaign, serving three days in the District of Columbia Jail in June 1917. She was a frequent banner-bearer, including the 'Kaiser Wilson' banner of August 1917.",
            array_merge($JAIL, [
                'charges' => 'Arrested among the first six picketers of the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, June 1917',
                'sentence' => 'Three days in the District of Columbia Jail.',
            ]),
            ['arrest_date' => [1917, 6, 27], 'incarceration_date' => [1917, 6, 27], 'release_date' => [1917, 6, 30]]);

        // ── July 14, 1917 group — pardoned after three days ──────────────
        $july14 = [
            ['Julia Hurlbut', 'Julia', 'Hurlbut', 'a New Jersey National Woman\'s Party organizer'],
            ['Mary Ingham', 'Mary', 'Ingham', 'a Philadelphia suffragist'],
            ['Beatrice Kinkead', 'Beatrice', 'Kinkead', 'a New Jersey suffragist'],
            ['Betsy Graves Reyneau', 'Betsy', 'Reyneau', 'a Michigan-born portrait painter'],
            ['Elizabeth Selden Rogers', 'Elizabeth', 'Rogers', 'a New York suffragist and sister-in-law of a former U.S. Secretary of State'],
            ['Mary Walker', 'Mary', 'Walker', 'a National Woman\'s Party picketer'],
            ['Minnie Abbott', 'Minnie', 'Abbott', 'a New Jersey Silent Sentinel'],
        ];
        foreach ($july14 as [$name, $first, $last, $who]) {
            $add($name, $first, $last,
                "{$name} was {$who} arrested on July 14, 1917 for picketing the Wilson White House for woman suffrage. She received a nominal sixty-day sentence at the Occoquan Workhouse and was pardoned by President Wilson after three days.",
                array_merge($OCC, [
                    'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                    'convicted' => 'Convicted, July 14, 1917',
                    'sentence' => 'Nominal sixty days at the Occoquan Workhouse; pardoned after three days.',
                ]),
                ['arrest_date' => [1917, 7, 14], 'incarceration_date' => [1917, 7, 14], 'release_date' => [1917, 7, 17]]);
        }

        $add('Eunice Dana Brannan', 'Eunice', 'Brannan',
            "Eunice Dana Brannan was a New York Silent Sentinel and daughter of newspaper editor Charles A. Dana. Arrested with the July 14, 1917 group, she was pardoned after three days but returned to prison following a later arrest during the 1917 campaign.",
            array_merge($OCC, [
                'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                'convicted' => 'Convicted, July 14, 1917',
                'sentence' => 'Nominal sixty days at the Occoquan Workhouse; pardoned after three days, later re-imprisoned.',
            ]),
            ['arrest_date' => [1917, 7, 14], 'incarceration_date' => [1917, 7, 14], 'release_date' => [1917, 7, 17]]);

        // ── Additional confirmed roster (LOC Gallery of Suffrage Prisoners)
        // General, accurate biographies; no incarceration/release dates are
        // asserted where the individual term is not documented, so no
        // imprisonment length is invented. A distinguishing clause is added
        // only where well established.
        $roster = [
            ['Lillian Ascough', 'Lillian', 'Ascough', ''],
            ['Hilda Blumberg', 'Hilda', 'Blumberg', ''],
            ['Lucy Gwynne Branham', 'Lucy', 'Branham', ' She was a noted hunger striker and later a touring speaker on the "Prison Special".'],
            ['Louise Bryant', 'Louise', 'Bryant', ' She was a radical journalist, later a correspondent on the Russian Revolution.'],
            ['Gertrude Crocker', 'Gertrude', 'Crocker', ''],
            ['Ruth Crocker', 'Ruth', 'Crocker', ''],
            ['Alice Gram', 'Alice', 'Gram', ''],
            ['Betty Gram', 'Betty', 'Gram', ''],
            ['Gladys Greiner', 'Gladys', 'Greiner', ''],
            ['Anna Gwinter', 'Anna', 'Gwinter', ''],
            ['Hattie Kruger', 'Hattie', 'Kruger', ' A Buffalo, New York suffragist.'],
            ['Anna Kuhn', 'Anna', 'Kuhn', ''],
            ['Katherine Lincoln', 'Katherine', 'Lincoln', ''],
            ['Mary Winsor', 'Mary', 'Winsor', ' A Pennsylvania suffragist and writer.'],
            ['Cora Week', 'Cora', 'Week', ''],
            ['Camilla Whitcomb', 'Camilla', 'Whitcomb', ' A Worcester, Massachusetts suffragist.'],
            ['Anna Kelton Wiley', 'Anna', 'Wiley', ' Wife of the chemist and pure-food reformer Dr. Harvey W. Wiley.'],
            ['Margaret Whittemore', 'Margaret', 'Whittemore', ' A National Woman\'s Party organizer.'],
            ['Cora Crawford', 'Cora', 'Crawford', ''],
            ['Mary Dubrow', 'Mary', 'Dubrow', ' A New Jersey organizer.'],
            ['Alice Kimball', 'Alice', 'Kimball', ''],
            ['Elizabeth McShane', 'Elizabeth', 'McShane', ' A hunger striker.'],
            ['Sue Shelton White', 'Sue', 'White', ' A Tennessee lawyer and editor of The Suffragist, jailed in 1919 for a watchfire demonstration.'],
            ['Bertha Wallerstein', 'Bertha', 'Wallerstein', ''],
        ];
        foreach ($roster as [$name, $first, $last, $tail]) {
            $add($name, $first, $last,
                "{$name} was a member of the National Woman's Party and one of the \"Silent Sentinels\" imprisoned during the 1917-1919 campaign to picket the Wilson White House for woman suffrage. Held at the Occoquan Workhouse in Virginia or the District of Columbia Jail, many of these prisoners joined hunger strikes and were force-fed.{$tail}",
                [
                    'charges' => 'Arrested for picketing the Wilson White House for woman suffrage.',
                    'convicted' => 'Imprisoned during the 1917-1919 suffrage picketing campaign',
                    'sentence' => 'Jailed during the National Woman\'s Party White House picketing campaign (individual term not fully documented).',
                ]);
        }

        // ── INSERT ───────────────────────────────────────────────────────
        $added = 0;
        $skipped = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  skip (already present as \"{$existing->name}\"): {$payload['name']}");
                $skipped++;
                continue;
            }

            $this->line(($dry ? '  would add: ' : '  add: ').$payload['name']);
            if ($dry) {
                $added++;
                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        // ── Enrich Dorothy Day (already in DB as a Catholic Worker) ──────
        if (! $dry) {
            $day = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%Dorothy%')->where('name', 'like', '%Day%')->first();
            if ($day) {
                $aff = is_array($day->affiliation) ? $day->affiliation : [];
                if (! in_array('National Woman\'s Party', $aff, true)) {
                    $day->affiliation = array_values(array_unique(array_merge($aff, ['National Woman\'s Party', 'Silent Sentinels'])));
                    $day->save();
                    $this->line('  enriched Dorothy Day affiliation with National Woman\'s Party.');
                }
                $hasSuffrageCase = $day->cases()->where('charges', 'like', '%suffrage%')->exists();
                if (! $hasSuffrageCase) {
                    $case = $day->cases()->create([
                        'charges' => 'Arrested November 10, 1917 for picketing the Wilson White House for woman suffrage.',
                        'convicted' => 'Convicted, November 14, 1917',
                        'sentence' => 'Thirty days at the Occoquan Workhouse; thrown over an iron bench during the Night of Terror. Released about November 27-28, 1917.',
                    ]);
                    $case->setPartialDate('arrest_date', 1917, 11, 10);
                    $case->setPartialDate('incarceration_date', 1917, 11, 14);
                    $case->setPartialDate('release_date', 1917, 11, 28);
                    $case->save();
                    $this->line('  added 1917 Occoquan suffrage case to Dorothy Day.');
                }
            }
        }

        \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$added} would be added, {$skipped} already present.");
        } else {
            $this->info("Done. Added {$added}, skipped {$skipped} already present.");
        }

        return self::SUCCESS;
    }
}
