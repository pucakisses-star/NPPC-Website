<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Imports 78 PP-relevant records from 22 Freedom Archives
 * collections beyond our four existing imports (c29, c1013, c1057,
 * c1109). One collection per named political prisoner or movement
 * (Assata Shakur, Geronimo Ji-Jaga / Pratt, SLA, Attica, NY3,
 * Panther 21, Soledad Brothers, Mutulu Shakur, Sundiata Acoli,
 * BLA, Dhoruba bin Wahad, Safiya Bukhari, Donald Cox, Angela
 * Davis, Fred Hampton, Republic of New Afrika, Dessie Woods,
 * Marilyn Buck, David Gilbert, Angola 3, NCDNAFF, Richard Aoki,
 * Puerto Rican prisoners, Breakthrough, John Brown Anti-Klan
 * Committee, Mabel & Robert F. Williams, Arm the Spirit,
 * Resistance Conspiracy Case).
 *
 * 46 PDFs are mirrored to public/pdfs/freedom-archives/; 24 mp3
 * audio + 8 Vimeo videos stay remote.
 */
final class FetchFreedomArchivesPpCollections extends Command {
    protected $signature = 'archive:fetch-freedom-archives-pp-collections {--force : Re-download even if local file exists}';
    protected $description = 'Fetch 78 PP-themed records across 22 Freedom Archives collections';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-pp-collections.json')), true);

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
            $isPdf = ($payload['record_type'] ?? 'document') === 'document' && ($payload['source_format'] ?? 'pdf') === 'pdf';
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
                            ->withOptions(['sink' => $tmp])
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
                $this->line('remote: '.$payload['title']);
                $skipped++;
            }

            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => $payload['record_type'] ?? 'document',
                'source_format' => $payload['source_format'] ?? 'pdf',
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
