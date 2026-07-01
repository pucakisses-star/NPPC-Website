<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Applies the Wave 2–3 (2010s + 2000s cohorts, 215 people) findings from the
 * release-date research into the "released, no release date" records.
 *
 *   1) REMOVE (44) — never actually imprisoned (booked and released / bailed
 *      out / probation-only / acquitted / dismissed), plus 2 never in U.S.
 *      custody (a FARC commander indicted in absentia and later killed, and an
 *      ELF fugitive never apprehended).
 *   2) FLIP (18) — still incarcerated (mostly long FARC / terrorism-support
 *      federal terms) but mislabeled released; set back to in-custody.
 *   3) DIED (4) — died in custody; set the death-in-custody date so the term
 *      computes correctly rather than showing "released".
 *   4) DATES (85) — set the researched release date (exact, or month/year
 *      precision).
 *
 * The 63 with no findable date are left for the global null-fallback (#1543).
 * Idempotent.
 */
final class ApplyWave23ReleaseResearch extends Command
{
    protected $signature = 'prisoners:apply-wave23-release-research';

    protected $description = 'Apply Wave 2–3 release-date research: remove non-prisoners, flip still-incarcerated, set death-in-custody, backfill dates';

    private const REMOVE = [
        'adriana-stumpo', 'ahmad-mustafa', 'ahmadullah-sais-niazi', 'ali-mohamed-bagegni',
        'amy-kathleen-kovac', 'andrew-sharo', 'annette-marie-klapstein', 'benjamin-gary-joldersma',
        'carlos-montes', 'connor-cash', 'daniel-kruk', 'diane-gandee-sorbi', 'emily-nesbitt-johnston',
        'ethan-merrill-petersen', 'german-briceno-suarez', 'gina-eleyna-wertz', 'gloria-merriweather',
        'hannah-kelman-zivolich', 'holden-dometrius', 'israel-l-hernandez', 'jackson-richman',
        'jacob-jeremiah-ferguson', 'james-angry-bird-white', 'jamil-salem-sarsour', 'jonathan-frohnmayer',
        'joseph-alcoff', 'josephine-sunshine-overaker', 'joshua-macrae-baker-cooper', 'kaden-cicily-fralick',
        'kenneth-ward', 'maryam-khajavi', 'michael-fasig', 'nicholas-evert-jones', 'osameh-al-wahaidy',
        'paul-picklesimer', 'randy-navarette', 'richard-anderson-jr', 'rosemarie-zoe-obrien',
        'samer-masterson', 'steven-robert-liptay', 'thomas-keenan-alcoff-2018', 'thomas-massey',
        'vanessa-castle', 'zuhair-hamed-el-shwehdi',
    ];

    private const FLIP_TO_CUSTODY = [
        'alexander-beltran-herrera', 'armando-gomez', 'carlos-gamarra-murillo', 'daniel-barrera-barrera',
        'diego-alfonso-navarrete-beltran', 'edilberto-berrio-ortiz', 'erminso-cuevas-cabrera',
        'fabio-simon-younes-arboleda', 'faouzi-jaber', 'francisco-joseph-arcila-ramirez',
        'gerardo-aguilar-ramirez', 'hafiz-muhammad-sher-ali-khan', 'ignacio-leal-garcia',
        'juan-jose-martinez-vega', 'juvenal-ovidio-ricardo-palmera-pineda', 'ramiz-zijad-hodzic',
        'thiruthanikan-thanigasalam', 'tony-alexander-hamilton',
    ];

    /** slug => death-in-custody date. */
    private const DIED_IN_CUSTODY = [
        'irving-david-rubin' => '2002-11-13',
        'imam-jamil-al-amin' => '2025-11-23',
        'kendall-myers' => '2026-03-12',
        'william-rodgers' => '2005-12-21',
    ];

    /** slug => researched release date (YYYY, YYYY-MM, or YYYY-MM-DD). */
    private const DATES = [
        'steven-james-murphy' => '2014', 'william-james-viehl' => '2010', 'emadeddin-muntasser' => '2008-12',
        'kifah-wael-jayyousi' => '2017', 'michael-sykes' => '2012', 'mohamed-shorbagi' => '2013',
        'carmen-maria-ponton-caro' => '2011', 'nadarasa-yograrasa' => '2018', 'nicolas-tapasco-romero' => '2009',
        'victor-daniel-salamanca' => '2011', 'vinh-tan-nguyen' => '2008', 'carlos-adolfo-romero-panchano' => '2006',
        'donte-smith' => '2006', 'george-mashkow' => '2005', 'jeremy-david-rosenbloom' => '2003',
        'matthew-whyte' => '2002', 'peter-gelderloos' => '2003', 'vicci-hamlin' => '2014-03-05',
        'ali-asad-chandia' => '2019-07-19', 'jesse-william-waters' => '2012-09-05',
        'mohamed-mustapha-ali-masfaka' => '2011', 'anderson-chamapuro-dogirama' => '2019',
        'jasmine-richards' => '2016-06-18', 'alex-jason-hall' => '2011-01', 'jorge-abel-ibarguen-palacio' => '2018',
        'kyle-shaw' => '2013-11-08', 'sister-mary-dennis-lentsch' => '2000-08', 'rodney-coronado' => '2011-01-14',
        'alexander-gorman-dial' => '2019', 'antonio-scott-zamora' => '2019', 'chase-a-davis' => '2021-01',
        'evan-kirk-duke' => '2019', 'mediha-medy-salkicevic' => '2020-08-21', 'mickael-gedlu' => '2020',
        'richard-joseph-klimek' => '2019', 'zachary-lange' => '2019', 'james-e-robinson' => '2024',
        'lacy-macauley' => '2018', 'van-l-mayes' => '2018-07', 'michael-rattler-markus' => '2021-01',
        'brandon-orlando-baldwin' => '2020', 'kevin-olliff' => '2016-05', 'sara-beining' => '2014-12-10',
        'yang-yoon-mo' => '2014-04-12', 'barbara-carter' => '2014-03-05', 'david-eli-baghdadi' => '2013-09-19',
        'lisa-leggio' => '2014-03-05', 'corbin-street' => '2003', 'dan-fortson' => '2003', 'dave-tarbell' => '2003',
        'don-haselfeld' => '2003-10', 'eloy-garcia' => '2003-04', 'fr-jim-hynes' => '2003-10',
        'ihsan-elashi' => '2011', 'jc-orton' => '2003', 'jason-lydon' => '2003-08', 'joyce-elwanger' => '2003-10',
        'katherine-bjorkman' => '2003', 'katherine-brown' => '2003', 'lee-mickey' => '2003',
        'lisa-hughes' => '2003-10', 'marilyn-white' => '2003-10', 'mark-warren-sands' => '2016',
        'marvin-warren' => '2003', 'mike-wisniewski' => '2003', 'mimi-lavalley' => '2003',
        'pedro-zenon-encarnacion' => '2003', 'sr-kathy-long' => '2003', 'william-slattery' => '2003-08',
        'bill-odonnell' => '2003', 'jeremiah-dean-colcleasure' => '2007', 'jose-maria-corredor-ibague' => '2022',
        'lili-marie-holland' => '2007', 'ryan-daniel-lewis' => '2010', 'harrison-david-burrows' => '2005',
        'joel-klimkewicz' => '2005', 'joshua-stephen-demmitt' => '2006', 'mohamed-el-mezain' => '2022',
        'soliman-s-biheiri' => '2005', 'ghassan-zayed-ballut' => '2005-12-06', 'hargit-singh-gill' => '2006',
        'paul-douglas-revak' => '2003-12-05', 'robert-brooks' => '2005', 'aaron-labe-linas' => '2006',
        'adam-blackwell' => '2004',
    ];

    public function handle(): int
    {
        $removed = $flipped = $died = $dated = 0;

        foreach (self::REMOVE as $slug) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->line("  remove: already gone — {$slug}");

                continue;
            }
            $p->cases()->delete();
            $p->podcastEpisodes()->delete();
            $p->calendarEntries()->delete();
            $name = $p->name;
            $p->delete();
            $this->info("  removed: {$name}");
            $removed++;
        }

        foreach (self::FLIP_TO_CUSTODY as $slug) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  flip: no prisoner '{$slug}'");

                continue;
            }
            $p->in_custody = true;
            $p->released = false;
            $p->save();
            if ($case = $p->cases()->first()) {
                $case->release_date = null;
                $case->save();
            }
            $this->info("  flipped to in-custody: {$p->name}");
            $flipped++;
        }

        foreach (self::DIED_IN_CUSTODY as $slug => $date) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  died: no prisoner '{$slug}'");

                continue;
            }
            $p->in_custody = false;
            $p->released = false;
            $p->save();
            if ($case = $p->cases()->first()) {
                // The saving hook sets release_date = death_in_custody_date and
                // computes imprisoned_for_days up to the death.
                $case->death_in_custody_date = $date;
                $case->save();
                $case->refresh();
                $this->info("  died in custody: {$p->name} ({$case->imprisoned_for_days} days)");
            }
            $died++;
        }

        foreach (self::DATES as $slug => $date) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  date: no prisoner '{$slug}'");

                continue;
            }
            $case = $p->cases()->first();
            if (! $case) {
                $this->warn("  date: {$p->name} has no case");

                continue;
            }
            [$y, $m, $d] = array_pad(array_map('intval', explode('-', $date)), 3, 0);
            $case->setPartialDate('release_date', $y, $m ?: null, $d ?: null);
            $p->released = true;
            $p->in_custody = false;
            $p->save();
            $case->save();
            $case->refresh();
            $this->info("  dated: {$p->name} → {$date} ({$case->imprisoned_for_days} days)");
            $dated++;
        }

        $this->info("\nDone. Removed {$removed}, flipped {$flipped} to in-custody, {$died} died-in-custody, dated {$dated}.");
        $this->line('The 63 no-date Wave 2–3 records are covered by the global null-fallback (#1543).');

        return self::SUCCESS;
    }
}
