<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds (or updates) Trenten Edward Barker with his BOP locator details and a
 * case at FCI Lompoc II (incarcerated April 30, 2026), and sets his profile
 * photo (cropped 15% on the right) from the committed image. Idempotent;
 * matches an existing record by slug/name so it won't duplicate.
 */
final class AddTrentenBarker extends Command
{
    protected $signature = 'prisoners:add-trenten-barker';

    protected $description = 'Add Trenten Edward Barker (BOP details, FCI Lompoc II case, photo)';

    private const SOURCE = 'images/prisoners/trenten-barker.jpg';

    private const PHOTO = 'prisoners/trenten-barker.jpg';

    public function handle(): int
    {
        $attributes = [
            'name' => 'Trenten Edward Barker',
            'first_name' => 'Trenten',
            'middle_name' => 'Edward',
            'last_name' => 'Barker',
            'gender' => 'Male',
            'race' => 'White',
            'age' => 35,
            'inmate_number' => '93764-511',
            'in_custody' => true,
            'released' => false,
            'under_review' => false,
            'photo' => self::PHOTO,
        ];

        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'trenten-barker')
            ->orWhere('slug', 'trenten-edward-barker')
            ->orWhere('name', 'like', '%Trenten%Barker%')
            ->first();

        if ($prisoner) {
            $prisoner->fill($attributes)->save();
            $this->info("Updated existing prisoner: {$prisoner->name} (ID: {$prisoner->id})");
        } else {
            $prisoner = Prisoner::create($attributes);
            $this->info("Created prisoner: {$prisoner->name} (ID: {$prisoner->id})");
        }

        // Copy the committed photo onto the public disk where photos are served.
        $source = public_path(self::SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
            $this->info('Photo copied to public disk: '.self::PHOTO);
        } else {
            $this->warn('Source image not found: public/'.self::SOURCE);
        }

        // Case at FCI Lompoc II, incarcerated April 30, 2026 — only if none yet.
        if ($prisoner->cases()->count() === 0) {
            $institution = Institution::firstOrCreate(
                ['name' => 'FCI Lompoc II'],
                ['city' => 'Lompoc', 'state' => 'California'],
            );

            $case = $prisoner->cases()->make(['institution_id' => $institution->id]);
            $case->setPartialDate('incarceration_date', 2026, 4, 30);
            $case->save();
            $this->info("Added case at {$institution->name} (incarcerated 2026-04-30).");
        } else {
            $this->line('Case(s) already present — left unchanged.');
        }

        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
