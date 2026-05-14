<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads the 33 PDFs hosted at midnightspecial.net/materials/ —
 * the Midnight Special Law Collective's full library of activist
 * legal-support materials: know-your-rights pamphlets, mass-defense
 * organising guides, grand-jury resistance primers, trainer's
 * curricula, and legal-observer handbooks. PDFs land in
 * public/pdfs/midnight-special/.
 */
final class FetchMidnightSpecialPdfs extends Command {
    protected $signature = 'archive:fetch-midnight-special-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Fetch 33 Midnight Special Law Collective PDFs';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/midnight-special-pdfs.json')), true);

        $dir = public_path('pdfs/midnight-special');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $url = $payload['pdf_url'];
            $filename = $slug.'.pdf';
            $localPath = $dir.DIRECTORY_SEPARATOR.$filename;
            $webPath = '/pdfs/midnight-special/'.$filename;

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

            $subjects = array_filter(['Midnight Special Law Collective', 'Mass Defense', 'Know Your Rights', $payload['topic'] ?? null]);
            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'collection' => 'Midnight Special Law Collective',
                'publisher' => 'Midnight Special Law Collective',
                'authors' => 'Midnight Special Law Collective',
                'year' => $payload['year'] ?? null,
                'subjects' => array_values(array_unique($subjects)),
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
