<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Pulls the "affected institutions" dataset that powers the student-visa
 * page's data panel from the public Nimble Tent Data Viewer source — a
 * Baserow table — and caches it as JSON in the repo so the page can render
 * a native table in-house instead of embedding the third-party app.
 *
 * The Baserow table id and read-only token are the ones published in the
 * public Observable notebook (observablehq.com/embed/bf4a008daf711602);
 * nothing secret is involved. Re-run to refresh the snapshot.
 */
class SyncVisaInstitutions extends Command {
    protected $signature = 'visa:sync-institutions
                            {--token=fKgnvDagUqXg11veMrgJTnn4LQisk8OQ : Baserow read-only API token}
                            {--table=503380 : Baserow table id}
                            {--dry-run : Fetch and report totals without writing the JSON}';

    protected $description = 'Sync the affected-institutions dataset (visa revocations) from Baserow into a local JSON snapshot';

    /** Where the snapshot lives; the Blade page reads this file. */
    public const SNAPSHOT_PATH = 'resources/data/affected-institutions.json';

    public function handle(): int {
        $token = (string) $this->option('token');
        $tableId = (string) $this->option('table');
        $base = "https://api.baserow.io/api/database/rows/table/{$tableId}/";

        $this->info("Fetching affected-institutions data from Baserow table {$tableId}…");

        $rows = [];
        $page = 1;
        $count = null;

        do {
            $response = Http::withHeaders([
                'Authorization' => "Token {$token}",
                'Content-Type' => 'application/json',
            ])->timeout(60)->get($base, [
                'user_field_names' => 'true',
                'size' => 200,
                'page' => $page,
            ]);

            if (! $response->successful()) {
                $this->error("Failed to fetch page {$page}: HTTP {$response->status()}");

                return self::FAILURE;
            }

            $body = $response->json();
            $count ??= (int) ($body['count'] ?? 0);
            $results = $body['results'] ?? [];
            $rows = array_merge($rows, $results);
            $page++;
        } while (! empty($results) && count($rows) < $count && $page < 50);

        $this->info(count($rows).' rows fetched.');

        $institutions = [];
        foreach ($rows as $row) {
            $institutions[] = [
                'name' => (string) ($row['institution'] ?? ''),
                'state' => (string) ($row['state'] ?? ''),
                'affected_people' => $this->affectedPeople($row['tally'] ?? null),
                'website' => (string) ($row['website'] ?? ''),
                'wikipedia' => (string) ($row['wikipedia-url'] ?? ''),
                'latitude' => $this->coord($row['latitude'] ?? null),
                'longitude' => $this->coord($row['longitude'] ?? null),
            ];
        }

        // Sort alphabetically by name, ignoring case — same as the source table.
        usort($institutions, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        $totalAffected = array_sum(array_filter(
            array_column($institutions, 'affected_people'),
            'is_int'
        ));

        $this->info('TOTAL known affected people: '.number_format($totalAffected));
        $this->info('Institutions: '.count($institutions));

        if ($this->option('dry-run')) {
            $this->warn('Dry run — snapshot not written.');

            return self::SUCCESS;
        }

        $payload = [
            'source' => 'Nimble Tent Data Viewer · Baserow table '.$tableId,
            'synced_at' => now()->toIso8601String(),
            'total_affected' => $totalAffected,
            'count' => count($institutions),
            'institutions' => $institutions,
        ];

        $path = base_path(self::SNAPSHOT_PATH);
        @mkdir(dirname($path), 0775, true);
        file_put_contents(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        $this->info('Wrote '.self::SNAPSHOT_PATH);

        return self::SUCCESS;
    }

    /**
     * Affected-people count for a row: the sum of its numeric "tally" link
     * values, or "unknown" when there are none — mirroring the notebook's
     * own calculation so our total matches the source exactly.
     */
    private function affectedPeople(mixed $tally): int|string {
        if (! is_array($tally) || $tally === []) {
            return 'unknown';
        }

        $total = 0;
        $valid = 0;
        foreach ($tally as $item) {
            $value = is_array($item) ? ($item['value'] ?? null) : null;
            if ($value === null || $value === '') {
                continue;
            }
            if (is_numeric(trim((string) $value))) {
                $total += (int) trim((string) $value);
                $valid++;
            }
        }

        return $valid > 0 ? $total : 'unknown';
    }

    /**
     * Parse a Baserow coordinate field into a float, or null when blank or
     * non-numeric (some institutions have no geocode). Rounded to 5 dp —
     * ~1 metre precision, plenty for campus map pins, and keeps the JSON small.
     */
    private function coord(mixed $value): ?float {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return round((float) $value, 5);
    }
}
