<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportChicagoAbcBulletin extends Command {
    protected $signature = 'archive:import-chicago-abc-bulletin';
    protected $description = 'Import the 1972 Chicago Anarchist Black Cross Bulletin #1';

    public function handle(): int {
        $slug = 'chicago-abc-bulletin-1-1972';

        $payload = [
            'title' => 'Chicago Anarchist Black Cross Bulletin, Issue #1 (1972)',
            'description' => 'First issue of the Chicago Anarchist Black Cross Bulletin, a prisoner-support periodical published in 1972 — among the earliest U.S. Anarchist Black Cross publications, predating the 1980s ABCF network by more than a decade. Sourced from libcom.org.',
            'record_type' => 'document',
            'source_format' => 'periodical',
            'file' => '/pdfs/azine-library/chicago-abc-bulletin-1-1972.pdf',
            'thumbnail' => '/images/archive/azine-library/chicago-abc-bulletin-1-1972-cover.jpg',
            'year' => 1972,
            'date' => '1972-01-01',
            'publisher' => 'Chicago Anarchist Black Cross',
            'collection' => 'Anarchist Zine Library',
            'volume' => 'Issue #1',
            'subjects' => ['Anarchist Black Cross', 'Political Prisoners', 'Prisoner Support', 'Historical'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 250,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info("updated: {$slug}");
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info("created: {$slug}");
        }

        return self::SUCCESS;
    }
}
