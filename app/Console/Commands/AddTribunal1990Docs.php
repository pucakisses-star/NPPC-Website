<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds two documents from the December 7, 1990 International Tribunal on
 * U.S. political prisoners, plus the related 1992 speech by Rita "Bo" Brown
 * on white North American political prisoners. Imports new prisoners
 * surfaced from both documents that are not already in NPPC.
 *
 * Documents:
 *  1. "Chicano Political Prisoners" — Committee to Free Alvaro Hernandez &
 *     Alberto Aranda (Movimiento de Liberacion Nacional Mexicano), submitted
 *     to the Dec 7 1990 Special International Tribunal.
 *  2. "The White North American Political Prisoners in the U.S." — speech
 *     by Rita "Bo" Brown at the International Tribunal, October 3, 1992.
 */
final class AddTribunal1990Docs extends Command {
    protected $signature = 'archive:add-tribunal-1990-docs';
    protected $description = 'Add the Dec 1990 / Oct 1992 International Tribunal documents and surface new prisoners';

    public function handle(): int {
        $records = [
            'chicano-political-prisoners-tribunal-1990' => [
                'title' => 'Chicano Political Prisoners — Committee to Free Alvaro Hernandez & Alberto Aranda (Dec 7, 1990 Tribunal)',
                'description' => 'Submission of the Committee to Free Alvaro Hernandez and Alberto Aranda (Movimiento de Liberacion Nacional Mexicano) to the December 7, 1990 Special International Tribunal on U.S. political prisoners, held on International Human Rights Day. Documents the cases of two Chicano political prisoners held in the Texas Department of Criminal Justice: Alvaro Hernandez (life sentence out of Pecos County, eight consecutive years in solitary as retaliation for his role in Ruiz v. Estelle and other prisoner-rights litigation) and Alberto Aranda (18 years for a $1.50 robbery, plus 40 years added in 1989 for an alleged assault on a guard). Includes essays by both prisoners written from their cells in Huntsville. Issued under the framework that the Chicano-Mexicano Nation is a captive nation, with Hernandez and Aranda as members of liberationist forces against Yankee colonialism.',
                'record_type' => 'document',
                'source_format' => 'pamphlet',
                'file' => '/pdfs/tribunals/chicano-pp-tribunal-1990-12-07.pdf',
                'collection' => 'International Tribunal on U.S. Political Prisoners',
                'authors' => 'Alvaro Hernandez; Alberto Aranda; Committee to Free Alvaro Hernandez and Alberto Aranda',
                'publisher' => 'Movimiento de Liberacion Nacional Mexicano',
                'year' => 1990,
                'date' => '1990-12-07',
                'subjects' => ['Political Prisoners', 'Chicano Liberation', 'Texas', 'Ruiz v. Estelle', 'Solitary Confinement'],
                'is_digitized' => true,
                'published' => true,
            ],
            'white-north-american-pps-bo-brown-1992' => [
                'title' => 'The White North American Political Prisoners in the U.S. — Rita "Bo" Brown speech (Oct 3, 1992)',
                'description' => 'Speech delivered by former George Jackson Brigade member Rita "Bo" Brown at the International Tribunal on October 3, 1992, surveying nearly thirty-five white North American political prisoners then held in U.S. prisons and jails. Brown — herself an 8.5-year veteran of federal women\'s prisons including the Davis Hall control unit at Alderson, held alongside Assata Shakur — names the Ohio 7 (Levasseur, Manning, Laaman, Carol Manning, Williams, Curzi, Gros), the Brink\'s defendants (Clark, Gilbert, Boudin, Buck), the Resistance Conspiracy defendants (Rosenberg, Blunk, Berkman, Whitehorn, Evans, Buck), the George Jackson Brigade (Cook, Mead), anti-authoritarians (Dunne, Giddings, Picariello), Plowshares (Lumsdaine, Kjoller), Sanctuary defendants, draft resister Gillam Kerley, Italian POW Silvia Baraldini, and the nine Irish Republican prisoners. Documents the use of control-unit prisons (Marion, Lexington, Marianna), preventative detention, sentencing disparities (Linda Evans got 40 years for buying weapons with false ID — longer than the KKK Dominica-invasion sentence), and medical neglect.',
                'record_type' => 'document',
                'source_format' => 'speech',
                'file' => '/pdfs/tribunals/white-pp-us-speech-1992.pdf',
                'collection' => 'International Tribunal on U.S. Political Prisoners',
                'authors' => 'Rita "Bo" Brown',
                'year' => 1992,
                'date' => '1992-10-03',
                'subjects' => ['Political Prisoners', 'Anti-imperialism', 'White Anti-imperialists', 'Control Unit Prisons', 'George Jackson Brigade'],
                'is_digitized' => true,
                'published' => true,
            ],
        ];

        foreach ($records as $slug => $payload) {
            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $this->info("RECORD updated: {$payload['title']}");
            } else {
                ArchiveRecord::create(['slug' => $slug] + $payload);
                $this->info("RECORD added: {$payload['title']}");
            }
        }

        $payloads = json_decode(file_get_contents(database_path('data/tribunal-1990-1992-prisoners.json')), true);
        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }
        $this->info("\nDone. Records=2 Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
