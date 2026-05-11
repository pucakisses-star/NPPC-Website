<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportFreedomArchivesAudioBatch2 extends Command {
    protected $signature = 'archive:import-fa-audio-batch2';
    protected $description = 'Import additional Freedom Archives political-prisoner MP3s (PP subject id 19891)';

    public function handle(): int {
        $path = database_path('data/fa-audio-batch2.json');
        if (! is_file($path)) {
            $this->error("Source JSON not found at {$path}");

            return self::FAILURE;
        }
        $records = json_decode(file_get_contents($path), true);
        if (! is_array($records)) {
            $this->error('Could not parse fa-audio-batch2.json');

            return self::FAILURE;
        }

        $created = 0;
        $updated = 0;
        $missing = 0;
        $sort = 320;

        foreach ($records as $r) {
            $id = $r['id'] ?? null;
            $file = $r['file'] ?? null;
            $title = trim((string) ($r['title'] ?? ''));
            $desc = trim((string) ($r['desc'] ?? ''));
            if (! $id || ! $file || ! $title) {
                continue;
            }

            $localPath = public_path('audio/freedom-archives/'.$file);
            if (! is_file($localPath)) {
                $missing++;
                $this->warn("skip (file missing): {$file}");

                continue;
            }

            $slug = 'freedom-archives-audio-'.$id;
            $payload = [
                'title' => $title,
                'description' => ($desc !== '' ? $desc.' ' : '').'Sourced from search.freedomarchives.org.',
                'record_type' => 'audio',
                'source_format' => 'mp3',
                'file' => '/audio/freedom-archives/'.$file,
                'collection' => 'Freedom Archives',
                'subjects' => ['Political Prisoners'],
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $slug] + $payload);
                $created++;
            }
        }

        $this->info("Done. Created={$created} Updated={$updated} MissingFile={$missing}");

        return self::SUCCESS;
    }
}
