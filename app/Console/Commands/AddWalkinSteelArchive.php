<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register all three known issues of *Walkin' Steel*, the newsletter
 * of the Committee to End the Marion Lockdown (CEML) — the principal
 * 1990s campaign against the federal control-unit prison at Marion,
 * Illinois (the post-Alcatraz federal supermax). Issues mirrored from
 * archive.org and self-hosted at /pdfs/periodicals/walkin-steel/.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddWalkinSteelArchive extends Command {
    protected $signature = 'archive:add-walkin-steel';
    protected $description = 'Register the CEML Walkin\' Steel newsletter (Vol. 1 Nos. 1–3, 1991–1992)';

    public function handle(): int {
        $issues = [
            ['01-no-1-spring-1991', '1991-03-01', 'Vol. 1 No. 1', 'Spring 1991', 1991],
            ['01-no-2-fall-1991',   '1991-09-01', 'Vol. 1 No. 2', 'Fall 1991',   1991],
            ['01-no-3-spring-1992', '1992-03-01', 'Vol. 1 No. 3', 'Spring 1992', 1992],
        ];

        $base = [
            'record_type' => 'newsletter',
            'source_format' => 'periodical',
            'collection' => 'Committee to End the Marion Lockdown — Walkin\' Steel',
            'authors' => 'Committee to End the Marion Lockdown (CEML)',
            'publisher' => 'Committee to End the Marion Lockdown',
            'subjects' => [
                'Committee to End the Marion Lockdown',
                'CEML',
                'Marion Federal Penitentiary',
                'Control Unit Prisons',
                'Supermax',
                'Solitary Confinement',
                'Political Prisoners',
                'Prison Abolition',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($issues as [$ref, $date, $volno, $period, $year]) {
            $slug = 'walkin-steel-vol-'.$ref;
            $title = "Walkin' Steel ({$volno}, {$period}) — CEML";
            $description = "Issue of *Walkin' Steel*, the newsletter of the Committee to End the Marion Lockdown (CEML). CEML was the principal U.S. campaign of the late 1980s and 1990s against the federal control-unit prison at Marion, Illinois — the post-Alcatraz federal supermax that held political prisoners including Sekou Odinga, Oscar López Rivera, Sundiata Acoli, Bashir Hameed, and others. The newsletter coordinated correspondence, court-monitoring, congressional pressure, and movement education on control-unit conditions, lockdown regimes, and the broader use of long-term solitary confinement as a political weapon — work that prefigured the later anti-supermax and anti-solitary movement. {$volno}, {$period}. Mirrored from Internet Archive.";
            $payload = $base + [
                'title' => $title,
                'description' => $description,
                'file' => "/pdfs/periodicals/walkin-steel/walkin-steel-vol-{$ref}.pdf",
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
