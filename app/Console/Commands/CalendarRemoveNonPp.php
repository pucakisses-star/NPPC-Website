<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;

/**
 * Removes 50 calendar entries that aren't about political prisoners,
 * movement-defense, or state repression of organizing — pure
 * historical events, racial massacres without a specific PP angle,
 * and other off-topic items.
 *
 * Supports --dry-run to preview before destruction.
 * Idempotent — entries already gone are reported but cause no error.
 */
final class CalendarRemoveNonPp extends Command {
    protected $signature = 'calendar:remove-non-pp {--dry-run : Print what would be deleted without deleting}';
    protected $description = 'Remove ~50 calendar entries unrelated to political prisoners';

    /**
     * Exact titles of entries to remove. Matching is strict so we
     * don't accidentally nuke similarly-titled new entries.
     */
    private const TITLES = [
        // "SAFE TO REMOVE" list — pure historical events with no PP angle
        'Paris Peace Accords end direct U.S. role in Vietnam War',
        'Dred Scott decision issued by the U.S. Supreme Court',
        'My Lai Massacre by U.S. troops in Vietnam',
        'LBJ announces he won\'t seek re-election',
        'Oklahoma City federal building bombing',
        'Robert F. Kennedy shot in Los Angeles',
        'Cuban revolutionaries attack Moncada Barracks',
        'United States drops atomic bomb on Hiroshima',
        'Richard Nixon resigns the U.S. presidency',
        'Heather Heyer killed at Charlottesville white supremacist rally',
        '19th Amendment ratified, women win the vote',
        'Tlatelolco massacre of student protesters in Mexico City',
        'Che Guevara executed in Bolivia',
        'U.S. embassy hostages taken in Tehran',
        'Jonestown mass murder-suicide in Guyana',
        'President John F. Kennedy assassinated in Dallas',
        '13th Amendment abolishing slavery ratified',
        'George Washington dies — 317 enslaved people on Mount Vernon',
        'Watergate burglars\' first break-in at DNC headquarters',

        // "GREY ZONE" list — racial massacres / state violence without
        // a specific prisoner angle
        'Porvenir massacre by US troops and Texas Rangers',
        'Black WWII vet Isaac Woodard blinded by police',
        'Wiyot massacre of Indigenous people in California',
        'Rodney King beaten by LAPD officers',
        'Gnadenhutten massacre by US revolutionary forces',
        '1935 Harlem Uprising against police brutality',
        'Civil rights worker Viola Liuzzo murdered in Alabama',
        'Colfax massacre in Reconstruction Louisiana',
        'Ludlow massacre of striking miners in Colorado',
        'Anaconda Road massacre of striking Butte miners',
        'California enacts first state forced sterilization law',
        'Native man Tacho lynched by white mob in Banning, CO',
        'Los Angeles uprising after Rodney King verdict',
        'Memorial Day Massacre of Chicago steelworkers',
        'Tulsa race massacre destroys Black Wall Street',
        'Zoot Suit Riots begin in Los Angeles',
        'Police massacre Youngstown steelworkers\' families',
        'Grabow massacre of Louisiana timber workers',
        'Hamburg massacre suppresses Black voters in SC',
        'Chicago race riots begin after racist beach killing',
        'Hilo massacre of striking Hawaiian dockers',
        'Watts riots erupt in Los Angeles',
        'Ruben Salazar killed by police at Chicano protest',
        'Danziger Bridge police shootings in New Orleans',
        'Chiquola Mill massacre of striking SC textile workers',
        'Elaine massacre of Black sharecroppers in Arkansas',
        'Thibodaux massacre of Black sugar workers',
        'Mississippi passes Black Code reintroducing slavery',
        'Custer leads Washita massacre of Cheyenne',
        'US nuns murdered by US-trained Salvadoran death squads',
        'Wounded Knee massacre of Lakota by US troops',
    ];

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');

        $matches = CalendarEntry::query()
            ->whereIn('title', self::TITLES)
            ->orderBy('month')
            ->orderBy('day')
            ->get(['id', 'month', 'day', 'year', 'title']);

        $found = $matches->pluck('title')->all();
        $missing = array_values(array_diff(self::TITLES, $found));

        $this->info(($dryRun ? '[DRY RUN] ' : '').'Would delete '.$matches->count().' calendar entries:');
        foreach ($matches as $e) {
            $date = sprintf('%02d-%02d', $e->month, $e->day);
            $this->line("  {$date} {$e->year}  {$e->title}");
        }

        if ($missing) {
            $this->newLine();
            $this->warn('Not found in DB ('.count($missing).'):');
            foreach ($missing as $t) {
                $this->line("  - {$t}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry run — no changes made. Re-run without --dry-run to delete.');
            return self::SUCCESS;
        }

        if ($matches->isEmpty()) {
            return self::SUCCESS;
        }

        $deleted = CalendarEntry::query()->whereIn('id', $matches->pluck('id'))->delete();
        $this->newLine();
        $this->info("Deleted {$deleted} calendar entries.");
        return self::SUCCESS;
    }
}
