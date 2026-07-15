<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fills the empty "Keyondre Robinson" stub — an 18-year-old from Buffalo, New
 * York prosecuted after the May 30, 2020 George Floyd protests for throwing a
 * water bottle that struck a Deputy United States Marshal in the face outside
 * the Robert H. Jackson U.S. Courthouse. He pleaded guilty on December 22, 2020
 * to assaulting a federal officer; the charge carried up to a year in prison,
 * and he was reported to have received a year of supervised release (no
 * confirmed term of incarceration).
 *
 * Create-or-update by slug; rebuilds his single case. Idempotent.
 */
class FillKeyondreRobinson extends Command
{
    protected $signature = 'prisoners:fill-keyondre-robinson';

    protected $description = 'Fill Keyondre Robinson (2020 Buffalo George Floyd protest, assault on a federal officer)';

    public function handle(): int
    {
        DB::transaction(function () {
            $r = Prisoner::withUnderReview()->where('slug', 'keyondre-robinson')->first()
                ?? new Prisoner(['name' => 'Keyondre Robinson']);

            $r->fill([
                'name' => 'Keyondre Robinson',
                'first_name' => 'Keyondre',
                'last_name' => 'Robinson',
                'gender' => 'Male',
                'state' => 'New York',
                'era' => '2020s',
                'ideologies' => ['Anti-racism', 'Racial justice'],
                'affiliation' => [],
                'description' => 'Keyondre Robinson, an 18-year-old from Buffalo, New York, was among those federally prosecuted after the May 30, 2020 George Floyd protests in Buffalo. Outside the Robert H. Jackson United States Courthouse, he threw a water bottle that struck a Deputy United States Marshal in the face. Charged with assaulting a federal officer, he pleaded guilty on December 22, 2020; the charge carried a maximum of one year in prison, and he was reported to have been sentenced to a year of supervised release.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $r->save();

            $r->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $r->id]);
            $case->fill([
                'prisoner_id' => $r->id,
                'charges' => 'Assaulting, resisting, or impeding a federal officer — for throwing a water bottle that struck a Deputy United States Marshal in the face during the George Floyd protests outside the Robert H. Jackson U.S. Courthouse in Buffalo on May 30, 2020.',
                'convicted' => 'Yes — pleaded guilty on December 22, 2020.',
                'sentence' => 'The charge carried up to one year in prison and a $100,000 fine; he was reported to have been sentenced (in 2021) to a year of supervised release.',
            ]);
            $case->save();

            $this->info('Filled Keyondre Robinson (slug: '.$r->slug.').');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
