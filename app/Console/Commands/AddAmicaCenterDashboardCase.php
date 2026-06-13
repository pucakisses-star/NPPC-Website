<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the Amica Center report that ICE Homeland Security Investigations and
 * HHS agents made unannounced visits to legal-services nonprofits representing
 * unaccompanied immigrant children (Amica Center, Ayuda, and Kids in Need of
 * Defense) in the Washington, DC area, seeking client documents and financial
 * records without warrants and being turned away. Categorized "other" (an
 * intimidation / targeting-of-advocates item, not an arrest or prosecution).
 * Matched on URL with updateOrCreate, so the command is idempotent.
 */
class AddAmicaCenterDashboardCase extends Command {
    protected $signature = 'dashboard:add-amica-center-case';
    protected $description = 'Add the Amica Center report on federal agents intimidating immigrant-children legal nonprofits';

    public function handle(): int {
        $cases = [
            [
                'title'          => 'Trump Administration Deploys Federal Agents to Intimidate Legal Services Nonprofits Representing Unaccompanied Immigrant Children',
                'url'            => 'https://amicacenter.org/press-releases/trump-administration-deploys-federal-agents-to-intimidate-legal-services-nonprofits-representing-unaccompanied-immigrant-children/',
                'source'         => 'Amica Center for Immigrant Rights',
                'category'       => 'other',
                'published_at'   => '2026-06-12',
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
