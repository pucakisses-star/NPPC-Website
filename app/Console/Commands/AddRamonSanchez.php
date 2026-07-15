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
 * Adds Ramon L. Sanchez, a Spanish-born laborer and IWW organizer arrested in
 * the Sacramento, California criminal-syndicalism prosecutions. From his I.W.W.
 * mug-shot record: arrested June 21, 1921; held to answer August 17, 1921 on a
 * District Attorney's complaint; sentenced October 20, 1921 to one to fourteen
 * years at San Quentin. He was found with I.W.W. organizer's books, literature,
 * and dues receipts. Create-or-update by name; idempotent.
 */
final class AddRamonSanchez extends Command
{
    protected $signature = 'prisoners:add-ramon-sanchez';

    protected $description = 'Add Ramon L. Sanchez (Sacramento IWW criminal-syndicalism case, 1921)';

    public function handle(): int
    {
        $sanQuentin = Institution::firstOrCreate(
            ['name' => 'San Quentin State Prison'],
            ['city' => 'San Quentin', 'state' => 'California']
        )->id;

        DB::transaction(function () use ($sanQuentin) {
            $prisoner = Prisoner::withUnderReview()->where('name', 'Ramon L. Sanchez')->first()
                ?? new Prisoner(['name' => 'Ramon L. Sanchez']);

            $prisoner->fill([
                'name' => 'Ramon L. Sanchez',
                'first_name' => 'Ramon',
                'middle_name' => 'L.',
                'last_name' => 'Sanchez',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'California',
                'era' => '1920s',
                'ideologies' => ['Anarcho-Syndicalism'],
                'affiliation' => ['Industrial Workers of the World (IWW)'],
                'description' => 'Ramon L. Sanchez was a Spanish-born laborer and Industrial Workers of the World (IWW) organizer arrested in Sacramento, California, on June 21, 1921 during the wave of criminal-syndicalism prosecutions of the union. He was 34 years old, stood 5\'3", weighed 133 pounds, and had black hair and brown eyes. Found with I.W.W. organizer\'s books, literature, and receipts showing his collections and affiliations with the union, he was held to answer on August 17, 1921 on a complaint issued by the District Attorney\'s office, and on October 20, 1921 was sentenced to one to fourteen years at San Quentin prison for criminal syndicalism.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $prisoner->setPartialDate('birthdate', 1887);
            $prisoner->save();

            // Attach his 1921 Sacramento mug-shot (public domain), cropped to the frontal view.
            $src = database_path('data/photos/ramon-l-sanchez.jpg');
            if (is_file($src)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/ramon-l-sanchez.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/ramon-l-sanchez.jpg';
                $prisoner->save();
            }

            $prisoner->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $sanQuentin,
                'charges' => 'Criminal syndicalism (California) — as an IWW organizer; arrested in Sacramento with I.W.W. organizer\'s books, literature, and dues receipts. Held to answer August 17, 1921 on a complaint issued by the District Attorney\'s office.',
                'convicted' => 'Yes — convicted of criminal syndicalism; sentenced October 20, 1921.',
                'sentence' => 'One to fourteen years at San Quentin prison.',
            ]);
            $case->setPartialDate('arrest_date', 1921, 6, 21);
            $case->setPartialDate('sentenced_date', 1921, 10, 20);
            $case->setPartialDate('incarceration_date', 1921, 10, 20);
            $case->save();

            $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
