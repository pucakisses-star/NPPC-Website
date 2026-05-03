<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class FixCharlesHoltBirthdate extends Command
{
    protected $signature = 'prisoners:fix-charles-holt-birthdate';
    protected $description = 'Correct Charles Holt\'s birthdate from 1774 to August 9, 1772.';

    public function handle(): int
    {
        $holt = Prisoner::where('name', 'Charles Holt')->first();

        if (! $holt) {
            $this->error('Charles Holt not found.');
            return self::FAILURE;
        }

        $oldBirth = $holt->birthdate?->toDateString();

        $holt->birthdate = '1772-08-09';
        $holt->save();

        $this->info("Updated Charles Holt birthdate: {$oldBirth} -> 1772-08-09");

        return self::SUCCESS;
    }
}
