<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Freedom of the Press Foundation FOIA lawsuit against the DOJ — over
 * whether the department concealed the Privacy Protection Act from judges when
 * seeking search warrants against journalists (including the FBI raid on a
 * Washington Post reporter's home) — to the dashboard newswire. Categorized
 * "other" (a press-freedom / legal item), with no map coordinates since it is a
 * national legal action rather than a located incident. Matched on URL with
 * updateOrCreate, so the command is idempotent ("add if not there already").
 */
class AddFpfPressProtectionsDashboardCase extends Command {
    protected $signature = 'dashboard:add-fpf-press-protections-case';
    protected $description = 'Add the FPF v. DOJ press-protections FOIA lawsuit to the dashboard';

    public function handle(): int {
        $case = [
            'title'        => 'Is DOJ hiding press protections to raid reporters? We sue to find out',
            'url'          => 'https://freedom.press/issues/is-doj-hiding-press-protections-to-raid-reporters-we-sue-to-find-out/',
            'source'       => 'Freedom of the Press Foundation',
            'category'     => 'other',
            'published_at' => '2026-06-08',
        ];

        $link = DashboardLink::updateOrCreate(
            ['url' => $case['url']],
            array_merge($case, ['published_at' => Carbon::parse($case['published_at'])]),
        );

        if ($link->wasRecentlyCreated) {
            $this->info("Added: {$case['title']}");
        } else {
            $this->line("Already present, refreshed: {$case['title']}");
        }

        return self::SUCCESS;
    }
}
