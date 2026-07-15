<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates a topic page for each major event in the history of the Industrial
 * Workers of the World (1905–1919) as a nested sub-topic of the IWW topic in
 * /topics — from the 1905 founding through the 1919 Centralia Massacre. Each
 * event gets an expanded multi-paragraph essay; the content lives in
 * database/data/iww-event-topics.json. Also resets the IWW topic body to its
 * overview. Create-or-update by slug, so it is idempotent and owns the Everett
 * Massacre sub-topic as well. No-op if the IWW topic is absent.
 */
final class AddIwwEventTopics extends Command
{
    protected $signature = 'topics:add-iww-events';

    protected $description = 'Add a nested topic page for each major IWW event (1905–1919) under the IWW topic';

    public function handle(): int
    {
        $iww = Topic::where('slug', 'industrial-workers-of-the-world')->first();
        if (! $iww) {
            $this->warn('The "industrial-workers-of-the-world" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $path = database_path('data/iww-event-topics.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $events = json_decode((string) file_get_contents($path), true);
        if (! is_array($events)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $overview = '<p>The Industrial Workers of the World (IWW), whose members are known as "Wobblies," is a revolutionary industrial union founded in Chicago in 1905 to organize all workers into "One Big Union." Its militant free-speech fights and strikes drew ferocious repression: songwriter Joe Hill was executed in 1915, organizer Frank Little was lynched in 1917, and during World War I the federal government prosecuted more than a hundred IWW leaders — among them Big Bill Haywood — under the Espionage Act in one of the largest mass political trials in U.S. history. Many served long sentences at Leavenworth, and the union remains a touchstone of American labor radicalism. The events below trace the union\'s rise and the campaign of repression that broke it.</p>';

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($iww, $events, $overview, &$added, &$updated) {
            $iww->body = $overview;
            $iww->published = true;
            $iww->save();

            foreach ($events as $e) {
                $existing = Topic::where('slug', $e['slug'])->first();
                $topic = $existing ?? new Topic(['slug' => $e['slug']]);
                $topic->fill([
                    'title' => $e['title'],
                    'slug' => $e['slug'],
                    'parent_id' => $iww->id,
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

        $this->info("\nDone. IWW event sub-topics — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
