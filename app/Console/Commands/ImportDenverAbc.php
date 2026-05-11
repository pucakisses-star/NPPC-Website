<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Imports the (small) set of PDFs surfaced from Denver Anarchist Black Cross's
 * /resources/ page that were still live at the original URLs. Most of DABC's
 * resource list links to long-dead URLs (abcf.net/abc/pdfs/, june11.org,
 * whoisleonardpeltier.info, etc.) — we grab only what loads.
 */
final class ImportDenverAbc extends Command {
    protected $signature = 'archive:import-denver-abc';
    protected $description = 'Import PDFs surfaced from Denver Anarchist Black Cross\'s resources page';

    public function handle(): int {
        $items = [
            ['supportmarius-flyer', 'Support Marius Mason — Flyer', 'Flyer in support of Earth Liberation Front political prisoner Marius Mason (Marie at the time of publication). Sourced via denverabc.wordpress.com from supportmarie.files.wordpress.com.',
                'flyer', ['Marius Mason', 'Earth Liberation', 'Green Scare', 'Political Prisoners']],
            ['supportmarius-hello-family', 'Support Marius Mason — Hello Family', 'Movement-family letter on supporting Earth Liberation Front political prisoner Marius Mason. Sourced via denverabc.wordpress.com.',
                'flyer', ['Marius Mason', 'Earth Liberation', 'Green Scare', 'Political Prisoners']],
            ['graham-defense-leaflet', 'John Graham Defense — Leaflet', 'Defense leaflet for John Graham, the American Indian Movement member tried for the 1975 killing of Anna Mae Pictou-Aquash on the Pine Ridge reservation. Graham, a Yukon native, was extradited to South Dakota in 2007 and convicted in 2010 in a case AIM supporters argued was deeply contaminated by the FBI\'s long-running infiltration of the movement. Sourced via denverabc.wordpress.com from grahamdefense.org.',
                'flyer', ['John Graham', 'AIM', 'Indigenous Resistance', 'COINTELPRO', 'Political Prisoners']],
            ['cuban-five-print-bios', 'The Cuban Five — Printable Biographies', 'Biographical handout on the Cuban Five (Gerardo Hernández, Ramón Labañino, Antonio Guerrero, Fernando González, René González) — Cuban intelligence officers convicted in 2001 in Miami federal court of conspiracy to commit espionage. International campaigns by 2014 secured their full release. Sourced via denverabc.wordpress.com from freethefive.org.',
                'flyer', ['Cuban Five', 'Anti-Imperialism', 'Political Prisoners']],
            ['cuban-five-12th-anniversary', 'The Cuban Five — 12th Anniversary Flyer', '12th-anniversary flyer (2010) for the Cuban Five international solidarity campaign. Sourced via denverabc.wordpress.com from freethefive.org.',
                'flyer', ['Cuban Five', 'Anti-Imperialism', 'Political Prisoners']],
            ['cuban-five-olga-adriana', 'The Cuban Five — Olga and Adriana', 'Solidarity flyer on Olga Salanueva and Adriana Pérez, the wives of imprisoned Cuban Five members denied U.S. visas to visit their husbands. Sourced via denverabc.wordpress.com from freethefive.org.',
                'flyer', ['Cuban Five', 'Anti-Imperialism', 'Political Prisoners']],
        ];

        $created = 0;
        $updated = 0;
        $sort = 500;
        foreach ($items as $i) {
            [$slug, $title, $desc, $sourceFormat, $subjects] = $i;
            $payload = [
                'title' => $title,
                'description' => $desc.' Sourced from denverabc.wordpress.com.',
                'record_type' => 'document',
                'source_format' => $sourceFormat,
                'file' => '/pdfs/denver-abc/'.$slug.'.pdf',
                'thumbnail' => '/images/archive/denver-abc/'.$slug.'-cover.jpg',
                'collection' => 'Denver Anarchist Black Cross',
                'publisher' => 'Denver Anarchist Black Cross (denverabc.wordpress.com)',
                'subjects' => $subjects,
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];

            $archiveSlug = 'denver-abc-'.$slug;
            $existing = ArchiveRecord::where('slug', $archiveSlug)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $archiveSlug] + $payload);
                $created++;
            }
        }

        $this->info("Done. Created={$created} Updated={$updated}");

        return self::SUCCESS;
    }
}
