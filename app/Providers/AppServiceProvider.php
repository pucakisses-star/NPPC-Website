<?php

namespace App\Providers;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Table::configureUsing(function (Table $table): Table {
            return $table->defaultPaginationPageOption(50);
        });

        // Bust the /api/prisoners response cache whenever any record
        // that feeds it changes, so admin edits show up immediately.
        $bust = fn () => Cache::forget(PrisonerApiController::cacheKey());
        foreach ([Prisoner::class, PrisonerCase::class, Institution::class] as $model) {
            $model::saved($bust);
            $model::deleted($bust);
        }
    }
}
