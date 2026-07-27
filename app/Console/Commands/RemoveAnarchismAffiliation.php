<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes "Anarchism" from prisoners' affiliation lists. Anarchism is a
 * political position rather than an organisation, so it belongs in
 * ideologies; the affiliation field is for groups (Industrial Workers of the
 * World, Free Society, Communist Party USA…).
 *
 * By default this only strips the value. Records that would be left with no
 * anarchist label at all — because "Anarchism" was in affiliation but not in
 * ideologies — are reported, and --to-ideology moves the label across for
 * those instead of dropping it. Idempotent; supports --dry-run.
 */
final class RemoveAnarchismAffiliation extends Command
{
    protected $signature = 'prisoners:remove-anarchism-affiliation
        {--to-ideology : Add Anarchism to ideologies where it is not already present, instead of losing the label}
        {--dry-run : Show planned changes without writing}';

    protected $description = 'Remove the "Anarchism" affiliation from all prisoners';

    private const TARGET = 'anarchism';

    private const LABEL = 'Anarchism';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $toIdeology = (bool) $this->option('to-ideology');

        $prisoners = Prisoner::withoutGlobalScopes()
            ->where('affiliation', 'like', '%narchism%')
            ->get();

        $changed = 0;
        $wouldLoseLabel = [];

        foreach ($prisoners as $prisoner) {
            $affiliation = is_array($prisoner->affiliation) ? $prisoner->affiliation : [];

            $kept = array_values(array_filter(
                $affiliation,
                fn ($value) => strtolower(trim((string) $value)) !== self::TARGET,
            ));

            if ($kept === $affiliation) {
                continue;   // matched "%narchism%" on some other value
            }

            $ideologies = is_array($prisoner->ideologies) ? $prisoner->ideologies : [];
            $hasIdeology = (bool) array_filter(
                $ideologies,
                fn ($value) => strtolower(trim((string) $value)) === self::TARGET,
            );

            $note = '';
            if (! $hasIdeology) {
                if ($toIdeology) {
                    $ideologies[] = self::LABEL;
                    $note = '  (+ ideology)';
                } else {
                    $wouldLoseLabel[] = $prisoner->slug;
                    $note = '  (no Anarchism ideology — label lost)';
                }
            }

            $this->line(sprintf(
                '  %-34s %s -> %s%s',
                $prisoner->slug,
                implode(', ', $affiliation) ?: '(none)',
                implode(', ', $kept) ?: '(none)',
                $note,
            ));

            if (! $dry) {
                $prisoner->affiliation = $kept;
                if ($toIdeology && ! $hasIdeology) {
                    $prisoner->ideologies = array_values($ideologies);
                }
                $prisoner->save();
            }

            $changed++;
        }

        if ($wouldLoseLabel) {
            $this->newLine();
            $this->warn(count($wouldLoseLabel).' record(s) have no "Anarchism" ideology to fall back on, so they end up with');
            $this->warn('no anarchist label at all. Re-run with --to-ideology to move the label across instead:');
            foreach ($wouldLoseLabel as $slug) {
                $this->line("  {$slug}");
            }
        }

        if (! $dry && $changed > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$changed} record(s) would change.");
        } else {
            $this->info("Done. Removed the \"Anarchism\" affiliation from {$changed} record(s).");
            $this->line('Note: prisoners:auto-place-zero-sort clusters by affiliation, so any unplaced');
            $this->line('anarchist records now need another shared affiliation to anchor to.');
        }

        return self::SUCCESS;
    }
}
