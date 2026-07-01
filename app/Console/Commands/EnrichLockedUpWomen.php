<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Enriches the four elderly/infirm women profiled in the June 6, 2024 Catholic
 * World Report piece "Locked Up: Meet the Elderly and Infirm Women Now in
 * Prison for Pro-Life Activism" — all D.C. Surgi-Clinic (Oct 22, 2020) blockade
 * defendants already in the database:
 *
 *   - Jean Marshall (Catholic nurse; sister of Paulette Harlow)
 *   - Heather Idoni (suffered a stroke in custody; had no age on file)
 *   - Joan Andrews Bell (longtime activist; prison ministry)
 *   - Paulette "Paula" Harlow (frail health; on home confinement until late 2024)
 *
 * Their prior descriptions carried stale hard-coded ages (now out of sync with
 * the site's computed age) and none of the article's human detail. This rewrites
 * the descriptions with the sourced biographical/health facts, drops the baked-in
 * age numbers, and sets Heather Idoni's birth year (she was 59 in June 2024, so
 * ~1965) since she was the only one missing an age.
 *
 * Case facts and pardon status are left as set by earlier commands. Idempotent.
 */
final class EnrichLockedUpWomen extends Command
{
    protected $signature = 'prisoners:enrich-locked-up-women';

    protected $description = 'Enrich the four "Locked Up" pro-life women (Marshall, Idoni, Bell, Harlow)';

    private const DESCRIPTIONS = [
        'jean-marshall' => "Jean Marshall, a Catholic nurse from Kingston, Massachusetts, and the sister of fellow defendant Paulette Harlow, was one of the \"DC Nine\" convicted in the October 22, 2020 blockade of the Washington Surgi-Clinic. Convicted September 15, 2023 and taken into custody, she was sentenced to 24 months in federal prison; her request to postpone reporting for scheduled hip surgery was denied, and she was held despite osteoporosis, acute hip pain and other ailments. \"I'm just there to save the child,\" she said of the blockade. She was pardoned by President Trump on January 23, 2025.",
        'heather-idoni' => 'Heather Idoni, a Christian mother from Linden, Michigan, was one of the "DC Nine" convicted in the October 22, 2020 blockade of the Washington Surgi-Clinic; she was also convicted in a separate Tennessee FACE Act case and in the Michigan clinic-blockade case. Convicted August 29, 2023 and taken into custody, she was sentenced to 24 months in federal prison. A diabetic, she suffered a stroke behind bars in April 2023 — followed by arterial stents, a later mini-stroke and vision loss in one eye — and reported severe medical neglect over her insulin. She was pardoned by President Trump on January 23, 2025.',
        'joan-bell' => 'Joan Andrews Bell, a longtime Catholic pro-life activist from Montague, New Jersey, was one of the "DC Nine" convicted in the October 22, 2020 blockade of the Washington Surgi-Clinic. Convicted September 15, 2023 and taken into custody, she was sentenced to 27 months in federal prison, where she carried on a prison ministry. Married to Chris Bell, she has seven children and seven grandchildren. She was pardoned by President Trump on January 23, 2025.',
        'paula-harlow' => 'Paulette "Paula" Harlow, a Catholic from Kingston, Massachusetts, and the sister of fellow defendant Jean Marshall, was one of the "DC Nine" convicted in the October 22, 2020 blockade of the Washington Surgi-Clinic. Convicted November 16, 2023, she was sentenced to 24 months in federal prison; in frail health — diabetes, spinal stenosis, bronchial asthma, fibromyalgia and a long list of other conditions — she was allowed to remain on home confinement until a medical-prison bed opened, reporting to prison in late November 2024. She was pardoned by President Trump on January 23, 2025.',
    ];

    public function handle(): int
    {
        $updated = 0;

        foreach (self::DESCRIPTIONS as $slug => $description) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("  no prisoner '{$slug}' — skipped");

                continue;
            }

            $prisoner->description = $description;

            // Heather Idoni was the only one of the four without an age on file:
            // 59 as of the June 2024 article → birth year ~1965.
            if ($slug === 'heather-idoni' && ! $prisoner->birthdate) {
                $prisoner->setPartialDate('birthdate', 1965);
            }

            $prisoner->save();
            $this->info("  enriched: {$prisoner->name}");
            $updated++;
        }

        $this->info("\nDone. Enriched {$updated} record(s).");

        return self::SUCCESS;
    }
}
