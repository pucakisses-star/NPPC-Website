<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Updates Salah Sarsour, president of the Islamic Society of Milwaukee, who was
 * detained by ICE on March 30, 2026 and released on June 18, 2026 by court
 * order. Sets his photo, marks him released, and adds his ICE-detention case
 * (arrest + incarceration March 30, 2026; release June 18, 2026) if he has
 * none. Matches the live record by slug, then name. Idempotent.
 */
final class UpdateSarsour extends Command
{
    protected $signature = 'prisoners:update-sarsour';

    protected $description = "Set Salah Sarsour's photo and add his ICE-detention case (released June 18, 2026)";

    private const SOURCE = 'images/prisoners/salah-sarsour.jpg';

    private const PHOTO = 'prisoners/salah-sarsour.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }

        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $p = Prisoner::withoutGlobalScopes()->where('slug', 'salah-sarsour')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Sarsour%')->first();

        if (! $p) {
            $this->warn('No Salah Sarsour record found — photo copied, but no record to update.');

            return self::SUCCESS;
        }

        $p->photo = self::PHOTO;
        $p->in_custody = false;
        $p->released = true;
        $p->save();
        $this->info("Set photo and marked released: {$p->name}.");

        if ($p->cases()->count() === 0) {
            $case = $p->cases()->make([
                'charges' => 'Detained by ICE in removal proceedings. The government cited his failure to disclose a conviction by Israeli military authorities as a teenager in the occupied West Bank; his lawyers argued he was targeted for constitutionally protected Palestinian-rights advocacy. He was held in immigration detention in Indiana.',
                'convicted' => 'Civil immigration detention (no criminal charge); ordered released by a federal judge citing a substantial First Amendment claim',
            ]);
            $case->setPartialDate('arrest_date', 2026, 3, 30);
            $case->setPartialDate('incarceration_date', 2026, 3, 30);
            $case->setPartialDate('release_date', 2026, 6, 18);
            $case->save();
            $this->info('Added ICE-detention case (Mar 30 2026 → Jun 18 2026).');
        } else {
            $this->line('Case already present — left untouched.');
        }

        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
