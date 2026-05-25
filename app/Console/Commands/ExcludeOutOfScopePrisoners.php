<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Flags out-of-scope prisoners (white-supremacist, neo-Nazi, far-right
 * mass-violence figures) with under_review = true so the
 * NotUnderReviewScope hides them from every public-facing query.
 *
 * NPPC supports political prisoners — racist mass-violence prosecutions
 * are not in scope and shouldn't surface in the archive even if they
 * landed in the DB via a generic Airtable import.
 *
 * Idempotent. Dry-run by default; --apply writes.
 */
final class ExcludeOutOfScopePrisoners extends Command {
    protected $signature = 'prisoners:exclude-out-of-scope {--apply : Actually flag matching rows under_review}';
    protected $description = 'Hide out-of-scope prisoners (Matthew Hale, etc.) by setting under_review = true';

    /**
     * Each entry is a name OR aka substring (case-insensitive LIKE).
     * Conservative — only includes well-documented far-right / mass-
     * violence cases that have shown up in third-party PP lists by
     * confusion but are clearly outside NPPC's scope.
     */
    private array $namesAndAkas = [
        'Matthew Hale',          // World Church of the Creator / Creativity Movement
        'Matt Hale',
        'Eric Rudolph',          // Atlanta Olympic / abortion-clinic bombings
        'Timothy McVeigh',       // executed but defensive in case of legacy entry
        'Terry Nichols',         // OKC bombing co-conspirator
        'Dylann Roof',           // Charleston AME church shooting
        'Robert Bowers',         // Pittsburgh synagogue shooting
        'Wade Michael Page',     // Oak Creek Sikh temple shooting
        'Patrick Crusius',       // El Paso shooting
        'Anders Breivik',        // foreign but sometimes appears in lists
        'David Lane',            // The Order
        'Frazier Glenn Miller',  // Overland Park JCC shooting
        'Frazier Glenn Cross',
    ];

    public function handle(): int {
        $apply = (bool) $this->option('apply');

        $query = Prisoner::query()->withoutGlobalScopes();
        $query->where(function ($q) {
            foreach ($this->namesAndAkas as $needle) {
                $q->orWhere('name', 'like', "%{$needle}%")
                  ->orWhere('aka', 'like', "%{$needle}%");
            }
        });

        $matches = $query->get(['id', 'name', 'aka', 'under_review']);

        if ($matches->isEmpty()) {
            $this->info('No out-of-scope prisoners found in the DB.');
            return self::SUCCESS;
        }

        $this->info('Matched '.$matches->count().' prisoner row(s):');
        $alreadyHidden = 0;
        $toUpdate = [];
        foreach ($matches as $p) {
            if ($p->under_review) {
                $alreadyHidden++;
                $this->line("  ✓ already hidden — {$p->name}".($p->aka ? " (aka {$p->aka})" : ''));
            } else {
                $toUpdate[] = $p;
                $this->line("  → will hide   — {$p->name}".($p->aka ? " (aka {$p->aka})" : ''));
            }
        }

        if (! $apply) {
            $this->newLine();
            $this->info('(dry-run; re-run with --apply to write)');
            return self::SUCCESS;
        }

        foreach ($toUpdate as $p) {
            $p->under_review = true;
            $p->save();
        }
        $this->newLine();
        $this->info("Hid {$matches->count()} prisoner row(s) ({$alreadyHidden} already hidden, ".count($toUpdate).' newly hidden).');
        return self::SUCCESS;
    }
}
