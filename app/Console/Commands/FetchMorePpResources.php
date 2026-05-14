<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads 78 PP-relevant PDFs surfaced from 15 movement /
 * solidarity sites that weren't yet in our archive:
 *
 *   - Jericho Movement (10) — newsletters, organizing manuals,
 *     UN OHCHR submission
 *   - Center for Constitutional Rights (14) — GTMO, CMU, torture
 *   - The Final Straw Radio (10) — TFSR zine series
 *   - Solitary Watch (8) — Calculating Torture, Louisiana on
 *     Lockdown, fact sheets
 *   - Free Mumia movement (7) — fact sheets, IFJ letters,
 *     Peace & Freedom pamphlet
 *   - Prison Legal News / HRDC (6)
 *   - Sprout Distro (5)
 *   - NLG (4), CLDC (3), Black & Pink (2), Free Peltier (2)
 *   - Earth First!, Free Alabama Movement, PFOC, SDS, Freedom
 *     Archives misc, Weather Underground misc, Journal of
 *     Prisoners on Prisons (1 each)
 *
 * Long-running: full run downloads ~78 PDFs to varied
 * public/pdfs/<source>/ paths. Idempotent — skips files already on
 * disk; --force re-downloads; --limit=N caps batch size.
 */
final class FetchMorePpResources extends Command {
    protected $signature = 'archive:fetch-more-pp-resources {--force : Re-download even if local file exists} {--limit=0 : Cap batch size}';
    protected $description = 'Fetch 78 PP-relevant PDFs from Jericho / CCR / Solitary Watch / Final Straw Radio / etc.';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');
        $payloads = json_decode(file_get_contents(database_path('data/more-pp-resources.json')), true);
        if ($limit > 0) {
            $payloads = array_slice($payloads, 0, $limit);
        }

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $url = $payload['pdf_url'];
            $dir = public_path($payload['public_dir']);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filename = $slug.'.pdf';
            $localPath = $dir.DIRECTORY_SEPARATOR.$filename;
            $webPath = '/'.$payload['public_dir'].'/'.$filename;

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
                'collection' => $payload['collection'],
                'publisher' => $payload['source'] ?? null,
                'authors' => null,
                'year' => $payload['year'] ?? null,
                'subjects' => array_values(array_filter([$payload['collection'], 'Political Prisoners', $payload['relevance'] ?? null])),
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
