<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds political prisoners of the Pacific Northwest treaty/fishing-rights
 * struggle, surfaced from Kent Blansett's "A Journey to Freedom: Richard Oakes,
 * Alcatraz, and the Red Power Movement" (Yale Univ. Press, 2018):
 *
 *   - "Fish-in" movement (Nisqually/Puyallup rivers, 1960s): Hank Adams,
 *     Billy Frank Jr., Janet McCloud, Al Bridges, Sid Mills, Suzette Bridges and
 *     Valerie Bridges — repeatedly arrested for treaty fishing-rights civil
 *     disobedience.
 *   - Forebear: Chief Leschi, the Nisqually leader hanged in 1858 after two
 *     trials and about a year in jail (the Medicine Creek treaty he resisted is
 *     the one the fish-in activists later invoked); and Billy Frank Sr., whose
 *     1916 arrest is among the earliest documented in the fight.
 *   - Ally: comedian Dick Gregory, jailed (with a hunger strike) for joining the
 *     1966 Nisqually fish-ins.
 *
 * Bios and dates are filled from the book and established history where well
 * documented and omitted where not. Idempotent — prisoner:add refuses duplicates
 * by name, so re-running only adds those still missing.
 */
final class AddFishingRightsPrisoners extends Command
{
    protected $signature = 'prisoners:add-fishing-rights';

    protected $description = 'Add Pacific NW treaty/fishing-rights political prisoners surfaced from the Blansett biography';

    public function handle(): int
    {
        $path = database_path('data/fishing-rights-prisoners.json');
        if (! is_file($path)) {
            $this->error("Data file not found: {$path}");

            return self::FAILURE;
        }

        $payloads = json_decode(file_get_contents($path), true);
        if (! is_array($payloads)) {
            $this->error('Could not parse JSON.');

            return self::FAILURE;
        }

        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'] ?? '(unnamed)';
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone. added={$added} skipped={$skipped}");

        return self::SUCCESS;
    }
}
