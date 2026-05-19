<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Fix the two broken records in the 4StruggleMag import that pointed at
 * PDF files that were never present in the repository:
 *
 *   - united-freedom-front-pamphlet (file: /pdfs/4strugglemag/UFF.pdf)
 *     → swapped for "The Ohio 7: Living For The Revolution" (a standalone
 *       UFF / Ohio 7 movement pamphlet sourced from IA), self-hosted at
 *       /pdfs/4strugglemag/ohio-7-living-for-the-revolution.pdf
 *
 *   - 4strugglemag-securite-eng (file: /pdfs/4strugglemag/Securite-eng-letter.pdf)
 *     → deleted; the original "Sécurité — An Open Letter" supplement is
 *       not locatable on archive.org or known mirrors.
 *
 * Idempotent.
 */
final class FixBroken4StruggleRecords extends Command {
    protected $signature = 'archive:fix-broken-4strugglemag-records';
    protected $description = 'Replace the UFF.pdf record with Ohio 7 pamphlet; delete the unfindable Sécurité record';

    public function handle(): int {
        // 1. UFF.pdf → Ohio 7: Living For The Revolution
        $uff = ArchiveRecord::where('slug', 'united-freedom-front-pamphlet')->first();
        if ($uff) {
            $uff->update([
                'title' => 'The Ohio 7: Living For The Revolution',
                'description' => 'Movement pamphlet on the Ohio 7 / United Freedom Front defendants — Ray Luc Levasseur, Tom Manning, Richard Williams, Jaan Laaman, Carol Manning, Patricia Levasseur, and Barbara Curzi-Laaman — the clandestine anti-imperialist formation responsible for the United Freedom Front bombings of corporate and military targets in the early 1980s, indicted and prosecuted in the 1988–89 Springfield, Massachusetts sedition-conspiracy trial. Self-hosted from the Internet Archive ohio-7-living-for-the-revolution item.',
                'file' => '/pdfs/4strugglemag/ohio-7-living-for-the-revolution.pdf',
                'subjects' => ['Ohio 7', 'United Freedom Front', 'UFF', 'Anti-Imperialism', 'Armed Struggle', 'Ray Luc Levasseur', 'Tom Manning', 'Richard Williams', 'Jaan Laaman', 'Sedition Conspiracy'],
            ]);
            $this->info('UFF record updated to point at Ohio 7: Living For The Revolution.');
        } else {
            $this->warn('united-freedom-front-pamphlet record not found.');
        }

        // 2. Securite — delete (source PDF not findable)
        $sec = ArchiveRecord::where('slug', '4strugglemag-securite-eng')->first();
        if ($sec) {
            $sec->delete();
            $this->info('4strugglemag-securite-eng record deleted (source PDF unfindable).');
        } else {
            $this->info('4strugglemag-securite-eng record not present (already cleaned up).');
        }

        return self::SUCCESS;
    }
}
