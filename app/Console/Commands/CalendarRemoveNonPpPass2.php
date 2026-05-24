<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;

/**
 * Second-pass removal of off-topic calendar entries — civil rights
 * icons' births/deaths who weren't themselves political prisoners,
 * sit-ins and movement landmarks without a specific PP angle, and
 * uprisings/riots without a named-defendant focus.
 *
 * Pairs with calendar:remove-non-pp (the first pass) — together
 * they get the calendar down to entries that all anchor a specific
 * political-prisoner / movement-defendant / state-repression case.
 *
 * --dry-run supported. Idempotent.
 */
final class CalendarRemoveNonPpPass2 extends Command {
    protected $signature = 'calendar:remove-non-pp-pass2 {--dry-run : Print what would be deleted without deleting}';
    protected $description = 'Remove 27 more calendar entries unrelated to political prisoners (movement landmarks, icon biographies, riots without defendants)';

    private const TITLES = [
        // Births / deaths of civil rights icons (not PPs themselves)
        'Martin Luther King Jr. born in Atlanta',
        'W.E.B. Du Bois born in Great Barrington, Massachusetts',
        'Birth control activist Ida Rauh dies',
        'Anarchist Lucy Parsons dies in Chicago',
        'Paul Robeson born in Princeton, New Jersey',
        'Civil rights activist Claudette Colvin born',

        // Sit-ins / boycotts / movement landmarks without a specific PP angle
        'Greensboro lunch counter sit-in begins',
        'Seattle General Strike begins',
        'Howard students stage segregated DC restaurant sit-in',
        'Columbia University student strike begins',
        'US general strike for the 8-hour day begins',
        'Pullman railroad strike begins in Chicago',
        'Frederick Douglass\'s \'What to the Slave is the Fourth of July?\'',
        'Frederick Douglass escapes slavery',
        'Occupy Wall Street begins in Zuccotti Park',
        'MLK awarded Nobel Peace Prize',
        'March on the Pentagon, 100,000 anti-war protesters',
        'Montgomery Bus Boycott begins',

        // Uprisings / riots / police events without a specific defendant focus
        'Police raid LGBT+ Black Cat Tavern in Los Angeles',
        'MLK\'s home in Montgomery bombed during bus boycott',
        'Hard Hat Riot attacks NYC anti-war protesters',
        'Largest mass arrest in Rochester NY history',
        'Division Street riots erupt in Chicago',
        'Albuquerque riot after police shoot Latino youth',
        'Shays\' Rebellion of war veterans begins in Massachusetts',
        'Racists try to assassinate Fannie Lou Hamer',
        'Christiana Resistance against the Fugitive Slave Act',
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
