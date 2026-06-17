<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Loads the political prisoners / political exiles named in the biography
 * "Two Who Were There: A Biography of Stanley Nowak" (Margaret Collingwood
 * Nowak, Wayne State University Press, 1989) from database/data/nowak-bio.json.
 *
 * Limited to the source-grounded names that were not already in the database:
 * the McCarthy-era Detroit deportees (George Pirinsky — first person deported
 * under the McCarran Act; Henry Podolski and James Papandreau, deported under
 * the Walter-McCarran Law) and Howard Fast (jailed 1950 for contempt of
 * Congress). Idempotent — skips by name. Era defaults to 1950s.
 */
class AddNowakBiographyCases extends Command
{
    protected $signature = 'prisoners:add-nowak-bio';

    protected $description = 'Add political prisoners/exiles from the Stanley Nowak biography (from nowak-bio.json)';

    public function handle(): int
    {
        $file = database_path('data/nowak-bio.json');
        if (! file_exists($file)) {
            $this->error('nowak-bio.json data file not found.');

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error("Could not parse {$file}");

            return self::FAILURE;
        }

        $added = 0;
        $skipped = 0;

        foreach ($rows as $r) {
            if (Prisoner::where('name', $r['name'])->exists()) {
                $this->warn("Skipped (exists): {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $prisoner = Prisoner::create([
                    'name' => $r['name'],
                    'first_name' => $r['first_name'],
                    'last_name' => $r['last_name'],
                    'description' => $r['bio'],
                    'gender' => $r['gender'] ?? null,
                    'race' => $r['race'] ?? null,
                    'death_date' => $r['death_date'] ?? null,
                    'state' => $r['state'] ?? null,
                    'era' => $r['era'] ?? '1950s',
                    'ideologies' => $r['ideologies'] ?? [],
                    'affiliation' => $r['affiliation'] ?? [],
                    'in_custody' => false,
                    'released' => $r['released'] ?? true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges' => $r['charges'] ?? null,
                    'convicted' => $r['convicted'] ?? null,
                    'sentence' => $r['sentence'] ?? null,
                ]);
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
