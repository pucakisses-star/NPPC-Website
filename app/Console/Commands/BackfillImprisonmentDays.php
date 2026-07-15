<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Backfills imprisoned_for_days on cases that have both an incarceration date
 * and a release date but no computed duration. Only computes when both dates
 * are day-precision and the release is after the incarceration, so year/month
 * placeholders and reversed dates are skipped rather than producing a bogus
 * number. Idempotent (only fills empty values). Run with --dry to preview.
 */
final class BackfillImprisonmentDays extends Command
{
    protected $signature = 'prisoners:backfill-imprisonment-days {--dry : Preview without writing}';

    protected $description = 'Compute imprisoned_for_days from day-precision incarceration/release dates';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $set = 0;
        $skipPrecision = 0;
        $skipOrder = 0;

        PrisonerCase::query()
            ->whereNotNull('incarceration_date')
            ->whereNotNull('release_date')
            ->where(fn ($q) => $q->whereNull('imprisoned_for_days')->orWhere('imprisoned_for_days', 0))
            ->orderBy('id')
            ->chunkById(500, function ($cases) use ($dry, &$set, &$skipPrecision, &$skipOrder) {
                foreach ($cases as $c) {
                    $inc = $c->incarceration_date;
                    $rel = $c->release_date;
                    if (! $inc || ! $rel) {
                        continue;
                    }

                    if (! $this->dayPrecise($c, 'incarceration_date', $inc) || ! $this->dayPrecise($c, 'release_date', $rel)) {
                        $skipPrecision++;

                        continue;
                    }

                    $days = (int) $inc->diffInDays($rel, false); // signed: rel - inc
                    if ($days <= 0) {
                        $skipOrder++;

                        continue;
                    }

                    if (! $dry) {
                        PrisonerCase::whereKey($c->getKey())->update(['imprisoned_for_days' => $days]);
                    }
                    $set++;
                }
            });

        $verb = $dry ? 'Would set' : 'Set';
        $this->info("{$verb} imprisoned_for_days on {$set} case(s).");
        $this->line("  skipped (not day-precision):        {$skipPrecision}");
        $this->line("  skipped (release before incarcer.): {$skipOrder}");

        if (! $dry && $set > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        return self::SUCCESS;
    }

    /**
     * True if the field is day-precision. If precision is explicitly recorded,
     * require 'day'; if it is unrecorded, accept unless the date is on Jan 1
     * (a common year-only placeholder).
     */
    private function dayPrecise(PrisonerCase $c, string $field, \Illuminate\Support\Carbon $date): bool
    {
        $prec = $c->date_precision[$field] ?? null;
        if ($prec !== null) {
            return $prec === 'day';
        }

        return ! ($date->month === 1 && $date->day === 1);
    }
}
