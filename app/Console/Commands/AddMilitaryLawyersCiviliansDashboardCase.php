<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds to the dashboard the May 1, 2026 ruling that the Justice Department may
 * use military lawyers to prosecute civilians. U.S. Magistrate Judge Shannon
 * Elkins in Minneapolis held that the Trump administration's assignment of
 * Judge Advocate General's Corps (JAG) lawyers to help prosecute civilians for
 * non-military offenses does not violate federal law, in the case of Paul
 * Johnson — a Minnesota resident charged with assaulting a Customs and Border
 * Protection agent during the administration's immigration-enforcement surge.
 * JAGs had similarly been sent to assist prosecutors in Washington, D.C. and
 * Tennessee. Filed as a prosecution marker. Idempotent (matched on URL).
 */
final class AddMilitaryLawyersCiviliansDashboardCase extends Command
{
    protected $signature = 'dashboard:add-military-lawyers-civilians';

    protected $description = 'Add the military-lawyers-prosecuting-civilians ruling to the dashboard';

    public function handle(): int
    {
        $case = [
            'title' => 'US Justice Department can use military lawyers to prosecute civilians, judge rules',
            'url' => 'https://www.reuters.com/world/us/us-justice-department-can-use-military-lawyers-prosecute-civilians-judge-rules-2026-05-02/',
            'source' => 'Reuters',
            'category' => 'prosecution',
            'published_at' => '2026-05-01',
            'location_label' => 'Minneapolis, Minnesota',
            'lat' => 44.9778,
            'lng' => -93.2650,
        ];

        $link = DashboardLink::updateOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        $this->info(($link->wasRecentlyCreated ? 'Added: ' : 'Updated: ').$case['title']);

        return self::SUCCESS;
    }
}
