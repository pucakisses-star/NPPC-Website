<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Imports US political prisoners surfaced by scanning the 25 issues of
 * Prairie Fire Organizing Committee's "Breakthrough" magazine (1977-1995)
 * archived at archive.org against the existing database. Idempotent —
 * prisoner:add refuses to create when a name already exists.
 */
final class AddPfocBreakthroughPrisoners extends Command
{
    protected $signature = 'archive:add-pfoc-breakthrough-prisoners';
    protected $description = 'Add prisoners surfaced from the PFOC Breakthrough magazine archive.org fulltext sweep';

    public function handle(): int
    {
        $path = database_path('data/pfoc-breakthrough-prisoners.json');
        $payloads = json_decode(file_get_contents($path), true);
        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }
        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
