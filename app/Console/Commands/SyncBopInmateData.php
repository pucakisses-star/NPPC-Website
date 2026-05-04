<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncBopInmateData extends Command
{
    protected $signature = 'prisoners:sync-bop {--dry-run : Print changes without writing}';
    protected $description = 'Look up every prisoner with a BOP-style inmate number against the federal inmate locator and patch missing race / gender / release date / released flag.';

    private const BOP_ENDPOINT = 'https://www.bop.gov/PublicInfo/execute/inmateloc';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $prisoners = Prisoner::whereNotNull('inmate_number')
            ->where('inmate_number', '!=', '')
            ->whereRaw("inmate_number REGEXP '^[0-9]{5}-[0-9]{3}$'")
            ->orderBy('name')
            ->get();

        $this->info("Looking up {$prisoners->count()} BOP-format inmate numbers…");

        $hit = 0; $miss = 0; $patched = 0; $skipped = 0;

        foreach ($prisoners as $prisoner) {
            $row = $this->lookup($prisoner->inmate_number);

            if (! $row) {
                $this->warn("[{$prisoner->inmate_number}] {$prisoner->name} — no BOP record");
                $miss++;
                continue;
            }

            $hit++;

            $changes = [];
            $caseChanges = [];

            // Gender
            if (empty($prisoner->gender) && ! empty($row['sex'])) {
                $changes['gender'] = $row['sex'];
            }

            // Race — map "American Indian" → "Native American"
            if (empty($prisoner->race) && ! empty($row['race'])) {
                $race = $row['race'];
                if ($race === 'American Indian') $race = 'Native American';
                $changes['race'] = $race;
            }

            // Released flag + in_custody flag
            $actRelDate = $row['actRelDate'] ?? '';
            $isReleased = $actRelDate !== '' || ($row['releaseCode'] ?? '') === 'R';

            if ($isReleased) {
                if (! $prisoner->released) $changes['released'] = true;
                if ($prisoner->in_custody) $changes['in_custody'] = false;
            }

            // Release date on most recent case
            if ($actRelDate) {
                try {
                    $iso = Carbon::createFromFormat('m/d/Y', $actRelDate)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $iso = null;
                }

                if ($iso) {
                    $case = $prisoner->cases()
                        ->orderByRaw('incarceration_date IS NULL, incarceration_date DESC')
                        ->first();

                    if ($case && empty($case->release_date)) {
                        $caseChanges = ['release_date' => $iso, 'case_id' => $case->id];
                    }
                }
            }

            if (empty($changes) && empty($caseChanges)) {
                $this->line("[{$prisoner->inmate_number}] {$prisoner->name} — already current");
                $skipped++;
                continue;
            }

            $summary = collect($changes)->map(fn ($v, $k) => "{$k}=" . (is_bool($v) ? ($v ? 'true' : 'false') : $v))->implode(', ');
            if (! empty($caseChanges)) {
                $summary .= ($summary ? '; ' : '') . "case.release_date={$caseChanges['release_date']}";
            }
            $this->info("[{$prisoner->inmate_number}] {$prisoner->name} — {$summary}");

            if ($dryRun) continue;

            DB::transaction(function () use ($prisoner, $changes, $caseChanges) {
                if (! empty($changes)) {
                    foreach ($changes as $k => $v) $prisoner->{$k} = $v;
                    $prisoner->save();
                }
                if (! empty($caseChanges)) {
                    PrisonerCase::where('id', $caseChanges['case_id'])
                        ->update(['release_date' => $caseChanges['release_date']]);
                }
            });

            $patched++;

            // Be polite to the BOP server
            usleep(250 * 1000);
        }

        $this->info("\nDone. BOP hits: {$hit}, misses: {$miss}, patched: {$patched}, no-op: {$skipped}" . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }

    private function lookup(string $inmateNum): ?array
    {
        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'NPPC-website/1.0 (https://nppc.org; contact@nppc.org)',
                'Accept'     => 'application/json',
            ])->timeout(15)->get(self::BOP_ENDPOINT, [
                'todo'      => 'query',
                'output'    => 'json',
                'inmateNum' => $inmateNum,
            ]);

            if (! $resp->successful()) return null;
            $body = $resp->json();
            $rows = $body['InmateLocator'] ?? [];
            return $rows[0] ?? null;
        } catch (\Throwable $e) {
            $this->warn("HTTP error for {$inmateNum}: {$e->getMessage()}");
            return null;
        }
    }
}
