<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds the Prairieland Defendants support-campaign links to every Prairieland
 * defendant profile: the official site (prairielanddefendants.com) plus the DFW
 * Support Committee social accounts scraped from it. The site publishes X and
 * Instagram (also TikTok/Bluesky, which the Prisoner model has no fields for);
 * no Facebook link is published, so that field is left untouched. Idempotent.
 */
final class SetPrairielandSupportLinks extends Command
{
    protected $signature = 'prisoners:set-prairieland-support-links';

    protected $description = 'Add the prairielanddefendants.com site + DFW Support Committee socials to Prairieland defendant profiles';

    private const LINKS = [
        'website' => 'https://prairielanddefendants.com/',
        'twitter' => 'https://x.com/DFWSupCommittee',
        'instagram' => 'https://www.instagram.com/dfwsupportcommittee/',
    ];

    public function handle(): int
    {
        $defendants = Prisoner::withoutGlobalScopes()
            ->where(fn ($q) => $q->where('description', 'like', '%Prairieland%')
                ->orWhere('affiliation', 'like', '%Prairieland%'))
            ->get();

        foreach ($defendants as $prisoner) {
            $prisoner->fill(self::LINKS)->save();
            $this->line("  {$prisoner->name} ({$prisoner->slug})");
        }

        $this->info("\nDone. Set support links on {$defendants->count()} Prairieland defendant profile(s).");
        $this->warn('No Facebook link is published on the site, so the facebook field was left untouched.');

        return self::SUCCESS;
    }
}
