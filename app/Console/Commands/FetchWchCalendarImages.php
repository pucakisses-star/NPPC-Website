<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchWchCalendarImages extends Command
{
    protected $signature = 'calendar:fetch-wch-images {--max-pages=120 : Mastodon API pages to fetch (40 statuses each)} {--overwrite : Replace existing images}';
    protected $description = 'Crawl Working Class History\'s Mastodon timeline and attach the original #OtD post image to each matching calendar entry.';

    private const ACCOUNT_ID = '1372518'; // mastodon.social/@WorkingClassHistory
    private const BASE_URL   = 'https://mastodon.social';

    private const MONTHS = [
        'jan' => 1, 'january' => 1,
        'feb' => 2, 'february' => 2,
        'mar' => 3, 'march' => 3,
        'apr' => 4, 'april' => 4,
        'may' => 5,
        'jun' => 6, 'june' => 6,
        'jul' => 7, 'july' => 7,
        'aug' => 8, 'august' => 8,
        'sep' => 9, 'sept' => 9, 'september' => 9,
        'oct' => 10, 'october' => 10,
        'nov' => 11, 'november' => 11,
        'dec' => 12, 'december' => 12,
    ];

    public function handle(): int
    {
        $maxPages  = (int) $this->option('max-pages');
        $overwrite = (bool) $this->option('overwrite');

        // Map (month, day) -> CalendarEntry for the entries that need images.
        $needsImage = CalendarEntry::query()
            ->when(! $overwrite, fn ($q) => $q->whereNull('image')->orWhere('image', ''))
            ->get()
            ->keyBy(fn ($e) => "{$e->month}-{$e->day}");

        $this->info("Need images for " . $needsImage->count() . " calendar entries.");
        if ($needsImage->isEmpty()) return self::SUCCESS;

        Storage::disk('public')->makeDirectory('calendar');

        $maxId = null;
        $matched = 0;
        $downloaded = 0;
        $pagesScanned = 0;

        for ($page = 0; $page < $maxPages; $page++) {
            $params = ['limit' => 40, 'exclude_reblogs' => 'true', 'exclude_replies' => 'true'];
            if ($maxId) $params['max_id'] = $maxId;

            $resp = Http::withHeaders([
                'User-Agent' => 'NPPC-Website/1.0 (https://nppc.org; contact@nppc.org)',
                'Accept'     => 'application/json',
            ])->timeout(30)->get(self::BASE_URL . '/api/v1/accounts/' . self::ACCOUNT_ID . '/statuses', $params);

            if (! $resp->successful()) {
                $this->error("API error on page {$page}: HTTP " . $resp->status());
                break;
            }

            $statuses = $resp->json();
            if (! is_array($statuses) || empty($statuses)) {
                $this->info("End of timeline at page {$page}.");
                break;
            }

            $pagesScanned++;
            $maxId = $statuses[count($statuses) - 1]['id'];

            foreach ($statuses as $status) {
                if (empty($status['media_attachments'])) continue;

                $parsed = $this->parseOtdDate($status['content'] ?? '');
                if (! $parsed) continue;

                $key = "{$parsed['month']}-{$parsed['day']}";
                if (! $needsImage->has($key)) continue;

                $entry = $needsImage->get($key);
                if (! $overwrite && ! empty($entry->image)) continue;

                // Take the first image-typed attachment
                $url = null;
                foreach ($status['media_attachments'] as $att) {
                    if (($att['type'] ?? '') === 'image' && ! empty($att['url'])) {
                        $url = $att['url'];
                        break;
                    }
                }
                if (! $url) continue;

                $matched++;

                try {
                    $bytes = Http::timeout(45)
                        ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (calendar image importer)'])
                        ->get($url)
                        ->throw()
                        ->body();
                } catch (\Throwable $e) {
                    $this->warn("  download failed for {$entry->month}/{$entry->day}: " . $e->getMessage());
                    continue;
                }

                $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) $ext = 'jpg';

                $slug = Str::slug($entry->title) ?: "{$entry->month}-{$entry->day}";
                $path = "calendar/{$slug}.{$ext}";
                Storage::disk('public')->put($path, $bytes);

                $entry->image = $path;
                $entry->save();
                $needsImage->forget($key);

                $downloaded++;
                $this->info("  {$entry->month}/{$entry->day}: {$entry->title} ← " . strlen($bytes) . " bytes");

                if ($needsImage->isEmpty()) {
                    $this->info("All entries matched. Done.");
                    break 2;
                }
            }

            // Polite pause between API pages
            usleep(400 * 1000);
        }

        $this->line('');
        $this->info("Done. Pages scanned: {$pagesScanned}, matched: {$matched}, downloaded: {$downloaded}, still missing: " . $needsImage->count());

        return self::SUCCESS;
    }

    /**
     * Pull "OtD <day> <month> <year>" out of the Mastodon HTML content.
     * Returns ['month' => int, 'day' => int, 'year' => int] or null.
     */
    private function parseOtdDate(string $html): ?array
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5);

        if (! preg_match('/OtD\s+(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/i', $text, $m)) {
            return null;
        }

        $day   = (int) $m[1];
        $month = self::MONTHS[strtolower($m[2])] ?? null;
        $year  = (int) $m[3];

        if (! $month || $day < 1 || $day > 31 || $year < 1500 || $year > 2100) return null;

        return ['month' => $month, 'day' => $day, 'year' => $year];
    }
}
