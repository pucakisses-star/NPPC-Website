<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Adds William Buwalda's birth and death dates (September 6, 1869 – July 6,
 * 1946) and attaches his portrait — a public-domain 1908 newspaper clipping
 * (caption: "Private William Buwalda, who is on trial for sympathizing with
 * Emma Goldman"), cropped to the framed figure. He was the U.S. Army soldier
 * court-martialed and sent to Alcatraz for shaking Emma Goldman's hand.
 *
 * Only fills what is missing; never overwrites an existing photo. Idempotent.
 */
class SetWilliamBuwaldaDetails extends Command
{
    protected $signature = 'prisoners:set-william-buwalda-details';

    protected $description = 'Set William Buwalda\'s birth/death dates and attach his photo';

    public function handle(): int
    {
        DB::transaction(function () {
            $b = Prisoner::withUnderReview()->where('name', 'William Buwalda')->first();
            if (! $b) {
                $this->warn('William Buwalda not found — run prisoners:add-anarchist-press-prisoners first.');

                return;
            }

            $b->setPartialDate('birthdate', 1869, 9, 6);
            $b->setPartialDate('death_date', 1946, 7, 6);
            $b->save();

            $src = database_path('data/photos/william-buwalda.jpg');
            if (is_file($src) && empty($b->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/william-buwalda.jpg', file_get_contents($src));
                $b->photo = 'prisoners/william-buwalda.jpg';
                $b->save();
                $this->info('Linked photo for William Buwalda.');
            }

            $this->info('Set William Buwalda (slug: '.$b->slug.'): b. 1869-09-06, d. 1946-07-06.');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
