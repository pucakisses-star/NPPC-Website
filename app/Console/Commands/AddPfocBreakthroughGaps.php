<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Add the four Breakthrough issues missed by #487 (Spring 1989,
 * Fall 1990, Summer 1992, Spring 1993), plus the 1969 Weatherman
 * founding document "You Don't Need a Weatherman to Know Which Way
 * the Wind Blows" — the foundational paper of the Weather Underground
 * Organization that became the PFOC tradition.
 *
 * Idempotent.
 */
final class AddPfocBreakthroughGaps extends Command {
    protected $signature = 'archive:add-pfoc-breakthrough-gaps';
    protected $description = 'Add 4 PFOC Breakthrough issues missed by #487 + Weatherman 1969 founding document';

    public function handle(): int {
        $issues = [
            ['17-spring-1989', '1989-03-01', 'Vol. 13 No. 1', 'Spring 1989', 1989],
            ['19-fall-1990',   '1990-09-01', 'Vol. 14 No. 2', 'Fall 1990',   1990],
            ['22-summer-1992', '1992-06-01', 'Vol. 16 No. 1', 'Summer 1992', 1992],
            ['24-spring-1993', '1993-03-01', 'Vol. 17 No. 1', 'Spring 1993', 1993],
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
            $description = "Issue of *Breakthrough*, the magazine of the Prairie Fire Organizing Committee (PFOC) — the above-ground white anti-imperialist organization that emerged in 1975 from the Weather Underground's 1974 Prairie Fire manifesto. {$volno}, {$period}. Mirrored from Internet Archive.";
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

        // Weatherman 1969 founding document
        $wmSlug = 'you-dont-need-a-weatherman-1969';
        $wmPayload = [
            'title' => "You Don't Need a Weatherman to Know Which Way the Wind Blows (1969)",
            'description' => "The founding political document of the Weather Underground Organization, presented at the June 18, 1969 Students for a Democratic Society (SDS) National Convention in Chicago by the faction that called itself the Revolutionary Youth Movement. Co-authored by Karen Ashley, Bill Ayers, Bernardine Dohrn, John Jacobs, Jeff Jones, Gerry Long, Howie Machtinger, Jim Mellen, Terry Robbins, Mark Rudd, and Steve Tappis. The paper takes its title from a line in Bob Dylan's 'Subterranean Homesick Blues' and lays out the analysis of U.S. imperialism, white-skin privilege, anti-racist solidarity with Black liberation, and the necessity of armed struggle that would shape both the Weather Underground's 1970s clandestine practice and — through the 1974 Prairie Fire manifesto — the above-ground Prairie Fire Organizing Committee that succeeded it.",
            'record_type' => 'book',
            'source_format' => 'manifesto',
            'file' => '/pdfs/weather-underground/you-dont-need-a-weatherman-to-know-which-way-the-wind-blows-1969.pdf',
            'collection' => 'Movement Reference',
            'authors' => 'Revolutionary Youth Movement / Weather Underground (Ashley, Ayers, Dohrn, Jacobs, Jones, Long, Machtinger, Mellen, Robbins, Rudd, Tappis)',
            'publisher' => 'Students for a Democratic Society / New Left Notes',
            'year' => 1969,
            'date' => '1969-06-18',
            'subjects' => ['Weather Underground', 'WUO', 'SDS', 'Revolutionary Youth Movement', 'Bernardine Dohrn', 'Bill Ayers', 'Mark Rudd', 'Anti-Imperialism', 'White Anti-Imperialist', 'New Left'],
            'is_digitized' => true,
            'published' => true,
        ];
        $existing = ArchiveRecord::where('slug', $wmSlug)->first();
        if ($existing) { $existing->update($wmPayload); $updated++; }
        else { ArchiveRecord::create(['slug' => $wmSlug] + $wmPayload); $added++; }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
