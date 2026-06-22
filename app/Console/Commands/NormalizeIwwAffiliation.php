<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Consolidates the several spellings of the Industrial Workers of the World
 * affiliation into one canonical string — "Industrial Workers of the World
 * (IWW)" — so the affiliation list/filter shows a single IWW entry instead of
 * two or three near-duplicates.
 *
 * Matches any variant: "Industrial Workers of the World", "...(IWW)",
 * "...(I.W.W.)", or a bare "IWW" / "I.W.W." / "Wobblies", and rewrites it to
 * the canonical form, de-duplicating if a prisoner happened to carry two forms.
 *
 * Idempotent. Use --dry-run to preview.
 */
final class NormalizeIwwAffiliation extends Command
{
    protected $signature = 'prisoners:normalize-iww-affiliation {--dry-run}';

    protected $description = 'Consolidate IWW affiliation variants into "Industrial Workers of the World (IWW)"';

    private const CANONICAL = 'Industrial Workers of the World (IWW)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $updated = 0;

        Prisoner::withUnderReview()->whereNotNull('affiliation')->get()->each(function (Prisoner $p) use (&$updated, $dry) {
            $orig = array_values((array) $p->affiliation);
            if (empty($orig)) {
                return;
            }

            $new = array_values(array_unique(array_map(
                fn ($a) => $this->isIww((string) $a) ? self::CANONICAL : $a,
                $orig,
            )));

            if ($new !== $orig) {
                $this->line(($dry ? '[dry] ' : '')."{$p->name}: ".json_encode($orig).' → '.json_encode($new));
                if (! $dry) {
                    $p->affiliation = $new;
                    $p->save();
                }
                $updated++;
            }
        });

        $verb = $dry ? 'would update' : 'updated';
        $this->info("Done. {$verb} {$updated} prisoner(s); canonical = \"".self::CANONICAL.'".');

        return self::SUCCESS;
    }

    private function isIww(string $a): bool
    {
        $n = rtrim(strtolower(trim($a)), '.');

        return str_starts_with($n, 'industrial workers of the world')
            || in_array($n, ['iww', 'i.w.w', 'wobblies', 'the wobblies'], true);
    }
}
