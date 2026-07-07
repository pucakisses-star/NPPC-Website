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
 * Cleans up John Potthast — an IWW defendant convicted at the 1918 Sacramento
 * mass trial — and attaches his portrait. Fixes the OCR errors in his bio
 * ("rWW" -> "IWW", "Penitentiaiy" -> "Penitentiary", stray footnote "324"),
 * adds the IWW affiliation, sets his case institution to the U.S. Penitentiary
 * at Leavenworth, and attaches his public-domain 1918 prisoner photo. His dates
 * (incarcerated Aug 17, 1918; released Dec 22, 1923) are preserved. Idempotent.
 */
final class FillJohnPotthast extends Command
{
    protected $signature = 'prisoners:fill-john-potthast';

    protected $description = 'Clean up John Potthast (Sacramento IWW trial), add affiliation, institution, and portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'John Potthast')->first();
        if (! $prisoner) {
            $this->error('John Potthast not found.');

            return self::FAILURE;
        }

        $leavenworth = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Leavenworth'],
            ['city' => 'Leavenworth', 'state' => 'Kansas']
        )->id;

        DB::transaction(function () use ($prisoner, $leavenworth) {
            $prisoner->description = 'John Potthast, a laborer from Baltimore, Maryland, was convicted at the mass IWW trial in Sacramento and sentenced to ten years in prison. He served in local jails and Leavenworth Penitentiary from August 17, 1918, to December 22, 1923.';
            $prisoner->affiliation = ['Industrial Workers of the World (IWW)'];
            $prisoner->ideologies = ['Anti-Militarism'];
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case) {
                $case->institution_id = $leavenworth;
                $case->charges = 'Convicted at the mass IWW trial in Sacramento — the 1918 federal Espionage Act / Sedition Act prosecution of the Industrial Workers of the World.';
                $case->convicted = 'Yes — convicted at the Sacramento IWW mass trial, 1918.';
                $case->sentence = 'Ten years. Served in local jails and the U.S. Penitentiary at Leavenworth from August 17, 1918 to December 22, 1923.';
                $case->setPartialDate('incarceration_date', 1918, 8, 17);
                $case->setPartialDate('release_date', 1923, 12, 22);
                $case->save();
            }
        });

        $src = database_path('data/photos/john-potthast.jpg');
        if (is_file($src)) {
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put('prisoners/john-potthast.jpg', (string) file_get_contents($src));
            $prisoner->photo = 'prisoners/john-potthast.jpg';
            $prisoner->save();
            $this->info('Attached portrait: prisoners/john-potthast.jpg');
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled John Potthast.');

        return self::SUCCESS;
    }
}
