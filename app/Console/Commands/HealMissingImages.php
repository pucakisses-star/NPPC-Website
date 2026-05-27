<?php

namespace App\Console\Commands;

use App\Support\MediaFields;
use Illuminate\Console\Command;

/**
 * Audit every model+field that stores a file path. Reports rows whose
 * stored path points at a file that no longer exists on disk — usually
 * the wreckage of a prior cleanup pass (e.g. an old DedupeFiles run that
 * didn't update DB pointers when removing duplicate copies).
 *
 *   php artisan archive:heal-missing-images           # dry-run report
 *   php artisan archive:heal-missing-images --null    # set missing paths to NULL
 *
 * --null is the safe healing action: nulling the field hides the broken
 * reference from the public site (sections that filter by whereNotNull
 * stop rendering it). Re-upload via /admin restores it.
 */
final class HealMissingImages extends Command {
    protected $signature = 'archive:heal-missing-images
        {--null : Set any field whose path points at a missing file to NULL}';
    protected $description = 'Find DB image/file fields pointing at missing files; optionally null them';

    public function handle(): int {
        $totalRows = 0;
        $totalMissing = 0;
        /** @var array<string, array<int, array{id: mixed, path: string}>> */
        $byField = [];

        foreach (MediaFields::all() as [$model, $field]) {
            $label = class_basename($model).'.'.$field;
            $rows = $model::query()->withoutGlobalScopes()
                ->whereNotNull($field)->where($field, '!=', '')
                ->get(['id', $field]);

            $totalRows += $rows->count();
            $missing = [];
            foreach ($rows as $r) {
                $path = (string) $r->{$field};
                if (MediaFields::resolveAbsPath($path) === null) {
                    $missing[] = ['id' => $r->getKey(), 'path' => $path];
                }
            }

            if (! empty($missing)) {
                $byField[$label] = ['model' => $model, 'field' => $field, 'rows' => $missing];
                $totalMissing += count($missing);
                $this->warn(sprintf('%s — %d missing', $label, count($missing)));
                foreach (array_slice($missing, 0, 10) as $m) {
                    $this->line(sprintf('  %s  →  %s', $m['id'], $m['path']));
                }
                if (count($missing) > 10) {
                    $this->line('  ... +'.(count($missing) - 10).' more');
                }
            }
        }

        $this->newLine();
        $this->info(sprintf('Scanned %d row(s) across %d model field(s); %d missing.',
            $totalRows, count(MediaFields::all()), $totalMissing));

        if (! $this->option('null')) {
            if ($totalMissing > 0) {
                $this->info('(dry-run; re-run with --null to clear the broken paths)');
            }
            return self::SUCCESS;
        }

        $cleared = 0;
        foreach ($byField as $entry) {
            $model = $entry['model'];
            $field = $entry['field'];
            $ids   = array_column($entry['rows'], 'id');
            $cleared += $model::query()->withoutGlobalScopes()
                ->whereIn('id', $ids)
                ->update([$field => null]);
        }
        $this->info(sprintf('Cleared %d field(s).', $cleared));
        return self::SUCCESS;
    }
}
