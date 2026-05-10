<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * For each prisoner whose `address` is empty, look at their most
 * recent case's institution and copy that institution's
 * physical_address (preferred) or mailing_address (fallback) into
 * the prisoner row's `address` field. The Prisoner saving hook
 * then geocodes the address into lat/lng on the next save, but we
 * also surface a follow-up command to re-run the explicit
 * coordinate backfill in one shot.
 *
 * Idempotent: only writes when the prisoner has no existing
 * address, so re-runs are no-ops.
 */
class PromoteInstitutionAddressToPrisoner extends Command
{
    protected $signature = 'prisoners:promote-institution-address
                            {--in-custody-only : Only operate on prisoners currently in custody}
                            {--dry-run : Print plan without writing}';

    protected $description = 'Copy each prisoner\'s most recent case institution address onto the prisoner row.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $inCustodyOnly = (bool) $this->option('in-custody-only');

        $q = Prisoner::query()
            ->where(function ($q) {
                $q->whereNull('address')->orWhere('address', '');
            })
            ->whereHas('cases.institution', function ($q) {
                $q->where(function ($q) {
                    $q->whereNotNull('physical_address')->where('physical_address', '!=', '')
                      ->orWhereNotNull('mailing_address')->where('mailing_address', '!=', '');
                });
            });

        if ($inCustodyOnly) $q->where('in_custody', true);

        $rows = $q->with(['cases' => fn ($q) => $q->latest()->with('institution')])->get();
        $this->info(sprintf('Candidates: %d  (dry-run=%s)', $rows->count(), $dryRun ? 'yes' : 'no'));

        $written = 0; $skipped = 0;
        foreach ($rows as $p) {
            $address = null;
            foreach ($p->cases as $case) {
                $inst = $case->institution;
                if (! $inst) continue;
                $address = trim((string) $inst->physical_address) ?: trim((string) $inst->mailing_address) ?: null;
                if ($address) break;
            }
            if (! $address) { $skipped++; continue; }

            $this->line(sprintf('  %s -> %s', $p->name, str_replace("\n", ' / ', $address)));
            if (! $dryRun) {
                $p->address = $address;
                $p->save();
            }
            $written++;
        }

        $this->info(sprintf("\nDone. wrote=%d, skipped=%d (dry-run=%s)", $written, $skipped, $dryRun ? 'yes' : 'no'));
        $this->info('Next: php artisan prisoners:backfill-coordinates');
        return self::SUCCESS;
    }
}
