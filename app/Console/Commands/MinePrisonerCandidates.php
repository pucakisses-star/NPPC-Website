<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Mines the OCR text layer of local archive PDFs for the names of political
 * prisoners who are NOT yet in the prisoner database — a research shortlist for
 * filling gaps. Movement publications in the archive (4StruggleMag, the
 * Anarchist Black Cross zines, etc.) carry write-to lists and case write-ups
 * naming many prisoners; this surfaces the ones we don't already track.
 *
 * Only PDFs that actually have extractable text (native or OCR'd via
 * archive:ocr-pdfs) are scanned; image-only PDFs are skipped. Requires
 * pdftotext (poppler-utils), the same dependency as the OCR command.
 *
 * Output is a ranked candidate list — names appearing with prisoner context
 * (a prison ID number, "sentenced", "incarcerated", a write-to address, etc.)
 * that do not match any existing prisoner. It is a research aid, not a clean
 * list: OCR noise and false positives are expected. Review before adding.
 *
 *   php artisan archive:mine-prisoner-candidates
 *   php artisan archive:mine-prisoner-candidates --collection="4StruggleMag" --top=120
 */
final class MinePrisonerCandidates extends Command {
    protected $signature = 'archive:mine-prisoner-candidates
        {--collection= : Only scan one collection (exact name match)}
        {--min-chars=200 : Treat PDFs with less extractable text than this as image-only and skip}
        {--min-score=3 : Minimum candidate score to report}
        {--limit=0 : Max PDFs to scan (0 = all)}
        {--top=100 : Max candidates to print}';
    protected $description = 'Mine OCR text of archive PDFs for political prisoners missing from the database';

    /** Title-case name: 2-3 tokens, allows middle initials and hyphenated/apostrophe surnames. */
    private const NAME_RE = "/\\b[A-Z][a-z]{2,}(?:\\s+(?:[A-Z]\\.|[A-Z][a-z'’\\-]{1,}|al-[A-Z][a-z]+|el-[A-Z][a-z]+)){1,2}\\b/u";
    private const CONTEXT_RE = '/\b(imprison|incarcerat|sentenc|convict|prison|parole|indict|arrest|penitentiary|correctional|clemency|political prisoner|death row|behind bars|life sentence|write to|freedom|free the|defendant|grand jury|frame-up|prisoner of war)\b/i';
    private const PRISONER_NUM_RE = '/(#\s?\d{5,8}\b|\b[A-Z]{1,2}-?\d{5,8}\b|\b\d{5,8}-\d{2,3}\b)/';

    /** Phrases that look like names but are orgs/places/boilerplate. */
    private const STOP = [
        'united states','new york','san francisco','los angeles','black panther','black liberation',
        'united nations','supreme court','district court','new jersey','new afrika','puerto rico',
        'anarchist black','black cross','prison industrial','south africa','white house','social club',
        'free press','red cross','civil rights','human rights','dear friend','dear comrade','po box',
        'attica brothers','san quentin','green scare','grand jury','death row','please write','best wishes',
        'mother jones','el salvador','black power','self defense','animal liberation','earth liberation',
        'support committee','legal defense','black august','new year','united freedom',
    ];

    public function handle(): int {
        if (trim((string) @shell_exec('command -v pdftotext 2>/dev/null')) === '') {
            $this->error('pdftotext (poppler-utils) is required.  sudo apt install poppler-utils');
            return self::FAILURE;
        }

        // Known prisoners: normalized full names + a set of surnames, to exclude
        // people we already track.
        $knownFull = []; $knownSurname = [];
        foreach (Prisoner::query()->get(['name', 'first_name', 'last_name']) as $p) {
            foreach ([$p->name, trim(($p->first_name ?? '').' '.($p->last_name ?? ''))] as $n) {
                $norm = $this->norm($n);
                if ($norm !== '') {
                    $knownFull[$norm] = true;
                    $parts = explode(' ', $norm);
                    if (count($parts) >= 2) $knownSurname[end($parts)] = true;
                }
            }
        }
        $stop = array_flip(self::STOP);

        $q = ArchiveRecord::query()->whereNotNull('file')->where('file', 'like', '%.pdf')
            ->where(function ($w) { $w->where('file', 'like', '/pdfs/%')->orWhere('file', 'not like', 'http%'); });
        if ($c = $this->option('collection')) $q->where('collection', $c);
        $records = $q->orderBy('collection')->orderBy('title')->get();

        $limit = (int) $this->option('limit');
        $minChars = (int) $this->option('min-chars');
        $cand = []; // norm => [display, score, mentions, collections=>true, snippet]
        $scanned = 0; $skipped = 0;

        $this->info('Scanning '.$records->count().' local PDF record(s) for OCR text...');
        foreach ($records as $r) {
            if ($limit > 0 && $scanned >= $limit) break;
            $path = $this->resolvePath($r->file);
            if (! $path) { $skipped++; continue; }
            $text = (string) @shell_exec(sprintf('pdftotext -q %s - 2>/dev/null', escapeshellarg($path)));
            if (mb_strlen(trim(preg_replace('/\s+/u', ' ', $text))) < $minChars) { $skipped++; continue; }
            $scanned++;
            $flat = preg_replace('/\s+/u', ' ', $text);

            if (! preg_match_all(self::NAME_RE, $flat, $m, PREG_OFFSET_CAPTURE)) continue;
            foreach ($m[0] as [$name, $off]) {
                $norm = $this->norm($name);
                $parts = explode(' ', $norm);
                if (count($parts) < 2) continue;
                if (isset($stop[$norm]) || isset($knownFull[$norm]) || isset($knownSurname[end($parts)])) continue;
                $win = mb_substr($flat, max(0, $off - 90), 200);
                $score = 0;
                if (preg_match(self::PRISONER_NUM_RE, $win)) $score += 3;
                if (preg_match(self::CONTEXT_RE, $win)) $score += 1;
                if ($score === 0) continue;
                if (! isset($cand[$norm])) $cand[$norm] = ['name' => $name, 'score' => 0, 'n' => 0, 'cols' => [], 'snip' => trim($win)];
                $cand[$norm]['score'] += $score;
                $cand[$norm]['n']++;
                $cand[$norm]['cols'][$r->collection ?: '—'] = true;
            }
        }

        $minScore = (int) $this->option('min-score');
        $cand = array_filter($cand, fn ($c) => $c['score'] >= $minScore);
        uasort($cand, fn ($a, $b) => $b['score'] <=> $a['score'] ?: $b['n'] <=> $a['n']);

        $this->newLine();
        $this->info(sprintf('Scanned %d PDF(s) with text, skipped %d (image-only/missing). %d candidate name(s) not in the database:',
            $scanned, $skipped, count($cand)));
        $this->newLine();
        $top = (int) $this->option('top');
        $i = 0;
        foreach ($cand as $c) {
            if ($i++ >= $top) break;
            $this->line(sprintf('  score %-3d (x%d)  %-30s  %s', $c['score'], $c['n'], $c['name'], implode('; ', array_keys($c['cols']))));
            $this->line('        …'.mb_substr($c['snip'], 0, 140).'…');
        }
        $this->newLine();
        $this->line('Format: [score/mentions] Name  collections. Review and research before adding — OCR noise and non-prisoners are expected.');
        return self::SUCCESS;
    }

    private function resolvePath(?string $file): ?string {
        if (! $file) return null;
        $clean = ltrim($file, '/');
        foreach ([base_path('public/'.$clean), storage_path('app/public/'.$clean)] as $p) {
            if (is_file($p)) return $p;
        }
        return null;
    }

    /** Normalize a name for matching: lowercase, strip accents and punctuation. */
    private function norm(?string $s): string {
        $s = (string) $s;
        $s = strtr($s, [
            'á'=>'a','à'=>'a','â'=>'a','ä'=>'a','ã'=>'a','å'=>'a','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i','ó'=>'o','ò'=>'o','ô'=>'o','ö'=>'o','õ'=>'o',
            'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','ñ'=>'n','ç'=>'c','ø'=>'o','ğ'=>'g','ş'=>'s','ı'=>'i',
        ]);
        $s = mb_strtolower($s);
        $s = preg_replace("/[.'’\\-]/u", ' ', $s);
        $s = preg_replace('/[^a-z ]/', ' ', $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }
}
