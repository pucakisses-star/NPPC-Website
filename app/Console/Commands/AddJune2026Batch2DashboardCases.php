<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * A second June 2026 batch of dashboard items:
 *
 *  - Truthout's report that the DOJ brought federal charges against eight
 *    University of Michigan Palestine-solidarity activists (the prosecution
 *    development of the existing UMich FBI-raid marker).
 *  - The New Republic's coverage of the FBI raid on the Ohio Organizing
 *    Collaborative (a third source alongside MS NOW and the Columbus Dispatch).
 *  - The Boston Globe's report on five Extinction Rebellion climate activists
 *    arrested blocking Copley Square outside a natural-gas convention.
 *  - The AP's coverage of the contempt hearing in the Tyler Robinson / Charlie
 *    Kirk case, where the defense sought sanctions against prosecutors over an
 *    alleged gag-order-violating media tour (an update to the existing Robinson
 *    marker).
 *  - Them's piece marking ten years since the Pulse nightclub shooting.
 *
 * Matched on URL with updateOrCreate, so the command is idempotent.
 */
class AddJune2026Batch2DashboardCases extends Command {
    protected $signature = 'dashboard:add-june-2026-batch-2';
    protected $description = 'Add a second June 2026 batch (UMich Palestine charges, Ohio raid 3rd source, Boston climate arrests)';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'DOJ Targets Michigan Palestine Solidarity Activists With Federal Charges',
                'url'            => 'https://truthout.org/articles/doj-targets-michigan-palestine-solidarity-activists-with-federal-charges/',
                'source'         => 'Truthout',
                'category'       => 'prosecution',
                'published_at'   => '2026-06-12',
                'location_label' => 'Ann Arbor, MI',
                'lat'            => 42.2790,
                'lng'            => -83.7440,
            ],
            [
                'title'          => 'FBI Raids Ohio Organizing Collaborative in Apparent Intimidation Campaign',
                'url'            => 'https://newrepublic.com/post/211755/fbi-ohio-organizing-collective-raid-intimidation',
                'source'         => 'The New Republic',
                'category'       => 'other',
                'published_at'   => '2026-06-12',
                'location_label' => 'Cleveland, OH',
                'lat'            => 41.4985,
                'lng'            => -81.6935,
            ],
            [
                'title'          => 'Five arrested during climate protest that blocked traffic in Copley Square',
                'url'            => 'https://www.bostonglobe.com/2026/06/08/metro/boston-climate-protest-five-arrested/',
                'source'         => 'The Boston Globe',
                'category'       => 'arrest',
                'published_at'   => '2026-06-08',
                'location_label' => 'Boston, MA',
                'lat'            => 42.3496,
                'lng'            => -71.0779,
            ],
            [
                'title'          => 'Robinson\'s lawyers seek contempt sanctions against prosecutors in Charlie Kirk case',
                'url'            => 'https://apnews.com/article/charlie-kirk-tyler-robinson-contempt-hearing-668d80039fb8a81d70d67af85ebc8ecf',
                'source'         => 'The Associated Press',
                'category'       => 'prosecution',
                'published_at'   => '2026-06-12',
                'location_label' => 'Provo, UT',
                'lat'            => 40.2338,
                'lng'            => -111.6585,
            ],
            [
                'title'          => 'Ten Years After the Pulse Nightclub Shooting, Survivors Reflect',
                'url'            => 'https://www.them.us/story/pulse-nightclub-ten-year-shooting-anniversary-survivors',
                'source'         => 'Them',
                'category'       => 'other',
                'published_at'   => '2026-06-12',
                'location_label' => 'Orlando, FL',
                'lat'            => 28.5310,
                'lng'            => -81.3768,
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
