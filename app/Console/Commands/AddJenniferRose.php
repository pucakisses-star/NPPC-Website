<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Creates (or updates) the prisoner page for Jennifer Amelia Rose — a
 * transgender prison-abolitionist activist, jailhouse lawyer, and writer
 * formerly known as Jennifer Gann — built from her Anarchist Black Cross
 * support page. Sets her biography, identity fields, photo, and a case at
 * Salinas Valley State Prison. Matches an existing record (prod may already
 * have her, possibly under "Jennifer Gann") so it won't create a duplicate;
 * idempotent.
 */
final class AddJenniferRose extends Command
{
    protected $signature = 'prisoners:add-jennifer-rose';

    protected $description = 'Create/update the Jennifer Rose (Jennifer Gann) prisoner page from her support page';

    private const SOURCE = 'images/prisoners/jennifer-rose.jpg';

    private const PHOTO = 'prisoners/jennifer-rose.jpg';

    public function handle(): int
    {
        $description = implode("\n\n", [
            'Jennifer Amelia Rose — formerly known as Jennifer Gann, and called "Babygirl" by supporters — is a transgender woman, jailhouse lawyer, writer, and prison abolitionist who has been incarcerated in California since 1990. Of white and Cherokee heritage, she has become a widely supported voice against the abuse of LGBTQ and gender-nonconforming people behind bars.',
            'Originally sentenced to seven years for armed robbery, Rose became a militant jailhouse lawyer and organizer, taking part in prison protests and direct action through the early 1990s. After being beaten by guards at Folsom Prison, she fought back; the resulting charges — including weapons possession and assault on a district attorney and an associate warden — brought multiple sentences of 25 years to life under California\'s "Three Strikes" law in 1995–1996, turning a fixed term into what amounts to a life sentence.',
            'She survived more than a decade in solitary confinement, including the Security Housing Units at Pelican Bay State Prison (1994–2004) and Tehachapi (2010–2011) — conditions widely condemned as torture.',
            'Rose came out as a bisexual trans woman in 2006. She has organized alongside anarchist, feminist, and queer prison abolitionists and the Maoist MIM (Prisons), served for five years in the leadership of Black & Pink, and is a prolific writer, blogger, poet, and artist whose work documents the realities faced by trans prisoners.',
            'In 2018 she won a California Supreme Court ruling granting reconsideration of her sentence, and she has continued to pursue relief, including resentencing under Proposition 36, the Three Strikes Reform Act. She remains imprisoned at Salinas Valley State Prison in Soledad, California.',
        ]);

        $attributes = [
            'name' => 'Jennifer Rose',
            'first_name' => 'Jennifer',
            'middle_name' => 'Amelia',
            'last_name' => 'Rose',
            'aka' => 'Jennifer Gann',
            'gender' => 'Female',
            'race' => 'White and Cherokee',
            'state' => 'California',
            'birthdate' => '1969-10-06',
            'inmate_number' => 'E-23852',
            'website' => 'https://bloomingtonabc.noblogs.org/war-fund/jennifer-gann/',
            'ideologies' => ['Anarchist', 'Prison abolitionist', 'Trans liberation'],
            'affiliation' => ['Black & Pink', 'MIM (Prisons)'],
            'era' => '1990s',
            'in_custody' => true,
            'released' => false,
            'under_review' => false,
            'description' => $description,
            'photo' => self::PHOTO,
        ];

        // Match an existing record first so prod (which may already hold her,
        // possibly as "Jennifer Gann") is updated rather than duplicated.
        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'jennifer-rose')
            ->orWhere('name', 'Jennifer Rose')
            ->orWhere('name', 'like', '%Jennifer%Gann%')
            ->orWhere('aka', 'like', '%Gann%')
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

        // Attach a case at Salinas Valley State Prison, only if she has none yet
        // (keeps re-runs idempotent).
        if ($prisoner->cases()->count() === 0) {
            $institution = Institution::firstOrCreate(
                ['name' => 'Salinas Valley State Prison'],
                ['city' => 'Soledad', 'state' => 'California'],
            );

            $case = $prisoner->cases()->make([
                'institution_id' => $institution->id,
                'charges' => 'Armed robbery (1990); later weapons possession and assault on a district attorney and an associate warden after fighting back against guard abuse at Folsom Prison (1995–1996)',
                'convicted' => 'Yes',
                'sentence' => 'Multiple terms of 25 years to life under California\'s Three Strikes law',
            ]);
            $case->setPartialDate('incarceration_date', 1990);
            $case->save();
            $this->info("Added case at {$institution->name}.");
        } else {
            $this->line('Case(s) already present — left unchanged.');
        }

        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
