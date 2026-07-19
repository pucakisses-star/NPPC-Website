<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the 1934 Multnomah County (Portland, Oregon) booking photograph of
 * Dirk DeJonge — of the landmark De Jonge v. Oregon (1937) — to his prisoner
 * record. The source is a dual front/profile mugshot; the committed image
 * (database/data/photos/legacy/dirk-dejonge.jpg) has been cropped to the
 * front-facing portrait. Only fills an empty photo field; idempotent.
 */
final class SetDeJongePhoto extends Command
{
    protected $signature = 'prisoners:set-dejonge-photo {--dry-run : Report what would change without writing}';

    protected $description = 'Attach the committed mugshot to the Dirk DeJonge prisoner record';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $file = 'dirk-dejonge.jpg';
        $source = database_path("data/photos/legacy/{$file}");

        if (! is_file($source)) {
            $this->error("source image missing: database/data/photos/legacy/{$file}");

            return self::FAILURE;
        }

        $prisoner = Prisoner::withoutGlobalScopes()->where('name', 'Dirk DeJonge')->first();
        if (! $prisoner) {
            $this->error('Dirk DeJonge not found.');

            return self::FAILURE;
        }

        if ($prisoner->photo) {
            $this->line('Dirk DeJonge already has a photo — skipped.');

            return self::SUCCESS;
        }

        $dest = "prisoners/{$file}";
        if ($dry) {
            $this->line("[dry-run] would set Dirk DeJonge -> {$dest}");

            return self::SUCCESS;
        }

        Storage::disk('public')->put($dest, file_get_contents($source));
        $prisoner->photo = $dest;
        $prisoner->save();
        $this->info("set photo -> Dirk DeJonge ({$dest})");

        return self::SUCCESS;
    }
}
