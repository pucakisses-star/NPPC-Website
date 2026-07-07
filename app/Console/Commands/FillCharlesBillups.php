<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Fills in the Rev. Charles Billups Jr. entry — Birmingham civil rights leader
 * and co-founder of the Alabama Christian Movement for Human Rights — with his
 * birth and death dates (March 17, 1927 – November 7, 1968) and a fuller
 * biography, including his 1968 assassination in Chicago, and sets his
 * affiliation to "Civil Rights Movement". Matched by name (preferring the
 * pre-existing ACMHR affiliation) so it can't hit an unrelated Charles Billups.
 * Idempotent.
 */
final class FillCharlesBillups extends Command
{
    protected $signature = 'prisoners:fill-charles-billups';

    protected $description = 'Set Charles Billups\'s birth/death dates and fuller bio';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'Charles Billups')
            ->get()
            ->first(fn ($p) => in_array('Alabama Christian Movement for Human Rights', (array) $p->affiliation, true))
            ?? Prisoner::withUnderReview()->where('name', 'Charles Billups')->first();

        if (! $prisoner) {
            $this->error('Charles Billups not found.');

            return self::FAILURE;
        }

        $prisoner->description = 'Rev. Charles Billups was a Civil Rights leader who along with 11 other pastors founded the Alabama Christian Movement for Human Rights after Alabama Attorney General John Patterson banned the NAACP from conducting activities in the state. During the Civil Rights Movement he was jailed numerous times for organizing marches and sit-ins to protest segregation. In 1968 he was assassinated in Chicago by an unknown gunman, police refused to investigate his murder.';
        $prisoner->aka = 'Rev. Charles Billups Jr.';
        $prisoner->affiliation = ['Civil Rights Movement'];
        $prisoner->released = true;
        $prisoner->setPartialDate('birthdate', 1927, 3, 17);
        $prisoner->setPartialDate('death_date', 1968, 11, 7);
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled Charles Billups (b. Mar 17, 1927 – d. Nov 7, 1968).');

        return self::SUCCESS;
    }
}
