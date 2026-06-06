<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Brings already-seeded dashboard markers up to date with case dispositions that
 * landed by June 2026 (pleas, verdicts, dismissals, appeals). The original
 * Add*DashboardCases seeders use firstOrCreate, so simply re-running them will
 * not touch existing rows; this command force-updates the live rows in place.
 *
 * It is idempotent and order-independent: each case is matched on its source URL
 * (plus a name fragment where co-defendants share one press-release URL), and
 * only the title is rewritten -- except James Comey, whose defunct source URL is
 * re-keyed to an authoritative reference. The replacement titles are identical to
 * the seeders, so the data converges whether a row was created by a seeder or by
 * this command.
 */
class AddDashboard2026Dispositions extends Command {
    protected $signature = 'dashboard:apply-2026-dispositions';
    protected $description = 'Update dashboard markers whose prosecutions changed status (pleas, verdicts, dismissals) by June 2026';

    public function handle(): int {
        $touched = 0;

        // James Comey: re-key a defunct source URL and reflect the Nov 2025
        // dismissal (unlawful appointment) plus the DOJ's Fourth Circuit appeal.
        $comeyOldUrl = 'https://www.cnbc.com/2026/04/28/james-comey-indicted-trump-seashell-8647.html';
        $comeyNewUrl = 'https://en.wikipedia.org/wiki/Prosecution_of_James_Comey';
        DashboardLink::where('url', $comeyOldUrl)->delete();
        $comey = DashboardLink::updateOrCreate(
            ['url' => $comeyNewUrl],
            [
                'title'          => 'Former FBI Director James Comey was indicted on charges of making a false statement to Congress and obstructing a congressional proceeding; a judge dismissed the case in November 2025, ruling the Trump-installed prosecutor unlawfully appointed, and the Justice Department appealed',
                'source'         => 'Wikipedia',
                'category'       => 'prosecution',
                'published_at'   => Carbon::parse('2025-11-24'),
                'location_label' => 'Alexandria, VA',
                'lat'            => 38.8048,
                'lng'            => -77.0469,
            ],
        );
        $this->line('Comey: '.($comey->wasRecentlyCreated ? 'created' : 'updated').' (dismissed, on appeal)');
        $touched++;

        // Cases keyed by a unique source URL: rewrite the title in place.
        $byUrl = [
            'Bolton (plea deal)' => [
                'url'   => 'https://www.nbcnews.com/politics/justice-department/john-bolton-indicted-trump-rcna236983',
                'title' => 'Former National Security Adviser John Bolton was indicted on 18 counts of mishandling classified information; the longtime Trump critic agreed in June 2026 to plead guilty to a single felony count of illegally retaining national-defense information as prosecutors dropped the rest',
            ],
            'Letitia James (dismissed, reindictment failed)' => [
                'url'   => 'https://www.nbcnews.com/politics/justice-department/judge-dismisses-cases-james-comey-letitia-james-finding-prosecutor-was-rcna244775',
                'title' => 'New York Attorney General Letitia James was indicted on bank-fraud charges by a Trump-installed prosecutor; a judge dismissed the case weeks later for the unlawful appointment, two grand juries then declined to reindict her, and the Justice Department appealed',
            ],
            'SPLC (moved to dismiss)' => [
                'url'   => 'https://www.npr.org/2026/04/22/nx-s1-5794620/doj-indicts-southern-poverty-law-center-on-federal-fraud-charges',
                'title' => 'The Justice Department indicted the Southern Poverty Law Center on 11 federal fraud and money-laundering counts; the civil-rights group pleaded not guilty, called it a vindictive, politically motivated prosecution, and moved to dismiss the case',
            ],
            'Mangione (federal trial Jan 2027)' => [
                'url'   => 'https://www.cnn.com/2026/01/30/us/luigi-mangione-case-rulings-trial',
                'title' => 'Federal judge tosses death-penalty counts against Luigi Mangione; his federal trial was later pushed to January 2027',
            ],
        ];
        foreach ($byUrl as $label => $row) {
            $n = DashboardLink::where('url', $row['url'])->update(['title' => $row['title']]);
            $this->line("{$label}: {$n} row(s) updated");
            $touched += $n;
        }

        // Co-defendants that share one press-release URL: match on a name fragment
        // (present in both the old and new titles), so each update is idempotent
        // and never touches a sibling defendant on the same URL.
        $byUrlAndName = [
            'Garduno Galvez (sentenced 4 yrs)' => [
                'url'   => 'https://www.justice.gov/usao-cdca/pr/federal-complaints-charge-socal-residents-assault-throwing-molotov-cocktails-officers',
                'like'  => '%Emiliano Garduno Galvez%',
                'title' => 'Paramount man Emiliano Garduno Galvez pleaded guilty to possessing an unregistered destructive device for throwing a Molotov cocktail toward deputies during the June 2025 immigration protests, and was sentenced to four years in federal prison',
            ],
            'Quiogue (sentenced 30 mo)' => [
                'url'   => 'https://www.justice.gov/usao-cdca/pr/federal-complaints-charge-socal-residents-assault-throwing-molotov-cocktails-officers',
                'like'  => '%Wrackkie Quiogue%',
                'title' => 'Long Beach man Wrackkie Quiogue pleaded guilty to possessing an unregistered destructive device tied to the June 2025 downtown Los Angeles immigration protests, and was sentenced to 30 months in federal prison',
            ],
            'Dana Briggs (dismissal sought)' => [
                'url'   => 'https://www.justice.gov/usao-ndil/pr/five-individuals-charged-federal-court-chicago-assaulting-or-resisting-federal-agents',
                'like'  => '%Dana Briggs%',
                'title' => 'Dana Briggs, a 70-year-old Air Force veteran charged with felony assault of a federal officer at the Broadview ICE facility, saw prosecutors move to dismiss the case',
            ],
            'Anthony Noto (guilty plea)' => [
                'url'   => 'https://www.justice.gov/usao-ndga/pr/social-media-provocateurs-charged-threatening-harm-federal-agent-and-his-wife',
                'like'  => '%Frank Waszut and Anthony Noto%',
                'title' => 'Two men, Frank Waszut and Anthony Noto, were indicted for transmitting online threats to injure an ICE deportation officer and his wife; Noto pleaded guilty in January 2026',
            ],
        ];
        foreach ($byUrlAndName as $label => $row) {
            $n = DashboardLink::where('url', $row['url'])
                ->where('title', 'like', $row['like'])
                ->update(['title' => $row['title']]);
            $this->line("{$label}: {$n} row(s) updated");
            $touched += $n;
        }

        $this->info("Done. Applied 2026 disposition updates ({$touched} change(s)).");

        return self::SUCCESS;
    }
}
