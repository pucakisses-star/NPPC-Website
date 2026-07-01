<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Applies the NON-destructive Wave 4 (historical, pre-2000) release-date
 * findings: backfilled release dates, died-in-custody dates, and one status
 * flip. Removals from Wave 4 (never-imprisoned / probation-only figures) are
 * handled separately, pending review, because they include many historically
 * significant people.
 *
 *   - DATES (71): researched release date (exact, or month/year precision).
 *   - DIED (44): died in custody — mostly executions (Haymarket martyrs, John
 *     Brown's raiders, the 1917 Houston Riot soldiers). Sets the
 *     death-in-custody date so the term computes to the death, not "released".
 *   - FLIP (1): John J. Ford (1996 radium plot) — held in psychiatric custody,
 *     never freed; set to in-custody.
 *
 * The ~210 Wave 4 records with no findable date are covered by the global
 * null-fallback (#1543). Idempotent.
 */
final class ApplyWave4Corrections extends Command
{
    protected $signature = 'prisoners:apply-wave4-corrections';

    protected $description = 'Apply Wave 4 (historical) release dates, died-in-custody dates, and status flip';

    private const DATES = [
        'larry-morlan' => '1999-01', 'gail-cohee' => '1975', 'linda-link' => '1975',
        'marla-seymour' => '1975', 'william-joe-wright' => '1978', 'ann-shepard' => '1977',
        'dora-kelly-lewis' => '1917-11-27', 'charles-krieger' => '1920', 'annie-melvin-arniel' => '1917-06',
        'leroy-pinkett' => '1927-06-03', 'elizabeth-selden-rogers' => '1917-07-19', 'minnie-d-abbott' => '1917-07-19',
        'helena-hill-weed' => '1917-07', 'vida-milholland' => '1917-07', 'lavinia-lloyd-dock' => '1917-09',
        'lucy-burns' => '1917-11-27', 'mary-a-nolan' => '1917-11-27', 'theodora-pollok' => '1919-02',
        'h-h-munson' => '1927', 'anna-mae-pictou-aquash' => '1975-09-08', 'james-earl-grant' => '1979-07',
        'thomas-james-reddy' => '1979-07', 'john-boncore-hill' => '1976', 'jose-solis-jordan' => '2003',
        'martin-mullen' => '2002', 'adam-troy-peace' => '1999-09-09', 'andrew-n-bishop' => '1999-09-09',
        'sean-albert-gautschy' => '1999-09-09', 'kathy-boylan' => '1999-11', 'cameron-michael-kraus' => '1997',
        'jason-troff' => '1998', 'ryan-durfee' => '1998', 'betsy-corner' => '1991-12',
        'randall-forsberg-kehler' => '1992-02', 'marcia-timmel' => '1989-01', 'al-zook' => '1986',
        'filiberto-ojeda-rios' => '1989-08-28', 'carl-kabat' => '1991-04-12', 'marie-nord' => '1986',
        'mary-sprunger-froese' => '1986', 'jack-elder' => '1985', 'erica-bouza' => '1983-11',
        'daniel-rutt' => '1983', 'vernon-joseph-rossman' => '1986', 'bill-hartman' => '1983',
        'gary-john-eklund' => '1983-05', 'james-cunningham' => '1983', 'vincent-kay' => '1983',
        'wallace-floyd-nelson' => '1947', 'anna-gyorgy' => '1977-05', 'guy-chichester' => '1977-05',
        'harvey-franklin-wasserman' => '1977-05', 'howard-gresham-hawkins-iii' => '1977-05', 'howard-morland' => '1977-05',
        'david-johnson' => '1993', 'rodolfo-gonzales' => '1971', 'janet-mccloud' => '1965-10',
        'henry-howe-jr' => '1967', 'annell-ponder' => '1963-06-12', 'fannie-lou-hamer' => '1963-06-12',
        'june-johnson' => '1963-06-12', 'lawrence-guyot' => '1963-06-12', 'bernard-lafayette' => '1961-07',
        'karl-meyer' => '1972', 'murray-bookchin' => '1977-05', 'robert-reynolds-cushing-jr' => '1977-05',
        'sukie-rice' => '1977-05', 'paul-gunter' => '1977-05', 'john-b-williamson' => '1955',
        'joseph-m-coldwell' => '1922-12-25', 'giovanni-baldazzi' => '1922-02-21',
    ];

    /** slug => death-in-custody date (mostly executions). */
    private const DIED = [
        'joseph-j-hofer' => '1918-11-29', 'michael-hofer' => '1918-12-02', 'albert-d-wright' => '1917-12-11',
        'carlos-snodgrass' => '1917-12-11', 'charles-w-baltimore' => '1917-12-11', 'frank-johnson' => '1917-12-11',
        'ira-b-davis' => '1917-12-11', 'james-divine' => '1917-12-11', 'james-wheatley' => '1917-12-11',
        'jesse-ball-moore' => '1917-12-11', 'larnon-j-brown' => '1917-12-11', 'pat-macwharter' => '1917-12-11',
        'risley-w-young' => '1917-12-11', 'william-brackenridge' => '1917-12-11', 'william-cleveland-nesbit' => '1917-12-11',
        'william-d-boone' => '1918-09-24', 'joe-hill' => '1915-11-19', 'adolph-fischer' => '1887-11-11',
        'albert-parsons' => '1887-11-11', 'august-spies' => '1887-11-11', 'george-engel' => '1887-11-11',
        'louis-lingg' => '1887-11-10', 'aaron-stevens' => '1860-03-16', 'albert-hazlett' => '1860-03-16',
        'edwin-coppoc' => '1859-12-16', 'john-anthony-copeland-jr' => '1859-12-16', 'john-brown' => '1859-12-02',
        'john-e-cook' => '1859-12-16', 'shields-green' => '1859-12-16', 'phil-africa' => '2015-01-10',
        'bruce-seidel' => '1976-01-23', 'hugo-pinell' => '2015-08-12', 'albert-nuh-washington' => '2000-04-28',
        'william-walter-remington' => '1954-11-24', 'booker-t-millner' => '1951-02-02', 'francis-desales-grayson' => '1951-02-05',
        'frank-hairston-jr' => '1951-02-02', 'howard-lee-hairston' => '1951-02-02', 'james-luther-hairston' => '1951-02-05',
        'joe-henry-hampton' => '1951-02-02', 'john-clabon-taylor' => '1951-02-05', 'samuel-shepherd' => '1951-11-06',
        'collis-english' => '1952-12-31', 'willie-mcgee' => '1951-05-08',
    ];

    private const FLIP_TO_CUSTODY = ['john-j-ford'];

    public function handle(): int
    {
        $dated = $died = $flipped = 0;

        foreach (self::DATES as $slug => $date) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p || ! ($case = $p->cases()->first())) {
                $this->warn("  date: missing prisoner/case '{$slug}'");

                continue;
            }
            [$y, $m, $d] = array_pad(array_map('intval', explode('-', $date)), 3, 0);
            $case->setPartialDate('release_date', $y, $m ?: null, $d ?: null);
            $p->released = true;
            $p->in_custody = false;
            $p->save();
            $case->save();
            $dated++;
        }

        foreach (self::DIED as $slug => $date) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  died: no prisoner '{$slug}'");

                continue;
            }
            $p->in_custody = false;
            $p->released = false;
            $p->save();
            if ($case = $p->cases()->first()) {
                $case->death_in_custody_date = $date; // hook sets release_date = death, computes days
                $case->save();
            }
            $died++;
        }

        foreach (self::FLIP_TO_CUSTODY as $slug) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  flip: no prisoner '{$slug}'");

                continue;
            }
            $p->in_custody = true;
            $p->released = false;
            $p->save();
            if ($case = $p->cases()->first()) {
                $case->release_date = null;
                $case->save();
            }
            $flipped++;
        }

        $this->info("Wave 4 corrections: dated {$dated}, died-in-custody {$died}, flipped {$flipped}.");
        $this->line('Wave 4 removals are handled separately (pending review). No-date records covered by #1543.');

        return self::SUCCESS;
    }
}
