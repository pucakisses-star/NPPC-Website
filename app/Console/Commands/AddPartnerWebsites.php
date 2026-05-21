<?php

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;

/**
 * Adds website URLs to two existing partners that were missing them:
 *   - Community Alliance For Peace And Justice
 *   - Free Jessica Reznicek
 *
 * Idempotent — matches by name.
 */
final class AddPartnerWebsites extends Command {
    protected $signature = 'archive:add-partner-websites';
    protected $description = 'Backfill website URLs for partners missing them';

    public function handle(): int {
        $updates = [
            'Community Alliance For Peace And Justice' => 'https://www.ca4pj.org/',
            'Free Jessica Reznicek' => 'https://supportjessicareznicek.com/',
        ];

        $changed = 0;
        $missing = 0;
        foreach ($updates as $name => $url) {
            // Match on trimmed name to tolerate stray trailing/leading whitespace
            // in the existing rows.
            $p = Partner::query()
                ->whereRaw('TRIM(name) = ?', [$name])
                ->first();
            if (! $p) {
                $this->warn("MISSING: {$name}");
                $missing++;
                continue;
            }

            $trimmedName = trim($p->name);
            if ($p->name !== $trimmedName) {
                $p->name = $trimmedName;
            }
            $p->url = $url;
            $p->save();
            $this->info("URL set: {$trimmedName} → {$url}");
            $changed++;
        }

        $this->info("Done — updated {$changed}, missing {$missing}.");
        return self::SUCCESS;
    }
}
