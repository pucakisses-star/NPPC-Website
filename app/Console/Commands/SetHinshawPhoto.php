<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets John Hinshaw's profile photo from the committed source image (an EWTN
 * portrait). Copies it to the public disk and attaches it to the John Hinshaw
 * record. Idempotent; matches by slug then name.
 */
final class SetHinshawPhoto extends Command
{
    protected $signature = 'prisoners:set-hinshaw-photo';

    protected $description = "Set John Hinshaw's profile photo from the committed image";

    private const SOURCE = 'data/photos/legacy/john-hinshaw.jpg';

    private const PHOTO = 'prisoners/john-hinshaw.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'john-hinshaw')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'John Hinshaw')->first();

        if (! $prisoner) {
            $this->warn('No John Hinshaw record found — photo copied, but nothing to attach it to.');

            return self::SUCCESS;
        }

        $prisoner->photo = self::PHOTO;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
