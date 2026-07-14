<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Enriches the Otto Janson record from his McNeil Island Penitentiary record
 * card (inmate no. 3149) and his family-history archive:
 *
 * - Offense: "Causing disloyalty and insubordination in the military and naval
 *   forces of the United States" (WWI Espionage/Sedition Acts).
 * - Arrested Oakland, Cal., Apr 13, 1918 (jailed 2 days, then bond); convicted
 *   San Francisco, Cal., May 4, 1918; sentenced May 10; received at McNeil
 *   Island May 16, 1918. Five-year term, expiring with good time Jan 10, 1922.
 * - Born August 19, 1890.
 *
 * Attaches the public-domain studio portrait (database/data/photos/otto-janson.jpg)
 * if he has none, updates his existing case in place, and links the McNeil
 * Island institution. Idempotent. Sources: McNeil Island record card;
 * jansonfamilyhistory.blogspot.com.
 */
final class EnrichOttoJanson extends Command
{
    protected $signature = 'prisoners:enrich-otto-janson';

    protected $description = 'Enrich Otto Janson from his McNeil Island record card + attach his portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('slug', 'otto-janson')->first();
        if (! $prisoner) {
            $this->error('Prisoner not found: otto-janson');

            return self::FAILURE;
        }

        DB::transaction(function () use ($prisoner) {
            $prisoner->update([
                'inmate_number' => '3149',
                'state' => 'California',
                'era' => '1910s',
                'gender' => 'Male',
                'description' => 'Otto Janson (born August 19, 1890) was an Oakland, California businessman '
                    .'prosecuted for sedition during World War I. He was overheard questioning the United States\' '
                    .'involvement in the war in Europe and was charged with causing disloyalty and insubordination in '
                    .'the military and naval forces of the United States under the wartime Espionage and Sedition Acts. '
                    .'Arrested in Oakland on April 13, 1918 — he was held only two days before being released on bond — '
                    .'he was convicted at San Francisco on May 4, 1918, sentenced on May 10, and entered the United '
                    .'States Penitentiary at McNeil Island, Washington (inmate no. 3149) on May 16, 1918. His five-year '
                    .'term was set to expire, with good-time credit, on January 10, 1922.',
            ]);

            // Firm birth date from the family archive.
            $prisoner->setPartialDate('birthdate', 1890, 8, 19);
            $prisoner->save();

            // Attach the public-domain portrait only if he has none.
            if (empty($prisoner->photo)) {
                $src = database_path('data/photos/otto-janson.jpg');
                if (is_file($src)) {
                    Storage::disk('public')->makeDirectory('prisoners');
                    Storage::disk('public')->put('prisoners/otto-janson.jpg', (string) file_get_contents($src));
                    $prisoner->photo = 'prisoners/otto-janson.jpg';
                    $prisoner->save();
                    $this->info('  Portrait attached.');
                } else {
                    $this->warn('  Portrait file missing: '.$src);
                }
            } else {
                $this->line('  Already has a photo — leaving alone.');
            }

            // McNeil Island institution (match or create).
            $institution = Institution::where('name', 'United States Penitentiary, McNeil Island')->first()
                ?? Institution::create([
                    'name' => 'United States Penitentiary, McNeil Island',
                    'city' => 'McNeil Island',
                    'state' => 'Washington',
                ]);

            $caseData = [
                'institution_id' => $institution->id,
                'charges' => 'Causing disloyalty and insubordination in the military and naval forces of the '
                    .'United States, under the wartime Espionage Act of 1917 / Sedition Act of 1918.',
                'convicted' => 'Convicted at San Francisco, California, on May 4, 1918.',
                'sentence' => 'Five years in prison; the term was set to expire, with good-time credit, on January 10, 1922.',
                'arrest_date' => '1918-04-13',
                'sentenced_date' => '1918-05-10',
                'incarceration_date' => '1918-05-16',
                'release_date' => '1922-01-10',
            ];

            // Update his existing case in place; create one only if none exists.
            $case = $prisoner->cases()->first();
            if ($case) {
                $case->update($caseData);
                $this->info('  Case updated.');
            } else {
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $caseData));
                $this->info('  Case created.');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Done: Otto Janson enriched.');

        return self::SUCCESS;
    }
}
