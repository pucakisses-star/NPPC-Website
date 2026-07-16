<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for a batch of recent movement defendants — Stop Cop City,
 * climate/pipeline, immigrant- and street-vendor-rights, crypto-liberty, and
 * anti-abortion-rescue cases. All are low-resolution non-free press / booking
 * images used under the site's fair-use rationale (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachProtestEraPhotos extends Command
{
    protected $signature = 'prisoners:attach-protest-era-photos';

    protected $description = 'Attach recent protest-era prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'edin-enamorado.jpg',
         'slugs' => ['edin-alex-enamorado', 'edin-enamorado'],
         'names' => ['Edin Alex Enamorado', 'Edin Enamorado']],
        ['file' => 'dan-baker.jpg',
         'slugs' => ['daniel-alan-baker', 'dan-baker', 'daniel-baker'],
         'names' => ['Daniel Alan Baker', 'Dan Baker', 'Daniel Baker']],
        ['file' => 'jasilyn-charger.jpg',
         'slugs' => ['jasilyn-charger'],
         'names' => ['Jasilyn Charger']],
        ['file' => 'aria-dimezzo.jpg',
         'slugs' => ['aria-dimezzo'],
         'names' => ['Aria DiMezzo']],
        ['file' => 'ian-freeman.jpg',
         'slugs' => ['ian-freeman', 'ian-bernard'],
         'names' => ['Ian Freeman', 'Ian Bernard']],
        ['file' => 'jazmine-jourdan.jpg',
         'slugs' => ['jazmine-jourdan'],
         'names' => ['Jazmine Jourdan']],
        ['file' => 'ayla-king.jpg',
         'slugs' => ['ayla-king'],
         'names' => ['Ayla King']],
        ['file' => 'mylene-vialard.jpg',
         'slugs' => ['mylene-vialard'],
         'names' => ['Mylène Vialard', 'Mylene Vialard']],
        ['file' => 'joan-andrews-bell.jpg',
         'slugs' => ['joan-andrews-bell', 'joan-andrews'],
         'names' => ['Joan Andrews Bell', 'Joan Andrews']],
        ['file' => 'thomas-jurgens.jpg',
         'slugs' => ['thomas-jurgens'],
         'names' => ['Thomas Jurgens']],
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
