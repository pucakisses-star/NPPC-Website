<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers Sara Falconer's 2004 MA thesis "Yours in solidarity:
 * alternative media projects with North American political prisoners"
 * (Concordia University) as an ArchiveRecord.
 *
 * Idempotent — matches by file path.
 */
final class AddFalconerThesis extends Command {
    protected $signature = 'archive:add-falconer-thesis';
    protected $description = 'Add Sara Falconer 2004 thesis on alternative media for political prisoners';

    public function handle(): int {
        $file = '/pdfs/scholarship/falconer-yours-in-solidarity-2004.pdf';

        $payload = [
            'title' => 'Yours in Solidarity: Alternative Media Projects with North American Political Prisoners',
            'description' => 'Sara Falconer\'s MA thesis (Concordia University, Department of Communication Studies) examining how international activist networks use alternative media to advocate for imprisoned activists, with particular focus on media projects created by political prisoners themselves from the 1960s onward.',
            'authors' => 'Sara Falconer',
            'year' => 2004,
            'file' => $file,
            'record_type' => 'document',
            'source_format' => 'thesis',
            'collection' => 'Scholarship',
            'publisher' => 'Concordia University',
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
