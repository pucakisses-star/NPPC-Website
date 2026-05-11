<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class Add4StruggleMissingIssues extends Command {
    protected $signature = 'archive:add-4struggle-missing';
    protected $description = 'Add placeholder ArchiveRecord rows for 4StruggleMag issues 1-10 (non-digitized; pre-Fall 2008)';

    public function handle(): int {
        $created = 0;
        $updated = 0;

        foreach ($this->records() as $r) {
            $existing = ArchiveRecord::where('slug', $r['slug'])->first();
            $payload = collect($r)->except('slug')->all();

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $r['slug']] + $payload);
                $created++;
            }
        }

        $this->info("Done. {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(): array {
        $rows = [];

        for ($n = 1; $n <= 10; $n++) {
            $rows[] = [
                'slug' => '4strugglemag-issue-'.$n,
                'title' => '4StruggleMag, Issue #'.$n,
                'description' => 'Issue #'.$n.' of 4StruggleMag, the independent non-sectarian revolutionary magazine produced by the Toronto chapter of the Anarchist Black Cross Federation and edited by anti-imperialist political prisoner Jaan Laaman. This pre-Fall-2008 issue has not yet been located in a digital format; if you have a scan, please contact us.',
                'record_type' => 'document',
                'source_format' => 'periodical',
                'file' => null,
                'thumbnail' => null,
                'year' => null,
                'date' => null,
                'publisher' => 'Toronto ABCF',
                'authors' => 'Jaan Laaman (editor)',
                'collection' => '4StruggleMag',
                'volume' => 'Issue #'.$n,
                'subjects' => ['Political Prisoners', 'Anarchist Black Cross', 'Anti-Imperialism', 'Black Liberation'],
                'is_digitized' => false,
                'published' => true,
                // Negative sort_order so issues 1-10 sit above the digitized issues 11-21 (0..10)
                'sort_order' => $n - 11,
            ];
        }

        return $rows;
    }
}
