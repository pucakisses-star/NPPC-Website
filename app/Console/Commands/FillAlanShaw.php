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
 * Fills out Alan Shaw's Oklahoma criminal-syndicalism case (1940), also recording
 * his alias Alan Lifshutz. Documented timeline:
 *   - Arrested August 17, 1940; held in the Oklahoma County Jail on $20,000 bond
 *     (he spent his 22nd birthday there).
 *   - Convicted of criminal syndicalism December 9, 1940; sentenced to ten years
 *     in the state penitentiary plus a $5,000 fine.
 *   - Conviction reversed by the Oklahoma Criminal Court of Appeals on
 *     February 17, 1943 (Shaw v. State); rehearing denied May 19, 1943.
 *
 * He did NOT serve until 1943: a January 21, 1941 New Masses notice for a
 * January 22, 1941 New York defense rally advertises his "victim's account,"
 * showing he was out of custody (on appeal bond) by early 1941. The exact
 * release date is not documented, so no release date is stored; the minimum
 * proven confinement is about 114–115 days (Aug 17 – at least Dec 9, 1940).
 *
 * Also sets his birth date (November 13, 1918) and attaches his portrait —
 * a ~1944 newspaper clipping cropped head-and-shoulders, in photos/nonfree/
 * under a fair-use / memorial rationale (see CREDITS-nonfree.md).
 * Idempotent — rebuilds the single case.
 */
final class FillAlanShaw extends Command
{
    protected $signature = 'prisoners:fill-alan-shaw';

    protected $description = 'Fill Alan Shaw (alias Alan Lifshutz) 1940 Oklahoma criminal-syndicalism case';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Alan Shaw')->first();
        if (! $prisoner) {
            // No base record yet — create it so a single run of this command
            // produces the full, visible entry rather than erroring out.
            $prisoner = Prisoner::create([
                'name' => 'Alan Shaw',
                'first_name' => 'Alan',
                'last_name' => 'Shaw',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Oklahoma',
                'era' => '1940s',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party USA'],
                'description' => 'Alan Shaw (also known as Alan Lifshutz) was the Oklahoma City secretary of the '
                    .'Communist Party who was prosecuted under Oklahoma\'s criminal-syndicalism law during the 1940 '
                    .'Oklahoma City "red raids." Arrested on August 17, 1940 — he spent his 22nd birthday in the '
                    .'Oklahoma County Jail on $20,000 bond — he was convicted of criminal syndicalism on December 9, '
                    .'1940 and sentenced to ten years in the state penitentiary plus a $5,000 fine. Released on appeal '
                    .'bond by early 1941, he spoke at a New York defense rally on January 22, 1941. The Oklahoma '
                    .'Criminal Court of Appeals reversed his conviction in Shaw v. State on February 17, 1943.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'currently_in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $this->info('Created base record: Alan Shaw');
        }

        $jail = Institution::firstOrCreate(
            ['name' => 'Oklahoma County Jail'],
            ['city' => 'Oklahoma City', 'state' => 'Oklahoma']
        )->id;

        DB::transaction(function () use ($prisoner, $jail) {
            if (empty($prisoner->aka)) {
                $prisoner->aka = 'Alan Lifshutz';
            }
            $prisoner->setPartialDate('birthdate', 1918, 11, 13);
            $prisoner->save();

            $prisoner->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $jail,
                'charges' => 'Criminal syndicalism (Oklahoma) — as the Oklahoma City secretary of the Communist Party. Arrested on a complaint sworn August 17, 1940.',
                'convicted' => 'Yes — convicted of criminal syndicalism on December 9, 1940; the conviction was reversed by the Oklahoma Criminal Court of Appeals in 1943 (Shaw v. State, decided February 17, 1943; rehearing denied May 19, 1943).',
                'sentence' => 'Ten years in the state penitentiary plus a $5,000 fine. Held in the Oklahoma County Jail on $20,000 bond from his August 17, 1940 arrest through his December 1940 trial (minimum proven confinement about 114–115 days). He was released on appeal bond by early 1941 — he spoke at a New York defense rally on January 22, 1941 — so he did not remain imprisoned until the 1943 reversal; the exact release date is not documented.',
            ]);
            $case->setPartialDate('arrest_date', 1940, 8, 17);
            $case->setPartialDate('incarceration_date', 1940, 8, 17);
            $case->setPartialDate('sentenced_date', 1940, 12, 9);
            // No release_date: exact date undocumented (out on appeal bond by ~Jan 22, 1941).
            $case->save();
        });

        $src = database_path('data/photos/nonfree/alan-shaw.jpg');
        if (is_file($src)) {
            if (empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/alan-shaw.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/alan-shaw.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/alan-shaw.jpg');
            } else {
                $this->info('Portrait already set; left as-is.');
            }
        } else {
            $this->warn('Portrait file not found at database/data/photos/nonfree/alan-shaw.jpg — case set, photo skipped.');
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled Alan Shaw (alias Alan Lifshutz) criminal-syndicalism case (b. Nov 13, 1918).');

        return self::SUCCESS;
    }
}
