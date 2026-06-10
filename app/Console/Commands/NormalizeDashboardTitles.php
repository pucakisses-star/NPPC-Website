<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;

/**
 * Rewrites selected dashboard marker titles to match their source article
 * headlines (instead of hand-written descriptive summaries). The seeders that
 * created these rows use firstOrCreate, so they will not update existing rows;
 * this force-updates the live rows. Idempotent and order-independent: each
 * 1:1 marker is matched by source URL, and the three Newark/Delaney Hall markers
 * that shared one Patch article are consolidated into a single marker.
 */
class NormalizeDashboardTitles extends Command {
    protected $signature = 'dashboard:normalize-titles';
    protected $description = 'Rewrite selected dashboard marker titles to match their source article headlines';

    public function handle(): int {
        $touched = 0;

        // 1:1 markers: set the title to the real article headline, matched by URL.
        $byUrl = [
            'https://www.thenation.com/article/society/sonny-rollins-exonerated-court-martial-racism/'
                => 'Sonny Rollins Lived to See Justice for His Wrongly Convicted Father',
            'https://www.dailykos.com/stories/2026/6/8/800052211/stateandlocal/los-angeles-deportation-ice-one-year-later/'
                => 'A year after Trump’s gestapo invaded LA, the fear remains',
            'https://www.thecityreporter.nyc/2026/06/10/amazon-driver-teamsters-fired-tiktok/'
                => 'NYC Amazon Driver Fired Over Pro-Union Social Media Posts',
            'https://capitolnewsillinois.com/news/now-cleared-broadview-6-immigration-protesters-seek-evidence-of-white-house-pressure-to-indict/'
                => 'Now-cleared ‘Broadview 6’ immigration protesters seek evidence of White House pressure to indict',
            'https://www.nationalnursesunited.org/press/nurses-at-research-medical-center-to-hold-rally-demanding-immediate-action-on-unsafe-patient-care-conditions'
                => 'Nurses at Research Medical Center to hold rally demanding immediate action on unsafe patient care conditions',
            'https://www.justice.gov/opa/pr/ceo-iran-tech-company-arrested-federal-charge-supplying-us-equipment-irans-nuclear-and'
                => 'CEO of Iran Tech Company Arrested on Federal Charge of Supplying U.S. Equipment to Iran’s Nuclear and Military Establishment',
            'https://www.injusticewatch.org/civil-courts/immigration/2026/dhs-alert-comedian-ben-palmer/'
                => 'DHS issued ‘be on the lookout’ alert for comedian Ben Palmer',
        ];
        foreach ($byUrl as $url => $title) {
            $n = DashboardLink::where('url', $url)->update(['title' => $title]);
            $this->line("{$n} row(s): {$title}");
            $touched += $n;
        }

        // Newark / Delaney Hall: three co-defendant markers (Clemens, Riddle,
        // Becker) all shared one Patch article. Consolidate to a single marker
        // carrying the real headline: keep one row, drop the rest, retitle it.
        $patch = 'https://patch.com/new-jersey/newarknj/arrests-continue-delaney-hall-after-ice-drawdown-they-re-slowing-down-nj-mayor';
        $ids = DashboardLink::where('url', $patch)->orderBy('id')->pluck('id');
        if ($ids->count() > 1) {
            DashboardLink::where('url', $patch)->whereNotIn('id', [$ids->first()])->delete();
        }
        $n = DashboardLink::where('url', $patch)->update([
            'title' => 'Arrests Continue At Delaney Hall After ICE Drawdown – But They’re Slowing Down, NJ Mayor Says',
        ]);
        $this->line("Newark/Delaney Hall: consolidated to {$n} marker.");
        $touched += $n;

        $this->info("Done. Normalized {$touched} title(s).");

        return self::SUCCESS;
    }
}
