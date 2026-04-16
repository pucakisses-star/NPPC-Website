<?php

namespace App\Http\Controllers;

use App\Models\TripAttachment;

final class TripController extends Controller {
    private array $days = [
        1 => ['title' => 'Monday, April 27th', 'subtitle' => 'Arrival & First Night Out'],
        2 => ['title' => 'Tuesday, April 28th', 'subtitle' => 'Art, Spa & Soho'],
        3 => ['title' => 'Wednesday, April 29th', 'subtitle' => 'Museums & Markets'],
        4 => ['title' => 'Thursday, April 30th', 'subtitle' => 'Birthday!', 'birthday' => true],
        5 => ['title' => 'Friday, May 1st', 'subtitle' => 'Thorpe Park'],
        6 => ['title' => 'Saturday, May 2nd', 'subtitle' => 'Open Day & Departure'],
    ];

    public function index() {
        return response()->file(base_path('london-trip.html'));
    }

    public function day(int $day) {
        if ($day < 1 || $day > 6) {
            abort(404);
        }

        $info = $this->days[$day];
        $attachments = TripAttachment::where('day', $day)->orderBy('sort_order')->get();
        $allDays = $this->days;

        return view('pages.trip-day', compact('day', 'info', 'attachments', 'allDays'));
    }
}
