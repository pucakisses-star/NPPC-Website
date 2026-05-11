<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Re-runs state extraction on imported WWI Kohn prisoners whose `state` column
 * is empty. Handles two corner cases that the original import missed:
 *
 * 1) OCR splits inside state names ("O h i o", "P ennsylvania", "C onnecticut")
 *    by collapsing single-letter-spaced sequences in the description before
 *    re-matching state names.
 * 2) Falls back to inferring the state from the institution name when the
 *    bio doesn't restate a home state (e.g. San Quentin → California).
 */
final class FixKohnPrisonerStates extends Command {
    protected $signature = 'archive:fix-kohn-states {--dry : preview only, do not write}';
    protected $description = 'Backfill state on Kohn WWI prisoners using OCR-tolerant matching + institution fallback';

    private const ERA = '1910s';

    public function handle(): int {
        $dry = (bool) $this->option('dry');

        $query = Prisoner::query()
            ->where('era', self::ERA)
            ->where(function ($q) {
                $q->whereNull('state')->orWhere('state', '');
            });

        $total = (clone $query)->count();
        $this->info("Candidates without state: {$total}");

        $updated = 0;
        $stillNone = 0;

        $query->with(['cases.institution'])->chunk(100, function ($batch) use (&$updated, &$stillNone, $dry) {
            foreach ($batch as $prisoner) {
                $state = $this->extractState((string) $prisoner->description);

                if (! $state) {
                    foreach ($prisoner->cases as $case) {
                        if ($case->institution && $case->institution->state) {
                            $state = $case->institution->state;
                            break;
                        }
                    }
                }

                if ($state) {
                    if (! $dry) {
                        $prisoner->state = $state;
                        $prisoner->save();
                    }
                    $updated++;
                } else {
                    $stillNone++;
                }
            }
        });

        $verb = $dry ? 'would update' : 'updated';
        $this->info("{$verb}: {$updated}");
        $this->info("still without state: {$stillNone}");

        return self::SUCCESS;
    }

    private function extractState(?string $text): ?string {
        if (! $text) {
            return null;
        }

        // Collapse OCR splits like "O h i o" → "Ohio".
        // The pattern: 2+ single-letter tokens separated by single spaces, ending at a word boundary.
        // We rebuild the word by stripping the inter-letter spaces.
        $normalized = preg_replace_callback(
            '/\b([A-Za-z](?:\s[A-Za-z]){2,})\b/u',
            fn ($m) => str_replace(' ', '', $m[1]),
            $text
        );

        // List of states (longest first so "New York" wins over "New").
        $states = [
            'New Hampshire', 'New Jersey', 'New Mexico', 'New York',
            'North Carolina', 'North Dakota', 'Rhode Island',
            'South Carolina', 'South Dakota', 'West Virginia',
            'Puerto Rico',
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
            'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
            'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
            'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
            'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
            'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania',
            'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
            'Wisconsin', 'Wyoming',
        ];

        foreach ($states as $state) {
            if (preg_match('/\b'.preg_quote($state, '/').'\b/i', $normalized)) {
                return $state;
            }
        }

        return null;
    }
}
