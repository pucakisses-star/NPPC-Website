<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Sets Wilhelm Schumann's (Leavenworth inmate #14689) photo and incarceration
 * date.
 *
 * His incarceration date is July 11, 1918 — the precise, cited Leavenworth
 * admission date (footnote 379 in his bio, from the National Archives prison
 * register via Kohn's "American Political Prisoners"). An earlier pass briefly
 * set it to "November 1919" from a secondary source; this restores the cited
 * July 11, 1918 date on both his case and his bio text. Release date is
 * December 25, 1921 (the Christmas 1921 commutation). Also swaps his photo for
 * his Leavenworth mugshot (front view, cropped from the #14689 record).
 * Idempotent; matches by slug then name.
 */
final class UpdateSchumann extends Command
{
    protected $signature = 'prisoners:update-schumann';

    protected $description = 'Set Wilhelm Schumann incarceration date (Jul 11, 1918) and replace his photo with his Leavenworth mugshot';

    private const SOURCE = 'data/photos/legacy/wilhelm-schumann.jpg';

    private const PHOTO = 'prisoners/wilhelm-schumann.jpg';

    public function handle(): int
    {
        $source = database_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: database/'.self::SOURCE);

            return self::FAILURE;
        }

        // Delete the old photo from the public disk, then write the new one.
        Storage::disk('public')->delete(self::PHOTO);
        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Replaced photo on public disk: '.self::PHOTO);

        $p = Prisoner::withoutGlobalScopes()->where('slug', 'wilhelm-schumann')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Schumann%')->first();

        if (! $p) {
            $this->warn('No Wilhelm Schumann record found — photo replaced, but no record to update.');

            return self::SUCCESS;
        }

        $p->photo = self::PHOTO;
        // Restore the cited July 11, 1918 date in the bio if an earlier pass
        // changed it to November 1919.
        if ($p->description && str_contains($p->description, 'November 1919')) {
            $p->description = str_replace('November 1919', 'July 11, 1918', $p->description);
            $this->line('Restored the July 11, 1918 reference in his bio.');
        }
        $p->save();

        $case = $p->cases()->first();
        if ($case) {
            $case->setPartialDate('incarceration_date', 1918, 7, 11); // cited Leavenworth admission date (#14689)
            $case->setPartialDate('release_date', 1921, 12, 25);      // Christmas 1921 commutation
            $case->save();
            $this->info('Set case: incarcerated July 11, 1918, released December 25, 1921.');
        } else {
            $this->warn('No case found to set dates on.');
        }

        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
