<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Backfills the `era` field for the 198 prisoners that had a blank era.
 *
 * The era for each was inferred from the prisoner's own record — case dates
 * (arrest/incarceration) where present, otherwise the earliest concrete year
 * mentioned in the biography (birth/death spans and later exoneration or
 * publication years were excluded). A handful of entries whose min-year
 * heuristic pointed at the wrong decade (e.g. Harris Neck protesters arrested
 * in 1979 over a 1942 land seizure; ex-Panthers whose imprisonment predated a
 * later-cited event) were corrected by hand. Resulting spread: 1950s (3),
 * 1960s (12), 1970s (144), 1990s (1), 2010s (4), 2020s (34).
 *
 * Safe and idempotent: it only writes `era` when the current value is blank,
 * so re-running it (or running it after any era has been set by hand) is a
 * no-op for already-populated rows. After running, re-run
 * `prisoners:normalize-sort-order` so the newly-dated rows sort into place.
 */
class SetBlankEras extends Command
{
    protected $signature = 'prisoners:set-blank-eras {--dry-run : List what would change without writing}';

    protected $description = 'Backfill era for the 198 prisoners with a blank era, inferred from their records';

    /** name => era, inferred from each prisoner's case dates / biography. */
    private const ERAS = [
        'David Webb' => '2020s',
        'Anthony Krohn' => '2020s',
        'Bryce Williams' => '2020s',
        'Charles Pittman' => '2020s',
        'Damion Zachary Feller' => '2010s',
        'Earlja Dudley' => '2020s',
        'Fornandous Henderson' => '2020s',
        'Jackson Patton' => '2020s',
        'Jesse Clark' => '2020s',
        'Jesse Smallwood' => '2020s',
        'Jose Felan' => '2020s',
        'Judah Bailey' => '2020s',
        'Kyle Olson' => '2020s',
        'Rakem Balogun' => '2010s',
        'Richard Rubalcava' => '2020s',
        'Shamar Betts' => '2020s',
        'Channel Lewis' => '2020s',
        'Deyanna Davis' => '2020s',
        'Linwood Kaine' => '2010s',
        'Edgar Samaniego' => '2020s',
        'Corey Long' => '2010s',
        'Cyril Lartigue' => '2020s',
        'Garrett Ziegler' => '2020s',
        'Jabari Davis' => '2020s',
        'John Dupree' => '2020s',
        'Kenyatta Huggins' => '2020s',
        'Mena Yousif' => '2020s',
        'Oliva Hull' => '2020s',
        'Ronald Raymond' => '2020s',
        'Samuel Frey' => '2020s',
        'Semaj Pigram' => '2020s',
        'Shante Sutton' => '2020s',
        'Talib Crump' => '2020s',
        'Tyler Maple' => '2020s',
        'Walter Stewart' => '2020s',
        'Zachary Karas' => '2020s',
        'James Marshall' => '2020s',
        'Steven Lopez' => '2020s',
        'Andres Figueroa Cordero' => '1950s',
        'Carl Bass' => '1970s',
        'Case Johnson' => '1970s',
        'Charles 2X Beasley' => '1970s',
        'Christopher McIntosh' => '1970s',
        'Edgar Timmons, Jr.' => '1970s',
        'Forest Jordan' => '1970s',
        'Frank X. Moore' => '1970s',
        'George Chagina Dobbins' => '1970s',
        'Gregory Felix' => '1970s',
        'Grover McCorvey' => '1970s',
        'Hercules Anderson' => '1970s',
        'James Collins' => '1970s',
        'Jerome Singleton' => '1970s',
        'Jesse Tuba Clancy' => '1970s',
        'Jessie Whitaker' => '1970s',
        'Johnny Imani Harris' => '1970s',
        'Khalil Islam' => '1960s',
        'Lincoln Heard' => '1970s',
        'Moses Evans' => '1970s',
        'Muhammad Abdul Aziz' => '1960s',
        'Oscar Gamba Johnson' => '1970s',
        'Robert Earl May, Jr.' => '1970s',
        'Stephen Bingham' => '1970s',
        'Ted Clark' => '1970s',
        'Terrence Johnson' => '1970s',
        'Tommy Dotson' => '1970s',
        'Warren E. Sumlin, Sr.' => '1970s',
        'Al McSurely' => '1970s',
        'Ann Shepard Turner' => '1970s',
        'Ben Chavis' => '1970s',
        'Bobby Hutton' => '1960s',
        'Cheryl Todd' => '1970s',
        'Delbert Africa' => '1970s',
        'Delia Gonzalez' => '1970s',
        'Dennis Goodwin' => '1970s',
        'Donald Thigpen' => '1970s',
        'Eddie Page' => '1970s',
        'Hayward Brown' => '1970s',
        'James Edward Garrett' => '1970s',
        'James McCoy' => '1970s',
        'JoAnne Little' => '1970s',
        'Joseph Waddell' => '1970s',
        'Joseph Waller' => '1970s',
        'Madelyn Fletcher' => '1970s',
        'Margaret McSurely' => '1970s',
        'Robert Heard' => '1970s',
        'Robert Houchens' => '1970s',
        'William Wright' => '1970s',
        'Ali Shokri' => '1970s',
        'Bob Duren' => '1970s',
        'Bobby Bishop' => '1970s',
        'Clarence Copens' => '1970s',
        'Curtis Jones' => '1970s',
        'Donald Hunter' => '1970s',
        'Eldson McGhee' => '1970s',
        'Eva Kutas' => '1970s',
        'Glen White' => '1970s',
        'Greg Franklin' => '1970s',
        'Gregory Coffey' => '1970s',
        'Herman Fletcher' => '1970s',
        'Inez Garcia' => '1970s',
        'Irving Flores' => '1950s',
        'James Jackson Jr.' => '1970s',
        'James McClain' => '1970s',
        'James Thornwell' => '1960s',
        'Johnny Jackson' => '1970s',
        'Johnny McRea' => '1970s',
        'Johnny Ross' => '1970s',
        'Johnson Cole' => '1970s',
        'Jose Medina' => '1970s',
        'Larry Roberson' => '1960s',
        'Lolita Lebron' => '1950s',
        'Lorenzo Komboa Ervin' => '1960s',
        'Lucious Amerson' => '1970s',
        'Marty Dixon' => '1970s',
        'Michael Cetawayo Tabor' => '1960s',
        'Molly Dougherty' => '1970s',
        'Ola Mae Davis' => '1970s',
        'Otis Johnson' => '1970s',
        'Richard Lake' => '1970s',
        'Ricky McGivery' => '1970s',
        'Robert Kendrick' => '1970s',
        'Robert Wesley Wells' => '1970s',
        'Shirley Herlth' => '1970s',
        'Tenola Gamble' => '1970s',
        'William Christmas' => '1970s',
        'Alfredo Lopez' => '1970s',
        'Andre Evans' => '1970s',
        'Anna Mae Aquash' => '1970s',
        'Blair Anderson' => '1960s',
        'Bob Yellow Bird' => '1970s',
        'Carl Vincent Henry' => '1970s',
        'Connie Wilson' => '1970s',
        'Craemen Gethers' => '1970s',
        'Curtis Jordan' => '1970s',
        'Darrelle Butler' => '1970s',
        'Darwin Lance Brown' => '1960s',
        'Donnell Moore' => '1970s',
        'Earl Brown' => '1970s',
        'Earl Gaither' => '1970s',
        'Elmer Pratt' => '1970s',
        'Faye Brown' => '1970s',
        'Glenn Diamond' => '1970s',
        'H. Rap Brown' => '1970s',
        'Howard Ay Gibbs' => '1970s',
        'J.B. Johnson' => '1970s',
        'James Dixon' => '1970s',
        'James Jones' => '1970s',
        'Jimmy Eagle' => '1970s',
        'Jo An Yellow Bird' => '1970s',
        'Johnny Larry Spain' => '1970s',
        'Kamook Banks' => '1970s',
        'Nate Saunsoci' => '1970s',
        'Oscar Jordan' => '1970s',
        'Ramon Chacon' => '1970s',
        'Randolph Jennings' => '1970s',
        'Ricardo Chavez-Ortiz' => '1970s',
        'Ronald Payne' => '1970s',
        'Ronald Satchel' => '1960s',
        'Sam Bell' => '1970s',
        'Sterling Hobbs Fatir' => '1970s',
        'Verlina Brewer' => '1960s',
        'Wilbur Shabazz' => '1970s',
        'Alex Poindexter' => '1970s',
        'Alfonso Ross' => '1970s',
        'Arthur Barber' => '1970s',
        'Bernard Stroble' => '1970s',
        'Billy Dean Smith' => '1970s',
        'Darryl King' => '1970s',
        'DeWayne Williams' => '1970s',
        'Earnest Ball' => '1970s',
        'Elton Rankin' => '1970s',
        'Federico Cintron Fiallo' => '1970s',
        'Frank Gaskins' => '1970s',
        'Frank Shuford' => '1970s',
        'Freddie Pitts' => '1960s',
        'Gamba Mani' => '1970s',
        'Gerald Bernard Best' => '1970s',
        'Henry Dee' => '1970s',
        'Herman Blyden' => '1970s',
        'Hosea Williams' => '1970s',
        'James Sayles' => '1970s',
        'Jim Grant' => '1970s',
        'Johnnie Harris' => '1970s',
        'Lehman Brightman' => '1970s',
        'Michael ZinZun' => '1970s',
        'Morris White' => '1970s',
        'Ormistan Spencer' => '1970s',
        'Philip Allen' => '1970s',
        'Raymond Sumter' => '1970s',
        'Rommie Loudd' => '1970s',
        'Ronald Lyons' => '1970s',
        'Roy Lee Patterson' => '1970s',
        'Sarah Bad Heart Bull' => '1970s',
        'Terry Phillips' => '1970s',
        'Tom Stevens' => '1970s',
        'William Ortiz' => '1970s',
        'Willie Burnett' => '1970s',
        'Fran Thompson' => '1990s',    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $set = 0;
        $skippedExisting = 0;
        $missing = [];

        foreach (self::ERAS as $name => $era) {
            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();

            if (! $prisoner) {
                $missing[] = $name;

                continue;
            }

            if (! empty($prisoner->era)) {
                $skippedExisting++;

                continue;
            }

            if ($dryRun) {
                $this->line(sprintf('  %-8s  %s', $era, $name));
            } else {
                $prisoner->era = $era;
                $prisoner->save();
            }

            $set++;
        }

        if ($dryRun) {
            $this->info("Dry run — would set era on {$set} prisoners; {$skippedExisting} already had one.");
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info("Set era on {$set} prisoners; {$skippedExisting} already had one. API cache cleared.");
        }

        if ($missing) {
            $this->warn('Not found by name ('.count($missing).'): '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }
}
