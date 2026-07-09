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
 * Adds the treason case for Thomas Wilson Dorr (1805–1854), leader of the
 * 1841–42 Dorr Rebellion in Rhode Island — the movement to replace the state's
 * restrictive colonial charter with a popularly ratified constitution extending
 * suffrage. Convicted of treason against the State of Rhode Island in 1844 and
 * sentenced to life at hard labor, he was imprisoned in June 1844 and freed a
 * year later under the General Assembly's Liberation Act (June 27, 1845).
 *
 * If a Dorr record already exists it is left as-is and only the case is added
 * (matched by institution so a re-run updates rather than duplicates it); if he
 * is missing, a full record is created. Idempotent.
 */
final class AddThomasDorrCase extends Command
{
    protected $signature = 'prisoners:add-thomas-dorr-case';

    protected $description = 'Add Thomas Wilson Dorr (Dorr Rebellion) and his 1844–45 treason imprisonment case';

    public function handle(): int
    {
        DB::transaction(function () {
            $prisoner = Prisoner::withUnderReview()
                ->whereIn('name', ['Thomas Wilson Dorr', 'Thomas Dorr'])
                ->first();

            if (! $prisoner) {
                $prisoner = new Prisoner([
                    'name' => 'Thomas Wilson Dorr',
                    'first_name' => 'Thomas',
                    'middle_name' => 'Wilson',
                    'last_name' => 'Dorr',
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => 'Rhode Island',
                    'era' => '1840s',
                    'ideologies' => ['Suffrage reform', 'Democratic reform'],
                    'affiliation' => ['Dorr Rebellion', "People's Party"],
                    'description' => 'Thomas Wilson Dorr (1805–1854) was a Rhode Island lawyer and reformer who led the 1841–42 Dorr Rebellion, an effort to replace the state\'s restrictive colonial charter — which limited voting to landowning men and their eldest sons — with a popularly ratified "People\'s Constitution" extending suffrage. Elected governor under that constitution by the reform movement, he attempted to install the new government and briefly led an armed force against the entrenched charter government. He was tried for treason against the State of Rhode Island, convicted in 1844, and sentenced to life imprisonment at hard labor. Imprisoned in June 1844, he was released a year later under a Liberation Act; his civil rights were restored in 1851 and the conviction formally annulled in 1854.',
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                $prisoner->setPartialDate('birthdate', 1805, 11, 5);
                $prisoner->setPartialDate('death_date', 1854, 12, 27);
                $prisoner->save();
                $this->info('Added prisoner: Thomas Wilson Dorr (slug: '.$prisoner->slug.')');
            } else {
                $this->line('Found existing prisoner: '.$prisoner->name.' — adding case only.');
            }

            $inst = Institution::firstOrCreate(
                ['name' => 'Rhode Island State Prison'],
                ['city' => 'Providence', 'state' => 'Rhode Island'],
            );

            $case = $prisoner->cases()->where('institution_id', $inst->id)->first()
                ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $inst->id,
                'charges' => 'Treason against the State of Rhode Island for leading the 1842 Dorr Rebellion — the People\'s Party\'s attempt to supplant the colonial-charter government with a popularly ratified constitution extending the vote.',
                'convicted' => 'Yes — convicted of treason (April 1844).',
                'sentence' => 'Life imprisonment at hard labor (sentenced June 25, 1844); freed by the General Assembly\'s Liberation Act on June 27, 1845.',
            ]);
            $case->setPartialDate('incarceration_date', 1844, 6, 25);
            $case->setPartialDate('release_date', 1845, 6, 27);
            $case->save();

            $this->info('Set treason case (incarcerated 1844-06-25, released 1845-06-27) at '.$inst->name.'.');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
