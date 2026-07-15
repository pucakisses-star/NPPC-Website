<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fills the Billy Dean Smith stub from the account in The Black Panther
 * (Black Panther Party newspaper), Vol. IX No. 6, November 23, 1972, p. 4 —
 * "The U.S. Military Is Guilty: Billy Dean Smith Found Innocent of 'Fragging'
 * Charges" — and attaches his cropped portrait from that page.
 *
 * Note on dates: the article both prints "March 15, 1972" for the Bien Hoa
 * grenade and says he had been held "since March of 1971" and was acquitted
 * "19 months later" on November 14, 1972. The documented date of the Bien Hoa
 * fragging is March 15, 1971, which matches the confinement timeline, so this
 * entry uses March 1971.
 *
 * Create-or-update by name; rebuilds the single case. Idempotent.
 */
final class FillBillyDeanSmith extends Command
{
    protected $signature = 'prisoners:fill-billy-dean-smith';

    protected $description = 'Fill the Billy Dean Smith entry (Vietnam "fragging" acquittal) and attach his portrait';

    public function handle(): int
    {
        $fortOrd = Institution::firstOrCreate(['name' => 'Fort Ord Stockade'], ['city' => 'Fort Ord', 'state' => 'California'])->id;

        $bio = 'Billy Dean Smith (born 1948) was a Black U.S. Army private whose court-martial for "fragging" — the grenade killing of two officers in Vietnam — became a cause célèbre for the antiwar and Black liberation movements. Born the tenth of twelve children in Bakersfield, California, he grew up partly in Texas and in the Watts section of Los Angeles. Drafted into the Army in 1969 against his will, he trained at Fort Ord, California and Fort Sill, Oklahoma and was sent to Vietnam in October 1970. Outspoken in his refusal to be a "good American soldier" in a war he saw as racist and unjust, he clashed with his commanding officer, Captain Rigby. On March 15, 1971, a fragmentation grenade exploded in the officers\' barracks at Bien Hoa, killing two lieutenants and wounding a third; Smith, the "incorrigible, unwilling" Black soldier in the company, was immediately singled out. He was charged with the murder of the two lieutenants, the attempted murder of his commanding officer and a first sergeant, and two counts of assault on military police — the only physical evidence being a single grenade pin said to have been found in his jacket pocket. Held for about nineteen months in a six-by-nine-foot cell, he was returned to the United States and tried by general court-martial at Fort Ord. On November 14, 1972 the panel acquitted him of the fragging charges; he had pled innocent throughout, and the government could not prove its case. He was released.';

        $prisoner = Prisoner::withUnderReview()->where('name', 'Billy Dean Smith')->first()
            ?? new Prisoner(['name' => 'Billy Dean Smith']);

        $prisoner->fill([
            'name' => 'Billy Dean Smith',
            'first_name' => 'Billy',
            'middle_name' => 'Dean',
            'last_name' => 'Smith',
            'gender' => 'Male',
            'race' => 'Black',
            'state' => 'California',
            'era' => '1970s',
            'ideologies' => ['Anti-War', 'Black liberation', 'Anti-racism'],
            'affiliation' => [],
            'description' => $bio,
            'in_custody' => false,
            'released' => true,
            'in_exile' => false,
            'awaiting_trial' => false,
        ]);
        $prisoner->setPartialDate('birthdate', 1948);
        $prisoner->save();

        // Attach the cropped portrait from the Black Panther newspaper (non-free).
        if (empty($prisoner->photo)) {
            $src = database_path('data/photos/nonfree/billy-dean-smith.jpg');
            if (is_file($src)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/billy-dean-smith.jpg', file_get_contents($src));
                $prisoner->photo = 'prisoners/billy-dean-smith.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/billy-dean-smith.jpg');
            } else {
                $this->warn('Portrait file not found; skipped photo.');
            }
        }

        DB::transaction(function () use ($prisoner, $fortOrd) {
            $prisoner->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $fortOrd,
                'charges' => 'Murder — the "fragging" (grenade) killing of two lieutenants at Bien Hoa, Vietnam — plus attempted murder of his commanding officer and a first sergeant and two counts of assault on military police. The only evidence was a single grenade pin said to have been found in his jacket pocket.',
                'convicted' => 'No — acquitted by general court-martial on November 14, 1972.',
                'sentence' => 'Held about nineteen months in pretrial confinement (a six-by-nine-foot cell) before his acquittal; released.',
            ]);
            $case->setPartialDate('incarceration_date', 1971, 3);
            $case->setPartialDate('release_date', 1972, 11, 14);
            $case->save();
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');

        return self::SUCCESS;
    }
}
