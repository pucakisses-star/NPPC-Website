<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Loads the 1952 National Guardian political-prisoner roster from
 * database/data/ng-1952-*.json — cases surfaced by a full page-by-page read of
 * all 52 of the paper's 1952 issues (OCR), cross-checked so cases already in
 * the database (the Rosenbergs, the Smith Act 11, the 1951 second-tier already
 * added, the Terminal Island Four, Harisiades, Mangaoang, Coplon, Browder,
 * Lattimore, Spector, the Groveland Four, the Trenton Six, Steve Nelson, etc.)
 * are NOT duplicated. Idempotent — skips by name. Era 1950s.
 */
class AddNationalGuardian1952Cases extends Command {
    protected $signature = 'prisoners:add-ng-1952';
    protected $description = 'Add 1952 National Guardian political-prisoner roster (from ng-1952-*.json)';

    public function handle(): int {
        $files = glob(database_path('data/ng-1952-*.json'));
        sort($files);
        if (! $files) {
            $this->error('No ng-1952-*.json data files found.');
            return self::FAILURE;
        }

        $added = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $rows = json_decode(file_get_contents($file), true);
            if (! is_array($rows)) {
                $this->error("Could not parse {$file}");
                return self::FAILURE;
            }

            foreach ($rows as $r) {
                if (Prisoner::where('name', $r['name'])->exists()) {
                    $this->warn("Skipped (exists): {$r['name']}");
                    $skipped++;
                    continue;
                }

                DB::transaction(function () use ($r) {
                    $prisoner = Prisoner::create([
                        'name'           => $r['name'],
                        'first_name'     => $r['first_name'],
                        'last_name'      => $r['last_name'],
                        'description'    => $r['bio'],
                        'gender'         => $r['gender'] ?? null,
                        'race'           => $r['race'] ?? null,
                        'death_date'     => $r['death_date'] ?? null,
                        'state'          => $r['state'] ?? null,
                        'era'            => $r['era'] ?? '1950s',
                        'ideologies'     => $r['ideologies'] ?? [],
                        'affiliation'    => $r['affiliation'] ?? [],
                        'in_custody'     => false,
                        'released'       => $r['released'] ?? true,
                        'awaiting_trial' => false,
                    ]);

                    PrisonerCase::create([
                        'prisoner_id' => $prisoner->id,
                        'charges'     => $r['charges'] ?? null,
                        'convicted'   => $r['convicted'] ?? null,
                        'sentence'    => $r['sentence'] ?? null,
                    ]);
                });

                $this->info("Added: {$r['name']}");
                $added++;
            }
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
