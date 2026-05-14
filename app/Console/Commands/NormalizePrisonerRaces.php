<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Collapses the race field on every prisoner record into one of the
 * six canonical buckets used by the public filter:
 *
 *   White, Black, Asian, Native American, Latino, Middle Eastern
 *
 * Values that don't match any rule are listed under "unmapped" at the
 * end of the run so they can be reviewed manually — no silent
 * mangling.
 *
 * Dry-run by default; --apply writes.
 */
final class NormalizePrisonerRaces extends Command {
    protected $signature = 'prisoners:normalize-races {--apply : Actually write the normalized values}';
    protected $description = 'Normalize Prisoner.race into White / Black / Asian / Native American / Latino / Middle Eastern.';

    /**
     * Source → canonical bucket. Compared case-insensitively after
     * trimming whitespace. Add new aliases here.
     *
     * Note: "Afro-Puerto Rican" maps to Latino so Puerto Rican
     * political prisoners cluster together in the public filter.
     * Override manually in /admin if a specific record should be
     * filed under Black instead.
     */
    private array $map = [
        // White
        'white' => 'White',
        'caucasian' => 'White',
        'european' => 'White',
        'european american' => 'White',

        // Black
        'black' => 'Black',
        'african american' => 'Black',
        'african-american' => 'Black',
        'afro-american' => 'Black',
        'black/african american' => 'Black',
        'black/cherokee' => 'Black',

        // Asian
        'asian' => 'Asian',
        'asian american' => 'Asian',
        'asian-american' => 'Asian',
        'east asian' => 'Asian',
        'south asian' => 'Asian',
        'southeast asian' => 'Asian',
        'pacific islander' => 'Asian',

        // Native American
        'native american' => 'Native American',
        'american indian' => 'Native American',
        'indigenous' => 'Native American',
        'first nations' => 'Native American',
        'alaska native' => 'Native American',
        'indigenous (cherokee/choctaw)' => 'Native American',
        'indigenous (cherokee)' => 'Native American',
        'indigenous (choctaw)' => 'Native American',
        'pascua yaqui' => 'Native American',
        'yaqui' => 'Native American',
        'apache/chicano' => 'Native American',

        // Latino
        'latino' => 'Latino',
        'latina' => 'Latino',
        'latinx' => 'Latino',
        'hispanic' => 'Latino',
        'hispanic/latino' => 'Latino',
        'hispanic/latina' => 'Latino',
        'latino/hispanic' => 'Latino',
        'latino / hispanic' => 'Latino',
        'latina/hispanic' => 'Latino',
        'chicano' => 'Latino',
        'chicana' => 'Latino',
        'mestizo' => 'Latino',
        'mestiza' => 'Latino',
        'mexican american' => 'Latino',
        'mexican-american' => 'Latino',
        'puerto rican' => 'Latino',
        'afro-puerto rican' => 'Latino',
        'cuban american' => 'Latino',
        'cuban-american' => 'Latino',

        // Middle Eastern
        'middle eastern' => 'Middle Eastern',
        'arab' => 'Middle Eastern',
        'arab american' => 'Middle Eastern',
        'arab-american' => 'Middle Eastern',
        'palestinian' => 'Middle Eastern',
        'persian' => 'Middle Eastern',
        'iranian' => 'Middle Eastern',
        'kurdish' => 'Middle Eastern',
        'lebanese' => 'Middle Eastern',
    ];

    public function handle(): int {
        $apply = (bool) $this->option('apply');

        $prisoners = Prisoner::query()
            ->whereNotNull('race')
            ->where('race', '!=', '')
            ->get(['id', 'name', 'slug', 'race']);

        $changes = [];
        $unmapped = [];

        foreach ($prisoners as $p) {
            $current = trim((string) $p->race);
            if ($current === '') {
                continue;
            }

            $key = mb_strtolower($current);
            if (! isset($this->map[$key])) {
                $unmapped[$current][] = $p;
                continue;
            }

            $target = $this->map[$key];
            if ($target !== $current) {
                $changes[] = compact('p', 'current', 'target');
            }
        }

        $this->info('Scanned '.$prisoners->count().' prisoners with a race set.');
        $this->line('');

        if (! empty($changes)) {
            $byTarget = [];
            foreach ($changes as $c) {
                $byTarget[$c['target']][$c['current']] = ($byTarget[$c['target']][$c['current']] ?? 0) + 1;
            }
            $this->info('Planned changes ('.count($changes).' records):');
            foreach ($byTarget as $target => $sources) {
                foreach ($sources as $src => $n) {
                    $this->line("  {$src}  →  {$target}  ({$n})");
                }
            }
            $this->line('');
        }

        if (! empty($unmapped)) {
            $this->warn('Unmapped values left untouched (review manually):');
            foreach ($unmapped as $value => $rows) {
                $this->line("  '{$value}'  — ".count($rows).' record(s)');
                foreach (array_slice($rows, 0, 5) as $p) {
                    $this->line('       #'.$p->id.'  /prisoner/'.$p->slug.'  — '.$p->name);
                }
                if (count($rows) > 5) {
                    $this->line('       …+'.(count($rows) - 5).' more');
                }
            }
            $this->line('');
        }

        if (! $apply) {
            $this->info('(dry-run; re-run with --apply to write)');
            return self::SUCCESS;
        }

        foreach ($changes as $c) {
            $c['p']->race = $c['target'];
            $c['p']->save();
        }
        $this->info('Applied '.count($changes).' updates.');

        return self::SUCCESS;
    }
}
