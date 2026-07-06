<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Ben Lewis Reitman (1879–1943) — the anarchist "hobo doctor," Emma Goldman's
 * lover and manager, and a birth-control and free-speech agitator. Twice jailed
 * for distributing birth-control literature under the Comstock laws: about 60
 * days in New York in 1916, and — after a December 12, 1916 arrest in Cleveland
 * and conviction on January 17, 1917 — a six-month term that was described as
 * the longest sentence served in the United States by a birth-control advocate.
 * In the 1912 San Diego free-speech fight he was abducted from his hotel by a
 * vigilante mob, driven to the county line, beaten, tarred, and branded — an
 * act of extrajudicial violence rather than a jailing, so it is recorded in the
 * biography but not as a case.
 *
 * Idempotent (skips by name). Sources: Wikipedia; PBS American Experience
 * ("Emma Goldman"); Emma Goldman documentary history / Reitman scholarship.
 */
class AddBenReitman extends Command
{
    protected $signature = 'prisoners:add-ben-reitman';

    protected $description = 'Add Ben Reitman, the anarchist "hobo doctor" jailed for birth-control advocacy';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Ben Reitman';

            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Ben',
                'middle_name' => 'Lewis',
                'last_name' => 'Reitman',
                'aka' => 'The Hobo Doctor; King of the Hoboes',
                'description' => 'Ben Lewis Reitman was an American anarchist and physician — the self-styled "hobo doctor" and "King of the Hoboes" — who ministered to tramps, sex workers, and the poor, and who became the lover and manager of the anarchist Emma Goldman, whom he met in 1908. Born in 1879 in St. Paul, Minnesota, to a Russian Jewish family and raised in Chicago, he was a tireless agitator for free speech and for the birth-control movement. During the 1912 San Diego free-speech fight he was seized from his hotel by a vigilante mob, driven to the county line, beaten, stripped, tarred, burned with cigars, and branded with the letters "I.W.W." Twice he was jailed for distributing birth-control information in violation of the Comstock laws: he served roughly 60 days in New York in 1916, and, after being arrested in Cleveland on December 12, 1916 and convicted on January 17, 1917, he served a six-month sentence that was described as the longest ever served in the United States by a birth-control advocate. He later returned to medicine and social work in Chicago, wrote "Sister of the Road" and "The Second Oldest Profession," and died of a heart attack in Chicago on November 16, 1943.',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Illinois',
                'era' => '1910s',
                'ideologies' => ['Anarchism', 'Free speech', 'Birth control'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            // Only the birth year is certain -> year precision (shows "1879").
            $prisoner->setPartialDate('birthdate', 1879);
            $prisoner->death_date = '1943-11-16';
            $prisoner->save();

            $nyWorkhouse = Institution::firstOrCreate(['name' => 'New York City Workhouse'], ['city' => 'New York', 'state' => 'New York']);
            $clevelandWorkhouse = Institution::firstOrCreate(['name' => 'Cleveland Workhouse (Warrensville)'], ['city' => 'Cleveland', 'state' => 'Ohio']);

            // 1916 New York birth-control case.
            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $nyWorkhouse->id,
                'charges' => 'Distributing birth-control information in violation of the Comstock laws (New York, 1916).',
                'convicted' => 'Yes — convicted of violating the anti-obscenity law by circulating birth-control literature.',
                'sentence' => 'Roughly 60 days in the New York City workhouse.',
                'imprisoned_for_days' => 60,
            ]);

            // Cleveland 1916–1917 birth-control case (the six-month term).
            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $clevelandWorkhouse->id,
                'charges' => 'Publicly advocating and distributing birth-control information in violation of the Comstock laws (Cleveland, Ohio).',
                'convicted' => 'Yes — arrested December 12, 1916 and convicted on January 17, 1917.',
                'sentence' => 'Six months — described as the longest sentence served in the United States by a birth-control advocate.',
                'arrest_date' => '1916-12-12',
                'sentenced_date' => '1917-01-17',
                'imprisoned_for_days' => 180,
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
