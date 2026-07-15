<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Tom Csekey — a U.S. Navy enlisted man and co-founder of GI's Against Fascism,
 * the first antiwar/resistance group organized within the U.S. Navy during the
 * Vietnam War (San Diego, 1969; publisher of the underground paper Duck Power).
 * Court-martialed for distributing a banned antiwar publication and sentenced
 * to twenty days' hard labor in the Navy brig.
 *
 * Create-or-update by name; rebuilds the single case. Idempotent.
 */
final class AddTomCsekey extends Command
{
    protected $signature = 'prisoners:add-tom-csekey';

    protected $description = "Add Tom Csekey (GI's Against Fascism co-founder jailed for antiwar literature, 1969)";

    public function handle(): int
    {
        $bio = "Tom Csekey was a U.S. Navy enlisted man and a co-founder of GI's Against Fascism, the first antiwar and resistance group organized within the U.S. Navy during the Vietnam War. Formed at San Diego in 1969 out of sailors' grievances over living conditions and institutional racism, the group published the underground newspaper Duck Power and later merged with antiwar marines to form the Movement for a Democratic Military. Csekey was arrested for distributing a banned publication and tried by summary court-martial; convicted, he was demoted in rank and sentenced to twenty days of hard labor in the Navy brig, then transferred to Yuma, Arizona. He ultimately received an honorable discharge.";

        DB::transaction(function () use ($bio) {
            $prisoner = Prisoner::withUnderReview()->where('name', 'Tom Csekey')->first()
                ?? new Prisoner(['name' => 'Tom Csekey']);

            $prisoner->fill([
                'name' => 'Tom Csekey',
                'first_name' => 'Tom',
                'last_name' => 'Csekey',
                'gender' => 'Male',
                'era' => '1960s',
                'ideologies' => ['Anti-War', 'GI resistance', 'Anti-fascism'],
                'affiliation' => ["GI's Against Fascism", 'Movement for a Democratic Military'],
                'description' => $bio,
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
                'charges' => 'Distributing a banned/unauthorized publication (antiwar literature) as a U.S. Navy sailor.',
                'convicted' => 'Yes — summary court-martial.',
                'sentence' => "Demoted in rank and sentenced to twenty days' hard labor in the Navy brig; transferred to Yuma, Arizona, and later honorably discharged.",
            ]);
            $case->setPartialDate('incarceration_date', 1969);
            $case->save();

            $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
