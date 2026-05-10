<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string      $name
 * @property string|null $city
 * @property string|null $state
 * @property string|null $security
 * @property string|null $mailing_address
 * @property string|null $physical_address
 * @property float|null  $lat
 * @property float|null  $lng
 */
final class Institution extends Model {
    public function cases(): HasMany {
        return $this->hasMany(PrisonerCase::class);
    }

    public static function booted(): void {
        parent::booted();

        // Geocode the institution on save when there's a usable
        // location string but lat/lng aren't set. Prefers the
        // physical address, then mailing address, then "city, state".
        // Manual overrides survive — geocoder only fills empty
        // coordinate fields.
        static::saving(function ($model) {
            if (! empty($model->lat) && ! empty($model->lng)) return;

            $candidates = array_filter([
                trim((string) $model->physical_address),
                trim((string) $model->mailing_address),
                trim(implode(', ', array_filter([$model->city, $model->state]))),
            ], fn ($s) => $s !== '');

            foreach ($candidates as $address) {
                try {
                    $coords = app(\App\Services\MapboxGeocoder::class)->geocode($address);
                    if ($coords) {
                        if (empty($model->lat)) $model->attributes['lat'] = $coords[0];
                        if (empty($model->lng)) $model->attributes['lng'] = $coords[1];
                        return;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('Institution geocode skipped: ' . $e->getMessage());
                    return;
                }
            }
        });
    }
}
