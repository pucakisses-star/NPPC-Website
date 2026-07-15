<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Third batch of organization event sub-topics for /topics, completing the
 * Organizations section: Students for a Democratic Society, SNCC, the Brown
 * Berets, the Socialist Workers Party, the Nation of Islam, the African
 * People's Socialist Party, the Earth Liberation Front, and the Animal
 * Liberation Front.
 *
 * All content lives in database/data/org-event-topics-batch3.json as a map of
 * parent slug => { overview, events: [{slug,title,sort_order,body}] }. The
 * parent topic's body is reset to the overview and each event becomes a nested
 * child topic page, chronological by sort_order. Create-or-update by slug, so
 * it is idempotent; organizations whose parent topic is absent are skipped.
 */
final class AddOrgEventTopicsBatch3 extends Command
{
    protected $signature = 'topics:add-org-events-batch3';

    protected $description = 'Add nested event topic pages for SDS, SNCC, Brown Berets, SWP, NOI, APSP, ELF, ALF';

    public function handle(): int
    {
        $path = database_path('data/org-event-topics-batch3.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $manifest = json_decode((string) file_get_contents($path), true);
        if (! is_array($manifest)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($manifest as $parentSlug => $data) {
            $parent = Topic::where('slug', $parentSlug)->first();
            if (! $parent) {
                $this->warn("Parent topic not found, skipping: {$parentSlug}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($parent, $data, &$added, &$updated) {
                if (! empty($data['overview'])) {
                    $parent->body = $data['overview'];
                }
                $parent->published = true;
                $parent->save();

                foreach ($data['events'] as $e) {
                    $existing = Topic::where('slug', $e['slug'])->first();
                    $topic = $existing ?? new Topic(['slug' => $e['slug']]);
                    $topic->fill([
                        'title' => $e['title'],
                        'slug' => $e['slug'],
                        'parent_id' => $parent->id,
                        'body' => $e['body'],
                        'published' => true,
                        'sort_order' => $e['sort_order'],
                    ]);
                    $topic->save();

                    if ($existing) {
                        $updated++;
                        $this->line('  updated: '.$e['title']);
                    } else {
                        $added++;
                        $this->info('  added: '.$e['title']);
                    }
                }
            });

            $this->info("{$parent->title}: ".count($data['events']).' event pages.');
        }

        $this->info("\nDone. Org event sub-topics batch 3 — added: {$added}, updated: {$updated}, orgs skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
