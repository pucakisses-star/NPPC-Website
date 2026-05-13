<?php

namespace App\Console\Commands;

use App\Models\HistoryEra;
use App\Models\HistoryTopic;
use Illuminate\Console\Command;

/**
 * Adds two new HistoryTopic entries to the 1800s era covering the
 * tenant uprising in upstate New York (Anti-Rent War, 1839-1845) and
 * the unemployed-workers march of 1894 (Coxey's Army). Also enriches
 * the existing Sedition Act topic with the comprehensive name list
 * surfaced in this session's research.
 *
 * Idempotent — looks up topics by era + title and updates rather than
 * duplicates.
 */
final class AddHistoryUpdates extends Command {
    protected $signature = 'archive:add-history-updates';
    protected $description = 'Add Anti-Rent War + Coxey\'s Army history topics; enrich Sedition Act topic';

    public function handle(): int {
        // --- Enrich existing Sedition Act topic ---
        $era1700s = HistoryEra::where('slug', '1700s')->first();
        if ($era1700s) {
            $sedition = HistoryTopic::where('history_era_id', $era1700s->id)
                ->where('title', 'The Sedition Act')->first();
            if ($sedition) {
                $sedition->update([
                    'summary' => 'Just seven years after the ratification of the Bill of Rights, President John Adams signed the Alien and Sedition Acts into law. The Sedition Act made it a federal crime to publish "false, scandalous, and malicious" statements against the government or its officials. At least 25 people were arrested and 10 convicted — nearly all of them newspaper editors and political opponents of the Federalist Party. Among those who actually served prison time: Vermont Congressman Matthew Lyon (4 months at Vergennes, 1798-99; re-elected to Congress from his cell); editor Thomas Cooper of the Northumberland Gazette (6 months in Philadelphia, 1800); James Thompson Callender of the Richmond Examiner (9 months, 1800-01); Vermont Gazette editor Anthony Haswell (2 months, 1800); Charles Holt of the New London Bee (3 months, 1800); Boston Independent Chronicle bookkeeper Abijah Adams (30 days, 1799, prosecuted by Massachusetts state authorities); Dedham liberty-pole-raiser David Brown (held over two years for inability to pay his fine); New York Argus printer David Frothingham (died in the Bridewell jail, 1800); New Jersey laborer Luther Baldwin (jailed for a tavern wisecrack about Adams, 1799); and Boston Constitutional Telegraphe editor John S. Lillie (3 months, 1802). The Act expired in 1801 under Thomas Jefferson, who pardoned all those convicted, but its precedent — that the federal government could imprison its critics — would echo through American history.',
                ]);
                $this->info('Updated topic: The Sedition Act');
            }
        }

        // --- Add new topics to 1800s era ---
        $era1800s = HistoryEra::where('slug', '1800s')->first();
        if (! $era1800s) {
            $this->error('1800s era not found — cannot add Anti-Rent War / Coxey\'s Army topics.');

            return self::FAILURE;
        }

        $newTopics = [
            [
                'title'         => 'The Anti-Rent War',
                'date_label'    => '1839 – 1845',
                'summary'       => 'In the Hudson Valley of New York, tenant farmers rose up against the patroon system — a Dutch feudal-style land tenure that bound tens of thousands of farmers to the Van Rensselaer, Livingston, and other manor lords with perpetual leases collected in livestock, labor, and cash. Disguised in calico-cloth masks and sheepskin face paint as "Calico Indians," the tenants resisted sheriffs serving distress warrants, intercepted rent-collection parties, and built a region-wide insurgency across Columbia, Rensselaer, Albany, and Delaware Counties. On August 7, 1845 at the Moses Earle farm in Andes, Delaware County, hundreds of Calico Indians surrounded and shot dead Undersheriff Osman N. Steele, who had ridden out to enforce a rent-distress sale. Four leaders — physician Smith A. Boughton ("Big Thunder"), and Calico Indians John Van Steenburgh, Edward O\'Connor, and the elderly tenant Moses Earle himself — were tried at Hudson and Delhi in 1845 before Justices John W. Edmonds and Amasa J. Parker. Boughton received life for robbery (taking a sheriff\'s rent paper); Van Steenburgh and O\'Connor were sentenced to be hanged for Steele\'s murder; Earle was convicted of manslaughter. All four served at Clinton State Prison at Dannemora. After Anti-Rent voting blocs delivered the 1846 gubernatorial election to John Young, the new governor pardoned every Anti-Rent prisoner in 1847. The patroon leasehold system was effectively dismantled by the new state constitution adopted that same year.',
                'bg_class'      => 'vbg-1800',
                'caption_era'   => '1845',
                'caption_label' => 'The Anti-Rent War',
                'sort_order'    => 3,
            ],
            [
                'title'         => 'Coxey\'s Army',
                'date_label'    => '1894',
                'summary'       => 'In the depths of the depression that followed the Panic of 1893, Ohio quarry owner Jacob S. Coxey organized the "Commonweal of Christ" — popularly known as Coxey\'s Army — and led roughly 500 unemployed men on foot from Massillon, Ohio to Washington, D.C., starting March 25, 1894. They demanded a federal $500 million public-works program to build good roads, funded by non-interest-bearing currency. It was the first protest march on Washington in American history. Parallel "Industrial Armies" set out from the West Coast — most notably Charles T. Kelly\'s 1,500-man column from San Francisco (Jack London was among its members) and Lewis C. Fry\'s 700 men from Los Angeles, who repeatedly clashed with the Southern Pacific Railroad over commandeered freight trains. Coxey\'s march reached the Capitol on May 1, 1894. As Coxey, chief lieutenant Carl Browne, and Philadelphia delegate Christopher Columbus Jones attempted to deliver their "petition in boots" from the Capitol steps, all three were arrested by the Capitol Police on the spurious charge of carrying banners and walking on Capitol grass. Convicted in D.C. police court before Judge Thomas Miller on May 8, sentenced May 21 to 20 days in the District jail, and released June 10, 1894. The arrests provoked national outrage and made the right to demonstrate on the Capitol grounds a pivotal civil-liberties question. Fifty years later, Congress invited Coxey to deliver his unread 1894 speech from the same Capitol steps.',
                'bg_class'      => 'vbg-1800',
                'caption_era'   => '1894',
                'caption_label' => 'Coxey\'s Army',
                'sort_order'    => 4,
            ],
        ];

        foreach ($newTopics as $payload) {
            $existing = HistoryTopic::where('history_era_id', $era1800s->id)
                ->where('title', $payload['title'])->first();
            if ($existing) {
                $existing->update($payload);
                $this->info('Updated topic: '.$payload['title']);
            } else {
                $existing = $era1800s->topics()->create($payload);
                $this->info('Added topic: '.$payload['title']);
            }
        }

        return self::SUCCESS;
    }
}
