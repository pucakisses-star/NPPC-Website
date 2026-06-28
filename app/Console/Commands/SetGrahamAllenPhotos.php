<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Fixes up the "San Quentin Two" records — Eugene Allen and Ernest "Shujaa"
 * Graham (record slug eugene-graham):
 *
 *  - Eugene Allen: correct portrait cropped from the "Free Graham and Allen"
 *    defense pamphlet (Freedom Archives), replacing the earlier incorrect
 *    photo. This supersedes prisoners:remove-eugene-allen-photo — running this
 *    command alone restores the correct image.
 *  - Ernest Graham: a present-day portrait (El País); aka updated to
 *    "Ernest Graham, Shujaa Graham".
 *  - Both: case dates set — incarceration 1973-11-27 (the day the guard was
 *    killed at the Deuel Vocational Institution, per the defense pamphlet) and
 *    release March 1981 (month precision), when they were acquitted at retrial.
 *
 * Idempotent; matches each record by slug. Safe to re-run.
 */
final class SetGrahamAllenPhotos extends Command
{
    protected $signature = 'prisoners:set-graham-allen-photos';

    protected $description = "Set the San Quentin Two photos, Graham's Shujaa alias, and both case dates";

    public function handle(): int
    {
        $allen = $this->setPhoto('eugene-allen', 'Eugene Allen', 'eugene-allen.jpg');
        if ($allen) {
            $this->setCaseDates($allen);
        }

        $graham = $this->setPhoto('eugene-graham', 'Graham', 'eugene-graham.jpg');
        if ($graham) {
            $graham->aka = 'Ernest Graham, Shujaa Graham';
            $graham->save();
            $this->info("Set aka on {$graham->name}: {$graham->aka}");
            $this->setCaseDates($graham);
        }

        return self::SUCCESS;
    }

    /** Copy a committed image to the public disk and attach it to the record by slug. Returns the prisoner. */
    private function setPhoto(string $slug, string $nameLike, string $file): ?Prisoner
    {
        $source = public_path('images/prisoners/'.$file);
        if (! is_file($source)) {
            $this->error('Source image not found: public/images/prisoners/'.$file);

            return null;
        }

        $stored = 'prisoners/'.$file;
        Storage::disk('public')->put($stored, file_get_contents($source));

        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', "%{$nameLike}%")->first();

        if (! $prisoner) {
            $this->warn("No record for slug '{$slug}' — photo copied to disk but not attached.");

            return null;
        }

        $prisoner->photo = $stored;
        $prisoner->save();
        $this->info("Set photo on {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return $prisoner;
    }

    /**
     * Set the case dates on the prisoner's (single) case: incarcerated on the
     * day the guard was killed (Nov 27, 1973) and released March 1981. Stored
     * with the documented precision via the HasPartialDates trait.
     */
    private function setCaseDates(Prisoner $prisoner): void
    {
        $case = $prisoner->cases()->first();
        if (! $case) {
            $this->warn("No case on {$prisoner->name}; skipping case dates.");

            return;
        }

        $case->setPartialDate('incarceration_date', 1973, 11, 27);
        $case->setPartialDate('release_date', 1981, 3);
        $case->save();
        $this->info("Set case dates on {$prisoner->name}: incarcerated 1973-11-27, released March 1981.");
    }
}
