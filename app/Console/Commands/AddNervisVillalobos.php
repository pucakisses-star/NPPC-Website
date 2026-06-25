<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Nervis Gerardo Villalobos Cárdenas — a former Venezuelan deputy minister
 * of electric energy indicted in the Southern District of Texas (2017) in the
 * PDVSA bribery / money-laundering case. He was arrested in Madrid in October
 * 2017 on a U.S. warrant and has been held in Spain through years of extradition
 * proceedings; as of early 2025 he remained in Spanish custody with the U.S.
 * charges still pending — he was never surrendered into U.S. custody or tried.
 * Recorded as detained abroad and awaiting trial. Idempotent; matches an
 * existing record (by slug/name) so it won't duplicate.
 */
final class AddNervisVillalobos extends Command
{
    protected $signature = 'prisoners:add-nervis-villalobos';

    protected $description = 'Add Nervis Villalobos Cárdenas (Venezuelan ex-official held in Spain on a US warrant, awaiting trial)';

    public function handle(): int
    {
        $description = implode("\n\n", [
            'Nervis Gerardo Villalobos Cárdenas is a former Venezuelan deputy minister of electric energy under President Hugo Chávez. In 2017 he was indicted in the U.S. District Court for the Southern District of Texas on charges of conspiracy to commit money laundering, money laundering, and conspiracy to violate the U.S. Foreign Corrupt Practices Act, for his alleged role in a scheme in which the owners of U.S.-based companies paid bribes to Venezuelan officials to win energy contracts and payment priority from the state oil company, Petróleos de Venezuela, S.A. (PDVSA).',
            'He was arrested in Madrid, Spain, in October 2017 on a U.S. warrant and has been held in Spain through protracted extradition proceedings. Spain approved his extradition to the United States, but as of early 2025 he remained in Spanish custody with the U.S. charges still pending; he has not been surrendered into U.S. custody or brought to trial.',
        ]);

        $attributes = [
            'name' => 'Nervis Villalobos Cárdenas',
            'first_name' => 'Nervis',
            'middle_name' => 'Gerardo',
            'last_name' => 'Villalobos Cárdenas',
            'gender' => 'Male',
            'state' => 'Venezuela',
            'era' => '2010s',
            'in_custody' => true,
            'awaiting_trial' => true,
            'released' => false,
            'under_review' => false,
            'description' => $description,
        ];

        // 'Nervis' is distinctive; avoid matching the unrelated Oscar Colmenárez
        // Villalobos by not keying on "Villalobos" alone.
        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'nervis-villalobos-cardenas')
            ->orWhere('name', 'like', '%Nervis%')
            ->first();

        if ($prisoner) {
            $prisoner->fill($attributes)->save();
            $this->info("Updated existing prisoner: {$prisoner->name} (ID: {$prisoner->id})");
        } else {
            $prisoner = Prisoner::create($attributes);
            $this->info("Created prisoner: {$prisoner->name} (ID: {$prisoner->id})");
        }

        // A case capturing the U.S. charges and his detention in Spain since the
        // 2017 arrest. Added only if he has no case yet (keeps re-runs idempotent).
        if ($prisoner->cases()->count() === 0) {
            $case = $prisoner->cases()->make([
                'charges' => 'Conspiracy to commit money laundering, money laundering, and conspiracy to violate the U.S. Foreign Corrupt Practices Act — alleged bribes paid to Venezuelan officials to secure PDVSA energy contracts (S.D. Texas, 2017 indictment).',
                'convicted' => 'No — charges pending; held in Spain awaiting extradition',
            ]);
            $case->setPartialDate('arrest_date', 2017, 10, 26);
            $case->setPartialDate('incarceration_date', 2017, 10, 26);
            $case->save();
            $this->info('Added case (US charges; detained in Spain since Oct 2017).');
        } else {
            $this->line('Case(s) already present — left unchanged.');
        }

        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
