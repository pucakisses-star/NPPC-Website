<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits for interwar-era U.S. labor, Communist, and civil-rights
 * political prisoners (1919 Red Scare through the 1940s Smith Act era), plus
 * Marcus Garvey and Virgin Islands editor Rothschild Francis. All but two are
 * public-domain early-20th-century images; the Rothschild Francis portrait is
 * CC BY-SA 4.0 and the Milka Sablich crop is a low-resolution fair-use image
 * (see CREDITS-nonfree.md).
 *
 * Each record is matched by slug (several candidates), then by exact name /
 * alias. The photo is set ONLY when the prisoner currently has none — existing
 * photos are never overwritten. Idempotent and safe to re-run.
 */
final class AttachInterwarLeftPhotos extends Command
{
    protected $signature = 'prisoners:attach-interwar-left-photos';

    protected $description = 'Attach interwar-era labor/Communist/civil-rights prisoner portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'william-bross-lloyd.jpg',
         'slugs' => ['william-bross-lloyd'],
         'names' => ['William Bross Lloyd']],
        ['file' => 'ella-reeve-bloor.jpg',
         'slugs' => ['ella-reeve-bloor', 'mother-bloor', 'ella-bloor'],
         'names' => ['Ella Reeve Bloor', 'Ella Reeve "Mother" Bloor', 'Mother Bloor', 'Ella Bloor']],
        ['file' => 'clarence-hathaway.jpg',
         'slugs' => ['clarence-hathaway', 'c-a-hathaway'],
         'names' => ['Clarence Hathaway', 'Clarence A. Hathaway']],
        ['file' => 'max-shachtman.jpg',
         'slugs' => ['max-shachtman'],
         'names' => ['Max Shachtman']],
        ['file' => 'robert-minor.jpg',
         'slugs' => ['robert-minor'],
         'names' => ['Robert Minor']],
        ['file' => 'william-dunne.jpg',
         'slugs' => ['william-f-dunne', 'william-dunne', 'bill-dunne'],
         'names' => ['William F. Dunne', 'William F. "Bill" Dunne', 'Bill Dunne', 'William Dunne']],
        ['file' => 'pablo-manlapit.jpg',
         'slugs' => ['pablo-manlapit'],
         'names' => ['Pablo Manlapit']],
        ['file' => 'john-brophy.jpg',
         'slugs' => ['john-brophy'],
         'names' => ['John Brophy']],
        ['file' => 'harry-eisman.jpg',
         'slugs' => ['harry-eisman'],
         'names' => ['Harry Eisman']],
        ['file' => 'hugo-oehler.jpg',
         'slugs' => ['hugo-oehler'],
         'names' => ['Hugo Oehler']],
        ['file' => 'marcus-garvey.jpg',
         'slugs' => ['marcus-garvey'],
         'names' => ['Marcus Garvey']],
        ['file' => 'powers-hapgood.jpg',
         'slugs' => ['powers-hapgood'],
         'names' => ['Powers Hapgood']],
        ['file' => 'karl-yoneda.jpg',
         'slugs' => ['karl-goso-yoneda', 'karl-yoneda'],
         'names' => ['Karl Goso Yoneda', 'Karl Yoneda']],
        ['file' => 'rothschild-francis.jpg',
         'slugs' => ['rothschild-francis', 'rothschild-polly-francis', 'polly-francis'],
         'names' => ['Rothschild Francis', 'Rothschild "Polly" Francis', 'Rothschild Polly Francis']],
        ['file' => 'milka-sablich.jpg',
         'slugs' => ['amelia-sablich', 'milka-sablich', 'amelia-milka-sablich'],
         'names' => ['Amelia Sablich', 'Amelia "Milka" Sablich', 'Milka Sablich']],
        ['file' => 'paul-crouch.jpg',
         'slugs' => ['paul-crouch'],
         'names' => ['Paul Crouch']],
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
