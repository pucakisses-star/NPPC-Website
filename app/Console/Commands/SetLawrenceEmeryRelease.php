<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fills Lawrence Emery's (empty) Imperial Valley criminal-syndicalism case and
 * records that he was the last of the jailed Imperial Valley organizers to be
 * released from San Quentin, on February 21, 1933. His incarceration began in
 * June 1930 (month precision — the exact day is not documented); the release
 * date is exact. Idempotent — updates his single case in place.
 */
class SetLawrenceEmeryRelease extends Command
{
    protected $signature = 'prisoners:set-lawrence-emery-release';

    protected $description = 'Fill Lawrence Emery\'s San Quentin case with his Feb 21, 1933 release (last Imperial Valley organizer freed)';

    public function handle(): int
    {
        DB::transaction(function () {
            $e = Prisoner::withUnderReview()->where('name', 'Lawrence Emery')->first();
            if (! $e) {
                $this->warn('Lawrence Emery not found.');

                return;
            }

            $sanQuentin = Institution::firstOrCreate(
                ['name' => 'San Quentin State Prison'],
                ['city' => 'San Quentin', 'state' => 'California']
            );

            $case = $e->cases()->first() ?? new PrisonerCase(['prisoner_id' => $e->id]);
            $case->institution_id = $sanQuentin->id;
            $case->charges = 'Criminal syndicalism (California Criminal Syndicalism Act) — for his role in the 1930 Imperial Valley farm workers\' strike.';
            $case->convicted = 'Yes — convicted of criminal syndicalism.';
            $case->sentence = 'Three to forty-two years at San Quentin. He was the last of the jailed Imperial Valley organizers to be released, freed on February 21, 1933.';
            // June 1930 (month precision) -> Feb 21, 1933 (exact).
            $case->setPartialDate('incarceration_date', 1930, 6);
            $case->setPartialDate('release_date', 1933, 2, 21);
            $case->save();

            $this->info('Lawrence Emery: case filled — San Quentin, June 1930 to Feb 21, 1933 ('.$case->imprisoned_for_days.' days).');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
