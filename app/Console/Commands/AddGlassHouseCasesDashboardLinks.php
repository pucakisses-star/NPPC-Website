<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Glass House Farms raid legal-case coverage: the Los Angeles Times
 * report on the April 10, 2026 federal-jury acquittal of CSU Channel
 * Islands professor Jonathan Caravello (charged with assaulting ICE
 * agents with their own tear-gas canister during the July 10, 2025
 * Camarillo raid protest), and the Ventura County Star one-year
 * roundup of where the remaining criminal and civil cases stand —
 * the Isai Carrillo / Virginia Reyes trial postponed to March 2027,
 * the Jaime Alanis Garcia wrongful-death suit, and George Retes's
 * unlawful-detention suit.
 *
 * The acquittal was already pinned via an NBC Los Angeles row; that
 * row is removed here so the event keeps a single pin under the
 * supplied LA Times source.
 */
final class AddGlassHouseCasesDashboardLinks extends Command
{
    protected $signature = 'dashboard:add-glass-house-cases';

    protected $description = 'Add the Glass House raid case coverage (Caravello acquittal + one-year legal roundup) to the dashboard newswire';

    public function handle(): int
    {
        $links = [
            [
                'url' => 'https://www.latimes.com/california/story/2026-04-10/csu-professor-acquitted-of-assaulting-u-s-agents-with-their-own-tear-gas',
                'title' => 'Federal jury acquits CSU Channel Islands professor Jonathan Caravello of assaulting U.S. agents with their own tear gas at the Glass House raid protest',
                'source' => 'Los Angeles Times',
                'category' => 'prosecution',
                'published_at' => Carbon::parse('2026-04-10'),
                'location_label' => 'Camarillo, CA',
                'lat' => 34.2164,
                'lng' => -119.0376,
            ],
            [
                'url' => 'https://okcthunderwire.usatoday.com/story/news/courts/2026/07/09/heres-where-the-legal-case-related-to-the-glass-house-raid-stand/90778037007/',
                'title' => 'One year after the Glass House raid: Carrillo/Reyes trial pushed to March 2027, wrongful-death and unlawful-detention suits proceed',
                'source' => 'Ventura County Star',
                'category' => 'prosecution',
                'published_at' => Carbon::parse('2026-07-09'),
                'location_label' => 'Camarillo, CA',
                'lat' => 34.2140,
                'lng' => -119.0410,
            ],
        ];

        foreach ($links as $link) {
            $url = $link['url'];
            unset($link['url']);
            $row = DashboardLink::updateOrCreate(['url' => $url], $link);
            $this->info(($row->wasRecentlyCreated ? 'Added' : 'Refreshed')." dashboard link: {$row->title}");
        }

        $superseded = DashboardLink::where('url', 'https://www.nbclosangeles.com/news/local/jury-acquits-csu-channel-islands-professor-federal-ice-assault-case/3874052/')->first();
        if ($superseded) {
            $superseded->delete();
            $this->info('Removed the superseded NBC Los Angeles row for the same acquittal.');
        }

        return self::SUCCESS;
    }
}
