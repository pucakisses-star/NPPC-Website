<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Mirrors Freedom Archives mp3 audio records to local storage.
 * Targets every ArchiveRecord where `record_type=audio` and `file`
 * points at freedomarchives.org (or starts with a remote URL).
 * Downloads each mp3 to public/audio/freedom-archives/ and
 * rewrites the file column to the local web path.
 *
 * Idempotent: skips files already on disk; --force re-downloads.
 * Failures leave the record pointing at the remote URL so the
 * player still works.
 */
final class MirrorFreedomArchivesAudio extends Command {
    protected $signature = 'archive:mirror-freedom-archives-audio {--force : Re-download even if local file exists} {--limit=0 : Only process this many records (0 = all)}';
    protected $description = 'Mirror Freedom Archives mp3 audio records to public/audio/freedom-archives/';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $limit = (int) $this->option('limit');

        $dir = public_path('audio/freedom-archives');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $query = ArchiveRecord::where('record_type', 'audio')
            ->where('file', 'like', '%freedomarchives.org%');
        if ($limit > 0) {
            $query->limit($limit);
        }
        $records = $query->get();

        $this->info('Found '.$records->count().' Freedom Archives audio records to mirror.');

        $downloaded = 0;
        $skipped = 0;
        $failed = 0;
        $rewritten = 0;

        foreach ($records as $r) {
            $url = $r->file;
            if (! str_starts_with($url, 'http')) {
                $this->line('  already local: '.$r->slug);
                $skipped++;

                continue;
            }
            $filename = $r->slug.'.mp3';
            $localPath = $dir.DIRECTORY_SEPARATOR.$filename;
            $webPath = '/audio/freedom-archives/'.$filename;

            if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                $this->line("fetch {$url}");
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp, 'allow_redirects' => true])
                        ->timeout(900)
                        ->get($url);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()} — leaving remote.");
                        $failed++;

                        continue;
                    }
                    $size = is_file($tmp) ? filesize($tmp) : 0;
                    if ($size < 1000) {
                        @unlink($tmp);
                        $this->error("  suspiciously small ({$size} bytes) — leaving remote.");
                        $failed++;

                        continue;
                    }
                    rename($tmp, $localPath);
                    $this->info('  saved '.number_format($size / 1024 / 1024, 1).' MB to '.$webPath);
                    $downloaded++;
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage().' — leaving remote.');
                    $failed++;

                    continue;
                }
            } else {
                $this->line('exists '.$webPath);
                $skipped++;
            }

            if ($r->file !== $webPath) {
                $r->file = $webPath;
                $r->save();
                $rewritten++;
            }
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Failed={$failed} Rewritten={$rewritten}");

        return self::SUCCESS;
    }
}
