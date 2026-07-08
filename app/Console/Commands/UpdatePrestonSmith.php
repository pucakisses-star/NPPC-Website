<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Refines the case dates for Morrie R. Preston and Joseph W. Smith — the two
 * IWW/WFM defendants framed in the 1907 Goldfield, Nevada killing of restaurant
 * owner John Silva — and attaches Preston's portrait if it has been placed at
 * database/data/photos/morrie-r-preston.jpg.
 *
 *   Preston: arrested Mar 10, 1907; convicted of second-degree murder May 9,
 *     1907 (25 years); paroled — the Nevada Attorney General ruled on July 9,
 *     1914 that he had been paroled and the warden had to release him.
 *   Smith:   arrested Mar 12, 1907; convicted of voluntary manslaughter May 9,
 *     1907 (10 years); released/paroled from Nevada State Prison at Carson City
 *     on November 14, 1911.
 *
 * Idempotent.
 */
final class UpdatePrestonSmith extends Command
{
    protected $signature = 'prisoners:update-preston-smith';

    protected $description = 'Refine Preston/Smith (1907 Goldfield) case dates and attach Preston\'s photo';

    public function handle(): int
    {
        $prison = Institution::firstOrCreate(
            ['name' => 'Nevada State Prison'],
            ['city' => 'Carson City', 'state' => 'Nevada']
        )->id;

        DB::transaction(function () use ($prison) {
            $preston = Prisoner::withUnderReview()->where('name', 'Morrie R. Preston')->first();
            if ($preston) {
                $preston->first_name = 'Morris';
                $preston->middle_name = 'Rockwood';
                $preston->last_name = 'Preston';
                $preston->setPartialDate('birthdate', 1882, 11, 28);
                $preston->setPartialDate('death_date', 1924, 10, 10);
                if (! str_contains((string) $preston->description, '1924')) {
                    $preston->description = rtrim((string) $preston->description).' He was paroled in 1914 and died in a Los Angeles workplace accident on October 10, 1924.';
                }
                $preston->save();

                $case = $preston->cases()->first();
                if ($case) {
                    $case->institution_id = $prison;
                    $case->convicted = 'Yes — convicted of second-degree murder on May 9, 1907 (widely regarded as a frame-up).';
                    $case->sentence = 'Twenty-five years. Paroled from Nevada State Prison; on July 9, 1914 the Nevada Attorney General ruled that he had been paroled and that the warden had a duty to release him.';
                    $case->setPartialDate('arrest_date', 1907, 3, 10);
                    $case->setPartialDate('sentenced_date', 1907, 5, 9);
                    $case->setPartialDate('incarceration_date', 1907, 5, 9);
                    $case->setPartialDate('release_date', 1914, 7, 9);
                    $case->save();
                }
                $this->attachPhoto($preston, 'morrie-r-preston.jpg');
                $this->info('Updated Morrie R. Preston.');
            } else {
                $this->warn('Morrie R. Preston not found.');
            }

            $smith = Prisoner::withUnderReview()->where('name', 'Joseph W. Smith')->first();
            if ($smith) {
                $smith->first_name = 'Joseph';
                $smith->middle_name = 'William';
                $smith->last_name = 'Smith';
                $smith->setPartialDate('death_date', 1935);
                $smith->save();

                $case = $smith->cases()->first();
                if ($case) {
                    $case->institution_id = $prison;
                    $case->convicted = 'Yes — convicted of voluntary manslaughter on May 9, 1907.';
                    $case->sentence = 'Ten years. Released/paroled from Nevada State Prison at Carson City on November 14, 1911.';
                    $case->setPartialDate('arrest_date', 1907, 3, 12);
                    $case->setPartialDate('sentenced_date', 1907, 5, 9);
                    $case->setPartialDate('incarceration_date', 1907, 5, 9);
                    $case->setPartialDate('release_date', 1911, 11, 14);
                    $case->save();
                }
                $this->info('Updated Joseph W. Smith.');
            } else {
                $this->warn('Joseph W. Smith not found.');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }

    private function attachPhoto(Prisoner $prisoner, string $file): void
    {
        $src = database_path('data/photos/'.$file);
        if (is_file($src) && empty($prisoner->photo)) {
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put('prisoners/'.$file, (string) file_get_contents($src));
            $prisoner->photo = 'prisoners/'.$file;
            $prisoner->save();
            $this->info('Attached portrait: prisoners/'.$file);
        }
    }
}
