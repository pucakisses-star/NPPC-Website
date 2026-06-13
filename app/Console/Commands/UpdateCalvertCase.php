<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Backfills case detail for Kyle Benjamin Douglas Calvert (the Feb. 24, 2024
 * bombing of the Alabama Attorney General's office in Montgomery). His record
 * already carried the charges, guilty plea, and 108-month sentence but was
 * missing the dates. This adds the arrest, plea, and sentencing dates, marks
 * him in custody, and rounds out the sentence with the supervised-release term
 * and the concurrent Alabama state sentence. It also downloads his booking
 * photo onto the public disk and assigns it. Idempotent — re-running re-sets
 * the same values and re-fetches the photo. Also shortens his display name
 * to "Kyle Calvert" (the form news outlets use) while keeping the original
 * slug so the /prisoner/kyle-benjamin-douglas-calvert URL stays stable.
 */
final class UpdateCalvertCase extends Command {
    protected $signature = 'prisoners:update-calvert-case';
    protected $description = 'Backfill arrest/plea/sentencing dates and sentence detail for Kyle Calvert';

    public function handle(): int {
        $prisoner = Prisoner::where('slug', 'kyle-benjamin-douglas-calvert')->first();
        if (! $prisoner) {
            $this->error('Prisoner kyle-benjamin-douglas-calvert not found.');

            return self::FAILURE;
        }

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);

        // Charges + conviction were already present; only set if missing.
        $case->charges = $case->charges ?: '18 U.S.C. 844(i) malicious use of an explosive; 26 U.S.C. 5861(d) possession of an unregistered destructive device';
        $case->convicted = $case->convicted ?: 'Yes — pleaded guilty';

        // The detail the record was missing.
        $case->arrest_date = '2024-04-10';
        $case->incarceration_date = '2024-04-10';
        $case->sentenced_date = '2024-11-21';
        $case->plead = 'Pleaded guilty to malicious use of an explosive, August 23, 2024';
        $case->sentence = '108 months (9 years) in federal prison plus 3 years of supervised release (sentenced November 21, 2024, U.S. District Court, Middle District of Alabama); also sentenced January 17, 2025 in Montgomery County to 10 years in Alabama state prison for second-degree arson and possession of an explosive device, to run concurrently';
        $case->save();

        $prisoner->in_custody = true;
        $prisoner->released = false;
        $prisoner->awaiting_trial = false;

        // Download the booking photo onto the public disk and assign it. If the
        // fetch fails (network/host issues) we keep whatever photo is already
        // set rather than wiping it, so the command degrades gracefully.
        $photoUrl = 'https://mr.cdn.ignitecdn.com/client_assets/thepostmillennial_com/media/picture/6617/47a7/ad21/820c/78b4/fa84/original_photo_2024-04-10_22.14.24.jpeg?1712801703';
        $photoPath = 'prisoners/kyle-benjamin-douglas-calvert.jpg';
        try {
            $response = Http::timeout(30)->get($photoUrl);
            if ($response->successful() && strlen($response->body()) > 0) {
                Storage::disk('public')->put($photoPath, $response->body());
                $prisoner->photo = $photoPath;
                $this->info("Saved photo to storage/app/public/{$photoPath} (" . strlen($response->body()) . ' bytes).');
            } else {
                $this->warn("Photo fetch returned HTTP {$response->status()}; keeping existing photo.");
            }
        } catch (\Throwable $e) {
            $this->warn('Photo fetch failed (' . $e->getMessage() . '); keeping existing photo.');
        }

        $prisoner->save();

        // Shorten the display name to "Kyle Calvert". Done via a query-builder
        // update so the model's "updating" hook doesn't regenerate the slug
        // from the new name — the existing /prisoner/kyle-benjamin-douglas-
        // calvert URL must stay stable.
        Prisoner::withoutGlobalScopes()
            ->where('id', $prisoner->id)
            ->update(['name' => 'Kyle Calvert']);

        $this->info("Updated Kyle Calvert: arrested 2024-04-10, pleaded 2024-08-23, sentenced 2024-11-21 (108 months + concurrent 10-yr state).");
        $this->info("Display name shortened to 'Kyle Calvert'; slug kept as {$prisoner->slug}.");
        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
