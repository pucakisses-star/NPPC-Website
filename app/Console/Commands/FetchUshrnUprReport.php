<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Adds two PP archive records the user surfaced:
 *
 * 1. The 2010 USHRN joint stakeholder report on U.S. political
 *    prisoners submitted to the UN UPR Session 09 (Annex 23).
 * 2. The Critical Resistance "Political Prisoners Write" file
 *    from the Freedom Archives Literary Prisoners scan set.
 *
 * (The user-mentioned MXMG Political Prisoner Handbook is already
 * imported via freedom-archives-collection-29.json — skipped.)
 */
final class FetchUshrnUprReport extends Command {
    protected $signature = 'archive:fetch-misc-pp-reports {--force : Re-download even if local file exists}';
    protected $description = 'Add 2010 USHRN UPR Annex 23 report + Critical Resistance "Political Prisoners Write"';

    public function handle(): int {
        $force = (bool) $this->option('force');

        $items = [
            [
                'slug' => 'ushrn-upr-2010-annex-23-us-political-prisoners',
                'pdf_url' => 'https://upr-info.org/sites/default/files/documents/2013-10/ushrn_upr_usa_s09_2010_annex23_us_political_prisoners_joint_report_usa.pdf',
                'public_dir' => 'pdfs/ushrn',
                'record' => [
                    'title' => 'US Political Prisoners — Joint Stakeholder Report to the UN UPR (Annex 23)',
                    'description' => 'Joint stakeholder report submitted by the U.S. Human Rights Network (USHRN) and partner organizations to the United Nations Human Rights Council\'s Universal Periodic Review (UPR) Session 09 in 2010. Annex 23 documents the continued imprisonment of U.S. political prisoners — including former Black Panther Party members, Puerto Rican independentistas, MOVE members, and others — and calls on the U.S. government to acknowledge and address its political-prisoner problem under international human rights law.',
                    'collection' => 'UN UPR Submissions',
                    'publisher' => 'U.S. Human Rights Network (USHRN)',
                    'authors' => 'U.S. Human Rights Network (USHRN) and partner organizations',
                    'year' => 2010,
                    'subjects' => ['UN UPR', 'Human Rights', 'US Political Prisoners', 'International Law', 'USHRN'],
                ],
            ],
            [
                'slug' => 'critical-resistance-political-prisoners-write',
                'pdf_url' => 'https://www.freedomarchives.org/Documents/Finder/DOC510_scans/Literary_Prisoners/510.political.prisoners.write.critical.resistance.pdf',
                'public_dir' => 'pdfs/freedom-archives',
                'record' => [
                    'title' => 'Political Prisoners Write (Critical Resistance)',
                    'description' => 'Compilation of writings by U.S. political prisoners published by Critical Resistance. From the Freedom Archives\' Literary Prisoners scan set.',
                    'collection' => 'Freedom Archives — Literary Prisoners',
                    'publisher' => 'Critical Resistance',
                    'authors' => 'Various political prisoners',
                    'subjects' => ['Critical Resistance', 'Prisoner Writings', 'Political Prisoners', 'Prison Abolition'],
                ],
            ],
        ];

        $downloaded = 0;
        $registered = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($items as $item) {
            $slug = $item['slug'];
            $dir = public_path($item['public_dir']);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $localPath = $dir.'/'.$slug.'.pdf';
            $webPath = '/'.$item['public_dir'].'/'.$slug.'.pdf';

            if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
                $this->line("fetch {$item['pdf_url']}");
                $tmp = $localPath.'.partial';
                try {
                    $resp = Http::withHeaders([
                        'User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)',
                    ])
                        ->withOptions(['sink' => $tmp])
                        ->timeout(600)
                        ->get($item['pdf_url']);
                    if (! $resp->successful()) {
                        @unlink($tmp);
                        $this->error("  HTTP {$resp->status()}");
                        $failed++;

                        continue;
                    }
                    $size = is_file($tmp) ? filesize($tmp) : 0;
                    if ($size < 1000) {
                        @unlink($tmp);
                        $this->error("  suspiciously small ({$size} bytes)");
                        $failed++;

                        continue;
                    }
                    rename($tmp, $localPath);
                    $this->info('  saved '.number_format($size / 1024, 1).' KB to '.$webPath);
                    $downloaded++;
                } catch (\Throwable $e) {
                    @unlink($tmp);
                    $this->error('  '.$e->getMessage());
                    $failed++;

                    continue;
                }
            } else {
                $this->line('exists '.$webPath);
                $skipped++;
            }

            $record = $item['record'] + [
                'record_type' => 'document',
                'source_format' => 'pdf',
                'file' => $webPath,
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info('  RECORD updated: '.$record['title']);
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info('  RECORD added: '.$record['title']);
            }
            $registered++;
        }

        $this->info("\nDone. Downloaded={$downloaded} Skipped={$skipped} Registered={$registered} Failed={$failed}");

        return self::SUCCESS;
    }
}
