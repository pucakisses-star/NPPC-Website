<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Repair OCR damage in prisoner biographies.
 *
 * Much of the WWI-era content was scanned from Stephen M. Kohn's "American
 * Political Prisoners" and carried the scanner's artifacts into the bios:
 *
 *   - footnote markers welded to the last sentence ("...1926.196", "... 2 2")
 *   - running page headers dropped mid-sentence ("war is or- 92 • American
 *     Political Prisoners ganized murder")
 *   - hyphenation breaks ("re- leased", "San Quen - tin", "through - out")
 *   - fused words ("Andersonjoined", "commutedonOctober", "anda half")
 *   - split characters ("December 2 1, 1921", "n o evidence", "m e")
 *   - spaced possessives ("Lippman ' s")
 *   - digit misreads ("paroled on June 30, 1992" for 1922; "i\e years" for
 *     five years)
 *
 * Worst of all, several bios absorbed WHOLE RUNS OF OTHER PEOPLE'S ENTRIES
 * from the book: Joseph Coldwell's bio carries Hubert Colley, Robert
 * Connellan, Roy Connor, Thomas Cornell and half of Alexander Cournos; Sam
 * Sadler's carries all of Vincent St. John's; Ammon Hennesey's carries Ernest
 * Henning, Emil Herman and C. H. Herriage; Samuel Lippman's carries Harry
 * Lloyd's; Sam Jacobs's carries Otto Janson's opener; Herbert Mansolf's and
 * Peter Wukusick's end in the next chapter's heading. Those tails are cut.
 *
 * And one record is not a person at all: "Although Lorton" is the second half
 * of Burt Lorton's book entry, split off by the OCR at a paragraph boundary
 * and turned into a phantom prisoner ("Although Lorton was arrested..."). Its
 * only case row is empty. The cleaned text is appended to Burt Lorton's bio
 * and the phantom is deleted.
 *
 * Ordering matters: tails are trimmed first, then per-record surgical fixes,
 * then the generic rules, so the generic rules see already-trimmed text.
 * Idempotent — run on repaired text, every stage finds nothing. Dry by
 * default; --apply writes.
 */
final class RepairOcrDamage extends Command
{
    protected $signature = 'prisoners:repair-ocr
        {--apply : Write the repairs (default is a dry run)}
        {--slug= : Limit to one or more slugs, comma-separated}';

    protected $description = 'Repair OCR damage in prisoner biographies: footnotes, page headers, broken words, absorbed entries';

    /**
     * Bios that absorbed the following entries from the source book.
     * Everything from the marker onward is cut.
     */
    private const TRIMS = [
        'joseph-m-coldwell' => '60 Hubert Colley',
        'sam-sadler' => '361 Vincent St. John',
        'ammon-a-hennesey' => '167 Ernest Henning',
        'samuel-lippman' => '2 2 9 Harry Lloyd',
        'sam-jacobs' => '1 9 0 Otto Janson',
        'herbert-mansolf' => '170 NEW YORK ANTI-ANARCHY LAW PRISONERS',
        'peter-wukusick' => '137 COLORADO ANTI-SEDITION ACT PRISONERS',
        'ed-burns' => '4 Joe Coya',
        'c-e-berquist' => '165 Harry Breen',
        'vladimir-lossieff' => '232 Chris A. Luber',
        'gustave-h-taubert' => '423 James P. Thompson',
        'ernest-a-stephens' => '407 Joseph V. Stilson',
        'isadore-trzeliakiewicz' => '430 Norris Tucker',
        'edgar-held' => '1 6 4 Rev. H. M. Hendricksen',
        'oscar-swanson' => '416 Pink Swindle',
        'h-e-kirchner' => '202 Edward A. Kolbe',
        'ignatz-mizher' => '178 Carl Paivo',
        'ragner-johannsen' => '1 9 4 Edward Johnson',
        'j-t-cumbie' => '68 Harry Daile',
        'ernest-d-wells' => '2 9 John M. Wolfe',
        'george-w-snell' => '393 Federal Espionage',
    ];

    /** Per-record surgical fixes the generic rules cannot make safely. */
    private const SURGICAL = [
        'charles-h-mackinnon' => [
            'i\\\\e years' => 'five years',
            'i\\e years' => 'five years',
        ],
        'olin-b-anderson' => [
            'June 30, 1992' => 'June 30, 1922',
            'Andersonjoined' => 'Anderson joined',
            'broughta shocking' => 'brought a shocking',
            'of acontinued' => 'of a continued',
            'Kallispell' => 'Kalispell',
        ],
        'jim-roe' => [
            'Alter his conviction' => 'After his conviction',
            'Committeenot' => 'Committee not',
            "was ' ' too old" => 'was "too old',
            '" waste "' => '"waste"',
        ],
        'andre-boutin' => [
            '" PureCommon Sense, "' => '"Pure Common Sense,"',
            '" Weneed' => '"We need',
            'oneanother' => 'one another',
            ' (See also Chapterl. )' => '',
        ],
        'joseph-m-coldwell' => [
            'WarrenG. Hardingcommuted' => 'Warren G. Harding commuted',
            'which hehelped lead' => 'which he helped lead',
            'athree - month' => 'a three-month',
            'refused apardon' => 'refused a pardon',
            '" ComradeDebs "' => '"Comrade Debs"',
            'andremained' => 'and remained',
        ],
        'louis-parenti' => [
            'Hehada wife andthree children' => 'He had a wife and three children',
        ],
        'otto-frederick-schmidt' => [
            '" nobed. "' => '"no bed."',
            'teethandhair' => 'teeth and hair',
        ],
        'h-klabo' => [
            'distribu t i n g' => 'distributing',
        ],
        'sam-jacobs' => [
            'tearingup' => 'tearing up',
            '" dementiapraecox, "' => '"dementia praecox,"',
            'Thereport' => 'The report',
            'hadno history' => 'had no history',
        ],
        'ammon-a-hennesey' => [
            'ninemonths' => 'nine months',
            'Henneseyremaineda radical' => 'Hennesey remained a radical',
        ],
        // Same-person continuations split by a footnote number: the second
        // paragraph is about the same prisoner, so only the number goes.
        'daniel-teuscher' => [
            "Leavenworth.25\n\nDaniel B. Teuscher" => "Leavenworth.\n\nDaniel B. Teuscher",
        ],
        'enrique-flores-magon' => [
            "Mexico. 2 3\n\nEnrique Flores Mag" => "Mexico.\n\nEnrique Flores Mag",
        ],
        'alexander-lanier' => [
            "times.12\n\nAlexander S. Lanier" => "times.\n\nAlexander S. Lanier",
        ],
        'george-ryan' => [
            "deported. 1 0\n\nGeorge W. Ryan" => "deported.\n\nGeorge W. Ryan",
        ],
        'although-lorton' => [
            'documentsadmit' => 'documents admit',
            '" [ n ] o evidence' => '"[n]o evidence',
            'Unionhad gained adecided' => 'Union had gained a decided',
            'WardenA. V. Anderson' => 'Warden A. V. Anderson',
        ],
    ];

    /** Hyphenation breaks: join with no hyphen. */
    private const WORD_JOINS = [
        're- leased' => 'released',
        'ar- rested' => 'arrested',
        'rear- rested' => 'rearrested',
        'cour- age' => 'courage',
        'im- proper' => 'improper',
        'im- migrant' => 'immigrant',
        'news- paper' => 'newspaper',
        'under- wear' => 'underwear',
        'help- less' => 'helpless',
        'chap- book' => 'chapbook',
        'break- down' => 'breakdown',
        'any- thing' => 'anything',
        'up- holding' => 'upholding',
        'peti- tioned' => 'petitioned',
        'or- ganized' => 'organized',
        'in- troduced' => 'introduced',
        'Di- vision' => 'Division',
        'mem - oranda' => 'memoranda',
        'mem - bers' => 'members',
        'mem - ber' => 'member',
        'San Quen - tin' => 'San Quentin',
        'through - out' => 'throughout',
        'pub - lished' => 'published',
    ];

    /** Broken compounds: join and keep the hyphen. */
    private const COMPOUND_JOINS = [
        'co- defendant' => 'co-defendant',
        'secretary- treasurer' => 'secretary-treasurer',
        'fruit- packing' => 'fruit-packing',
        'community- based' => 'community-based',
        'pro - peace' => 'pro-peace',
        'thirty- three' => 'thirty-three',
        'thirty- four' => 'thirty-four',
        'fifteen- year' => 'fifteen-year',
    ];

    /** Common scanner misreads. */
    private const MISREADS = [
        'entiy' => 'entry',
        'histoiy' => 'history',
        'Secretaiy' => 'Secretary',
        'innovaUve' => 'innovative',
        'rWW' => 'IWW',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $slugs = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('slug')))));

        $prisoners = Prisoner::withoutGlobalScopes()
            ->when($slugs, fn ($q) => $q->whereIn('slug', $slugs))
            ->orderBy('name')
            ->get();

        $changed = 0;
        $ruleTally = [];

        DB::transaction(function () use ($prisoners, $apply, &$changed, &$ruleTally) {
            foreach ($prisoners as $p) {
                if ($p->slug === 'although-lorton') {
                    continue;   // handled by the merge below, once, deliberately last
                }

                $before = (string) $p->description;
                [$after, $applied] = $this->repair($before, $p->slug);

                if ($after === $before) {
                    continue;
                }

                $changed++;
                foreach ($applied as $label) {
                    $ruleTally[$label] = ($ruleTally[$label] ?? 0) + 1;
                }

                $this->line('<info>'.$p->slug.'</info>  '.implode(', ', $applied));
                $this->showDelta($before, $after);

                if ($apply) {
                    $p->description = $after;
                    $p->save();
                }
            }

            $this->mergePhantomLorton($apply, $changed, $ruleTally);
        });

        if ($apply && $changed > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->newLine();
        ksort($ruleTally);
        foreach ($ruleTally as $label => $n) {
            $this->line(sprintf('  %-28s %d', $label, $n));
        }
        $this->newLine();

        if ($apply) {
            $this->info("Repaired {$changed} record(s).");
        } else {
            $this->warn("Dry run — nothing written. {$changed} record(s) would be repaired.");
            $this->line('Re-run with --apply to write.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: string, 1: array<string>}
     */
    private function repair(string $text, string $slug): array
    {
        $applied = [];

        // 1. cut absorbed entries
        if (isset(self::TRIMS[$slug])) {
            $at = mb_strpos($text, self::TRIMS[$slug]);
            if ($at !== false) {
                $text = rtrim(mb_substr($text, 0, $at));
                $applied[] = 'absorbed-entries-cut';
            }
        }

        // 2. per-record surgical fixes
        foreach (self::SURGICAL[$slug] ?? [] as $from => $to) {
            if ($from !== '' && str_contains($text, $from)) {
                $text = str_replace($from, $to, $text);
                $applied[] = 'surgical';
            }
        }
        $applied = array_values(array_unique($applied));

        $generic = [
            // running page headers dropped mid-sentence
            'page-header' => [
                ['/\s*\d{1,3}\s*•\s*American Political Prisoners\s*/u', ' '],
                ['/\s*American Political Prisoners\s*•\s*\d{1,3}\s*/u', ' '],
                ['/\s*Federal Espionage and Sedition Act Prisoners\s*•\s*\d{0,3}\s*/u', ' '],
                ['/\s*State Anti-Sedition and Criminal Syndicalism Prisoners\s*•\s*\d{0,3}\s*/u', ' '],
            ],
            // footnote markers welded to the final sentence
            'trailing-footnote' => [
                ['/([.!?"\x{201D}\x{2019}\'])\s*\d{1,3}(?:\s\d){0,3}\s*$/u', '$1'],
            ],
            // footnote glued to a year mid-text: "1925.137 COLORADO"
            'mid-footnote' => [
                ['/(?<![\d,.$])((?:1[6-9]|20)\d{2})\.\d{1,3}(?=\s+[A-Z])/u', '$1.'],
            ],
            // split date digits: "December 2 1, 1921"
            'split-date-digits' => [
                ['/\b(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d)\s(\d)\b/u', '$1 $2$3'],
            ],
            // "$30, 000"
            'money-spacing' => [
                ['/(\$\d{1,3}),\s(\d{3})\b/u', '$1,$2'],
            ],
            // "Lippman ' s" and "operators ' private"
            'spaced-possessive' => [
                ["/(\w)\s+'\s*s\b/u", "$1's"],
                ["/(\w)s\s+'\s+(?=[a-z])/u", "$1s' "],
            ],
            // "commutedonOctober", "OnJune"
            'fused-on-month' => [
                ['/\b(beginning|commuted|sentence|paroled|released)on(January|February|March|April|May|June|July|August|September|October|November|December)\b/u', '$1 on $2'],
                ['/\b([Oo]n)(January|February|March|April|May|June|July|August|September|October|November|December)\b/u', '$1 $2'],
            ],
            // "anda half", "hada wife"
            'fused-article' => [
                ['/\banda\b/u', 'and a'],
                ['/\bhada\b/u', 'had a'],
            ],
            // "n o evidence", "Tell m e the truth"
            'split-letters' => [
                ['/(?<=\s)n\so(?=\s[a-z])/u', 'no'],
                ['/(?<=\s)m\se(?=\s[a-z.])/u', 'me'],
            ],
            // "three- year prison sentence" (suspended forms like "one- to" are untouched)
            'broken-number-year' => [
                ['/\b(one|two|three|four|five|six|seven|eight|nine|ten|twenty)- (year)\b/u', '$1-$2'],
            ],
        ];

        // The whole rule set runs to a fixpoint because rules feed each other:
        // "onNovember 2 1, 1922" hides the split date behind the fused month
        // word until the on-month rule has split it, and only then can the
        // split-date rule see "November 2 1".
        for ($round = 0; $round < 4; $round++) {
            $any = false;
            foreach ($generic as $label => $rules) {
                foreach ($rules as [$pattern, $replacement]) {
                    $next = preg_replace($pattern, $replacement, $text, -1, $count);
                    if ($next !== null && $count > 0) {
                        $text = $next;
                        $any = true;
                        if (! in_array($label, $applied, true)) {
                            $applied[] = $label;
                        }
                    }
                }
            }
            if (! $any) {
                break;
            }
        }

        foreach (['word-join' => self::WORD_JOINS, 'compound-join' => self::COMPOUND_JOINS, 'misread' => self::MISREADS] as $label => $map) {
            foreach ($map as $from => $to) {
                if (str_contains($text, $from)) {
                    $text = str_replace($from, $to, $text);
                    if (! in_array($label, $applied, true)) {
                        $applied[] = $label;
                    }
                }
            }
        }

        // page-header removal can leave doubled spaces behind
        $text = trim(preg_replace('/ {2,}/', ' ', $text));

        return [$text, $applied];
    }

    /**
     * "Although Lorton" is not a person — it is the second half of Burt
     * Lorton's book entry, split into a phantom record by the OCR. Append the
     * repaired text to Burt Lorton and delete the phantom (its only case row
     * is empty, so nothing else is lost).
     */
    private function mergePhantomLorton(bool $apply, int &$changed, array &$ruleTally): void
    {
        $phantom = Prisoner::withoutGlobalScopes()->where('slug', 'although-lorton')->with('cases')->first();
        if (! $phantom) {
            return;
        }

        $burt = Prisoner::withoutGlobalScopes()->where('slug', 'burt-lorton')->first();
        if (! $burt) {
            $this->warn('although-lorton exists but burt-lorton does not — phantom left untouched for review.');

            return;
        }

        $datedCases = $phantom->cases->filter(
            fn ($c) => $c->incarceration_date || $c->arrest_date || $c->release_date,
        )->count();
        if ($datedCases > 0) {
            $this->warn('although-lorton now has dated cases — refusing to delete it. Merge by hand.');

            return;
        }

        [$fragment, $applied] = $this->repair((string) $phantom->description, 'although-lorton');

        $this->newLine();
        $this->line('<info>although-lorton -> burt-lorton</info>  phantom record merged: '.implode(', ', $applied));
        $this->line('  The fragment ("Although Lorton was arrested...") continues Burt Lorton\'s book entry.');
        $this->line('  Appended to his biography; the phantom and its empty case row are deleted.');

        if ($apply && ! str_contains((string) $burt->description, 'Although Lorton was arrested')) {
            $burt->description = rtrim((string) $burt->description)."\n\n".$fragment;
            $burt->save();
            $phantom->delete();
        }

        $changed++;
        $ruleTally['phantom-merged'] = ($ruleTally['phantom-merged'] ?? 0) + 1;
    }

    /** Show what changed, compactly: the differing head and tail of each version. */
    private function showDelta(string $before, string $after): void
    {
        // common prefix / suffix
        $max = min(mb_strlen($before), mb_strlen($after));
        $p = 0;
        while ($p < $max && mb_substr($before, $p, 1) === mb_substr($after, $p, 1)) {
            $p++;
        }
        $s = 0;
        while ($s < $max - $p
            && mb_substr($before, mb_strlen($before) - 1 - $s, 1) === mb_substr($after, mb_strlen($after) - 1 - $s, 1)) {
            $s++;
        }

        $cut = fn (string $t) => mb_strimwidth(mb_substr($t, max(0, $p - 25), mb_strlen($t) - $s - max(0, $p - 25) + 25), 0, 160, '…');
        $this->line('    - '.$cut($before));
        $this->line('    + '.$cut($after));
    }
}
