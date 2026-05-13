<?php

namespace App\Console\Commands;

use App\Models\HistoryEra;
use App\Models\HistoryTopic;
use Illuminate\Console\Command;

/**
 * Reorders HistoryTopics under the 1800s era so the timeline reads:
 *   1. Anti-Rent War (1839-1845)
 *   2. Abolition Movement (1830s-1860s)
 *   3. Civil War (1861-1865)
 *   4. Coxey's Army (1894)
 *
 * The Anti-Rent War actually began before the Abolition Movement's
 * peak years and was earlier-resolved, so it should display first.
 */
final class Resort1800sHistory extends Command {
    protected $signature = 'archive:resort-1800s-history';
    protected $description = 'Reorder 1800s history topics so Anti-Rent War comes before Abolition Movement';

    public function handle(): int {
        $era = HistoryEra::where('slug', '1800s')->first();
        if (! $era) {
            $this->error('1800s era not found.');

            return self::FAILURE;
        }

        $order = [
            'The Anti-Rent War'      => 1,
            'The Abolition Movement' => 2,
            'The Civil War'          => 3,
            "Coxey's Army"           => 4,
        ];

        foreach ($order as $title => $sortOrder) {
            $topic = HistoryTopic::where('history_era_id', $era->id)
                ->where('title', $title)->first();
            if (! $topic) {
                $this->warn("Topic not found: {$title}");

                continue;
            }
            if ($topic->sort_order === $sortOrder) {
                $this->line("  {$title}: already at {$sortOrder}");

                continue;
            }
            $old = $topic->sort_order;
            $topic->update(['sort_order' => $sortOrder]);
            $this->info("  {$title}: {$old} → {$sortOrder}");
        }

        return self::SUCCESS;
    }
}
