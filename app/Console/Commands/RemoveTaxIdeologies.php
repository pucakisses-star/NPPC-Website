<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes "Tax Resistance" and "Anti-tax" from prisoners' ideologies
 * (case-insensitive, also catches "anti tax" / "tax resistance" spelling
 * variants). The values are dropped from each ideologies array; no
 * replacement is added. Idempotent; supports --dry-run.
 */
final class RemoveTaxIdeologies extends Command
{
    protected $signature = 'prisoners:remove-tax-ideologies {--dry-run : Show planned changes without writing}';

    protected $description = 'Remove the "Tax Resistance" and "Anti-tax" ideologies from all prisoners';

    private const TARGETS = ['tax resistance', 'anti-tax', 'anti tax'];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $prisoners = Prisoner::withoutGlobalScopes()
            ->where(fn ($q) => $q
                ->where('ideologies', 'like', '%tax%')
                ->orWhere('ideologies', 'like', '%Tax%'))
            ->get();

        $changed = 0;
        foreach ($prisoners as $p) {
            $ideologies = is_array($p->ideologies) ? $p->ideologies : [];

            $kept = array_values(array_filter(
                $ideologies,
                fn ($v) => ! in_array(strtolower(trim((string) $v)), self::TARGETS, true),
            ));

            if ($kept === $ideologies) {
                continue;
            }

            $this->line("  {$p->name}: ".implode(', ', $ideologies).'  ->  '.(implode(', ', $kept) ?: '(none)'));

            if (! $dry) {
                $p->ideologies = $kept;
                $p->save();
            }

            $changed++;
        }

        if (! $dry && $changed > 0) {
            \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
        }

        $this->newLine();
        if ($dry) {
            $this->warn("Dry run — no changes written. {$changed} record(s) would change.");
        } else {
            $this->info("Done. Removed the tax ideologies from {$changed} record(s).");
        }

        return self::SUCCESS;
    }
}
