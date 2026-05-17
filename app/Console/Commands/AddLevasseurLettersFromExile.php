<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Download Raymond Luc Levasseur's "Letters From Exile" compilation
 * (Marion Federal Prison writings, 1989–1995) from the Internet
 * Archive, and ALSO extract pages 18–22 — his May 1992 essay
 * "The Uprising" (LA Uprising ↔ Palestinian Intifada) — as a
 * standalone PDF. Both register as ArchiveRecord entries.
 *
 * Page extraction prefers qpdf, then pdftk, then ghostscript; if
 * none are installed, the standalone "Uprising" record falls back
 * to a remote-URL pointer at the IA item page (so the link still
 * works) and the command warns.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddLevasseurLettersFromExile extends Command {
    protected $signature = 'archive:add-levasseur-letters-from-exile {--force : Re-download even if local file exists}';
    protected $description = "Add Levasseur's 'Letters From Exile' compilation + standalone 'The Uprising' (1992) excerpt";

    private const COMPILATION_SLUG = 'levasseur-letters-from-exile-marion-prison';
    private const UPRISING_SLUG    = 'levasseur-the-uprising-marion-prison-may-1992';
    private const SRC_URL          = 'https://archive.org/download/ray-luc-levasseur/RayLucLevasseur.pdf';
    private const IA_PAGE          = 'https://archive.org/details/ray-luc-levasseur';
    private const UPRISING_FIRST   = 18;
    private const UPRISING_LAST    = 22;

    public function handle(): int {
        $force = (bool) $this->option('force');

        $dir = public_path('pdfs/movement-memoirs');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $compPath  = $dir.'/'.self::COMPILATION_SLUG.'.pdf';
        $compWeb   = '/pdfs/movement-memoirs/'.self::COMPILATION_SLUG.'.pdf';
        $upPath    = $dir.'/'.self::UPRISING_SLUG.'.pdf';
        $upWeb     = '/pdfs/movement-memoirs/'.self::UPRISING_SLUG.'.pdf';

        // 1. Download the compilation if missing.
        if (! is_file($compPath) || $force || filesize($compPath) < 1_000_000) {
            $this->line('fetch '.self::SRC_URL);
            $tmp = $compPath.'.partial';
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->withOptions(['sink' => $tmp, 'allow_redirects' => true])
                    ->timeout(900)
                    ->get(self::SRC_URL);
                if (! $resp->successful() || (is_file($tmp) && filesize($tmp) < 1_000_000)) {
                    @unlink($tmp);
                    $this->error('Compilation download failed — using remote URL for both records.');
                    $compWeb = self::SRC_URL;
                    $upWeb   = self::IA_PAGE;
                } else {
                    rename($tmp, $compPath);
                    $this->info('Saved compilation ('.number_format(filesize($compPath) / 1024 / 1024, 1).' MB) to '.$compWeb);
                }
            } catch (\Throwable $e) {
                @unlink($tmp);
                $this->error('Compilation fetch error: '.$e->getMessage());
                $compWeb = self::SRC_URL;
                $upWeb   = self::IA_PAGE;
            }
        } else {
            $this->line('exists '.$compWeb);
        }

        // 2. Extract pages 18–22 as standalone "The Uprising" PDF.
        if (is_file($compPath) && (! is_file($upPath) || $force || filesize($upPath) < 1000)) {
            $extracted = $this->extractPages($compPath, $upPath, self::UPRISING_FIRST, self::UPRISING_LAST);
            if ($extracted) {
                $this->info('Extracted The Uprising (pages '.self::UPRISING_FIRST.'–'.self::UPRISING_LAST.') to '.$upWeb);
            } else {
                $this->warn('PDF page-extract tool not found (tried qpdf / pdftk / gs). The Uprising record will point at the IA item page.');
                $upWeb = self::IA_PAGE;
            }
        } elseif (is_file($upPath)) {
            $this->line('exists '.$upWeb);
        }

        // 3. Register both ArchiveRecord entries.
        $this->upsertRecord(self::COMPILATION_SLUG, [
            'title'         => 'Letters From Exile — Raymond Luc Levasseur, Marion Prison',
            'description'   => "Compilation of essays, letters, and statements written by Raymond Luc Levasseur from inside the federal control-unit prison at Marion, Illinois between approximately 1989 and 1995. Levasseur, a Franco-American Vietnam-era veteran turned anti-imperialist organizer, was a leader of the Sam Melville–Jonathan Jackson Unit and the United Freedom Front (UFF) — clandestine armed groups that carried out a campaign of bombings of U.S. military and corporate targets in the late 1970s and 1980s in support of Puerto Rican independence, against apartheid South Africa, and against U.S. intervention in Central America. He was captured in 1984, convicted at the 1986 Brooklyn federal trial of seditious conspiracy and bombing, and sentenced to 45 years; the second 1989 \"Ohio 7\" sedition trial in Springfield, MA ended in acquittal on the sedition counts. He served much of his sentence in lockdown at USP Marion and was paroled in 2004.\n\nThe collection includes \"My Blood Is Quebecois\" (his autobiographical essay on Franco-American radicalism), \"The Uprising\" (his May 1992 response to the Los Angeles Uprising drawing parallels to the Palestinian Intifada), commentary on the George H.W. Bush Iran-Contra pardons, statements on amnesty for political prisoners, and reflections on the silence of the U.S. Left about U.S.-held political prisoners and POWs.\n\nSource: Internet Archive item ".self::IA_PAGE,
            'record_type'   => 'book',
            'source_format' => 'pdf',
            'file'          => $compWeb,
            'collection'    => 'Movement Memoirs',
            'publisher'     => 'Friends of Political Prisoners / Internet Archive (community upload)',
            'authors'       => 'Raymond Luc Levasseur',
            'year'          => 1995,
            'subjects'      => ['Raymond Luc Levasseur', 'United Freedom Front', 'Ohio 7', 'Sam Melville-Jonathan Jackson Unit', 'Marion Prison', 'Prison Writing', 'Anti-Imperialism'],
            'is_digitized'  => true,
            'published'     => true,
        ]);

        $this->upsertRecord(self::UPRISING_SLUG, [
            'title'         => 'The Uprising — Raymond Luc Levasseur (Marion Prison, May 1992)',
            'description'   => "Raymond Luc Levasseur's May 1992 essay written from his cell at Marion Federal Prison in the immediate aftermath of the Los Angeles Uprising. Levasseur, a captured United Freedom Front leader serving 45 years for the 1976–1984 bombing campaign in support of Puerto Rican independence, anti-apartheid struggle, and Central American liberation movements, draws explicit parallels between the LA Uprising and the Palestinian First Intifada — calling both \"dispossessed nations fighting for basic human rights\" whose struggles are rooted in \"the right to national identity and land.\"\n\nThe essay rejects mainstream coverage that framed the uprising as criminality or a riot, instead positioning it within 500 years of European-exported genocide on Indigenous and Mexican land in California, the African Diaspora's historical Black Belt land base, and the lineage from earliest slave rebellions through Malcolm X. He addresses the participation of Mexicans (occupying stolen Mexican land) and white youth in solidarity, and closes with a call to white anti-racists to follow John Brown.\n\nExtracted from the larger \"Letters From Exile\" compilation (pages 18–22). Source: ".self::IA_PAGE,
            'record_type'   => 'document',
            'source_format' => 'pdf',
            'file'          => $upWeb,
            'collection'    => 'Movement Memoirs',
            'publisher'     => 'Friends of Political Prisoners / Internet Archive (community upload)',
            'authors'       => 'Raymond Luc Levasseur',
            'year'          => 1992,
            'date'          => '1992-05-01',
            'subjects'      => ['Raymond Luc Levasseur', 'United Freedom Front', '1992 LA Uprising', 'Palestinian Intifada', 'Prison Writing', 'Anti-Imperialism'],
            'is_digitized'  => true,
            'published'     => true,
        ]);

        return self::SUCCESS;
    }

    private function extractPages(string $src, string $dst, int $first, int $last): bool {
        $tools = [
            "qpdf --empty --pages ".escapeshellarg($src)." {$first}-{$last} -- ".escapeshellarg($dst),
            "pdftk ".escapeshellarg($src)." cat {$first}-{$last} output ".escapeshellarg($dst),
            "gs -dBATCH -dNOPAUSE -dQUIET -sDEVICE=pdfwrite -dFirstPage={$first} -dLastPage={$last} -sOutputFile=".escapeshellarg($dst)." ".escapeshellarg($src),
        ];
        foreach ($tools as $cmd) {
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

    private function upsertRecord(string $slug, array $payload): void {
        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: '.$payload['title']);
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: '.$payload['title']);
        }
    }
}
