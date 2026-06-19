<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Softens Carter Camp's record to reflect the contested court record. His
 * existing entry stated flatly that he "served two years in federal prison"
 * with a "3 years" sentence, but the Eighth Circuit REVERSED his Wounded Knee
 * postal-inspector conviction in 1976 (United States v. Camp, 541 F.2d 737)
 * for a defective indictment. Obituaries still say he served ~2–3 years at
 * Leavenworth, so the time served is genuinely contested. This rewrites the
 * misleading bio clause and the case's conviction/sentence fields to present
 * the dispute honestly, without deleting the (obituary-sourced) dates.
 * Idempotent.
 */
final class UpdateCarterCamp extends Command
{
    protected $signature = 'prisoners:update-carter-camp';

    protected $description = "Soften Carter Camp's record to note his Wounded Knee conviction was reversed on appeal";

    private const OLD_BIO = 'He was convicted in 1975 of assault on a federal officer at Wounded Knee and served two years in federal prison.';

    private const NEW_BIO = 'He was convicted in 1975 of interfering with a U.S. postal inspector detained at Wounded Knee, but the conviction was reversed on appeal in 1976 (United States v. Camp, 541 F.2d 737) over a defective indictment; accounts of how much time he actually served at Leavenworth vary, with obituaries citing roughly two to three years.';

    public function handle(): int
    {
        $p = Prisoner::withUnderReview()->where('slug', 'carter-camp')->first();

        if (! $p) {
            $this->warn('carter-camp not found — skipping (no-op).');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($p) {
            if ($p->description && str_contains($p->description, self::OLD_BIO)) {
                $p->description = str_replace(self::OLD_BIO, self::NEW_BIO, $p->description);
                $p->save();
                $this->info('  Bio updated.');
            } else {
                $this->line('  Bio clause not found (already updated?) — left as-is.');
            }

            $case = $p->cases()->first();
            if ($case) {
                $case->convicted = 'Convicted 1975 (Cedar Rapids), but the conviction was reversed on appeal in 1976 — United States v. Camp, 541 F.2d 737 (8th Cir.) — for a defective indictment';
                $case->sentence = '3 years imposed; conviction later reversed on appeal (1976). Accounts of time actually served at Leavenworth vary (obituaries cite roughly two to three years).';
                $case->save();
                $this->info('  Case conviction/sentence updated.');
            }
        });

        $this->info('Updated Carter Camp (conviction reversed on appeal; contested time served).');

        return self::SUCCESS;
    }
}
