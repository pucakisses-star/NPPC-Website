<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Records LeRoi Jones (Amiri Baraka)'s post-sentencing custody and release for
 * his 1967 Newark case. He was arrested July 14, 1967 during the rebellion and
 * freed on pre-trial bail; convicted November 6, 1967; and sentenced January 4,
 * 1968 (2½–3 years plus a $1,000 fine), at which point he was taken into
 * custody. He was released on $25,000 bail on January 9, 1968 pending appeal —
 * the bail his mother raised by mortgaging homes — and the conviction was
 * reversed April 21, 1969, so the prison sentence was never served.
 *
 * Sets incarceration_date 1968-01-04 and release_date 1968-01-09 (the ~5 days of
 * documented post-sentencing custody) and clarifies the sentence text.
 * Idempotent.
 */
final class SetBarakaReleaseDate extends Command
{
    protected $signature = 'prisoners:set-baraka-release-date';

    protected $description = "Set LeRoi Jones (Amiri Baraka)'s 1968 incarceration and bail-release dates";

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'LeRoi Jones')->first();

        if (! $prisoner) {
            $this->warn('LeRoi Jones not found, nothing to do.');

            return self::SUCCESS;
        }

        $case = $prisoner->cases->first(fn ($c) => str_contains(strtolower((string) $c->charges), 'revolver') || str_contains(strtolower((string) $c->charges), 'newark'))
            ?? $prisoner->cases->first();

        if (! $case) {
            $this->warn('LeRoi Jones has no case to update.');

            return self::SUCCESS;
        }

        $case->incarceration_date = '1968-01-04';
        $case->release_date = '1968-01-09';
        $case->sentence = '2½ to 3 years plus a $1,000 fine (sentenced January 4, 1968); released on $25,000 bail January 9, 1968 pending appeal, and the conviction was reversed on April 21, 1969 — the prison sentence was never served';
        $case->save();
        $case->refresh();

        $this->info(sprintf(
            'LeRoi Jones case: incarceration=%s release=%s (%d days).',
            $case->incarceration_date?->format('Y-m-d'),
            $case->release_date?->format('Y-m-d'),
            $case->imprisoned_for_days ?? 0,
        ));

        return self::SUCCESS;
    }
}
