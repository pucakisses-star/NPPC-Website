<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Add Black Panther Party newspaper records to the NPPC archive.
 *
 * Three records:
 *  1. The founding April 25, 1967 issue (Vol 1 No 1), mirrored
 *     locally at /pdfs/bpp-newspaper/. The single most important
 *     historical issue of the BPP newspaper — the launch number,
 *     written from the Bay Area chapter founded the previous fall
 *     by Huey P. Newton and Bobby Seale.
 *
 *  2. The 1967–1968 bulk Internet Archive collection covering
 *     Vol 1 (full year, six issues) and the surviving subset of
 *     Vol 2 (12 issues), registered as an external IA reference.
 *
 *  3. The broader 1967–1970 uploader collection on Internet Archive
 *     (jadelin.mcleod uploader, 66 issues), registered as an
 *     external IA reference covering nearly the full national
 *     publication run from launch through the 1970 zenith.
 *
 * Mirroring the full back-issue run locally would be ~425 MB for
 * 1967–1968 alone, so the external references are the scalable path
 * — same pattern used for the TWWA FOIA file set in #471.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddBppNewspaperArchive extends Command {
    protected $signature = 'archive:add-bpp-newspaper';
    protected $description = 'Add Black Panther Party newspaper records (founding issue + IA collection references)';

    public function handle(): int {
        $records = [
            [
                'slug' => 'the-black-panther-vol-1-no-1-april-1967-founding-issue',
                'title' => 'The Black Panther: Black Community News Service — Vol. 1 No. 1 (April 25, 1967, founding issue)',
                'description' => "The founding issue of The Black Panther: Black Community News Service, the official newspaper of the Black Panther Party for Self-Defense. Published April 25, 1967 from the Party's Bay Area headquarters, less than six months after Huey P. Newton and Bobby Seale founded the BPP in Oakland in October 1966. The four-page launch number carried the editorial that became the prototype for the paper's signature voice — radical Black self-defense reportage, community-survival programs, photographs and graphics by Emory Douglas (whose visual identity would define the paper for the next decade), and direct dispatches from Black communities under police occupation. The Black Panther would go on to become the largest-circulation U.S. Black radical publication of the twentieth century, peaking in 1970 at a claimed national/international circulation of more than 150,000. This is where it started.",
                'record_type' => 'newspaper',
                'source_format' => 'newspaper',
                'file' => '/pdfs/bpp-newspaper/the-black-panther-vol-01-no-01-1967-04-25.pdf',
                'collection' => 'The Black Panther Newspaper',
                'authors' => 'Black Panther Party for Self-Defense',
                'publisher' => 'Black Panther Party for Self-Defense (Oakland, CA)',
                'year' => 1967,
                'date' => '1967-04-25',
                'volume' => 'Vol. 1 No. 1',
                'subjects' => ['Black Panther Party', 'BPP', 'The Black Panther', 'Huey P. Newton', 'Bobby Seale', 'Emory Douglas', 'Black Liberation', 'Self-Defense', 'Oakland'],
            ],
            [
                'slug' => 'the-black-panther-newspaper-1967-1968-ia-collection',
                'title' => 'The Black Panther Newspaper, 1967–1968 (Internet Archive collection — Vols. 1–2, 18 issues)',
                'description' => "Internet Archive bulk collection mirroring eighteen issues of The Black Panther: Black Community News Service covering 1967 and 1968 — the BPP newspaper's foundational period. Includes the complete six-issue run of Volume 1 (April–December 1967) and twelve of the fifteen Volume 2 issues from 1968. The 1967–1968 issues document the Party's transition from a local Oakland self-defense formation to a national Black liberation organization: the Sacramento State Capitol armed lobby (May 2, 1967), the October 1967 Oakland police-confrontation that led to Huey Newton's manslaughter conviction, the launch of the Free Huey campaign, the expansion of the survival programs, and the emergence of Emory Douglas's revolutionary-graphic visual identity. Registered for the NPPC archive as an external IA collection reference; individual issues are downloadable directly from Internet Archive.",
                'record_type' => 'collection',
                'source_format' => 'newspaper run',
                'file' => 'https://archive.org/details/the-black-panther-newspaper-1967-1968',
                'collection' => 'The Black Panther Newspaper',
                'authors' => 'Black Panther Party for Self-Defense',
                'publisher' => 'Black Panther Party for Self-Defense / mirrored on Internet Archive',
                'year' => 1967,
                'date' => '1967-04-25',
                'subjects' => ['Black Panther Party', 'BPP', 'The Black Panther', 'Huey P. Newton', 'Bobby Seale', 'Eldridge Cleaver', 'Free Huey', 'Black Liberation', 'Newspaper Archive'],
            ],
            [
                'slug' => 'the-black-panther-newspaper-1967-1970-ia-full-run',
                'title' => 'The Black Panther Newspaper, 1967–1970 (Internet Archive — 66-issue national publication run)',
                'description' => "Internet Archive's 66-issue digitization of The Black Panther: Black Community News Service spanning the BPP newspaper's full national publication run from the founding April 25, 1967 issue through the 1970 zenith — including the complete surviving 1969 cohort (47 issues), the bulk of 1968 (12 issues), the founding 1967 issue, and the 1970 transition months. 1969–1970 represented the paper's peak circulation and political reach, with the issues covering the Chicago Police / FBI raid that killed Fred Hampton and Mark Clark (December 4, 1969), the December 1969 LAPD SWAT raid on the Los Angeles chapter, the Panther 21 trial in New York, the Bobby Seale gag-and-chain at the Chicago Eight trial, and the founding of the People's Free Medical Clinics. The single largest digitized run of The Black Panther freely accessible online. Registered for the NPPC archive as an external IA reference; this is the working bibliography for any scholar or researcher of the BPP press.",
                'record_type' => 'collection',
                'source_format' => 'newspaper run',
                'file' => 'https://archive.org/search.php?query=uploader%3A%22jadelin.mcleod%40gmail.com%22+AND+title%3A%22Black+Panther%22&sort=date',
                'collection' => 'The Black Panther Newspaper',
                'authors' => 'Black Panther Party',
                'publisher' => 'Black Panther Party / Internet Archive',
                'year' => 1969,
                'date' => '1969-01-01',
                'subjects' => ['Black Panther Party', 'BPP', 'The Black Panther', 'Fred Hampton', 'Mark Clark', 'Panther 21', 'Bobby Seale', 'COINTELPRO', 'Black Liberation', 'Newspaper Archive'],
            ],
        ];

        $base = [
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($records as $r) {
            $slug = $r['slug']; unset($r['slug']);
            $payload = $r + $base;
            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $this->info("RECORD updated: {$payload['title']}");
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $slug] + $payload);
                $this->info("RECORD added: {$payload['title']}");
                $added++;
            }
        }
        $this->line("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
