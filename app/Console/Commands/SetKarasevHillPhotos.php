<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets profile photos for Peter Karasev and Autumn Hill from committed source
 * images, copying each to the public disk and attaching it to the matching
 * prisoner. Idempotent; matches by name, warns if a record or source is absent.
 */
final class SetKarasevHillPhotos extends Command
{
    protected $signature = 'prisoners:set-karasev-hill-photos';

    protected $description = 'Set profile photos for Peter Karasev and Autumn Hill';

    /** match fragment => [source under public/, public-disk path, label] */
    private const PHOTOS = [
        'Karasev' => ['images/prisoners/peter-karasev.jpg', 'prisoners/peter-karasev.jpg', 'Peter Karasev'],
        'Autumn Hill' => ['images/prisoners/autumn-hill.jpg', 'prisoners/autumn-hill.jpg', 'Autumn Hill'],
    ];

    public function handle(): int
    {
        foreach (self::PHOTOS as $fragment => [$source, $dest, $label]) {
            $src = public_path($source);
            if (! is_file($src)) {
                $this->warn("Source image missing: public/{$source}");

                continue;
            }

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', 'like', '%'.$fragment.'%')->first();
            if (! $prisoner) {
                $this->warn("No prisoner matching '{$label}' found — skipped.");

                continue;
            }

            Storage::disk('public')->put($dest, file_get_contents($src));
            $prisoner->photo = $dest;
            $prisoner->save();
            $this->info("Set photo on {$prisoner->name}: {$dest}");
        }

        return self::SUCCESS;
    }
}
