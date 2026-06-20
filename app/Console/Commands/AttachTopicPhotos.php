<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets freely-licensed Wikimedia Commons background photos on topics, used as
 * the full-bleed background on the /topics page. Images are committed under
 * database/data/topic-photos/ named by topic slug; this copies each onto the
 * public disk and sets Topic->image. Topics without a committed photo keep the
 * blade's existing fallback imagery.
 *
 * Attribution for the CC images is in database/data/topic-photos/CREDITS.md.
 * Idempotent / re-syncable; topics not present are skipped with a warning.
 */
final class AttachTopicPhotos extends Command
{
    protected $signature = 'topics:attach-photos';

    protected $description = 'Set freely-licensed Wikimedia background photos on topics (from database/data/topic-photos)';

    public function handle(): int
    {
        $dir = database_path('data/topic-photos');
        $set = 0;
        $missing = 0;

        foreach (glob($dir.'/*') as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (! in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                continue; // skip CREDITS.md and anything non-image
            }

            $slug = pathinfo($file, PATHINFO_FILENAME);
            $topic = Topic::where('slug', $slug)->first();
            if (! $topic) {
                $this->warn("Topic not found, skipping: {$slug}");
                $missing++;

                continue;
            }

            $path = 'topics/'.$slug.'.'.$ext;
            Storage::disk('public')->put($path, (string) file_get_contents($file));
            $topic->image = $path;
            $topic->save();
            $this->info("  {$slug} ← {$path}");
            $set++;
        }

        $this->info("\nDone. Topic backgrounds set={$set}  Skipped={$missing}");

        return self::SUCCESS;
    }
}
