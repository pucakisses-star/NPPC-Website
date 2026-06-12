<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds Defending Rights & Dissent's FOIA lawsuit against the State Department,
 * FBI, DOJ, and ICE — seeking records on federal surveillance of Palestine
 * solidarity activism, including agency communications with private
 * blacklisting groups (ADL, Betar, Canary Mission) used for selective
 * immigration enforcement — to the dashboard as a DashboardLink marker. The
 * underlying FOIA request was filed in April 2025; the suit followed in 2026
 * after the agencies stonewalled. Categorized "other" (a surveillance/
 * transparency-litigation item, not an arrest or prosecution). Matched on URL
 * so the command is idempotent and safe to re-run.
 */
class AddPalestineFoiaDashboardCases extends Command {
    protected $signature = 'dashboard:add-palestine-foia-cases';
    protected $description = 'Add the Defending Rights & Dissent FOIA surveillance lawsuit to the dashboard';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Defending Rights & Dissent Sues State Dept, FBI, DOJ, & ICE For Records on Surveillance of Palestine Solidarity Activism',
                'url'            => 'https://www.rightsanddissent.org/news/defending-rights-dissent-sues-state-dept-fbi-doj-ice-for-records-on-surveillance-of-palestine-solidarity-activism/',
                'source'         => 'Defending Rights & Dissent',
                'category'       => 'other',
                'published_at'   => '2026-06-11',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8951,
                'lng'            => -77.0364,
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
