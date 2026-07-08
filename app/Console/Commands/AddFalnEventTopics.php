<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates a topic page for each major event in the history of the FALN (Fuerzas
 * Armadas de Liberación Nacional, 1974–2010s) as a nested sub-topic of the FALN
 * topic in /topics. Each event gets an expanded multi-paragraph essay; the
 * content lives in database/data/faln-event-topics.json. Also resets the FALN
 * topic body to its overview. Create-or-update by slug, so it is idempotent.
 * No-op if the FALN topic is absent.
 */
final class AddFalnEventTopics extends Command
{
    protected $signature = 'topics:add-faln-events';

    protected $description = 'Add a nested topic page for each major FALN event under the FALN topic';

    public function handle(): int
    {
        $faln = Topic::where('slug', 'faln')->first();
        if (! $faln) {
            $this->warn('The "faln" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $path = database_path('data/faln-event-topics.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $events = json_decode((string) file_get_contents($path), true);
        if (! is_array($events)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $overview = '<p>The Fuerzas Armadas de Liberación Nacional (FALN) was a clandestine organization that fought for Puerto Rican independence through a bombing campaign on the U.S. mainland between 1974 and 1983, concentrated in New York and Chicago. Rooted in the long tradition of the Puerto Rican independence movement, its members were prosecuted under the rarely-used seditious-conspiracy statute and given sentences of decades, becoming central figures in the Puerto Rican political-prisoner movement — most famously Oscar López Rivera. The events below trace the group\'s campaign, the prosecutions that broke it, and the long fight for its imprisoned members\' release.</p>';

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($faln, $events, $overview, &$added, &$updated) {
            $faln->body = $overview;
            $faln->published = true;
            $faln->save();

            foreach ($events as $e) {
                $existing = Topic::where('slug', $e['slug'])->first();
                $topic = $existing ?? new Topic(['slug' => $e['slug']]);
                $topic->fill([
                    'title' => $e['title'],
                    'slug' => $e['slug'],
                    'parent_id' => $faln->id,
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

        $this->info("\nDone. FALN event sub-topics — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
