<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Registers Freedom Archives Collection 1109 — San Francisco 8
 * (SF8): materials from the campaign to free former Black Panther
 * Party members charged in connection with a 1971 SF police-station
 * attack. Downloads the one digitized PDF (CDHR torture brochure)
 * and registers the other nine items as non-digitized catalog
 * references pointing at the Freedom Archives record landing page.
 */
final class FetchFreedomArchivesC1109 extends Command {
    protected $signature = 'archive:fetch-freedom-archives-c1109 {--force : Re-download even if local file exists}';
    protected $description = 'Fetch Freedom Archives Collection 1109 (San Francisco 8) and register ArchiveRecords';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-collection-1109.json')), true);

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
            $isDigitized = (bool) ($payload['is_digitized'] ?? false);
            $webPath = null;

            if ($isDigitized && ! empty($payload['pdf_url'])) {
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
                            $this->error('  suspiciously small response ('.$size.' bytes).');
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
            } else {
                $webPath = $payload['record_url'] ?? null;
                $this->line('catalog-only: '.$payload['title']);
                $skipped++;
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'collection' => 'Freedom Archives — San Francisco 8',
                'publisher' => $payload['publisher'] ?? 'Freedom Archives',
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['San Francisco 8', 'Black Panther Party', 'COINTELPRO', 'Torture', 'Black Liberation'],
                'is_digitized' => $isDigitized,
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
