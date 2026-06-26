<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects records wrongly flagged as currently in custody, and fills in the
 * incarceration/release dates so the "Time Imprisoned" counter renders:
 *
 *  - Marius Mason   — released to a halfway house May 14, 2026 (held since his
 *                     March 2008 arrest); also fixes his placeholder birth year.
 *  - Matthew DePalma — 42-month sentence; released September 2011.
 *  - Johnny Imani Harris — paroled/released in 1991 (death sentence overturned
 *                     1987); has since died. Also corrects his bio, which still
 *                     said he "remains serving life."
 *
 * Idempotent; matches the live record by slug/name (these may be absent from a
 * local snapshot) and only fills the dates on the first case.
 */
final class FixCustodyStatuses extends Command
{
    protected $signature = 'prisoners:fix-custody-statuses';

    protected $description = 'Fix stale "in custody" flags + incarceration/release dates for released prisoners';

    public function handle(): int
    {
        $fixes = [
            [
                'slug' => 'marius-mason',
                'name' => '%Marius Mason%',
                'set' => ['in_custody' => false, 'released' => true],
                'birthdate' => [1962, 1, 26],
                'incarceration_date' => [2008, 3, 10],
                'release_date' => [2026, 5, 14],
            ],
            [
                'slug' => 'matthew-depalma',
                'name' => '%DePalma%',
                'set' => ['in_custody' => false, 'released' => true],
                'incarceration_date' => [2008, 8, 30],
                'release_date' => [2011, 9, null],
            ],
            [
                'slug' => 'johnny-imani-harris',
                'name' => '%Imani Harris%',
                'set' => ['in_custody' => false, 'released' => true],
                'incarceration_date' => [1970, null, null],
                'release_date' => [1991, null, null],
                'descReplace' => [
                    '/[Hh]e remains serving life[^.]*\./',
                    'he was released on parole in 1991 after more than two decades in prison; he has since died.',
                ],
            ],
        ];

        foreach ($fixes as $f) {
            $p = Prisoner::withoutGlobalScopes()
                ->where('slug', $f['slug'])
                ->orWhere('name', 'like', $f['name'])
                ->with('cases')
                ->first();

            if (! $p) {
                $this->warn("Not found: {$f['slug']} — skipped.");

                continue;
            }

            foreach ($f['set'] as $k => $v) {
                $p->{$k} = $v;
            }

            if (isset($f['birthdate'])) {
                $p->setPartialDate('birthdate', ...$f['birthdate']);
            }

            if (isset($f['descReplace']) && $p->description) {
                $p->description = preg_replace($f['descReplace'][0], $f['descReplace'][1], $p->description);
            }

            $p->save();

            $case = $p->cases->first();
            if ($case) {
                if (isset($f['incarceration_date'])) {
                    $case->setPartialDate('incarceration_date', ...$f['incarceration_date']);
                }
                if (isset($f['release_date'])) {
                    $case->setPartialDate('release_date', ...$f['release_date']);
                }
                $case->save();
                $this->info("{$p->name}: status fixed; case dates set ({$case->imprisoned_for_days} days).");
            } else {
                $this->warn("{$p->name}: status fixed, but no case to attach dates to.");
            }
        }

        return self::SUCCESS;
    }
}
