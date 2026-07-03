<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * One-off backfill: the AntifaWatch 600-663 import created these prisoners via
 * prisoner:add, but any that already existed were skipped entirely (name-duplicate
 * guard), so they may be missing the case we compiled. This command adds the case
 * ONLY when the prisoner currently has zero cases -- it is safe and idempotent, and
 * never touches a prisoner that already has case records.
 */
final class BackfillAntifawatchCases extends Command
{
    protected $signature = 'prisoner:backfill-antifawatch-cases {--dry-run : Report what would change without writing}';

    protected $description = 'Attach missing cases to AntifaWatch 600-663 prisoners that have none';

    /** @var array<int, array{0:string,1:string,2:string}> [name, charges, sentence] */
    private array $rows = [
            ['Anthony Hayne', '\'Cleveland 5\' 2012 FBI-sting bridge bomb plot', '72 months federal prison'],
            ['Anthony Krohn', 'Felon-in-possession of firearm during June 2020 Madison protests', '60 months federal prison'],
            ['Branden Wolfe', 'Aided arson of Minneapolis Police Third Precinct (2020)', '41 months federal prison'],
            ['Brandon Baxter', '\'Cleveland 5\' 2012 FBI-sting bridge bomb plot', '117 months federal prison'],
            ['Bruce Thompson', 'Federal arson of a Gainesville GA police car (June 2020)', '14 months federal prison'],
            ['Bryce Williams', 'Conspiracy to commit arson, Minneapolis Police Third Precinct (2020)', '27 months federal prison'],
            ['Channel Lewis', 'Federal conspiracy; lookout/driver in CVS pharmacy burglary during June 2020 Louisville unrest', '7 months federal prison'],
            ['Charles Pittman', 'Arson of Fayetteville Market House during May 30 2020 protest', '5 years federal prison'],
            ['Colinford Mattis', 'Drove van in NYPD-vehicle Molotov firebombing, Brooklyn (2020); attorney', '12 months and a day federal prison'],
            ['Connor Stevens', '\'Cleveland 5\' 2012 FBI-sting bridge bomb plot', '97 months federal prison'],
            ['Courtland Renford', 'Buffalo City Hall fire during 2020 protests (federal rioting)', '60 months federal prison'],
            ['Damion Zachary Feller', 'Threw flares into police cruiser and Target during Portland May Day 2017 riot; riot/arson', '73 months prison'],
            ['Dashun Martin', 'Federal arson of a Gainesville GA police car (June 2020)', '17 months federal prison'],
            ['David Elmakayes', 'Blew up an ATM with explosive during 2020 Philadelphia unrest + felon-in-possession', '15 years federal prison'],
            ['Delveccho Waller', 'Federal arson of a Gainesville GA police car (June 2020); watchlist spelled \'Deveccho\'', '21 months federal prison'],
            ['Devarian Haynes', 'Federal civil disorder; burning of a Las Vegas police SUV (2020)', '2 years federal prison'],
            ['Deyanna Davis', 'Drove SUV into a state trooper during Buffalo protest (2020)', '30 months state prison'],
            ['Douglas Wright', '\'Cleveland 5\' 2012 FBI-sting bridge bomb plot', '138 months federal prison'],
            ['Dylan Robinson', 'Aiding/abetting arson of Minneapolis Police Third Precinct (2020)', '48 months federal prison'],
            ['Earlja Dudley', 'Attempted arson of a Trenton police vehicle (2020, federal civil disorder)', '30 months federal prison'],
            ['Edgar Samaniego', 'Shot and paralyzed a Las Vegas police officer during June 2020 protest; attempted murder', '20-50 years state prison'],
            ['Fornandous Henderson', 'Molotov arson of Dakota County government building (2020)', '78 months federal prison'],
            ['Gage Halupowski', 'Baton assault at June 2019 Portland protest; 2nd-degree assault', '70 months (5 yr 10 mo) prison'],
            ['Jackson Patton', 'Federal civil disorder; role in burning a Salt Lake City police car (2020)', '24 months federal prison'],
            ['Jesse Clark', 'Aggravated arson of Nashville Metro Courthouse (May 30 2020)', '12 years TN state prison'],
            ['Jesse Smallwood', 'Federal arson of a Gainesville GA police car (June 2020)', '21 months federal prison'],
            ['Jose Felan', 'Arson of multiple St. Paul buildings during 2020 unrest', '78 months federal prison'],
            ['Joshua Stafford', '\'Cleveland 5\' 2012 FBI-sting bridge bomb plot', '120 months federal prison'],
            ['Judah Bailey', 'Federal arson of a Gainesville GA police car (June 2020)', '21 months federal prison'],
            ['Kyle Olson', 'Felon-in-possession of firearm during May 31 2020 Madison unrest', '27 months federal prison'],
            ['Linwood Kaine', 'Obstructing legal process at March 2017 St. Paul counter-protest', '4 days jail + probation'],
            ['Lore-Elisabeth Blumenthal', 'Arson of two Philadelphia police vehicles (2020); identified via Etsy purchase', '30 months federal prison'],
            ['Loren Reed', '18 USC 844(e) threat to burn govt buildings, Page AZ (2020); held ~11 months federal pretrial detention without bail', '~11 months pretrial detention; non-cooperation plea'],
            ['Margaret Channon', 'Federal arson; set five Seattle police vehicles on fire (May 30 2020)', '5 years federal prison'],
            ['Matthew Rupert', 'Arson of Minneapolis cellphone store + destructive devices (2020)', '105 months federal prison'],
            ['Melquan Barnett', 'Set fire to an Erie coffee shop during May 30 2020 protest', '5 years federal prison'],
            ['Miguel Ramos', 'Set a Rochester police car on fire (2020); riot + arson', '16 months prison'],
            ['Montez Lee', 'Arson of Minneapolis pawn shop (2020); a man died in the fire', '120 months federal prison'],
            ['Nicholas Lucia', 'Threw an explosive device at police during May 30 2020 Pittsburgh protest; federal civil disorder', '24 months federal prison'],
            ['Rakem Balogun', 'FBI-targeted over Facebook posts; felon-in-possession; ~6 months pretrial detention then indictment DISMISSED', '~6 months pretrial detention; case dismissed'],
            ['Ricardo Densmore', 'Federal civil disorder; burning of a Las Vegas police SUV (2020)', '2 years federal prison'],
            ['Richard Rubalcava', 'Set fires to Raleigh businesses during May 30 2020 riot; federal arson', '~84 months federal prison'],
            ['Robert Majure', 'Doused officers with lubricant/glitter, Aug 2018 Portland counter-protest; harassment', '5 days jail + probation'],
            ['Samantha Shader', 'Threw Molotov at occupied NYPD van near Brooklyn Museum (2020)', '72 months federal prison'],
            ['Shamar Betts', 'Facebook flyer that incited the May 31 2020 Champaign mall riot; federal Anti-Riot Act', '48 months federal prison'],
            ['Tandre Buchanan', 'Hobbs Act robbery of a Cleveland shop during May 30 2020 unrest', '4 years federal prison'],
            ['Timothy O\'Donnell', 'Set a Chicago police SUV on fire during May 30 2020 unrest; federal civil disorder', '34 months federal prison'],
            ['Tyree Walker', 'Federal civil disorder; burning of a Las Vegas police SUV (2020)', '2 years federal prison'],
            ['Urooj Rahman', 'Threw Molotov cocktail at NYPD vehicle, Brooklyn (2020); attorney', '15 months federal prison'],
            ['Wesley Somers', 'Federal arson of Nashville Historic Courthouse (May 30 2020)', '5 years federal prison'],
        ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $added = $skippedHasCases = $notFound = 0;

        foreach ($this->rows as [$name, $charges, $sentence]) {
            $p = Prisoner::where('name', $name)->first();
            if (! $p) {
                $this->warn("NOT FOUND: {$name} (no prisoner with this exact name)");
                $notFound++;
                continue;
            }
            $count = $p->cases()->count();
            if ($count > 0) {
                $this->line("skip: {$name} already has {$count} case(s)");
                $skippedHasCases++;
                continue;
            }
            if ($dry) {
                $this->line("[dry-run] would add case to {$name}: {$charges}");
                $added++;
                continue;
            }
            PrisonerCase::create([
                'prisoner_id'    => $p->id,
                'institution_id' => null,
                'charges'        => $charges,
                'sentence'       => $sentence,
            ]);
            $this->info("added case -> {$name} ({$sentence})");
            $added++;
        }

        $verb = $dry ? 'would add' : 'added';
        $this->info("\nDone. {$verb} {$added} case(s); skipped {$skippedHasCases} that already had cases; {$notFound} not found.");

        return self::SUCCESS;
    }
}
