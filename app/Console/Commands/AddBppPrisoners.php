<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds political prisoners surfaced from Wikipedia's "Members of the Black
 * Panther Party" category who were not already in the database — i.e. BPP/BLA
 * figures imprisoned for their activism (George Jackson, Mumia Abu-Jamal,
 * Geronimo Pratt, Assata Shakur, Sundiata Acoli, Jalil Muntaqim, Sekou Odinga,
 * Russell Maroon Shoatz, Dhoruba bin Wahad, Veronza Bowers Jr., Robert Hillary
 * King, Kuwasi Balagoon, Bashir Hameed, David Hilliard, Jamil Al-Amin, Safiya
 * Bukhari, Ashanti Alston, Lorenzo Kom'boa Ervin, Aaron Dixon, Larry Gossett,
 * Kent Ford, Akua Njeri) or forced into political exile (Assata Shakur, William
 * Lee Brent, Kathleen Cleaver, Connie Matthews).
 *
 * Members never imprisoned for activism, informants (e.g. William O'Neal),
 * murder victims (e.g. Alex Rackley), and those killed by police (martyrs, not
 * prisoners) were deliberately excluded. Idempotent — prisoner:add refuses
 * duplicates by name, so re-running only adds those still missing.
 */
final class AddBppPrisoners extends Command
{
    protected $signature = 'prisoners:add-bpp';

    protected $description = 'Add Black Panther Party-category political prisoners missing from the database';

    public function handle(): int
    {
        $path = database_path('data/bpp-prisoners.json');
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
