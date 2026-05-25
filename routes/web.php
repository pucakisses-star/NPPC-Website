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
        Route::get('archive-records', 'archiveRecords');
        Route::get('timeline', 'timeline');
        Route::get('annual-report', 'annualReport');
        Route::get('topics/{slug?}', 'topics');
        Route::get('calendar', 'calendar');
        Route::get('birthdays', 'birthdays');
        Route::get('map', 'map');
        Route::get('faq', 'faq');
        Route::get('staff', 'staff');
        Route::get('podcast', 'podcast');
        Route::get('store', 'store');
        Route::get('events', 'events');
        Route::get('volunteer', 'volunteer');
        Route::get('prisoner-outreach', 'prisonerOutreach');
        Route::get('petitions', 'petitionsIndex');
        Route::get('petition/{slug}', 'petitionPage');
        Route::post('petition/{slug}/sign', 'petitionSign');
        Route::get('prisoner/{slug}', 'prisoner');
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
