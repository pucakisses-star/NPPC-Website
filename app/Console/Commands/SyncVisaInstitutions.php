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

    /**
     * Known wrong state values in the upstream Baserow data, keyed by exact
     * institution name. Applied on every sync so the map's state filter
     * groups these campuses correctly.
     */
    public const STATE_CORRECTIONS = [
        'Bridgewater State University' => 'Massachusetts', // tagged "Minnesota" upstream; campus is in Bridgewater, MA
    ];

    /**
     * Affected-people counts for institutions the upstream data left as
     * "unknown" but for which a specific figure was publicly reported. Keyed
     * by exact institution name; applied on every sync. Each is sourced:
     *
     *  - U. of Maryland (7): dbknews.com 2025-04-17 — university confirmed 7.
     *  - UC Santa Barbara (10): Daily Nexus 2025-04-09 — 7 students + 3 OPT grads.
     *  - U. of Wyoming (6): Cowboy State Daily 2025-04-28 — "six to 10"; 6 reinstated.
     *  - U. of Missouri (5): KOMU — Pres. Mun Choi; corroborated by 5-student suit.
     *  - Purdue University (5): Purdue Exponent — 5 ACLU plaintiffs (6 later restored).
     *  - U. of Nebraska System (3): Daily Nebraskan — 3 at UNL flagship.
     */
    public const AFFECTED_OVERRIDES = [
        'University of Maryland' => 7,
        'University of California, Santa Barbara' => 10,
        'University of Wyoming' => 6,
        'University of Missouri' => 5,
        'Purdue University' => 5,
        'University of Nebraska System' => 3,
    ];

    /**
     * Affected-people floors from the Inside Higher Ed national tracker
     * (insidehighered.com, "Where Students Have Had Their Visas Revoked"),
     * keyed by exact institution name. Unlike AFFECTED_OVERRIDES (which only
     * fills "unknown"), these raise the count to the floor when our figure is
     * unknown or lower — applied as max(upstream, floor) so a larger upstream
     * number always wins and a number is never lowered. Each is sourced:
     *
     *  - U. of Texas System (170): IHE tracker aggregate for all UT campuses.
     *  - Cornell University (26): Ithaca Voice 2025-04 — SEVIS terminations.
     *  - De Anza College (9): La Voz 2025-04-11 — Foothill–De Anza district.
     *  - CSU Fullerton (8): Daily Titan 2025-04 — academic senate briefing.
     *  - CSU Long Beach (6): LB Current 2025-04-11 — Pres. Conoley confirmed 6.
     *  - CSU East Bay (3): Times-Herald 2025-04-08 — Bay Area cancellations.
     */
    public const AFFECTED_FLOORS = [
        'University of Texas System' => 170,
        'Cornell University' => 26,
        'De Anza College' => 9,
        'California State University, Fullerton' => 8,
        'California State University, Long Beach' => 6,
        'California State University, East Bay' => 3,
    ];

    /**
     * Coordinates for institutions the upstream data left ungeocoded, keyed
     * by exact name. Applied only when the upstream lat/lng is missing, so a
     * real upstream geocode is never overwritten. Without these the row is
     * absent from the map (it still shows in the table). Geocoded via
     * OpenStreetMap/Nominatim; the CU system row uses its Boulder flagship.
     */
    public const COORDINATE_OVERRIDES = [
        'Snow College' => [39.36055, -111.58067],
        'South Central College' => [44.17466, -94.04647],
        'University of Colorado (all campuses)' => [40.00701, -105.26644],
    ];

    /**
     * Institutions with publicly reported Spring 2025 visa revocations /
     * SEVIS terminations that are absent from the upstream Baserow dataset.
     * Appended on every sync (skipped if a row of the same name already
     * exists upstream) so they appear on the map. Coordinates geocoded via
     * OpenStreetMap/Nominatim. affected_people is an int where a specific
     * figure was reported, otherwise 'unknown'. Each entry is sourced:
     *
     *  - Florida Atlantic U. (6): UPress 2025-04 — 4 students + 2 post-grads.
     *  - San Francisco State U. (5): Axios SF 2025-04-15 — 1 student + 4 grads.
     *  - St. Ambrose U. (2): WQAD 2025-04 — 2 SEVIS terminations.
     *  - U. of North Florida (2): Miami New Times 2025-04 — 2 recent grads.
     *  - UMass Dartmouth (1): WBUR 2025-04-07 — 1 student.
     *  - Clarke U. (1): Iowa Capital Dispatch 2025-04 — 1 student.
     *  - Brandeis U. (unknown): Boston Globe 2025-04-23 — named in federal TRO.
     *  - Coe College, Luther College, Des Moines Area CC, Eastern Iowa CC,
     *    Grinnell College (unknown): Iowa Capital Dispatch 2025-04 roundup.
     *  - U. of South Florida (unknown): USF Oracle 2025-04-22 (~a dozen per atty).
     *  - Barnard College (1): named case (Yunseo Chung); The Hill / Al Jazeera.
     *  - Indiana Institute of Technology (1): WFYI / Mirror Indy — 1 student
     *    restored per the 2025-04-28 court filing.
     *  - Ensign College (unknown): named in the ACLU of Utah suit (2025-04-18),
     *    alongside Utah and BYU.
     */
    public const ADDITIONAL_INSTITUTIONS = [
        ['name' => 'Florida Atlantic University', 'state' => 'Florida', 'affected_people' => 6, 'website' => 'https://www.fau.edu', 'latitude' => 26.37434, 'longitude' => -80.10272],
        ['name' => 'San Francisco State University', 'state' => 'California', 'affected_people' => 5, 'website' => 'https://www.sfsu.edu', 'latitude' => 37.72452, 'longitude' => -122.48000],
        ['name' => 'St. Ambrose University', 'state' => 'Iowa', 'affected_people' => 2, 'website' => 'https://www.sau.edu', 'latitude' => 41.53975, 'longitude' => -90.58118],
        ['name' => 'University of North Florida', 'state' => 'Florida', 'affected_people' => 2, 'website' => 'https://www.unf.edu', 'latitude' => 30.26899, 'longitude' => -81.50966],
        ['name' => 'University of Massachusetts Dartmouth', 'state' => 'Massachusetts', 'affected_people' => 1, 'website' => 'https://www.umassd.edu', 'latitude' => 41.62250, 'longitude' => -71.00734],
        ['name' => 'Clarke University', 'state' => 'Iowa', 'affected_people' => 1, 'website' => 'https://www.clarke.edu', 'latitude' => 42.50962, 'longitude' => -90.69069],
        ['name' => 'Brandeis University', 'state' => 'Massachusetts', 'affected_people' => 'unknown', 'website' => 'https://www.brandeis.edu', 'latitude' => 42.36651, 'longitude' => -71.25802],
        ['name' => 'Coe College', 'state' => 'Iowa', 'affected_people' => 'unknown', 'website' => 'https://www.coe.edu', 'latitude' => 41.99060, 'longitude' => -91.65697],
        ['name' => 'Luther College', 'state' => 'Iowa', 'affected_people' => 'unknown', 'website' => 'https://www.luther.edu', 'latitude' => 43.31695, 'longitude' => -91.79850],
        ['name' => 'Des Moines Area Community College', 'state' => 'Iowa', 'affected_people' => 'unknown', 'website' => 'https://www.dmacc.edu', 'latitude' => 41.70769, 'longitude' => -93.61022],
        ['name' => 'Eastern Iowa Community Colleges', 'state' => 'Iowa', 'affected_people' => 'unknown', 'website' => 'https://www.eicc.edu', 'latitude' => 41.52360, 'longitude' => -90.57760],
        ['name' => 'Grinnell College', 'state' => 'Iowa', 'affected_people' => 'unknown', 'website' => 'https://www.grinnell.edu', 'latitude' => 41.75112, 'longitude' => -92.71985],
        ['name' => 'University of South Florida', 'state' => 'Florida', 'affected_people' => 'unknown', 'website' => 'https://www.usf.edu', 'latitude' => 28.06000, 'longitude' => -82.41384],
        ['name' => 'Barnard College', 'state' => 'New York', 'affected_people' => 1, 'website' => 'https://barnard.edu', 'latitude' => 40.80949, 'longitude' => -73.96355],
        ['name' => 'Indiana Institute of Technology', 'state' => 'Indiana', 'affected_people' => 1, 'website' => 'https://www.indianatech.edu', 'latitude' => 41.07755, 'longitude' => -85.11768],
        ['name' => 'Ensign College', 'state' => 'Utah', 'affected_people' => 'unknown', 'website' => 'https://www.ensign.edu', 'latitude' => 40.77113, 'longitude' => -111.90025],
    ];

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
            $name = (string) ($row['institution'] ?? '');

            // Affected count: prefer the upstream tally; fall back to a
            // sourced override only when the upstream value is "unknown" so
            // a real reported number is never overwritten.
            $affected = $this->affectedPeople($row['tally'] ?? null);
            if ($affected === 'unknown' && isset(self::AFFECTED_OVERRIDES[$name])) {
                $affected = self::AFFECTED_OVERRIDES[$name];
            }

            // Raise to the Inside Higher Ed tracker floor when ours is
            // unknown or lower — max(upstream, floor) — so a larger upstream
            // number still wins and a count is never lowered.
            if (isset(self::AFFECTED_FLOORS[$name])) {
                $floor = self::AFFECTED_FLOORS[$name];
                $affected = is_int($affected) ? max($affected, $floor) : $floor;
            }

            // Coordinates: prefer the upstream geocode; fall back to a sourced
            // override only when the upstream lat/lng is missing, so the row
            // can still be placed on the map.
            $latitude = $this->coord($row['latitude'] ?? null);
            $longitude = $this->coord($row['longitude'] ?? null);
            if (($latitude === null || $longitude === null) && isset(self::COORDINATE_OVERRIDES[$name])) {
                [$latitude, $longitude] = self::COORDINATE_OVERRIDES[$name];
            }

            $institutions[] = [
                'name' => $name,
                // Correct known wrong state values in the upstream data
                // (e.g. Bridgewater State University is tagged Minnesota but
                // its campus and coordinates are in Massachusetts).
                'state' => self::STATE_CORRECTIONS[$name] ?? (string) ($row['state'] ?? ''),
                'affected_people' => $affected,
                'website' => (string) ($row['website'] ?? ''),
                'wikipedia' => (string) ($row['wikipedia-url'] ?? ''),
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        // Append institutions confirmed by reporting but missing from the
        // upstream dataset. Skip any whose name already arrived from Baserow
        // so we never create a duplicate pin.
        $existingNames = array_column($institutions, 'name');
        foreach (self::ADDITIONAL_INSTITUTIONS as $extra) {
            if (in_array($extra['name'], $existingNames, true)) {
                continue;
            }

            $institutions[] = [
                'name' => $extra['name'],
                'state' => $extra['state'],
                'affected_people' => $extra['affected_people'],
                'website' => $extra['website'] ?? '',
                'wikipedia' => $extra['wikipedia'] ?? '',
                'latitude' => $extra['latitude'],
                'longitude' => $extra['longitude'],
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
