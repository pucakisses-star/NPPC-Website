<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Walks every Article and reports rows with health issues:
 *
 *   - Empty/short body or title
 *   - Missing image (or image file doesn't exist on disk)
 *   - Broken inline <img src> URLs in body
 *   - Missing author or category foreign key
 *   - Missing or duplicate slug
 *   - Future published_at, or unpublished (null) published_at
 *   - Likely-truncated body (ends mid-sentence with no punctuation)
 *   - Citations JSON malformed
 *
 * Report-only. Pass --type=X to filter the report to one category.
 */
final class ScanArticleHealth extends Command {
    protected $signature = 'archive:scan-article-health {--type= : Filter to a specific issue type}';
    protected $description = 'Scan all Articles for health issues';

    public function handle(): int {
        $only = $this->option('type');

        $articles = Article::query()->orderBy('id')->get();
        $this->info('Scanning '.$articles->count().' articles.');

        $issues = [
            'empty_title'         => [],
            'short_title'         => [],
            'empty_body'          => [],
            'very_short_body'     => [],
            'missing_image_field' => [],
            'missing_image_file'  => [],
            'broken_inline_img'   => [],
            'no_author'           => [],
            'no_category'         => [],
            'no_slug'             => [],
            'duplicate_slug'      => [],
            'future_published'    => [],
            'unpublished'         => [],
            'truncated_body'      => [],
            'bad_citations'       => [],
        ];

        $slugCounts = [];
        foreach ($articles as $a) {
            if ($a->slug) {
                $slugCounts[$a->slug] = ($slugCounts[$a->slug] ?? 0) + 1;
            }
        }
        $duplicateSlugs = array_keys(array_filter($slugCounts, fn ($n) => $n > 1));

        foreach ($articles as $a) {
            $title = trim((string) $a->title);
            $body = trim((string) $a->body);

            if ($title === '') {
                $issues['empty_title'][] = $a;
            } elseif (strlen($title) < 6) {
                $issues['short_title'][] = $a;
            }

            if ($body === '') {
                $issues['empty_body'][] = $a;
            } elseif (mb_strlen(strip_tags($body)) < 80) {
                $issues['very_short_body'][] = $a;
            } else {
                // Truncated heuristic: body ends with no terminal punctuation
                $tail = mb_substr(rtrim(strip_tags($body)), -3);
                if (! preg_match('/[.!?")\]]\s*$/', $tail)) {
                    $issues['truncated_body'][] = $a;
                }
            }

            if (empty($a->image)) {
                $issues['missing_image_field'][] = $a;
            } else {
                if (! Storage::disk('public')->exists($a->image)) {
                    $issues['missing_image_file'][] = $a;
                }
            }

            if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', (string) $a->body, $m)) {
                foreach ($m[1] as $src) {
                    // Only flag plausible local paths that should resolve
                    if (! str_starts_with($src, 'http')) {
                        $path = ltrim($src, '/');
                        $local = public_path($path);
                        if (! is_file($local)) {
                            $issues['broken_inline_img'][] = $a;
                            break;
                        }
                    }
                }
            }

            if (empty($a->author_id)) {
                $issues['no_author'][] = $a;
            }
            if (empty($a->category_id)) {
                $issues['no_category'][] = $a;
            }
            if (empty($a->slug)) {
                $issues['no_slug'][] = $a;
            } elseif (in_array($a->slug, $duplicateSlugs, true)) {
                $issues['duplicate_slug'][] = $a;
            }

            if ($a->published_at === null) {
                $issues['unpublished'][] = $a;
            } elseif ($a->published_at->isFuture()) {
                $issues['future_published'][] = $a;
            }

            if (! empty($a->citations_json)) {
                if (! is_array($a->citations_json)) {
                    $issues['bad_citations'][] = $a;
                }
            }
        }

        foreach ($issues as $type => $rows) {
            if ($only && $only !== $type) {
                continue;
            }
            if (empty($rows)) {
                continue;
            }
            $this->line("\n— ".strtoupper(str_replace('_', ' ', $type))."  ({$type})  ".count($rows).' —');
            foreach ($rows as $a) {
                $this->line('  #'.$a->id.'  /news/'.$a->slug.'  — '.\Illuminate\Support\Str::limit($a->title, 80));
            }
        }

        $totals = array_map('count', $issues);
        $this->line("\n— SUMMARY —");
        arsort($totals);
        foreach ($totals as $k => $n) {
            if ($n > 0) {
                $this->info(str_pad((string) $n, 6, ' ', STR_PAD_LEFT).'  '.$k);
            }
        }

        return self::SUCCESS;
    }
}
