<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills incarceration_date and release_date on the first
 * PrisonerCase of each well-documented Sedition Act / common-law
 * libel prisoner. Sets only the fields we have confident historical
 * dates for; leaves uncertain fields alone.
 *
 * Idempotent — only writes when the target field is currently null
 * or differs from the value we want to set, and never blanks out
 * anything that's already populated.
 */
final class SetSeditionActDates extends Command {
    protected $signature = 'archive:set-sedition-act-dates {--dry-run : Preview without saving}';
    protected $description = 'Backfill arrest / incarceration / release dates on Sedition Act prisoners';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');

        // [slug => [field => value, ...]]
        $updates = [
            // ---- High confidence ----
            'matthew-lyon' => [
                'incarceration_date' => '1798-10-09',
                'release_date'       => '1799-02-09',
            ],
            'thomas-cooper' => [
                'incarceration_date' => '1800-04-26',
                'release_date'       => '1800-10-26',
            ],
            'james-thompson-callender' => [
                'incarceration_date' => '1800-06-03',
                'release_date'       => '1801-03-16',
            ],
            'anthony-haswell' => [
                'incarceration_date' => '1800-05-09',
                'release_date'       => '1800-07-09',
            ],
            'charles-holt' => [
                'incarceration_date' => '1800-04-17',
                'release_date'       => '1800-07-17',
            ],
            'abijah-adams' => [
                'incarceration_date' => '1799-02-19',
                'release_date'       => '1799-03-21',
            ],
            // ---- Medium confidence — set only what we know ----
            'david-brown' => [
                // Arrested in Dedham, MA in November 1798 over the liberty
                // pole. Pleaded guilty and sentenced June 1799 in Salem
                // (Justice Samuel Chase) to 18 months + $480 fine. Held
                // continuously because he could not pay the fine. Released
                // March 12, 1801 by Jefferson's general pardon.
                'arrest_date'        => '1798-11-01',
                'incarceration_date' => '1799-06-08',
                'release_date'       => '1801-03-12',
            ],
            'david-frothingham' => [
                // Tried Nov 21, 1799 in NY Court of General Sessions on
                // Hamilton's common-law libel complaint (not federal
                // Sedition Act). Sentenced to 4 months + $100 fine; held
                // beyond his sentence because he could not pay. Died in
                // the NYC Bridewell in 1800 — exact death date not
                // confidently known, so release_date intentionally left
                // blank.
                'incarceration_date' => '1799-11-21',
            ],
            'luther-baldwin' => [
                // Indictment + guilty plea on Oct 3, 1799 in U.S.
                // Circuit Court at Newark, NJ. Fined $150 + costs and
                // held in custody until paid; secondary sources say he
                // sat roughly two months. Fines remitted by Jefferson
                // on taking office in March 1801. Release_date is an
                // approximation pending day-precise primary source
                // (Smith, Freedom's Fetters, 1956, ch. 18).
                'incarceration_date' => '1799-10-03',
                'release_date'       => '1799-12-03',
            ],
            'john-s-lillie' => [
                // CASE YEAR CORRECTION: this is 1802, not 1801. Lillie
                // was indicted Feb 1802 by Massachusetts authorities
                // (CJ Francis Dana of the SJC) for libel against Dana
                // himself, and sentenced to 3 months plus $100. A
                // farewell column in the Telegraphe is dated "Boston
                // Gaol, March 30 — 19th day of Imprisonment," which
                // back-dates entry to ~Mar 12, 1802; a 3-month term
                // from there gives release ~Jun 12, 1802. A letter
                // from Lillie to Jefferson dated Apr 7, 1802 is sent
                // from the gaol. Day-precise dates pending Smith,
                // Freedom's Fetters.
                'incarceration_date' => '1802-03-12',
                'release_date'       => '1802-06-12',
            ],
            'william-durrell' => [
                // Arrested Jul 17, 1798; pleaded not guilty in U.S.
                // Circuit Court Sept 5, 1798; tried April 1800;
                // convicted and sentenced to 4 months + $50 fine +
                // $2,000 surety. Adams granted a PARTIAL pardon that
                // REMITTED THE PRISON TERM (declining a full pardon —
                // fine and surety bond remained). So Durrell was never
                // actually incarcerated under his Sedition Act
                // sentence — incarceration_date / release_date stay
                // blank. We populate arrest_date only.
                //
                // FYI: this means Durrell does not strictly satisfy
                // the project's "actually served jail time" criterion
                // and may belong with the indicted-but-not-jailed
                // group (Duane, Greenleaf, Mayer, Fahnestock, Bache,
                // Burk, Thomas Adams). Decide on a separate pass.
                'arrest_date' => '1798-07-17',
            ],
        ];

        $touched = 0;
        $skipped = 0;

        foreach ($updates as $slug => $fields) {
            $prisoner = Prisoner::with('cases')->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Prisoner not found: {$slug}");
                $skipped++;

                continue;
            }
            $case = $prisoner->cases->first();
            if (! $case) {
                $this->warn("Prisoner {$prisoner->name} has no PrisonerCase — skipping.");
                $skipped++;

                continue;
            }

            $changes = [];
            foreach ($fields as $field => $value) {
                $current = $case->{$field}?->toDateString() ?? $case->{$field};
                if ($current === $value) {
                    continue;
                }
                $changes[$field] = ['from' => $current, 'to' => $value];
                if (! $dryRun) {
                    $case->{$field} = $value;
                }
            }

            if (empty($changes)) {
                $this->line("unchanged: {$prisoner->name}");

                continue;
            }

            foreach ($changes as $f => $diff) {
                $this->line(($dryRun ? '[dry-run] ' : '')."{$prisoner->name}  {$f}: ".($diff['from'] ?? '(null)').' -> '.$diff['to']);
            }
            if (! $dryRun) {
                $case->save();
            }
            $touched++;
        }

        $this->info("\nDone. Updated={$touched} Skipped={$skipped}".($dryRun ? ' (DRY RUN)' : ''));

        return self::SUCCESS;
    }
}
