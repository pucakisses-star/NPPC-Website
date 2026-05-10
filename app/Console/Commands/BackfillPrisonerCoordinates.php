<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Services\MapboxGeocoder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Walk every prisoner whose address is non-empty but whose lat/lng
 * are blank, geocode the address via Mapbox, and write the
 * coordinates. The model's saving hook does the same thing on every
 * save going forward; this is a one-shot reconciliation.
 */
class BackfillPrisonerCoordinates extends Command
{
    protected $signature   = 'prisoners:backfill-coordinates {--dry-run : Print plan without writing} {--limit= : Only process the first N rows} {--throttle=300 : Milliseconds to sleep between Mapbox requests}';
    protected $description = 'Geocode every prisoner with an address but no lat/lng via Mapbox.';

    public function handle(MapboxGeocoder $geocoder): int
    {
        $dryRun  = (bool) $this->option('dry-run');
        $limit   = $this->option('limit') ? (int) $this->option('limit') : null;
        $throttle = max(0, (int) $this->option('throttle'));

        if (! config('services.mapbox.token')) {
            $this->error('MAPBOX_TOKEN is not configured in .env. Set it and re-run.');
            return self::FAILURE;
        }

        $query = Prisoner::query()
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->where(function ($q) {
                $q->whereNull('lat')->orWhereNull('lng');
            })
            ->orderBy('name');

        if ($limit) $query->limit($limit);

        $rows = $query->get(['id', 'name', 'address', 'lat', 'lng']);
        $this->info("Candidates: {$rows->count()}");

        $hits = 0; $miss = 0;
        foreach ($rows as $i => $p) {
            $coords = $geocoder->geocode((string) $p->address);
            if (! $coords) {
                $this->line("  [no match] {$p->name}");
                $miss++;
            } else {
                [$lat, $lng] = $coords;
                $this->line(sprintf("  [geocoded] %s  -> (%.5f, %.5f)", $p->name, $lat, $lng));
                if (! $dryRun) {
                    DB::table('prisoners')->where('id', $p->id)->update([
                        'lat' => $lat,
                        'lng' => $lng,
                    ]);
                }
                $hits++;
            }

            if ($throttle && $i < $rows->count() - 1) usleep($throttle * 1000);
        }

        $this->info("\nDone. geocoded={$hits}, no_match={$miss}" . ($dryRun ? ' (dry-run)' : ''));
        return self::SUCCESS;
    }
}
