<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-shot cleanup of the confirmed duplicate Topics surfaced by
 * `topics:find-duplicates`. Two jobs:
 *
 *   1. Merge three duplicate pairs that were introduced by two different
 *      seeders (topics:reorganize vs topics:seed). For each pair the survivor
 *      is kept and the duplicate is deleted; any children of the duplicate are
 *      re-pointed to the survivor first, and the survivor's body is only
 *      back-filled if it happens to be blank (so no content is lost).
 *
 *   2. Remove the "Repressive Tools" section entirely (root + all children;
 *      the parent_id foreign key cascades on delete). It is hidden from the
 *      explorer and overlaps the Eras COINTELPRO entry.
 *
 * Dry-run by default: prints what it WOULD do and writes nothing. Pass --force
 * to apply inside a transaction.
 */
class CleanupDuplicateTopics extends Command
{
    protected $signature = 'topics:cleanup-duplicates {--force : Actually apply the changes (otherwise dry-run)}';

    protected $description = 'Merge the 3 confirmed duplicate topic pairs and delete the Repressive Tools section. Dry-run unless --force.';

    /** survivor slug => duplicate slug to merge in and delete */
    private const PAIRS = [
        ['the-war-on-terror-2001',     'the-war-on-terror'],
        ['the-green-scare-2005-2010',  'the-green-scare'],
        ['anti-war-resistance',        'anti-war-activism'],
    ];

    /** root sections to delete outright (children cascade) */
    private const DELETE_SECTIONS = ['repressive-tools'];

    public function handle(): int
    {
        $apply = (bool) $this->option('force');
        $this->info($apply ? 'Applying topic cleanup…' : 'DRY RUN — no changes will be written (pass --force to apply).');
        $this->newLine();

        $plan = [];   // closures that perform the writes, collected then run in a txn

        // ---- 1. Merge the duplicate pairs ----
        $this->line('Duplicate pairs:');
        foreach (self::PAIRS as [$survivorSlug, $dupSlug]) {
            $survivor = Topic::where('slug', $survivorSlug)->first();
            $dup      = Topic::where('slug', $dupSlug)->first();

            if (! $survivor && ! $dup) {
                $this->line("  - skip: neither \"{$survivorSlug}\" nor \"{$dupSlug}\" found.");
                continue;
            }
            if (! $survivor) {
                $this->warn("  - skip: survivor \"{$survivorSlug}\" not found (duplicate \"{$dupSlug}\" left untouched).");
                continue;
            }
            if (! $dup) {
                $this->line("  - skip: duplicate \"{$dupSlug}\" already gone; survivor \"{$survivorSlug}\" kept.");
                continue;
            }

            $children = Topic::where('parent_id', $dup->id)->get();
            $backfill = blank($survivor->body) && filled($dup->body);

            $this->line("  - merge \"{$dup->title}\" (id {$dup->id}) -> \"{$survivor->title}\" (id {$survivor->id})");
            if ($children->isNotEmpty()) {
                $this->line("      re-point {$children->count()} child topic(s) to the survivor");
            }
            if ($backfill) {
                $this->line('      survivor body is blank — back-fill from the duplicate');
            }

            $plan[] = function () use ($survivor, $dup, $children, $backfill) {
                foreach ($children as $child) {
                    $child->update(['parent_id' => $survivor->id]);
                }
                if ($backfill) {
                    $survivor->update(['body' => $dup->body]);
                }
                $dup->delete();
            };
        }

        // ---- 2. Delete whole sections ----
        $this->newLine();
        $this->line('Sections to remove:');
        foreach (self::DELETE_SECTIONS as $slug) {
            $root = Topic::where('slug', $slug)->whereNull('parent_id')->first()
                ?? Topic::where('slug', $slug)->first();

            if (! $root) {
                $this->line("  - skip: \"{$slug}\" not found (already removed).");
                continue;
            }

            $descendants = $this->descendantCount($root);
            $this->line("  - delete \"{$root->title}\" (id {$root->id}) and {$descendants} descendant topic(s) [cascade]");

            $plan[] = function () use ($root) {
                $root->delete();   // FK parent_id ON DELETE CASCADE removes children
            };
        }

        $this->newLine();

        if (! $apply) {
            $this->warn('Dry run complete. Re-run with --force to apply the above.');
            return self::SUCCESS;
        }

        if (empty($plan)) {
            $this->info('Nothing to do.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($plan) {
            foreach ($plan as $step) {
                $step();
            }
        });

        $this->info('Done. Topic cleanup applied.');

        return self::SUCCESS;
    }

    /** Count all descendants of a topic by walking children (handles any depth). */
    private function descendantCount(Topic $root): int
    {
        $count = 0;
        $stack = Topic::where('parent_id', $root->id)->get()->all();
        while ($stack) {
            $node = array_pop($stack);
            $count++;
            foreach (Topic::where('parent_id', $node->id)->get() as $child) {
                $stack[] = $child;
            }
        }
        return $count;
    }
}
