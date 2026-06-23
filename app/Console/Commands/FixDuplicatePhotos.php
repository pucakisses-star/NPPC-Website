<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Repairs photos that the fuzzy name-matcher copied onto multiple unrelated
 * records (one image shared by several prisoners). It recomputes the duplicate
 * groups live from the files on disk (md5), so it always acts on current data,
 * and for each group:
 *
 *   - exactly ONE "bare-slug" file (e.g. hugo-pinell.jpg, no -<uuid> suffix) —
 *     treated as the original upload: keep it, NULL the column + DELETE the file
 *     on every other record in the group.
 *   - NO bare-slug file (original is unidentifiable) — left untouched and
 *     reported, unless --clear-unowned is passed (then all are cleared).
 *   - MULTIPLE bare-slug files (ambiguous) — left untouched and reported.
 *
 * The "NO IMAGE AVAILABLE" placeholder md5 is skipped (handled by
 * prisoners:clear-placeholder-photos). Idempotent; --dry-run previews.
 */
final class FixDuplicatePhotos extends Command
{
    protected $signature = 'prisoners:fix-duplicate-photos {--dry-run : Preview without changing anything} {--clear-unowned : Also clear groups with no identifiable original}';

    protected $description = 'Keep the original photo per shared-image group and clear the fuzzy-matched copies';

    private const PLACEHOLDER_MD5 = ['56f7ee32d16ac711c6768265e1538357'];

    private const UUID_SUFFIX = '/-[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.[a-z0-9]+$/i';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $clearUnowned = (bool) $this->option('clear-unowned');
        $disk = Storage::disk('public');

        // 1. Group every existing prisoner photo by file content (md5).
        $byHash = [];
        foreach (Prisoner::withUnderReview()->whereNotNull('photo')->where('photo', '!=', '')->cursor() as $prisoner) {
            $path = ltrim(preg_replace('#^.*/storage/#', '', (string) $prisoner->photo), '/');
            if (! $disk->exists($path)) {
                continue;
            }
            try {
                $hash = md5($disk->get($path));
            } catch (\Throwable $e) {
                continue;
            }
            $byHash[$hash][] = ['prisoner' => $prisoner, 'path' => $path];
        }

        $toClear = [];          // [['prisoner'=>, 'path'=>], ...]
        $groupsFixed = 0;
        $noOwner = [];
        $ambiguous = [];

        // 2. Decide per shared-image group.
        foreach ($byHash as $hash => $members) {
            if (count($members) < 2 || in_array($hash, self::PLACEHOLDER_MD5, true)) {
                continue;
            }

            $bare = array_values(array_filter($members, fn ($m) => ! preg_match(self::UUID_SUFFIX, basename($m['path']))));

            if (count($bare) === 1) {
                $keepPath = $bare[0]['path'];
                $this->line("KEEP: {$bare[0]['prisoner']->name}  ({$keepPath})");
                foreach ($members as $m) {
                    if ($m['path'] !== $keepPath) {
                        $toClear[] = $m;
                    }
                }
                $groupsFixed++;
            } elseif (count($bare) === 0) {
                if ($clearUnowned) {
                    foreach ($members as $m) {
                        $toClear[] = $m;
                    }
                    $groupsFixed++;
                } else {
                    $noOwner[] = array_map(fn ($m) => $m['prisoner']->name, $members);
                }
            } else {
                $ambiguous[] = array_map(fn ($m) => $m['prisoner']->name, $members);
            }
        }

        // 3. Apply (or preview) the clears.
        $columnsCleared = 0;
        $filesDeleted = 0;
        foreach ($toClear as $m) {
            if ($dryRun) {
                $this->line("  would clear+delete: {$m['prisoner']->name}  ({$m['path']})");
            } else {
                $m['prisoner']->photo = null;
                $m['prisoner']->save();
                if ($disk->delete($m['path'])) {
                    $filesDeleted++;
                }
            }
            $columnsCleared++;
        }

        // 4. Report the groups left untouched.
        if ($noOwner) {
            $this->warn("\nNo identifiable original (left untouched; pass --clear-unowned to clear): ".count($noOwner).' groups');
            foreach ($noOwner as $names) {
                $this->line('  - '.implode('  |  ', $names));
            }
        }
        if ($ambiguous) {
            $this->warn("\nAmbiguous (multiple bare-slug originals; manual review): ".count($ambiguous).' groups');
            foreach ($ambiguous as $names) {
                $this->line('  - '.implode('  |  ', $names));
            }
        }

        $this->info("\nDone".($dryRun ? ' (dry run)' : '').'. '
            ."Groups fixed: {$groupsFixed} | records cleared: {$columnsCleared} | files deleted: {$filesDeleted}"
            .' | no-owner groups: '.count($noOwner).' | ambiguous: '.count($ambiguous));

        return self::SUCCESS;
    }
}
