<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Reads database/data/wikipedia-pp-candidates.json (a list of names
 * harvested from Wikipedia's "Political prisoners in the United
 * States" article + adjacent sources) and reports which are already
 * in NPPC vs missing. Matches by name, then aka, then last-name
 * fuzzy. Use to identify gaps to add via prisoner:add.
 */
final class CheckWikipediaPpCandidates extends Command {
    protected $signature = 'archive:check-wikipedia-pp-candidates';
    protected $description = 'Check Wikipedia PP candidate list against NPPC DB';

    public function handle(): int {
        $entries = json_decode(file_get_contents(database_path('data/wikipedia-pp-candidates.json')), true);

        $hit = 0;
        $missing = [];
        foreach ($entries as $e) {
            $name = $e['name'];
            $aka = $e['aka'] ?? null;
            $note = $e['note'] ?? '';

            $match = Prisoner::withUnderReview()
                ->where('name', $name)
                ->orWhere(fn ($q) => $aka ? $q->where('name', $aka) : $q)
                ->orWhere('aka', 'like', "%{$name}%")
                ->first();

            if (! $match && $aka) {
                $match = Prisoner::withUnderReview()
                    ->where('aka', 'like', "%{$aka}%")->first();
            }

            if (! $match) {
                $parts = preg_split('/\s+/', $name);
                $last = end($parts);
                if (strlen($last) > 3) {
                    $match = Prisoner::withUnderReview()
                        ->where('name', 'like', "%{$last}%")->first();
                }
            }

            if ($match) {
                $this->info(str_pad('IN-DB', 12).$name.'  ('.$match->slug.')');
                $hit++;
            } else {
                $this->warn(str_pad('MISSING', 12).$name.'   — '.$note);
                $missing[] = $e;
            }
        }

        $this->line("\n— SUMMARY —");
        $this->info('In DB:   '.$hit);
        $this->warn('Missing: '.count($missing));

        if (! empty($missing)) {
            $this->line("\nMissing names:");
            foreach ($missing as $m) {
                $this->line('  - '.$m['name'].($m['aka'] ? ' / '.$m['aka'] : '').'   — '.($m['note'] ?? ''));
            }
        }

        return self::SUCCESS;
    }
}
