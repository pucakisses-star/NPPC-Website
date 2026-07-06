<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits of the 1930 Imperial Valley criminal-syndicalism
 * defendants, cropped from the captioned group photograph in Labor Defender
 * Vol. 5, No. 7 (July 1930). All eight convicted defendants were already in the
 * database; seven of them had no photo (Lawrence Emery already had one). The
 * source is public domain — U.S. works published in 1930 entered the public
 * domain on January 1, 2026. See database/data/photos/CREDITS-imperial-valley-1930.md.
 *
 * Idempotent: only sets a photo when the prisoner currently has none, so it
 * never overwrites an existing image and is safe to re-run.
 */
class AttachImperialValley1930Photos extends Command
{
    protected $signature = 'prisoners:attach-imperial-valley-1930-photos';

    protected $description = 'Attach Labor Defender (1930) portraits to the Imperial Valley criminal-syndicalism defendants without photos';

    /** Prisoner name => source filename in photos/imperial-valley-1930/. */
    private const MAP = [
        'Danny Roxas' => 'danny-roxas.jpg',
        'Oscar Erickson' => 'oscar-erickson.jpg',
        'Braulio Orosco' => 'braulio-orosco.jpg',
        'Carl Sklar' => 'carl-sklar.jpg',
        'Tetsuji Horiuchi' => 'tetsuji-horiuchi.jpg',
        'Eduardo Herrera' => 'eduardo-herrera.jpg',
        'Frank Spector' => 'frank-spector.jpg',
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        foreach (self::MAP as $name => $file) {
            $src = database_path("photos/imperial-valley-1930/{$file}");
            if (! is_file($src)) {
                $this->warn("Source image not found: {$src}");

                continue;
            }

            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();
            if (! $prisoner) {
                $missing[] = $name;

                continue;
            }

            if (! empty($prisoner->photo)) {
                $this->line("{$name} already has a photo — leaving alone.");
                $skipped++;

                continue;
            }

            $relative = 'prisoners/'.$file;
            Storage::disk('public')->put($relative, file_get_contents($src));
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info("Linked photo for {$name}.");
            $linked++;
        }

        if ($linked > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->info("\nDone. Linked={$linked}, already-had-photo={$skipped}.");
        if ($missing) {
            $this->warn('Not found by name ('.count($missing).'): '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }
}
