<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Adds two user-surfaced ABC PDFs that were missing from the
 * archive: a Richmond ABC trifold introductory pamphlet from
 * azinelibrary.org, and the Anarchist Black Cross Network
 * Newsletter (Summer 2003) from Freedom Archives' ABC scan set.
 */
final class FetchAbcMiscPdfs extends Command {
    protected $signature = 'archive:fetch-abc-misc-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Add Richmond ABC trifold + ABC Network Newsletter Summer 2003';

    public function handle(): int {
        $force = (bool) $this->option('force');

        $items = [
            [
                'slug' => 'richmond-abc-trifold-pamphlet',
                'pdf_url' => 'https://azinelibrary.org/approved/richmond-anarchist-black-cross-trifold-pamphlet-1.pdf',
                'public_dir' => 'pdfs/abc',
                'record' => [
                    'title' => 'Richmond Anarchist Black Cross — Trifold Pamphlet',
                    'description' => 'Trifold introductory pamphlet from Richmond Anarchist Black Cross — a local Anarchist Black Cross chapter\'s prisoner-support / chapter-orientation handout.',
                    'collection' => 'Anarchist Black Cross — Local Chapters',
                    'publisher' => 'Richmond Anarchist Black Cross',
                    'authors' => 'Richmond Anarchist Black Cross',
                    'subjects' => ['Richmond ABC', 'Anarchist Black Cross', 'Prisoner Support'],
                ],
            ],
            [
                'slug' => 'abc-network-newsletter-summer-2003',
                'pdf_url' => 'https://www.freedomarchives.org/Documents/Finder/DOC510_scans/Anarchist_Black_Cross/510.abc.network.newsletter.Summer.2003.pdf',
                'public_dir' => 'pdfs/freedom-archives',
                'record' => [
                    'title' => 'Anarchist Black Cross Network Newsletter — Summer 2003',
                    'description' => 'Summer 2003 newsletter of the Anarchist Black Cross Network, scanned from the Freedom Archives\' Anarchist Black Cross scan set. Inter-chapter news, prisoner updates, and ABC organizing notes.',
                    'collection' => 'Freedom Archives — Anarchist Black Cross',
                    'publisher' => 'Anarchist Black Cross Network',
                    'authors' => 'Anarchist Black Cross Network',
                    'year' => 2003,
                    'subjects' => ['Anarchist Black Cross Network', 'ABC', 'Prisoner Support', 'Movement Newsletter'],
                ],
            ],
        ];

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $slug = $item['slug'];
            $dir = public_path($item['public_dir']);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $localPath = $dir.'/'.$slug.'.pdf';
            $webPath = '/'.$item['public_dir'].'/'.$slug.'.pdf';

            if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                $this->line("fetch {$item['pdf_url']}");
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp])
                        ->timeout(600)
                        ->get($item['pdf_url']);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()}");
                        $failed++;

                        continue;
                    }
                    $size = is_file($tmp) ? filesize($tmp) : 0;
                    if ($size < 1000) {
                        @unlink($tmp);
                        $this->error("  suspiciously small ({$size} bytes)");
                        $failed++;

                        continue;
                    }
                    rename($tmp, $localPath);
                    $this->info('  saved '.number_format($size / 1024, 1).' KB to '.$webPath);
                    $downloaded++;
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage());
                    $failed++;

                    continue;
                }
            } else {
                $this->line('exists '.$webPath);
                $skipped++;
            }

            $record = $item['record'] + [
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info('  RECORD updated: '.$record['title']);
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info('  RECORD added: '.$record['title']);
            }
            $registered++;
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} Failed={$failed}");

        return self::SUCCESS;
    }
}
