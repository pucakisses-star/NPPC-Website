<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fills the cases of William "Lefty" Gilday's accomplices in the September 23,
 * 1970 Brighton bank robbery (State Street Bank and Trust, Boston), in which
 * Officer Walter Schroeder was killed, with the arrest/incarceration dates from
 * the sequence of arrests (Valeri the day after the robbery, then Bond, then
 * Gilday):
 *   - Susan Saxe — captured in Philadelphia March 27, 1975; released 1982.
 *   - Katherine Ann Power — surrendered September 15, 1993; released 1999.
 *   - Robert Valeri — arrested September 24, 1970; ~5 years, then paroled.
 *     Added here (with his mug shot). Turned state's evidence.
 *
 * Stanley Bond is handled by prisoners:set-stanley-bond-details. Idempotent.
 */
class AddBrightonRobberyAccomplices extends Command
{
    protected $signature = 'prisoners:add-brighton-robbery-accomplices';

    protected $description = 'Fill Saxe/Power/Valeri cases with arrest dates (1970 Brighton bank robbery) and add Valeri + photo';

    public function handle(): int
    {
        // --- Susan Saxe ---
        DB::transaction(function () {
            $s = Prisoner::withUnderReview()->where('slug', 'susan-saxe')->first();
            if (! $s) {
                $this->warn('Susan Saxe not found.');

                return;
            }
            $case = $s->cases()->first() ?? new PrisonerCase(['prisoner_id' => $s->id]);
            $case->fill([
                'prisoner_id' => $s->id,
                'charges' => 'Armed robbery and murder — for her part in the September 23, 1970 robbery of the State Street Bank and Trust Company in Brighton, Boston, in which Boston Police Officer Walter Schroeder was shot and killed, together with a related National Guard armory raid.',
                'convicted' => 'A fugitive on the FBI Ten Most Wanted list for nearly five years; captured in Philadelphia on March 27, 1975. Pleaded guilty to manslaughter and armed robbery.',
                'sentence' => 'Served roughly seven years in prison; released in 1982.',
                'incarceration_date' => '1975-03-27',
            ]);
            $case->setPartialDate('release_date', 1982);
            $case->save();
            $this->info('Filled Susan Saxe case (incarcerated 1975-03-27).');
        });

        // --- Katherine Ann Power ---
        DB::transaction(function () {
            $p = Prisoner::withUnderReview()->where('slug', 'katherine-ann-power')->first();
            if (! $p) {
                $this->warn('Katherine Ann Power not found.');

                return;
            }
            $case = $p->cases()->first() ?? new PrisonerCase(['prisoner_id' => $p->id]);
            $case->fill([
                'prisoner_id' => $p->id,
                'charges' => 'Armed robbery and murder — for her part in the September 23, 1970 Brighton bank robbery (State Street Bank and Trust, Boston) in which Officer Walter Schroeder was killed.',
                'convicted' => 'A fugitive for 23 years, living under the alias Alice Metzinger in Oregon; she surrendered on September 15, 1993 and pleaded guilty to manslaughter and armed robbery.',
                'sentence' => 'Eight to twelve years; paroled/released in 1999 after serving about six years.',
                'incarceration_date' => '1993-09-15',
            ]);
            $case->setPartialDate('release_date', 1999);
            $case->save();
            $this->info('Filled Katherine Ann Power case (incarcerated 1993-09-15).');
        });

        // --- Robert Valeri: create-or-update + case + photo ---
        DB::transaction(function () {
            $v = Prisoner::withUnderReview()->where('name', 'Robert Valeri')->first()
                ?? new Prisoner(['name' => 'Robert Valeri']);
            $v->fill([
                'name' => 'Robert Valeri',
                'first_name' => 'Robert', 'last_name' => 'Valeri',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'era' => '1970s',
                'ideologies' => ['Anti-War'],
                'affiliation' => ['Weather Underground'],
                'description' => 'Robert Valeri was one of the five involved in the September 23, 1970 robbery of the State Street Bank and Trust Company in Brighton, Boston — the bank expropriation, meant to fund the anti-war movement, in which Boston Police Officer Walter Schroeder was killed. He had met William "Lefty" Gilday and Stanley Bond in prison. The day after the robbery an anonymous tip led police to Valeri; arrested, he named his co-conspirators and turned state\'s evidence, testifying against them in exchange for a reduced sentence. He served about five years and was paroled.',
                'in_custody' => false, 'released' => true, 'in_exile' => false, 'awaiting_trial' => false,
            ]);
            $v->save();

            $case = $v->cases()->first() ?? new PrisonerCase(['prisoner_id' => $v->id]);
            $case->fill([
                'prisoner_id' => $v->id,
                'charges' => 'Murder and armed robbery — for his part in the September 23, 1970 Brighton bank robbery in which Officer Walter Schroeder was killed.',
                'convicted' => 'Arrested September 24, 1970, the day after the robbery; he named his co-conspirators, pleaded guilty, and became a state\'s witness against them.',
                'sentence' => 'A reduced sentence for his cooperation — about five years in prison, then paroled.',
                'arrest_date' => '1970-09-24',
                'incarceration_date' => '1970-09-24',
            ]);
            $case->setPartialDate('release_date', 1975);
            $case->save();

            $src = database_path('data/photos/nonfree/robert-valeri.jpg');
            if (is_file($src) && empty($v->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/robert-valeri.jpg', file_get_contents($src));
                $v->photo = 'prisoners/robert-valeri.jpg';
                $v->save();
                $this->info('Linked photo for Robert Valeri.');
            }
            $this->info('Set Robert Valeri (slug: '.$v->slug.'; arrested 1970-09-24).');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
