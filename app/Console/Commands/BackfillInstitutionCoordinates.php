<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Services\MapboxGeocoder;
use Illuminate\Console\Command;

/**
 * Geocode every institution with a usable location string but no
 * lat/lng. Mirrors the prisoner-level backfill (and reuses the
 * same Mapbox throttle pattern) so the institution-coordinate
 * refactor can be brought live in one shot.
 *
 * Source-of-truth precedence: physical_address, then mailing_address,
 * then "city, state". Skips silently when none are present.
 */
class BackfillInstitutionCoordinates extends Command
{
    protected $signature   = 'institutions:backfill-coordinates {--dry-run : Print plan without writing} {--limit= : Only process the first N rows} {--throttle=300 : Milliseconds to sleep between Mapbox requests}';
    protected $description = 'Geocode every institution that has an address but no lat/lng via Mapbox.';

    public function handle(MapboxGeocoder $geocoder): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $limit   = $this->option('limit') ? (int) $this->option('limit') : null;
        $throttle = max(0, (int) $this->option('throttle'));

        if (! config('services.mapbox.token')) {
            $this->error('MAPBOX_TOKEN is not configured in .env. Set it and re-run.');
            return self::FAILURE;
        }

        $query = Institution::query()
            ->where(function ($q) {
                $q->whereNull('lat')->orWhereNull('lng');
            })
            ->where(function ($q) {
                $q->whereNotNull('physical_address')->where('physical_address', '!=', '')
                  ->orWhereNotNull('mailing_address')->where('mailing_address', '!=', '')
                  ->orWhere(function ($q) {
                      $q->whereNotNull('city')->where('city', '!=', '');
                  });
            })
            ->orderBy('name');

        if ($limit) $query->limit($limit);

        $rows = $query->get();
        $this->info("Candidates: {$rows->count()}");

        $hits = 0; $miss = 0;
        foreach ($rows as $i => $inst) {
            $candidates = array_filter([
                trim((string) $inst->physical_address),
                trim((string) $inst->mailing_address),
                trim(implode(', ', array_filter([$inst->city, $inst->state]))),
            ], fn ($s) => $s !== '');

            $coords = null;
            foreach ($candidates as $addr) {
                $coords = $geocoder->geocode($addr);
                if ($coords) break;
            }

            if (! $coords) {
                $this->line("  [no match] {$inst->name}");
                $miss++;
            } else {
                [$lat, $lng] = $coords;
                $this->line(sprintf("  [geocoded] %s  -> (%.5f, %.5f)", $inst->name, $lat, $lng));
                if (! $dryRun) {
                    \Illuminate\Support\Facades\DB::table('institutions')
                        ->where('id', $inst->id)
                        ->update(['lat' => $lat, 'lng' => $lng]);
                }
                $hits++;
            }

            if ($throttle && $i < $rows->count() - 1) usleep($throttle * 1000);
        }

        $this->info("\nDone. geocoded={$hits}, no_match={$miss}" . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }
}
