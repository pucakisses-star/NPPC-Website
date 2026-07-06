<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fills in the cases for the last three Communists freed from the Harlan,
 * Kentucky jail on April 16, 1932, per The New York Times ("All Reds Freed at
 * Harlan," Apr. 17, 1932): Doris Parks, Verne Smith, and Vincent Kemenovich,
 * held during the 1931–32 Harlan–Bell County coal strike and awaiting a May 1932
 * trial on criminal-syndicalism indictments.
 *
 *   - Doris Parks and Vincent Kemenovich are already in the database with empty
 *     cases; this fills them. Parks's description is also corrected — she was
 *     jailed in February 1932 with the Waldo Frank writers' delegation to
 *     Pineville, not in the January 4 raid.
 *   - Verne Smith (jailed January 4, 1932 with Kemenovich and six others) is new.
 *
 * Idempotent: updates the single case in place (or creates one) and skips the
 * new-record creation if Verne Smith already exists.
 */
class UpdateHarlanSyndicalismCases extends Command
{
    protected $signature = 'prisoners:update-harlan-syndicalism-cases';

    protected $description = 'Fill/correct cases for the three Reds freed from the Harlan KY jail on April 16, 1932 (Parks, Smith, Kemenovich)';

    public function handle(): int
    {
        $harlan = Institution::firstOrCreate(['name' => 'Harlan County Jail'], ['city' => 'Harlan', 'state' => 'Kentucky']);

        // --- Doris Parks: correct the description and fill the case ---
        DB::transaction(function () use ($harlan) {
            $parks = Prisoner::withUnderReview()->where('name', 'Doris Parks')->first();
            if (! $parks) {
                $this->warn('Doris Parks not found.');

                return;
            }
            $parks->description = 'Doris Parks was a Communist activist jailed during the 1931–32 Harlan–Bell County, Kentucky coal strike. In February 1932 she traveled to Pineville with the writers\' delegation led by Waldo Frank (the National Committee for the Defense of Political Prisoners, which went to the coal fields to support the striking miners) and was jailed on a criminal-syndicalism charge, then moved to the jail at Harlan. She was one of the last three Communists freed from the Harlan jail on April 16, 1932, and was to be tried the following month on a criminal-syndicalism indictment. The National Miners\' Union organizers seized in the January 4, 1932 raid on the union\'s Pineville headquarters — with whom she was held — each faced up to twenty-one years; the prisoners were freed after a national International Labor Defense campaign.';
            $parks->state = 'Kentucky';
            $parks->era = '1930s';
            $parks->save();

            $case = $parks->cases()->first() ?? new PrisonerCase(['prisoner_id' => $parks->id]);
            $case->fill([
                'prisoner_id' => $parks->id,
                'institution_id' => $harlan->id,
                'charges' => 'Criminal syndicalism — jailed in February 1932 after traveling to Pineville, Kentucky with the Waldo Frank writers\' delegation in support of the striking miners during the Harlan–Bell coal strike.',
                'convicted' => 'Indicted for criminal syndicalism and freed on April 16, 1932 pending trial (set for May 1932).',
                'release_date' => '1932-04-16',
            ]);
            $case->save();
            $this->info('Updated Doris Parks (corrected Feb 1932 / Waldo Frank; case filled).');
        });

        // --- Vincent Kemenovich: fill the case ---
        DB::transaction(function () use ($harlan) {
            $k = Prisoner::withUnderReview()->where('name', 'Vincent Kemenovich')->first();
            if (! $k) {
                $this->warn('Vincent Kemenovich not found.');

                return;
            }
            $case = $k->cases()->first() ?? new PrisonerCase(['prisoner_id' => $k->id]);
            $case->fill([
                'prisoner_id' => $k->id,
                'institution_id' => $harlan->id,
                'charges' => 'Criminal syndicalism — jailed in the January 4, 1932 raid on the National Miners\' Union Southern District headquarters at Pineville, Kentucky (Bell County), during the Harlan–Bell coal strike. Each of those seized faced up to twenty-one years.',
                'convicted' => 'Indicted for criminal syndicalism; held at Pineville and then Harlan, and freed on April 16, 1932 (one of the last three released) pending trial set for May 1932.',
                'incarceration_date' => '1932-01-04',
                'release_date' => '1932-04-16',
            ]);
            $case->save();
            $this->info('Updated Vincent Kemenovich (case filled: 1932-01-04 to 1932-04-16).');
        });

        // --- Verne Smith: add (new) ---
        DB::transaction(function () use ($harlan) {
            $name = 'Verne Smith';
            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }
            $smith = Prisoner::create([
                'name' => $name,
                'first_name' => 'Verne',
                'last_name' => 'Smith',
                'description' => 'Verne Smith was a Communist and National Miners\' Union organizer jailed in the January 4, 1932 raid on the union\'s Southern District headquarters at Pineville, Kentucky, during the 1931–32 Harlan–Bell County coal strike. Seized with Vincent Kemenovich and six others and charged with criminal syndicalism — a charge that carried up to twenty-one years — he was held at Pineville and then Harlan. He was one of the last three Communists freed from the Harlan jail on April 16, 1932, and was to be tried the following month on a criminal-syndicalism indictment. The prisoners were freed after a national International Labor Defense campaign.',
                'gender' => 'Male',
                'state' => 'Kentucky',
                'era' => '1930s',
                'ideologies' => ['Communism', 'Labor organizing'],
                'affiliation' => ['National Miners Union'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $smith->id,
                'institution_id' => $harlan->id,
                'charges' => 'Criminal syndicalism — jailed in the January 4, 1932 raid on the National Miners\' Union Southern District headquarters at Pineville, Kentucky (Bell County), with Vincent Kemenovich and six others, during the Harlan–Bell coal strike. Each faced up to twenty-one years.',
                'convicted' => 'Indicted for criminal syndicalism; held at Pineville and then Harlan, and freed on April 16, 1932 (one of the last three released) pending trial set for May 1932.',
                'incarceration_date' => '1932-01-04',
                'release_date' => '1932-04-16',
            ]);
            $this->info('Added: '.$smith->name.' (slug: '.$smith->slug.')');
        });

        return self::SUCCESS;
    }
}
