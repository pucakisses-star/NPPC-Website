<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds ProPublica's report that the Trump Justice Department shut down a
 * federal criminal investigation into Clean Water Act violations by Sen. Jim
 * Justice's Southern Coal operations, sidelining the career prosecutors who
 * had built the case. A DOJ-politicization / favoritism item (the inverse of
 * the dashboard's usual repression markers), added as "other" and anchored in
 * West Virginia. Matched on URL with updateOrCreate, so the command is
 * idempotent.
 */
class AddJimJusticeDashboardCase extends Command {
    protected $signature = 'dashboard:add-jim-justice-case';
    protected $description = 'Add ProPublica report on the DOJ shutting down the Jim Justice / Southern Coal investigation';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Trump\'s DOJ Shut Down a Criminal Investigation Into Sen. Jim Justice\'s Coal Companies',
                'url'            => 'https://www.propublica.org/article/trump-jim-justice-doj-southern-coal-investigation-west-virginia',
                'source'         => 'ProPublica',
                'category'       => 'other',
                'published_at'   => '2026-06-08',
                'location_label' => 'Charleston, WV',
                'lat'            => 38.3498,
                'lng'            => -81.6326,
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
