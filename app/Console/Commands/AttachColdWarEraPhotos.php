<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for mid-century radical, civil-rights, and pacifist
 * political prisoners (1940s-50s Red Scare and beyond). Most images are public
 * domain; Paul Sweezy is CC BY-SA 4.0, Billy Frank Jr. is CC BY 2.0, and the
 * Joffre Stewart / Thomas Banyacya / Joseph Gelders images are low-resolution
 * fair use (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachColdWarEraPhotos extends Command
{
    protected $signature = 'prisoners:attach-cold-war-era-photos';

    protected $description = 'Attach mid-century radical/civil-rights/pacifist prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'dorothy-healey.jpg',
         'slugs' => ['dorothy-ray-healey', 'dorothy-healey'],
         'names' => ['Dorothy Ray Healey', 'Dorothy Healey']],
        ['file' => 'dirk-struik.jpg',
         'slugs' => ['dirk-jan-struik', 'dirk-struik'],
         'names' => ['Dirk Jan Struik', 'Dirk Struik']],
        ['file' => 'donald-ogden-stewart.jpg',
         'slugs' => ['donald-ogden-stewart'],
         'names' => ['Donald Ogden Stewart']],
        ['file' => 'ella-winter.jpg',
         'slugs' => ['ella-winter'],
         'names' => ['Ella Winter']],
        ['file' => 'paul-sweezy.jpg',
         'slugs' => ['paul-m-sweezy', 'paul-sweezy'],
         'names' => ['Paul M. Sweezy', 'Paul Sweezy', 'Paul Marlor Sweezy']],
        ['file' => 'billy-frank-jr.jpg',
         'slugs' => ['billy-frank-jr', 'billy-frank'],
         'names' => ['Billy Frank Jr.', 'Billy Frank Jr', 'Billy Frank']],
        ['file' => 'ethel-rosenberg.jpg',
         'slugs' => ['ethel-rosenberg'],
         'names' => ['Ethel Rosenberg']],
        ['file' => 'joffre-stewart.jpg',
         'slugs' => ['joffre-stewart'],
         'names' => ['Joffre Stewart']],
        ['file' => 'thomas-banyacya.jpg',
         'slugs' => ['thomas-banyacya'],
         'names' => ['Thomas Banyacya']],
        ['file' => 'joseph-gelders.jpg',
         'slugs' => ['joseph-gelders'],
         'names' => ['Joseph Gelders']],
        ['file' => 'lolita-lebron.jpg',
         'slugs' => ['lolita-lebron'],
         'names' => ['Lolita Lebrón', 'Lolita Lebron']],
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
