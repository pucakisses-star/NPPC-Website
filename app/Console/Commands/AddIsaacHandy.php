<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the Rev. Isaac W. K. Handy — a Presbyterian minister held without trial
 * at Fort Delaware as a Civil War political prisoner (July 1863 – October 1864)
 * after a remark critical of the U.S. flag, under the wartime suspension of
 * habeas corpus — and sets his profile photo from the committed source image.
 *
 * Idempotent and update-capable: prisoner:add creates a missing record, then
 * this command backfills the status flags + case dates and attaches the photo,
 * so a re-run enriches a record created by an earlier run.
 */
final class AddIsaacHandy extends Command
{
    protected $signature = 'prisoner:add-isaac-handy';

    protected $description = 'Add Rev. Isaac W. K. Handy (Civil War political prisoner, Fort Delaware) with photo';

    private const SOURCE = 'images/prisoners/isaac-handy.jpg';

    private const PHOTO = 'prisoners/isaac-handy.jpg';

    public function handle(): int
    {
        $payload = [
            'name' => 'Isaac W. K. Handy',
            'first_name' => 'Isaac',
            'last_name' => 'Handy',
            'aka' => 'Isaac William Ker Handy',
            'description' => "The Rev. Isaac William Ker Handy (1815–1878) was a Presbyterian minister held without trial at Fort Delaware as a Civil War political prisoner. While having dinner in Delaware in 1863, Handy — who had once served the congregation at Port Penn — remarked that the U.S. flag no longer represented its original \"high and noble principles.\" He was reported and arrested without trial, the writ of habeas corpus having been suspended during the war, and imprisoned at Fort Delaware for roughly fifteen months (July 1863 – October 1864), until he was released in a prisoner exchange. In 1874 he published a detailed diary of his confinement — down to the measurements of his barracks — titled United States Bonds; or, Duress by Federal Authority. Handy had become involved with Delaware College (forerunner of the University of Delaware) in 1851, hired to sell scholarship certificates on the Delmarva Peninsula and appointed a professor of mathematics although he never taught; his brother-in-law, William Purnell, became the college's president in 1870.",
            'state' => 'Delaware',
            'gender' => 'Male',
            'ideologies' => ['Free speech', 'Confederate sympathies'],
            'affiliation' => ['Presbyterian Church'],
            'era' => '1860s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_name' => 'Fort Delaware',
                'institution_city' => 'Delaware City',
                'institution_state' => 'Delaware',
                'charges' => 'Detained without trial as a political prisoner after remarking, at an 1863 dinner in Delaware, that the U.S. flag no longer represented its original "high and noble principles." Arrested on suspicion of Southern sympathies under the wartime suspension of habeas corpus.',
                'arrest_date' => '1863-07-20',
                'incarceration_date' => '1863-07-20',
                'release_date' => '1864-10-13',
                'convicted' => 'Never tried — held without charge under the suspended writ of habeas corpus.',
                'sentence' => 'About 15 months of military detention without trial (July 1863 – October 1864); released in a prisoner exchange.',
                'imprisoned_for_days' => 451,
            ]],
        ];

        $this->call('prisoner:add', ['json' => json_encode($payload)]);

        $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Handy%')->first();

        if (! $prisoner) {
            $this->warn('No Handy record found after prisoner:add — nothing to enrich.');

            return self::SUCCESS;
        }

        // Backfill flags + case dates (prisoner:add won't update an existing record).
        $prisoner->in_custody = false;
        $prisoner->released = true;

        // Attach the committed photo via the public disk.
        $source = public_path(self::SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
            $prisoner->photo = self::PHOTO;
            $this->info('Copied photo to public disk: '.self::PHOTO);
        } else {
            $this->warn('Source image not found: public/'.self::SOURCE);
        }
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if ($case) {
            $caseData = $payload['cases'][0];
            foreach (['charges', 'arrest_date', 'incarceration_date', 'release_date', 'convicted', 'sentence', 'imprisoned_for_days'] as $f) {
                if (! empty($caseData[$f])) {
                    $case->{$f} = $caseData[$f];
                }
            }
            $case->save();
        }

        $this->info("Done. {$prisoner->name} ensured with photo. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
