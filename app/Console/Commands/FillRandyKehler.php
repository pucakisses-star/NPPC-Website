<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fills out the empty Randy Kehler stub (draft-resistance and war-tax-resistance
 * activist, 1944–2024) and deletes the garbled duplicate "Randall Forsberg
 * Kehler" — a name-mangle conflating Kehler with Randall Forsberg, the separate
 * analyst who authored the Nuclear Freeze proposal (and who was never
 * imprisoned, so does not belong in the database).
 *
 * Kehler had two incarcerations:
 *   1. Draft resistance — convicted after returning his draft card; served
 *      twenty-two months of a two-year federal sentence (c. 1970–1972).
 *   2. War tax resistance — he and his wife Betsy Corner withheld federal taxes
 *      from 1977; the IRS seized their Colrain, MA home in 1989. Arrested for
 *      trespassing at the seized house in 1990 and 1991, he received a
 *      six-month jail sentence for contempt of court.
 *
 * Rebuilds his cases (delete-then-create) so re-runs collapse to exactly two.
 * Idempotent — create-or-update by slug.
 */
class FillRandyKehler extends Command
{
    protected $signature = 'prisoners:fill-randy-kehler';

    protected $description = 'Fill out Randy Kehler and remove the duplicate "Randall Forsberg Kehler" stub';

    public function handle(): int
    {
        DB::transaction(function () {
            $k = Prisoner::withUnderReview()->where('slug', 'randy-kehler')->first()
                ?? new Prisoner(['name' => 'Randy Kehler']);

            $k->fill([
                'name' => 'Randy Kehler',
                'first_name' => 'Randy',
                'last_name' => 'Kehler',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Massachusetts',
                'era' => '1970s',
                'ideologies' => ['Anti-War', 'Pacifism', 'Anti-militarism'],
                'affiliation' => ['War Resisters League', 'Nuclear Weapons Freeze Campaign', 'Traprock Peace Center'],
                'description' => 'Randy Kehler (July 16, 1944 – July 2024) was an American anti-war activist, draft resister, and war tax resister. Working for the War Resisters League in San Francisco from 1967, he returned his draft card to the Selective Service rather than cooperate with the Vietnam-era draft. He represented himself at trial, arguing the war and the draft were themselves unjust, and — refusing even to claim conscientious-objector status as a form of cooperation — was convicted and served twenty-two months of a two-year federal sentence. Daniel Ellsberg later credited hearing Kehler speak in August 1969, as Kehler prepared to go to prison, as a pivotal moment in his own decision to release the Pentagon Papers. From 1977, Kehler and his wife Betsy Corner refused to pay federal income taxes in protest of military spending, redirecting the money to charity; the IRS seized their Colrain, Massachusetts farmhouse in 1989. Arrested for trespassing at the seized home in 1990 and 1991, Kehler was jailed for six months for contempt of court. He went on to serve as national coordinator of the Nuclear Weapons Freeze Campaign (1981–1984) and co-founded the Traprock Peace Center and the Valley Community Land Trust.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $k->setPartialDate('birthdate', 1944, 7, 16);
            $k->setPartialDate('death_date', 2024, 7);
            $k->save();

            // Rebuild his two cases so re-runs collapse to exactly two.
            $k->cases()->delete();

            $draft = new PrisonerCase(['prisoner_id' => $k->id]);
            $draft->fill([
                'prisoner_id' => $k->id,
                'charges' => 'Refusing to comply with the Selective Service (draft resistance) — he returned his draft card to protest the Vietnam War.',
                'convicted' => 'Yes — convicted at trial, where he represented himself and argued that the draft and the war were unjust, declining to seek conscientious-objector status.',
                'sentence' => 'Two years; he served twenty-two months in federal prison.',
            ]);
            $draft->setPartialDate('incarceration_date', 1970);
            $draft->setPartialDate('release_date', 1972);
            $draft->save();

            $tax = new PrisonerCase(['prisoner_id' => $k->id]);
            $tax->fill([
                'prisoner_id' => $k->id,
                'charges' => 'Trespassing and contempt of court — arrested in 1990 and 1991 at the Colrain, Massachusetts farmhouse the IRS had seized in 1989 over his war tax resistance (withholding federal income taxes to protest military spending, from 1977).',
                'convicted' => 'Held in contempt of court for refusing to cooperate.',
                'sentence' => 'Six months in jail.',
            ]);
            $tax->setPartialDate('incarceration_date', 1991);
            $tax->save();

            $this->info('Filled Randy Kehler (slug: '.$k->slug.') with two cases.');

            // Delete the garbled duplicate stub.
            $dup = Prisoner::withUnderReview()->where('slug', 'randall-forsberg-kehler')->first();
            if ($dup && $dup->id !== $k->id) {
                $dup->cases()->delete();
                $dup->delete();
                $this->info('Deleted duplicate stub "Randall Forsberg Kehler".');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
