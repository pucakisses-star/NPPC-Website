<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Carole A. Graves, president of the Newark Teachers Union (AFT Local 481),
 * jailed for contempt during the landmark 1970 and 1971 Newark teachers' strikes.
 * Surfaced from The Militant's labor coverage; sourced to Wikipedia and the
 * history "The Newark Teacher Strikes: Hopes on the Line." Idempotent.
 */
class AddCaroleGraves extends Command {
    protected $signature = 'prisoners:add-carole-graves';
    protected $description = 'Add Carole A. Graves (Newark Teachers Union; jailed in the 1970/1971 strikes)';

    private const BIO = <<<'TXT'
Carole A. Graves was the president of the Newark Teachers Union (American Federation of Teachers Local 481) during the two landmark Newark teachers' strikes of 1970 and 1971 — among the most bitter and racially charged labor battles in American education. Elected president in 1968, Graves led a three-week strike in 1970 and an eleven-week strike in 1971; both defied court injunctions, and the walkouts unfolded amid a fraught conflict between the union and a Black-led community-control movement in the city.

Graves was jailed for contempt of court for her role in the strikes — serving roughly three months in the Essex County Jail over the 1970 strike and six months over the 1971 strike, during which more than 200 Newark teachers were also jailed. The 1971 settlement nonetheless won significant gains in teachers' rights and working conditions.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Carole Graves')->exists()) {
            $this->error('Carole Graves already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $jail = Institution::firstOrCreate(
                ['name' => 'Essex County Jail'],
                ['city' => 'Newark', 'state' => 'New Jersey']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Carole Graves',
                'first_name'     => 'Carole',
                'last_name'      => 'Graves',
                'description'    => self::BIO,
                'gender'         => 'Female',
                'race'           => 'Black',
                'state'          => 'New Jersey',
                'era'            => '1970s',
                'ideologies'     => ['Labor', 'Trade unionism'],
                'affiliation'    => ['Newark Teachers Union (AFT Local 481)'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $jail->id,
                'charges'        => 'Criminal contempt of court for leading the Newark Teachers Union (AFT Local 481) in strikes that defied court injunctions — the three-week 1970 strike and the eleven-week 1971 strike, the latter one of the most bitter and racially charged teachers\' strikes in U.S. history (more than 200 teachers were jailed).',
                'convicted'      => 'Jailed for contempt: roughly three months in the Essex County Jail over the 1970 strike, and six months over the 1971 strike.',
                'sentence'       => 'About three months (1970) and six months (1971) in the Essex County Jail.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
