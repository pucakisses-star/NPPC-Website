<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds dashboard newswire/map entries for states that had no prior
 * representation on the tracker. Sourced from public reporting and matched
 * on URL (updateOrCreate) so the command is idempotent and re-runnable.
 *
 * Delaware is the one previously-unrepresented state with a clearly-dated
 * event inside the mid-June to mid-July 2026 window: the General Assembly's
 * June 30 passage of HB 182, banning local 287(g) police–ICE agreements.
 */
final class AddUnrepresentedStatesDashboardCases extends Command
{
    protected $signature = 'dashboard:add-unrepresented-states';

    protected $description = 'Add dashboard entries for previously-unrepresented states';

    public function handle(): int
    {
        $cases = [
            [
                'url'            => 'https://www.aclu-de.org/news/delaware-banned-287g-agreements-ice-heres-what-must-happen-next/',
                'title'          => 'Delaware General Assembly passes HB 182, banning local police 287(g) agreements with ICE',
                'source'         => 'ACLU of Delaware',
                'category'       => 'other',
                'published_at'   => '2026-06-30',
                'location_label' => 'Dover, DE',
                'lat'            => 39.1573,
                'lng'            => -75.5197,
            ],
        ];

        $added = 0;
        foreach ($cases as $c) {
            $link = DashboardLink::updateOrCreate(
                ['url' => $c['url']],
                [
                    'title'          => $c['title'],
                    'source'         => $c['source'],
                    'category'       => $c['category'],
                    'published_at'   => Carbon::parse($c['published_at']),
                    'location_label' => $c['location_label'],
                    'lat'            => $c['lat'],
                    'lng'            => $c['lng'],
                ],
            );
            $this->line(($link->wasRecentlyCreated ? '  Added: ' : '  Refreshed: ').$link->title);
            $added++;
        }

        $this->info("\nDone. Processed {$added} dashboard "
            .($added === 1 ? 'entry' : 'entries').'.');

        return self::SUCCESS;
    }
}
