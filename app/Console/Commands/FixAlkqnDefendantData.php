<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects ALKQN Greensboro RICO defendant records (PR #257) using DOJ
 * press releases, FBI Charlotte field-office releases, and the Fourth
 * Circuit opinion in No. 13-4630 (2015). Original data had several
 * fabrications/errors:
 *
 *   - "Russell Lloyd Cornell / King Reckless" does not exist — conflation
 *     with Russell Lloyd Kilfoil, "King Peaceful" (already a separate row)
 *   - Russell Kilfoil's sentence was 15 yr, not 24
 *   - Ernesto Wilson was an *associate* (not member), sentenced to 17 yr
 *   - Randolph Kilfoil, Samuel Velasquez, Irvin Vasquez — acquitted Nov 21, 2012
 *   - Carlos Coleman — charges dismissed
 *   - Sentencing dates were Aug 13/14/15, 2013 (not Dec 2012)
 *   - Yates pleaded Apr 22, 2013; Williams pleaded Oct 3, 2012
 */
final class FixAlkqnDefendantData extends Command {
    protected $signature = 'archive:fix-alkqn-defendant-data';
    protected $description = 'Correct ALKQN Greensboro RICO defendant data';

    public function handle(): int {
        // 1. Delete the fictional "Russell Lloyd Cornell / King Reckless" row
        $fictional = Prisoner::where('name', 'Russell Lloyd Cornell')->first();
        if ($fictional) {
            $fictional->cases()->delete();
            $fictional->delete();
            $this->info('Deleted fictional row: Russell Lloyd Cornell.');
        }

        // 2. Jorge Cornell — sentence correct (28 yr); fix conviction date.
        $this->updateCase('Jorge Peter Cornell', [
            'convicted' => 'Yes — convicted at trial Nov 21, 2012; sentenced Aug 14, 2013',
            'sentence' => '28 years (336 months) federal prison',
        ]);
        $this->updateDescription('Jorge Peter Cornell',
            'Founder and "Inca" (leader) of the Greensboro, North Carolina chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 and convicted at trial November 21, 2012 on federal RICO conspiracy and violent crimes in aid of racketeering (VICAR) charges, including conspiracy to murder rival gang members. Sentenced August 14, 2013 to 28 years in federal prison. The Fourth Circuit upheld the conviction in 2015 (No. 13-4630).'
        );

        // 3. Russell Kilfoil ("King Peaceful") — sentence 15 yr, not 24; conviction 2012; Cornell's half-brother
        $this->updateCase('Russell Kilfoil', [
            'convicted' => 'Yes — convicted at trial Nov 21, 2012; sentenced Aug 14, 2013',
            'sentence' => '15 years federal prison',
        ]);
        $this->updateDescription('Russell Kilfoil',
            'High-ranking member ("King Peaceful") of the Greensboro chapter of the Almighty Latin King & Queen Nation and half-brother of chapter leader Jorge Cornell. Indicted December 2011 and convicted at trial November 21, 2012 on federal RICO conspiracy and VICAR charges. Sentenced August 14, 2013 to 15 years in federal prison.'
        );

        // 4. Ernesto Wilson — associate (not member), 17 yr (not 24)
        $this->updateCase('Ernesto Wilson', [
            'convicted' => 'Yes — convicted at trial Nov 21, 2012; sentenced Aug 13, 2013',
            'sentence' => '17 years federal prison',
        ]);
        $this->updateDescription('Ernesto Wilson',
            'Charged as an *associate* (not a formal member) of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 and convicted at trial November 21, 2012 on federal RICO conspiracy and VICAR charges. Sentenced August 13, 2013 to 17 years in federal prison.'
        );

        // 5. Randolph Kilfoil — ACQUITTED at trial
        $this->updateCase('Randolph Kilfoil', [
            'convicted' => 'No — acquitted Nov 21, 2012',
            'sentence' => null,
            'release_date' => '2012-11-21',
        ]);
        $this->updateStatus('Randolph Kilfoil', inCustody: false, released: true);
        $this->updateDescription('Randolph Kilfoil',
            'Member ("King Paul") of the Greensboro chapter of the Almighty Latin King & Queen Nation and half-brother of co-defendant Russell Kilfoil and Jorge Cornell. Indicted December 2011 in the federal RICO case; *acquitted at trial November 21, 2012*.'
        );

        // 6. Samuel Velasquez — ACQUITTED, not plea
        $this->updateCase('Samuel Velasquez', [
            'convicted' => 'No — acquitted Nov 21, 2012',
            'sentence' => null,
            'release_date' => '2012-11-21',
        ]);
        $this->updateStatus('Samuel Velasquez', inCustody: false, released: true);
        $this->updateDescription('Samuel Velasquez',
            'Charged as a member ("King Hype") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO case against the Greensboro Latin Kings; *acquitted at trial November 21, 2012*.'
        );

        // 7. Irvin Vasquez — ACQUITTED, not plea
        $this->updateCase('Irvin Vasquez', [
            'convicted' => 'No — acquitted Nov 21, 2012',
            'sentence' => null,
            'release_date' => '2012-11-21',
        ]);
        $this->updateStatus('Irvin Vasquez', inCustody: false, released: true);
        $this->updateDescription('Irvin Vasquez',
            'Charged as a member ("King Dice") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO case; *acquitted at trial November 21, 2012*.'
        );

        // 8. Carlos Coleman — charges DISMISSED, not plea
        $this->updateCase('Carlos Coleman', [
            'convicted' => 'No — federal charges dismissed by Judge Beaty',
            'sentence' => null,
            'release_date' => '2012-11-21',
            'institution_name' => 'Federal Bureau of Prisons',
        ]);
        $this->updateStatus('Carlos Coleman', inCustody: false, released: true);
        $this->updateDescription('Carlos Coleman',
            'Charged as a member ("King Spanky") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO case; *federal charges dismissed* at trial by U.S. District Judge James A. Beaty Jr. NC DOC inmate #1204107 reflects unrelated state custody, not this case.'
        );

        // 9. Jason Yates — plea Apr 22, 2013; sentenced Aug 15, 2013 (length unknown)
        $this->updateCase('Jason Yates', [
            'convicted' => 'Yes — pleaded guilty Apr 22, 2013; sentenced Aug 15, 2013',
            'sentence' => null,
        ]);
        $this->updateDescription('Jason Yates',
            'Member ("King Squirrel") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO prosecution; pleaded guilty to RICO conspiracy on April 22, 2013, and sentenced August 15, 2013. Sentence length not publicly reported.'
        );

        // 10. Wesley Williams — plea Oct 3, 2012; sentenced Aug 13, 2013 (length unknown)
        $this->updateCase('Wesley Williams', [
            'convicted' => 'Yes — pleaded guilty Oct 3, 2012; sentenced Aug 13, 2013',
            'sentence' => null,
        ]);
        $this->updateDescription('Wesley Williams',
            'Member ("King Bam") of the Greensboro chapter of the Almighty Latin King & Queen Nation. Indicted December 2011 in the federal RICO case; pleaded guilty to RICO conspiracy on October 3, 2012, and sentenced August 13, 2013. Sentence length not publicly reported.'
        );

        $this->info("\nDone.");

        return self::SUCCESS;
    }

    private function updateCase(string $name, array $caseFields): void {
        $prisoner = Prisoner::where('name', $name)->first();
        if (! $prisoner) {
            $this->warn("Prisoner not found: {$name}");

            return;
        }
        $case = $prisoner->cases()->first();
        if (! $case) {
            $this->warn("No case found for: {$name}");

            return;
        }
        foreach ($caseFields as $k => $v) {
            $case->{$k} = $v;
        }
        $case->save();
        $this->info("Updated case for: {$name}");
    }

    private function updateStatus(string $name, bool $inCustody, bool $released): void {
        $prisoner = Prisoner::where('name', $name)->first();
        if (! $prisoner) {
            return;
        }
        $prisoner->in_custody = $inCustody;
        $prisoner->released = $released;
        $prisoner->save();
    }

    private function updateDescription(string $name, string $description): void {
        $prisoner = Prisoner::where('name', $name)->first();
        if (! $prisoner) {
            return;
        }
        $prisoner->description = $description;
        $prisoner->save();
    }
}
