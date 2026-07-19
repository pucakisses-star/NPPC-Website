<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the November 11, 1979 calendar entry for the death of Ángel
 * Rodríguez Cristóbal in FCI Tallahassee. Shares the day with the 1919
 * Wesley Everest entry (the month/day unique constraint was dropped in
 * the 2026-05-17 migration).
 *
 * Idempotent — matches on month/day/year/title.
 */
final class AddRodriguezCristobalCalendar extends Command {
    protected $signature = 'calendar:add-rodriguez-cristobal';
    protected $description = 'Add the Nov 11, 1979 Ángel Rodríguez Cristóbal calendar entry';

    public function handle(): int {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'angel-rodriguez-cristobal')->first();

        // Reuse his prisoner portrait for the calendar card if available.
        $imagePath = null;
        if (Storage::disk('public')->exists('calendar/angel-rodriguez-cristobal.jpg')) {
            $imagePath = 'calendar/angel-rodriguez-cristobal.jpg';
        } elseif ($prisoner && $prisoner->photo && Storage::disk('public')->exists($prisoner->photo)) {
            Storage::disk('public')->copy($prisoner->photo, 'calendar/angel-rodriguez-cristobal.jpg');
            $imagePath = 'calendar/angel-rodriguez-cristobal.jpg';
            $this->info('Copied prisoner portrait to '.$imagePath);
        } else {
            $this->warn('No portrait available; entry created without image.');
        }

        $title = 'Ángel Rodríguez Cristóbal dies in federal custody';
        $description = "On November 11, 1979, Ángel Rodríguez Cristóbal — a farmer from Ciales, a leader of the Liga Socialista Puertorriqueña, and one of the twenty-one Vieques civil-disobedience protesters arrested that May — was found hanged in his cell at the Federal Correctional Institution in Tallahassee, Florida, six weeks into a six-month sentence for trespassing on the U.S. Navy's Vieques bombing range. He had refused to recognize the court's authority over Puerto Rico.\n\nAuthorities ruled the death a suicide. His supporters, pointing to reports of bruising on his body, have regarded it as a murder in custody ever since. His funeral drew thousands, his death became a rallying cry of the Vieques and independence movements — and three weeks later the Sabana Seca ambush was carried out in his name.";

        $existing = CalendarEntry::query()
            ->where('month', 11)->where('day', 11)->where('year', 1979)
            ->where('title', $title)
            ->first();

        $payload = [
            'month' => 11,
            'day' => 11,
            'year' => 1979,
            'title' => $title,
            'description' => $description,
            'image' => $imagePath,
            'published' => true,
            'prisoner_id' => $prisoner?->id,
        ];

        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing entry.');
        } else {
            CalendarEntry::create($payload);
            $this->info('Created entry.');
        }

        return self::SUCCESS;
    }
}
