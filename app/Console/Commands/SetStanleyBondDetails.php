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
 * Authoritative record for Stanley Ray Bond (1944–1972), one of the five in the
 * September 23, 1970 Brighton bank robbery that killed Boston Police Officer
 * Walter Schroeder. Corrects the earlier stub: he was never tried. Arrested
 * September 27, 1970 and indicted October 1, 1970 for first-degree murder and
 * armed robbery alongside William Gilday and Robert Valeri, he was held without
 * bail at the Norfolk County Jail in Dedham, Massachusetts, and died in custody
 * on May 24, 1972 — before trial — when a bomb he was assembling exploded.
 *
 * Create-or-update by name; rebuilds his single case and attaches his portrait
 * (non-free, credited in CREDITS-nonfree.md). Idempotent.
 */
class SetStanleyBondDetails extends Command
{
    protected $signature = 'prisoners:set-stanley-bond-details';

    protected $description = 'Set Stanley Ray Bond\'s corrected record, case, and photo';

    public function handle(): int
    {
        DB::transaction(function () {
            $bond = Prisoner::withUnderReview()->where('name', 'Stanley Bond')->first()
                ?? new Prisoner(['name' => 'Stanley Bond']);

            $bond->fill([
                'name' => 'Stanley Bond',
                'first_name' => 'Stanley',
                'middle_name' => 'Ray',
                'last_name' => 'Bond',
                'aka' => 'Stanley Ray Bond',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Massachusetts',
                'era' => '1970s',
                'ideologies' => ['Anti-War', 'Anti-imperialism'],
                'affiliation' => ['Students for a Democratic Society', 'Weather Underground'],
                'description' => 'Stanley Ray Bond (October 30, 1944 – May 24, 1972) was an Army veteran of the Vietnam War and radical who, while serving a prior robbery sentence, met William "Lefty" Gilday and Robert Valeri in prison. Entering college through the Student Tutor Education Program, he joined the campus movement, Students for a Democratic Society, and the Weather Underground, and helped organize the September 23, 1970 robbery of the State Street Bank and Trust Company in Brighton, Boston, to fund the anti-war movement — the robbery in which Boston Police Officer Walter Schroeder was killed. He was arrested on September 27, 1970 and indicted on October 1, 1970 for murder in the first degree and armed robbery, alongside Gilday and Valeri, and was held without bail at the Norfolk County Jail in Dedham, Massachusetts. No trial ever occurred: he remained in custody until his death on May 24, 1972, at age 27, when a bomb he was assembling exploded.',
                'in_custody' => false,
                'released' => false,   // died in custody
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $bond->setPartialDate('birthdate', 1944, 10, 30);
            $bond->setPartialDate('death_date', 1972, 5, 24);
            $bond->save();

            // Rebuild his single (pre-trial custody) case.
            $jail = Institution::firstOrCreate(['name' => 'Norfolk County Jail'], ['city' => 'Dedham', 'state' => 'Massachusetts']);
            $bond->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $bond->id]);
            $case->fill([
                'prisoner_id' => $bond->id,
                'institution_id' => $jail->id,
                'charges' => 'Murder in the first degree and armed robbery — for the September 23, 1970 robbery of the State Street Bank and Trust Company (Brighton, Boston) in which Officer Walter Schroeder was killed. Indicted October 1, 1970 alongside William Gilday and Robert Valeri.',
                'convicted' => 'Arrested September 27, 1970 and indicted October 1, 1970; held without bail at the Norfolk County Jail in Dedham, Massachusetts. No trial took place — he remained in custody until his death.',
                'sentence' => 'None — he died in custody on May 24, 1972, at age 27, before trial, when a bomb he was assembling exploded.',
                'arrest_date' => '1970-09-27',
                'incarceration_date' => '1970-09-27',
                'death_in_custody_date' => '1972-05-24',
            ]);
            $case->save();

            // Attach the portrait (non-free) if he has none.
            $src = database_path('data/photos/nonfree/stanley-bond.jpg');
            if (is_file($src) && empty($bond->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/stanley-bond.jpg', file_get_contents($src));
                $bond->photo = 'prisoners/stanley-bond.jpg';
                $bond->save();
                $this->info('Linked photo for Stanley Bond.');
            }

            $this->info('Set Stanley Bond (slug: '.$bond->slug.'): arrested 1970-09-27, died in custody 1972-05-24.');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
