<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register the Prairie Fire Organizing Committee's Breakthrough
 * magazine as individual archive records — 22 issues spanning
 * 1977-1995 mirrored locally at /pdfs/pfoc-breakthrough/. Each
 * issue gets its own ArchiveRecord with date, volume/number, title.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddPfocBreakthroughArchive extends Command {
    protected $signature = 'archive:add-pfoc-breakthrough';
    protected $description = 'Register the PFOC Breakthrough magazine run (1977-1995, 22 issues)';

    public function handle(): int {
        $issues = [
            ['1-march-1977',  '1977-03-01', 'Vol. 1 No. 1',  'March 1977',           1977],
            ['2-june-july-1977', '1977-06-01', 'Vol. 1 No. 2', 'June–July 1977',     1977],
            ['3-october-december-1977', '1977-10-01', 'Vol. 1 Nos. 3–4', 'Oct.–Dec. 1977 (double issue)', 1977],
            ['4-spring-1978', '1978-03-01', 'Vol. 2 No. 1', 'Spring 1978',           1978],
            ['5-fall-1978',   '1978-09-01', 'Vol. 2 No. 2', 'Fall 1978',             1978],
            ['6-spring-1979', '1979-03-01', 'Vol. 3 No. 1', 'Spring 1979',           1979],
            ['7-winter-1980', '1980-01-01', 'Vol. 4 No. 1', 'Winter 1980',           1980],
            ['8-spring-1981', '1981-03-01', 'Vol. 5 No. 1', 'Spring 1981',           1981],
            ['9-spring-1982', '1982-03-01', 'Vol. 6 No. 1', 'Spring 1982',           1982],
            ['10-winter-1983', '1983-01-01', 'Vol. 7 No. 1', 'Winter 1983',          1983],
            ['11-summer-1984', '1984-06-01', 'Vol. 8 No. 1', 'Summer 1984',          1984],
            ['12-spring-summer-1985', '1985-03-01', 'Vol. 9 No. 1', 'Spring/Summer 1985', 1985],
            ['13-spring-summer-1986', '1986-03-01', 'Vol. 10 No. 1', 'Spring/Summer 1986', 1986],
            ['14-winter-spring-1987', '1987-01-01', 'Vol. 11 No. 1', 'Winter/Spring 1987', 1987],
            ['15-fall-1987',   '1987-09-01', 'Vol. 11 No. 2', 'Fall 1987',           1987],
            ['16-summer-1988', '1988-06-01', 'Vol. 12 No. 1', 'Summer 1988',         1988],
            ['18-winter-1990', '1990-01-01', 'Vol. 14 No. 1', 'Winter 1990',         1990],
            ['20-winter-1991', '1991-01-01', 'Vol. 15 No. 1', 'Winter 1991',         1991],
            ['21-summer-1991', '1991-06-01', 'Vol. 15 No. 2', 'Summer 1991',         1991],
            ['23-fall-1992',   '1992-09-01', 'Vol. 16 No. 2', 'Fall 1992',           1992],
            ['25-spring-1994', '1994-03-01', 'Vol. 18 No. 1', 'Spring 1994',         1994],
            ['27-summer-1995', '1995-06-01', 'Vol. 19 No. 1', 'Summer 1995',         1995],
        ];

        $base = [
            'record_type' => 'magazine',
            'source_format' => 'periodical',
            'collection' => 'Prairie Fire Organizing Committee — Breakthrough',
            'authors' => 'Prairie Fire Organizing Committee (PFOC)',
            'publisher' => 'Prairie Fire Organizing Committee',
            'subjects' => ['Prairie Fire Organizing Committee', 'PFOC', 'Breakthrough', 'Anti-Imperialism', 'White Anti-Imperialist', 'Political Prisoners', 'New Left'],
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($issues as [$ref, $date, $volno, $period, $year]) {
            $slug = 'pfoc-breakthrough-'.$ref;
            $title = "Breakthrough ({$volno}, {$period}) — PFOC";
            $description = "Issue of *Breakthrough*, the magazine of the Prairie Fire Organizing Committee (PFOC) — the above-ground white anti-imperialist organization that emerged in 1975 from the Weather Underground's 1974 Prairie Fire manifesto. PFOC was the principal U.S. white-radical formation supporting Black, Puerto Rican, Native American, and Chicano national liberation movements through the 1970s and 1980s, and one of the most consistent sites of political-prisoner support work in that period. {$volno}, {$period}. Mirrored from Internet Archive.";
            $payload = $base + [
                'title' => $title,
                'description' => $description,
                'file' => "/pdfs/pfoc-breakthrough/pfoc-breakthrough-{$ref}.pdf",
                'year' => $year,
                'date' => $date,
                'volume' => $volno,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($payload); $updated++; }
            else { ArchiveRecord::create(['slug' => $slug] + $payload); $added++; }
        }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
