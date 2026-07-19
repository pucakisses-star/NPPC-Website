<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the March 1977 issue of Revolutionary Cause (vol. 2 no. 1), the
 * newspaper of the August Twenty-Ninth Movement (Marxist-Leninist), via
 * the Encyclopedia of Anti-Revisionism On-Line scan at marxists.org.
 * Carries extensive Skyhorse-Mohawk defense coverage, the St. Luke's 23
 * trial, and the Ben Lenard frame-up.
 */
final class AddRevolutionaryCause1977Archive extends Command {
    protected $signature = 'archive:add-revolutionary-cause-1977';
    protected $description = 'Add Revolutionary Cause vol. 2 no. 1 (March 1977) to the archive';

    public function handle(): int {
        $file = '/pdfs/periodicals/revolutionary-cause-vol2-no1-march-1977.pdf';
        $payload = [
            'title' => 'Revolutionary Cause, Vol. 2 No. 1 (March 1977)',
            'description' => 'International Working Women\'s Day issue of the August Twenty-Ninth Movement (Marxist-Leninist) newspaper. Carries the fullest movement-side account of the Skyhorse-Mohawk case — the immunized witnesses, the jail beatings, forced drugging and solitary confinement, the Ventura County Bar Association\'s "People vs. Tonto" skit, and Skyhorse\'s Oxnard prison letter — plus the St. Luke\'s 23 hospital-protest trial in Chicago, the Ben Lenard police frame-up, the J.P. Stevens organizing drive, and defense campaigns for Joann Little, Assata Shakur and Yvonne Wanrow. Scanned by the Encyclopedia of Anti-Revisionism On-Line (marxists.org).',
            'file' => $file,
            'thumbnail' => '/thumbnails/revolutionary-cause-vol2-no1-march-1977.jpg',
            'record_type' => 'document',
            'source_format' => 'periodical',
            'collection' => 'Periodicals',
            'publisher' => 'August Twenty-Ninth Movement (Marxist-Leninist)',
            'volume' => 'Vol. 2, No. 1',
            'date' => '1977-03-01',
            'year' => 1977,
            'subjects' => ['Skyhorse-Mohawk case', 'American Indian Movement', 'Police frame-ups', 'Labor organizing', 'Women\'s liberation'],
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
