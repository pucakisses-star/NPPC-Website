<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets James Omura's birth and death dates. James Matsumoto Omura (1912–1994),
 * the Nisei journalist and English-language editor of the Rocky Shimpo who
 * defended the Heart Mountain draft resisters and was tried (and acquitted) for
 * conspiracy to counsel draft evasion, was born November 27, 1912 and died
 * June 20, 1994. His record carried no dates. Idempotent.
 */
final class SetJamesOmuraDates extends Command
{
    protected $signature = 'prisoners:set-james-omura-dates';

    protected $description = "Set James Omura's birth (1912-11-27) and death (1994-06-20) dates";

    private const BIRTH = '1912-11-27';

    private const DEATH = '1994-06-20';

    public function handle(): int
    {
        $p = Prisoner::withUnderReview()->where('name', 'James Omura')->first();

        if (! $p) {
            $this->warn('James Omura not found, nothing to do.');

            return self::SUCCESS;
        }

        $beforeB = $p->birthdate?->format('Y-m-d') ?? '(none)';
        $beforeD = $p->death_date?->format('Y-m-d') ?? '(none)';

        $p->birthdate = self::BIRTH;
        $p->death_date = self::DEATH;
        $p->save();

        $this->info("{$p->name}: birth {$beforeB} → ".self::BIRTH.', death '.$beforeD.' → '.self::DEATH.'.');

        return self::SUCCESS;
    }
}
