<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Homer Morris Reed (1889–1951) — a Virginia member of the Church of the
 * Brethren (one of the historic peace churches) who refused military service
 * as a conscientious objector during World War I, was court-martialed, and
 * received a 25-year sentence. He entered the Fort Leavenworth Disciplinary
 * Barracks on November 23, 1918 and was released January 25, 1919 (~63 days)
 * as the government commuted and released conscientious objectors after the
 * armistice. Idempotent (skips by name).
 */
final class AddHomerReed extends Command
{
    protected $signature = 'prisoners:add-homer-reed';

    protected $description = 'Add Homer Morris Reed, WWI Church of the Brethren conscientious objector';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Homer Morris Reed';

            if (Prisoner::withUnderReview()->where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Homer',
                'middle_name' => 'Morris',
                'last_name' => 'Reed',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Virginia',
                'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'affiliation' => ['Church of the Brethren'],
                'description' => 'Homer Morris Reed (July 27, 1889 – February 24, 1951) was a Virginia member of the '
                    .'Church of the Brethren — one of the historic peace churches — who refused to serve in the U.S. '
                    .'Army during World War I because of his pacifist religious convictions. He was court-martialed for '
                    .'refusing military service and sentenced to 25 years in prison. He entered the Fort Leavenworth '
                    .'Disciplinary Barracks in Kansas on November 23, 1918, and was released on January 25, 1919, after '
                    .'roughly two months, as the government commuted and released conscientious objectors following the '
                    .'armistice.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'currently_in_exile' => false,
                'awaiting_trial' => false,
            ]);

            $prisoner->birthdate = '1889-07-27';
            $prisoner->death_date = '1951-02-24';
            $prisoner->save();

            $institution = Institution::where('name', 'Fort Leavenworth Disciplinary Barracks')->first()
                ?? Institution::create([
                    'name' => 'Fort Leavenworth Disciplinary Barracks',
                    'city' => 'Fort Leavenworth',
                    'state' => 'Kansas',
                ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $institution->id,
                'charges' => 'Refusing military service as a conscientious objector during World War I '
                    .'(court-martialed for refusing to serve on religious/pacifist grounds).',
                'convicted' => 'Yes — court-martialed and convicted of refusing military service.',
                'sentence' => '25 years; released after about two months as conscientious objectors were commuted '
                    .'and released following the armistice.',
                'incarceration_date' => '1918-11-23',
                'release_date' => '1919-01-25',
                'imprisoned_for_days' => 63,
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
