<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes the Colin Ferguson prisoner entry. Colin Ferguson is the perpetrator
 * of the 1993 Long Island Rail Road mass shooting — a hate-motivated spree
 * killing of civilians, not a political prisoner. The record had been added
 * (likely via an over-broad import) and mistagged "Black Liberation," which is
 * both factually wrong and reputationally harmful. This deletes his record and
 * any associated cases, calendar entries, and podcast episodes, plus his photo
 * file. Idempotent: does nothing if he's already gone.
 */
final class RemoveColinFerguson extends Command
{
    protected $signature = 'prisoners:remove-colin-ferguson';

    protected $description = 'Remove the Colin Ferguson entry (LIRR mass shooter — not a political prisoner)';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'colin-ferguson')
            ->orWhere('name', 'Colin Ferguson')
            ->first();

        if (! $prisoner) {
            $this->info('No Colin Ferguson record found — nothing to remove.');

            return self::SUCCESS;
        }

        $this->warn("Removing: {$prisoner->name} (ID: {$prisoner->id}, slug: {$prisoner->slug})");

        if ($prisoner->photo && Storage::disk('public')->exists($prisoner->photo)) {
            Storage::disk('public')->delete($prisoner->photo);
            $this->line("Deleted photo: {$prisoner->photo}");
        }

        $cases = $prisoner->cases()->count();
        $calendar = $prisoner->calendarEntries()->count();
        $episodes = $prisoner->podcastEpisodes()->count();

        $prisoner->cases()->delete();
        $prisoner->calendarEntries()->delete();
        $prisoner->podcastEpisodes()->delete();
        $prisoner->delete();

        $this->info("Removed Colin Ferguson and {$cases} case(s), {$calendar} calendar entry(ies), {$episodes} podcast episode(s).");

        return self::SUCCESS;
    }
}
