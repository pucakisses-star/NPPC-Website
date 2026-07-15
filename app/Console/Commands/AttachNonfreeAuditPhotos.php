<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the low-resolution, fair-use portraits for the High-confidence
 * "confirmed online photo" rows of the top-100 photo audit — the ones whose
 * only sources are copyrighted (press/booking/courtesy photos), rehosted at
 * low resolution under the same non-commercial fair-use rationale the site
 * already uses for its other non-free portraits (see CREDITS-nonfree.md).
 *
 * Only three of the seven such rows were rehostable: the others were skipped
 * because the retrievable image could not identify the person (Robert Majors —
 * unlabeled group protest shot), had no clean non-Getty source (Khalil Islam),
 * came from a host with a broken TLS certificate (Francisco Torres — SF8
 * brochure), or had gone 404 at its origin (Mohaman Koti). Those await a
 * usable source or a rights determination.
 *
 * Matched by slug; idempotent (skips any record that already has a photo).
 */
final class AttachNonfreeAuditPhotos extends Command
{
    protected $signature = 'prisoners:attach-nonfree-audit-photos';

    protected $description = 'Attach the fair-use (nonfree) portraits from the top-100 photo audit to records with no photo';

    /** Prisoner slug => source filename in database/data/photos/nonfree/. */
    private const MAP = [
        'terrence-johnson' => 'terrence-johnson.jpg',
        'linwood-kaine'    => 'linwood-kaine.jpg',
        'stephen-bingham'  => 'stephen-bingham.jpg',
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        foreach (self::MAP as $slug => $file) {
            $src = database_path("data/photos/nonfree/{$file}");
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
