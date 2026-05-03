<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\PodcastEpisode;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeKnownDuplicates extends Command
{
    protected $signature = 'prisoners:merge-known-duplicates {--dry-run : Show what would happen without writing}';
    protected $description = 'Merge the 7 confirmed duplicate prisoner pairs: combine bios (no info lost), merge unique fields, move all cases / podcasts / calendar entries to the survivor, and delete the duplicate.';

    /**
     * Each entry: survivor slug → duplicate slug to be merged in and deleted.
     * Survivor was chosen as the entry with the lower sort_order or richer
     * existing content per the original 583-record dump.
     */
    private const PAIRS = [
        ['keith-mchenry',          'keith-keith-mchenry'],
        ['jeremy-hinzman',         'jeremy-hinzman-2'],
        ['kevin-kjonaas',          'kevin-kjonaas-2'],
        ['christopher-trotter',    'christopher-naeem-trotter'],
        ['roy-bourgeois',          'roy-bourgeois-2'],
        ['alberto-rodriguez',      'alberto-rodriguez-2'],
        ['joshua-williams',        'josh-williams'],
    ];

    public function handle(): int
    {
        $merged = 0;
        $skipped = 0;
        $errors = 0;

        foreach (self::PAIRS as [$survivorSlug, $duplicateSlug]) {
            try {
                $result = $this->mergePair($survivorSlug, $duplicateSlug);
                if ($result === 'merged')  $merged++;
                if ($result === 'skipped') $skipped++;
            } catch (\Throwable $e) {
                $this->error("  [{$survivorSlug} <- {$duplicateSlug}] failed: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->line('');
        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
        }
        $this->info("Done. Merged: {$merged}; Skipped: {$skipped}; Errors: {$errors}");

        return self::SUCCESS;
    }

    private function mergePair(string $survivorSlug, string $duplicateSlug): string
    {
        $survivor  = Prisoner::where('slug', $survivorSlug)->first();
        $duplicate = Prisoner::where('slug', $duplicateSlug)->first();

        if (! $survivor) {
            $this->warn("  [{$survivorSlug} <- {$duplicateSlug}] survivor not found, skipping");
            return 'skipped';
        }

        if (! $duplicate) {
            $this->line("  [{$survivorSlug} <- {$duplicateSlug}] duplicate already gone, nothing to do");
            return 'skipped';
        }

        $survivorBio  = trim((string) $survivor->description);
        $duplicateBio = trim((string) $duplicate->description);

        // Decide bio merge
        $newBio = $survivorBio;
        $bioAction = 'kept';
        if ($duplicateBio !== '' && ! $this->essentiallyContains($survivorBio, $duplicateBio)) {
            $newBio = $survivorBio === ''
                ? $duplicateBio
                : ($survivorBio."\n\n".$duplicateBio);
            $bioAction = 'concatenated';
        } elseif ($duplicateBio !== '') {
            $bioAction = 'duplicate-already-contained';
        }

        // Decide field-level merges (only fill if survivor is empty)
        $fillFromDup = [];
        foreach (['aka', 'birthdate', 'death_date', 'race', 'state', 'address',
                  'photo', 'inmate_number', 'website', 'twitter', 'facebook',
                  'instagram', 'gender', 'middle_name', 'first_name', 'last_name'] as $f) {
            $surv = $survivor->getAttribute($f);
            $dup  = $duplicate->getAttribute($f);
            if ($this->isEmpty($surv) && ! $this->isEmpty($dup)) {
                $fillFromDup[$f] = $dup;
            }
        }

        // Merge array fields
        foreach (['ideologies', 'affiliation'] as $f) {
            $a = is_array($survivor->{$f}) ? $survivor->{$f} : [];
            $b = is_array($duplicate->{$f}) ? $duplicate->{$f} : [];
            $merged = array_values(array_unique(array_merge($a, $b)));
            if (! empty($merged) && $merged !== ($survivor->{$f} ?? [])) {
                $fillFromDup[$f] = $merged;
            }
        }

        $caseCount     = PrisonerCase::where('prisoner_id', $duplicate->id)->count();
        $podcastCount  = PodcastEpisode::where('prisoner_id', $duplicate->id)->count();
        $calendarCount = CalendarEntry::where('prisoner_id', $duplicate->id)->count();

        $survivorBioLen  = strlen($survivorBio);
        $newBioLen       = strlen($newBio);

        $this->info("  [{$survivor->name}] <- [{$duplicate->name}]");
        $this->line("    bio: {$bioAction} ({$survivorBioLen} -> {$newBioLen} chars)");
        $this->line("    fields filled from duplicate: ".(empty($fillFromDup) ? 'none' : implode(', ', array_keys($fillFromDup))));
        $this->line("    cases to move: {$caseCount}, podcast episodes: {$podcastCount}, calendar entries: {$calendarCount}");

        if ($this->option('dry-run')) {
            return 'merged';
        }

        DB::transaction(function () use ($survivor, $duplicate, $newBio, $fillFromDup) {
            $survivor->description = $newBio;
            foreach ($fillFromDup as $field => $value) {
                $survivor->setAttribute($field, $value);
            }
            $survivor->save();

            PrisonerCase::where('prisoner_id', $duplicate->id)
                ->update(['prisoner_id' => $survivor->id]);

            PodcastEpisode::where('prisoner_id', $duplicate->id)
                ->update(['prisoner_id' => $survivor->id]);

            CalendarEntry::where('prisoner_id', $duplicate->id)
                ->update(['prisoner_id' => $survivor->id]);

            $duplicate->delete();
        });

        return 'merged';
    }

    private function isEmpty($v): bool
    {
        if ($v === null || $v === '') return true;
        if (is_array($v) && count($v) === 0) return true;
        return false;
    }

    /**
     * Treat the duplicate bio as already contained when the survivor's
     * normalized bio includes the entire duplicate bio (after collapsing
     * whitespace), to avoid duplicating identical text.
     */
    private function essentiallyContains(string $haystack, string $needle): bool
    {
        $norm = fn ($s) => preg_replace('/\s+/', ' ', strtolower(trim($s)));
        return str_contains($norm($haystack), $norm($needle));
    }
}
