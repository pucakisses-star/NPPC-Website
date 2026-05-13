<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads Freedom Archives Collection 15 (Puerto Rican POWs /
 * Independence Movement) — 13 net new items: 12 PDFs + 1 MP3 audio.
 * PDFs go to public/pdfs/freedom-archives/; the MP3 to
 * public/audio/freedom-archives/. Streams via Guzzle sink.
 */
final class FetchFreedomArchivesC15 extends Command {
    protected $signature = 'archive:fetch-freedom-archives-c15 {--force : Re-download even if local file exists}';
    protected $description = 'Download Freedom Archives Collection 15 (Puerto Rican POWs) PDFs + audio';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-collection-15.json')), true);

        $pdfDir = public_path('pdfs/freedom-archives');
        $audioDir = public_path('audio/freedom-archives');
        foreach ([$pdfDir, $audioDir] as $d) {
            if (! is_dir($d)) {
                mkdir($d, 0755, true);
            }
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $isAudio = ($payload['source_format'] ?? '') === 'audio';
            $ext = $isAudio ? 'mp3' : 'pdf';
            $filename = $slug.'.'.$ext;
            $localPath = ($isAudio ? $audioDir : $pdfDir).DIRECTORY_SEPARATOR.$filename;
            $webPath = ($isAudio ? '/audio/freedom-archives/' : '/pdfs/freedom-archives/').$filename;

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
                        $this->error('  Suspiciously small ('.$size.' bytes).');
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
                'title' => $payload['title'],
                'description' => $payload['description'],
                'record_type' => $isAudio ? 'audio' : 'document',
                'source_format' => $payload['source_format'] ?? 'pamphlet',
                'file' => $webPath,
                'collection' => 'Freedom Archives — Puerto Rican POWs',
                'authors' => $payload['authors'] ?? null,
                'publisher' => $payload['publisher'] ?? null,
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['Puerto Rican Independence', 'FALN', 'Macheteros', 'Political Prisoners'],
                'is_digitized' => true,
                'published' => true,
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
