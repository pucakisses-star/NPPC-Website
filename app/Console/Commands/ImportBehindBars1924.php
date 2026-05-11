<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportBehindBars1924 extends Command {
    protected $signature = 'archive:import-behind-bars-1924';
    protected $description = 'Import "Behind the Bars" #1 (January 1924), the Anarchist Red Cross Society\'s prisoner-support bulletin';

    public function handle(): int {
        $slug = 'behind-the-bars-1-1924';

        $payload = [
            'title' => 'Behind the Bars, Issue #1 (January 1924)',
            'description' => 'First issue of Behind the Bars: The Voice of the Imprisoned, the prisoner-support bulletin published in New York in January 1924 by the Anarchist Red Cross Society — the U.S. forerunner of the Anarchist Black Cross. Sub-titled "Capitalist, \'Communist,\' Republican and Monarchist" with the epigraph "All Governments Are Tyrannies / The Whole World Is a Prison." Contents include letters from anarchist prisoners Librado Rivera and Mollie Steimer, a "Call from Our Japanese Comrades," news from French prisons, the poem "Night in Prison," letters from imprisoned correspondents, and a financial report. Sourced from libcom.org; published in NY, January 1924; 25 cents at the time.',
            'record_type' => 'document',
            'source_format' => 'periodical',
            'file' => '/pdfs/azine-library/behind-bars-1-1924.pdf',
            'thumbnail' => '/images/archive/azine-library/behind-bars-1-1924-cover.jpg',
            'year' => 1924,
            'date' => '1924-01-01',
            'publisher' => 'Anarchist Red Cross Society (New York)',
            'authors' => 'Includes letters from Librado Rivera, Mollie Steimer, Yartchuk',
            'collection' => 'Anarchist Zine Library',
            'volume' => 'Issue #1',
            'subjects' => ['Anarchist Red Cross', 'Political Prisoners', 'Prisoner Support', 'Historical', 'Anarchist'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 251,
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
