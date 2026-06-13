<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the follow-up developments in the Turtle Island Liberation Front
 * bombing-plot case that postdate the initial arrest entries:
 *
 *  - The Dec. 23, 2025 six-count federal grand-jury terrorism indictment,
 *    added to the dashboard as a "prosecution" marker (the case progressing
 *    arrest -> indictment).
 *  - Refreshes the four defendants' case detail with the indictment and the
 *    Feb. 17, 2026 tentative trial date (Carroll, Page, and Lai; Gaffield was
 *    arraigned separately Jan. 20).
 *
 * As of this writing no verdict, plea deal, or sentencing has been reported,
 * so the records remain awaiting-trial. Idempotent — the dashboard link uses
 * updateOrCreate and the case updates re-set the same values.
 */
class UpdateTurtleIslandCase extends Command {
    protected $signature = 'turtle-island:update';
    protected $description = 'Add the Turtle Island indictment to the dashboard and refresh the four profiles with indictment/trial detail';

    public function handle(): int {
        // 1) Indictment dashboard marker (prosecution).
        $link = DashboardLink::updateOrCreate(
            ['url' => 'https://www.justice.gov/usao-cdca/pr/grand-jury-charges-four-members-anti-government-group-terrorism-felonies-stemming-new'],
            [
                'title'          => 'Grand Jury Charges Four Members of Anti-Government Group with Terrorism Felonies Stemming from New Year\'s Eve Bombing Plot',
                'source'         => 'U.S. Attorney (C.D. Cal.)',
                'category'       => 'prosecution',
                'published_at'   => Carbon::parse('2025-12-23'),
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0522,
                'lng'            => -118.2437,
            ],
        );
        $this->info(($link->wasRecentlyCreated ? 'Added' : 'Updated').' dashboard indictment marker.');

        // 2) Refresh each defendant's case detail.
        $indicted = 'Yes — six-count federal grand-jury indictment for terrorism felonies, Central District of California, returned December 23, 2025';
        $trialGroup = 'Pleaded not guilty (arraigned January 2026). Trial tentatively set for February 17, 2026, U.S. District Court, Central District of California.';

        $updates = [
            'audrey-carroll' => [
                'indicted' => $indicted,
                'plead'    => $trialGroup.' Statutory maximum if convicted: life in federal prison.',
            ],
            'zachary-page' => [
                'indicted' => $indicted,
                'plead'    => $trialGroup.' Statutory maximum if convicted: life in federal prison.',
            ],
            'tina-lai' => [
                'indicted' => $indicted,
                'plead'    => $trialGroup.' Statutory maximum if convicted: 25 years in federal prison.',
            ],
            'dante-gaffield' => [
                'indicted' => $indicted,
                'plead'    => 'Pleaded not guilty (arraigned January 20, 2026), U.S. District Court, Central District of California. Statutory maximum if convicted: 25 years in federal prison.',
            ],
        ];

        $touched = 0;
        foreach ($updates as $slug => $fields) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Prisoner {$slug} not found — skipping.");

                continue;
            }

            $case = $prisoner->cases()->first();
            if (! $case) {
                $this->warn("No case on {$slug} — skipping.");

                continue;
            }

            $case->indicted = $fields['indicted'];
            $case->plead = $fields['plead'];
            $case->save();
            $this->info("Updated case detail for {$prisoner->name}.");
            $touched++;
        }

        $this->info("\nDone. Dashboard marker set; {$touched} profile case(s) refreshed.");

        return self::SUCCESS;
    }
}
