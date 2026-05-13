<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads a batch of 13 Freedom Archives documents from "Collection 29
 * — Political Prisoners" (https://search.freedomarchives.org/collections/29)
 * into public/pdfs/freedom-archives/, and registers each as an
 * ArchiveRecord.
 *
 * Idempotent — skips download if the local file is already present
 * (and looks healthy), and updates rather than duplicates the
 * ArchiveRecord.
 */
final class FetchFreedomArchivesBatch extends Command {
    protected $signature = 'archive:fetch-freedom-archives-batch {--force : Re-download even if local file exists}';
    protected $description = 'Download a Freedom Archives Collection 29 batch and register ArchiveRecords';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-collection-29.json')), true);

        $publicDir = public_path('pdfs/freedom-archives');
        if (! is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $filename = $slug.'.pdf';
            $localPath = $publicDir.DIRECTORY_SEPARATOR.$filename;
            $webPath = '/pdfs/freedom-archives/'.$filename;

            if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                $this->line("fetch {$payload['pdf_url']}");
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp])
                        ->timeout(600)
                        ->get($payload['pdf_url']);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()} — skipping registration.");
                        $failed++;

                        continue;
                    }
                    $size = is_file($tmp) ? filesize($tmp) : 0;
                    if ($size < 1000) {
                        @unlink($tmp);
                        $this->error('  Suspiciously small response ('.$size.' bytes) — skipping registration.');
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

            $record = [
                'title'         => $payload['title'],
                'description'   => $payload['description'],
                'record_type'   => 'document',
                'source_format' => $payload['source_format'] ?? 'pamphlet',
                'file'          => $webPath,
                'collection'    => 'Freedom Archives — Political Prisoners',
                'authors'       => $payload['authors'] ?? null,
                'publisher'     => $payload['publisher'] ?? null,
                'year'          => $payload['year'] ?? null,
                'date'          => $payload['date'] ?? null,
                'subjects'      => ['Political Prisoners', 'Freedom Archives'],
                'is_digitized'  => true,
                'published'     => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info("  RECORD updated: {$payload['title']}");
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info("  RECORD added: {$payload['title']}");
            }
            $registered++;
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} Failed={$failed}");

        return self::SUCCESS;
    }
}
