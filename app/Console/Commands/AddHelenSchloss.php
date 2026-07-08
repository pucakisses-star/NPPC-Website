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
 * Adds Helen Schloss, "the Red Nurse," a socialist public-health nurse jailed
 * for her leading role in the IWW-led 1912 Little Falls, New York textile
 * strike. Charged with inciting to riot, she was held about two weeks in the
 * Herkimer County jail and released on a $2,000 bond. Create-or-update by name;
 * idempotent.
 */
final class AddHelenSchloss extends Command
{
    protected $signature = 'prisoners:add-helen-schloss';

    protected $description = 'Add Helen Schloss ("the Red Nurse"; 1912 Little Falls textile strike)';

    public function handle(): int
    {
        $jail = Institution::firstOrCreate(
            ['name' => 'Herkimer County Jail'],
            ['city' => 'Herkimer', 'state' => 'New York']
        )->id;

        DB::transaction(function () use ($jail) {
            $prisoner = Prisoner::withUnderReview()->where('name', 'Helen Schloss')->first()
                ?? new Prisoner(['name' => 'Helen Schloss']);

            $prisoner->fill([
                'name' => 'Helen Schloss',
                'first_name' => 'Helen',
                'last_name' => 'Schloss',
                'gender' => 'Female',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1910s',
                'ideologies' => ['Socialism', 'Labor organizing'],
                'affiliation' => ['Industrial Workers of the World (IWW)'],
                'description' => 'Helen Schloss, known as "the Red Nurse," was a socialist public-health nurse who had worked among the poor on New York City\'s Lower East Side before being brought to Little Falls, New York, in May 1912 to treat a tuberculosis outbreak. Moved by the conditions of the mill workers, she resigned her post and threw herself into the IWW-led 1912 Little Falls textile strike, becoming one of its central figures alongside the organizer Matilda Rabinowitz. Considered a strike ringleader, she was arrested and charged with inciting to riot; the police brought in three doctors to "examine her sanity" before her lawyer secured her release. She was held about two weeks in the Herkimer County jail and freed on a $2,000 bond. She later became active in the women\'s suffrage movement.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $prisoner->save();

            $prisoner->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $jail,
                'charges' => 'Inciting to riot — for her leading role in the IWW-led 1912 Little Falls textile strike.',
                'convicted' => 'Charged with inciting to riot; released on a $2,000 bond. Three doctors were brought in to "examine her sanity" before her lawyer secured her release.',
                'sentence' => 'Held about two weeks in the Herkimer County jail, then released on a $2,000 bond.',
            ]);
            $case->setPartialDate('arrest_date', 1912, 10);
            $case->setPartialDate('incarceration_date', 1912, 10);
            $case->setPartialDate('release_date', 1912, 11);
            $case->save();

            $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
