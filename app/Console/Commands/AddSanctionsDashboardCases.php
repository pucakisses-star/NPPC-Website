<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds recent DOJ sanctions / export-control prosecutions to the dashboard as
 * DashboardLink markers (map pins + newswire). These are IEEPA / Export Control
 * Reform Act / sanctions-evasion enforcement actions announced on
 * justice.gov/news/press-releases -- Iran, Russia/Belarus, China, and North
 * Korea cases. Sourced from DOJ press releases plus corroborating news; matched
 * on URL so the command is idempotent and re-runnable.
 */
class AddSanctionsDashboardCases extends Command {
    protected $signature = 'dashboard:add-sanctions-cases';
    protected $description = 'Add recent DOJ sanctions / export-control prosecutions to the dashboard';

    public function handle(): int {
        $cases = [
            // ---- Iran ----
            [
                'title'          => 'Jamshid Ghomi, CEO of the Tehran tech firm Faraz Pardaz Rayaneh, was arrested on a federal charge of conspiring to violate U.S. sanctions by smuggling more than 250 metric tons of U.S.-origin networking equipment to Iranian nuclear and military programs',
                'url'            => 'https://www.justice.gov/opa/pr/ceo-iran-tech-company-arrested-federal-charge-supplying-us-equipment-irans-nuclear-and',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'prosecution',
                'published_at'   => '2026-06-03',
                'location_label' => 'Santa Ana, CA',
                'lat'            => 33.7455,
                'lng'            => -117.8677,
            ],
            [
                'title'          => 'The Justice Department filed civil forfeiture complaints to seize $15.3 million tied to the Shamkhani network, an Iranian oil-shipping and sanctions-evasion operation accused of funding the IRGC and its Quds Force',
                'url'            => 'https://www.justice.gov/opa/pr/united-states-files-civil-forfeiture-complaints-against-15m-funds-allegedly-linked-iranian',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'other',
                'published_at'   => '2026-03-06',
                'location_label' => 'Washington, DC',
                'lat'            => 38.9072,
                'lng'            => -77.0369,
            ],
            [
                'title'          => 'Iranian national Shamim Mafi was arrested at LAX on a federal complaint charging her with conspiring to violate U.S. sanctions by brokering a $70 million sale of Iranian-made Mohajer-6 military drones to Sudan',
                'url'            => 'https://www.justice.gov/usao-cdca/pr/iranian-national-living-san-fernando-valley-arrested-federal-complaint-charging-her',
                'source'         => 'U.S. Attorney (C.D. Cal.)',
                'category'       => 'prosecution',
                'published_at'   => '2026-04-20',
                'location_label' => 'Los Angeles, CA',
                'lat'            => 34.0522,
                'lng'            => -118.2437,
            ],

            // ---- Russia / Belarus ----
            [
                'title'          => 'Belarusian national Yana Leonova pleaded guilty to illegally exporting U.S.-sourced avionics and aircraft components to Russia in violation of export-control law',
                'url'            => 'https://www.justice.gov/usao-dc/pr/belarusian-woman-pleads-guilty-illegally-exporting-us-sourced-aviation-components-russia',
                'source'         => 'U.S. Attorney (D.D.C.)',
                'category'       => 'prosecution',
                'published_at'   => '2026-05-20',
                'location_label' => 'Washington, DC',
                'lat'            => 38.8926,
                'lng'            => -77.0147,
            ],
            [
                'title'          => 'Eleview International owner Oleg Nayandin and a senior employee, Vitaliy Borisenko, were sentenced for illegally exporting millions of dollars of U.S. technology to Russia through third countries',
                'url'            => 'https://www.justice.gov/usao-edva/pr/virginia-company-owner-and-senior-employee-sentenced-illegally-exporting-millions',
                'source'         => 'U.S. Attorney (E.D. Va.)',
                'category'       => 'prosecution',
                'published_at'   => '2026-02-13',
                'location_label' => 'Alexandria, VA',
                'lat'            => 38.8016,
                'lng'            => -77.0640,
            ],
            [
                'title'          => 'Delhi businessman Sanjay Kaushik was sentenced to 30 months for conspiring to illegally export a controlled aviation navigation and flight-control system from Oregon to Russian end users',
                'url'            => 'https://www.justice.gov/opa/pr/delhi-india-man-sentenced-conspiring-illegally-export-aviation-components-oregon-russia',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'prosecution',
                'published_at'   => '2026-01-16',
                'location_label' => 'Portland, OR',
                'lat'            => 45.5152,
                'lng'            => -122.6784,
            ],
            [
                'title'          => 'Iurii Gugnin, founder of the crypto-payment firm Evita, was charged with laundering roughly $530 million through U.S. banks to evade Russia sanctions and export controls',
                'url'            => 'https://www.justice.gov/opa/pr/founder-cryptocurrency-payment-company-charged-evading-sanctions-and-export-controls',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'prosecution',
                'published_at'   => '2025-06-09',
                'location_label' => 'Brooklyn, NY',
                'lat'            => 40.6936,
                'lng'            => -73.9890,
            ],

            // ---- China ----
            [
                'title'          => 'Three men — Yih-Shyan Liaw, Ruei-Tsang Chang and Ting-Wei Sun — were charged with conspiring to divert about $2.5 billion in servers containing advanced Nvidia AI chips to China in violation of U.S. export controls',
                'url'            => 'https://www.justice.gov/opa/pr/three-charged-conspiring-unlawfully-divert-cutting-edge-us-artificial-intelligence',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'prosecution',
                'published_at'   => '2026-03-19',
                'location_label' => 'New York, NY',
                'lat'            => 40.7136,
                'lng'            => -74.0011,
            ],
            [
                'title'          => 'Stanley Yi Zheng, Matthew Kelly and Tommy Shad English were charged in a roughly $170 million scheme to smuggle export-controlled AI servers and chips to China through Thailand',
                'url'            => 'https://www.justice.gov/opa/pr/chinese-national-and-two-us-citizens-charged-conspiring-smuggle-artificial-intelligence',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'prosecution',
                'published_at'   => '2026-03-25',
                'location_label' => 'Atlanta, GA',
                'lat'            => 33.7490,
                'lng'            => -84.3880,
            ],
            [
                'title'          => 'Alan Hao Hsu pleaded guilty as U.S. authorities dismantled a major China-linked network that smuggled more than $160 million in advanced Nvidia AI chips out of the United States',
                'url'            => 'https://www.justice.gov/opa/pr/us-authorities-shut-down-major-china-linked-ai-tech-smuggling-network',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'prosecution',
                'published_at'   => '2025-12-08',
                'location_label' => 'Houston, TX',
                'lat'            => 29.7589,
                'lng'            => -95.3677,
            ],

            // ---- North Korea ----
            [
                'title'          => 'U.S. nationals Kejia Wang and Zhenxing Wang were sentenced for facilitating a North Korean remote IT-worker scheme that used more than 80 stolen American identities to generate over $5 million for the regime',
                'url'            => 'https://www.justice.gov/opa/pr/two-us-nationals-sentenced-facilitating-fraudulent-remote-information-technology-worker-0',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'prosecution',
                'published_at'   => '2026-04-15',
                'location_label' => 'Boston, MA',
                'lat'            => 42.3530,
                'lng'            => -71.0455,
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
