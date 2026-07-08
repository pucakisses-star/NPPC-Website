<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates a topic page for each major event in the history of the Black
 * Liberation Army (c. 1970–present) as a nested sub-topic of the Black
 * Liberation Army topic in /topics. Each event gets an expanded multi-paragraph
 * essay; the content lives in database/data/bla-event-topics.json. Also resets
 * the Black Liberation Army topic body to its overview. Create-or-update by
 * slug, so it is idempotent. No-op if the Black Liberation Army topic is absent.
 */
final class AddBlaEventTopics extends Command
{
    protected $signature = 'topics:add-bla-events';

    protected $description = 'Add a nested topic page for each major Black Liberation Army event under the BLA topic';

    public function handle(): int
    {
        $bla = Topic::where('slug', 'black-liberation-army')->first();
        if (! $bla) {
            $this->warn('The "black-liberation-army" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $path = database_path('data/bla-event-topics.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $events = json_decode((string) file_get_contents($path), true);
        if (! is_array($events)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $overview = '<p>The Black Liberation Army was a clandestine armed formation that emerged from the Black Panther Party in the early 1970s, after FBI repression and internal splits drove some members underground. Operating as a decentralized network of cells, it carried out a campaign against the police and became the target of one of the largest federal manhunts of the era. Its members were prosecuted in a series of high-profile cases, and a number remain among the longest-held political prisoners in the United States. The events below trace the BLA\'s emergence, its armed campaign, and the prosecutions and prisoner-support movements that followed.</p>';

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($bla, $events, $overview, &$added, &$updated) {
            $bla->body = $overview;
            $bla->published = true;
            $bla->save();

            foreach ($events as $e) {
                $existing = Topic::where('slug', $e['slug'])->first();
                $topic = $existing ?? new Topic(['slug' => $e['slug']]);
                $topic->fill([
                    'title' => $e['title'],
                    'slug' => $e['slug'],
                    'parent_id' => $bla->id,
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

        $this->info("\nDone. BLA event sub-topics — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
