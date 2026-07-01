<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Applies the Wave 1 (2020s cohort) findings from the release-date research into
 * the 117 "released, no release date" records whose time-served was inflated.
 *
 * Three actions:
 *   1) REMOVE (32) — people who were never actually imprisoned: booked and
 *      released the same day / bailed out, or (4 anomalies) never in U.S.
 *      custody at all (Hamas leaders charged in absentia and killed; a student
 *      whose ICE arrest a judge blocked). Consistent with the site's standard
 *      of only listing people who were actually imprisoned.
 *   2) FLIP (4) — mislabeled "released" but actually still incarcerated; set
 *      back to in-custody so they stop showing a (wrong) release status.
 *   3) DATES (59) — set the researched release date (exact, or month/year
 *      precision) so imprisoned_for_days computes correctly.
 *
 * The 22 Wave 1 records with no findable release date are left for the global
 * null-fallback fix. Idempotent.
 */
final class ApplyWave1ReleaseResearch extends Command
{
    protected $signature = 'prisoners:apply-wave1-release-research';

    protected $description = 'Apply Wave 1 release-date research: remove non-prisoners, flip still-incarcerated, backfill dates';

    /** Never actually imprisoned (same-day/bailed-out) or never in U.S. custody — remove. */
    private const REMOVE = [
        'sayed-a-quraishi', 'trevor-h-carter', 'victor-h-smith', 'yafa-k-issa', 'yahya-sinwar',
        'ian-dinkla', 'krystal-dipippa', 'whitney-m-durant', 'anna-n-kochakian', 'edmee-chavannes',
        'davis-loren-nafshun', 'joseph-m-kleckner', 'lucas-jensen-griffith', 'madeline-rose-fening',
        'yunseo-chung', 'brian-okum', 'elizabeth-a-sotiropoulos', 'eyal-shalom', 'joel-atkinson',
        'julia-lankisch', 'malachi-joshua-marlan-librett', 'marwan-issa', 'melissa-brunn',
        'mohammad-al-masri', 'samuel-holman-smith', 'sophia-dempsey', 'darrell-anthony-kimberlin',
        'davis-alan-beeman', 'kai-ave-james-douvia', 'kyle-martin-romstad', 'maximilian-jennings',
        'theodore-adrian-matthee-o-brien',
    ];

    /** Actually still incarcerated — the "released" flag is wrong. */
    private const FLIP_TO_CUSTODY = [
        'jacob-d-little', 'brian-cortez-lightfoot-jr', 'jeremy-white', 'daniel-jongyon-park',
    ];

    /** slug => researched release date (YYYY, YYYY-MM, or YYYY-MM-DD). */
    private const DATES = [
        'andrew-steven-faulkner' => '2020-07', 'brian-jordan-bartels' => '2020-06',
        'brittany-dawn-jeffrey' => '2022-12-06', 'bruce-thompson' => '2021-08',
        'corey-smith' => '2022-12', 'dashun-martin' => '2021-11',
        'edward-william-carubis' => '2020-07-14', 'errick-steven-toa' => '2020-07',
        'george-allen' => '2023-01', 'jhajuan-sabb' => '2021-03', 'loren-reed' => '2021-07',
        'mackenzie-drechsler' => '2021-08', 'miguel-ramos' => '2022',
        'rene-christopher-bracamonte' => '2021-05', 'ryan-david-lucero' => '2021-06-15',
        'sedina-unkic-hodzic' => '2024', 'tearra-naasia-guthrie' => '2020',
        'timothy-hummel' => '2020-03', 'alexander-akridgejacobs' => '2024-11',
        'bryan-rivera' => '2024-09', 'christian-martinez' => '2024-09',
        'faraz-martin-talab' => '2024-12-26', 'ruchelle-ogden' => '2024-12-27',
        'richard-timothy-hernandez' => '2022-10-04', 'rowan-mcmanigal' => '2022-04-04',
        'thomas-welnicki' => '2022-01', 'cortez-aaron-rice' => '2022-02',
        'damian-smith-birge' => '2022-06-22', 'eugene-huelsman' => '2021-05',
        'kyle-robert-tornow' => '2021-07-27', 'will-parzybok' => '2025-01',
        'zaid-mohammed-mahdawi' => '2025-04', 'aida-yagmur-aston' => '2022-07-31',
        'alexander-jacob-castro' => '2022-07-31', 'alexandria-ty-fite' => '2022-07-31',
        'david-chavez' => '2024-12-12', 'deep-alpesh-kumar-patel' => '2024-09',
        'edwin-pena' => '2024-12-12', 'elise-saramarielle-kelder' => '2022-07-31',
        'fernando-lopez' => '2024-12-12', 'kamile-dincsoy' => '2022-07-31',
        'stephanie-amesquita' => '2024-06-07', 'vanessa-carrasco' => '2024-06-07',
        'wendy-lujan' => '2024-06-07', 'josie-robotin' => '2022-08-18',
        'caleb-a-brown' => '2024-05', 'christopher-k-zelle' => '2024-05',
        'csaba-john-csukas' => '2024-10-30', 'ghufran-ullah' => '2024-11',
        'hicham-talal' => '2024-03', 'izhar-muhammad' => '2024-11',
        'jarrid-bailey-huber' => '2025-01', 'jeffrey-stevens' => '2025',
        'john-mazurek' => '2024-04', 'leo-a-randle' => '2024-05',
        'gregory-william-loel-timm' => '2021-04-22', 'hunter-mattin' => '2021-04-15',
        'mylene-vialard' => '2021-08', 'rickey-johnson' => '2022-10-26',
    ];

    public function handle(): int
    {
        $removed = $flipped = $dated = 0;

        // 1) Remove never-imprisoned / never-in-custody records.
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

        // 2) Flip mislabeled "released" → still in custody.
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
                $case->release_date = null; // still serving — imprisoned_for_days counts to today
                $case->save();
            }
            $this->info("  flipped to in-custody: {$p->name}");
            $flipped++;
        }

        // 3) Backfill researched release dates (with precision).
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

        $this->info("\nDone. Removed {$removed}, flipped {$flipped} to in-custody, dated {$dated}.");
        $this->line('The 22 no-date Wave 1 records are left for the global null-fallback fix.');

        return self::SUCCESS;
    }
}
