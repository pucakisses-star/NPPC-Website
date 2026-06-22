<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fleshes out Patrick L. Quinlan's 1913 Paterson silk-strike case and attaches
 * his photograph. Quinlan (1883–1948) — an Irish-born socialist orator and IWW
 * speaker — was arrested during the 1913 Paterson strike and, after a first
 * trial ended in a 7–5 mistrial favoring acquittal, convicted at retrial of
 * inciting to riot and sentenced to two-to-seven years at the New Jersey State
 * Prison in Trenton (plus a $500 fine). He entered prison in July 1913 and was
 * paroled in 1916 after a national labor-defense campaign.
 *
 * The photo is a September 1921 portrait from The New Age (Buffalo, NY),
 * public domain (PD-1923), committed at database/data/photos/patrick-quinlan.jpg
 * and credited in CREDITS-wikipedia.md.
 *
 * Idempotent: re-running updates the case and re-syncs the photo. Creates the
 * prisoner if he is not present (he is normally added by the rebel-girl batch).
 */
final class AddPatrickQuinlanCasePhoto extends Command
{
    protected $signature = 'prisoners:add-patrick-quinlan-case-photo';

    protected $description = "Complete Patrick Quinlan's 1913 Paterson case and attach his photo";

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Patrick Quinlan')->first();

        if (! $prisoner) {
            $prisoner = Prisoner::create([
                'name' => 'Patrick Quinlan',
                'first_name' => 'Patrick',
                'last_name' => 'Quinlan',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'New Jersey',
                'era' => '1910s',
                'birthdate' => '1883-02-23',
                'ideologies' => ['Socialism', 'Syndicalism'],
                'affiliation' => ['Industrial Workers of the World (IWW)', 'Socialist Party of America'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Patrick L. Quinlan (1883–1948) was an Irish-born American socialist orator and labor organizer who spoke for the Socialist Party and the Industrial Workers of the World. Arrested during the 1913 Paterson, New Jersey silk strike of 25,000 workers, he was — after a first trial ended in a hung jury favoring acquittal — convicted at retrial of inciting to riot and sentenced to two to seven years at the New Jersey State Prison in Trenton. He became the focus of a sustained national defense campaign and was paroled in 1916.',
            ]);
            $this->info('Created Patrick Quinlan.');
        }

        $institution = Institution::firstOrCreate(
            ['name' => 'New Jersey State Prison'],
            ['city' => 'Trenton', 'state' => 'New Jersey'],
        );

        $caseData = [
            'charges' => 'Inciting to riot — inciting personal injury, inciting assault, and advocating burning and destruction (1913 Paterson silk strike)',
            'arrest_date' => '1913-02-25',
            'incarceration_date' => '1913-07-07',
            'release_date' => '1916-01-15',
            'convicted' => 'Yes — convicted at a 1913 retrial (the first trial ended in a 7–5 mistrial favoring acquittal)',
            'sentence' => 'Two to seven years at the New Jersey State Prison (Trenton) plus a $500 fine; paroled in 1916 after a national labor-defense campaign',
            'institution_id' => $institution->id,
        ];

        $case = $prisoner->cases()->first();
        if ($case) {
            $case->fill($caseData)->save();
            $this->info('Updated Patrick Quinlan\'s Paterson case.');
        } else {
            $caseData['prisoner_id'] = $prisoner->id;
            PrisonerCase::create($caseData);
            $this->info('Added Patrick Quinlan\'s Paterson case.');
        }

        $this->attachLocalPhoto($prisoner, 'photos/patrick-quinlan.jpg');

        return self::SUCCESS;
    }

    /**
     * Copy the committed public-domain photo onto the public disk and set it as
     * the prisoner's photo. Re-synced on every run.
     */
    private function attachLocalPhoto(Prisoner $prisoner, string $relative): void
    {
        $src = database_path('data/'.$relative);
        if (! is_file($src)) {
            $this->warn("  Local photo not found: {$relative}");

            return;
        }

        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg');
        $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;
        Storage::disk('public')->put($path, (string) file_get_contents($src));
        $prisoner->photo = $path;
        $prisoner->save();
        $this->info("  Photo set: {$path}");
    }
}
