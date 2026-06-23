<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Fills missing case release dates by looking up federal BOP register numbers
 * against the Bureau of Prisons public inmate locator. For every prisoner whose
 * inmate_number matches the BOP format (NNNNN-NNN) and who has a case with no
 * release_date, it queries the locator and — only when the locator reports an
 * ACTUAL release date (actRelDate) and the surname matches — writes that date.
 *
 * Safety: it verifies the locator's last name against the record before writing,
 * never fills a projected release date for someone still in custody, and is
 * idempotent (cases that already have a release date are skipped). Use --dry-run
 * to preview, --limit to cap how many to process, and --sleep (ms) to throttle.
 */
final class FillBopReleaseDates extends Command
{
    protected $signature = 'prisoners:fill-bop-release-dates {--dry-run : Preview without saving} {--limit=0 : Max prisoners to process (0 = all)} {--sleep=700 : Milliseconds to wait between BOP requests}';

    protected $description = 'Fill missing release dates from the federal BOP inmate locator (by register number)';

    private const BOP_URL = 'https://www.bop.gov/PublicInfo/execute/inmateloc';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $sleepMs = (int) $this->option('sleep');

        // Pull everyone with an inmate number, then keep only BOP-format ones
        // (NNNNN-NNN) in PHP — avoids DB-specific REGEXP so it runs the same on
        // SQLite and MySQL.
        $prisoners = Prisoner::withUnderReview()
            ->whereNotNull('inmate_number')
            ->where('inmate_number', '!=', '')
            ->with('cases')
            ->get()
            ->filter(fn ($p) => preg_match('/^\d{5}-\d{3}$/', trim((string) $p->inmate_number)))
            ->values();

        $filled = 0;
        $stillIn = 0;
        $noRecord = 0;
        $nameMismatch = 0;
        $noCaseToFill = 0;
        $errors = 0;
        $processed = 0;

        foreach ($prisoners as $prisoner) {
            $case = $prisoner->cases->first(fn ($c) => empty($c->release_date));
            if (! $case) {
                continue; // nothing missing a release date
            }

            if ($limit > 0 && $processed >= $limit) {
                break;
            }
            $processed++;

            $num = trim($prisoner->inmate_number);

            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (NPPC release-date backfill)'])
                    ->timeout(30)
                    ->get(self::BOP_URL, [
                        'todo' => 'query',
                        'output' => 'json',
                        'inmateNumType' => 'IRN',
                        'inmateNum' => $num,
                    ]);
            } catch (\Throwable $e) {
                $this->warn("  HTTP error for {$num} ({$prisoner->name}): ".$e->getMessage());
                $errors++;
                $this->throttle($sleepMs);

                continue;
            }

            $rows = $resp->json('InmateLocator') ?? [];
            $row = collect($rows)->first(fn ($r) => ($r['inmateNum'] ?? '') === $num) ?? ($rows[0] ?? null);

            if (! $row) {
                $this->line("  no BOP record: {$num} ({$prisoner->name})");
                $noRecord++;
                $this->throttle($sleepMs);

                continue;
            }

            // Verify the surname matches before trusting the record.
            if (! $this->surnameMatches($prisoner, (string) ($row['nameLast'] ?? ''))) {
                $this->warn("  NAME MISMATCH for {$num}: record='{$prisoner->name}' vs BOP='".
                    trim(($row['nameFirst'] ?? '').' '.($row['nameLast'] ?? ''))."' — skipping");
                $nameMismatch++;
                $this->throttle($sleepMs);

                continue;
            }

            $actRel = trim((string) ($row['actRelDate'] ?? ''));
            if ($actRel === '') {
                $proj = trim((string) ($row['projRelDate'] ?? ''));
                $this->line("  still in custody (no actual release; projected={$proj}): {$prisoner->name} @ ".($row['faclName'] ?? '?'));
                $stillIn++;
                $this->throttle($sleepMs);

                continue;
            }

            try {
                $date = Carbon::createFromFormat('m/d/Y', $actRel)->format('Y-m-d');
            } catch (\Throwable $e) {
                $this->warn("  unparseable actRelDate '{$actRel}' for {$prisoner->name}");
                $errors++;
                $this->throttle($sleepMs);

                continue;
            }

            if ($dryRun) {
                $this->line("  would set release_date={$date}: {$prisoner->name} ({$num})");
            } else {
                $case->release_date = $date;
                $case->save();
                $this->info("  {$date}  ←  {$prisoner->name} ({$num})");
            }
            $filled++;

            $this->throttle($sleepMs);
        }

        $this->info("\nDone".($dryRun ? ' (dry run)' : '').". filled={$filled} stillInCustody={$stillIn} "
            ."noBopRecord={$noRecord} nameMismatch={$nameMismatch} httpErrors={$errors}");

        return self::SUCCESS;
    }

    private function throttle(int $ms): void
    {
        if ($ms > 0) {
            usleep($ms * 1000);
        }
    }

    private function surnameMatches(Prisoner $prisoner, string $bopLast): bool
    {
        $norm = fn (string $s) => preg_replace('/[^a-z]/', '', strtolower(
            \Illuminate\Support\Str::ascii($s)
        ));

        $bop = $norm($bopLast);
        if ($bop === '') {
            return false;
        }
        // Match BOP surname (or each hyphen-separated part) against the full name.
        $fullName = $norm((string) $prisoner->name).$norm((string) $prisoner->last_name);
        foreach (preg_split('/[-\s]+/', strtolower($bopLast)) as $part) {
            $p = $norm($part);
            if ($p !== '' && str_contains($fullName, $p)) {
                return true;
            }
        }

        return str_contains($fullName, $bop);
    }
}
