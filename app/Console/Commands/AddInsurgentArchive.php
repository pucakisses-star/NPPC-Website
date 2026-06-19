<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Adds the Freedom Archives "The Insurgent" collection (collection 169) to the
 * archive: the six surviving items of The Insurgent, newsletter of the
 * Committee to Fight Repression — a 1980s U.S. political-prisoner support
 * publication. The PDFs and first-page cover thumbnails are committed to the
 * repo (so they ship with a git pull); each record is created/updated by slug,
 * and a missing PDF falls back to a re-download from freedomarchives.org.
 * Idempotent.
 */
final class AddInsurgentArchive extends Command
{
    protected $signature = 'archive:add-insurgent {--force : Re-download PDFs even if present}';

    protected $description = "Add the Freedom Archives 'The Insurgent' collection (6 items) to the archive";

    private const FA_BASE = 'https://www.freedomarchives.org/Documents/Finder/DOC510_scans/Insurgent/';

    private const PUB_INTRO = 'The Insurgent was the newsletter of the Committee to Fight Repression, a 1980s publication produced by friends and families of U.S. political prisoners and prisoners of war. It covered the federal prison control units (Marion and the new women\'s unit at Lexington), Puerto Rican independence prisoners, Black liberation, grand-jury resistance, and anti-imperialist solidarity.';

    public function handle(): int
    {
        $dir = public_path('pdfs/the-insurgent');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $added = 0;
        $updated = 0;

        foreach ($this->records() as $r) {
            $slug = $r['slug'];
            $localPdf = $dir.DIRECTORY_SEPARATOR.$slug.'.pdf';
            $webPdf = '/pdfs/the-insurgent/'.$slug.'.pdf';

            if (! is_file($localPdf) || $this->option('force') || filesize($localPdf) < 1000) {
                $url = self::FA_BASE.$r['fa'];
                $this->line('fetch '.$url);
                $tmp = $localPdf.'.partial';
                try {
                    $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0; +https://nationalpoliticalprisonercoalition.org)'])
                        ->withOptions(['sink' => $tmp, 'allow_redirects' => true])
                        ->timeout(900)->retry(3, 2000)->get($url);
                    if (! $resp->successful() || (is_file($tmp) && filesize($tmp) < 1000)) {
                        @unlink($tmp);
                        $this->error('  download failed — remote URL stored.');
                        $webPdf = $url;
                    } else {
                        rename($tmp, $localPdf);
                        $this->info('  saved '.number_format(filesize($localPdf) / 1024, 1).' KB');
                    }
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage().' — remote URL stored.');
                    $webPdf = $url;
                }
            }

            $thumbRel = '/thumbnails/'.$slug.'.jpg';
            $thumb = is_file(public_path('thumbnails/'.$slug.'.jpg')) ? $thumbRel : null;

            $payload = [
                'title' => $r['title'],
                'description' => $r['description'],
                'record_type' => 'document',
                'source_format' => $r['format'],
                'file' => $webPdf,
                'thumbnail' => $thumb,
                'collection' => 'The Insurgent',
                'publisher' => 'Committee to Fight Repression',
                'year' => $r['year'] ?? null,
                'subjects' => $r['subjects'],
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $this->info('RECORD updated: '.$r['title']);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $slug] + $payload);
                $this->info('RECORD added: '.$r['title']);
                $added++;
            }
        }

        $this->info("\nDone. Added={$added} Updated={$updated}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        $core = ['Political Prisoners', 'State Repression', 'Anti-Imperialism', 'Committee to Fight Repression'];

        return [
            [
                'slug' => 'the-insurgent-newsletter-proposal',
                'fa' => '510.insurgent.newsletter.proposal.pdf',
                'title' => 'The Insurgent — Newsletter Proposal',
                'format' => 'document',
                'subjects' => $core,
                'description' => self::PUB_INTRO.' This short proposal was circulated as The Insurgent and the allied Outside Agitator were winding down (c. 1989), by friends and family of political prisoners seeking to launch a larger, more comprehensive quarterly magazine for and about U.S. political prisoners and state repression, jointly produced by incarcerated and outside collectives.',
            ],
            [
                'slug' => 'the-insurgent-vol-2-no-3-winter-1986',
                'fa' => '510.insurgent.vol2.no3.Winter1986.pdf',
                'title' => 'The Insurgent, Vol. 2 No. 3 (Winter 1986)',
                'format' => 'periodical',
                'year' => 1986,
                'subjects' => array_merge($core, ['Control Units', 'Puerto Rican Independence', 'United Freedom Front']),
                'description' => self::PUB_INTRO.' This Winter 1986 issue (Vol. 2, No. 3) leads with the opening of the Lexington high-security control unit for women ("Shut It Down!") and carries communiqués from Puerto Rico, an interview on sanctuary, coverage of United Freedom Front defendants Tom Manning and Richard Williams on trial, and the founding of a Black Panther Party foundation.',
            ],
            [
                'slug' => 'the-insurgent-vol-3-no-2-fall-1987',
                'fa' => '510.insurgent.vol3.no2.Fall1987.pdf',
                'title' => 'The Insurgent, Vol. 3 No. 2 (Fall 1987)',
                'format' => 'periodical',
                'year' => 1987,
                'subjects' => array_merge($core, ['Assata Shakur', 'Puerto Rican Independence', 'Control Units', 'AIDS in Prison']),
                'description' => self::PUB_INTRO.' This Fall 1987 issue (Vol. 3, No. 2) features "Assata Shakur is Alive and Well in Cuba!", an interview on Puerto Rican independence prisoners, coverage of Iran/Contragate, fighting AIDS in prison, and an update on the Lexington control unit.',
            ],
            [
                'slug' => 'the-insurgent-vol-4-no-1-womens-day-1988',
                'fa' => '510.insurgent.vol4.no1.Summer1987.pdf',
                'title' => "The Insurgent, Vol. 4 No. 1 — International Women's Day Special Issue (Winter 1988)",
                'format' => 'periodical',
                'year' => 1988,
                'subjects' => array_merge($core, ['Women Political Prisoners', "Women's Liberation"]),
                'description' => self::PUB_INTRO.' This International Women\'s Day 1988 special issue (Vol. 4, No. 1) centers on women political prisoners and women\'s liberation.',
            ],
            [
                'slug' => 'the-insurgent-vol-4-no-3-1988',
                'fa' => '510.insurgent.vol4.no3.Summer1988.pdf',
                'title' => 'The Insurgent, Vol. 4 No. 3 (1988)',
                'format' => 'periodical',
                'year' => 1988,
                'subjects' => array_merge($core, ['United Freedom Front', 'Ohio 7', 'Sedition', 'Puerto Rican Independence', 'Grand Jury Resistance']),
                'description' => self::PUB_INTRO.' This issue (Vol. 4, No. 3) leads with "Ohio 7 On Trial for Seditious Conspiracy" — the United Freedom Front sedition case — alongside El Grito de Lares in Puerto Rico, the case of Silvia Baraldini, and grand-jury resisters.',
            ],
            [
                'slug' => 'the-insurgent-vol-5-no-1-spring-1989',
                'fa' => '510.insurgent.vol5.no1.Summer1989.pdf',
                'title' => 'The Insurgent, Vol. 5 No. 1 (Spring 1989)',
                'format' => 'periodical',
                'year' => 1989,
                'subjects' => array_merge($core, ['Resistance Conspiracy Case', 'Ohio 7', 'Sedition', 'Hunger Strikes']),
                'description' => self::PUB_INTRO.' This Spring 1989 issue (Vol. 5, No. 1), "Support the Resistance Conspiracy Defendants," covers the opening of the Resistance Conspiracy and Ohio 7 sedition trials and hunger strikes in South Africa and West Germany.',
            ],
        ];
    }
}
