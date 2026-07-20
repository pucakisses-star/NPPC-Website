<?php

namespace App\Console\Commands;

use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Applies the narrative-charges audit: many cases stored a description of
 * what happened to the person ("Arrested and persecuted from 1928 as a
 * child Young Pioneer...") in the charges field instead of actual charges.
 * Every distinct suspect value was reviewed; the resulting decisions live
 * in database/data/charges-fixes.json as a map of exact old text to either
 * an amended charge-style value or null (remove).
 *
 * Matching is by charges text normalized the same way the public API
 * renders it (split on newlines, each line trimmed), since the fix map was
 * audited from an API snapshot. Idempotent: once a value is rewritten it
 * no longer matches its old key.
 */
final class FixNarrativeCharges extends Command {
    protected $signature = 'prisoners:fix-charges {--dry : Report what would change without saving}';
    protected $description = 'Rewrite or remove narrative charges-field values per the audited fix map';

    public function handle(): int {
        $path = database_path('data/charges-fixes.json');
        if (! is_file($path)) {
            $this->error("Missing {$path}");

            return self::FAILURE;
        }
        $fixes = json_decode(file_get_contents($path), true);
        if (! is_array($fixes)) {
            $this->error('charges-fixes.json is not valid JSON');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry');
        $amended = 0;
        $removed = 0;

        PrisonerCase::query()
            ->whereNotNull('charges')
            ->chunkById(500, function ($cases) use ($fixes, $dry, &$amended, &$removed) {
                foreach ($cases as $case) {
                    $key = implode("\n", array_map('trim', explode("\n", str_replace("\r\n", "\n", (string) $case->charges))));
                    if (trim($key) === '' || ! array_key_exists($key, $fixes)) {
                        continue;
                    }
                    $new = $fixes[$key];
                    if ($new === null) {
                        $case->charges = null;
                        $removed++;
                    } else {
                        $case->charges = $new;
                        $amended++;
                    }
                    if (! $dry) {
                        $case->save();
                    }
                }
            });

        if (! $dry) {
            \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
        }

        $this->info(($dry ? '[dry run] Would rewrite' : 'Rewrote')
            ." {$amended} case charge value(s) and clear {$removed}.");

        return self::SUCCESS;
    }
}
