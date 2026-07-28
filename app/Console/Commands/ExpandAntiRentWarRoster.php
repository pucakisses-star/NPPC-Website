<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The Anti-Rent War state-prison group, 1845-1847 — the fifteen men missing
 * from the reconstructed nineteen, plus upgrades to the four already present.
 *
 * (archive:add-anti-rent-war-prisoners created the original four: Boughton,
 * Van Steenburgh, O'Connor, Earle. This command completes the group and fixes
 * what those four records lacked — their cases carried no incarceration or
 * release dates, so all four imprisonment counters sat at zero.)
 *
 * All nineteen get the "Anti-Rent War" affiliation so the group is connected.
 * New records are created at sort_order 0; prisoners:place-zero-sort-by-year
 * clusters them beside the four, currently at sorts 7419-7422.
 *
 * CUSTODY DATES
 *   - Steele-sale defendants: arrest and incarceration August 1845 at MONTH
 *     precision. Custody from the August sweep is documented: by August 27
 *     more than fifty prisoners were confined in Delaware County, and by
 *     September 2 the jail had overflowed into the courtroom and jury rooms.
 *   - McCumber (a separate Anti-Rent confrontation) and the anti-disguise
 *     three (indicted April 3, 1845): 1845 at YEAR precision — their custody
 *     start is not documented more closely.
 *   - Release: Governor Young's mass Anti-Rent pardon of January 27, 1847 on
 *     every record, with a stated caveat: one account says eighteen prisoners
 *     remained in the state prisons at the pardon, so one of the nineteen had
 *     already left custody, and the surviving lists do not agree on which.
 *
 * NO INSTITUTION is asserted for the new records: some accounts route the
 * Delaware County men through Sing Sing first, later accounts place at least
 * Boughton and Earle at Clinton, and transfers likely explain the
 * disagreement. The existing four keep the Clinton institution they carry.
 *
 * EZEKIEL C. KELLEY IS DELIBERATELY EXCLUDED: he pleaded guilty under the
 * anti-disguise law but was fined $250 and never sent to state prison. A fine
 * is not custody (the Thomas Garrett rule).
 *
 * Idempotent: creates-or-updates by name. Run:
 *   php artisan prisoners:expand-anti-rent-war
 *   php artisan prisoners:place-zero-sort-by-year --apply
 */
final class ExpandAntiRentWarRoster extends Command
{
    protected $signature = 'prisoners:expand-anti-rent-war';

    protected $description = 'Add the 15 missing Anti-Rent War state prisoners and upgrade the existing four';

    private const PARDON_NOTE = 'Freed by Governor John Young\'s mass Anti-Rent pardon of January 27, 1847. One account says eighteen Anti-Rent prisoners were still in the state prisons at that point, so one of the nineteen recorded here had already left custody — the surviving lists do not agree on which — and the pardon date recorded as the release may overstate one man\'s term.';

    private const WHERE_HELD = 'Accounts differ on where the Anti-Rent prisoners were held: some say the Delaware County men went first to Sing Sing, while later accounts place at least Boughton and Earle at Clinton Prison before the pardon, transfers likely explaining the disagreement — so no institution is asserted here.';

    private const STEELE_SCENE = 'the August 7, 1845 killing of Undersheriff Osman N. Steele at the Moses Earle farm in Andes, Delaware County, New York, where a rent-distress sale on a patroon-system leasehold drew hundreds of disguised "Calico Indian" Anti-Rent militants';

    public function handle(): int
    {
        $created = 0;
        $updated = 0;

        DB::transaction(function () use (&$created, &$updated) {
            foreach ($this->roster() as $person) {
                $p = Prisoner::withoutGlobalScopes()
                    ->whereRaw('LOWER(name) = ?', [strtolower($person['name'])])
                    ->first();

                $isNew = ! $p;
                if ($isNew) {
                    $p = new Prisoner(['name' => $person['name']]);
                }

                $p->first_name = $person['first'];
                $p->middle_name = $person['middle'] ?? null;
                $p->last_name = $person['last'];
                if (! empty($person['aka'])) {
                    $p->aka = $person['aka'];
                }
                $p->gender = 'Male';
                $p->state = 'New York';
                $p->era = '1800s';
                $p->ideologies = ['Tenant Rights'];
                $affs = is_array($p->affiliation) ? $p->affiliation : [];
                if (! in_array('Anti-Rent War', $affs, true)) {
                    $affs[] = 'Anti-Rent War';
                }
                $p->affiliation = array_values($affs);
                $p->in_custody = false;
                $p->awaiting_trial = false;
                $p->released = true;
                $p->description = $person['bio'];
                $p->save();

                $case = $p->cases()->first() ?? $p->cases()->create([]);
                $case->charges = $person['charges'];
                if (! empty($person['indicted'])) {
                    $case->indicted = $person['indicted'];
                }
                $case->plead = $person['plead'] ?? null;
                $case->convicted = $person['convicted'];
                $case->sentence = $person['sentence'].' '.self::PARDON_NOTE.' '.self::WHERE_HELD;
                $case->setPartialDate('arrest_date', ...$person['arrest']);
                $case->setPartialDate('incarceration_date', ...$person['incarceration']);
                $case->setPartialDate('release_date', 1847, 1, 27);
                $case->save();

                $isNew ? $created++ : $updated++;
                $this->line(($isNew ? 'created  ' : 'updated  ').str_pad($p->slug, 24)
                    .' days='.($case->imprisoned_for_days ?? 'null'));
            }

            $this->upgradeExistingFour();
        });

        Cache::forget(PrisonerApiController::cacheKey());

        $this->newLine();
        $this->info("Done. Created {$created}, updated {$updated}, plus the four existing records upgraded.");
        $this->line('Ezekiel C. Kelley is deliberately absent: fined $250 under the anti-disguise law, never imprisoned.');
        $this->line('Now place the new records: php artisan prisoners:place-zero-sort-by-year --apply');

        return self::SUCCESS;
    }

    /** The four already in the database: connect, date, and correct them. */
    private function upgradeExistingFour(): void
    {
        $this->newLine();
        $this->line('--- upgrading the four existing records ---');

        foreach (['smith-a-boughton', 'john-van-steenburgh', 'edward-oconnor', 'moses-earle'] as $slug) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  missing: {$slug} — skipped");
                continue;
            }

            $affs = is_array($p->affiliation) ? $p->affiliation : [];
            if (! in_array('Anti-Rent War', $affs, true)) {
                $affs[] = 'Anti-Rent War';
                $p->affiliation = array_values($affs);
            }
            $p->save();

            $case = $p->cases()->first();
            if (! $case) {
                $this->warn("  {$slug}: no case row — dates skipped");
                continue;
            }

            // The three Steele defendants were held from the August 7 arrest
            // on capital charges, with the county jail overflowing; Boughton
            // was in custody from his September 1845 conviction at the latest
            // (arrested December 1844, but custody between the hung jury and
            // the retrial is not established here).
            if ($slug === 'smith-a-boughton') {
                $case->setPartialDate('incarceration_date', 1845, 9);
            } else {
                $case->setPartialDate('incarceration_date', 1845, 8, 7);
            }
            $case->setPartialDate('release_date', 1847, 1, 27);

            if ($slug === 'edward-oconnor') {
                $case->convicted = 'Yes — convicted of murder in October 1845 at Delhi, NY (Judge Amasa J. Parker), although the prosecution did not prove that he fired the fatal shot.';
                $case->sentence = 'Sentenced to hang on November 29, 1845. Governor Silas Wright commuted the sentence to life imprisonment on November 22, 1845, a week before the scheduled execution, after intense Anti-Rent organizing; Governor John Young pardoned him in January 1847 with the other Anti-Rent prisoners.';
            }
            if ($slug === 'john-van-steenburgh') {
                $case->convicted = 'Yes — convicted of murder in October 1845 at Delhi, NY (Judge Amasa J. Parker), although the prosecution did not prove that he fired the fatal shot.';
            }
            if ($slug === 'moses-earle') {
                // The dossier corrects the disposition: a guilty plea, not a verdict.
                $case->plead = 'Guilty — to first-degree manslaughter, having been indicted for murder';
                $case->convicted = 'Yes — by guilty plea to first-degree manslaughter at the Delhi sessions, 1845.';
                $case->sentence = 'Life imprisonment on the first-degree manslaughter plea; pardoned by Governor John Young in January 1847.';
            }
            $case->save();

            $this->line('  '.str_pad($slug, 24).' days='.($case->imprisoned_for_days ?? 'null'));
        }
    }

    private function roster(): array
    {
        $lifePlea = fn (string $name) => [
            'bio' => $name.' was a Delaware County, New York tenant farmer imprisoned after '.self::STEELE_SCENE.'. Indicted for murder over the confrontation, he pleaded guilty to first-degree manslaughter and received a life sentence, and was freed by Governor John Young\'s mass Anti-Rent pardon on January 27, 1847.',
            'charges' => 'Murder of Undersheriff Osman N. Steele (Andes, Delaware County, August 7, 1845) — resolved by a guilty plea to first-degree manslaughter.',
            'plead' => 'Guilty — to first-degree manslaughter, having been indicted for murder',
            'convicted' => 'Yes — by guilty plea to first-degree manslaughter at the Delhi sessions, 1845.',
            'sentence' => 'Life imprisonment on the first-degree manslaughter plea.',
            'arrest' => [1845, 8],
            'incarceration' => [1845, 8],
        ];

        $sevenPlea = fn (string $name) => [
            'bio' => $name.' was a Delaware County, New York Anti-Renter imprisoned after '.self::STEELE_SCENE.'. He pleaded guilty to first-degree manslaughter and received seven years in state prison, and was freed by Governor John Young\'s mass Anti-Rent pardon on January 27, 1847.',
            'charges' => 'First-degree manslaughter — the Andes rent-distress confrontation and the killing of Undersheriff Osman N. Steele, August 7, 1845.',
            'plead' => 'Guilty — to first-degree manslaughter',
            'convicted' => 'Yes — by guilty plea to first-degree manslaughter at the Delhi sessions, 1845.',
            'sentence' => 'Seven years in state prison.',
            'arrest' => [1845, 8],
            'incarceration' => [1845, 8],
        ];

        $disguise = fn (string $name, string $others) => [
            'bio' => $name.' was one of the first Anti-Rent prisoners, prosecuted under New York\'s new law against appearing armed and disguised — the statute aimed at the "Calico Indians" — before the killing of Undersheriff Steele. Indicted on April 3, 1845 with '.$others.', he was sentenced to two years in state prison and freed by Governor John Young\'s mass Anti-Rent pardon on January 27, 1847. A fourth co-defendant, Ezekiel C. Kelley, pleaded guilty but was fined $250 rather than imprisoned, and for that reason has no entry in this database.',
            'charges' => 'Appearing armed and disguised, under the New York anti-disguise law of 1845 aimed at the Anti-Rent "Calico Indians".',
            'indicted' => 'April 3, 1845',
            'convicted' => 'Yes — under the anti-disguise law, 1845.',
            'sentence' => 'Two years in state prison.',
            'arrest' => [1845],
            'incarceration' => [1845],
        ];

        return [
            array_merge(['name' => 'Daniel W. Squires', 'first' => 'Daniel', 'middle' => 'W.', 'last' => 'Squires'], $lifePlea('Daniel W. Squires')),
            array_merge(['name' => 'Zera Preston', 'first' => 'Zera', 'last' => 'Preston'], $lifePlea('Zera Preston')),
            array_merge(['name' => 'Daniel Northrup', 'first' => 'Daniel', 'last' => 'Northrup'], $lifePlea('Daniel Northrup')),

            array_merge(['name' => 'John Phoenix', 'first' => 'John', 'last' => 'Phoenix'], $sevenPlea('John Phoenix')),
            array_merge(['name' => 'John Burtch', 'first' => 'John', 'last' => 'Burtch', 'aka' => 'John Burch'], $sevenPlea('John Burtch')),
            array_merge(['name' => 'John Lathan', 'first' => 'John', 'last' => 'Lathan', 'aka' => 'John Latham'], $sevenPlea('John Lathan')),
            array_merge(['name' => 'William Reside', 'first' => 'William', 'last' => 'Reside'], $sevenPlea('William Reside')),
            array_merge(['name' => 'Isaac L. Burhans', 'first' => 'Isaac', 'middle' => 'L.', 'last' => 'Burhans'], $sevenPlea('Isaac L. Burhans')),

            [
                'name' => 'Calvin Madison', 'first' => 'Calvin', 'last' => 'Madison', 'aka' => 'Caleb Madison',
                'bio' => 'Calvin Madison — sometimes recorded as Caleb Madison — was a Delaware County, New York Anti-Renter imprisoned after '.self::STEELE_SCENE.'. He pleaded guilty to first-degree manslaughter and received ten years in state prison, and was freed by Governor John Young\'s mass Anti-Rent pardon on January 27, 1847.',
                'charges' => 'First-degree manslaughter — the Andes rent-distress confrontation and the killing of Undersheriff Osman N. Steele, August 7, 1845.',
                'plead' => 'Guilty — to first-degree manslaughter',
                'convicted' => 'Yes — by guilty plea at the Delhi sessions, 1845.',
                'sentence' => 'Ten years in state prison.',
                'arrest' => [1845, 8],
                'incarceration' => [1845, 8],
            ],
            [
                'name' => 'William Brisbane', 'first' => 'William', 'last' => 'Brisbane',
                'bio' => 'William Brisbane was an Anti-Rent lecturer imprisoned after '.self::STEELE_SCENE.'. Unlike the disguised militants he attended the Earle sale openly, and was convicted of second-degree manslaughter and sentenced to seven years in state prison. He was freed by Governor John Young\'s mass Anti-Rent pardon on January 27, 1847.',
                'charges' => 'Second-degree manslaughter — the Andes rent-distress confrontation and the killing of Undersheriff Osman N. Steele, August 7, 1845. He attended the sale openly rather than in disguise.',
                'convicted' => 'Yes — of second-degree manslaughter at the Delhi sessions, 1845.',
                'sentence' => 'Seven years in state prison.',
                'arrest' => [1845, 8],
                'incarceration' => [1845, 8],
            ],
            [
                'name' => 'Charles T. McCumber', 'first' => 'Charles', 'middle' => 'T.', 'last' => 'McCumber',
                'bio' => 'Charles T. McCumber was a New York Anti-Renter convicted of second-degree robbery and sentenced to seven years in state prison; his case apparently arose from a separate Anti-Rent confrontation rather than the killing of Undersheriff Steele. He was freed by Governor John Young\'s mass Anti-Rent pardon on January 27, 1847.',
                'charges' => 'Second-degree robbery — an Anti-Rent confrontation apparently separate from the Steele killing.',
                'convicted' => 'Yes — of second-degree robbery, 1845.',
                'sentence' => 'Seven years in state prison.',
                'arrest' => [1845],
                'incarceration' => [1845],
            ],
            [
                'name' => 'William Joscelyn', 'first' => 'William', 'last' => 'Joscelyn', 'aka' => 'William Jocelyn',
                'bio' => 'William Joscelyn — the surname is also spelled Jocelyn — was a Delaware County, New York Anti-Renter imprisoned after '.self::STEELE_SCENE.'. Convicted of fourth-degree manslaughter, he received two years in state prison and was freed by Governor John Young\'s mass Anti-Rent pardon on January 27, 1847.',
                'charges' => 'Fourth-degree manslaughter — the Andes rent-distress confrontation and the killing of Undersheriff Osman N. Steele, August 7, 1845.',
                'convicted' => 'Yes — of fourth-degree manslaughter at the Delhi sessions, 1845.',
                'sentence' => 'Two years in state prison.',
                'arrest' => [1845, 8],
                'incarceration' => [1845, 8],
            ],

            array_merge(['name' => 'Anson K. Burrill', 'first' => 'Anson', 'middle' => 'K.', 'last' => 'Burrill', 'aka' => 'Anson K. Burrell'], $disguise('Anson K. Burrill', 'Lewis Knapp and Silas Tompkins')),
            array_merge(['name' => 'Lewis Knapp', 'first' => 'Lewis', 'last' => 'Knapp'], $disguise('Lewis Knapp', 'Anson K. Burrill and Silas Tompkins')),
            array_merge(['name' => 'Silas Tompkins', 'first' => 'Silas', 'last' => 'Tompkins'], $disguise('Silas Tompkins', 'Anson K. Burrill and Lewis Knapp')),
        ];
    }
}
