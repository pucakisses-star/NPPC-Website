<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Edin Alex Enamorado — the Southern California street-vendor-rights
 * activist and lead defendant of the "Justice 8." His six co-defendants are
 * already in the database, but he (the central figure) was missing.
 *
 * Enamorado has been held without bail since his December 14, 2023 arrest,
 * faces 16 felony counts arising from three September 2023 confrontations, and
 * rejected the prosecution's plea offers — so he remains in custody awaiting
 * trial (no conviction as of mid-2026). His case is set with no release date so
 * the site counts his (lengthy) pretrial detention up to the present.
 *
 * Uses prisoner:add (the sanctioned path) then backfills the custody flags and
 * case dates so it is safe to re-run. Idempotent: prisoner:add refuses to
 * create a duplicate.
 */
final class AddEnamorado extends Command
{
    protected $signature = 'prisoners:add-enamorado';

    protected $description = 'Add Edin Alex Enamorado (Justice 8 street-vendor activist)';

    public function handle(): int
    {
        $payload = [
            'name' => 'Edin Alex Enamorado',
            'first_name' => 'Edin',
            'last_name' => 'Enamorado',
            'description' => "Edin Alex Enamorado, of Upland, California, is a street-vendor-rights activist known for viral social-media videos confronting people who harass Latino street vendors and challenging what he calls discriminatory local enforcement of street-vending laws. He is the lead defendant among the \"Justice 8,\" eight activists arrested in December 2023 over confrontations at three September 2023 protests in the Inland Empire and Pomona. Held without bail since his December 14, 2023 arrest, Enamorado faces 16 felony counts — including conspiracy, kidnapping, false imprisonment, and assault with a deadly weapon. His six co-defendants took plea deals in 2024 (the women receiving probation, the men two-year terms with credit for time served) and a seventh had his charges dropped; Enamorado — the group's alleged leader — rejected a plea offer (in part because he is undocumented) and continues to fight the charges from jail, where supporters call his prolonged detention political persecution of an activist.",
            'state' => 'California',
            'gender' => 'Male',
            'ideologies' => ['Street Vendor Rights'],
            'affiliation' => ['#Justice8'],
            'era' => '2020s',
            'in_custody' => true,
            'released' => false,
            'awaiting_trial' => true,
            'cases' => [[
                'charges' => 'Sixteen felony counts — including conspiracy (California PC § 182(a)(1)), kidnapping (PC § 207(a)), false imprisonment (PC § 236) and assault with a deadly weapon (PC § 245(a)(4)) — arising from three September 2023 confrontations (Pomona on Sept. 3 and a Victorville car wash on Sept. 23) that grew out of his street-vendor-defense activism.',
                'arrest_date' => '2023-12-14',
                'incarceration_date' => '2023-12-14',
                'convicted' => "No — held without bail since his December 14, 2023 arrest; rejected the prosecution's plea offer (two felonies and a six-year term) and is awaiting trial.",
            ]],
        ];

        $this->call('prisoner:add', ['json' => json_encode($payload)]);

        // Backfill so the record is correct and the command is safe to re-run.
        $prisoner = Prisoner::withoutGlobalScopes()->where('name', 'Edin Alex Enamorado')->first();
        if (! $prisoner) {
            $this->warn('Enamorado record not found after prisoner:add.');

            return self::SUCCESS;
        }

        $prisoner->in_custody = true;
        $prisoner->released = false;
        $prisoner->awaiting_trial = true;
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if ($case) {
            $case->arrest_date = '2023-12-14';
            $case->incarceration_date = '2023-12-14';
            $case->release_date = null; // still in custody — time served counts to today
            $case->save();
            $case->refresh();
            $this->info("Enamorado: in custody, {$case->imprisoned_for_days} days and counting. View: /prisoner/{$prisoner->slug}");
        }

        return self::SUCCESS;
    }
}
