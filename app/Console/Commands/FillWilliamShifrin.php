<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Corrects William Shifrin's case timeline from 1929 to 1928. Daily Worker
 * coverage places the Hebrew Butchers' Union strike start around September 21,
 * 1928; Shifrin was reported "behind the bars" by October 1, 1928 after
 * defending himself against five knife-wielding strike-breakers, and on
 * November 3, 1928 he pleaded not guilty while his attorney asked for release
 * on $15,000 bail. His arrest is therefore pinned to late September 1928
 * (stored at month precision). He was acquitted at trial (the self-defense
 * killing); the exact acquittal/release date is not documented, so no release
 * date is stored. Idempotent — rebuilds the single case.
 */
final class FillWilliamShifrin extends Command
{
    protected $signature = 'prisoners:fill-william-shifrin';

    protected $description = 'Correct William Shifrin\'s case to the September 1928 butchers\' strike';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'William Shifrin')->first();
        if (! $prisoner) {
            $this->error('William Shifrin not found.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($prisoner) {
            $prisoner->description = 'William Shifrin, a member of the New York butchers\' union, was indicted for manslaughter after killing, in self-defense, one of five knife-wielding strike-breakers sent against the union during the September 1928 Hebrew Butchers\' Union strike. The ILD\'s Labor Defender took up his defense with a "Shifrin Defense Fund," and he was acquitted at trial.';
            $prisoner->era = '1920s';
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $prisoner->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Manslaughter — for killing, in self-defense, one of five knife-wielding strike-breakers sent by the Hebrew Butchers\' Union machine during the September 1928 butchers\' strike.',
                'convicted' => 'No — acquitted at trial. He had pleaded not guilty on November 3, 1928 (his attorney asking for release on $15,000 bail); the jury accepted the self-defense case.',
                'sentence' => 'Held under high bail pending trial — reported "behind the bars" by October 1, 1928, with the defense committee still seeking bail in early November 1928 — then acquitted and freed. The exact acquittal/release date is not documented.',
            ]);
            // Arrested in late September 1928 (after the ~Sept 21 strike start, before the Oct 1 "behind bars" report).
            $case->setPartialDate('arrest_date', 1928, 9);
            $case->setPartialDate('incarceration_date', 1928, 9);
            $case->save();
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Corrected William Shifrin to the September 1928 butchers\' strike case.');

        return self::SUCCESS;
    }
}
