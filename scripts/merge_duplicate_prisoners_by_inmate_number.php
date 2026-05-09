<?php

declare(strict_types=1);

/**
 * Merge nine sets of duplicate prisoner records that share an
 * inmate_number. For each group:
 *   - Pick the record with the longest description as the canonical
 *     keeper; the rest are dupes.
 *   - Concatenate every unique description from canon + dupes (in
 *     descending length order) onto the canonical record's
 *     description, separated by a single space, with no other text
 *     edits — per the user's instruction not to remove any bio.
 *   - Promote each dupe's name into the canonical record's aka
 *     field (preserving any existing aka values too).
 *   - Union the ideologies and affiliation JSON arrays.
 *   - For every other scalar/date column, keep the canon's value if
 *     it's non-null, otherwise pull in a non-null value from a dupe.
 *   - Reassign all PrisonerCase, PodcastEpisode, and CalendarEntry
 *     rows that point at a dupe to point at the canonical record.
 *   - Delete the dupe prisoner rows.
 *
 * Skipped (genuine data conflict, NOT the same person): the
 * 26548-050 row pair (Abdulrahman Odeh and Ciaron O'Reilly).
 *
 * Idempotent: re-running after a successful merge is a no-op
 * because the dupe rows no longer exist.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$isBlank = function ($v): bool {
    if ($v === null) return true;
    if (is_string($v) && trim($v) === '') return true;
    if (is_array($v) && count($v) === 0) return true;
    return false;
};

$groups = [
    '00010-099' => 'Freddie Hilton / Kamau Sadiki',
    '00816-111' => 'Stephen Michael Kelly / Steve Kelly / Steve Kelly SJ',
    '03229-045' => 'Paul Kabat / Rev. Paul Kabat',
    '10375-016' => 'Carol Ann Manning / Carol Saucier Manning',
    '25201-075' => 'Michael Giron / Michael A. Giron / Michael Little Feather Giron',
    '28361-013' => 'Sachio Oliver Coe / Oliver Sachio Coe',
    '86275-020' => 'William Bichsel / Bill Bichsel',
    '88968-024' => 'Adolfo Matos Antogiorgi / Adolfo Matos',
    '88971-024' => 'Dylcia Pagán / Dylcia Pagan',
];

$mergedGroups = 0;
$mergedRows   = 0;

foreach ($groups as $num => $label) {
    $rows = Prisoner::where('inmate_number', $num)->get();
    if ($rows->count() < 2) {
        echo "  [{$num}] only " . $rows->count() . " row(s) — already merged or unexpected. Skipping.\n";
        continue;
    }

    $sorted = $rows->sortByDesc(fn ($p) => mb_strlen((string) $p->description))
                   ->values();
    $canon  = $sorted->first();
    $dupes  = $sorted->slice(1);

    echo "[{$num}] canonical: {$canon->name} (id={$canon->id})\n";
    foreach ($dupes as $d) echo "          merging:   {$d->name} (id={$d->id})\n";

    DB::transaction(function () use ($canon, $dupes, $isBlank) {
        $bios = [];
        $seen = [];
        foreach (collect([$canon])->merge($dupes) as $row) {
            $b = trim((string) $row->description);
            if ($b === '') continue;
            $key = mb_strtolower(preg_replace('/\s+/', ' ', $b));
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $bios[] = $b;
        }
        $canon->description = implode(' ', $bios) ?: null;

        $akas = [];
        if (! empty($canon->aka)) {
            foreach (preg_split('/\s*,\s*/', $canon->aka) as $a) {
                $a = trim($a);
                if ($a !== '') $akas[] = $a;
            }
        }
        foreach ($dupes as $d) {
            if (! empty($d->aka)) {
                foreach (preg_split('/\s*,\s*/', $d->aka) as $a) {
                    $a = trim($a);
                    if ($a !== '') $akas[] = $a;
                }
            }
            if (! empty($d->name) && $d->name !== $canon->name) {
                $akas[] = $d->name;
            }
        }
        $akas = array_values(array_unique(array_filter($akas, fn ($a) => $a !== $canon->name)));
        $canon->aka = $akas ? implode(', ', $akas) : $canon->aka;

        foreach (['ideologies', 'affiliation'] as $arr) {
            $combined = is_array($canon->$arr) ? $canon->$arr : [];
            foreach ($dupes as $d) {
                if (is_array($d->$arr)) $combined = array_merge($combined, $d->$arr);
            }
            $combined = array_values(array_unique(array_filter($combined, fn ($v) => $v !== null && $v !== '')));
            $canon->$arr = $combined ?: null;
        }

        $skipCols = [
            'id', 'created_at', 'updated_at', 'description', 'aka',
            'ideologies', 'affiliation', 'name', 'first_name',
            'middle_name', 'last_name', 'slug', 'inmate_number',
        ];
        $cols = Schema::getColumnListing('prisoners');
        foreach ($cols as $col) {
            if (in_array($col, $skipCols, true)) continue;
            $canonVal = $canon->getAttribute($col);
            if (! $isBlank($canonVal)) continue;
            foreach ($dupes as $d) {
                $v = $d->getAttribute($col);
                if (! $isBlank($v)) { $canon->setAttribute($col, $v); break; }
            }
        }

        foreach ($dupes as $d) {
            DB::table('prisoner_cases')->where('prisoner_id', $d->id)->update(['prisoner_id' => $canon->id]);
            if (Schema::hasTable('podcast_episodes')) {
                DB::table('podcast_episodes')->where('prisoner_id', $d->id)->update(['prisoner_id' => $canon->id]);
            }
            if (Schema::hasTable('calendar_entries')) {
                DB::table('calendar_entries')->where('prisoner_id', $d->id)->update(['prisoner_id' => $canon->id]);
            }
        }

        $canon->save();

        foreach ($dupes as $d) {
            $d->delete();
        }
    });

    $mergedGroups++;
    $mergedRows += $dupes->count();
}

echo "\nDone. Merged {$mergedGroups} group(s); deleted {$mergedRows} dupe row(s).\n";
