<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * 1) Adds Farouk Abdel-Muhti (Palestinian activist, post-9/11 INS
 *    detention, 2002–2004) to the prisoners table.
 *
 * 2) Fixes the `era` values on the 19 prisoners added in the
 *    AddCldcPrisoners batch — they were tagged with movement names
 *    ("Stop Cop City," "Pipeline resistance," etc.) but NPPC's
 *    convention is decade strings ("2020s," "2010s," etc.).
 */
final class AddFaroukAndFixCldcEras extends Command {
    protected $signature = 'archive:add-farouk-fix-eras';
    protected $description = 'Add Farouk Abdel-Muhti and fix CLDC batch eras to decades';

    public function handle(): int {
        $farouk = [
            'name' => 'Farouk Abdel-Muhti',
            'first_name' => 'Farouk',
            'last_name' => 'Abdel-Muhti',
            'description' => 'Palestinian human-rights activist and longtime WBAI radio programmer in New York City, born in Ramallah in 1947 and active in the U.S. from the late 1970s. Arrested April 26, 2002 by NYPD and INS officers at his Queens apartment without a warrant under the Department of Justice\'s post-9/11 "Absconder Apprehension Initiative" targeting Middle Eastern immigrants with outstanding deportation orders. He was never charged with any crime; the government held him on a 1995 deportation order. Over the next two years he was moved between detention centers in New Jersey, Pennsylvania, and Georgia, spending approximately eight months in solitary confinement. Released April 12, 2004 after sustained organizing by supporters and his attorneys. Two months later, on July 21, 2004, he collapsed and died at age 57 minutes after finishing a speech at the Ethical Culture Society in Philadelphia. His case is the subject of the documentary "Enemy Alien" (Third World Newsreel).',
            'state' => 'New York',
            'race' => 'Arab',
            'gender' => 'Male',
            'birthdate' => '1947-01-01',
            'death_date' => '2004-07-21',
            'ideologies' => ['Palestinian liberation', 'Anti-imperialist'],
            'era' => '2000s',
            'in_custody' => false,
            'released' => true,
            'cases' => [
                [
                    'charges' => 'No criminal charges; immigration detention on a 1995 deportation order.',
                    'arrest_date' => '2002-04-26',
                    'release_date' => '2004-04-12',
                    'sentence' => 'Approximately 2 years in INS/ICE detention without charge; ~8 months in solitary confinement.',
                ],
            ],
        ];

        $exit = $this->call('prisoner:add', ['json' => json_encode($farouk)]);
        if ($exit === self::SUCCESS) {
            $this->info('ADD: Farouk Abdel-Muhti');
        } else {
            $this->warn('Farouk Abdel-Muhti not added (likely already exists).');
        }

        $eraFixes = [
            'Manuel Esteban Paez Terán' => '2020s',
            'Mahmoud Khalil' => '2020s',
            'Caleb Freestone' => '2020s',
            'Tarek Mehanna' => '2010s',
            'Loren Reed' => '2020s',
            'Jeffrey Luers' => '2000s',
            'Tre Arrow' => '2000s',
            'Lori Berenson' => '1990s',
            'Jerry Koch' => '2010s',
            'Ken Ward' => '2010s',
            'Leonard Higgins' => '2010s',
            'Emily Johnston' => '2010s',
            'Annette Klapstein' => '2010s',
            'Sam Jessup' => '2010s',
            'Benjamin Joldersma' => '2010s',
            'Ayla King' => '2020s',
            'Amber Smith-Stewart' => '2020s',
            'Annarella Rivera' => '2020s',
            'Gabriela Oropesa' => '2020s',
        ];

        $updated = 0;
        $missing = 0;
        foreach ($eraFixes as $name => $era) {
            $p = Prisoner::query()->where('name', $name)->first();
            if (! $p) {
                $this->warn("MISSING: {$name}");
                $missing++;
                continue;
            }
            if ($p->era === $era) {
                continue;
            }
            $p->era = $era;
            $p->save();
            $this->info("ERA: {$name} → {$era}");
            $updated++;
        }

        $this->info("Done — era updates: {$updated}, missing: {$missing}.");
        return self::SUCCESS;
    }
}
