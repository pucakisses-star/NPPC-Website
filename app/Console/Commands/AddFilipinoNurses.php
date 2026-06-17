<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Filipina Narciso and Leonora Perez, the Filipina immigrant nurses
 * prosecuted in the 1975 Ann Arbor VA hospital poisoning case — convicted in 1977
 * of poisoning/conspiracy (acquitted of murder), verdicts set aside for
 * prosecutorial misconduct, and all charges dropped in 1978. Widely condemned as
 * a racist frame-up of immigrant nurses. Surfaced in the Militant/Workers Vanguard
 * readings; sourced to Wikipedia ("Ann Arbor Hospital murders"). Idempotent.
 */
class AddFilipinoNurses extends Command {
    protected $signature = 'prisoners:add-filipino-nurses';
    protected $description = 'Add Filipina Narciso and Leonora Perez (1975 Ann Arbor VA nurses frame-up)';

    private const SHARED = <<<'TXT'
Over several months in 1975, an unusual cluster of patients at the hospital suffered sudden respiratory failure — attributed to the unauthorized injection of the paralytic drug Pavulon into their IV lines, with ten deaths. After an FBI investigation, the two nurses, both recent immigrants, were charged in 1976; the patients who had identified them died before trial.

At a 1977 trial in U.S. District Court, the nurses were acquitted of murder but found guilty of poisoning and conspiracy to poison. The trial was marred by overt anti-Filipino racism — a prosecution witness called them "slant-eyed bitches," and the government floated the notion of a "nationwide conspiracy of Filipino nurses to murder veterans." Trial judge Philip Pratt set the guilty verdicts aside, finding the jury had been swayed by the prosecution's prejudicial conduct, and the government dropped all charges in 1978 after a new U.S. attorney concluded the public did not support the prosecution. No other suspect was ever charged, and the case became a landmark rallying point for Filipino-American and immigrant communities.
TXT;

    public function handle(): int {
        DB::transaction(function () {
            $defendants = [
                ['name' => 'Filipina Narciso', 'first' => 'Filipina', 'last' => 'Narciso', 'other' => 'Leonora Perez'],
                ['name' => 'Leonora Perez',   'first' => 'Leonora',  'last' => 'Perez',   'other' => 'Filipina Narciso'],
            ];

            foreach ($defendants as $d) {
                if (Prisoner::where('name', $d['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$d['name']}");
                    continue;
                }

                $bio = "{$d['name']} was one of two Filipina immigrant nurses at the Veterans Administration Medical Center in Ann Arbor, Michigan — with co-defendant {$d['other']} — prosecuted in a case widely condemned as a racist frame-up.\n\n".self::SHARED;

                $prisoner = Prisoner::create([
                    'name'           => $d['name'],
                    'first_name'     => $d['first'],
                    'last_name'      => $d['last'],
                    'description'    => $bio,
                    'gender'         => 'Female',
                    'race'           => 'Asian',
                    'state'          => 'Michigan',
                    'era'            => '1970s',
                    'ideologies'     => [],
                    'affiliation'    => [],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => 'Poisoning and conspiracy to poison patients at the Ann Arbor Veterans Administration hospital (acquitted of murder) — arising from the 1975 deaths of patients injected with the paralytic drug Pavulon. Charged in 1976.',
                    'convicted'   => 'Convicted in 1977 of poisoning and conspiracy (acquitted of murder) in U.S. District Court for the Eastern District of Michigan; the guilty verdicts were set aside by Judge Philip Pratt for prejudicial prosecutorial conduct, and the government dropped all charges in 1978.',
                    'sentence'    => 'Convictions vacated before sentencing; all charges dropped in 1978.',
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
