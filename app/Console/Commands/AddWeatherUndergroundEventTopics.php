<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates a topic page for each major event in the history of the Weather
 * Underground (1969–late 1970s) as a nested sub-topic of the Weather Underground
 * topic in /topics. Each event gets an expanded multi-paragraph essay; the
 * content lives in database/data/weather-underground-event-topics.json. Also
 * resets the Weather Underground topic body to its overview. Create-or-update
 * by slug, so it is idempotent. No-op if the Weather Underground topic is absent.
 */
final class AddWeatherUndergroundEventTopics extends Command
{
    protected $signature = 'topics:add-weather-underground-events';

    protected $description = 'Add a nested topic page for each major Weather Underground event under the WU topic';

    public function handle(): int
    {
        $wu = Topic::where('slug', 'weather-underground')->first();
        if (! $wu) {
            $this->warn('The "weather-underground" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $path = database_path('data/weather-underground-event-topics.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $events = json_decode((string) file_get_contents($path), true);
        if (! is_array($events)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $overview = '<p>The Weather Underground grew out of the 1969 split in Students for a Democratic Society, when a militant faction abandoned mass student organizing for clandestine armed struggle to "bring the war home." Through the first half of the 1970s it carried out symbolic bombings of government and corporate property — the Capitol, the Pentagon, the State Department — issuing warnings beforehand to avoid casualties. Its prosecutions largely collapsed amid revelations of illegal FBI surveillance, though several members were later imprisoned for other actions. The events below trace the group\'s emergence, its bombing campaign, and its decline.</p>';

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($wu, $events, $overview, &$added, &$updated) {
            $wu->body = $overview;
            $wu->published = true;
            $wu->save();

            foreach ($events as $e) {
                $existing = Topic::where('slug', $e['slug'])->first();
                $topic = $existing ?? new Topic(['slug' => $e['slug']]);
                $topic->fill([
                    'title' => $e['title'],
                    'slug' => $e['slug'],
                    'parent_id' => $wu->id,
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

        $this->info("\nDone. Weather Underground event sub-topics — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
