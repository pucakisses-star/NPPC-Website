<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects the "time served" for the 23 pro-life FACE Act activists pardoned by
 * President Trump on January 23, 2025.
 *
 * The problem: every one of these records had its case `incarceration_date` set
 * to the 2022 ARREST date. Because the site computes imprisoned_for_days as
 * diffInDays(incarceration_date, release_date), that made "time in jail" count
 * from 2022 — inflating it to ~830–1030 days for everyone, including people who
 * never spent a day in prison (they got probation or home confinement).
 *
 * This sets each case's real prison-entry date instead:
 *   - D.C. Surgi-Clinic defendants were remanded at their 2023 guilty verdicts
 *     and held continuously until the pardon (~16–17 months).
 *   - The three Tennessee defendants who did prison reported after their 2024
 *     sentencings (~3–4 months by the pardon).
 *   - Everyone who got probation / home confinement / time-served has
 *     incarceration_date cleared, so imprisoned_for_days becomes null (no
 *     prison term recorded) rather than an inflated multi-year figure.
 *   - Two individual cases (Bevelyn Williams, Fr. Moscinski) get their real
 *     surrender / term dates.
 *
 * NOTE: the PrisonerCase saving hook always recomputes imprisoned_for_days from
 * the dates, so this fixes the dates — not the derived column directly.
 *
 * Idempotent: safe to re-run; it just re-sets the same dates.
 *
 * Sourcing: DOJ/USAO press releases (quoted via CNA, NCRegister, NBC4/WUSA9,
 * Detroit Catholic, OSV News, Live Action, Thomas More Society, LifeNews).
 */
final class FixFaceActTimeServed extends Command
{
    protected $signature = 'prisoners:fix-face-act-time-served';

    protected $description = 'Set real prison-entry dates for the pardoned FACE Act activists so time-served is accurate';

    private const PARDON = '2025-01-23';

    /**
     * slug => [incarceration_date|null, release_date, sentence-text override|null]
     *
     * incarceration_date null  => never imprisoned (probation / home confinement
     * / time-served); imprisoned_for_days becomes null.
     */
    private const PLAN = [
        // ---- Washington, D.C. Surgi-Clinic case (Oct 2020 blockade) ----
        // First-trial defendants remanded at the Aug 29, 2023 guilty verdict.
        'lauren-handy' => ['2023-08-29', self::PARDON, null],
        'john-hinshaw' => ['2023-08-29', self::PARDON, null],
        'william-goodman' => ['2023-08-29', self::PARDON, null],
        'herb-geraghty' => ['2023-08-29', self::PARDON, null],
        'heather-idoni' => ['2023-08-29', self::PARDON,
            '24 months in federal prison in the Washington, D.C. Surgi-Clinic blockade case; an 8-month Tennessee FACE Act sentence ran concurrently and the Michigan case was pardoned before sentencing. Remanded at the August 2023 verdict and held until the January 23, 2025 pardon.'],
        // Second-trial defendants remanded at the Sept 15, 2023 verdict.
        'jonathan-darnel' => ['2023-09-15', self::PARDON, null],
        'jean-marshall' => ['2023-09-15', self::PARDON, null],
        'joan-bell' => ['2023-09-15', self::PARDON, null],
        // Sentenced to 24 mo but allowed to stay on house arrest until a
        // medical-prison bed opened; reported to prison Nov 27, 2024.
        'paula-harlow' => ['2024-11-27', self::PARDON,
            '24 months in federal prison for the D.C. Surgi-Clinic blockade. Allowed to remain on home confinement after her May 2024 sentencing until a medical-prison bed opened; reported to prison on November 27, 2024, and freed by the January 23, 2025 pardon after roughly two months inside.'],
        // Pleaded guilty; sentenced to 10 months in 2023 and completed the term
        // before the pardon. Exact custody dates were not published — approximate.
        'jay-smith' => ['2023-04-01', '2024-02-01',
            '10 months in federal prison after pleading guilty in the D.C. Surgi-Clinic case. He served the roughly 10-month term during 2023–2024 and had been released before the January 23, 2025 pardon (which cleared the conviction). Exact custody dates are not publicly documented; the dates shown are approximate.'],

        // ---- Nashville / Mount Juliet, TN case (Mar 5, 2021 Carafem blockade) ----
        // Only three of the Tennessee defendants served any prison.
        // Report dates after the fall-2024 sentencings are not published — approximate.
        'chester-gallagher' => ['2024-09-27', self::PARDON,
            '16 months in federal prison and three years of supervised release — the longest Tennessee sentence — as the organizer of the 2021 Carafem clinic blockade. Reported to prison after his fall 2024 sentencing and was freed by the January 23, 2025 pardon; the exact report date is not published (approximate).'],
        'calvin-zastrow' => ['2024-10-01', self::PARDON,
            '6 months in federal prison and three years of supervised release in the Tennessee Carafem case (his Michigan case was pardoned before sentencing). Self-reported to prison around October 2024 and was freed by the January 23, 2025 pardon; the exact report date is not published (approximate).'],

        // Tennessee defendants who got probation / home confinement / time-served
        // — no prison term. incarceration_date cleared.
        'coleman-boyd' => [null, self::PARDON,
            'Five years of probation, six months of home confinement, and a $10,000 fine in the Tennessee Carafem case — no prison. Pardoned January 23, 2025.'],
        'paul-vaughn' => [null, self::PARDON,
            'Six months of home confinement and three years of supervised release, with no prison time and no fine, in the Tennessee Carafem case; prosecutors had sought a year in prison. Pardoned January 23, 2025.'],
        'dennis-green' => [null, self::PARDON,
            'Time served plus three years of supervised release (including six months of home confinement) in the Tennessee Carafem case — no new prison term. Pardoned January 23, 2025.'],
        'eva-edl' => [null, self::PARDON,
            'Three years of probation in the Tennessee Carafem case — no prison. A 90-year-old concentration-camp survivor. Pardoned January 23, 2025.'],
        'james-zastrow' => [null, self::PARDON,
            'Three years of probation, with the first 90 days on home detention, in the Tennessee Carafem case — no prison. Pardoned January 23, 2025.'],
        'paul-place' => [null, self::PARDON,
            'Three years of probation, with the first 90 days on home detention, in the Tennessee Carafem case — no prison. Pardoned January 23, 2025.'],
        // Michigan (Sterling Heights) defendant, pardoned before sentencing — no prison.
        'eva-zastrow' => [null, self::PARDON,
            'Convicted in the Michigan (Sterling Heights) clinic-blockade case in August 2024; sentencing was stayed and she was pardoned before it took place, so she served no prison time. Pardoned January 23, 2025.'],

        // ---- Individual cases ----
        // 41-month SDNY sentence; self-reported Oct 16, 2024, freed early by the pardon.
        'bevelyn-beatty-williams' => ['2024-10-16', self::PARDON,
            '41 months in federal prison and two years of supervised release for the June 2020 blockade of a lower-Manhattan Planned Parenthood (SDNY); sentenced July 24, 2024. She self-reported to FCI Aliceville, Alabama on October 16, 2024 and was freed by the January 23, 2025 pardon after roughly three months, with most of the term unserved.'],
        // 6-month Hempstead, NY sentence completed ~2024, before the pardon (approximate).
        'fidelis-moscinski' => ['2023-07-01', '2024-01-01',
            'Six months in federal prison for the July 2022 lock-and-chain blockade of a Planned Parenthood in Hempstead, New York; sentenced June 27, 2023. He completed the term in early 2024 and had already been released before the January 23, 2025 pardon, which cleared the conviction. Exact custody dates are not publicly documented; the dates shown are approximate.'],
    ];

    public function handle(): int
    {
        $changed = 0;

        foreach (self::PLAN as $slug => [$inc, $rel, $sentence]) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("  no prisoner '{$slug}' — skipped");

                continue;
            }

            $case = $prisoner->cases()->first();
            if (! $case) {
                $this->warn("  {$prisoner->name} has no case — skipped");

                continue;
            }

            $prisoner->released = true;
            $prisoner->in_custody = false;
            $prisoner->save();

            // Clear any stale year-only precision on these two date fields so a
            // full YYYY-MM-DD renders as an exact date.
            $precision = $case->date_precision ?? [];
            unset($precision['incarceration_date'], $precision['release_date']);
            $case->date_precision = $precision ?: null;

            $case->incarceration_date = $inc; // null => never imprisoned
            $case->release_date = $rel;
            if ($sentence !== null) {
                $case->sentence = $sentence;
            }
            $case->save(); // saving hook recomputes imprisoned_for_days from the dates

            $case->refresh();
            $days = $case->imprisoned_for_days;
            $label = $days === null ? 'no prison' : "{$days} days";
            $this->info("  {$prisoner->name}: {$label}");
            $changed++;
        }

        $this->info("\nDone. Corrected time-served on {$changed} FACE Act record(s).");
        $this->line('Not touched (already 0): joel-curry, justin-phillips (pardoned before sentencing).');

        return self::SUCCESS;
    }
}
