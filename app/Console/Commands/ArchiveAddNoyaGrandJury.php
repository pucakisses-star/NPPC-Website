<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs Carlos Noya's essay "Judgment of the Grand Jury" (Liga Socialista
 * Puertorriqueña) in the site archive. Noya was exiled and imprisoned in the
 * U.S. for refusing to collaborate with a federal grand jury investigating the
 * Puerto Rican armed clandestine independence movement; he was released in
 * March 1982 after 17 months. The essay first appeared in Correo de la Quincena
 * (organ of the LSP political bureau, Vol. XIX, 1982) and was reprinted in
 * Breakthrough, the journal of the Prairie Fire Organizing Committee. The scan
 * comes from the Encyclopedia of Anti-Revisionism On-Line (Marxists Internet
 * Archive) and is committed at public/pdfs/breakthrough/.
 *
 * Idempotent — skips if the slug already exists. Run archive:generate-thumbnails
 * afterward to render the page-1 cover thumbnail.
 */
final class ArchiveAddNoyaGrandJury extends Command
{
    protected $signature = 'archive:add-noya-grand-jury';

    protected $description = 'Add Carlos Noya\'s "Judgment of the Grand Jury" (Breakthrough) to the archive';

    public function handle(): int
    {
        $slug = 'judgment-of-the-grand-jury-carlos-noya';

        if (ArchiveRecord::where('slug', $slug)->exists()) {
            $this->info('Archive record already exists, skipping.');

            return self::SUCCESS;
        }

        $record = ArchiveRecord::create([
            'title' => 'Judgment of the Grand Jury',
            'slug' => $slug,
            'description' => 'An essay by Carlos Noya, a leading member of the Liga Socialista Puertorriqueña (LSP), a '
                .'public revolutionary organization in Puerto Rico that argued for a protracted people\'s war to achieve '
                .'independence and socialism. Noya was exiled and imprisoned in the United States for refusing to '
                .'collaborate with a federal grand jury investigating the Puerto Rican armed clandestine movement; after '
                .'serving 17 months he was released in March 1982. The essay analyzes the grand jury as an instrument of '
                .'"judicial terrorism" and political repression used against the independence, Black liberation, Native, '
                .'anti-war, labor, feminist, and white anti-imperialist movements. It first appeared in Correo de la '
                .'Quincena, organ of the LSP political bureau (Vol. XIX, March–September 1982), and was reprinted in '
                .'Breakthrough, the political journal of the Prairie Fire Organizing Committee. Digitized by the '
                .'Encyclopedia of Anti-Revisionism On-Line (Marxists Internet Archive).',
            'record_type' => 'document',
            'source_format' => 'article',
            'file' => '/pdfs/breakthrough/judgment-of-the-grand-jury-carlos-noya.pdf',
            'year' => 1982,
            'publisher' => 'Prairie Fire Organizing Committee (Breakthrough)',
            'authors' => 'Carlos Noya',
            'collection' => 'Prairie Fire Organizing Committee — Breakthrough',
            'subjects' => [
                'Puerto Rican independence',
                'Grand jury resistance',
                'Political imprisonment',
                'Anti-imperialism',
                'Liga Socialista Puertorriqueña',
                'Government Repression',
            ],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 0,
        ]);

        $this->info("Added archive record: {$record->title} (file {$record->file})");
        $this->comment('Run `php artisan archive:generate-thumbnails` to render the cover thumbnail.');

        return self::SUCCESS;
    }
}
