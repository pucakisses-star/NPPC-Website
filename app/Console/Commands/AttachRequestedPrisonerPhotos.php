<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches a batch of user-supplied portraits to prisoners that currently have
 * no photo. Companion to AttachNonfreeAuditPhotos — it resolves several rows
 * that command had left pending a usable source or a rights determination:
 *
 *   - Andrés Figueroa Cordero  — AP wire portrait (Wikimedia, public domain)
 *   - Muhammad Abdul Aziz       — 1965 AP press photo (Wikimedia, public domain)
 *   - Francisco "Cisco" Torres  — SF8 acquittal photo (revolutionaryfrontlines)
 *   - Khalil Islam              — 1965 police-station news photo (Getty copy)
 *   - Leonard W. Boehner        — 1918 BOP inmate mugshot (public domain; Alamy copy)
 *
 * Provenance and licensing for each are recorded in CREDITS-nonfree.md.
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run (e.g. after the
 * amnesty/pardon import creates the Boehner record).
 */
final class AttachRequestedPrisonerPhotos extends Command
{
    protected $signature = 'prisoners:attach-requested-photos';

    protected $description = 'Attach a batch of supplied portraits to prisoners with no photo (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'andres-figueroa-cordero.jpg',
         'slugs' => ['andres-figueroa-cordero', 'andres-figueroa'],
         'names' => ['Andrés Figueroa Cordero', 'Andres Figueroa Cordero', 'Andrés Figueroa', 'Andres Figueroa']],
        ['file' => 'muhammad-abdul-aziz.jpg',
         'slugs' => ['muhammad-abdul-aziz', 'norman-3x-butler', 'norman-butler'],
         'names' => ['Muhammad Abdul Aziz', 'Norman 3X Butler', 'Norman Butler']],
        ['file' => 'francisco-torres.jpg',
         'slugs' => ['francisco-torres', 'francisco-cisco-torres', 'cisco-torres'],
         'names' => ['Francisco Torres', 'Francisco "Cisco" Torres', 'Francisco Cisco Torres', 'Cisco Torres']],
        ['file' => 'khalil-islam.jpg',
         'slugs' => ['khalil-islam', 'thomas-15x-johnson', 'thomas-johnson'],
         'names' => ['Khalil Islam', 'Thomas 15X Johnson', 'Thomas 15x Johnson']],
        ['file' => 'leonard-boehner.jpg',
         'slugs' => ['leonard-w-boehner', 'l-w-boehner', 'leonard-boehner'],
         'names' => ['Leonard W. Boehner', 'L. W. Boehner', 'Leonard Boehner']],
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        foreach (self::ENTRIES as $e) {
            $src = database_path("data/photos/nonfree/{$e['file']}");
            if (! is_file($src)) {
                $this->warn("Source image not found: {$src}");

                continue;
            }

            $prisoner = $this->resolve($e['slugs'], $e['names']);
            if (! $prisoner) {
                $missing[] = $e['names'][0];

                continue;
            }

            if (! empty($prisoner->photo)) {
                $this->line("{$prisoner->name} already has a photo — leaving alone.");
                $skipped++;

                continue;
            }

            $relative = 'prisoners/'.$e['file'];
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
            $this->warn('Not found ('.count($missing).'): '.implode(', ', $missing)
                .' — pass me the exact site name/slug and I will map it.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  string[]  $slugs
     * @param  string[]  $names
     */
    private function resolve(array $slugs, array $names): ?Prisoner
    {
        foreach ($slugs as $slug) {
            $p = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if ($p) {
                return $p;
            }
        }
        foreach ($names as $name) {
            $p = Prisoner::withUnderReview()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            if ($p) {
                return $p;
            }
        }

        return null;
    }
}
