<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Imports the 79 orphan files under public/pdfs/external-imports/ as
 * ArchiveRecord rows so they're cataloged + reachable via /archive-records.
 *
 * Classifier maps filename patterns to a sensible collection:
 *   church-committee-*         -> "Church Committee Reports"
 *   fa-c<NNN>-*                -> "Freedom Archives"
 *   fbi-cointelpro-*           -> "FBI Vault — COINTELPRO"
 *   fbi-*                      -> "FBI Vault — Surveillance Files"
 *   new-afrikan-*              -> "New Afrikan Movement"
 *   *.mp4                      -> classified as video (record_type=video)
 *   anything else              -> "External Imports"
 *
 * Idempotent — skips files whose path already exists in the DB.
 *
 *   php artisan archive:import-external-imports             # dry-run
 *   php artisan archive:import-external-imports --apply
 */
final class ImportExternalImportsOrphans extends Command {
    protected $signature = 'archive:import-external-imports {--apply : Actually create the ArchiveRecord rows}';
    protected $description = 'Catalog the 79 orphan files in public/pdfs/external-imports/';

    public function handle(): int {
        $dir = base_path('public/pdfs/external-imports');
        if (! is_dir($dir)) {
            $this->error('No public/pdfs/external-imports/ directory found.');
            return self::FAILURE;
        }

        $files = glob($dir.'/*.{pdf,mp4,mp3,m4a}', GLOB_BRACE) ?: [];
        $this->info('Found '.count($files).' file(s) in external-imports/.');

        $created = 0; $skipped = 0; $byCollection = [];
        foreach ($files as $abs) {
            $rel = 'pdfs/external-imports/'.basename($abs);
            $relWithSlash = '/'.$rel;

            $exists = ArchiveRecord::where('file', $rel)->orWhere('file', $relWithSlash)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            $payload = $this->buildPayload(basename($abs), $relWithSlash);
            $byCollection[$payload['collection']][] = $payload['title'];

            $this->line(sprintf('  [%s]  %s  -> %s', strtoupper($payload['record_type']), $payload['title'], $payload['collection']));

            if ($this->option('apply')) {
                ArchiveRecord::create($payload);
                $created++;
            }
        }

        $this->newLine();
        if (! $this->option('apply')) {
            $this->info('(dry-run; '.count($files).' file(s) would be imported, '.$skipped.' already cataloged)');
        } else {
            $this->info("Done — created {$created}, skipped {$skipped}.");
        }

        $this->newLine();
        $this->info('By collection:');
        foreach ($byCollection as $col => $titles) {
            $this->line('  '.$col.' — '.count($titles));
        }

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function buildPayload(string $filename, string $file): array {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $stem = pathinfo($filename, PATHINFO_FILENAME);

        // Classify by filename pattern.
        [$collection, $strip] = match (true) {
            str_starts_with($stem, 'church-committee')             => ['Church Committee Reports', 'church-committee-?(hearings-)?(vol-\d+-)?'],
            (bool) preg_match('/^fa-c\d+/', $stem)                 => ['Freedom Archives', 'fa-c\d+-'],
            (bool) preg_match('/^fa\d+-/', $stem)                  => ['Freedom Archives', 'fa\d+-'],
            str_starts_with($stem, 'fbi-cointelpro')               => ['FBI Vault — COINTELPRO', 'fbi-cointelpro-'],
            str_starts_with($stem, 'fbi-')                         => ['FBI Vault — Surveillance Files', 'fbi-'],
            str_starts_with($stem, 'new-afrikan')                  => ['New Afrikan Movement', ''],
            str_contains($stem, 'attica')                          => ['Attica Prison Rebellion', ''],
            str_contains($stem, 'malcolm-x')                       => ['Malcolm X', ''],
            str_contains($stem, 'martin-luther-king')              => ['Martin Luther King Jr.', ''],
            str_contains($stem, 'angela-davis') || str_contains($stem, 'angela')
                                                                   => ['Angela Davis', ''],
            str_contains($stem, 'assata')                          => ['Assata Shakur', ''],
            str_contains($stem, 'fred-hampton') || str_contains($stem, 'hampton-mark-clark')
                                                                   => ['Fred Hampton', ''],
            default                                                => ['External Imports', ''],
        };

        // Strip prefix + humanize the title.
        $title = $stem;
        if ($strip !== '') {
            $title = preg_replace('/^'.$strip.'/', '', $title);
        }
        $title = $this->humanize($title);

        $isVideo = in_array($ext, ['mp4', 'mov', 'webm'], true);
        $isAudio = in_array($ext, ['mp3', 'm4a', 'wav', 'ogg'], true);

        return [
            'title'         => $title,
            'description'   => null,
            'authors'       => null,
            'year'          => $this->extractYear($stem),
            'file'          => $file,
            'record_type'   => $isVideo ? 'video' : ($isAudio ? 'audio' : 'document'),
            'source_format' => $isVideo ? 'video' : ($isAudio ? 'audio' : 'pamphlet'),
            'collection'    => $collection,
            'is_digitized'  => true,
            'published'     => true,
        ];
    }

    private function humanize(string $s): string {
        $s = str_replace('-', ' ', $s);
        $s = ucwords($s);
        // Fix common acronyms / proper nouns that ucwords mangled.
        $fixes = [
            'Fbi' => 'FBI', 'Cointelpro' => 'COINTELPRO', 'Bpp' => 'BPP',
            'Aim' => 'AIM', 'Bla' => 'BLA', 'Sds' => 'SDS', 'Iww' => 'IWW',
            'Move' => 'MOVE', 'Sclc' => 'SCLC', 'Cominfil' => 'COMINFIL',
            'Nyc' => 'NYC', 'Sf' => 'SF', 'La' => 'LA', 'Mlk' => 'MLK',
            'Pflp' => 'PFLP', 'Faln' => 'FALN', 'Falna' => 'FALN', 'Nbhrc' => 'NBHRC',
            'Iv' => 'IV', 'Iii' => 'III', 'Ii' => 'II', 'Vi' => 'VI', 'Vii' => 'VII',
        ];
        foreach ($fixes as $bad => $good) {
            $s = preg_replace('/\b'.$bad.'\b/', $good, $s);
        }
        return trim($s);
    }

    private function extractYear(string $stem): ?int {
        if (preg_match('/(19|20)\d{2}/', $stem, $m)) {
            $y = (int) $m[0];
            if ($y >= 1700 && $y <= (int) date('Y') + 1) return $y;
        }
        return null;
    }
}
