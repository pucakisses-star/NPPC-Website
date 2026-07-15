<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches freely-licensed (public-domain) Wikipedia lead portraits to ten
 * prisoner records that had no photo, sourced by cross-referencing the English
 * Wikipedia "Category:Industrial Workers of the World members" against the
 * database's photo-less entries. Each match was verified to be the SAME person
 * (biography, dates, and story — not just the name) before inclusion, so these
 * are not name collisions. Images are stored in database/data/photos/ and
 * credited in CREDITS-wikipedia.md.
 *
 * Only fills a record whose photo is currently empty (never overwrites).
 * Idempotent.
 */
final class AttachIwwWikipediaPhotos extends Command
{
    protected $signature = 'prisoners:attach-iww-wikipedia-photos';

    protected $description = 'Attach public-domain Wikipedia portraits to 10 photo-less IWW-member prisoners';

    /** prisoner name => photo file in database/data/photos/ */
    private const MAP = [
        'George Andreytchine' => 'george-andreytchine.jpg',
        'Arthur Caron' => 'arthur-caron.jpg',
        'Jay Fox' => 'jay-fox.jpg',
        'Emil Herman' => 'emil-herman.jpg',
        'James Larkin' => 'james-larkin.jpg',
        'Frank Tannenbaum' => 'frank-tannenbaum.jpg',
        'Albert Weisbord' => 'albert-weisbord.jpg',
        'Carl Skoglund' => 'carl-skoglund.jpg',
        'Carl Paivio' => 'carl-paivio.jpg',
        'Bill Haywood' => 'bill-haywood.jpg',
    ];

    public function handle(): int
    {
        $set = 0;
        $skipped = 0;
        foreach (self::MAP as $name => $file) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$name}");

                continue;
            }
            if (! empty($prisoner->photo)) {
                $this->line("Has photo, skipped: {$name}");
                $skipped++;

                continue;
            }
            $src = database_path('data/photos/'.$file);
            if (! is_file($src)) {
                $this->warn("Photo file missing: {$file}");

                continue;
            }
            Storage::disk('public')->makeDirectory('prisoners');
            Storage::disk('public')->put('prisoners/'.$file, (string) file_get_contents($src));
            $prisoner->photo = 'prisoners/'.$file;
            $prisoner->save();
            $this->info("Set photo: {$name} -> prisoners/{$file}");
            $set++;
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("Done. set={$set} skipped(existing)={$skipped}");

        return self::SUCCESS;
    }
}
