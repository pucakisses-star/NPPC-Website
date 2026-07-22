<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Flag prisoner records that share an inmate / DOC / BOP number — a strong
 * duplicate signal, since the same custody number almost always means the same
 * person entered twice under name variants.
 *
 * Numbers are normalised (uppercased, non-alphanumerics stripped) before
 * grouping, so "#12571-506", "12571506" and "12571-506" cluster together.
 * Very short values (< 4 chars after normalising) are ignored as too weak to
 * be a reliable key. Each cluster notes whether the surnames also match, which
 * separates near-certain duplicates from coincidental cross-system number
 * collisions to eyeball.
 *
 * Read-only. Confirmed pairs get folded in via prisoners:merge-duplicates.
 *   php artisan prisoners:audit-inmate-number-duplicates
 */
final class AuditInmateNumberDuplicates extends Command
{
    protected $signature = 'prisoners:audit-inmate-number-duplicates {--min-len=4 : Ignore inmate numbers shorter than this after normalising}';

    protected $description = 'Flag prisoner records that share an inmate/DOC/BOP number (likely duplicates).';

    public function handle(): int
    {
        $minLen = max(1, (int) $this->option('min-len'));

        $prisoners = Prisoner::query()
            ->whereNotNull('inmate_number')
            ->where('inmate_number', '!=', '')
            ->get(['id', 'name', 'last_name', 'inmate_number', 'slug', 'state']);

        $byNumber = $prisoners->groupBy(function ($p) {
            return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $p->inmate_number));
        })->filter(fn ($g, $key) => strlen($key) >= $minLen && $g->count() > 1);

        if ($byNumber->isEmpty()) {
            $this->info('Scanned '.$prisoners->count().' prisoners with an inmate number; no shared-number clusters found.');

            return self::SUCCESS;
        }

        $clusters = 0;
        foreach ($byNumber as $key => $group) {
            $clusters++;
            $lastNames = $group->map(fn ($p) => strtolower(trim((string) $p->last_name)))->filter()->unique();
            $sameSurname = $lastNames->count() <= 1 && $lastNames->isNotEmpty();
            $tag = $sameSurname ? 'HIGH (same surname)' : 'REVIEW (surnames differ — possible cross-system collision)';

            $this->line('');
            $this->info("#{$key}  [{$tag}]");
            foreach ($group as $p) {
                $this->line('   '.str_pad('#'.$p->inmate_number, 16)
                    .' '.str_pad($p->name, 34)
                    .' /prisoner/'.$p->slug
                    .($p->state ? '  ('.$p->state.')' : ''));
            }
        }

        $this->line('');
        $this->info('Scanned '.$prisoners->count().' prisoners with an inmate number; '
            ."{$clusters} shared-number cluster(s) flagged.");
        $this->line('Confirm each, then add the pair(s) to prisoners:merge-duplicates.');

        return self::SUCCESS;
    }
}
