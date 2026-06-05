<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds miscellaneous free-speech / protest arrest cases to the dashboard as
 * DashboardLink markers (map pins + newswire). Each is an arrest or charge over
 * expressive conduct — a joke, a post, sidewalk chalk, a protest. Sourced from
 * public reporting; matched on URL so the command is idempotent and re-runnable.
 */
class AddSpeechArrestDashboardCases extends Command {
    protected $signature = 'dashboard:add-speech-cases';
    protected $description = 'Add miscellaneous free-speech / protest arrest cases to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'FIU student Gabriela Saldana charged with a felony "written threat" over a WhatsApp joke about Netanyahu',
                'url'            => 'https://wsvn.com/news/local/miami-dade/fiu-student-arrested-for-wanting-israels-netanyahu-to-drop-bombs-on-school-event-arena-police-say/',
                'source'         => 'WSVN',
                'category'       => 'arrest',
                'published_at'   => '2026-04-16',
                'location_label' => 'Miami, FL',
                'lat'            => 25.7563,
                'lng'            => -80.3736,
            ],
            [
                'title'          => 'Protesters arrested (charges later dropped) for chalking the rainbow back onto Orlando\'s Pulse memorial crosswalk',
                'url'            => 'https://www.orlandoweekly.com/news/pulse/state-attorney-drops-charges-for-pulse-crosswalk-arrests/',
                'source'         => 'Orlando Weekly',
                'category'       => 'protest',
                'published_at'   => '2025-08-31',
                'location_label' => 'Orlando, FL',
                'lat'            => 28.5306,
                'lng'            => -81.3766,
            ],
        ];

        $created = 0;
        foreach ($cases as $case) {
            $link = DashboardLink::firstOrCreate(
                ['url' => $case['url']],
                array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->info("Added: {$case['title']}");
            } else {
                $this->line("Skipped (already present): {$case['title']}");
            }
        }

        $this->info("Done. {$created} new case(s) added; ".(count($cases) - $created).' already present.');

        return self::SUCCESS;
    }
}
