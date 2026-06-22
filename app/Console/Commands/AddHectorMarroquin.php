<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Héctor Marroquín, the Mexican-born Young Socialist Alliance / Socialist
 * Workers Party activist whose 11-year fight against political deportation became
 * a major asylum and civil-liberties cause (he said he faced torture or death in
 * Mexico for his political activity). Surfaced from The Militant's "Free Hector
 * Marroquin" coverage; sourced to The Militant, UPI, and Marroquin-Manriquez v.
 * INS (3d Cir. 1982). A deportation/asylum case rather than a criminal conviction.
 */
class AddHectorMarroquin extends Command {
    protected $signature = 'prisoners:add-hector-marroquin';
    protected $description = 'Add Héctor Marroquín (SWP immigrant; 11-year political-deportation fight)';

    private const BIO = <<<'TXT'
Héctor Marroquín was a Mexican-born activist whose decade-long fight against deportation became a major political-asylum and civil-liberties cause championed by the U.S. left. Born in Matamoros, Mexico, he came to the United States in 1974 and became active in the antideportation and trade-union movements and a member of the Young Socialist Alliance and the Socialist Workers Party. He said he had fled political persecution in Mexico — where he was wanted in connection with the student and leftist movements — and feared torture or death if he were returned.

In September 1977, returning from a visit to Mexico, Marroquín was detained by the Immigration and Naturalization Service at Eagle Pass, Texas and charged with entering the country illegally; he applied for political asylum. A Héctor Marroquín Defense Committee organized national support, and his testimony featured in the SWP's landmark Socialist Workers Party v. Attorney General lawsuit over FBI and government surveillance of the left. After roughly eleven years of government efforts to deport him for his political activity, Marroquín ultimately won the right to remain in the United States.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Hector Marroquin')->exists()) {
            $this->error('Hector Marroquin already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'Hector Marroquin',
                'first_name'     => 'Hector',
                'last_name'      => 'Marroquin',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Hispanic',
                'state'          => 'New York',
                'era'            => '1970s',
                'ideologies'     => ['Socialism', 'Immigrant rights'],
                'affiliation'    => ['Young Socialist Alliance', 'Socialist Workers Party', 'Hector Marroquin Defense Committee'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'Immigration charges (illegal entry) and a deportation order — the U.S. government sought for some eleven years to deport him to Mexico, where he said he faced torture or death for his political activity. Detained by the INS at Eagle Pass, Texas in September 1977; he sought political asylum.',
                'convicted'   => 'Not a criminal conviction — a political deportation and asylum fight. After roughly eleven years of attempted deportation, Marroquín won the right to remain in the United States.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
