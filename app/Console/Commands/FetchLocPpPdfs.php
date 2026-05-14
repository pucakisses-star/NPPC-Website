<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads 39 Library of Congress PDFs documenting U.S. political
 * prisoners — congressional reports / hearings on the Mooney case
 * and WWI Espionage Act amnesty, Palmer Raids documents, IWW
 * General Defense Committee broadsides, the 1886 Haymarket
 * broadsides (English + German), the Scottsboro 1934 ILD broadside,
 * Caleb Powers 1906 appeal, 17 SCOTUS opinions on Espionage Act /
 * Smith Act / criminal-syndicalism / Rosenberg cases, and the
 * 1859-1909 John Brown / Harpers Ferry corpus. PDFs land in
 * public/pdfs/loc/.
 *
 * (The Emma Goldman / Alexander Berkman 1920 prison pamphlet is
 * already imported via the prior institutional-pp-pdfs run with a
 * slightly different LoC URL — not duplicated here.)
 */
final class FetchLocPpPdfs extends Command {
    protected $signature = 'archive:fetch-loc-pp-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Fetch 39 Library of Congress PP PDFs (LoC + SCOTUS U.S. Reports)';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/loc-pp-pdfs.json')), true);

        $dir = public_path('pdfs/loc');
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
            $webPath = '/pdfs/loc/'.$filename;

            if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                $this->line("fetch {$url}");
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0; +https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp, 'allow_redirects' => true])
                        ->timeout(600)
                        ->get($url);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()} — storing remote URL.");
                        $webPath = $url;
                        $failed++;
                    } else {
                        $size = is_file($tmp) ? filesize($tmp) : 0;
                        if ($size < 1000) {
                            @unlink($tmp);
                            $this->error("  suspiciously small ({$size} bytes) — storing remote.");
                            $webPath = $url;
                            $failed++;
                        } else {
                            rename($tmp, $localPath);
                            $this->info('  saved '.number_format($size / 1024, 1).' KB to '.$webPath);
                            $downloaded++;
                        }
                    }
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage().' — storing remote.');
                    $webPath = $url;
                    $failed++;
                }
            } else {
                $this->line('exists '.$webPath);
                $skipped++;
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'collection' => $payload['collection'] ?? 'Library of Congress',
                'publisher' => $payload['publisher'] ?? null,
                'authors' => $payload['authors'] ?? null,
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['Library of Congress', 'Political Prisoners', 'Espionage Act', 'Smith Act', 'Criminal Syndicalism', 'Mooney', 'Haymarket', 'Scottsboro'],
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

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} FailedDownload={$failed}");

        return self::SUCCESS;
    }
}
