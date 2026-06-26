<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the photo and locator details for Haki Malik Abdullah (state name Michael
 * Green) from his Jericho Movement profile: his CDCR number (C56123) and his
 * facility, Folsom State Prison. Fills the empty fields on his existing case
 * (institution + an approximate incarceration year) without disturbing his
 * existing biography. Idempotent; matches the live record by slug/name.
 */
final class SetHakiMalikAbdullahInfo extends Command
{
    protected $signature = 'prisoners:set-haki-malik-abdullah-info';

    protected $description = 'Add Haki Malik Abdullah photo + BOP/CDCR details (C56123, Folsom State Prison)';

    private const SOURCE = 'images/prisoners/haki-malik-abdullah.jpg';

    private const PHOTO = 'prisoners/haki-malik-abdullah.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $p = Prisoner::withoutGlobalScopes()
            ->where('slug', 'haki-malik-abdullah')
            ->orWhere('name', 'like', '%Haki%Abdullah%')
            ->orWhere('aka', 'like', '%Michael Green%')
            ->with('cases')
            ->first();

        if (! $p) {
            $this->warn('Haki Malik Abdullah not found — photo copied but not attached.');

            return self::SUCCESS;
        }

        $p->photo = self::PHOTO;
        $p->inmate_number = 'C56123';
        $p->in_custody = true;
        $p->released = false;
        $p->save();
        $this->info("Set photo + inmate number on {$p->name}.");

        // Fill the empty case: facility (Folsom State Prison) + an approximate
        // incarceration year (early 1980s per his profile). Don't overwrite if
        // already populated.
        $case = $p->cases->first() ?? $p->cases()->make([]);

        if (! $case->institution_id) {
            $institution = Institution::firstOrCreate(
                ['name' => 'Folsom State Prison'],
                ['city' => 'Represa', 'state' => 'California'],
            );
            $case->institution_id = $institution->id;
            $this->line('  set facility: Folsom State Prison');
        }

        if (! $case->incarceration_date) {
            $case->setPartialDate('incarceration_date', 1981); // year precision (approx.)
            $this->line('  set incarceration year: 1981 (approx.)');
        }

        $case->save();

        $this->info("Done. View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
