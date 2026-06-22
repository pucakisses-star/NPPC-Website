<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs a Colorado State University Pueblo library exhibit panel on Chicano
 * movement attorney Francisco "Kiko" Martínez ("Fight for Justice"), which
 * reproduces a September 16, 1983 La Cucaracha article on the dismissal of the
 * 1973 bombing charges against him. The PDF is committed at
 * public/pdfs/government-repression/ and referenced by its public /pdfs/ path,
 * matching the other ArchiveRecords. Idempotent — skips if the slug exists.
 */
final class ArchiveAddKikoMartinezExhibitPanel extends Command
{
    protected $signature = 'archive:add-kiko-martinez-exhibit-panel';

    protected $description = 'Add the CSU Pueblo "Fight for Justice" Kiko Martínez exhibit panel to the archive';

    public function handle(): int
    {
        $slug = 'kiko-martinez-fight-for-justice-exhibit-panel';

        if (ArchiveRecord::where('slug', $slug)->exists()) {
            $this->info('Archive record already exists, skipping.');

            return self::SUCCESS;
        }

        $record = ArchiveRecord::create([
            'title' => 'Fight for Justice: Francisco "Kiko" Martínez (CSU Pueblo exhibit panel)',
            'slug' => $slug,
            'description' => 'A panel from a Colorado State University Pueblo library exhibit on Chicano-movement '
                .'attorney Francisco "Kiko" Martínez. Titled "Fight for Justice," it reproduces a September 16, 1983 '
                .'La Cucaracha article — "Martínez Cleared on Bomb Charges" — reporting U.S. District Judge Frank G. '
                .'Theis\'s August 18, 1983 dismissal of the last 1973 bombing counts against him after the exposure of '
                .'judicial misconduct (a federal judge secretly meeting with prosecutors). It also recounts his '
                .'September 1980 capture at the Arizona border and includes an interview in which Martínez disavows '
                .'faith in the legal system. Illustration by Juan Espinosa; photos by David A. Martínez.',
            'record_type' => 'document',
            'source_format' => 'exhibit panel',
            'file' => '/pdfs/government-repression/kiko-martinez-fight-for-justice-exhibit-panel.pdf',
            'publisher' => 'Colorado State University Pueblo Library',
            'collection' => 'Government Repression',
            'subjects' => [
                'Francisco "Kiko" Martínez',
                'Chicano movement',
                'Political repression',
                'La Cucaracha',
                'Government Repression',
            ],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 0,
        ]);

        $this->info("Added archive record: {$record->title} (file {$record->file})");

        return self::SUCCESS;
    }
}
