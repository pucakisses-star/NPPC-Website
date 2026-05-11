<?php

use App\Http\Controllers\DonateController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::controller(DonateController::class)
    ->group(function () {
        Route::get('/donate-callback', 'callback');
    });

Route::controller(SiteController::class)
    ->group(function () {
        Route::get('/', 'home')->name('home');
        Route::get('/search', 'search');
        Route::get('/news/{slug}', 'article');
        Route::get('history', 'history');
        Route::get('timeline', 'timeline');
        Route::get('annual-report', 'annualReport');
        Route::get('topics/{slug?}', 'topics');
        Route::get('calendar', 'calendar');
        Route::get('map', 'map');
        Route::get('faq', 'faq');
        Route::get('staff', 'staff');
        Route::get('podcast', 'podcast');
        Route::get('store', 'store');
        Route::get('events', 'events');
        Route::get('volunteer', 'volunteer');
        Route::get('prisoner-outreach', 'prisonerOutreach');
        Route::get('petition/{slug}', 'petitionPage');
        Route::post('petition/{slug}/sign', 'petitionSign');
        Route::get('prisoner/{slug}', 'prisoner');
        Route::get('archive1-records', 'archive1Records');
        Route::get('board-of-directors', 'boardOfDirectors');
        Route::get('partners', 'partners');
        Route::get('about', function() { return view('pages.about'); });
        Route::get('/{slug}', 'page');
    });

Route::controller(FormSubmissionController::class)
    ->group(function () {
        Route::post('/form/{form}', 'submit');
    });

Route::post('/sign-up', function (\Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);
    \App\Models\EmailSubscriber::firstOrCreate(['email' => $request->input('email')]);

    return redirect()->back()->with('subscribed', true);
});

// TEMP debug — remove after diagnosing the /admin/archive-records 404
Route::get('/__debug-archive', function () {
    try {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $resources = array_map('strval', \Filament\Facades\Filament::getResources());
    } catch (\Throwable $e) {
        $resources = ['error' => $e->getMessage()];
    }
    $adminRoutes = collect(app('router')->getRoutes())
        ->map(fn ($r) => $r->uri())
        ->filter(fn ($u) => str_starts_with($u, 'admin/'))
        ->sort()
        ->values()
        ->all();

    return response()->json([
        'php_sapi' => php_sapi_name(),
        'php_version' => PHP_VERSION,
        'app_env' => app()->environment(),
        'class_exists' => class_exists(\App\Filament\Resources\ArchiveRecordResource::class),
        'resource_in_filament' => in_array(\App\Filament\Resources\ArchiveRecordResource::class, $resources),
        'resources_total' => count($resources),
        'resources' => $resources,
        'admin_routes' => $adminRoutes,
    ], 200, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT);
});
