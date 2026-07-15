<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Updates Ben Fletcher — the Black IWW organizer of Philadelphia's Local 8
 * longshoremen, convicted at the 1918 Chicago mass trial — with his full legal
 * name (Benjamin Harrison Fletcher, displayed as "Ben Fletcher"), birth and
 * death dates (Apr 13, 1890 – Jul 10, 1949), gender, race, and his public-domain
 * 1918 Leavenworth mugshot (NARA). Also merges the duplicate "Benjamin Fletcher"
 * record into this one. Idempotent.
 */
final class FillBenFletcher extends Command
{
    protected $signature = 'prisoners:fill-ben-fletcher';

    protected $description = 'Fill Ben Fletcher (name parts, dates, gender/race, portrait) and merge the duplicate';

    public function handle(): int
    {
        $candidates = Prisoner::withUnderReview()
            ->whereIn('name', ['Ben Fletcher', 'Benjamin Fletcher'])
            ->get();
        if ($candidates->isEmpty()) {
            $this->error('Ben/Benjamin Fletcher not found.');

            return self::FAILURE;
        }
        // Prefer the record that already has a birthdate; else the "Ben Fletcher" one; else the first.
        $prisoner = $candidates->firstWhere(fn ($p) => ! empty($p->birthdate))
            ?? $candidates->firstWhere('name', 'Ben Fletcher')
            ?? $candidates->first();

        DB::transaction(function () use ($prisoner, $candidates) {
            foreach ($candidates as $dup) {
                if ($dup->id !== $prisoner->id) {
                    $dup->cases()->delete();
                    $dup->delete();
                    $this->info('Merged duplicate: '.$dup->name.' (deleted).');
                }
            }

            $prisoner->fill([
                'name' => 'Ben Fletcher',
                'first_name' => 'Benjamin',
                'middle_name' => 'Harrison',
                'last_name' => 'Fletcher',
                'gender' => 'Male',
                'race' => 'Black',
            ]);
            $prisoner->setPartialDate('birthdate', 1890, 4, 13);
            $prisoner->setPartialDate('death_date', 1949, 7, 10);
            $prisoner->save();

            $src = database_path('data/photos/ben-fletcher.jpg');
            if (is_file($src)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/ben-fletcher.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/ben-fletcher.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/ben-fletcher.jpg');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled Ben Fletcher.');

        return self::SUCCESS;
    }
}
