<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catches every ABC-themed ArchiveRecord (whether its collection
 * is the old "NYC ABC Historical Documents" / "ABCF Warchest
 * Reports" / "ABCF Posters" or the partially-merged
 * "Anarchist Black Cross" / "Anarchist Black Cross — Other") and
 * normalizes each row's collection to the chapter-level
 * sub-collection that matches its slug prefix:
 *
 *   nycabc-*           → Anarchist Black Cross — NYC
 *   abc-boston-* /
 *   boston-abc-*       → Anarchist Black Cross — Boston
 *   abc-atx-*          → Anarchist Black Cross — Austin
 *   philly-abc-*       → Anarchist Black Cross — Philly
 *   abcf-*             → Anarchist Black Cross — Federation (ABCF)
 *   richmond-abc-*     → Anarchist Black Cross — Richmond
 *   fag-* with ABC
 *   collection         → Anarchist Black Cross — Freedom Archives
 *   else               → Anarchist Black Cross — Other
 *
 * Pass --apply to write. Dry-run by default.
 */
final class NormalizeAbcCollections extends Command {
    protected $signature = 'archive:normalize-abc-collections {--apply : Actually write}';
    protected $description = 'Catch every ABC-themed collection and normalize to a chapter-level Anarchist Black Cross sub-collection';

    public function handle(): int {
        $apply = (bool) $this->option('apply');

        // Cast a wide net — anything starting with any of these
        // chapter prefixes OR already in the merged ABC namespace.
        $rows = ArchiveRecord::query()
            ->where(function ($q) {
                $q->where('collection', 'like', 'NYC ABC%')
                  ->orWhere('collection', 'like', 'Boston ABC%')
                  ->orWhere('collection', 'like', 'Austin ABC%')
                  ->orWhere('collection', 'like', 'Philly ABC%')
                  ->orWhere('collection', 'like', 'Richmond ABC%')
                  ->orWhere('collection', 'like', 'ABCF%')
                  ->orWhere('collection', 'like', 'Anarchist Black Cross%')
                  ->orWhere('collection', 'like', 'Freedom Archives — Anarchist Black Cross%');
            })
            ->get(['id', 'slug', 'title', 'collection']);

        $counts = [];
        $changes = [];
        foreach ($rows as $r) {
            $sub = $this->classify((string) $r->slug, (string) $r->collection);
            $counts[$sub] = ($counts[$sub] ?? 0) + 1;
            if ($r->collection !== $sub) {
                $changes[] = ['row' => $r, 'from' => $r->collection, 'to' => $sub];
            }
        }

        arsort($counts);
        $this->line('Target sub-collection counts:');
        foreach ($counts as $c => $n) {
            $this->info('  '.str_pad((string) $n, 5, ' ', STR_PAD_LEFT).'  '.$c);
        }
        $this->info('Rows scanned: '.$rows->count().'    To rewrite: '.count($changes));

        if (! $apply) {
            $this->line("\nFirst 10 rewrites:");
            foreach (array_slice($changes, 0, 10) as $c) {
                $this->line('  #'.$c['row']->id.'  '.$c['row']->slug.'  ('.$c['from'].' → '.$c['to'].')');
            }
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

    private function classify(string $slug, string $collection): string {
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
        if (str_starts_with($s, 'richmond-abc-')) {
            return 'Anarchist Black Cross — Richmond';
        }
        if (str_starts_with($s, 'abcf-') || str_starts_with($s, 'abcf')) {
            return 'Anarchist Black Cross — Federation (ABCF)';
        }
        // FA-imported ABC content
        if (str_contains($collection, 'Freedom Archives') && stripos($collection, 'Anarchist Black Cross') !== false) {
            return 'Anarchist Black Cross — Freedom Archives';
        }

        return 'Anarchist Black Cross — Other';
    }
}
