<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Save the handwritten-page scan of one of Kuwasi Balagoon's
 * prison letters (the one containing the often-quoted "we took
 * the slogans seriously, transforming the slogans into events"
 * passage), wrap it as a one-page PDF, and register it as a
 * standalone ArchiveRecord. Source image surfaced by
 * @Workshops4Gaza, 2026-05-07.
 *
 * Image-to-PDF conversion tries img2pdf, then ImageMagick
 * (magick / convert). If none are available, the record is
 * registered with the JPG as the file (record_type = image).
 *
 * Idempotent — re-runs update by slug.
 */
final class AddBalagoonHandwrittenLetter extends Command {
    protected $signature = 'archive:add-balagoon-handwritten-letter {--force : Re-download even if local file exists}';
    protected $description = "Add scan of Kuwasi Balagoon's handwritten prison letter (slogans-into-events passage)";

    private const SLUG     = 'kuwasi-balagoon-handwritten-prison-letter-slogans-into-events';
    private const SRC_IMG  = 'https://pbs.twimg.com/media/HHssX2jXMAEF3Gh.jpg';
    private const TWEET    = 'https://x.com/Workshops4Gaza/status/2052282890587627557';

    public function handle(): int {
        $force = (bool) $this->option('force');

        $jpgDir = public_path('images/archive/balagoon');
        $pdfDir = public_path('pdfs/freedom-archives');
        foreach ([$jpgDir, $pdfDir] as $d) {
            if (! is_dir($d)) {
                mkdir($d, 0755, true);
            }
        }
        $jpgPath = $jpgDir.'/'.self::SLUG.'.jpg';
        $pdfPath = $pdfDir.'/'.self::SLUG.'.pdf';
        $pdfWeb  = '/pdfs/freedom-archives/'.self::SLUG.'.pdf';
        $jpgWeb  = '/images/archive/balagoon/'.self::SLUG.'.jpg';

        // 1. Download the source JPG.
        if (! is_file($jpgPath) || $force) {
            $this->line('fetch '.self::SRC_IMG);
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::SRC_IMG);
                if (! $resp->successful() || strlen($resp->body()) < 5000) {
                    $this->error('JPG download failed.');
                    return self::FAILURE;
                }
                file_put_contents($jpgPath, $resp->body());
                $this->info('Saved JPG to '.$jpgWeb);
            } catch (\Throwable $e) {
                $this->error('Fetch error: '.$e->getMessage());
                return self::FAILURE;
            }
        } else {
            $this->line('exists '.$jpgWeb);
        }

        // 2. Wrap the JPG in a single-page PDF.
        $file = $pdfWeb;
        $sourceFormat = 'pdf';
        if (! is_file($pdfPath) || $force) {
            $made = $this->jpgToPdf($jpgPath, $pdfPath);
            if ($made) {
                $this->info('Wrapped JPG into PDF at '.$pdfWeb);
            } else {
                $this->warn('No img2pdf/ImageMagick — registering JPG directly as an image record.');
                $file = $jpgWeb;
                $sourceFormat = 'image';
            }
        } else {
            $this->line('exists '.$pdfWeb);
        }

        // 3. Register the ArchiveRecord.
        $record = [
            'title'         => "Kuwasi Balagoon — Handwritten Prison Letter (Slogans Into Events)",
            'description'   => "A handwritten page from one of Kuwasi Balagoon's prison letters (c. 1983–1985, Brink's-case era), containing the often-quoted passage: \"The difference between most of us in here and most of the 'revolutionaries' outside is we took the slogans seriously, transforming the slogans into events. Now we must transform a series of events into a deadlier and consistent form of struggle.\"\n\nBalagoon (1946–1986) was a Black Liberation Army combatant and New Afrikan Anarchist who was captured in connection with the 1981 Brink's expropriation in Nyack, NY and died of AIDS-related illness at Auburn Prison in December 1986, while still imprisoned. His prison letters are primary-source movement documents; the typed/transcribed compendium of these letters is also held in our archive at /archive-records?q=balagoon+letters+from+prison.\n\nThis scan surfaced via @Workshops4Gaza on 2026-05-07: ".self::TWEET,
            'record_type'   => 'document',
            'source_format' => $sourceFormat,
            'file'          => $file,
            'collection'    => 'Freedom Archives — Political Prisoners',
            'publisher'     => '@Workshops4Gaza (community share)',
            'authors'       => 'Kuwasi Balagoon',
            'year'          => 1984,
            'subjects'      => ['Kuwasi Balagoon', 'Black Liberation Army', 'Brink\'s Case', 'Prison Writing', 'New Afrikan Anarchism'],
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

    private function jpgToPdf(string $src, string $dst): bool {
        $cmds = [
            'img2pdf '.escapeshellarg($src).' -o '.escapeshellarg($dst),
            'magick '.escapeshellarg($src).' '.escapeshellarg($dst),
            'convert '.escapeshellarg($src).' '.escapeshellarg($dst),
        ];
        foreach ($cmds as $cmd) {
            $bin = explode(' ', $cmd)[0];
            $which = trim((string) shell_exec('command -v '.escapeshellarg($bin).' 2>/dev/null'));
            if ($which === '') {
                continue;
            }
            shell_exec($cmd.' 2>/dev/null');
            if (is_file($dst) && filesize($dst) > 1000) {
                return true;
            }
        }
        return false;
    }
}
