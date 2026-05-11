<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Scans the description of every WWI Kohn prisoner and extracts arrest,
 * incarceration, and release dates that the original import didn't catch,
 * writing them to the prisoner's first PrisonerCase where the field is
 * currently blank.
 *
 * Run archive:normalize-kohn-descriptions first so OCR letter-splits in
 * date strings have been collapsed.
 */
final class BackfillKohnDates extends Command {
    protected $signature = 'archive:backfill-kohn-dates {--dry : preview only, do not write} {--sample=5 : show N before/after samples}';
    protected $description = 'Backfill arrest_date, incarceration_date, and release_date on Kohn WWI prisoner cases';

    private const ERA = 'World War I (Espionage & Sedition Acts)';

    public function handle(): int {
        $dry = (bool) $this->option('dry');
        $sampleN = max(0, (int) $this->option('sample'));

        $query = Prisoner::query()
            ->where('era', self::ERA)
            ->with('cases');

        $total = (clone $query)->count();
        $this->info("Inspecting {$total} WWI-era prisoners…");

        $arrestSet = 0;
        $incSet = 0;
        $releaseSet = 0;
        $unchanged = 0;
        $samples = [];

        $query->chunk(100, function ($batch) use (&$arrestSet, &$incSet, &$releaseSet, &$unchanged, &$samples, $sampleN, $dry) {
            foreach ($batch as $prisoner) {
                $case = $prisoner->cases->first();
                if (! $case) {
                    $unchanged++;

                    continue;
                }

                $desc = (string) $prisoner->description;
                $dates = $this->extractDates($desc);
                $changes = [];

                foreach (['arrest_date', 'incarceration_date', 'release_date'] as $field) {
                    if (empty($case->$field) && ! empty($dates[$field])) {
                        $changes[$field] = $dates[$field];
                    }
                }

                if (! $changes) {
                    $unchanged++;

                    continue;
                }

                if (! $dry) {
                    foreach ($changes as $f => $v) {
                        $case->$f = $v;
                    }
                    $case->save();
                }

                if (isset($changes['arrest_date'])) {
                    $arrestSet++;
                }
                if (isset($changes['incarceration_date'])) {
                    $incSet++;
                }
                if (isset($changes['release_date'])) {
                    $releaseSet++;
                }

                if (count($samples) < $sampleN) {
                    $samples[] = [$prisoner->name, $changes];
                }
            }
        });

        $verb = $dry ? 'would set' : 'set';
        $this->info("{$verb} arrest_date: {$arrestSet}");
        $this->info("{$verb} incarceration_date: {$incSet}");
        $this->info("{$verb} release_date: {$releaseSet}");
        $this->info("no new date extractable: {$unchanged}");

        foreach ($samples as [$name, $changes]) {
            $this->line("\n--- {$name} ---");
            foreach ($changes as $f => $v) {
                $this->line("  {$f} = {$v}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @return array{arrest_date?:string,incarceration_date?:string,release_date?:string}
     */
    public function extractDates(string $text): array {
        $dates = [];

        $months = '(January|February|March|April|May|June|July|August|September|October|November|December)';
        $year = '(191[7-9]|192[0-9])';

        // -- Arrest --
        // "arrested on Month Day, Year"
        if (preg_match('/arrested(?:[^\.]{0,60}?)\bon '.$months.' (\d{1,2}),?\s*'.$year.'\b/i', $text, $m)) {
            $dates['arrest_date'] = $this->toIso($m[2], $m[1], $m[3]);
        } elseif (preg_match('/arrested(?:[^\.]{0,40}?)\bin '.$months.'\s+'.$year.'\b/i', $text, $m)) {
            $dates['arrest_date'] = $this->toIso(null, $m[1], $m[2]);
        } elseif (preg_match('/arrested in '.$year.'\b/i', $text, $m)) {
            $dates['arrest_date'] = $m[1].'-01-01';
        }

        // -- Incarceration start --
        // Detect "from Month Day, Year, to Month Day, Year" range first so we
        // can capture both endpoints; this is the most common pattern in Kohn.
        if (preg_match('/\bfrom '.$months.' (\d{1,2}),?\s*'.$year.'(?:,?\s+to\s+'.$months.' (\d{1,2}),?\s*'.$year.')?/i', $text, $m)) {
            $dates['incarceration_date'] = $this->toIso($m[2], $m[1], $m[3]);
            if (! empty($m[4]) && ! empty($m[5]) && ! empty($m[6])) {
                $dates['release_date'] = $this->toIso($m[5], $m[4], $m[6]);
            }
        }
        // "began serving a (prison )sentence on Month Day, Year"
        elseif (preg_match('/began (?:to )?serv(?:e|ing) (?:a |his |her )?(?:prison )?sentence(?:[^\.]{0,40})?\s+(?:on\s+)?'.$months.' (\d{1,2}),?\s*'.$year.'\b/i', $text, $m)) {
            $dates['incarceration_date'] = $this->toIso($m[2], $m[1], $m[3]);
        }
        // "beginning Month Day, Year"
        elseif (preg_match('/beginning '.$months.' (\d{1,2}),?\s*'.$year.'\b/i', $text, $m)) {
            $dates['incarceration_date'] = $this->toIso($m[2], $m[1], $m[3]);
        }

        // -- Release (only if not already captured above) --
        if (! isset($dates['release_date'])) {
            if (preg_match('/released(?:[^\.]{0,40}?)\bon '.$months.' (\d{1,2}),?\s*'.$year.'\b/i', $text, $m)) {
                $dates['release_date'] = $this->toIso($m[2], $m[1], $m[3]);
            } elseif (preg_match('/paroled(?:[^\.]{0,40}?)\bon '.$months.' (\d{1,2}),?\s*'.$year.'\b/i', $text, $m)) {
                $dates['release_date'] = $this->toIso($m[2], $m[1], $m[3]);
            } elseif (preg_match('/(?:commuted|deported|pardoned)(?:[^\.]{0,40}?)\bon '.$months.' (\d{1,2}),?\s*'.$year.'\b/i', $text, $m)) {
                $dates['release_date'] = $this->toIso($m[2], $m[1], $m[3]);
            }
        }

        // Sanity check: release after incarceration
        if (isset($dates['incarceration_date'], $dates['release_date'])
            && strcmp($dates['release_date'], $dates['incarceration_date']) < 0) {
            unset($dates['release_date']);
        }

        return $dates;
    }

    private function toIso(?string $day, string $month, string $year): string {
        $m = date('m', strtotime($month.' 1'));
        $d = $day !== null ? str_pad($day, 2, '0', STR_PAD_LEFT) : '01';

        return "{$year}-{$m}-{$d}";
    }
}
