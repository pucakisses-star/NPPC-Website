<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches portraits of nine Joint Anti-Fascist Refugee Committee board
 * members, individually cropped from the 1950 press photograph of the eleven
 * defendants at their Washington press conference before surrendering to
 * begin their contempt-of-HUAC sentences. The photograph's published caption
 * names both rows explicitly left-to-right (seated: Bradley, Leider, Barsky,
 * Stern; standing: Justiz, Miller, Chodorov, Magaña, Lustig, Auslander,
 * Fast), so every crop is caption-certified; Barsky and Fast already have
 * portraits. Colorized copy via ALBA's The Volunteer (photos/nonfree/,
 * CREDITS-nonfree.md).
 *
 * Also backfills George Marshall's birth and death dates (fill-if-empty).
 *
 * Fill-if-empty: the photo is set ONLY when the prisoner currently has none.
 * Idempotent and safe to re-run.
 */
final class AttachJafrcPhotos extends Command
{
    protected $signature = 'prisoners:attach-jafrc-photos';

    protected $description = 'Attach 1950 JAFRC press-conference portraits (fill-if-empty)';

    /** @var array<int,array{file:string,slugs:string[],names:string[]}> */
    private const ENTRIES = [
        ['file' => 'nonfree/lyman-r-bradley.jpg', 'slugs' => ['lyman-r-bradley'], 'names' => ['Lyman R. Bradley', 'Lyman Bradley']],
        ['file' => 'nonfree/ruth-leider.jpg', 'slugs' => ['ruth-leider'], 'names' => ['Ruth Leider']],
        ['file' => 'nonfree/charlotte-stern.jpg', 'slugs' => ['charlotte-stern'], 'names' => ['Charlotte Stern']],
        ['file' => 'nonfree/harry-m-justiz.jpg', 'slugs' => ['harry-m-justiz', 'harry-justiz'], 'names' => ['Harry M. Justiz', 'Harry Justiz']],
        ['file' => 'nonfree/louis-miller.jpg', 'slugs' => ['louis-miller'], 'names' => ['Louis Miller']],
        ['file' => 'nonfree/marjorie-chodorov.jpg', 'slugs' => ['marjorie-chodorov'], 'names' => ['Marjorie Chodorov']],
        ['file' => 'nonfree/manuel-magana.jpg', 'slugs' => ['manuel-magana'], 'names' => ['Manuel Magaña', 'Manuel Magana']],
        ['file' => 'nonfree/james-lustig.jpg', 'slugs' => ['james-lustig'], 'names' => ['James Lustig']],
        ['file' => 'nonfree/jacob-auslander.jpg', 'slugs' => ['jacob-auslander'], 'names' => ['Jacob Auslander']],
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('prisoners');

        $linked = 0;
        $skipped = 0;
        $missing = [];

        foreach (self::ENTRIES as $e) {
            $src = database_path("data/photos/{$e['file']}");
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

            $relative = 'prisoners/'.basename($e['file']);
            Storage::disk('public')->put($relative, (string) file_get_contents($src));
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info("Linked photo for {$prisoner->name}.");
            $linked++;
        }

        // George Marshall (NFCL) — exact dates found during the photo hunt.
        $gm = Prisoner::withUnderReview()->where('slug', 'george-marshall')->first();
        if ($gm) {
            $changed = false;
            if (empty($gm->birthdate)) {
                $gm->birthdate = '1904-02-11';
                $changed = true;
            }
            if (empty($gm->death_date)) {
                $gm->death_date = '2000-05-15';
                $changed = true;
            }
            if ($changed) {
                $gm->save();
                $this->info('Backfilled George Marshall birth/death dates.');
                $linked++;
            }
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
