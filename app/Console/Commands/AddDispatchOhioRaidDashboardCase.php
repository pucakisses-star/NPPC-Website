<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Columbus Dispatch report on the FBI raid of the Ohio Organizing
 * Collaborative (an Ohio voting-rights / pro-democracy group) as an additional
 * source marker on the dashboard, alongside the MS NOW coverage already added
 * in the June 2026 batch. Categorized "other"; placed in Columbus (the
 * Dispatch's home and the group's statewide hub). Matched on URL with
 * updateOrCreate, so the command is idempotent.
 */
class AddDispatchOhioRaidDashboardCase extends Command {
    protected $signature = 'dashboard:add-ohio-raid-dispatch';
    protected $description = 'Add the Columbus Dispatch source on the Ohio voting-rights group FBI raid to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'FBI raids Ohio voting rights group',
                'url'            => 'https://www.dispatch.com/story/news/politics/2026/06/12/fbi-raid-ohio-voting-rights-group/90521146007/',
                'source'         => 'The Columbus Dispatch',
                'category'       => 'other',
                'published_at'   => '2026-06-12',
                'location_label' => 'Columbus, OH',
                'lat'            => 39.9612,
                'lng'            => -82.9988,
            ],
        ];

        $created = 0;
        $updated = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $updated++;
                $this->line("Updated: {$case['title']}");
            }
        }

        $this->info("Done. {$created} added, {$updated} updated.");

        return self::SUCCESS;
    }
}
