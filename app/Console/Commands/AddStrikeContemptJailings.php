<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds two minor-tier strike-injunction-contempt jailings verified in The
 * Militant (1976–77): Tess Ewing (Boston school-bus drivers' strike) and Joe
 * O'Sullivan (1976 San Francisco craft strike). Other minor-tier names from the
 * archive sweep (the "Trenton 7," the Cambion/Polanski case) could not be
 * verified from the OCR and were deliberately left out. Idempotent.
 */
class AddStrikeContemptJailings extends Command {
    protected $signature = 'prisoners:add-strike-contempt-jailings';
    protected $description = 'Add Tess Ewing and Joe O\'Sullivan (1976–77 strike-injunction contempt jailings)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Tess Ewing', 'first' => 'Tess', 'last' => 'Ewing',
                'gender' => 'Female', 'state' => 'Massachusetts',
                'affiliation' => ['United Steelworkers (USWA)'],
                'institution' => ['name' => 'Framingham Women\'s Correctional Institution', 'city' => 'Framingham', 'state' => 'Massachusetts'],
                'bio' => 'Tess Ewing was a Boston school-bus driver and member of the United Steelworkers (USWA) who was jailed for contempt during the union\'s strike in the spring of 1977. When the drivers struck in April 1977, a court issued a no-strike injunction; Ewing was among those jailed for defying it, serving twelve days at the Framingham Women\'s Correctional Institution. She saw the jailings as part of an effort, as she put it, "to squeeze out a contract" from the union.',
                'charges' => 'Criminal contempt for violating a no-strike injunction during the April 1977 Boston school-bus drivers\' strike.',
                'convicted' => 'Jailed twelve days at the Framingham Women\'s Correctional Institution for contempt during the 1977 Boston school-bus drivers\' strike.',
                'sentence' => 'Twelve days in jail (1977).',
            ],
            [
                'name' => 'Joe O\'Sullivan', 'first' => 'Joe', 'last' => 'O\'Sullivan',
                'gender' => 'Male', 'state' => 'California',
                'affiliation' => ['United Brotherhood of Carpenters (San Francisco)'],
                'institution' => null,
                'bio' => 'Joe O\'Sullivan was a business agent for the Carpenters union in San Francisco during the 1976 strike by city craft workers — the roughly five-week walkout of seventeen craft unions against the City and County of San Francisco in the spring of 1976. After the strike was defeated, the city\'s administration prosecuted the union officials who had led it, and several were convicted of violating antistrike injunctions. O\'Sullivan, alone among them, did not appeal his sentence and served a four-day jail term. (In a parallel move, plumbers\' union chief Joseph Mazzola was ousted from the San Francisco Airport Commission for his role in the strike.)',
                'charges' => 'Criminal contempt for violating an antistrike injunction during the 1976 San Francisco city craft workers\' strike, in which he was a Carpenters union business agent.',
                'convicted' => 'Convicted of violating an antistrike injunction; served a four-day jail term (he did not appeal).',
                'sentence' => 'Four days in jail (1976).',
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $institutionId = null;
                if ($c['institution']) {
                    $inst = Institution::firstOrCreate(
                        ['name' => $c['institution']['name']],
                        ['city' => $c['institution']['city'], 'state' => $c['institution']['state']]
                    );
                    $institutionId = $inst->id;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => $c['gender'],
                    'state'          => $c['state'],
                    'era'            => '1970s',
                    'ideologies'     => ['Labor', 'Trade unionism'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $institutionId,
                    'charges'        => $c['charges'],
                    'convicted'      => $c['convicted'],
                    'sentence'       => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
