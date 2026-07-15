<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Tracy Roy Rogers — former secretary of the Communist Party of Colorado and
 * husband of Jane Rogers (the party treasurer whose own contempt case reached
 * the U.S. Supreme Court as Rogers v. United States, 340 U.S. 367). He was the
 * sixth of the Colorado Communists jailed for contempt of a federal grand jury
 * in Denver in 1948. Per The Colorado Statesman (Dec 11, 1948): on Monday,
 * December 6, 1948 U.S. District Judge J. Foster Symes sentenced him to an
 * indeterminate jail term for refusing to answer the grand jury's questions
 * about Communist Party activity in the state; the next day the U.S. 10th
 * Circuit Court of Appeals in Wichita set a $1,000 appeal bond, on which he was
 * freed. Idempotent (skips by name).
 */
class AddTracyRogers extends Command
{
    protected $signature = 'prisoners:add-tracy-rogers';

    protected $description = 'Add Tracy Roy Rogers, the sixth Colorado Communist jailed for grand-jury contempt in Denver (1948)';

    public function handle(): int
    {
        $jail = Institution::firstOrCreate(
            ['name' => 'Denver County Jail'],
            ['city' => 'Denver', 'state' => 'Colorado'],
        );

        DB::transaction(function () use ($jail) {
            $name = 'Tracy Roy Rogers';

            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Tracy',
                'middle_name' => 'Roy',
                'last_name' => 'Rogers',
                'description' => 'Tracy Roy Rogers was the former secretary of the Communist Party of Colorado and the husband of Jane Rogers, the party treasurer whose own contempt case reached the U.S. Supreme Court as Rogers v. United States. He was among the Colorado Communists jailed for contempt of court in Denver in 1948 for refusing to answer a federal grand jury\'s questions about the activities of the Communist Party in the state. On Monday, December 6, 1948, U.S. District Judge J. Foster Symes sentenced him to an indeterminate jail term for contempt; the following day the U.S. 10th Circuit Court of Appeals in Wichita, Kansas set a $1,000 appeal bond, on which he was freed. The grand-jury contempt jailings of the Colorado party leaders were part of the federal pursuit that would later produce the Smith Act prosecutions (Bary v. United States) in the 1950s.',
                'gender' => 'Male',
                'state' => 'Colorado',
                'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech'],
                'affiliation' => ['Communist Party of Colorado'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $jail->id,
                'charges' => 'Contempt of court — for refusing to answer a federal grand jury\'s questions about Communist Party activity in Colorado.',
                'convicted' => 'Yes — adjudged in contempt and sentenced to an indeterminate jail term by U.S. District Judge J. Foster Symes.',
                'sentence' => 'Indeterminate jail term for civil contempt; freed the next day on a $1,000 appeal bond set by the 10th Circuit Court of Appeals.',
                'judge' => 'U.S. District Judge J. Foster Symes',
                'sentenced_date' => '1948-12-06',
                'incarceration_date' => '1948-12-06',
                'release_date' => '1948-12-07',
                'imprisoned_for_days' => 1,
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
