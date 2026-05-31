<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Read-only detector for probable duplicate Topics — built because the explorer
 * has been seeded by more than one command (topics:reorganize and topics:seed),
 * each of which introduces movements/eras with overlapping but differently
 * worded titles (e.g. "Anti-War Activism" vs "Anti-War Resistance", or
 * "The Green Scare" vs "The Green Scare (2005-2010)"). Those produce different
 * slugs, so the seeders' own slug guards don't catch them.
 *
 * This command only reports; it never deletes or merges.
 */
class FindDuplicateTopics extends Command
{
    protected $signature = 'topics:find-duplicates {--section= : Limit to one root section by title, e.g. "Movements" or "Eras"}';

    protected $description = 'Detect probable duplicate Topics (exact slug/title collisions plus near-duplicate titles within a section).';

    public function handle(): int
    {
        $topics = Topic::query()->get(['id', 'title', 'slug', 'parent_id', 'published', 'sort_order']);
        $byId   = $topics->keyBy('id');

        // Resolve each topic's root section title (walk up parent_id).
        $rootTitleOf = function (Topic $t) use ($byId): string {
            $cur = $t;
            $guard = 0;
            while ($cur->parent_id && isset($byId[$cur->parent_id]) && $guard++ < 20) {
                $cur = $byId[$cur->parent_id];
            }
            return $cur->title;
        };

        $sectionFilter = $this->option('section');
        if ($sectionFilter !== null) {
            $topics = $topics->filter(fn ($t) => strcasecmp($rootTitleOf($t), $sectionFilter) === 0)->values();
            $this->info("Scanning section: {$sectionFilter} ({$topics->count()} topics)");
        } else {
            $this->info('Scanning all topics: '.$topics->count());
        }
        $this->newLine();

        $found = 0;

        // ---- 1. Exact slug collisions (shouldn't happen given seeder guards) ----
        $slugGroups = $topics->groupBy('slug')->filter(fn ($g) => $g->count() > 1);
        if ($slugGroups->isNotEmpty()) {
            $this->error('=== EXACT slug collisions ===');
            foreach ($slugGroups as $slug => $group) {
                $this->line("  slug \"{$slug}\":");
                foreach ($group as $t) {
                    $this->line('    - '.$this->describe($t, $rootTitleOf($t)));
                }
                $found++;
            }
            $this->newLine();
        }

        // ---- 2. Exact match after normalizing away parentheticals/articles ----
        // "The Green Scare" and "The Green Scare (2005-2010)" both reduce to
        // "green scare" — a strong duplicate signal even with distinct slugs.
        $normGroups = $topics
            ->groupBy(fn ($t) => $this->normalizeTitle($t->title))
            ->filter(fn ($g) => $g->pluck('slug')->unique()->count() > 1);

        if ($normGroups->isNotEmpty()) {
            $this->error('=== Same title ignoring dates/articles (likely duplicates) ===');
            foreach ($normGroups as $norm => $group) {
                $this->line("  \"{$norm}\":");
                foreach ($group as $t) {
                    $this->line('    - '.$this->describe($t, $rootTitleOf($t)));
                }
                $found++;
            }
            $this->newLine();
        }

        // ---- 3. Near-duplicate titles within the same section (token overlap) ----
        // Catches "Anti-War Activism" vs "Anti-War Resistance" (share "anti war").
        $seen = [];
        $near = [];
        $list = $topics->values()->all();
        $n = count($list);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $list[$i];
                $b = $list[$j];
                if ($a->slug === $b->slug) {
                    continue; // exact, already reported
                }
                $na = $this->normalizeTitle($a->title);
                $nb = $this->normalizeTitle($b->title);
                if ($na === $nb) {
                    continue; // caught in pass 2
                }
                // Only compare topics in the same root section.
                if ($rootTitleOf($a) !== $rootTitleOf($b)) {
                    continue;
                }
                $sim = $this->jaccard($na, $nb);
                if ($sim >= 0.5) {
                    $key = $a->id < $b->id ? "{$a->id}-{$b->id}" : "{$b->id}-{$a->id}";
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $near[] = [$a, $b, $sim, $rootTitleOf($a)];
                }
            }
        }
        if ($near) {
            // Strongest matches first.
            usort($near, fn ($x, $y) => $y[2] <=> $x[2]);
            $this->warn('=== Near-duplicate titles in the same section ===');
            foreach ($near as [$a, $b, $sim, $root]) {
                $pct = round($sim * 100);
                $this->line("  [{$root}] {$pct}% token overlap:");
                $this->line('    - '.$this->describe($a, $root));
                $this->line('    - '.$this->describe($b, $root));
                $found++;
            }
            $this->newLine();
        }

        if ($found === 0) {
            $this->info('No duplicate or near-duplicate topics found.');
        } else {
            $this->info("Reported {$found} potential duplicate group(s). This is detection only — nothing was changed.");
        }

        return self::SUCCESS;
    }

    private function describe(Topic $t, string $root): string
    {
        $flags = [];
        if (! $t->published) {
            $flags[] = 'UNPUBLISHED';
        }
        $suffix = $flags ? ' ['.implode(', ', $flags).']' : '';

        return "\"{$t->title}\" (slug: {$t->slug}, section: {$root}, id: {$t->id}){$suffix}";
    }

    /**
     * Reduce a title to its comparable core: drop any parenthetical
     * (date ranges, qualifiers), drop a leading article, lowercase, and
     * collapse punctuation to single spaces.
     */
    private function normalizeTitle(string $title): string
    {
        $t = preg_replace('/\([^)]*\)/u', ' ', $title);      // remove "(2005-2010)" etc.
        $t = Str::lower($t);
        $t = preg_replace('/^\s*(the|a|an)\s+/u', '', $t);    // drop leading article
        $t = preg_replace('/[^a-z0-9]+/u', ' ', $t);          // punctuation -> space
        return trim(preg_replace('/\s+/', ' ', $t));
    }

    /** Jaccard similarity over the word tokens of two normalized titles. */
    private function jaccard(string $a, string $b): float
    {
        $sa = array_filter(array_unique(explode(' ', $a)));
        $sb = array_filter(array_unique(explode(' ', $b)));
        if (! $sa || ! $sb) {
            return 0.0;
        }
        $inter = count(array_intersect($sa, $sb));
        $union = count(array_unique(array_merge($sa, $sb)));
        return $union ? $inter / $union : 0.0;
    }
}
