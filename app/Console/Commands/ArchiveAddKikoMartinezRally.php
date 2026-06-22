<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs a 1981 compilation of Francisco "Kiko" Martínez defense materials from
 * the Herman Baca Papers (MSS 0649, Box 27, Folder 4), Special Collections &
 * Archives, UC San Diego Library — digitized in the library's Digital Collections
 * (object bb4506450f). The nine-page document gathers the bilingual flyers for the
 * January 24, 1981 National Rally in Pueblo, Colorado, the Rally Committee's
 * organizing letter and schedule, contemporaneous newspaper clippings on his 1980
 * capture at the border, Martínez's own essay "Change at What Cost?", a summary of
 * the five federal and state trials he faced, and a chronology of his life and
 * case. The PDF is committed at public/pdfs/government-repression/ and referenced
 * by its public /pdfs/ path, matching the other ArchiveRecords. Idempotent —
 * skips if the slug already exists.
 */
final class ArchiveAddKikoMartinezRally extends Command
{
    protected $signature = 'archive:add-kiko-martinez-rally';

    protected $description = 'Add the 1981 Kiko Martínez rally/defense materials (Herman Baca Collection, UC San Diego) to the archive';

    public function handle(): int
    {
        $slug = 'kiko-martinez-national-rally-1981-herman-baca';

        if (ArchiveRecord::where('slug', $slug)->exists()) {
            $this->info('Archive record already exists, skipping.');

            return self::SUCCESS;
        }

        $record = ArchiveRecord::create([
            'title' => 'National Rally to Support Francisco "Kiko" Martínez — defense materials (1981)',
            'slug' => $slug,
            'description' => 'A nine-page compilation of materials supporting Chicano-movement attorney Francisco '
                .'"Kiko" Martínez, assembled around the National Rally held in Pueblo, Colorado on January 24, 1981 — '
                .'three days before the first of the federal trials charging him with mailing explosives in 1973. It '
                .'gathers the rally flyers in English and Spanish, the Rally Committee\'s January 16, 1981 organizing '
                .'letter and day-of schedule, contemporaneous newspaper clippings on his September 1980 capture at the '
                .'Arizona border ("Martinez Hours From Freedom When Caught"), Martínez\'s own essay "Change at What '
                .'Cost?", a summary titled "The United States of America vs. Franke E. Martinez" laying out the five '
                .'federal and state trials he faced, and a detailed chronology of his life and case. Digitized from the '
                .'Herman Baca Papers (MSS 0649, Box 27, Folder 4), Special Collections & Archives, UC San Diego Library.',
            'record_type' => 'document',
            'source_format' => 'flyers and clippings',
            'file' => '/pdfs/government-repression/kiko-martinez-national-rally-1981-herman-baca.pdf',
            'year' => 1981,
            'publisher' => 'Francisco "Kiko" Martínez Defense Committee / Rally Committee',
            'collection' => 'Government Repression',
            'subjects' => [
                'Francisco "Kiko" Martínez',
                'Chicano movement',
                'Political repression',
                'Crusade for Justice',
                'Herman Baca Collection',
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
