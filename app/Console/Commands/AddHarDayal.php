<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Lala Har Dayal, the Indian revolutionary and founder of the Ghadar
 * Party in San Francisco. On March 16, 1914 U.S. immigration authorities
 * arrested him for deportation as an "undesirable alien" (an anarchist); he was
 * freed on bail on March 27, 1914. Rather than face deportation he jumped bail
 * and fled to Switzerland on April 14, 1914, beginning a long exile in Europe.
 * His exile ended when he returned to the United States on November 7, 1938.
 * The imprisonment/exile is modeled as one case: the 1914 immigration arrest
 * (arrest → bail release) that flows into the 1914–1938 exile. Idempotent —
 * skips if a record with this name already exists.
 */
final class AddHarDayal extends Command
{
    protected $signature = 'prisoners:add-har-dayal';

    protected $description = 'Add Lala Har Dayal, Ghadar Party founder arrested for deportation in 1914 and exiled 1914–1938';

    public function handle(): int
    {
        DB::transaction(function () {
            $prisoner = Prisoner::withUnderReview()->where('name', 'Har Dayal')->first();
            if ($prisoner) {
                $this->warn('Skipped (already exists): Har Dayal');

                return;
            }

            $prisoner = Prisoner::create([
                'name' => 'Har Dayal',
                'first_name' => 'Har',
                'last_name' => 'Dayal',
                'gender' => 'Male',
                'race' => 'Asian',
                'state' => 'California',
                'era' => '1910s',
                'ideologies' => ['Indian independence', 'Anarchism'],
                'affiliation' => ['Ghadar Party'],
                'description' => 'Lala Har Dayal was an Indian revolutionary, scholar, and anti-colonial organizer who became a leading figure of the Indian independence movement in the United States. A lecturer at Stanford University, in 1913 he helped found the Ghadar Party in San Francisco, which called for an armed uprising against British rule in India. On March 16, 1914 he was arrested by U.S. immigration authorities for deportation as an "undesirable alien," on the grounds that he was an anarchist, and was freed on bail on March 27, 1914. Rather than submit to deportation he jumped bail and fled to Switzerland on April 14, 1914, beginning a long exile in Europe, where he continued to work for Indian independence. His exile ended when he returned to the United States on November 7, 1938. He died in Philadelphia the following year.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => true,
                'currently_in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $prisoner->setPartialDate('birthdate', 1884, 10, 14);
            $prisoner->setPartialDate('death_date', 1939, 3, 4);
            $prisoner->save();

            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Arrested by U.S. immigration authorities for deportation as an "undesirable alien," on the grounds that he was an anarchist, over his Ghadar Party organizing for Indian independence.',
                'convicted' => 'No — held for deportation and freed on bail; he jumped bail and fled the country before any deportation proceeding was completed.',
                'sentence' => 'Arrested March 16, 1914 and released on bail March 27, 1914. Facing deportation, he fled to Switzerland on April 14, 1914, beginning an exile in Europe that lasted until he returned to the United States on November 7, 1938.',
            ]);
            $case->setPartialDate('arrest_date', 1914, 3, 16);
            $case->setPartialDate('incarceration_date', 1914, 3, 16);
            $case->setPartialDate('release_date', 1914, 3, 27);
            $case->setPartialDate('in_exile_since', 1914, 4, 14);
            $case->setPartialDate('end_of_exile', 1938, 11, 7);
            $case->save();

            // Public-domain 1910s portrait, if bundled and not already set.
            $src = database_path('data/photos/har-dayal.jpg');
            if (is_file($src) && empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/har-dayal.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/har-dayal.jpg';
                $prisoner->save();
            }

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
