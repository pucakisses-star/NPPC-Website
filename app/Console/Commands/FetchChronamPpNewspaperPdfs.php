<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads 58 historic newspaper-page PDFs from the Library of
 * Congress's Chronicling America collection covering 10 major U.S.
 * political-prisoner cases: Haymarket (1886-87), Joe Hill execution
 * (1915), Mooney/Billings Preparedness Day bombing (1916-39), Emma
 * Goldman / Alexander Berkman anti-conscription arrests + Soviet
 * Ark deportation (1917-19), Debs Canton speech / arrest /
 * sentencing / Harding commutation (1918-21), IWW Sept 1917 raids
 * + Chicago Case verdict (1917-18), Centralia Massacre / Wesley
 * Everest (1919), Palmer Raids (Nov 1919 + Jan 1920), Sacco-Vanzetti
 * death sentence + execution (1927), Scottsboro arrests + first
 * trials + Powell v. Alabama (1931-32).
 *
 * Note: The legacy chroniclingamerica.loc.gov/lccn/.../seq-N.pdf URL
 * pattern no longer serves PDFs; this command uses the canonical
 * tile.loc.gov/storage-services/service/ndnp/... URLs the LoC now
 * serves them from.
 */
final class FetchChronamPpNewspaperPdfs extends Command {
    protected $signature = 'archive:fetch-chronam-pp-newspaper-pdfs {--force : Re-download even if local file exists}';
    protected $description = 'Fetch 58 historic newspaper-page PDFs from LoC Chronicling America (10 PP cases)';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/chronam-pp-newspaper-pdfs.json')), true);

        $dir = public_path('pdfs/chronam');
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
            $webPath = '/pdfs/chronam/'.$filename;

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

            $title = $payload['newspaper'].' — '.$payload['date'].' ('.$payload['case'].')';
            $record = [
                'title' => $title,
                'description' => $payload['description'],
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'collection' => 'Chronicling America — '.preg_replace('/\s*\(.*\)\s*$/', '', $payload['case']),
                'publisher' => $payload['newspaper'],
                'authors' => null,
                'year' => $payload['year'],
                'date' => $payload['date'],
                'subjects' => ['Chronicling America', 'Newspaper Coverage', preg_replace('/\s*\(.*\)\s*$/', '', $payload['case'])],
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info('  RECORD updated: '.$title);
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info('  RECORD added: '.$title);
            }
            $registered++;
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} FailedDownload={$failed}");

        return self::SUCCESS;
    }
}
