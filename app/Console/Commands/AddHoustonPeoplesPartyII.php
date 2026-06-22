<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Houston's People's Party II and the July 26, 1970 Dowling Street police attack.
 * Researched after The Black Panther (Jan. 5, 1974) named Houston defendants
 * charged with assault with intent to kill police; verification tied the case to
 * the police assassination of People's Party II leader Carl Hampton:
 *
 *  - Carl Hampton — founder and leader of People's Party II (a Houston Black
 *    Power group allied with the Black Panther Party), killed by Houston police
 *    snipers on July 26, 1970.
 *  - Bartee Haile — a John Brown Revolutionary Party member wounded in the same
 *    attack and charged in connection with it.
 *
 * (Four other names in the newspaper account could not be independently verified
 * and were not added.) Idempotent: skips any name already present.
 */
final class AddHoustonPeoplesPartyII extends Command
{
    protected $signature = 'prisoners:add-houston-pp2';

    protected $description = 'Add Carl Hampton (People\'s Party II) and Bartee Haile';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    if (! empty($c['institution_name'])) {
                        $inst = Institution::firstOrCreate(
                            ['name' => $c['institution_name']],
                            ['city' => $c['institution_city'] ?? null, 'state' => $c['institution_state'] ?? null],
                        );
                        $c['institution_id'] = $inst->id;
                    }
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        return [
            [
                'name' => 'Carl Hampton',
                'first_name' => 'Carl',
                'last_name' => 'Hampton',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Texas',
                'era' => '1970s',
                'birthdate' => '1948-12-17',
                'death_date' => '1970-07-26',
                'ideologies' => ['Black Power', 'Black liberation', 'Revolutionary socialism'],
                'affiliation' => ['People\'s Party II'],
                'in_custody' => false,
                'released' => false,
                'awaiting_trial' => false,
                'description' => 'Carl Hampton, born December 17, 1948, was the founder and leader of People\'s Party II, a Houston Black Power organization allied with the Black Panther Party that ran community survival programs and distributed The Black Panther. After weeks of Houston police harassment of members selling the paper on Dowling Street, police snipers positioned on a nearby church roof opened fire on July 26, 1970; Hampton was killed at the age of 21, and several others — including John Brown Revolutionary Party member Bartee Haile — were wounded. A grand jury declined to indict the officers, and no one was ever held accountable for his death. James Aaron took over the leadership of People\'s Party II afterward.',
                'cases' => [[
                    'institution_state' => 'Texas',
                    'charges' => 'Killed by Houston police snipers at Dowling and Tuam streets on July 26, 1970, during the police siege of People\'s Party II; the officers were never indicted',
                    'death_in_custody_date' => '1970-07-26',
                ]],
            ],
            [
                'name' => 'Bartee Haile',
                'first_name' => 'Bartee',
                'last_name' => 'Haile',
                'aka' => 'Roy Bartee Haile',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Texas',
                'era' => '1970s',
                'ideologies' => ['Revolutionary socialism', 'Anti-imperialism'],
                'affiliation' => ['John Brown Revolutionary Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Bartee Haile was a white Houston revolutionary, a member of the John Brown Revolutionary Party allied with Carl Hampton\'s People\'s Party II. On July 26, 1970 he was at Hampton\'s side when Houston police snipers opened fire on Dowling Street, killing Hampton; Haile was wounded but survived and was charged with the attempted murder of a police officer in connection with the shootout. The Black Panther (January 5, 1974) later reported him among Houston activists charged with assault with intent to kill police and facing sentences of two years to life after police attacks on the city\'s radical movement. He went on to become a Texas writer and historian.',
                'cases' => [[
                    'institution_state' => 'Texas',
                    'charges' => 'Attempted murder of a police officer / assault with intent to kill police, in connection with the July 26, 1970 Dowling Street shootout (in which he was wounded and Carl Hampton was killed) and later Houston police attacks on protesters',
                ]],
            ],
        ];
    }
}
