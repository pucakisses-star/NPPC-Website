<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Splits the flattened "Anarchist Black Cross" collection back
 * into chapter-level sub-collections by slug prefix:
 *
 *   - nycabc-*          → "Anarchist Black Cross — NYC"
 *   - abc-boston-* /
 *     boston-abc-*      → "Anarchist Black Cross — Boston"
 *   - abc-atx-*         → "Anarchist Black Cross — Austin"
 *   - philly-abc-*      → "Anarchist Black Cross — Philly"
 *   - abcf-* /
 *     abcf-library-*    → "Anarchist Black Cross — Federation (ABCF)"
 *   - everything else   → "Anarchist Black Cross — Other"
 *
 * Pass --apply to write. Dry-run by default.
 */
final class ReSegmentAbcByChapter extends Command {
    protected $signature = 'archive:resegment-abc-by-chapter {--apply : Actually write the change}';
    protected $description = 'Split the merged Anarchist Black Cross collection back into chapter-level sub-collections';

    public function handle(): int {
        $apply = (bool) $this->option('apply');

        $rows = ArchiveRecord::query()
            ->where('collection', 'Anarchist Black Cross')
            ->orWhere('collection', 'like', 'Anarchist Black Cross —%')
            ->get(['id', 'slug', 'title', 'collection']);

        $counts = [];
        $changes = [];
        foreach ($rows as $r) {
            $sub = $this->classify((string) $r->slug);
            $counts[$sub] = ($counts[$sub] ?? 0) + 1;
            if ($r->collection !== $sub) {
                $changes[] = ['row' => $r, 'to' => $sub];
            }
        }

        $this->line('Target sub-collection counts:');
        arsort($counts);
        foreach ($counts as $c => $n) {
            $this->info('  '.str_pad((string) $n, 5, ' ', STR_PAD_LEFT).'  '.$c);
        }
        $this->info('Total rows: '.$rows->count().'    To rewrite: '.count($changes));

        if (! $apply) {
            $this->info('(dry-run; re-run with --apply to write)');

            return self::SUCCESS;
        }

        foreach ($changes as $c) {
            $c['row']->collection = $c['to'];
            $c['row']->save();
        }
        $this->info('Updated '.count($changes).' rows.');

        return self::SUCCESS;
    }

    private function classify(string $slug): string {
        $s = strtolower($slug);
        if (str_starts_with($s, 'nycabc-') || str_starts_with($s, 'nyc-abc-')) {
            return 'Anarchist Black Cross — NYC';
        }
        if (str_starts_with($s, 'abc-boston-') || str_starts_with($s, 'boston-abc-')) {
            return 'Anarchist Black Cross — Boston';
        }
        if (str_starts_with($s, 'abc-atx-') || str_starts_with($s, 'austin-abc-')) {
            return 'Anarchist Black Cross — Austin';
        }
        if (str_starts_with($s, 'philly-abc-')) {
            return 'Anarchist Black Cross — Philly';
        }
        if (str_starts_with($s, 'abcf-')) {
            return 'Anarchist Black Cross — Federation (ABCF)';
        }
        if (str_starts_with($s, 'richmond-abc-')) {
            return 'Anarchist Black Cross — Richmond';
        }

        return 'Anarchist Black Cross — Other';
    }
}
