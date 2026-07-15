<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Records that John Briggs — the Vietnam Veterans Against the War member and
 * "Gainesville Eight" defendant — was jailed September 8–17, 1972, by setting
 * his case's incarceration and release dates. The Goldstein importer skips
 * records that already exist, so this updates the live record in place (matched
 * by name + Gainesville Eight marker). Idempotent.
 */
final class SetJohnBriggsJailing extends Command
{
    protected $signature = 'prisoners:set-john-briggs-jailing';

    protected $description = 'Set John Briggs (Gainesville Eight) jailed Sept 8–17, 1972 on his case';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'John Briggs')
            ->get()
            ->first(fn ($x) => str_contains((string) $x->description, 'Gainesville')
                || in_array('Gainesville Eight', (array) $x->affiliation, true));

        if (! $prisoner) {
            $this->warn('John Briggs (Gainesville Eight) not found — run prisoners:add-goldstein-prisoners first.');

            return self::SUCCESS;
        }

        if (! str_contains((string) $prisoner->description, 'jailed September 8')) {
            $prisoner->description = str_replace(
                'in Miami Beach.',
                'in Miami Beach. He was jailed September 8–17, 1972.',
                (string) $prisoner->description,
            );
            $prisoner->save();
        }

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
        $case->prisoner_id = $prisoner->id;
        $case->setPartialDate('incarceration_date', 1972, 9, 8);
        $case->setPartialDate('release_date', 1972, 9, 17);
        $case->sentence = 'Jailed September 8–17, 1972.';
        $case->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Set John Briggs jailed Sept 8–17, 1972 (slug: '.$prisoner->slug.').');

        return self::SUCCESS;
    }
}
