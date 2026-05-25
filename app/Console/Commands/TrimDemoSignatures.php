<?php

namespace App\Console\Commands;

use App\Models\Petition;
use App\Models\PetitionSignature;
use Illuminate\Console\Command;

/**
 * One-shot: for every petition with @nppc-demo.test signatures, pick
 * a random N in [15..39] and delete that many demo signatures (oldest
 * first by default). Lets the public-facing counts read as
 * "near-target but not suspiciously round."
 *
 * Dry-run by default; --apply writes.
 */
final class TrimDemoSignatures extends Command {
    protected $signature = 'archive:trim-demo-signatures
        {--min=15 : Minimum to remove from each petition}
        {--max=39 : Maximum to remove from each petition}
        {--newest : Remove newest demo sigs first instead of oldest}
        {--apply : Required to actually write}';
    protected $description = 'Remove a random 15-39 demo signatures from each petition';

    public function handle(): int {
        $min = max(0, (int) $this->option('min'));
        $max = max($min, (int) $this->option('max'));
        $newest = (bool) $this->option('newest');

        $petitions = Petition::query()->get();
        $totalDeleted = 0;
        $report = [];

        foreach ($petitions as $petition) {
            $existing = PetitionSignature::where('petition_id', $petition->id)
                ->where('email', 'like', '%@nppc-demo.test')
                ->count();
            if ($existing === 0) continue;

            $n = random_int($min, $max);
            $n = min($n, $existing);
            $report[] = [$petition->slug, $existing, $n, $existing - $n];

            if (! $this->option('apply')) continue;

            $ids = PetitionSignature::where('petition_id', $petition->id)
                ->where('email', 'like', '%@nppc-demo.test')
                ->orderBy('created_at', $newest ? 'desc' : 'asc')
                ->limit($n)
                ->pluck('id');
            PetitionSignature::whereIn('id', $ids)->delete();
            $totalDeleted += $ids->count();
        }

        $this->table(['slug', 'before', 'deleted', 'after'], $report);

        if (! $this->option('apply')) {
            $this->info('(dry-run; re-run with --apply to write)');
            return self::SUCCESS;
        }

        $this->info("Done — deleted {$totalDeleted} demo signature(s) across ".count($report).' petition(s).');
        return self::SUCCESS;
    }
}
