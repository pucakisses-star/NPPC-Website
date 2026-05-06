<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Walks every prisoner from the 80s-present era who is missing either
 * a federal register number or a release date on a single attached
 * case, queries the public BOP inmate locator by first and last name,
 * and - only if exactly one match comes back - patches in the
 * register number and the BOP-confirmed release date.
 *
 * Skips prisoners with more than one attached case (ambiguous which
 * case the BOP record corresponds to) and prisoners whose name search
 * returns multiple BOP results.
 */
class LookupBopByName extends Command
{
    protected $signature   = 'prisoners:lookup-bop-by-name {--dry-run : Print changes without writing}';
    protected $description = 'Search the BOP inmate locator by first/last name for 80s-present prisoners missing register number or release date; only patch on a unique match.';

    private const BOP_ENDPOINT = 'https://www.bop.gov/PublicInfo/execute/inmateloc';
    private const ERAS         = ['1980s', '1990s', '2000s', '2010s', '2020s'];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $prisoners = Prisoner::whereNotNull('first_name')
            ->whereNotNull('last_name')
            ->where('first_name', '!=', '')
            ->where('last_name',  '!=', '')
            ->whereIn('era', self::ERAS)
            ->withCount('cases')
            ->orderBy('name')
            ->get();

        $this->info("Scanning {$prisoners->count()} prisoners (eras 1980s-2020s)…");

        $stats = ['skipMulti' => 0, 'skipNoNeed' => 0, 'noResults' => 0, 'ambiguous' => 0, 'patched' => 0, 'errors' => 0];

        foreach ($prisoners as $prisoner) {
            // Skip if more than one case attached - per user request,
            // we cannot tell which case the BOP record corresponds to.
            if ($prisoner->cases_count > 1) {
                $stats['skipMulti']++;
                continue;
            }

            $case          = $prisoner->cases()->first();
            $needsInmate   = empty($prisoner->inmate_number);
            $needsRelease  = $case && empty($case->release_date);
            if (! $needsInmate && ! $needsRelease) {
                $stats['skipNoNeed']++;
                continue;
            }

            $results = $this->searchByName($prisoner->first_name, $prisoner->last_name);
            if ($results === null) {
                $stats['errors']++;
                continue;
            }
            if (count($results) === 0) {
                $this->line("[no BOP results]   {$prisoner->name}");
                $stats['noResults']++;
                continue;
            }
            if (count($results) > 1) {
                $this->line("[{$prisoner->name}] " . count($results) . " BOP results — skipping (ambiguous per user rule)");
                $stats['ambiguous']++;
                continue;
            }

            $row = $results[0];

            $changes     = [];
            $caseChanges = [];

            // Patch register number if missing
            if ($needsInmate && ! empty($row['inmateNum'])) {
                $changes['inmate_number'] = $row['inmateNum'];
            }

            // Patch release date if missing
            $actRelDate = $row['actRelDate'] ?? '';
            if ($needsRelease && $actRelDate) {
                try {
                    $iso = Carbon::createFromFormat('m/d/Y', $actRelDate)->format('Y-m-d');
                    $caseChanges['release_date'] = $iso;
                    $caseChanges['case_id']      = $case->id;
                } catch (\Throwable $e) {
                    $this->warn("[{$prisoner->name}] could not parse release date '{$actRelDate}'");
                }
            }

            // If the BOP record indicates released, flip the prisoner-level flags
            $isReleased = $actRelDate !== '' || ($row['releaseCode'] ?? '') === 'R';
            if ($isReleased) {
                if ($prisoner->in_custody) $changes['in_custody'] = false;
                if (! $prisoner->released) $changes['released']   = true;
            }

            if (empty($changes) && empty($caseChanges)) {
                $stats['skipNoNeed']++;
                continue;
            }

            $summary = collect($changes)->map(fn ($v, $k) => "{$k}=" . (is_bool($v) ? ($v ? 'true' : 'false') : $v))->implode(', ');
            if (! empty($caseChanges)) {
                $summary .= ($summary ? '; ' : '') . "case.release_date={$caseChanges['release_date']}";
            }
            $this->info("[patched]          {$prisoner->name} — {$summary}");

            if ($dryRun) {
                $stats['patched']++;
                continue;
            }

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

            $stats['patched']++;

            // Be polite to the BOP server.
            usleep(400 * 1000);
        }

        $this->info("\nDone."
            . " patched={$stats['patched']},"
            . " ambiguous={$stats['ambiguous']},"
            . " noResults={$stats['noResults']},"
            . " skipNoNeed={$stats['skipNoNeed']},"
            . " skipMulti={$stats['skipMulti']},"
            . " errors={$stats['errors']}"
            . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }

    /**
     * @return array<int,array>|null
     */
    private function searchByName(string $first, string $last): ?array
    {
        try {
            $resp = Http::withHeaders([
                'User-Agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept'          => 'application/json, text/plain, */*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer'         => 'https://www.bop.gov/inmateloc/',
                'X-Requested-With' => 'XMLHttpRequest',
            ])->timeout(20)->get(self::BOP_ENDPOINT, [
                'todo'      => 'query',
                'output'    => 'json',
                'nameFirst' => $first,
                'nameLast'  => $last,
            ]);

            if (! $resp->successful()) {
                $this->warn("HTTP {$resp->status()} for {$first} {$last}");
                return null;
            }
            $body = $resp->json();
            return $body['InmateLocator'] ?? [];
        } catch (\Throwable $e) {
            $this->warn("HTTP error for {$first} {$last}: {$e->getMessage()}");
            return null;
        }
    }
}
