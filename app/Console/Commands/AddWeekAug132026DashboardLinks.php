<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Dashboard newswire additions for the week of August 6-13, 2026, which
 * is where the newswire had run out: its most recent entry was August 6.
 *
 * Six rows. Keith LaMar's execution reprieve is the one that matters most
 * to this archive — a Lucasville defendant speaking from death row — and
 * it was not on the wire at all. The rest are the week's detention,
 * protest and dissent items, plus one older press-freedom prosecution
 * that had been missed: the arrest of journalist Georgia Fort for filming
 * a protest, which belongs on a wire about the policing of dissent.
 *
 * Every URL was requested before being written here. The Minnesota
 * Reformer report on Fort's motion to dismiss was the natural source for
 * that row and is left out: it answers 403 to every automated request, so
 * it could not be confirmed as reachable, and the U.S. Press Freedom
 * Tracker record of the same prosecution is used instead.
 *
 * Idempotent (updateOrCreate keyed by URL).
 */
final class AddWeekAug132026DashboardLinks extends Command {
    protected $signature = 'dashboard:add-week-2026-08-13';
    protected $description = 'Add dashboard links for the week of August 6-13, 2026';

    public function handle(): int {
        $links = [
            [
                'url' => 'https://www.democracynow.org/2026/8/10/ohio_death_penalty',
                'title' => 'Keith LaMar speaks from Ohio death row after a three-year execution reprieve',
                'source' => 'Democracy Now!',
                'category' => 'prosecution',
                'published_at' => '2026-08-10',
                // Ohio State Penitentiary, Youngstown — where LaMar has been
                // held in solitary since the 1993 Lucasville uprising trials.
                'location_label' => 'Ohio State Penitentiary, Youngstown, OH',
                'lat' => 41.1520,
                'lng' => -80.6420,
            ],
            [
                'url' => 'https://www.democracynow.org/2026/8/10/headlines/ap_trump_admin_detains_parents_and_spouses_of_active_duty_us_military_personnel_in_immigration_crackdown',
                'title' => 'AP investigation: more than 50 parents and spouses of active-duty U.S. service members detained since January 2025',
                'source' => 'Associated Press via Democracy Now!',
                'category' => 'arrest',
                'published_at' => '2026-08-10',
                'location_label' => 'Nationwide (pinned at Washington, DC)',
                'lat' => 38.9072,
                'lng' => -77.0369,
            ],
            [
                'url' => 'https://www.democracynow.org/2026/8/10/headlines/rallies_held_to_mark_one_month_since_ice_killing_of_mexican_father_lorenzo_salgado_araujo',
                'title' => 'Rallies across Texas mark one month since the ICE killing of Lorenzo Salgado Araujo',
                'source' => 'Democracy Now!',
                'category' => 'protest',
                'published_at' => '2026-08-10',
                // Same pin as the July rows for this killing, so the month of
                // protest reads as one place on the map rather than scattering.
                'location_label' => 'Houston, TX',
                'lat' => 29.7604,
                'lng' => -95.3698,
            ],
            [
                'url' => 'https://pressfreedomtracker.us/all-incidents/independent-journalist-arrested-charged-over-minnesota-protest-coverage/',
                'title' => 'Journalist Georgia Fort indicted and arrested over her filming of a protest against an immigration operation at a St. Paul church',
                'source' => 'U.S. Press Freedom Tracker',
                'category' => 'prosecution',
                // The arrest, not this week: she was indicted January 29 and
                // arrested January 30, 2026, and the case was still pending at
                // the tracker's last update. Dated to the arrest so it sits in
                // the wire where it happened rather than pretending to be new.
                'published_at' => '2026-01-30',
                'location_label' => 'St. Paul, MN',
                'lat' => 44.9537,
                'lng' => -93.0900,
            ],
            [
                'url' => 'https://www.democracynow.org/2026/8/11/headlines/state_department_revokes_more_than_175_000_visas_from_foreign_nationals',
                'title' => 'State Department revokes more than 175,000 visas held by foreign nationals',
                'source' => 'Democracy Now!',
                'category' => 'other',
                'published_at' => '2026-08-11',
                'location_label' => 'Nationwide (pinned at Washington, DC)',
                'lat' => 38.8951,
                'lng' => -77.0364,
            ],
            [
                'url' => 'https://www.democracynow.org/2026/8/12/headlines/ice_plans_to_spend_20m_to_purchase_electric_shock_gloves',
                'title' => 'ICE plans a $20 million purchase of electric shock gloves for immigration officers',
                'source' => 'Democracy Now!',
                'category' => 'other',
                'published_at' => '2026-08-12',
                'location_label' => 'Nationwide (pinned at Washington, DC)',
                'lat' => 38.8899,
                'lng' => -77.0091,
            ],
        ];

        foreach ($links as $link) {
            $url = $link['url'];
            unset($link['url']);
            $link['published_at'] = Carbon::parse($link['published_at']);
            DashboardLink::updateOrCreate(['url' => $url], $link);
            $this->info("Upserted: {$link['title']}");
        }

        $this->info("\n".count($links).' link(s) upserted.');

        return self::SUCCESS;
    }
}
