<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Restore prisoner_cases rows from a JSON file recovered out of a
 * cached/saved /database HTML page. The page renders cases via
 * v-if so collapsed cases were never in the DOM; we asked the
 * user to expand all cards (one console snippet) and re-save the
 * page, then parsed it offline into JSON. This command imports
 * that JSON.
 *
 * The JSON is at database/data/recovered_cases_2026_05_10.json
 * by default; pass --json=PATH to override. Each entry has:
 *   slug, name, inmate_number, days_imprisoned, days_in_exile,
 *   cases: [ {Arrested, Convicted, Released, Charges, Sentence,
 *             Institution Name, Institution City, State, Judge,
 *             Prosecutor, Plead, Indicted, Sentenced} ]
 *
 * Idempotent: matches an existing case on (prisoner_id, arrest_date,
 * institution_name) and updates it; otherwise creates a new row.
 * Re-running after a successful import is a no-op.
 */
class RestoreCasesFromRecoveredJson extends Command
{
    protected $signature = 'cases:restore-from-recovered-json
                            {--json=database/data/recovered_cases_2026_05_10.json : path to the recovered JSON}
                            {--dry-run : Print plan without writing}
                            {--limit= : Only process the first N prisoners (for testing)}';

    protected $description = 'Restore prisoner_cases from JSON parsed from a saved /database HTML page.';

    public function handle(): int
    {
        $path = $this->option('json');
        if (! str_starts_with($path, '/')) {
            $path = base_path($path);
        }
        if (! is_readable($path)) {
            $this->error("JSON not readable at: {$path}");
            return self::FAILURE;
        }

        $records = json_decode((string) file_get_contents($path), true);
        if (! is_array($records)) {
            $this->error('JSON failed to decode or is not an array.');
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit  = $this->option('limit') ? (int) $this->option('limit') : null;
        if ($limit) $records = array_slice($records, 0, $limit);

        $this->info(sprintf('Records to process: %d  (dry-run=%s)', count($records), $dryRun ? 'yes' : 'no'));

        $stats = [
            'prisoners_matched'   => 0,
            'prisoners_unmatched' => 0,
            'cases_created'       => 0,
            'cases_updated'       => 0,
            'cases_skipped_empty' => 0,
            'institutions_created'=> 0,
        ];
        $bar = $this->output->createProgressBar(count($records));

        foreach ($records as $rec) {
            $bar->advance();

            $prisoner = Prisoner::where('slug', $rec['slug'])->first();
            if (! $prisoner) {
                $stats['prisoners_unmatched']++;
                continue;
            }
            $stats['prisoners_matched']++;

            $caseInputs = $rec['cases'] ?? [];
            // Stub case for prisoners that only have days but no
            // expanded detail. The HTML save path captured days
            // for ~777 prisoners; many but not all overlap with
            // the 1,938 with full detail. Fill the gap with a
            // minimal row so calculatedPunishment renders.
            if (empty($caseInputs) && ! empty($rec['days_imprisoned'])) {
                $caseInputs = [['__stub__' => true]];
            }
            if (empty($caseInputs) && ! empty($rec['days_in_exile'])) {
                $caseInputs[] = ['__stub__' => true];
            }

            foreach ($caseInputs as $c) {
                $arrest         = $this->parseDate($c['Arrested']     ?? null);
                $sentencedDate  = $this->parseDate($c['Sentenced']    ?? null);
                $release        = $this->parseDate($c['Released']     ?? null);

                $institutionId = null;
                $instName = $this->cleanText($c['Institution Name'] ?? null);
                if ($instName) {
                    $instCity   = $this->cleanText($c['Institution City'] ?? null);
                    $instState  = $this->cleanText($c['State'] ?? null);
                    $instSec    = $this->cleanText($c['Institution Security'] ?? null);
                    $instMail   = $this->cleanText($c['Mailing Address'] ?? null);
                    $instPhys   = $this->cleanText($c['Physical Address'] ?? null);
                    $institution = Institution::firstOrNew(['name' => $instName]);
                    $created = ! $institution->exists;
                    if ($instCity   && ! $institution->city)             $institution->city = $instCity;
                    if ($instState  && ! $institution->state)            $institution->state = $instState;
                    if ($instSec    && ! $institution->security)         $institution->security = $instSec;
                    if ($instMail   && ! $institution->mailing_address)  $institution->mailing_address = $instMail;
                    if ($instPhys   && ! $institution->physical_address) $institution->physical_address = $instPhys;
                    if (! $dryRun) $institution->save();
                    if ($created)  $stats['institutions_created']++;
                    $institutionId = $institution->id;
                }

                // Match an existing case by (prisoner, arrest_date,
                // institution). Falls back to (prisoner, charges) so
                // dateless cases still dedupe across re-runs.
                $charges = $this->cleanText($c['Charges'] ?? null);
                $existing = null;
                $q = PrisonerCase::where('prisoner_id', $prisoner->id);
                if ($arrest)        $q->where('arrest_date', $arrest);
                if ($institutionId) $q->where('institution_id', $institutionId);
                $existing = $q->first();
                if (! $existing && $charges) {
                    $existing = PrisonerCase::where('prisoner_id', $prisoner->id)
                        ->where('charges', $charges)->first();
                }

                $isStub = ! empty($c['__stub__']);
                $payload = [
                    'prisoner_id'        => $prisoner->id,
                    'institution_id'     => $institutionId,
                    'charges'            => $charges,
                    'arrest_date'        => $arrest,
                    'sentenced_date'     => $sentencedDate,
                    'incarceration_date' => $arrest ?: $sentencedDate, // best-available start
                    'release_date'       => $release,
                    'indicted'           => $this->cleanText($c['Indicted']   ?? null),
                    'convicted'          => $this->cleanText($c['Convicted']  ?? null),
                    'plead'              => $this->cleanText($c['Plead']      ?? null),
                    'prosecutor'         => $this->cleanText($c['Prosecutor'] ?? null),
                    'judge'              => $this->cleanText($c['Judge']      ?? null),
                    'sentence'           => $this->cleanText($c['Sentence']   ?? null),
                    'imprisoned_for_days'=> $rec['days_imprisoned'] ?? null,
                    'in_exile_for_days'  => $rec['days_in_exile']   ?? null,
                ];
                if ($isStub) {
                    // Stub-only rows store nothing but the days
                    // counters so calculatedPunishment renders.
                    foreach (['charges','arrest_date','sentenced_date','incarceration_date','release_date','indicted','convicted','plead','prosecutor','judge','sentence'] as $k) {
                        $payload[$k] = null;
                    }
                    if (empty($payload['imprisoned_for_days']) && empty($payload['in_exile_for_days'])) {
                        $stats['cases_skipped_empty']++;
                        continue;
                    }
                }

                $payload = array_filter($payload, fn ($v) => $v !== null && $v !== '');

                if ($existing) {
                    if (! $dryRun) {
                        foreach ($payload as $k => $v) {
                            if ($k === 'prisoner_id') continue;
                            // Don't overwrite a non-empty value with the same value
                            $existing->setAttribute($k, $v);
                        }
                        $existing->save();
                    }
                    $stats['cases_updated']++;
                } else {
                    if (! $dryRun) {
                        PrisonerCase::create($payload);
                    }
                    $stats['cases_created']++;
                }
            }
        }
        $bar->finish();
        $this->newLine(2);

        $this->table(['metric','value'], collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->toArray());
        if ($dryRun) $this->warn('Dry run — nothing written.');
        return self::SUCCESS;
    }

    private function cleanText(?string $v): ?string
    {
        if ($v === null) return null;
        $v = trim($v);
        return $v === '' ? null : $v;
    }

    /**
     * Parse the human-formatted dates emitted by the Vue page,
     * e.g. "Mar 12th 2025", "May 10th 2026", "Jan 2nd 2026".
     */
    private function parseDate(?string $v): ?string
    {
        if (! $v) return null;
        $v = trim($v);
        // Strip ordinal suffixes
        $v = preg_replace('/(\d+)(st|nd|rd|th)/', '$1', $v);
        try {
            return Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
