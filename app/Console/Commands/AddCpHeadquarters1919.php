<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Add the Bureau of Investigation surveillance report "A Visit to Communist
 * Party Headquarters, Chicago — Oct. 14, 1919" (by Special Agent A.H. Loula)
 * to the archive. A primary source documenting the federal surveillance of
 * the newly founded Communist Party of America; it names party leaders
 * including Joseph Kowalski (added separately as a prisoner record).
 *
 *   PDF: https://www.marxists.org/history/usa/parties/cpusa/1919/10/1014-loula-cpheadquarters.pdf
 *   Original: DoJ/BoI Investigative Files, NARA M-1085, reel 922, file 202600-14.
 *   Republished by 1000 Flowers Publishing (2007), ed. Tim Davenport.
 *
 * Idempotent — re-runs update the existing record.
 */
final class AddCpHeadquarters1919 extends Command
{
    protected $signature = 'archive:add-cp-headquarters-1919 {--force : Re-download even if local file exists}';

    protected $description = "Add the 1919 BoI report 'A Visit to Communist Party Headquarters, Chicago' to the archive";

    private const SLUG = 'visit-to-communist-party-headquarters-chicago-1919';

    private const PDF_URL = 'https://www.marxists.org/history/usa/parties/cpusa/1919/10/1014-loula-cpheadquarters.pdf';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $dir = public_path('pdfs/government-repression');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $localPath = $dir.DIRECTORY_SEPARATOR.self::SLUG.'.pdf';
        $webPath = '/pdfs/government-repression/'.self::SLUG.'.pdf';

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

        // Cover thumbnail (first page of the PDF) is committed to the repo so
        // it ships with a git pull; fall back to null if it's somehow missing.
        $thumbWeb = '/thumbnails/'.self::SLUG.'.jpg';
        $thumb = is_file(public_path('thumbnails/'.self::SLUG.'.jpg')) ? $thumbWeb : null;

        $record = [
            'title' => 'A Visit to Communist Party Headquarters, Chicago — Oct. 14, 1919',
            'description' => "A U.S. Bureau of Investigation surveillance report by Special Agent A.H. Loula describing his October 14, 1919 visit to the national headquarters of the Communist Party of America, then newly founded, at the Smolny Institute, 1221 Blue Island Avenue, Chicago. Loula names the party's leaders — Louis Fraina, Alexander Stoklitsky, Isaac Hourwich, Isaac Ferguson, J. Stilson, C.E. Ruthenberg, Joseph Kowalski, and Friedman — and recounts confiscating copies of a pamphlet and trading threats with the organizers, several of whom (including Stoklitsky) already faced deportation or indictment. An early documentary record of the federal surveillance and Red Scare prosecution of the CPA. Excerpted from Loula's weekly report for the week ending Oct. 18, 1919; the original is held in the Department of Justice / Bureau of Investigation Investigative Files, NARA microfilm M-1085, reel 922, file 202600-14. Republished by 1000 Flowers Publishing (Corvallis, OR, 2007), edited by Tim Davenport, via the Marxists Internet Archive.",
            'record_type' => 'document',
            'source_format' => 'report',
            'file' => $webPath,
            'thumbnail' => $thumb,
            'collection' => 'Government Repression',
            'publisher' => '1000 Flowers Publishing / Marxists Internet Archive',
            'authors' => 'A.H. Loula (U.S. Bureau of Investigation); ed. Tim Davenport',
            'year' => 1919,
            'date' => '1919-10-14',
            'subjects' => ['Red Scare', 'Communist Party of America', 'Bureau of Investigation', 'Government Surveillance', 'Primary Source', '1919'],
            'is_digitized' => true,
            'published' => true,
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
