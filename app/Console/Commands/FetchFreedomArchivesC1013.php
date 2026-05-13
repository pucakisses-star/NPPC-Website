<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Registers items from Freedom Archives Collection 1013 — White
 * Anti-Imperialist Prisoners — that aren't already present via the
 * earlier Collection 29 import. The collection has 69 records, of
 * which 60 are also in c29 (Black Flag periodical, the entire
 * Resistance Conspiracy Case set, etc.) — this command imports the
 * 5 net-new items: 3 PDFs (Galeano/Marilyn Buck, Boston Irish
 * activists pamphlet, Chuck Malone monograph) and 2 Vimeo videos
 * (David Gilbert "Lifetime of Struggle", Marilyn Buck tribute).
 */
final class FetchFreedomArchivesC1013 extends Command {
    protected $signature = 'archive:fetch-freedom-archives-c1013 {--force : Re-download even if local file exists}';
    protected $description = 'Fetch Freedom Archives Collection 1013 net-new items (5 records)';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-collection-1013.json')), true);

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
            $isVideo = ($payload['record_type'] ?? 'document') === 'video';
            $filePath = null;

            if ($isVideo) {
                $filePath = $url;
                $this->line('video: '.$payload['title'].' -> '.$url);
                $skipped++;
            } else {
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
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => $payload['record_type'] ?? 'document',
                'source_format' => $payload['source_format'] ?? 'pdf',
                'file' => $filePath,
                'collection' => 'Freedom Archives — White Anti-Imperialist Prisoners',
                'publisher' => $payload['publisher'] ?? 'Freedom Archives',
                'authors' => $payload['authors'] ?? null,
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['White Anti-Imperialist Prisoners', 'Resistance Conspiracy Case', 'Marilyn Buck', 'David Gilbert', 'Irish Republican Prisoners'],
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
