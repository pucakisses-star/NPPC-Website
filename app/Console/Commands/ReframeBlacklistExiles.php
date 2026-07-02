<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Reframes the seven HUAC/Hollywood-blacklist figures who fled the United
 * States as EXILES rather than "released prisoners." None were ever
 * imprisoned — they left the country to escape the blacklist/HUAC and lived
 * abroad — so their records carried a placeholder incarceration date (the year
 * they were blacklisted) and no release date, which is the wrong model.
 *
 * This clears the bogus incarceration/release dates and instead records the
 * exile: in_exile + in_exile_since (departure from the U.S.) and end_of_exile
 * (death abroad, or return to the U.S. for Carl Foreman, who came back in
 * 1975). Death dates are filled in too. currently_in_exile is false for all
 * (each has since died or returned).
 *
 * Idempotent.
 */
final class ReframeBlacklistExiles extends Command
{
    protected $signature = 'prisoners:reframe-blacklist-exiles';

    protected $description = 'Model the 7 HUAC-blacklist émigrés as exiles (in_exile_since / end_of_exile) instead of released prisoners';

    /** slug => [in_exile_since, end_of_exile, death_date] (YYYY | YYYY-MM | YYYY-MM-DD). */
    private const EXILES = [
        'bertolt-brecht' => ['1947-10-31', '1956-08-14', '1956-08-14'], // fled day after HUAC testimony; died East Berlin
        'hanns-eisler' => ['1948-02', '1962-09-06', '1962-09-06'],      // left "voluntarily" under deportation threat; died East Berlin
        'donald-ogden-stewart' => ['1951', '1980-08', '1980-08'],       // to London 1951; died there 1980
        'ella-winter' => ['1951', '1980-08', '1980-08'],                // to London 1951; died there 1980
        'carl-foreman' => ['1952', '1975', '1984-06-26'],               // to England 1952; RETURNED to US 1975; died 1984
        'joseph-losey' => ['1953', '1984-06-22', '1984-06-22'],         // to UK 1953; died London 1984
        'jules-dassin' => ['1953', '2008-03-31', '2008-03-31'],         // to France 1953; died Athens 2008
    ];

    public function handle(): int
    {
        $done = 0;

        foreach (self::EXILES as $slug => [$since, $end, $death]) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  no prisoner '{$slug}'");

                continue;
            }

            $p->in_exile = true;
            $p->currently_in_exile = false; // all have since died or returned
            $p->released = false;           // never imprisoned, so not "released"
            $p->in_custody = false;
            $this->setPart($p, 'death_date', $death);
            $p->save();

            $case = $p->cases()->first();
            if (! $case) {
                $this->warn("  {$p->name}: no case");

                continue;
            }

            // They were never incarcerated — clear the placeholder dates.
            $case->incarceration_date = null;
            $case->release_date = null;
            $this->setPart($case, 'in_exile_since', $since);
            $this->setPart($case, 'end_of_exile', $end);
            $case->save();
            $case->refresh();

            $this->info("  {$p->name}: in exile {$since} → {$end} ({$case->in_exile_for_days} days).");
            $done++;
        }

        $this->info("\nDone. Reframed {$done} blacklist émigré(s) as exiles.");

        return self::SUCCESS;
    }

    private function setPart(object $model, string $field, string $date): void
    {
        [$y, $m, $d] = array_pad(array_map('intval', explode('-', $date)), 3, 0);
        $model->setPartialDate($field, $y, $m ?: null, $d ?: null);
    }
}
