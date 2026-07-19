<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the November 16, 1967 issue of Workers World (vol. 9 no. 23),
 * via the Encyclopedia of Trotskyism On-Line scan at marxists.org.
 * Carries the Edward Oquendo draft-refusal conviction, the Edward R.
 * Lynn "Black Defiance in the Navy" case, the Martin Sostre defense
 * campaign, and the LeRoi Jones (Amiri Baraka) Newark conviction.
 */
final class AddWorkersWorld1967Archive extends Command {
    protected $signature = 'archive:add-workers-world-1967';
    protected $description = 'Add Workers World vol. 9 no. 23 (November 16, 1967) to the archive';

    public function handle(): int {
        $file = '/pdfs/periodicals/workers-world-1967-11-16.pdf';
        $payload = [
            'title' => 'Workers World, Vol. 9 No. 23 (November 16, 1967)',
            'description' => 'Issue of the Workers World Party newsweekly. Coverage includes "Hanging Judge and Stacked Jury Convict Oquendo in Fifteen Minutes!" — the Brooklyn draft-refusal trial of Black war resister Edward Oquendo, defended by Conrad Lynn — plus "Black Defiance in the Navy" (hospital corpsman Edward R. Lynn\'s statement campaign at the San Diego Naval Hospital and the UCMJ charges it drew), the Martin Sostre defense campaign, the LeRoi Jones (Amiri Baraka) Newark conviction, Rap Brown, and Huey Newton support organizing. Scanned by the Encyclopedia of Trotskyism On-Line (marxists.org).',
            'file' => $file,
            'thumbnail' => '/thumbnails/workers-world-1967-11-16.jpg',
            'record_type' => 'document',
            'source_format' => 'periodical',
            'collection' => 'Periodicals',
            'publisher' => 'Workers World Party',
            'volume' => 'Vol. 9, No. 23',
            'date' => '1967-11-16',
            'year' => 1967,
            'subjects' => ['Draft resistance', 'Vietnam War', 'GI resistance', 'Martin Sostre', 'Amiri Baraka', 'Black liberation'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::query()->where('file', $file)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing record.');
        } else {
            ArchiveRecord::create($payload);
            $this->info('Created record.');
        }

        return self::SUCCESS;
    }
}
