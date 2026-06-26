<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Removes the four Canadian "Direct Action" / "Squamish Five" members — Ann
 * Hansen, Brent Taylor, Gerry Hannah, and Doug Stewart. They were Canadian
 * anarchists arrested near Squamish, B.C. in 1983 and prosecuted in Canada for
 * actions in Canada, so they fall outside the NPPC's scope (U.S. political
 * prisoners, U.S. exiles, and foreign nationals imprisoned as a direct result
 * of U.S. action). Deletes each record and its cases, calendar entries,
 * podcast episodes, and photo file. Idempotent — skips anyone already gone.
 */
final class RemoveDirectActionPrisoners extends Command
{
    protected $signature = 'prisoners:remove-direct-action';

    protected $description = 'Remove the Canadian Direct Action / Squamish Five members (out of scope)';

    public function handle(): int
    {
        $names = ['Ann Hansen', 'Brent Taylor', 'Gerry Hannah', 'Doug Stewart'];
        $removed = 0;

        foreach ($names as $name) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($name))
                ->orWhere('name', $name)
                ->first();

            if (! $prisoner) {
                $this->line("Not found (already removed?): {$name}");

                continue;
            }

            if ($prisoner->photo && Storage::disk('public')->exists($prisoner->photo)) {
                Storage::disk('public')->delete($prisoner->photo);
            }

            $cases = $prisoner->cases()->count();
            $prisoner->cases()->delete();
            $prisoner->calendarEntries()->delete();
            $prisoner->podcastEpisodes()->delete();
            $prisoner->delete();
            $removed++;

            $this->info("Removed: {$name} ({$cases} case(s)).");
        }

        $this->info("\nDone. Removed {$removed} record(s).");

        return self::SUCCESS;
    }
}
