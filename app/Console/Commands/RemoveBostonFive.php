<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes the "Boston Five" (United States v. Spock, 1968) draft-conspiracy
 * defendants — Benjamin Spock, William Sloane Coffin, Mitchell Goodman,
 * Michael Ferber, and Marcus Raskin. None served any jail time: they were free
 * pending appeal and their convictions were reversed by the First Circuit in
 * 1969 (Raskin was acquitted outright at trial), so they do not belong in the
 * political-prisoner database.
 *
 * The creation sources have also been removed (the Boston Five blocks in
 * prisoners:add-us-rebellions-pps and prisoners:fill-baldwin-book-stubs, and the
 * five records in goldstein-prisoners.json) so they will not be re-added.
 * Idempotent.
 */
final class RemoveBostonFive extends Command
{
    protected $signature = 'prisoners:remove-boston-five';

    protected $description = 'Delete the Boston Five (Spock trial) defendants — none served jail time';

    private const NAMES = [
        'Benjamin Spock',
        'William Sloane Coffin',
        'William Sloane Coffin Jr.',
        'Mitchell Goodman',
        'Michael Ferber',
        'Marcus Raskin',
    ];

    public function handle(): int
    {
        $prisoners = Prisoner::withUnderReview()->whereIn('name', self::NAMES)->get();

        if ($prisoners->isEmpty()) {
            $this->info('No Boston Five entries found — already removed.');

            return self::SUCCESS;
        }

        foreach ($prisoners as $prisoner) {
            $prisoner->cases()->delete();
            $prisoner->delete();
            $this->info('Deleted: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
