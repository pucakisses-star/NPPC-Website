<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political prisoners of Fries's Rebellion (1798–1800) — the "House Tax
 * Rebellion" / "Hot Water War" in which Pennsylvania-German farmers of Bucks,
 * Northampton and Montgomery counties resisted the federal Direct Tax of 1798.
 * John Fries (the leader) is already in the database; this command adds the
 * others prosecuted or imprisoned with him. Idempotent (skips by name).
 *
 *   Tried for treason, sentenced to hang, pardoned by President Adams
 *   (May 21, 1800) alongside Fries:
 *     Frederick Heaney (Heany), John Getman (Gettman)
 *   Imprisoned participants who died of yellow fever in custody before trial:
 *     Michael Schmoyer, David Schaeffer, Phillip Desch
 *
 * The three condemned men were Fries, Heaney and Getman (W.W.H. Davis, 1899;
 * Paul Douglas Newman, 2004). Prosecuted by U.S. Attorney William Rawle in the
 * U.S. Circuit Court for the District of Pennsylvania; the 1800 treason retrials
 * were held before Justice Samuel Chase and District Judge Richard Peters.
 */
class AddFriesRebellion extends Command
{
    protected $signature = 'prisoners:add-fries-rebellion';

    protected $description = "Add political prisoners of Fries's Rebellion (1798–1800) tried or jailed alongside John Fries";

    public function handle(): int
    {
        $walnut = Institution::firstOrCreate(['name' => 'Walnut Street Prison'], ['city' => 'Philadelphia', 'state' => 'Pennsylvania']);
        $norristown = Institution::firstOrCreate(['name' => 'Norristown Jail'], ['city' => 'Norristown', 'state' => 'Pennsylvania']);

        $people = [
            [
                'name' => 'Frederick Heaney', 'first' => 'Frederick', 'last' => 'Heaney', 'aka' => 'Frederick Heany',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s', 'birth_year' => 1769,
                'ideologies' => ['Tax resistance', 'Anti-Federalism'],
                'affiliation' => ["Fries's Rebellion"],
                'bio' => 'Frederick Heaney (also spelled Heany or Hainly) was a Pennsylvania German tailor from Milford Township, Bucks County, and — next to John Fries himself — one of the two most active leaders of Fries’s Rebellion, the 1798–1800 uprising of eastern-Pennsylvania farmers against the federal direct ("house") tax. Tried for treason in the U.S. Circuit Court at Philadelphia in 1800 before Justice Samuel Chase, he was convicted of levying war against the United States and, with Fries and John Getman, sentenced to be hanged. President John Adams pardoned all three on May 21, 1800, two days before the scheduled execution. Heaney returned home, later serving as a justice of the peace and captain of a volunteer militia company, and died at an advanced age in Plainfield, Northampton County.',
                'charges' => 'Treason (levying war against the United States) — for his leading role in the armed resistance to the 1798 federal house tax.',
                'convicted' => 'Yes — convicted of treason in the U.S. Circuit Court for Pennsylvania (1800).',
                'sentence' => 'Death by hanging; pardoned by President John Adams on May 21, 1800.',
                'prosecutor' => 'William Rawle (U.S. Attorney)',
                'judge' => 'Justice Samuel Chase, with District Judge Richard Peters',
                'release_date' => '1800-05-21',
                'institution_id' => $walnut->id,
                'released' => true,
            ],
            [
                'name' => 'John Getman', 'first' => 'John', 'last' => 'Getman', 'aka' => 'John Gettman',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s',
                'ideologies' => ['Tax resistance', 'Anti-Federalism'],
                'affiliation' => ["Fries's Rebellion"],
                'bio' => 'John Getman (also spelled Gettman) was a Pennsylvania German tailor of Milford Township, Bucks County, who lived within about half a mile of John Fries and was, with Fries and Frederick Heaney, one of the most active instigators of Fries’s Rebellion against the federal house tax of 1798. He was tried for treason in the U.S. Circuit Court at Philadelphia in 1800, convicted of levying war against the United States, and sentenced to hang alongside Fries and Heaney. President John Adams pardoned all three on May 21, 1800.',
                'charges' => 'Treason (levying war against the United States) — for his leading part in the armed resistance to the 1798 federal house tax.',
                'convicted' => 'Yes — convicted of treason in the U.S. Circuit Court for Pennsylvania (1800).',
                'sentence' => 'Death by hanging; pardoned by President John Adams on May 21, 1800.',
                'prosecutor' => 'William Rawle (U.S. Attorney)',
                'judge' => 'Justice Samuel Chase, with District Judge Richard Peters',
                'release_date' => '1800-05-21',
                'institution_id' => $walnut->id,
                'released' => true,
            ],
            [
                'name' => 'Michael Schmoyer', 'first' => 'Michael', 'last' => 'Schmoyer', 'aka' => 'Michael Schmeyer',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s',
                'ideologies' => ['Tax resistance'],
                'affiliation' => ["Fries's Rebellion"],
                'bio' => 'Michael Schmoyer was a Pennsylvania German participant in Fries’s Rebellion from the Macungie (Millerstown) area of what was then Northampton County. Arrested for his part in the resistance to the 1798 federal direct tax, he was jailed to await trial and, before his case was concluded, contracted yellow fever and died in custody — one of at least three Fries’s Rebellion prisoners who died of the fever in jail rather than living to be tried or pardoned.',
                'charges' => 'Participation in the insurrection against the 1798 federal direct tax (resisting the house-tax assessment).',
                'convicted' => 'Held to await trial; died in custody before his case was resolved.',
                'sentence' => 'Died of yellow fever in prison while awaiting trial.',
                'institution_id' => $norristown->id,
                'released' => false,
            ],
            [
                'name' => 'David Schaeffer', 'first' => 'David', 'last' => 'Schaeffer', 'aka' => 'David Schaffer',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s',
                'ideologies' => ['Tax resistance'],
                'affiliation' => ["Fries's Rebellion"],
                'bio' => 'David Schaeffer was a Pennsylvania German of Macungie (Millerstown) and a local leader of the anti-house-tax movement in Fries’s Rebellion; his household is tied by local tradition to the rebellion’s "Hot Water War" nickname, from the story that boiling water was thrown on the federal tax assessors. Arrested for his part in the resistance to the 1798 direct tax, he was imprisoned and, like Michael Schmoyer, died of yellow fever in custody before his case was resolved.',
                'charges' => 'Participation in the insurrection against the 1798 federal direct tax.',
                'convicted' => 'Held in custody; died before his case was resolved.',
                'sentence' => 'Died of yellow fever in prison.',
                'institution_id' => $norristown->id,
                'released' => false,
            ],
            [
                'name' => 'Phillip Desch', 'first' => 'Phillip', 'last' => 'Desch', 'aka' => 'Philip Desh',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s',
                'ideologies' => ['Tax resistance'],
                'affiliation' => ["Fries's Rebellion"],
                'bio' => 'Phillip Desch was a Pennsylvania German participant in Fries’s Rebellion who was jailed for resisting the 1798 federal direct tax. Grouped in local histories with David Schaeffer and Michael Schmoyer, he was among the prisoners who died of yellow fever in custody before coming to trial. Little else about him is documented.',
                'charges' => 'Participation in the insurrection against the 1798 federal direct tax.',
                'convicted' => 'Held in custody; died before coming to trial.',
                'sentence' => 'Died of yellow fever in prison.',
                'institution_id' => $norristown->id,
                'released' => false,
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                if (Prisoner::where('name', $p['name'])->exists()) {
                    $this->warn('Skipped (already exists): '.$p['name']);

                    continue;
                }

                $prisoner = Prisoner::create([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?? null,
                    'last_name' => $p['last'] ?? null,
                    'aka' => $p['aka'] ?? null,
                    'description' => $p['bio'],
                    'gender' => $p['gender'] ?? null,
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'] ?? null,
                    'ideologies' => $p['ideologies'] ?? [],
                    'affiliation' => $p['affiliation'] ?? [],
                    'in_custody' => false,
                    'released' => $p['released'] ?? true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);

                // Year-only birth dates are stored with year precision so the
                // site shows "1769", never a fabricated "January 1, 1769".
                if (! empty($p['birth_year'])) {
                    $prisoner->setPartialDate('birthdate', (int) $p['birth_year']);
                    $prisoner->save();
                }

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['institution_id'] ?? null,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                    'prosecutor' => $p['prosecutor'] ?? null,
                    'judge' => $p['judge'] ?? null,
                    'release_date' => $p['release_date'] ?? null,
                ]);

                $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        return self::SUCCESS;
    }
}
