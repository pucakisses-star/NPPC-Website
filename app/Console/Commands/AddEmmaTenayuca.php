<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Emma Beatrice Tenayuca (1916–1999) — Mexican-American labor leader and
 * Communist organizer in San Antonio, Texas, best known as the 21-year-old
 * strike leader of the 1938 pecan shellers' strike, one of the largest labor
 * actions in Texas history. Arrested repeatedly from the age of sixteen for her
 * strike and organizing activity; roughly a thousand strikers, Tenayuca among
 * them, were jailed during the 1938 strike amid clubbing and tear gas. The
 * specific charges and dispositions of her many brief jailings are largely
 * undocumented and are not asserted here. Idempotent (skips by name).
 * Source: Wikipedia and the standard accounts of the San Antonio labor movement.
 */
class AddEmmaTenayuca extends Command
{
    protected $signature = 'prisoners:add-emma-tenayuca';

    protected $description = 'Add Emma Tenayuca, leader of the 1938 San Antonio pecan shellers\' strike';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Emma Tenayuca';

            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Emma',
                'middle_name' => 'Beatrice',
                'last_name' => 'Tenayuca',
                'description' => 'Emma Beatrice Tenayuca was a Mexican-American labor leader, union organizer, and Communist activist in San Antonio, Texas. Born on December 21, 1916 in San Antonio, she was drawn into the labor movement as a teenager and was first arrested at sixteen on the picket line of the 1933 Finck Cigar Company strike. She organized with the Workers Alliance of America — leading unemployed and relief demonstrations during the Depression — and joined the Communist Party USA. In 1938 she became the public leader of the pecan shellers\' strike, in which as many as 12,000 mostly Mexican-American workers walked out against wage cuts; the roughly two-month strike met with police clubbing, tear gas, and mass arrests, with about a thousand strikers jailed, Tenayuca among them. By her own account she was "arrested a number of times." In 1939, a Communist Party meeting she helped organize at the San Antonio Municipal Auditorium was besieged by a mob of thousands; she was accused of inciting a riot and disturbing the peace, though the charges were dropped. Blacklisted and unable to find work, she left San Antonio, later earned degrees in California and at Our Lady of the Lake University, and taught in the Harlandale school district until her 1982 retirement. She died in San Antonio on July 23, 1999.',
                'gender' => 'Female',
                'race' => 'Hispanic',
                'state' => 'Texas',
                'era' => '1930s',
                'ideologies' => ['Communism', 'Labor rights'],
                'affiliation' => ['Workers Alliance of America', 'Communist Party USA'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            $prisoner->birthdate = '1916-12-21';
            $prisoner->death_date = '1999-07-23';
            $prisoner->save();

            // First arrest: 1933 Finck Cigar Company strike.
            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Arrested on the picket line of the Finck Cigar Company strike in San Antonio, Texas, in 1933, at the age of sixteen — her first arrest.',
                'convicted' => 'Arrested during the 1933 strike; the disposition is not documented in the available sources.',
            ]);

            // 1938 pecan shellers' strike.
            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Jailed while leading the 1938 San Antonio pecan shellers\' strike, in which police used clubs and tear gas and imprisoned roughly a thousand strikers.',
                'convicted' => 'Arrested as strike leader; the specific charges and disposition are not documented in the available sources.',
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
