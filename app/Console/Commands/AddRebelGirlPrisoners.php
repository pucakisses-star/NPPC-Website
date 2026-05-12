<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Imports the 59 US political prisoners surfaced from Elizabeth Gurley
 * Flynn's "The Rebel Girl" autobiography (1955), plus the 1949 + 1952
 * Foley Square Smith Act co-defendants whose names aren't in the book
 * but who belong with Flynn's case.
 *
 * Adds:
 *   - Elizabeth Gurley Flynn herself
 *   - Pre-WWI / 1900s–1910s labor cases (Preston, Smith, Ettor,
 *     Giovannitti, Caruso, Quinlan, Scott, Boyd, Buccafori, Tannenbaum,
 *     Cline, Schmidt, Caplan)
 *   - Preparedness Day / Mooney case (Tom Mooney, Warren Billings,
 *     Israel Weinberg, Edward Nolan, Rena Mooney)
 *   - Ludlow / Everett / Centralia (John Lawson, Thomas Tracy, the
 *     Centralia 7 — Britt Smith, O.C. Bland, Bert Bland, McInerney,
 *     Becker, Barnett, Lamb — plus Wesley Everest, lynched)
 *   - Espionage Act women not in Kohn (Elizabeth Ford, Elizabeth Baer)
 *   - 1949 Foley Square Smith Act (11 defendants — Dennis, Winston,
 *     Foster, Williamson, Stachel, Thompson, Davis, Gates, Potash,
 *     Green, Winter, Hall)
 *   - 1952 second-string Foley Square Smith Act (16 of Flynn's
 *     co-defendants)
 *
 * Also registers the source PDF (Flynn's autobiography, 1955) as an
 * ArchiveRecord.
 *
 * Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddRebelGirlPrisoners extends Command {
    protected $signature = 'archive:add-rebel-girl-prisoners';
    protected $description = 'Add prisoners surfaced from Elizabeth Gurley Flynn\'s autobiography "The Rebel Girl"';

    public function handle(): int {
        $path = database_path('data/rebel-girl-prisoners.json');
        if (! is_file($path)) {
            $this->error("Data file not found: {$path}");

            return self::FAILURE;
        }

        $payloads = json_decode(file_get_contents($path), true);
        if (! is_array($payloads)) {
            $this->error('Could not parse JSON.');

            return self::FAILURE;
        }

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

        // Register the source PDF as an ArchiveRecord.
        $slug = 'flynn-rebel-girl-autobiography-1955';
        $record = [
            'title' => 'The Rebel Girl: An Autobiography, My First Life (1906–1926)',
            'description' => 'The 1955 autobiography of Elizabeth Gurley Flynn covering her early life and her organizing with the Industrial Workers of the World from the Spokane free-speech fights of 1909 through the Lawrence and Paterson textile strikes, the Joe Hill defense, the Tom Mooney case, the Sacco-Vanzetti campaign, the WWI Espionage Act prisoner defense, and the founding of the American Civil Liberties Union. Re-edited per Flynn\'s wishes and first published in November 1955 by Masses & Mainstream while she was serving her Smith Act sentence at Alderson, West Virginia.',
            'record_type' => 'book',
            'source_format' => 'book',
            'file' => '/pdfs/books/rebel-girl-autobiography-OCR.pdf',
            'collection' => 'Movement Memoirs',
            'authors' => 'Elizabeth Gurley Flynn',
            'publisher' => 'Masses & Mainstream',
            'year' => 1955,
            'date' => '1955-11-01',
            'subjects' => ['IWW', 'Communist Party USA', 'Smith Act', 'Espionage Act', 'Free Speech Fights', 'Labor History'],
            'is_digitized' => true,
            'published' => true,
        ];
        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($record);
            $this->info('RECORD updated: Flynn — The Rebel Girl (1955).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $record);
            $this->info('RECORD added: Flynn — The Rebel Girl (1955).');
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
