<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Add the Tilted Scales Collective's defendant support handbook —
 * "A Tilted Guide to Being a Defendant" — to the archive. Surfaced
 * by @PplsCityCouncil tweet (2026-03-06) recommending it as a
 * resource for "all politically active people."
 *
 *   PDF: https://files.libcom.org/files/atiltedguide-web-1.pdf
 *
 * Idempotent — re-runs update the existing record.
 */
final class AddTiltedGuide extends Command {
    protected $signature = 'archive:add-tilted-guide {--force : Re-download even if local file exists}';
    protected $description = "Add the Tilted Scales Collective's 'A Tilted Guide to Being a Defendant'";

    private const SLUG = 'tilted-guide-to-being-a-defendant';
    private const PDF_URL = 'https://files.libcom.org/files/atiltedguide-web-1.pdf';

    public function handle(): int {
        $force = (bool) $this->option('force');

        $dir = public_path('pdfs/mass-defense');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $localPath = $dir.DIRECTORY_SEPARATOR.self::SLUG.'.pdf';
        $webPath = '/pdfs/mass-defense/'.self::SLUG.'.pdf';

        if (! is_file($localPath) || $force || filesize($localPath) < 1000) {
            $this->line('fetch '.self::PDF_URL);
            $tmp = $localPath.'.partial';
            try {
                $resp = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0; +https://nationalpoliticalprisonercoalition.org)',
                ])
                    ->withOptions(['sink' => $tmp, 'allow_redirects' => true])
                    ->timeout(900)
                    ->get(self::PDF_URL);

                if (! $resp->successful() || (is_file($tmp) && filesize($tmp) < 1000)) {
                    @unlink($tmp);
                    $this->error('  download failed — remote URL stored instead.');
                    $webPath = self::PDF_URL;
                } else {
                    rename($tmp, $localPath);
                    $this->info('  saved '.number_format(filesize($localPath) / 1024, 1).' KB to '.$webPath);
                }
            } catch (\Throwable $e) {
                @unlink($tmp);
                $this->error('  '.$e->getMessage().' — remote URL stored.');
                $webPath = self::PDF_URL;
            }
        } else {
            $this->line('exists '.$webPath);
        }

        $record = [
            'title'         => 'A Tilted Guide to Being a Defendant',
            'description'   => "A defendant support handbook from the Tilted Scales Collective written by experienced legal-support activists. The guide draws on the wisdom of dozens of people who have weathered trials and incarceration in political cases — covering pretrial decisions, plea negotiations, cooperation pressure, mental and physical health under indictment, family and community support, sentencing, prison preparation, and post-release reintegration. Distributed by libcom.org / The Tilted Scales Collective. Surfaced via @PplsCityCouncil (March 2026).",
            'record_type'   => 'book',
            'source_format' => 'pdf',
            'file'          => $webPath,
            'collection'    => 'Movement Reference',
            'publisher'     => 'Tilted Scales Collective',
            'authors'       => 'Tilted Scales Collective',
            'subjects'      => ['Mass Defense', 'Defendant Support', 'Legal Support', 'Political Prosecutions', 'Movement Reference'],
            'is_digitized'  => true,
            'published'     => true,
        ];

        $existing = ArchiveRecord::where('slug', self::SLUG)->first();
        if ($existing) {
            $existing->update($record);
            $this->info('RECORD updated: '.$record['title']);
        } else {
            ArchiveRecord::create(['slug' => self::SLUG] + $record);
            $this->info('RECORD added: '.$record['title']);
        }

        return self::SUCCESS;
    }
}
