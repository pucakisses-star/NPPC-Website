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
 * Updates Burt Lorton — IWW leader convicted at the 1918 Chicago mass trial —
 * with his birth date (June 4, 1877), his Leavenworth register number (13132),
 * his sentencing date (August 30, 1918), the Leavenworth institution, and his
 * public-domain 1917–18 prisoner portrait. Fixes a couple of OCR spacing errors
 * in the bio. His incarceration/release dates (Sep 7, 1917 – Dec 24, 1923) are
 * preserved. Idempotent.
 */
final class FillBurtLorton extends Command
{
    protected $signature = 'prisoners:fill-burt-lorton';

    protected $description = 'Update Burt Lorton: birth date, inmate #13132, institution, and portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Burt Lorton')->first();
        if (! $prisoner) {
            $this->error('Burt Lorton not found.');

            return self::FAILURE;
        }

        $leavenworth = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Leavenworth'],
            ['city' => 'Leavenworth', 'state' => 'Kansas']
        )->id;

        DB::transaction(function () use ($prisoner, $leavenworth) {
            $prisoner->description = 'Burt Lorton, a nine-year IWW member and secretary of its Chicago Recruiting Union, was convicted at the mass IWW trial in Chicago for seditious conspiracy and conspiracy to obstruct military service. He was sentenced on August 30, 1918, to ten years in prison and fined $30,000. He served in the Cook County Jail and Leavenworth Penitentiary from September 7, 1917, to December 24, 1923.';
            $prisoner->setPartialDate('birthdate', 1877, 6, 4);
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case) {
                $case->institution_id = $leavenworth;
                $case->charges = 'Seditious conspiracy and conspiracy to obstruct military service — convicted at the mass IWW trial in Chicago (Espionage Act of 1917 / Sedition Act of 1918).';
                $case->convicted = 'Yes — convicted at the Chicago IWW mass trial; sentenced August 30, 1918.';
                $case->sentence = 'Ten years in prison and a $30,000 fine. Held in the Cook County Jail and the U.S. Penitentiary at Leavenworth (register no. 13132) from September 7, 1917 to December 24, 1923.';
                $case->setPartialDate('sentenced_date', 1918, 8, 30);
                $case->setPartialDate('incarceration_date', 1917, 9, 7);
                $case->setPartialDate('release_date', 1923, 12, 24);
                $case->save();
            }
        });

        $src = database_path('data/photos/burt-lorton.jpg');
        if (is_file($src)) {
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put('prisoners/burt-lorton.jpg', (string) file_get_contents($src));
            $prisoner->photo = 'prisoners/burt-lorton.jpg';
            $prisoner->save();
            $this->info('Attached portrait: prisoners/burt-lorton.jpg');
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled Burt Lorton.');

        return self::SUCCESS;
    }
}
