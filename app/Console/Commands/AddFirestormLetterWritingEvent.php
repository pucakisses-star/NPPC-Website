<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds the Blue Ridge ABC "Political Prisoner Letter Writing" event
 * hosted at Firestorm Co-op (Asheville, NC) — a recurring monthly
 * event (first Sunday) of letter-writing for incarcerated comrades'
 * birthdays.
 *
 *   Source: https://firestorm.coop/events/3040-political-prisoner-letter-writing.html
 */
final class AddFirestormLetterWritingEvent extends Command {
    protected $signature = 'archive:add-firestorm-letter-writing-event';
    protected $description = 'Add the Blue Ridge ABC Political Prisoner Letter Writing recurring event at Firestorm';

    public function handle(): int {
        $slug = 'political-prisoner-letter-writing-firestorm';

        $imagePath = 'events/'.$slug.'.jpg';
        if (! Storage::disk('public')->exists($imagePath)) {
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
                    ->timeout(30)
                    ->get('https://firestorm.coop/img/events/3040-PoliticalPrisonerLetterWriting.jpg');
                if ($resp->successful() && strlen($resp->body()) > 5000) {
                    Storage::disk('public')->put($imagePath, $resp->body());
                    $this->info('Saved event image to '.$imagePath);
                } else {
                    $this->warn('Image fetch returned HTTP '.$resp->status().' — leaving image blank.');
                    $imagePath = null;
                }
            } catch (\Throwable $e) {
                $this->warn('Image fetch failed: '.$e->getMessage());
                $imagePath = null;
            }
        }

        $event = Event::firstOrNew(['slug' => $slug]);
        $event->title = 'Political Prisoner Letter Writing';
        $event->description = 'Letters save lives! Join Blue Ridge ABC each month at Firestorm Books & Coffee in Asheville for an evening of solidarity with incarcerated comrades — celebrating their birthdays by sending words of encouragement and support.';
        $event->body = "<p><strong>Hosted by:</strong> Blue Ridge ABC (Anarchist Black Cross)</p>"
            ."<p><strong>Venue:</strong> Firestorm Books &amp; Coffee — 610 Haywood Road, Asheville, NC 28806</p>"
            ."<p><strong>Recurrence:</strong> Monthly, first Sunday of each month.</p>"
            ."<p>Letters save lives! Join Blue Ridge ABC each month for an evening of solidarity with incarcerated comrades. Celebrate their birthdays by sending words of encouragement and support. All supplies provided — bring your friends.</p>"
            ."<p>Original listing: <a href=\"https://firestorm.coop/events/3040-political-prisoner-letter-writing.html\" target=\"_blank\" rel=\"noopener\">firestorm.coop/events/3040-political-prisoner-letter-writing.html</a></p>";
        $event->location = 'Firestorm Books & Coffee — 610 Haywood Road, Asheville, NC 28806';
        $event->time = '5:00 PM – 7:00 PM';
        $event->event_url = 'https://firestorm.coop/events/3040-political-prisoner-letter-writing.html';
        $event->series = 'Blue Ridge ABC — Political Prisoner Letter Writing';
        $event->published = true;

        if (! $event->event_date) {
            $event->event_date = $this->nextFirstSunday();
        }
        if ($imagePath) {
            $event->image = $imagePath;
        }
        $event->save();

        $this->info('Event saved: '.$event->title.' on '.$event->event_date->format('Y-m-d'));
        return self::SUCCESS;
    }

    private function nextFirstSunday(): \Carbon\Carbon {
        $candidate = now()->startOfMonth()->modify('first sunday of this month');
        if ($candidate->isPast()) {
            $candidate = now()->modify('first sunday of next month');
        }
        return \Carbon\Carbon::parse($candidate);
    }
}
