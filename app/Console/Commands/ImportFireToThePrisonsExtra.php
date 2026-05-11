<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportFireToThePrisonsExtra extends Command {
    protected $signature = 'archive:import-ftp-extra';
    protected $description = 'Import the remaining Fire to the Prisons issues (#2, #3, #8–#12) sourced from libcom.org';

    public function handle(): int {
        $issues = [
            2 => ['Fall 2007', 2007, '2007-10-01'],
            3 => ['Spring 2008', 2008, '2008-04-01'],
            8 => ['Winter 2010', 2010, '2010-01-01'],
            9 => ['Summer 2010', 2010, '2010-07-01'],
            10 => ['Fall 2010', 2010, '2010-10-01'],
            11 => ['Spring 2011', 2011, '2011-04-01'],
            12 => ['Spring 2015', 2015, '2015-04-01'],
        ];

        $created = 0;
        $updated = 0;

        foreach ($issues as $n => [$season, $year, $date]) {
            $slug = 'azine-fire-to-the-prisons-'.$n;
            $payload = [
                'title' => 'Fire to the Prisons, Issue #'.$n.' ('.$season.')',
                'description' => 'Issue #'.$n.' of Fire to the Prisons, the U.S. insurrectionary-anarchist anti-prison periodical. Sourced from libcom.org.',
                'record_type' => 'document',
                'source_format' => 'periodical',
                'file' => '/pdfs/azine-library/fire-to-the-prisons-'.$n.'.pdf',
                'thumbnail' => '/images/archive/azine-library/fire-to-the-prisons-'.$n.'-cover.jpg',
                'year' => $year,
                'date' => $date,
                'publisher' => 'Fire to the Prisons',
                'collection' => 'Anarchist Zine Library',
                'volume' => 'Issue #'.$n,
                'subjects' => ['Anti-Prison', 'Insurrectionary Anarchism', 'Political Prisoners'],
                'is_digitized' => true,
                'published' => true,
                'sort_order' => 230 + $n,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $slug] + $payload);
                $created++;
            }
        }

        $this->info("Done. {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }
}
