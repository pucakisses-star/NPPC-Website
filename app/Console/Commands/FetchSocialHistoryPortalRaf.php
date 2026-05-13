<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads the U.S. political-prisoner solidarity material from
 * socialhistoryportal.org — primarily "Mappe 18: USA" of the
 * Rote Armee Fraktion document archive (BPP / WUO / George
 * Jackson / SLA / FALN / Mumia ephemera) plus the CIRA Sacco &
 * Vanzetti 1928 memorial print. PDFs land in
 * public/pdfs/social-history-portal/, image in
 * public/images/social-history-portal/.
 */
final class FetchSocialHistoryPortalRaf extends Command {
    protected $signature = 'archive:fetch-social-history-portal-raf {--force : Re-download even if local file exists}';
    protected $description = 'Fetch socialhistoryportal.org RAF Mappe 18 (USA) + CIRA Sacco & Vanzetti';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/social-history-portal-raf.json')), true);

        $pdfDir = public_path('pdfs/social-history-portal');
        $imgDir = public_path('images/social-history-portal');
        foreach ([$pdfDir, $imgDir] as $dir) {
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
            $url = $payload['pdf_url'];
            $isImage = ($payload['record_type'] ?? 'document') === 'image';
            $ext = $isImage ? (strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg') : 'pdf';
            $filename = $slug.'.'.$ext;
            $localDir = $isImage ? $imgDir : $pdfDir;
            $webPrefix = $isImage ? '/images/social-history-portal' : '/pdfs/social-history-portal';
            $localPath = $localDir.DIRECTORY_SEPARATOR.$filename;
            $webPath = $webPrefix.'/'.$filename;

            if (! is_file($localPath) || $force || filesize($localPath) < 500) {
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
                    if ($size < 500) {
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

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => $payload['record_type'] ?? 'document',
                'source_format' => $payload['source_format'] ?? 'pdf',
                'file' => $webPath,
                'collection' => 'Social History Portal — RAF Mappe 18 (USA)',
                'publisher' => $payload['publisher'] ?? 'Social History Portal',
                'authors' => $payload['authors'] ?? null,
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['International Solidarity', 'Black Panther Party', 'Weather Underground', 'George Jackson', 'George Jackson Brigade', 'Mumia Abu-Jamal', 'Sacco and Vanzetti', 'FALN'],
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
