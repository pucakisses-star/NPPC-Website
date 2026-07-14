<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the three ready-to-use, public-domain portraits surfaced by the
 * top-100 photo audit (the "Downloaded / usable now", High-confidence rows) to
 * their prisoner records — but only where the record currently has no photo.
 *
 * Matched by slug (stable across the name corrections in
 * prisoners:apply-top100-revisions). Idempotent: skips any record that already
 * carries a photo. Sources / public-domain basis: see
 * database/data/photos/CREDITS-wikipedia.md.
 *
 * The other "High"-rated audit rows are leads that still need archival
 * digitization or a rights determination (Alamy/NARA, Idaho State Archives,
 * Getty/AP, courtesy photos) or are sensitive images flagged do-not-use
 * (autopsy photographs), so they are intentionally not attached here.
 */
final class AttachTop100AuditPhotos extends Command
{
    protected $signature = 'prisoners:attach-top100-photos';

    protected $description = 'Attach the public-domain portraits from the top-100 photo audit to records with no photo';

    /** Prisoner slug => source filename in database/data/photos/. */
    private const MAP = [
        'edgar-timmons-jr'          => 'edgar-timmons-jr.jpg',
        'andres-figueroa-cordero-2' => 'andres-figueroa-cordero.jpg',
        'muhammad-abdul-aziz'       => 'muhammad-abdul-aziz.jpg',
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        foreach (self::MAP as $slug => $file) {
            $src = database_path("data/photos/{$file}");
            if (! is_file($src)) {
                $this->warn("Source image not found: {$src}");

                continue;
            }

            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $missing[] = $slug;

                continue;
            }

            if (! empty($prisoner->photo)) {
                $this->line("{$prisoner->name} already has a photo — leaving alone.");
                $skipped++;

                continue;
            }

            $relative = 'prisoners/'.$file;
            Storage::disk('public')->put($relative, (string) file_get_contents($src));
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info("Linked photo for {$prisoner->name}.");
            $linked++;
        }

        if ($linked > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->info("\nDone. Linked={$linked}, already-had-photo={$skipped}.");
        if ($missing) {
            $this->warn('Not found by slug ('.count($missing).'): '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }
}
