<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Sarah Bad Heart Bull — mother of Wesley Bad Heart Bull, whose 1973
 * killing sparked the AIM protest at the Custer County Courthouse. She was
 * convicted of "riot where arson was committed" and actually served about
 * five months — one of the few people who served real jail time from the
 * Wounded Knee-era prosecutions (most convictions drew probation). Idempotent.
 */
final class AddSarahBadHeartBull extends Command
{
    protected $signature = 'prisoners:add-sarah-bad-heart-bull';

    protected $description = 'Add Sarah Bad Heart Bull (Custer courthouse protest; ~5 months served)';

    public function handle(): int
    {
        if (Prisoner::withUnderReview()->where('name', 'Sarah Bad Heart Bull')->exists()) {
            $this->warn('Sarah Bad Heart Bull already exists — skipping.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Sarah Bad Heart Bull',
                'first_name' => 'Sarah',
                'last_name' => 'Bad Heart Bull',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'South Dakota',
                'era' => '1970s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['American Indian Movement'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Sarah Bad Heart Bull (Oglala Lakota, 1929–2013) was the mother of Wesley Bad Heart Bull, whose January 1973 stabbing death — and the lenient manslaughter charge brought against the white man who killed him — sparked the American Indian Movement\'s February 6, 1973 protest at the Custer County Courthouse in South Dakota. When the protest turned into a riot (police struck her as she tried to enter the courthouse), she was charged and convicted of "riot where arson was committed" and sentenced to one to five years, serving about five months before being released on bail pending appeal. In a much-cited injustice, she — the grieving mother of the victim — served jail time while her son\'s killer served essentially none.',
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Riot where arson was committed — the February 6, 1973 AIM protest at the Custer County Courthouse, SD, over the lenient manslaughter charge against her son Wesley\'s killer',
                'arrest_date' => '1973-02-06',
                'convicted' => 'Convicted; conviction affirmed (State v. Bad Heart Bull, 257 N.W.2d 715, S.D. 1977)',
                'sentence' => 'One to five years (set at one year by the parole board); served about five months before release on bail pending appeal',
            ]);
        });

        $this->info('Added Sarah Bad Heart Bull.');

        return self::SUCCESS;
    }
}
