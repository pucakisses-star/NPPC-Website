<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Fills missing birthdays and register numbers on existing prisoner records,
 * sourced from the political-prisoner directory. Each field is set ONLY when it
 * is currently empty — existing data is never overwritten. Idempotent.
 */
final class FillDirectoryInfo extends Command
{
    protected $signature = 'prisoners:fill-directory-info';

    protected $description = 'Fill missing birthdays / register numbers on existing records from the PP directory';

    public function handle(): int
    {
        // [exact DB name, birthdate [Y,M,D] or null, register# or null]
        $fills = [
            ['Mumia Abu-Jamal', [1954, 4, 24], 'AM8335'],
            ['Sundiata Acoli', [1937, 1, 14], '39794-066'],
            ['Michael Davis Africa', [1955, 10, 6], 'AM4973'],
            ['Herman Bell', [1948, 1, 14], '79C0262'],
            ['Kojo Bomani Sababu', null, '39384-066'],
            ['Jalil Muntaqim', [1951, 10, 18], '77A4283'],
            ['Veronza Bowers Jr.', null, '35316-136'],
            ['Marshall Conway', [1946, 4, 23], '116469'],
            ['Romaine Fitzgerald', [1949, 4, 11], 'B-27527'],
            ['David Gilbert', [1944, 10, 6], '83A6158'],
            ['Avelino González-Claudio', null, '09873-000'],
            ['Norberto González-Claudio', [1945, 5, 27], '09864-000'],
            ['Robert Seth Hayes', [1948, 10, 15], '74-A-2280'],
            ['Alvaro Luna Hernandez', [1952, 5, 12], '255735'],
            ['Mohaman Geuka Koti', [1926, 10, 11], null],
            ['Jaan Laaman', [1948, 3, 21], '10372-016'],
            ['Richard Mafundi Lake', [1940, 3, 1], '079972'],
            ['Oscar López Rivera', [1943, 1, 6], '87651-024'],
            ['Ruchell Magee', [1939, 3, 17], null],
            ['Abdullah Majid', [1949, 6, 25], '83-A-0483'],
            ['Thomas Manning', [1946, 6, 28], '10373-016'],
            ['Marius Mason', null, '04672-061'],
            ['Sekou Odinga', [1944, 6, 17], '09A3775'],
            ['Leonard Peltier', [1944, 9, 12], '89637-132'],
            ['Hugo Pinell', [1945, 3, 10], 'A88401'],
            ['Joy Powell', null, '07G0632'],
            ['Hanif Shabazz Bey', [1950, 8, 16], null],
            ['Mutulu Shakur', [1950, 8, 8], '83205-012'],
            ['Russell Maroon Shoatz', [1943, 8, 23], 'AF-3855'],
            ['Gary Tyler', null, '84156'],
            ['Herman Wallace', [1941, 10, 13], '76759'],
        ];

        $filled = 0;
        foreach ($fills as [$name, $bday, $inmate]) {
            $p = Prisoner::withoutGlobalScopes()->where('name', $name)->first()
                ?? Prisoner::withoutGlobalScopes()->where('slug', Str::slug($name))->first();

            if (! $p) {
                $this->warn("not found: {$name}");

                continue;
            }

            $changed = [];
            if ($bday && $p->birthdate === null) {
                $p->setPartialDate('birthdate', ...$bday);
                $changed[] = 'birthdate';
            }
            if ($inmate && empty($p->inmate_number)) {
                $p->inmate_number = $inmate;
                $changed[] = 'inmate#';
            }

            if ($changed) {
                $p->save();
                $filled++;
                $this->info("{$p->name}: set ".implode(', ', $changed));
            } else {
                $this->line("{$p->name}: already complete");
            }
        }

        $this->info("\nDone. {$filled} record(s) updated.");

        return self::SUCCESS;
    }
}
