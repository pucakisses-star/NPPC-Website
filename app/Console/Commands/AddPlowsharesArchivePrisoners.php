<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Add U.S. Plowshares (anti-nuclear disarmament) political prisoners drawn
 * from Art Laffin's Plowshares chronology 1980-2018
 * (https://ickevald.net/plowshares/plowshares-chronology-1980-2018).
 *
 * U.S. participants ONLY — foreign nationals (Herngren, Koning, O'Reilly,
 * Cole, the UK/German/Swedish/Dutch participants, etc.) were excluded.
 *
 * Behaviour: creates each prisoner via the canonical prisoner:add pipeline,
 * which refuses to create a duplicate by exact name (so anyone already in the
 * database is skipped). Affiliation is set to "Plowshares Movement". No data
 * changes until this is run on the server.
 *
 * CAVEATS (see also the per-entry descriptions): the chronology carries full
 * participant lists and sentences only through 1993. Entries for 1994-2018
 * actions, and some enrichment of famous figures, are supplemented from
 * widely-documented public record and labeled as such in each description;
 * a number of later sentences are approximate. Verify specifics before
 * relying on them.
 */
final class AddPlowsharesArchivePrisoners extends Command {
    protected $signature = 'prisoners:add-plowshares-archive';
    protected $description = 'Add U.S. Plowshares anti-nuclear political prisoners from the 1980-2018 chronology';

    public function handle(): int {
        $path = __DIR__.'/data/plowshares_us_prisoners.json';
        if (! is_file($path)) {
            $this->error("Data file not found: {$path}");
            return self::FAILURE;
        }
        $entries = json_decode(file_get_contents($path), true);
        if (! is_array($entries)) {
            $this->error('Could not parse '.$path);
            return self::FAILURE;
        }

        $created = 0; $existing = 0;
        foreach ($entries as $e) {
            $name = $e['name'] ?? null;
            if (! $name) {
                continue;
            }
            if (Prisoner::where('name', $name)->exists()) {
                $this->warn("Exists, skipping: {$name}");
                $existing++;
                continue;
            }
            Artisan::call('prisoner:add', [
                'json' => json_encode($e, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $out = trim(Artisan::output());
            $this->line($out !== '' ? $out : "Added {$name}");
            $created++;
        }

        $this->info("\nDone. Created {$created}, already-present {$existing}, of ".count($entries)." entries.");
        return self::SUCCESS;
    }
}
