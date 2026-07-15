<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates a topic page for each major event in the history of the Black Panther
 * Party (1966–early 1980s) as a nested sub-topic of the Black Panther Party
 * topic in /topics — from the 1966 founding through the party's dissolution.
 * Each event gets an expanded multi-paragraph essay; the content lives in
 * database/data/bpp-event-topics.json. Also resets the Black Panther Party topic
 * body to its overview. Create-or-update by slug, so it is idempotent. No-op if
 * the Black Panther Party topic is absent.
 */
final class AddBppEventTopics extends Command
{
    protected $signature = 'topics:add-bpp-events';

    protected $description = 'Add a nested topic page for each major Black Panther Party event under the BPP topic';

    public function handle(): int
    {
        $bpp = Topic::where('slug', 'black-panther-party')->first();
        if (! $bpp) {
            $this->warn('The "black-panther-party" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $path = database_path('data/bpp-event-topics.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $events = json_decode((string) file_get_contents($path), true);
        if (! is_array($events)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $overview = '<p>Founded in Oakland in 1966 by Huey P. Newton and Bobby Seale, the Black Panther Party fused armed self-defense against police violence with revolutionary Black politics and grassroots "survival programs" such as free breakfasts and community health clinics. It became the FBI\'s primary COINTELPRO target; many members were imprisoned, exiled, or killed, and several remain among the longest-held political prisoners in the United States. The events below trace the party\'s rise, its community programs, and the campaign of repression that broke its national organization.</p>';

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($bpp, $events, $overview, &$added, &$updated) {
            $bpp->body = $overview;
            $bpp->published = true;
            $bpp->save();

            foreach ($events as $e) {
                $existing = Topic::where('slug', $e['slug'])->first();
                $topic = $existing ?? new Topic(['slug' => $e['slug']]);
                $topic->fill([
                    'title' => $e['title'],
                    'slug' => $e['slug'],
                    'parent_id' => $bpp->id,
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

        $this->info("\nDone. BPP event sub-topics — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
