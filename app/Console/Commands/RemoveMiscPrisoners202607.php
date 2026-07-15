<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes six prisoner entries at the user's request:
 *   - Joseph Mdluli, Walter Sisulu, Looksmart Ngudle (anti-apartheid ANC figures)
 *   - Kook, Quamana, Harry Kenner (1811 German Coast Uprising)
 *
 * The three German Coast entries have also been removed from
 * prisoners:add-us-rebellions-pps so that loader will not re-add them.
 * Idempotent — safe to re-run.
 */
final class RemoveMiscPrisoners202607 extends Command
{
    protected $signature = 'prisoners:remove-misc-202607';

    protected $description = 'Delete six entries (Mdluli, Sisulu, Ngudle, Kook, Quamana, Harry Kenner)';

    private const NAMES = [
        'Joseph Mdluli',
        'Walter Sisulu',
        'Looksmart Ngudle',
        'Kook',
        'Quamana',
        'Harry Kenner',
    ];

    public function handle(): int
    {
        $prisoners = Prisoner::withUnderReview()->whereIn('name', self::NAMES)->get();

        if ($prisoners->isEmpty()) {
            $this->info('None of the six entries found — already removed.');

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
