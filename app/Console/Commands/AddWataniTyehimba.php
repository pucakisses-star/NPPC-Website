<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Watani Tyehimba — a founder of the New Afrikan People's Organization and the
 * Malcolm X Grassroots Movement, jailed August 21, 1986 for civil contempt after
 * he refused to give a photograph, fingerprints and handwriting samples to a
 * federal grand jury investigating the Black liberation movement (and the October
 * 1981 Brink's expropriation, in which prosecutors alleged he had helped harbor
 * Mutulu Shakur and Cheri Dalton / Nehanda Abiodun). BOP register no. 84463-012;
 * out of federal custody as of October 7, 1987.
 *
 * Idempotent upsert: updates an existing "Tyehimba" record if one is present
 * (e.g. on production), otherwise creates it.
 */
final class AddWataniTyehimba extends Command
{
    protected $signature = 'prisoners:add-watani-tyehimba';

    protected $description = 'Add or update Watani Tyehimba (New Afrikan grand-jury resister, 1986-87)';

    public function handle(): int
    {
        $attrs = [
            'name' => 'Watani Tyehimba',
            'first_name' => 'Watani',
            'last_name' => 'Tyehimba',
            'aka' => 'Watani Sundiata Tyehimba; BOP register no. 84463-012',
            'gender' => 'Male',
            'race' => 'Black',
            'state' => 'California',
            'era' => '1980s',
            'ideologies' => ['New Afrikan', 'Black nationalism', 'Black liberation'],
            'affiliation' => ['New Afrikan People\'s Organization', 'Malcolm X Grassroots Movement', 'Republic of New Afrika'],
            'in_custody' => false,
            'released' => true,
            'awaiting_trial' => false,
            'description' => 'Watani Tyehimba is a longtime activist of the New Afrikan Independence Movement and a founding member of the New Afrikan People\'s Organization and the Malcolm X Grassroots Movement. A former United Parcel Service mechanic and father of three, he was jailed on August 21, 1986 for civil contempt after he refused to provide a photograph, fingerprints and handwriting samples to a federal grand jury investigating the Black liberation movement — part of the long inquiry into the October 1981 Brink\'s expropriation, in which prosecutors alleged he had helped harbor Mutulu Shakur and Cheri Dalton (Nehanda Abiodun). Held as a grand-jury resister rather than on any criminal conviction, he was released from federal custody on October 7, 1987 (Bureau of Prisons register number 84463-012).',
        ];

        $case = [
            'charges' => 'Civil contempt of court — refusal to provide a photograph, fingerprints and handwriting samples to a federal grand jury investigating the Black liberation movement and the October 1981 Brink\'s case',
            'incarceration_date' => '1986-08-21',
            'release_date' => '1987-10-07',
            'convicted' => 'Held for civil contempt (grand-jury resistance); not a criminal conviction',
        ];

        $existing = Prisoner::withUnderReview()->where('name', 'like', '%Tyehimba%')->first();

        if ($existing) {
            // Preserve the existing name; fill in / refresh the rest.
            $update = $attrs;
            unset($update['name']);
            $existing->fill($update)->save();

            $pc = PrisonerCase::where('prisoner_id', $existing->id)->first();
            if ($pc) {
                $pc->fill($case)->save();
            } else {
                $case['prisoner_id'] = $existing->id;
                PrisonerCase::create($case);
            }

            $this->info("Updated existing prisoner: {$existing->name}");

            return self::SUCCESS;
        }

        DB::transaction(function () use ($attrs, $case) {
            $prisoner = Prisoner::create($attrs);
            $case['prisoner_id'] = $prisoner->id;
            PrisonerCase::create($case);
        });

        $this->info('Created: Watani Tyehimba');

        return self::SUCCESS;
    }
}
