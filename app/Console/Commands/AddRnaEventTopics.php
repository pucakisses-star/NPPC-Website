<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates a topic page for each major event in the history of the Republic of
 * New Afrika (1968–present) as a nested sub-topic of the Republic of New Afrika
 * topic in /topics. Each event gets an expanded multi-paragraph essay; the
 * content lives in database/data/rna-event-topics.json. Also resets the Republic
 * of New Afrika topic body to its overview. Create-or-update by slug, so it is
 * idempotent. No-op if the Republic of New Afrika topic is absent.
 */
final class AddRnaEventTopics extends Command
{
    protected $signature = 'topics:add-rna-events';

    protected $description = 'Add a nested topic page for each major Republic of New Afrika event under the RNA topic';

    public function handle(): int
    {
        $rna = Topic::where('slug', 'republic-of-new-afrika')->first();
        if (! $rna) {
            $this->warn('The "republic-of-new-afrika" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $path = database_path('data/rna-event-topics.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $events = json_decode((string) file_get_contents($path), true);
        if (! is_array($events)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $overview = '<p>The Republic of New Afrika, founded in Detroit in 1968, advocated an independent Black-majority nation in the Deep South, billions in reparations for slavery, and a plebiscite on Black citizenship, framing Black Americans as a captive nation entitled to self-determination. Its Provisional Government was a target of intense state surveillance and prosecution, from the 1969 New Bethel Incident to the 1971 FBI raid on its Jackson, Mississippi headquarters that imprisoned much of its leadership. The events below trace the RNA\'s founding, its Southern project, and the repression and prisoner tradition that followed.</p>';

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($rna, $events, $overview, &$added, &$updated) {
            $rna->body = $overview;
            $rna->published = true;
            $rna->save();

            foreach ($events as $e) {
                $existing = Topic::where('slug', $e['slug'])->first();
                $topic = $existing ?? new Topic(['slug' => $e['slug']]);
                $topic->fill([
                    'title' => $e['title'],
                    'slug' => $e['slug'],
                    'parent_id' => $rna->id,
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

        $this->info("\nDone. Republic of New Afrika event sub-topics — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
