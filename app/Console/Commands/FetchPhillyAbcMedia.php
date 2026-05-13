<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads the Philly ABC media corpus surfaced from phillyabc.org and
 * registers each item as an ArchiveRecord. PDFs land in
 * public/pdfs/philly-abc/, audio in public/audio/philly-abc/, and
 * video in public/videos/philly-abc/.
 *
 * Idempotent — skips downloads when the local file is already present
 * (and a healthy non-zero size). --force re-downloads.
 */
final class FetchPhillyAbcMedia extends Command {
    protected $signature = 'archive:fetch-philly-abc-media {--force : Re-download even if local file exists}';
    protected $description = 'Download Philly ABC PDFs / audio / video and register ArchiveRecords';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/philly-abc-media.json')), true);

        $dirs = [
            'pdf' => public_path('pdfs/philly-abc'),
            'audio' => public_path('audio/philly-abc'),
            'video' => public_path('videos/philly-abc'),
        ];
        $webPrefixes = [
            'pdf' => '/pdfs/philly-abc',
            'audio' => '/audio/philly-abc',
            'video' => '/videos/philly-abc',
        ];
        $recordTypes = [
            'pdf' => 'document',
            'audio' => 'audio',
            'video' => 'video',
        ];
        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $format = $payload['source_format'];
            $url = $payload['file_url'];
            $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: $format;
            $filename = $slug.'.'.$ext;
            $localPath = $dirs[$format].DIRECTORY_SEPARATOR.$filename;
            $webPath = $webPrefixes[$format].'/'.$filename;

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

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'],
                'record_type' => $recordTypes[$format],
                'source_format' => $format,
                'file' => $webPath,
                'collection' => $payload['collection'] ?? 'Philly ABC',
                'publisher' => 'Philadelphia Anarchist Black Cross',
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['Philly ABC', 'Anarchist Black Cross', 'Political Prisoners'],
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
