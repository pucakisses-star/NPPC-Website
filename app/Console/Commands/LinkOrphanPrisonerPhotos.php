<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * For each orphan file in storage/app/public/prisoners/, derive a slug
 * from its filename and try to attach it to a matching prisoner whose
 * photo is null/empty OR points at a missing file (broken link).
 *
 * Slug derivation:
 *   1. Strip extension
 *   2. Strip trailing collision suffix (-2, -3, ...)
 *   3. Strip trailing 8-4-4-4-12 UUID
 *   4. Strip trailing 26-char Crockford-base32 ULID
 *   5. Lowercase + Str::slug
 *
 * Match is deterministic: only links when exactly one prisoner has the
 * derived slug AND that prisoner has no working photo. Anything
 * ambiguous is reported instead of guessed at.
 */
final class LinkOrphanPrisonerPhotos extends Command {
    protected $signature = 'prisoners:link-orphan-photos {--dry-run : Preview without changing anything}';
    protected $description = 'Attach orphan photo files to prisoners whose photo is missing or broken, matched by slug';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');
        $disk = Storage::disk('public');

        $referenced = Prisoner::whereNotNull('photo')
            ->where('photo', '!=', '')
            ->pluck('photo')
            ->map(fn ($p) => ltrim($p, '/'))
            ->all();
        $referenced = array_flip($referenced);

        $linked = 0;
        $noMatch = 0;
        $alreadyHasPhoto = 0;
        $ambiguous = 0;

        foreach ($disk->files('prisoners') as $path) {
            if (isset($referenced[$path])) {
                continue;
            }

            $stem = pathinfo($path, PATHINFO_FILENAME);
            $stem = preg_replace('/-\d+$/', '', $stem);
            $stem = preg_replace('/-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', '', $stem);
            $stem = preg_replace('/^[0-9A-HJKMNP-TV-Z]{26}$/i', '', $stem);
            $slug = Str::slug($stem);
            if ($slug === '') {
                continue;
            }

            $candidates = Prisoner::where('slug', $slug)->get();
            if ($candidates->isEmpty()) {
                $this->line("no match: {$path}  (derived slug: {$slug})");
                $noMatch++;

                continue;
            }
            if ($candidates->count() > 1) {
                $this->warn("ambiguous: {$path} matches ".$candidates->count()." prisoners with slug \"{$slug}\" — skipping.");
                $ambiguous++;

                continue;
            }
            $prisoner = $candidates->first();

            $hasWorkingPhoto = ! empty($prisoner->photo) && $disk->exists(ltrim($prisoner->photo, '/'));
            if ($hasWorkingPhoto) {
                $alreadyHasPhoto++;

                continue;
            }

            $this->line(($dryRun ? '[dry-run] ' : '')."link {$prisoner->name}: photo={$path} (was: ".($prisoner->photo ?: '(empty)').')');
            if (! $dryRun) {
                $prisoner->photo = $path;
                $prisoner->save();
            }
            $linked++;
        }

        $this->info("\nDone. Linked={$linked} NoMatch={$noMatch} Ambiguous={$ambiguous} AlreadyHasPhoto={$alreadyHasPhoto}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
