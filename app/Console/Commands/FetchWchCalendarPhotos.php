<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * For each CalendarEntry without a stored photo, query Working Class
 * History's public Baserow API for posts matching the same month + day,
 * pick the best year + title match, and download the row's `media`
 * field into storage/app/public/calendar/.
 *
 * WCH stores its "on this day" stories in Baserow table 33215, queryable
 * via `https://api.baserow.io/api/database/rows/table/33215/`. The token
 * is the public/read-only one embedded in the SPA bundle.
 *
 * Use --force to re-fetch even if the entry already has an image.
 * Use --dry-run to log what would be downloaded without writing.
 */
final class FetchWchCalendarPhotos extends Command {
    protected $signature = 'calendar:fetch-wch-photos {--force : Re-fetch even if image already set} {--dry-run : Preview without writing} {--limit=0 : Stop after N entries (0 = no limit)}';
    protected $description = 'Fetch Working Class History photos for calendar entries';

    private const BASE_URL = 'https://api.baserow.io/api/database/rows/table/33215/';

    private const TOKEN = 'lt1DznAg1rDLSIrimfYiED1ce2IoLBtr';

    private const F_YEAR = 'field_177138';

    private const F_MONTH = 'field_177139';

    private const F_DAY = 'field_177140';

    public function handle(): int {
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $disk = Storage::disk('public');
        $disk->makeDirectory('calendar');

        $linked = 0;
        $skipped = 0;
        $noMatch = 0;
        $emptyMedia = 0;
        $failed = 0;
        $processed = 0;

        $query = CalendarEntry::query()->whereNotNull('month')->whereNotNull('day');
        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('image')->orWhere('image', '');
            });
        }
        $entries = $query->orderBy('month')->orderBy('day')->get();

        $this->info('Processing '.$entries->count().' calendar entries...');

        $rowsByDate = [];

        foreach ($entries as $entry) {
            if ($limit > 0 && $processed >= $limit) {
                break;
            }
            $processed++;
            $key = sprintf('%02d-%02d', $entry->month, $entry->day);

            if (! isset($rowsByDate[$key])) {
                $rows = $this->fetchRowsForDate($entry->month, $entry->day);
                if ($rows === null) {
                    $this->error("  HTTP error fetching {$key}, skipping.");
                    $failed++;

                    continue;
                }
                $rowsByDate[$key] = $rows;
            }

            $rows = $rowsByDate[$key];
            $match = $this->pickBestMatch($entry, $rows);

            if (! $match) {
                $this->line("no match: {$key}  \"{$entry->title}\" (year {$entry->year})");
                $noMatch++;

                continue;
            }
            if (empty($match['media'])) {
                $this->line("no media: {$key}  matched WCH row id={$match['id']} but media field empty");
                $emptyMedia++;

                continue;
            }

            $mediaUrl = $match['media'];
            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION)) ?: 'jpg';
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
                $ext = 'jpg';
            }
            $relative = 'calendar/'.$entry->id.'.'.$ext;
            $localPath = storage_path('app/public/'.$relative);

            $this->line(($dryRun ? '[dry-run] ' : '')."fetch {$mediaUrl}");
            if (! $dryRun) {
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp])
                        ->timeout(120)
                        ->get($mediaUrl);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()} downloading media.");
                        $failed++;

                        continue;
                    }
                    $size = is_file($tmp) ? filesize($tmp) : 0;
                    if ($size < 500) {
                        @unlink($tmp);
                        $this->error('  suspiciously small ('.$size.' bytes), skipping.');
                        $failed++;

                        continue;
                    }
                    rename($tmp, $localPath);
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage());
                    $failed++;

                    continue;
                }
                $entry->image = $relative;
                $entry->save();
            }
            $this->info("  linked: \"{$entry->title}\" ({$entry->year}) -> {$relative}");
            $linked++;
        }

        $this->info("\nDone. Linked={$linked} NoMatch={$noMatch} EmptyMedia={$emptyMedia} Failed={$failed} Processed={$processed}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }

    private function fetchRowsForDate(int $month, int $day): ?array {
        try {
            $resp = Http::withHeaders([
                'Authorization' => 'Token '.self::TOKEN,
                'User-Agent' => 'NPPC-Archive/1.0',
            ])->timeout(30)->get(self::BASE_URL, [
                'user_field_names' => 'false',
                'filter__'.self::F_MONTH.'__equal' => $month,
                'filter__'.self::F_DAY.'__equal' => $day,
                'size' => 200,
            ]);
            if (! $resp->successful()) {
                return null;
            }
            $data = $resp->json();
            $rows = [];
            foreach (($data['results'] ?? []) as $r) {
                $rows[] = [
                    'id' => $r['id'] ?? null,
                    'year' => $r[self::F_YEAR] ?? null,
                    'month' => $r[self::F_MONTH] ?? null,
                    'day' => $r[self::F_DAY] ?? null,
                    'title' => $this->extractText($r, ['field_177141', 'field_177135', 'field_177142']),
                    'media' => $this->extractText($r, ['field_177143', 'field_177144', 'field_177145']),
                ];
            }

            return $rows;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function extractText(array $row, array $candidateFields): ?string {
        foreach ($candidateFields as $f) {
            $v = $row[$f] ?? null;
            if (is_string($v) && $v !== '') {
                return $v;
            }
            if (is_array($v)) {
                foreach ($v as $sub) {
                    if (is_string($sub) && $sub !== '') {
                        return $sub;
                    }
                    if (is_array($sub) && isset($sub['url'])) {
                        return $sub['url'];
                    }
                }
            }
        }
        foreach ($row as $key => $v) {
            if (! is_string($v) || $v === '') {
                continue;
            }
            if (preg_match('/\.(jpg|jpeg|png|webp|gif)(\?|$)/i', $v)) {
                return $v;
            }
        }

        return null;
    }

    private function pickBestMatch(CalendarEntry $entry, array $rows): ?array {
        if (empty($rows)) {
            return null;
        }
        $candidates = $rows;
        if ($entry->year) {
            $byYear = array_values(array_filter($rows, fn ($r) => (int) ($r['year'] ?? 0) === (int) $entry->year));
            if (! empty($byYear)) {
                $candidates = $byYear;
            }
        }

        $entryTitleNorm = strtolower(trim((string) $entry->title));
        $best = null;
        $bestScore = -1;
        foreach ($candidates as $row) {
            $rowTitle = strtolower(trim((string) ($row['title'] ?? '')));
            $score = 0;
            if (! empty($row['media'])) {
                $score += 10;
            }
            if ($rowTitle !== '' && $entryTitleNorm !== '') {
                similar_text($entryTitleNorm, $rowTitle, $pct);
                $score += $pct;
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $row;
            }
        }

        return $best;
    }
}
