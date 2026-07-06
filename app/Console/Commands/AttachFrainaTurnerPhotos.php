<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits to two existing prisoner records:
 *   - Louis Fraina — non-free archival portrait (spartacus-educational.com).
 *   - John Turner — public-domain portrait (Current Literature, 1904, via
 *     Wikimedia Commons).
 *
 * Only fills a record that has no photo; never overwrites an existing image.
 * Idempotent.
 */
class AttachFrainaTurnerPhotos extends Command
{
    protected $signature = 'prisoners:attach-fraina-turner-photos';

    protected $description = 'Attach portraits for Louis Fraina and John Turner';

    public function handle(): int
    {
        $map = [
            'louis-fraina' => database_path('data/photos/nonfree/louis-fraina.jpg'),
            'john-turner' => database_path('data/photos/john-turner.jpg'),
        ];

        foreach ($map as $slug => $src) {
            $p = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("Not found: {$slug}");

                continue;
            }
            if (! is_file($src)) {
                $this->warn("Missing file for {$slug}: {$src}");

                continue;
            }
            if (! empty($p->photo)) {
                $this->info("Already has a photo: {$p->name}");

                continue;
            }
            Storage::disk('public')->makeDirectory('prisoners');
            $dest = 'prisoners/'.basename($src);
            Storage::disk('public')->put($dest, file_get_contents($src));
            $p->photo = $dest;
            $p->save();
            $this->info("Linked photo for {$p->name} ({$slug}).");
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
