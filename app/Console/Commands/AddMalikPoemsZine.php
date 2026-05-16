<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Add the third "Malik's Poems" zine for political prisoner Malik
 * Muhammad — sourced from a 2026-04-30 @ADayIn1920 tweet flagging
 * the new release on Internet Archive:
 *
 *   https://archive.org/details/malik-poems-3-imposed
 *
 * Downloads the imposed-print PDF to public/pdfs/prisoner-zines/
 * and registers an ArchiveRecord linked by description to Malik's
 * profile.
 */
final class AddMalikPoemsZine extends Command {
    protected $signature = 'archive:add-malik-poems-zine {--force : Re-download even if local file exists}';
    protected $description = 'Add the third "Malik\'s Poems" zine for Malik Muhammad';

    private const SLUG = 'malik-muhammad-poems-zine-3';
    private const PDF_URL = 'https://archive.org/download/malik-poems-3-imposed/malik-poems-3-imposed.pdf';
    private const ARCHIVE_PAGE = 'https://archive.org/details/malik-poems-3-imposed';

    public function handle(): int {
        $force = (bool) $this->option('force');

        $dir = public_path('pdfs/prisoner-zines');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $localPath = $dir.DIRECTORY_SEPARATOR.self::SLUG.'.pdf';
        $webPath = '/pdfs/prisoner-zines/'.self::SLUG.'.pdf';

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
            'title'         => "Malik's Poems, Vol. 3 (Print Zine)",
            'description'   => "A poetry zine by political prisoner Malik Muhammad, released April 2026 by supporters via Internet Archive. Malik is a Black anarchist organizer from Portland, Oregon prosecuted for actions during the 2020 George Floyd Uprising and currently incarcerated in Oregon state prison. The zine includes his most recent mailing address for letters of solidarity. Source: ".self::ARCHIVE_PAGE,
            'record_type'   => 'zine',
            'source_format' => 'pdf',
            'file'          => $webPath,
            'collection'    => 'Prisoner Solidarity Zines',
            'publisher'     => 'Internet Archive (community upload)',
            'authors'       => 'Malik Muhammad',
            'year'          => 2026,
            'subjects'      => ['Malik Muhammad', 'Prisoner Writing', 'Poetry', '2020 Uprising', 'Anarchist'],
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
