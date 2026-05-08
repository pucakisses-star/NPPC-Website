<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * For every prisoner that has a BOP register number (`inmate_number`)
 * but no race recorded, query the public BOP inmate locator by that
 * register number and patch the prisoner's `race` from the BOP record.
 *
 * BOP returns race values like "White", "Black", "Asian",
 * "American Indian", "Native Hawaiian/Pacific Islander". We write the
 * value through unchanged.
 */
class LookupBopRaceByNumber extends Command
{
    protected $signature   = 'prisoners:lookup-bop-race {--dry-run : Print changes without writing} {--delay=3.0 : Seconds to sleep after every BOP request (jittered ±25%)}';
    protected $description = 'Fill in missing race for prisoners that have a BOP register number, by querying the BOP inmate locator.';

    private const BOP_ENDPOINT = 'https://www.bop.gov/PublicInfo/execute/inmateloc';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $delay  = max(0.0, (float) $this->option('delay'));

        $prisoners = Prisoner::whereNotNull('inmate_number')
            ->where('inmate_number', '!=', '')
            ->where(function ($q) {
                $q->whereNull('race')->orWhere('race', '');
            })
            ->orderBy('name')
            ->get();

        $this->info("Scanning {$prisoners->count()} prisoners with a BOP register number and no race…");

        $stats = ['patched' => 0, 'noResults' => 0, 'noRace' => 0, 'numberMismatch' => 0, 'errors' => 0];

        foreach ($prisoners as $prisoner) {
            $num = trim((string) $prisoner->inmate_number);
            $results = $this->searchByNumber($num);

            if ($delay > 0) {
                $jitter = $delay * (0.75 + (mt_rand() / mt_getrandmax()) * 0.5);
                usleep((int) round($jitter * 1_000_000));
            }

            if ($results === null) {
                $stats['errors']++;
                continue;
            }
            if (count($results) === 0) {
                $this->line("[no BOP results]   {$prisoner->name} ({$num})");
                $stats['noResults']++;
                continue;
            }

            // Defensive: the BOP search by inmateNum should return at most one
            // record, but make sure the returned register number matches.
            $row = collect($results)->first(fn ($r) => ($r['inmateNum'] ?? '') === $num) ?? $results[0];
            if (($row['inmateNum'] ?? '') !== $num) {
                $this->warn("[{$prisoner->name}] BOP returned a different register number ({$row['inmateNum']}) — skipping");
                $stats['numberMismatch']++;
                continue;
            }

            $race = trim((string) ($row['race'] ?? ''));
            if ($race === '') {
                $this->line("[no race field]    {$prisoner->name} ({$num})");
                $stats['noRace']++;
                continue;
            }

            $this->info("[patched]          {$prisoner->name} ({$num}) — race={$race}");

            if ($dryRun) {
                $stats['patched']++;
                continue;
            }

            DB::transaction(function () use ($prisoner, $race) {
                $prisoner->race = $race;
                $prisoner->save();
            });

            $stats['patched']++;
        }

        $this->info("\nDone."
            . " patched={$stats['patched']},"
            . " noResults={$stats['noResults']},"
            . " noRace={$stats['noRace']},"
            . " numberMismatch={$stats['numberMismatch']},"
            . " errors={$stats['errors']}"
            . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }

    /**
     * @return array<int,array>|null
     */
    private function searchByNumber(string $inmateNum): ?array
    {
        try {
            $resp = Http::withHeaders([
                'User-Agent'       => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
                'Accept'           => 'application/json, text/plain, */*',
                'Accept-Language'  => 'en-US,en;q=0.9',
                'Referer'          => 'https://www.bop.gov/inmateloc/',
                'X-Requested-With' => 'XMLHttpRequest',
            ])->timeout(20)->get(self::BOP_ENDPOINT, [
                'todo'      => 'query',
                'output'    => 'json',
                'inmateNum' => $inmateNum,
            ]);

            if (! $resp->successful()) {
                $this->warn("HTTP {$resp->status()} for {$inmateNum}");
                return null;
            }
            $body = $resp->json();
            return $body['InmateLocator'] ?? [];
        } catch (\Throwable $e) {
            $this->warn("HTTP error for {$inmateNum}: {$e->getMessage()}");
            return null;
        }
    }
}
