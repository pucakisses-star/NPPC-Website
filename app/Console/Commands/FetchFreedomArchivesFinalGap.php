<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Imports the 719-record final-gap sweep of Freedom Archives PP
 * collections beyond our prior 8 import files. The agent walked
 * pages 1-4 of FA's 327-collection catalog and dedup'd against
 * existing URLs + record_ids.
 *
 * Largest collections added (by net-new records):
 *   - Committee to End the Marion Lockdown (98)
 *   - Women Against Imperialism (62)
 *   - Libertad (46)
 *   - Wild Poppies — MOVE + Mumia gather files (41)
 *   - Out of Control / Lesbian Cmte to Support Women Prisoners (41)
 *   - PFOC (30), BPP NorCal (27), NAPO (25), Control Units (25)
 *   - MLN (25), JBAKC Chicago (24), FALN (23), BLM Pubs (22)
 *   - Women's Bail Fund (21), Attica (19), WUO (18), Huey
 *     Newton (15), Lucasville/Skatzes (15), Eldridge Cleaver (13)
 *   - Pelican Bay (11), plus ~22 smaller collections
 *
 * 693 PDFs are mirrored to public/pdfs/freedom-archives/; 25 mp3
 * audio + 1 other URL are kept remote (the audio-mirror command
 * can pull them later).
 *
 * Long-running: full PDF run is ~1-7 GB. --limit=N caps it.
 */
final class FetchFreedomArchivesFinalGap extends Command {
    protected $signature = 'archive:fetch-freedom-archives-final-gap {--force : Re-download even if local file exists} {--limit=0 : Cap batch size}';
    protected $description = 'Fetch 719 net-new FA records from movement collections (Marion, Pelican Bay, FALN, MLN, WUO, WAI, Attica, etc.)';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-final-gap.json')), true);
        if ($limit > 0) {
            $payloads = array_slice($payloads, 0, $limit);
        }

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
            $url = $payload['url'];
            $isPdf = $payload['asset_class'] === 'document' && $payload['ext'] === 'pdf';
            $filePath = null;

            if ($isPdf) {
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
                            ->withOptions(['sink' => $tmp, 'allow_redirects' => true])
                            ->timeout(600)
                            ->get($url);
                        if (! $resp->successful()) {
                            @unlink($tmp);
                            $this->error("  HTTP {$resp->status()} — storing remote.");
                            $filePath = $url;
                            $failed++;
                        } else {
                            $size = is_file($tmp) ? filesize($tmp) : 0;
                            if ($size < 1000) {
                                @unlink($tmp);
                                $this->error("  suspiciously small ({$size} bytes) — storing remote.");
                                $filePath = $url;
                                $failed++;
                            } else {
                                rename($tmp, $localPath);
                                $this->info('  saved '.number_format($size / 1024, 1).' KB to '.$filePath);
                                $downloaded++;
                            }
                        }
                    } catch (\Throwable $e) {
                        @unlink($tmp);
                        $this->error('  '.$e->getMessage().' — storing remote.');
                        $filePath = $url;
                        $failed++;
                    }
                } else {
                    $this->line('exists '.$filePath);
                    $skipped++;
                }
            } else {
                $filePath = $url;
                $this->line('remote ('.$payload['asset_class'].'): '.$payload['title']);
                $skipped++;
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => $payload['asset_class'] === 'audio' ? 'audio' : ($payload['asset_class'] === 'video' ? 'video' : ($payload['asset_class'] === 'image' ? 'image' : 'document')),
                'source_format' => $payload['ext'] === 'mp3' ? 'mp3' : ($payload['ext'] === 'video' ? 'video' : 'pdf'),
                'file' => $filePath,
                'collection' => 'Freedom Archives — '.$payload['collection_name'],
                'publisher' => $payload['publisher'] ?? 'Freedom Archives',
                'authors' => null,
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['Freedom Archives', 'Political Prisoners', $payload['collection_name']],
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
