<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads Freedom Archives Collection 1057 — Government
 * Repression. 82 records, all net-new (no overlap with the c29 /
 * c1013 / c1109 imports per the enumeration agent). 70 PDFs are
 * mirrored to public/pdfs/freedom-archives/; 12 video URLs
 * (Vimeo COINTELPRO 101 series + extras) are registered with
 * their remote URL as the file.
 */
final class FetchFreedomArchivesC1057 extends Command {
    protected $signature = 'archive:fetch-freedom-archives-c1057 {--force : Re-download even if local file exists}';
    protected $description = 'Fetch FA Collection 1057 (Government Repression) — 82 records';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-collection-1057.json')), true);

        $pdfDir = public_path('pdfs/freedom-archives');
        if (! is_dir($pdfDir)) {
            mkdir($pdfDir, 0755, true);
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $url = $payload['pdf_url'];
            $isPdf = ($payload['record_type'] ?? 'document') === 'document' && ($payload['source_format'] ?? 'pdf') === 'pdf';
            $filePath = null;

            if ($isPdf) {
                $filename = $slug.'.pdf';
                $localPath = $pdfDir.DIRECTORY_SEPARATOR.$filename;
                $filePath = '/pdfs/freedom-archives/'.$filename;

                if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                    $this->line("fetch {$url}");
                    $tmp = $localPath.'.partial';
                    try {
                        $resp = Http::withHeaders([
                            'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                        ])
                            ->withOptions(['sink' => $tmp])
                            ->timeout(600)
                            ->get($url);
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
                        $this->info('  saved '.number_format($size / 1024, 1).' KB to '.$filePath);
                        $downloaded++;
                    } catch (\Throwable $e) {
                        @unlink($tmp);
                        $this->error('  '.$e->getMessage());
                        $failed++;

                        continue;
                    }
                } else {
                    $this->line('exists '.$filePath);
                    $skipped++;
                }
            } else {
                $filePath = $url;
                $this->line('remote: '.$payload['title'].' -> '.$url);
                $skipped++;
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => $payload['record_type'] ?? 'document',
                'source_format' => $payload['source_format'] ?? 'pdf',
                'file' => $filePath,
                'collection' => 'Freedom Archives — Government Repression',
                'publisher' => $payload['publisher'] ?? 'Freedom Archives',
                'authors' => is_array($payload['authors'] ?? null) ? implode(', ', $payload['authors']) : ($payload['authors'] ?? null),
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['Government Repression', 'COINTELPRO', 'Grand Jury Resistance', 'FBI Surveillance', 'Freedom Archives'],
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info('  RECORD updated: '.$payload['title']);
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info('  RECORD added: '.$payload['title']);
            }
            $registered++;
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} Failed={$failed}");

        return self::SUCCESS;
    }
}
