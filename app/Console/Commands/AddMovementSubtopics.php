<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds nested sub-topic pages under each of the eleven Movements topics —
 * key campaigns, cases, and prisoner traditions within each movement (e.g.
 * Black Liberation → the Garvey prosecution, the COINTELPRO frame-ups, the
 * Mumia case; Anti-Nuclear Resistance → the Golden Rule, Seabrook, the Nevada
 * Test Site) — giving the Movements section the same third-level nav as the
 * Organizations section.
 *
 * Content lives in database/data/movement-subtopics-a.json and -b.json as maps
 * of parent slug => { children: [{slug,title,sort_order,body}] }. Unlike the
 * org event commands, the movement parent's own body is left untouched (those
 * pages carry seeded/admin-authored intros). Create-or-update by slug, so it
 * is idempotent; movements whose parent topic is absent are skipped.
 */
final class AddMovementSubtopics extends Command
{
    protected $signature = 'topics:add-movement-subtopics';

    protected $description = 'Add nested sub-topic pages under each Movements topic';

    public function handle(): int
    {
        $files = ['movement-subtopics-a.json', 'movement-subtopics-b.json'];
        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $path = database_path('data/'.$file);
            if (! is_file($path)) {
                $this->error('Missing data file: '.$path);

                return self::FAILURE;
            }
            $manifest = json_decode((string) file_get_contents($path), true);
            if (! is_array($manifest)) {
                $this->error('Could not parse '.$path);

                return self::FAILURE;
            }

            foreach ($manifest as $parentSlug => $data) {
                $parent = Topic::where('slug', $parentSlug)->first();
                if (! $parent) {
                    $this->warn("Parent topic not found, skipping: {$parentSlug}");
                    $skipped++;

                    continue;
                }

                DB::transaction(function () use ($parent, $data, &$added, &$updated) {
                    $parent->published = true;
                    $parent->save();

                    foreach ($data['children'] as $c) {
                        $existing = Topic::where('slug', $c['slug'])->first();
                        $topic = $existing ?? new Topic(['slug' => $c['slug']]);
                        $topic->fill([
                            'title' => $c['title'],
                            'slug' => $c['slug'],
                            'parent_id' => $parent->id,
                            'body' => $c['body'],
                            'published' => true,
                            'sort_order' => $c['sort_order'],
                        ]);
                        $topic->save();

                        if ($existing) {
                            $updated++;
                            $this->line('  updated: '.$c['title']);
                        } else {
                            $added++;
                            $this->info('  added: '.$c['title']);
                        }
                    }
                });

                $this->info("{$parent->title}: ".count($data['children']).' sub-topic pages.');
            }
        }

        $this->info("\nDone. Movement sub-topics — added: {$added}, updated: {$updated}, movements skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
