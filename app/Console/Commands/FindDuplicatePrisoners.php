<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class FindDuplicatePrisoners extends Command
{
    protected $signature = 'prisoners:find-duplicates';
    protected $description = 'Detect probable duplicate prisoner records by comparing normalized names and slugs.';

    public function handle(): int
    {
        $all = Prisoner::query()
            ->get(['id', 'name', 'slug', 'sort_order', 'birthdate'])
            ->all();

        $this->info('Total prisoners: '.count($all));

        // Bucket each prisoner by a normalized name key.
        $buckets = [];
        foreach ($all as $p) {
            $key = $this->normalize($p->name);
            $buckets[$key][] = $p;
        }

        $exactDupes = array_filter($buckets, fn ($g) => count($g) > 1);

        // Also check for slug-based dupes (e.g. "keith-keith-mchenry" → "keith-mchenry")
        $slugBuckets = [];
        foreach ($all as $p) {
            $base = $this->slugBase($p->slug);
            $slugBuckets[$base][] = $p;
        }
        $slugDupes = array_filter($slugBuckets, fn ($g) => count($g) > 1);

        // Also check for very-similar-but-not-identical names (Levenshtein <=2)
        $similar = [];
        $seen = [];
        $names = array_map(fn ($p) => $p, $all);
        for ($i = 0; $i < count($names); $i++) {
            for ($j = $i + 1; $j < count($names); $j++) {
                $a = $this->normalize($names[$i]->name);
                $b = $this->normalize($names[$j]->name);
                if ($a === $b) continue; // already caught
                if (abs(strlen($a) - strlen($b)) > 3) continue;
                $dist = levenshtein($a, $b);
                if ($dist > 0 && $dist <= 2 && strlen($a) > 6) {
                    $key = min($names[$i]->id, $names[$j]->id).'-'.max($names[$i]->id, $names[$j]->id);
                    if (isset($seen[$key])) continue;
                    $seen[$key] = true;
                    $similar[] = [$names[$i], $names[$j], $dist];
                }
            }
        }

        $this->line('');
        $this->info('=== EXACT name matches (after normalization) ===');
        foreach ($exactDupes as $key => $group) {
            $this->line('');
            $this->warn("  {$key}");
            foreach ($group as $p) {
                $this->line("    [{$p->id}] {$p->name}  (slug: {$p->slug}, sort: {$p->sort_order}, birth: ".($p->birthdate?->toDateString() ?? '—').')');
            }
        }

        $this->line('');
        $this->info('=== Slug-base matches (e.g. "keith-mchenry" vs "keith-keith-mchenry") ===');
        foreach ($slugDupes as $base => $group) {
            // Filter out cases already covered above
            $names = array_unique(array_map(fn ($p) => $this->normalize($p->name), $group));
            if (count($names) === 1) continue; // already in exact-name bucket
            $this->line('');
            $this->warn("  base: {$base}");
            foreach ($group as $p) {
                $this->line("    [{$p->id}] {$p->name}  (slug: {$p->slug}, sort: {$p->sort_order}, birth: ".($p->birthdate?->toDateString() ?? '—').')');
            }
        }

        $this->line('');
        $this->info('=== Near-name matches (Levenshtein 1-2) ===');
        foreach ($similar as [$a, $b, $dist]) {
            $this->line("  ({$dist}) [{$a->id}] {$a->name} -- vs --  [{$b->id}] {$b->name}");
        }

        $this->line('');
        $this->info('Summary:');
        $this->line('  exact name duplicates:   '.count($exactDupes).' groups');
        $this->line('  slug-base duplicates:    '.count($slugDupes).' groups');
        $this->line('  near-name (edit dist 1-2): '.count($similar).' pairs');

        return self::SUCCESS;
    }

    private function normalize(string $name): string
    {
        // strip accents, lowercase, collapse whitespace, strip parenthetical aliases
        $n = $name;
        $n = preg_replace('/\(.*?\)/', '', $n);
        if (function_exists('iconv')) {
            $tx = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $n);
            if ($tx !== false) $n = $tx;
        }
        $n = preg_replace('/[^a-zA-Z\s]/', '', $n);
        $n = preg_replace('/\s+/', ' ', $n);
        return strtolower(trim($n));
    }

    private function slugBase(string $slug): string
    {
        // remove trailing -2, -3 etc.; collapse repeated tokens like "keith-keith"
        $slug = preg_replace('/-\d+$/', '', $slug);
        $tokens = explode('-', $slug);
        $deduped = [];
        $prev = null;
        foreach ($tokens as $t) {
            if ($t !== $prev) $deduped[] = $t;
            $prev = $t;
        }
        return implode('-', $deduped);
    }
}
