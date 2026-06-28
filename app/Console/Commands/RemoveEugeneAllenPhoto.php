<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes the incorrect profile photo from the Eugene Allen record: deletes the
 * image file from the public disk and clears the `photo` column. Idempotent —
 * if the record already has no photo it reports so and exits. Matches by slug,
 * then name.
 */
final class RemoveEugeneAllenPhoto extends Command
{
    protected $signature = 'prisoners:remove-eugene-allen-photo';

    protected $description = "Remove and delete Eugene Allen's (incorrect) profile photo";

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'eugene-allen')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Eugene Allen')->first();

        if (! $prisoner) {
            $this->error('No Eugene Allen record found.');

            return self::FAILURE;
        }

        if (! $prisoner->photo) {
            $this->info("{$prisoner->name} already has no photo — nothing to remove.");

            return self::SUCCESS;
        }

        // The column stores a disk-relative path (e.g. "prisoners/eugene-allen.jpg"),
        // but normalize in case a full /storage/ URL was stored.
        $relative = $prisoner->photo;
        if (str_contains($relative, '/storage/')) {
            $relative = substr($relative, strpos($relative, '/storage/') + strlen('/storage/'));
        }

        if (Storage::disk('public')->exists($relative)) {
            Storage::disk('public')->delete($relative);
            $this->info("Deleted image file from public disk: {$relative}");
        } else {
            $this->warn("Image file not found on public disk ({$relative}); clearing the field anyway.");
        }

        $prisoner->photo = null;
        $prisoner->save();
        $this->info("Cleared photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
