<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillMissingCases extends Command
{
    protected $signature = 'prisoners:backfill-missing-cases';
    protected $description = 'Attach a researched case to every in-custody prisoner that currently has no PrisonerCase records.';

    public function handle(): int
    {
        // Pre-create the institutions we'll reference. firstOrCreate by name only,
        // so we don't disturb existing rows.
        $bopVaried       = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons (location varied)']);
        $bopFederal      = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons (federal custody)']);
        $sciMahanoy      = Institution::firstOrCreate(['name' => 'SCI Mahanoy'], ['city' => 'Frackville', 'state' => 'Pennsylvania']);
        $sciPhoenix      = Institution::firstOrCreate(['name' => 'SCI Phoenix'], ['city' => 'Collegeville', 'state' => 'Pennsylvania']);
        $sciHuntingdon   = Institution::firstOrCreate(['name' => 'SCI Huntingdon'], ['city' => 'Huntingdon', 'state' => 'Pennsylvania']);
        $faribault       = Institution::firstOrCreate(['name' => 'MCF-Faribault'], ['city' => 'Faribault', 'state' => 'Minnesota']);
        $stillwater      = Institution::firstOrCreate(['name' => 'MCF-Stillwater'], ['city' => 'Bayport', 'state' => 'Minnesota']);
        $jacksonMI       = Institution::firstOrCreate(['name' => 'Michigan Department of Corrections – Cooper Street'], ['city' => 'Jackson', 'state' => 'Michigan']);
        $atlantaUSP      = Institution::firstOrCreate(['name' => 'USP Atlanta'], ['city' => 'Atlanta', 'state' => 'Georgia']);
        $coloradoSupermax = Institution::firstOrCreate(['name' => 'ADX Florence'], ['city' => 'Florence', 'state' => 'Colorado']);
        $allenwood       = Institution::firstOrCreate(['name' => 'USP Allenwood'], ['city' => 'White Deer', 'state' => 'Pennsylvania']);
        $lewisburg       = Institution::firstOrCreate(['name' => 'USP Lewisburg'], ['city' => 'Lewisburg', 'state' => 'Pennsylvania']);
        $coleman         = Institution::firstOrCreate(['name' => 'FCI Coleman'], ['city' => 'Coleman', 'state' => 'Florida']);
        $yazooCity       = Institution::firstOrCreate(['name' => 'FCI Yazoo City'], ['city' => 'Yazoo City', 'state' => 'Mississippi']);
        $carswellFMC     = Institution::firstOrCreate(['name' => 'Federal Medical Center, Carswell'], ['city' => 'Fort Worth', 'state' => 'Texas']);
        $shu             = Institution::firstOrCreate(['name' => 'Communications Management Unit'], ['state' => 'Indiana']);
        $allenwoodLow    = Institution::firstOrCreate(['name' => 'FCI Allenwood Low'], ['city' => 'White Deer', 'state' => 'Pennsylvania']);
        $oregonSP        = Institution::firstOrCreate(['name' => 'Oregon State Penitentiary'], ['city' => 'Salem', 'state' => 'Oregon']);
        $virginiaDOC     = Institution::firstOrCreate(['name' => 'Virginia Department of Corrections (location varied)']);
        $alabamaDOC      = Institution::firstOrCreate(['name' => 'Holman Correctional Facility'], ['city' => 'Atmore', 'state' => 'Alabama']);
        $indianaDOC      = Institution::firstOrCreate(['name' => 'Pendleton Correctional Facility'], ['city' => 'Pendleton', 'state' => 'Indiana']);
        $kernValley      = Institution::firstOrCreate(['name' => 'Kern Valley State Prison'], ['city' => 'Delano', 'state' => 'California']);
        $californiaDOC   = Institution::firstOrCreate(['name' => 'California Department of Corrections (location varied)']);
        $sdStatePen      = Institution::firstOrCreate(['name' => 'South Dakota State Penitentiary'], ['city' => 'Sioux Falls', 'state' => 'South Dakota']);
        $georgiaDOC      = Institution::firstOrCreate(['name' => 'Georgia Department of Corrections (location varied)']);
        $cookCountyJail  = Institution::firstOrCreate(['name' => 'Cook County Jail'], ['city' => 'Chicago', 'state' => 'Illinois']);
        $greeneCountyAR  = Institution::firstOrCreate(['name' => 'Greene County Detention Center'], ['city' => 'Paragould', 'state' => 'Arkansas']);
        $scDOC           = Institution::firstOrCreate(['name' => 'South Carolina Department of Corrections (location varied)']);
        $albanyCounty    = Institution::firstOrCreate(['name' => 'Albany County Jail'], ['city' => 'Albany', 'state' => 'New York']);
        $iceTexasFacility = Institution::firstOrCreate(['name' => 'ICE Detention Facility, Texas'], ['state' => 'Texas']);
        $iceNySyracuse   = Institution::firstOrCreate(['name' => 'ICE Detention (New York / self-removal)'], ['state' => 'New York']);
        $iceWashington   = Institution::firstOrCreate(['name' => 'ICE Detention (Washington State)'], ['state' => 'Washington']);
        $silverwater     = Institution::firstOrCreate(['name' => 'Silverwater Correctional Complex'], ['city' => 'Sydney', 'state' => 'New South Wales, Australia']);
        $cubaExile       = Institution::firstOrCreate(['name' => 'Republic of Cuba (political asylum)']);
        $albanyNYstate   = Institution::firstOrCreate(['name' => 'Auburn Correctional Facility'], ['city' => 'Auburn', 'state' => 'New York']);
        $oakdaleLA       = Institution::firstOrCreate(['name' => 'FCI Oakdale'], ['city' => 'Oakdale', 'state' => 'Louisiana']);
        $sheridanOR      = Institution::firstOrCreate(['name' => 'FCI Sheridan'], ['city' => 'Sheridan', 'state' => 'Oregon']);
        $cumberlandMD    = Institution::firstOrCreate(['name' => 'FCI Cumberland'], ['city' => 'Cumberland', 'state' => 'Maryland']);
        $threeRiversTX   = Institution::firstOrCreate(['name' => 'FCI Three Rivers'], ['city' => 'Three Rivers', 'state' => 'Texas']);
        $atwaterCA       = Institution::firstOrCreate(['name' => 'USP Atwater'], ['city' => 'Atwater', 'state' => 'California']);
        $virginIslandsBOP = Institution::firstOrCreate(['name' => 'Federal custody (U.S. Virgin Islands prosecution; transferred BOP)']);

        $cases = [
            // ─── Federal post-Floyd / BLM uprising defendants ───
            ['Margaret Aislinn Channon',        $bopFederal->id,       'Arson of five Seattle Police Department vehicles during George Floyd uprising protests, May 30 2020', '2022-05-01', 'Yes — guilty plea',         '5 years federal prison + 36 months supervised release'],
            ['Caleb Freestone',                 $yazooCity->id,        'FACE Act / vandalism of South Broward Pregnancy Help Center, Life Choice Pregnancy Center, and Heartbeat of Miami Pregnancy Help Medical Clinic, post-Dobbs (2022)', '2024-09-15', 'Yes — guilty plea',  '1 year and 1 day federal prison'],
            ['José Felan',                      $coleman->id,          'Federal arson and conspiracy charges, fires set during George Floyd uprising, Twin Cities, May 28 2020', '2022-10-18',       'Yes — guilty plea',         '6.5 years federal prison + 3 years supervised release'],
            ['Dylan Robinson',                  $bopFederal->id,       'Conspiracy to commit arson — burning of Minneapolis 3rd Police Precinct, May 28 2020',          '2020-06-15',     'Yes — guilty plea',         'Multi-year federal sentence'],
            ['David Elmakayes',                 $bopFederal->id,       'Use of an explosive device to damage an ATM and illegal possession of a firearm, Philadelphia, June 2020', '2022-06-15',  'Yes — guilty plea',         '15 years federal prison'],
            ['Matthew Rupert',                  $bopFederal->id,       'Civil disorder, rioting, and arson — Minneapolis, May 2020',                                    '2021-01-15',     'Yes — guilty plea',         'Federal sentence (multi-year)'],
            ['H.P. Nall (Howard Eugene Nall)',  $jacksonMI->id,        'Destruction of a parking meter during a Black Lives Matter protest in Grand Rapids, Michigan',  '2022-02-15',     'Yes',                       '2.5–10 years state prison + restitution exceeding $40,000'],
            ['Matthew White',                   $bopFederal->id,       'Federal arson / property destruction charges connected to George Floyd uprising in Minneapolis, 2020', '2021-03-01', 'Yes — guilty plea',         'Multi-year federal sentence (released to halfway house mid-2025)'],
            ['Montez Lee',                      $bopFederal->id,       'Federal arson — burning of a building during George Floyd uprising protests in Minneapolis, 2020', '2022-01-15',  'Yes — guilty plea',         '10 years federal prison'],
            ['Chase Vladamir Spencer',          $jacksonMI->id,        'Riot and malicious destruction of a building during 2020 protests in downtown Grand Rapids, Michigan', '2020-06-15', null,                       'State pretrial detention / sentencing pending'],
            ['Jack (John Mazurek)',             $georgiaDOC->id,       'First-degree arson — July 1 2023 burning of police motorcycles at an Atlanta police special operations center (Stop Cop City)', '2024-02-15', null, 'Pretrial detention; bond proceedings ongoing'],
            ['John Wade',                       $bopFederal->id,       'Federal riot-related charges connected to George Floyd uprising protests',                       '2020-06-01',     null,                        'Federal sentence (case ongoing per support networks)'],
            ['Ellie Brett',                     $bopFederal->id,       'Conspiracy — arson of multiple United States Postal Service vehicles, metro Atlanta, 2020',     '2021-08-01',     'Yes — guilty plea',         '5 years federal prison + supervised release + restitution'],
            ['Branden Wolfe',                   $bopFederal->id,       'Federal aiding and abetting arson — burning of Minneapolis Police Department 3rd Precinct, May 28 2020', '2021-04-01', 'Yes — guilty plea',     'Multi-year federal sentence + restitution'],
            ['Dawn Jeffrey',                    $greeneCountyAR->id,   'State vandalism / property destruction charges tied to BLM protests in Little Rock, AR; bond revoked June 25 2021', '2021-06-25', null,        'Pretrial detention awaiting trial'],
            ['Shamar N. Betts',                 $bopFederal->id,       'Inciting a riot via a Facebook post calling for protest in Champaign, Illinois after the police murder of George Floyd, May 31 2020', '2021-08-19', 'Yes — guilty plea', '4 years federal prison'],
            ['Brittany Martin',                 $scDOC->id,            'Breach of peace, "high and aggravated" — leading chants and confronting officers during George Floyd protest in South Carolina', '2022-05-01', 'Yes', '4 years state prison (judge\'s "high and aggravated" enhancement)'],
            ['Gage Halupowski',                 $oregonSP->id,         'Second-degree assault — Pioneer Courthouse Square clash, Portland, June 2019',                  '2019-08-01',     'Yes — guilty plea',         '70 months Oregon state prison'],
            ['Alexander Stokes Contompasis',    $albanyNYstate->id,    'Felony assault — January 6, 2021 brawl at New York State Capitol Stop the Steal rally; defended counter-protestor from Proud Boys', '2022-11-17', 'Yes', '20 years New York state prison'],
            ['David Annarelli',                 $virginiaDOC->id,      'Malicious wounding of a law enforcement officer (sheriff\'s deputy shot during a Floyd County, Virginia domestic disturbance call)', null, 'No contest plea', '15 years Virginia state prison'],
            ['Luke O\'Donovan',                 $georgiaDOC->id,       'Aggravated assault — January 1 2013 New Year\'s party stabbing of a group of attackers (queer self-defense), Atlanta', '2014-01-01', 'Yes — plea deal', '2 years state prison + 8 years probation + banishment from Georgia (faced up to 110 years if found guilty)'],

            // ─── Black liberation / Black nationalist political prisoners ───
            ['Mumia Abu-Jamal',                 $sciMahanoy->id,       'First-degree murder of Philadelphia Police Officer Daniel Faulkner, December 9 1981 — death sentence overturned 2011, resentenced to life', '1981-12-09', 'Yes — Pennsylvania state jury verdict, 1982', 'Life in prison without parole (originally death; resentenced 2011)'],
            ['Fred Burton (Muhammad Burton)',   $sciPhoenix->id,       'First-degree murder — alleged 1970 attack on the Cobb Creek Guardhouse, Philadelphia ("Philly 5"); coerced co-defendant testimony', '1970-09-15', 'Yes — Pennsylvania state jury verdict (all-white jury after every Black juror was struck)', 'Life in prison'],
            ['Kojo Bomani Sababu (Grailing Brown)', $allenwood->id,    'Federal armed robbery — attempted bank robbery to fund revolutionary causes, Black Liberation Army',  '1975-11-01',     'Yes — federal jury verdict',  'Life in federal prison'],
            ['Ronald Reed',                     $stillwater->id,       'First-degree murder and conspiracy to commit first-degree murder — 1970 killing of St. Paul police officer; charged 25 years later', '2006-04-01', 'Yes — Minnesota state jury verdict', 'Life in Minnesota state prison'],
            ['Veronza Bowers Jr.',              $atlantaUSP->id,       'Federal felony murder of a U.S. Park Ranger — convicted on the testimony of two government informants who received reduced sentences for unrelated crimes', '1974-04-01', 'Yes — federal jury verdict', 'Life in federal prison'],
            ['Kamau Sadiki (Freddie Hilton)',   $georgiaDOC->id,       'Murder of Atlanta police officer (1971 cold-case prosecution) — charged in 2002 after refusing FBI request to help extradite Assata Shakur', '2003-04-15', 'Yes — Georgia state jury verdict, 2003', 'Life in Georgia state prison'],
            ['Joe-Joe Bowen (Joseph Bowen)',    $sciHuntingdon->id,    'Murder of a Pennsylvania prison warden and deputy warden after retaliation for advocating for Muslim prisoners',         '1973-04-01',     'Yes — Pennsylvania state jury verdict', 'Two consecutive life sentences (in addition to prior life sentence for killing Philadelphia police officer)'],
            ['Omar Askia Ali (Edward Sistrunk)', $sciPhoenix->id,      'First-degree murder — 1970 Philadelphia furniture store robbery; conviction relied on undisclosed police informant whose testimony defense was barred from cross-examining', '1971-01-07', 'Yes — Philadelphia state jury verdict (all-white jury)', 'Life in Pennsylvania state prison'],
            ['Larry Hoover',                    $coloradoSupermax->id, 'Federal racketeering / conspiracy plus 1973 Illinois state murder conviction; concurrent life sentences in connection with the 1978 Pontiac prison uprising', '1995-08-01', 'Yes — federal and Illinois state convictions', 'Life federal + life state (concurrent)'],
            ['Naeem (Christopher Trotter)',     $indianaDOC->id,       'Riot, kidnapping, and assault — leader of the February 1 1985 Pendleton Correctional Facility uprising in response to white-supremacist guard violence (Pendleton 2)', '1985-02-01', 'Yes — Indiana state jury verdict', '142 years state prison (subsequent resentencing litigation ongoing)'],
            ['Balagoon (John C Cole Jr.)',      $indianaDOC->id,       'Riot, kidnapping, and assault — co-leader of the February 1 1985 Pendleton Correctional Facility uprising (Pendleton 2)', '1985-02-01', 'Yes — Indiana state jury verdict', '84 additional years on top of pre-existing sentence'],
            ['Michael Kimble',                  $alabamaDOC->id,       'Murder — fatal shooting of a man who attacked Kimble using racist and homophobic slurs, Alabama 1986', '1987-01-01',  'Yes — Alabama state jury verdict', 'Life in Alabama state prison'],
            ['Kevin Rashid Johnson',            $virginiaDOC->id,      'Murder — convicted 1990; co-founder of the New Afrikan Black Panther Party, prolific prison organizer', '1990-06-01',  'Yes — state jury verdict, 1990', 'Life imprisonment'],

            // ─── Indigenous / Latin-American solidarity ───
            ['John Boy Patton (John Graham)',   $sdStatePen->id,       'Felony murder — 1975 execution-style killing of AIM activist Anna Mae Aquash, conviction based largely on uncorroborated testimony of co-conspirators and informants', '2010-12-10', 'Yes — South Dakota state jury verdict, December 10, 2010', 'Life in state prison'],

            // ─── Whistleblowers / surveillance state ───
            ['Charles Littlejohn',              $bopFederal->id,       'Unauthorized disclosure of confidential federal tax return information — leaked records of wealthiest Americans (incl. Donald Trump) to The New York Times and ProPublica between 2019 and 2020', '2025-04-01', 'Yes — guilty plea, October 2023', '5 years federal prison (statutory maximum) — sentenced January 2025'],

            // ─── Foreign nationals / extradition / detention ───
            ['Daniel Duggan',                   $silverwater->id,      'Awaiting extradition to U.S. on 2017 indictment for conspiracy to defraud the U.S. government, money laundering, and Arms Export Control Act violations (allegedly training Chinese military pilots)', '2022-10-21', 'Indicted 2017; pretrial detention pending extradition', 'Detained pending extradition; held in maximum-security / isolated custody in New South Wales'],
            ['Lelo (Alfredo Juarez)',           $iceWashington->id,    'ICE administrative detention — March 25 2025 warrantless seizure based on a 2018 court deportation order Juarez disputes; lifelong farmworker organizer (Familias Unidas por la Justicia)', '2025-03-25', 'No criminal charges — ICE administrative custody', 'Indefinite ICE detention pending removal proceedings'],
            ['Momodou Taal',                    $iceNySyracuse->id,    'Ordered to surrender to ICE in Syracuse, NY in March 2025 amid Trump-administration campaign targeting pro-Palestinian student activists; Taal\'s lawyers filed suit challenging the executive orders before he ultimately self-deported', '2025-03-15', 'No criminal charges — ICE administrative action', 'Self-deported under threat of detention; litigation ongoing'],
            ['Badar Khan Suri',                 $iceTexasFacility->id, 'ICE administrative detention in March 2025 after student visa revocation; alleged "spreading Hamas propaganda" without criminal charges', '2025-03-15', 'No criminal charges', 'Released by federal judge May 2025; government appeal pending'],

            // ─── Hijack-to-Cuba / exile cases ───
            ['William Lee Brent',               $cubaExile->id,        'TWA Flight 154 hijacking, June 17 1969 — diverted to Havana to escape pretrial proceedings on November 1968 San Francisco gas-station robbery / shootout charge', '1969-06-17', 'Cuban detention 22 months following hijacking; granted asylum', 'Lived in exile in Cuba until death November 4 2006 (pneumonia)'],
            ['Jacob Appelbaum',                 null,                  'No criminal charges in the United States — subject to ongoing DOJ surveillance, repeated border detentions and electronics seizures; relocated to Germany following 2016 misconduct allegations', null, 'No criminal charges filed', 'De facto exile in Germany'],

            // ─── Holy Land Foundation / Muslim political prisoners ───
            ['Patrice Lumumba Ford',            $bopFederal->id,       'Seditious conspiracy and levying war against American and allied forces ("Portland Seven" / alleged attempt to travel to Afghanistan to aid the Taliban after 9/11)',         '2002-10-01',     'Yes — guilty plea',         '18 years federal prison'],

            // ─── Long-term BLA / political prisoners ───
            ['Bill Dunne',                      $atwaterCA->id,        'Federal auto theft and aiding the prison escape of Artie Ray Dufur (anarchist / communist political militant organization)',                  '1979-10-14',     'Yes — federal jury verdict',  '80 years federal + 5 to 15 years state (plus 15 years for attempted escape)'],
            ['Sean Swain',                      $shu->id,              'Murder (1993) — killing during home break-in by nephew of court official; later charged with incitement to riot for confiscated draft of essay criticizing prison payment service "JPay"', '1993-05-01', 'Yes — state jury verdict', '15 years to life Ohio state prison + supermax transfer for political writing'],
            ['Jennifer Rose',                   $californiaDOC->id,    'Originally 7-year sentence for armed robbery (1990); charged with assault on guards after 1991 Folsom Hunger Strike retaliation, sentenced to life under California three-strikes', '1992-01-01', 'Yes — California state jury verdict', 'Life under California three-strikes act'],

            // ─── Fountain Valley 5: 4 already exist with cases above; this catches the 5th from list if needed ───

            // ─── Dr Aafia Siddiqui / Muslim political prisoners — already have cases ───
        ];

        $created = 0;
        $skippedHasCases = 0;
        $skippedNotFound = 0;

        foreach ($cases as $row) {
            [$name, $instId, $charges, $incarcerationDate, $convicted, $sentence] = $row;

            DB::transaction(function () use ($name, $instId, $charges, $incarcerationDate, $convicted, $sentence,
                                              &$created, &$skippedHasCases, &$skippedNotFound) {
                $prisoner = $this->findPrisoner($name);

                if (! $prisoner) {
                    $this->warn("Not in DB: {$name}");
                    $skippedNotFound++;
                    return;
                }

                if ($prisoner->cases()->exists()) {
                    $this->line("Already has cases: {$prisoner->name}");
                    $skippedHasCases++;
                    return;
                }

                PrisonerCase::create([
                    'prisoner_id'        => $prisoner->id,
                    'institution_id'     => $instId,
                    'charges'            => $charges,
                    'incarceration_date' => $incarcerationDate,
                    'convicted'          => $convicted,
                    'sentence'           => $sentence,
                ]);

                $this->info("Added case for: {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped (already had cases) {$skippedHasCases}, not found {$skippedNotFound}.");

        return self::SUCCESS;
    }

    private function findPrisoner(string $name): ?Prisoner
    {
        $prisoner = Prisoner::where('name', $name)->first();
        if ($prisoner) return $prisoner;

        // Try curly/straight quote variants
        $variants = [
            str_replace(['"', "'"], ["\u{201C}", "\u{2019}"], $name),
            str_replace(['"'], ["\u{201D}"], $name),
            str_replace(["\u{201C}", "\u{201D}", "\u{2019}", "\u{2018}"], ['"', '"', "'", "'"], $name),
        ];
        foreach ($variants as $variant) {
            if ($variant === $name) continue;
            $found = Prisoner::where('name', $variant)->first();
            if ($found) return $found;
        }

        // LIKE fallback with quote chars wildcarded
        $needle = '%' . str_replace(['"', "'", "\u{201C}", "\u{201D}", "\u{2019}", "\u{2018}"], '%', $name) . '%';
        return Prisoner::where('name', 'like', $needle)->first();
    }
}
