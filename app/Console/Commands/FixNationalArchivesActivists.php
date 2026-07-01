<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects the two Declare Emergency climate activists who threw red powder on
 * the U.S. Constitution's display case at the National Archives Rotunda on
 * February 14, 2024. Both are already in the database, but their records had
 * several errors:
 *
 *  - Donald Zepeda's description gave the wrong incident date (Nov 25, 2024)
 *    and he was marked released — but his 24-month term (sentenced Nov 15,
 *    2024) runs until roughly late 2026, so as of mid-2026 he is still in
 *    prison.
 *  - Jackson Green's case listed the National Gallery "Honor Them" action as a
 *    21-month sentence; it was actually 90 days served concurrently with his
 *    18-month National Archives sentence.
 *  - Both had incarceration_date set to their 2022/2024 arrest date rather than
 *    their actual prison entry, inflating time served.
 *
 * Sentencing: Green Nov 12, 2024 (18 mo + 90 days concurrent); Zepeda Nov 15,
 * 2024 (24 mo); both before Judge Amy Berman Jackson, each with 2 years'
 * supervised release and $58,600 shared restitution. Exact surrender dates were
 * not published, so the sentencing date is used as the prison-entry anchor.
 *
 * Idempotent.
 *
 * Sources: The Art Newspaper (2024-11-22), Fox News, Climate Rights Intl,
 * Courthouse News, WJLA — all reporting the Nov 2024 sentencings.
 */
final class FixNationalArchivesActivists extends Command
{
    protected $signature = 'prisoners:fix-national-archives';

    protected $description = 'Correct the National Archives red-powder activists (Zepeda, Green)';

    public function handle(): int
    {
        // ---- Donald Zepeda (still serving 24-month term) ----
        $this->fix('donald-zepeda', 'Donald Zepeda', [
            'in_custody' => true,
            'released' => false,
            'description' => 'Donald Jose-David Zepeda, age 35, of Maryland, a member of the climate group Declare Emergency, dumped red powder over the display case holding the U.S. Constitution in the Rotunda of the National Archives in Washington, D.C. on February 14, 2024, alongside co-defendant Jackson Green, to demand action on climate change. The cleanup cost more than $58,000 and closed the Rotunda for four days. He pleaded guilty on August 15, 2024 to felony destruction of government property and was sentenced on November 15, 2024 by Judge Amy Berman Jackson to 24 months in federal prison.',
        ], [
            'charges' => 'Felony destruction of government property (and defacing public property) for dumping red powder over the U.S. Constitution\'s display case at the National Archives, February 14, 2024.',
            'sentenced_date' => '2024-11-15',
            'incarceration_date' => '2024-11-15',
            'release_date' => null, // still in custody as of mid-2026
            'sentence' => '24 months in federal prison, 24 months of supervised release, and $58,600 in restitution (shared with co-defendant Jackson Green), plus community service including graffiti cleanup and a ban from Washington, D.C. and all U.S. museums. Sentenced November 15, 2024 by Judge Amy Berman Jackson. Exact prison-report date not published; the sentencing date is used as the custody anchor.',
        ]);

        // ---- Jackson George Green (18-month term, released 2026) ----
        $this->fix('jackson-george-green', 'Jackson George Green', [
            'in_custody' => false,
            'released' => true,
            'description' => 'Jackson George Green, age 27, of Utah, a member of the climate group Declare Emergency, dumped red powder over the display case holding the U.S. Constitution in the Rotunda of the National Archives on February 14, 2024, alongside co-defendant Donald Zepeda. He had earlier, on November 14, 2023, painted "Honor Them" in red on the wall beside the Augustus Saint-Gaudens Shaw 54th Regiment Memorial at the National Gallery of Art, causing about $706 in damage. He pleaded guilty in August 2024 and was sentenced on November 12, 2024 by Judge Amy Berman Jackson to 18 months in federal prison for the Archives action, with a 90-day sentence for the National Gallery action to run concurrently.',
        ], [
            'charges' => 'Felony destruction of government property for the February 14, 2024 red-powder action on the U.S. Constitution\'s display case at the National Archives; and destruction of National Gallery of Art property (40 U.S.C. §§ 6303, 6307) for the November 14, 2023 "Honor Them" paint action.',
            'sentenced_date' => '2024-11-12',
            'incarceration_date' => '2024-11-12',
            'release_date' => '2026-05-12', // projected end of the 18-month term; exact release date not published
            'sentence' => '18 months in federal prison, 24 months of supervised release, and $58,600 in restitution (shared with co-defendant Donald Zepeda) for the National Archives action; plus a concurrent 90 days and $706 in restitution for the 2023 "Honor Them" paint action at the National Gallery of Art. Sentenced November 12, 2024 by Judge Amy Berman Jackson. Released in 2026 (exact release date not published; shown here as the full 18-month term).',
        ]);

        $this->info("\nDone.");

        return self::SUCCESS;
    }

    private function fix(string $slug, string $name, array $prisonerFields, array $caseFields): void
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', $name)->first();

        if (! $prisoner) {
            $this->warn("  no prisoner '{$slug}' — skipped");

            return;
        }

        foreach ($prisonerFields as $k => $v) {
            $prisoner->{$k} = $v;
        }
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if (! $case) {
            $this->warn("  {$prisoner->name} has no case — flags updated only");

            return;
        }

        // Clear any year-only precision on the date fields we are setting.
        $precision = $case->date_precision ?? [];
        unset($precision['incarceration_date'], $precision['release_date'], $precision['sentenced_date']);
        $case->date_precision = $precision ?: null;

        foreach ($caseFields as $k => $v) {
            $case->{$k} = $v;
        }
        $case->save();
        $case->refresh();

        $status = $prisoner->in_custody ? 'in custody' : 'released';
        $days = $case->imprisoned_for_days;
        $this->info("  {$prisoner->name}: {$status}, ".($days === null ? 'no days' : "{$days} days").". View: /prisoner/{$prisoner->slug}");
    }
}
