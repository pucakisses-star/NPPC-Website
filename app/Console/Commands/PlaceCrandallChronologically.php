<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PlaceCrandallChronologically extends Command
{
    protected $signature = 'prisoners:place-crandall {--dry-run : Show what would change without writing}';
    protected $description = 'Move Reuben Crandall down to sit chronologically with other early-19th-century prisoners.';

    public function handle(): int
    {
        $crandall = Prisoner::where('name', 'like', '%Crandall%')->first();

        if (! $crandall) {
            $this->error('No prisoner found whose name contains "Crandall".');
            return self::FAILURE;
        }

        if (! $crandall->birthdate) {
            $this->error("{$crandall->name} has no birthdate set; cannot place chronologically.");
            return self::FAILURE;
        }

        $this->info("Found: {$crandall->name} (birthdate {$crandall->birthdate->toDateString()}, current sort_order {$crandall->sort_order})");

        // Find the prisoner who should sit immediately before Crandall:
        // the one with the latest birthdate that is still earlier than his.
        $previous = Prisoner::where('id', '!=', $crandall->id)
            ->whereNotNull('birthdate')
            ->where('birthdate', '<=', $crandall->birthdate)
            ->orderBy('birthdate', 'desc')
            ->orderBy('sort_order', 'desc')
            ->first();

        // And the one who should sit immediately after.
        $next = Prisoner::where('id', '!=', $crandall->id)
            ->whereNotNull('birthdate')
            ->where('birthdate', '>', $crandall->birthdate)
            ->orderBy('birthdate', 'asc')
            ->orderBy('sort_order', 'asc')
            ->first();

        if (! $previous) {
            $this->warn('No earlier-born prisoner found. Crandall is already the chronologically earliest — nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Previous chronologically: {$previous->name} (birthdate {$previous->birthdate->toDateString()}, sort_order {$previous->sort_order})");
        if ($next) {
            $this->info("Next chronologically:     {$next->name} (birthdate {$next->birthdate->toDateString()}, sort_order {$next->sort_order})");
        } else {
            $this->info('No later-born prisoner found; Crandall will go at the end.');
        }

        $targetSortOrder = $previous->sort_order + 1;

        // If `next` already has sort_order > targetSortOrder, no shift needed.
        // Otherwise, shift everyone with sort_order >= targetSortOrder by 1
        // (excluding Crandall himself).
        $needsShift = Prisoner::where('id', '!=', $crandall->id)
            ->where('sort_order', '>=', $targetSortOrder)
            ->where('sort_order', '<=', $crandall->sort_order)
            ->exists();

        $this->info("Plan: set {$crandall->name}->sort_order = {$targetSortOrder}".($needsShift ? ' (and shift conflicting rows by +1)' : ''));

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($crandall, $targetSortOrder, $needsShift) {
            if ($needsShift) {
                Prisoner::where('id', '!=', $crandall->id)
                    ->where('sort_order', '>=', $targetSortOrder)
                    ->where('sort_order', '<', $crandall->sort_order)
                    ->increment('sort_order');
            }

            $crandall->sort_order = $targetSortOrder;
            $crandall->save();
        });

        $this->info("Done. {$crandall->name} now at sort_order {$targetSortOrder}.");

        return self::SUCCESS;
    }
}
