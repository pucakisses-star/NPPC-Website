<?php

namespace App\Providers;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Filament\Forms\Components\Field;
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

        // Admin-panel autosave (save-on-blur). When enabled, every form field
        // defaults to syncing on blur, so EditRecord pages using the
        // AutosavesOnBlur trait can persist as the admin tabs between fields.
        // This is only a default applied at field construction, so any field a
        // resource explicitly marks live() (e.g. for dependent-field reactivity)
        // still overrides it. Entirely inert when the feature is disabled.
        if (config('filament-autosave.enabled', false)) {
            Field::configureUsing(function (Field $field): void {
                $field->live(onBlur: true);
            });
        }

        // Bust the /api/prisoners response cache whenever any record
        // that feeds it changes, so admin edits show up immediately.
        $bust = fn () => Cache::forget(PrisonerApiController::cacheKey());
        foreach ([Prisoner::class, PrisonerCase::class, Institution::class] as $model) {
            $model::saved($bust);
            $model::deleted($bust);
        }
    }
}
