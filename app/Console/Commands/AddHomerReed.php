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
 * armistice.
 *
 * Creates the record if absent; if it already exists, enriches it non-
 * destructively (fills only blank fields and a missing case). Idempotent.
 */
final class AddHomerReed extends Command
{
    protected $signature = 'prisoners:add-homer-reed';

    protected $description = 'Add or enrich Homer Morris Reed, WWI Church of the Brethren conscientious objector';

    private const DESCRIPTION = 'Homer Morris Reed (July 27, 1889 – February 24, 1951) was a Virginia member of the '
        .'Church of the Brethren — one of the historic peace churches — who refused to serve in the U.S. Army during '
        .'World War I because of his pacifist religious convictions. He was court-martialed for refusing military '
        .'service and sentenced to 25 years in prison. He entered the Fort Leavenworth Disciplinary Barracks in Kansas '
        .'on November 23, 1918, and was released on January 25, 1919, after roughly two months, as the government '
        .'commuted and released conscientious objectors following the armistice.';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Homer Morris Reed';
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();

            if (! $prisoner) {
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
                    'description' => self::DESCRIPTION,
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'currently_in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                $this->info('Created: '.$name);
            } else {
                $this->info('Found existing record — filling gaps: '.$name);
            }

            // Fill only blank scalar fields.
            $scalars = [
                'first_name' => 'Homer', 'middle_name' => 'Morris', 'last_name' => 'Reed',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Virginia', 'era' => '1910s',
                'description' => self::DESCRIPTION,
            ];
            foreach ($scalars as $field => $value) {
                if (blank($prisoner->{$field})) {
                    $prisoner->{$field} = $value;
                    $this->line("  set {$field}");
                }
            }
            if (empty($prisoner->ideologies)) {
                $prisoner->ideologies = ['Pacifism', 'Conscientious objection'];
                $this->line('  set ideologies');
            }
            if (empty($prisoner->affiliation)) {
                $prisoner->affiliation = ['Church of the Brethren'];
                $this->line('  set affiliation');
            }
            if (blank($prisoner->birthdate)) {
                $prisoner->birthdate = '1889-07-27';
                $this->line('  set birthdate 1889-07-27');
            }
            if (blank($prisoner->death_date)) {
                $prisoner->death_date = '1951-02-24';
                $this->line('  set death_date 1951-02-24');
            }
            $prisoner->save();

            // Case: create if none; otherwise fill blank fields on the first case.
            $institution = Institution::where('name', 'Fort Leavenworth Disciplinary Barracks')->first()
                ?? Institution::create([
                    'name' => 'Fort Leavenworth Disciplinary Barracks',
                    'city' => 'Fort Leavenworth',
                    'state' => 'Kansas',
                ]);

            $caseData = [
                'institution_id' => $institution->id,
                'charges' => 'Refusing military service as a conscientious objector during World War I '
                    .'(court-martialed for refusing to serve on religious/pacifist grounds).',
                'convicted' => 'Yes — court-martialed and convicted of refusing military service.',
                'sentence' => '25 years; released after about two months as conscientious objectors were commuted '
                    .'and released following the armistice.',
                'incarceration_date' => '1918-11-23',
                'release_date' => '1919-01-25',
                'imprisoned_for_days' => 63,
            ];

            $case = $prisoner->cases()->first();
            if (! $case) {
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $caseData));
                $this->line('  created case');
            } else {
                foreach ($caseData as $field => $value) {
                    if (blank($case->{$field})) {
                        $case->{$field} = $value;
                        $this->line("  set case.{$field}");
                    }
                }
                $case->save();
            }

            $this->info('Done: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
