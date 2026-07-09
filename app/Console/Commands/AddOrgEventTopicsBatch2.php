<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Second batch of organization event sub-topics for /topics, extending the
 * pattern of topics:add-iww-events / add-bpp-events etc. to ten more
 * organizations: the American Indian Movement, MOVE, the Puerto Rican
 * Nationalist Party, the Communist Party USA, the Symbionese Liberation Army,
 * Los Macheteros, the May 19th Communist Organization, the United Freedom
 * Front, the Young Lords, and the George Jackson Brigade.
 *
 * Each organization's content lives in database/data/<org>-event-topics.json
 * as { "overview": html, "events": [{slug,title,sort_order,body}, ...] }. The
 * parent topic's body is reset to the overview and each event becomes a nested
 * child topic page, chronological by sort_order. Create-or-update by slug, so
 * it is idempotent; organizations whose parent topic is absent are skipped.
 */
final class AddOrgEventTopicsBatch2 extends Command
{
    protected $signature = 'topics:add-org-events-batch2';

    protected $description = 'Add nested event topic pages for AIM, MOVE, PRNP, CPUSA, SLA, Macheteros, M19CO, UFF, Young Lords, GJB';

    /** parent topic slug => data file (relative to database/data/). */
    private array $manifest = [
        'american-indian-movement' => 'aim-event-topics.json',
        'move' => 'move-event-topics.json',
        'puerto-rican-nationalist-party' => 'prnp-event-topics.json',
        'communist-party-usa' => 'cpusa-event-topics.json',
        'symbionese-liberation-army' => 'sla-event-topics.json',
        'los-macheteros' => 'macheteros-event-topics.json',
        'may-19th-communist-organization' => 'm19co-event-topics.json',
        'united-freedom-front' => 'uff-event-topics.json',
        'young-lords' => 'young-lords-event-topics.json',
        'george-jackson-brigade' => 'gjb-event-topics.json',
    ];

    public function handle(): int
    {
        $added = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($this->manifest as $parentSlug => $file) {
            $parent = Topic::where('slug', $parentSlug)->first();
            if (! $parent) {
                $this->warn("Parent topic not found, skipping: {$parentSlug}");
                $skipped++;

                continue;
            }

            $path = database_path('data/'.$file);
            if (! is_file($path)) {
                $this->error('Missing data file: '.$path);

                return self::FAILURE;
            }
            $data = json_decode((string) file_get_contents($path), true);
            if (! is_array($data) || empty($data['events'])) {
                $this->error('Could not parse '.$path);

                return self::FAILURE;
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

        $this->info("\nDone. Org event sub-topics batch 2 — added: {$added}, updated: {$updated}, orgs skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
