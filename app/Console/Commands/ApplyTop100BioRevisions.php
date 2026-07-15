<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Applies the source-grounded biography revisions for the 100 most-incomplete
 * prisoner records (the "Top 100 Records Needing the Most Work" research pass).
 *
 * Data lives in database/data/top100_bio_revisions.json so the prose is
 * reviewable in the diff. Each revision always rewrites `description`; a few
 * carry a corrected `name` (only where a court/archival record establishes the
 * correction) and a few carry `under_review = true` to quarantine records with
 * no established imprisonment (a probable name error, or a scope audit) so they
 * drop out of public pages until confirmed.
 *
 * Idempotent: it matches each prisoner by slug (including already-hidden rows
 * via withUnderReview) and writes columns directly with the query builder, so
 * re-running it is a no-op and name corrections never regenerate the slug (the
 * public URL stays stable). Run with --dry to preview.
 */
final class ApplyTop100BioRevisions extends Command
{
    protected $signature = 'prisoners:apply-top100-revisions {--dry : Show what would change without writing}';

    protected $description = 'Apply researched biography revisions to the top 100 incomplete prisoner records';

    public function handle(): int
    {
        $path = database_path('data/top100_bio_revisions.json');

        if (! is_file($path)) {
            $this->error("Data file not found: {$path}");

            return self::FAILURE;
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data) || empty($data['revisions'])) {
            $this->error('Data file is empty or malformed.');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry');
        $updated = 0;
        $renamed = 0;
        $flagged = 0;
        $missing = [];

        foreach ($data['revisions'] as $rev) {
            $slug = $rev['slug'] ?? null;
            if (! $slug) {
                continue;
            }

            /** @var Prisoner|null $prisoner */
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();

            if (! $prisoner) {
                $missing[] = $slug;
                $this->warn("  Missing: {$slug} (no matching prisoner)");

                continue;
            }

            $changes = ['description' => $rev['description']];

            if (! empty($rev['name']) && $rev['name'] !== $prisoner->name) {
                $changes['name'] = $rev['name'];
                $renamed++;
                $this->line("  Rename: {$prisoner->name} -> {$rev['name']}");
            }

            if (array_key_exists('under_review', $rev)) {
                $target = (bool) $rev['under_review'];
                if ((bool) $prisoner->under_review !== $target) {
                    $changes['under_review'] = $target;
                    if ($target) {
                        $flagged++;
                        $this->line("  Quarantine (under review): {$prisoner->name}");
                    }
                }
            }

            if (! $dry) {
                // Query-builder update: writes columns without firing the
                // model's updating/saving hooks, so a name change does NOT
                // regenerate the slug and the public URL stays put.
                Prisoner::withUnderReview()->where('id', $prisoner->id)->update($changes);
            }

            $updated++;
            $this->line(($dry ? '  [dry] ' : '  Updated: ').$prisoner->name);
        }

        $verb = $dry ? 'Would update' : 'Updated';
        $this->info("\n{$verb} {$updated} record(s); {$renamed} renamed; {$flagged} newly quarantined.");
        if ($missing) {
            $this->warn(count($missing).' slug(s) had no match: '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }
}
