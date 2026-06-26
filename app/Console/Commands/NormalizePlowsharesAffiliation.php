<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Collapses the three Plowshares affiliation spellings — "Plowshares",
 * "Plowshares movement", and "Plowshares Movement" — into the single canonical
 * "Plowshares Movement". Each prisoner's affiliation array is rewritten in
 * place (and de-duplicated). Idempotent — re-runs change nothing once
 * normalized.
 */
final class NormalizePlowsharesAffiliation extends Command
{
    protected $signature = 'prisoners:normalize-plowshares-affiliation';

    protected $description = 'Merge the Plowshares affiliation variants into "Plowshares Movement"';

    private const CANONICAL = 'Plowshares Movement';

    public function handle(): int
    {
        // Narrow to rows whose JSON affiliation mentions "lowshares" (any case),
        // then normalize in PHP so this works on both MySQL and SQLite.
        $prisoners = Prisoner::withoutGlobalScopes()
            ->where('affiliation', 'like', '%lowshares%')
            ->get();

        $changed = 0;
        foreach ($prisoners as $p) {
            $aff = $p->affiliation;
            if (! is_array($aff)) {
                continue;
            }

            $mapped = array_map(function ($v) {
                $key = strtolower(trim((string) $v));

                return ($key === 'plowshares' || $key === 'plowshares movement') ? self::CANONICAL : $v;
            }, $aff);

            $mapped = array_values(array_unique($mapped));

            if ($mapped !== $aff) {
                $p->affiliation = $mapped;
                $p->save();
                $changed++;
                $this->line("Updated: {$p->name}");
            }
        }

        $this->info("\nDone. Normalized {$changed} record(s) to \"".self::CANONICAL.'".');

        return self::SUCCESS;
    }
}
