<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Corrects Wilhelm Schumann (Leavenworth inmate #14689) and replaces his photo.
 *
 * His Leavenworth inmate record and the court documents show he arrived at
 * Leavenworth in November 1919 (not July 11, 1918, as previously recorded) and
 * was released December 25, 1921 in the Christmas 1921 commutation. This fixes
 * the incarceration date on his case and in his bio text, and swaps his photo
 * for his Leavenworth mugshot (front view, cropped from the #14689 record).
 * Idempotent; matches by slug then name.
 */
final class UpdateSchumann extends Command
{
    protected $signature = 'prisoners:update-schumann';

    protected $description = 'Fix Wilhelm Schumann incarceration date (Nov 1919) and replace his photo with his Leavenworth mugshot';

    private const SOURCE = 'images/prisoners/wilhelm-schumann.jpg';

    private const PHOTO = 'prisoners/wilhelm-schumann.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

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
        // Keep the bio consistent with the corrected date.
        if ($p->description && str_contains($p->description, 'July 11, 1918')) {
            $p->description = str_replace('July 11, 1918', 'November 1919', $p->description);
            $this->line('Updated the July 11, 1918 reference in his bio to November 1919.');
        }
        $p->save();

        $case = $p->cases()->first();
        if ($case) {
            $case->setPartialDate('incarceration_date', 1919, 11); // arrived at Leavenworth Nov 1919
            $case->setPartialDate('release_date', 1921, 12, 25);   // Christmas 1921 commutation
            $case->save();
            $this->info('Set case: incarcerated November 1919, released December 25, 1921.');
        } else {
            $this->warn('No case found to set dates on.');
        }

        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
