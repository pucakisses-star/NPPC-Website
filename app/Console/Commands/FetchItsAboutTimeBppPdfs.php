<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads the political-prisoner PDF corpus held by itsabouttimebpp.com
 * into public/pdfs/itsabouttimebpp/ and registers each as an
 * ArchiveRecord. Streams downloads via Guzzle sink, the same as the
 * Boston ABC and Freedom Archives fetchers.
 *
 * The source is a Black Panther Party alumni site whose files date from
 * roughly 1995 to 2013 and are served over plain HTTP from a host with no
 * redundancy. Seven of the seventeen are image-only scans with no text
 * layer, flagged in the manifest as has_text_layer false and recorded on
 * the ArchiveRecord subjects so archive:audit-pdf-ocr can find them.
 */
final class FetchItsAboutTimeBppPdfs extends Command {
    protected $signature = 'archive:fetch-itsabouttimebpp-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Download the itsabouttimebpp.com political-prisoner PDFs (rosters, case files, event material)';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/itsabouttimebpp-pdfs.json')), true);

        if (! $payloads) {
            $this->error('Could not read database/data/itsabouttimebpp-pdfs.json');

            return self::FAILURE;
        }

        $publicDir = public_path('pdfs/itsabouttimebpp');
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
            $webPath = '/pdfs/itsabouttimebpp/'.$filename;

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
                        $this->error('  Suspiciously small response ('.$size.' bytes).');
                        $failed++;

                        continue;
                    }
                    // The source serves an HTML error page with a 200 on some
                    // missing files, so check the magic number rather than
                    // trusting the status code.
                    if (file_get_contents($tmp, false, null, 0, 5) !== '%PDF-') {
                        @unlink($tmp);
                        $this->error('  Response is not a PDF — skipping registration.');
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

            $subjects = ['Black Panther Party', 'Political Prisoners', "It's About Time BPP"];

            // Flag the image-only scans so the OCR audit can pick them up
            // rather than someone discovering it by opening the file.
            if (($payload['has_text_layer'] ?? true) === false) {
                $subjects[] = 'Needs OCR';
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'],
                'record_type' => 'document',
                'source_format' => $payload['source_format'] ?? 'document',
                'file' => $webPath,
                'collection' => "It's About Time BPP — Political Prisoners",
                'authors' => $payload['authors'] ?? null,
                'publisher' => "It's About Time BPP",
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => $subjects,
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

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
