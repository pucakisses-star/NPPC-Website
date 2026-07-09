<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Removes non-U.S. political prisoners that fall outside the scope of this
 * (United States) database — chiefly South African anti-apartheid figures, plus
 * a Chilean, a Dominican, and a Namibian:
 *   - Edgardo Enríquez Espinosa (Chile, MIR);
 *   - Ras Kabinda / Desmond Trotter (Dominica);
 *   - Andimba (Herman) Toivo ya Toivo (Namibia, SWAPO);
 *   - the SASO Nine and other South African anti-apartheid detainees
 *     (Mavi, Mokoape, Sedibe, Lekota, Myeza, Nkomo, Nefolovhodwe, Saths Cooper,
 *     Strini Moodley, Cindi, Eric Abraham, Thenjiwe Mtintso, Mabelane, John
 *     Kani, Winston Ntshona, Nat Serache, Peter Magubane).
 *
 * None were created by an artisan command (they were added via the admin panel /
 * import), so there is no creation source to remove. Matched by distinctive name
 * fragments chosen to avoid common surnames (Cooper, Abraham, Trotter) so no
 * U.S. prisoner — e.g. Christopher "Naeem" Trotter or Edgardo Rodríguez
 * Riquelme — is touched. Idempotent.
 */
final class RemoveNonUsPrisoners202607 extends Command
{
    protected $signature = 'prisoners:remove-non-us-202607';

    protected $description = 'Delete out-of-scope non-U.S. political prisoners (Chile, Dominica, Namibia, and South African anti-apartheid figures)';

    /** Distinctive fragments — each unique enough not to match a U.S. prisoner. */
    private const LIKE = [
        '%Edgardo Enr%',        // Edgardo Enríquez (Espinosa)
        '%Kabinda%', '%Desmond Trotter%', // Ras Kabinda / Desmond Trotter
        '%Toivo%',              // Andimba / Herman Toivo ya Toivo
        '%Joseph Mavi%',
        '%Mokoape%',
        '%Sedibe%',
        '%Lekota%',
        '%Myeza%',
        '%Nkwenke%',            // Nkwenke Nkomo
        '%Nefolovhodwe%',
        '%Saths%',              // Saths Cooper (avoid bare "Cooper")
        '%Strini%',             // Strini Moodley
        '%Zithulele%',          // Zithulele Cindi
        '%Eric Anthony Abraham%', // avoid bare "Abraham"
        '%Thenjiwe%',           // Thenjiwe Mtintso / Mthintso
        '%Mabelane%',
        '%John Kani%',
        '%Ntshona%',            // Winston Ntshona
        '%Serache%',            // Nat / Nathaniel Serache
        '%Magubane%',           // Peter Magubane
    ];

    public function handle(): int
    {
        $prisoners = Prisoner::withUnderReview()
            ->where(function ($q) {
                foreach (self::LIKE as $pat) {
                    $q->orWhere('name', 'like', $pat);
                }
            })
            ->get();

        if ($prisoners->isEmpty()) {
            $this->info('No matching entries found — already removed.');

            return self::SUCCESS;
        }

        foreach ($prisoners as $prisoner) {
            $prisoner->cases()->delete();
            $prisoner->delete();
            $this->info('Deleted: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("\nRemoved {$prisoners->count()} out-of-scope non-U.S. prisoner(s).");

        return self::SUCCESS;
    }
}
