<?php

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        // Create or find institution
        $institution = Institution::where('name', 'Etowah County Jail')->first()
            ?? Institution::create([
                'name'             => 'Etowah County Jail',
                'city'             => 'Gadsden',
                'state'            => 'Alabama',
                'physical_address' => '827 Forrest Avenue, Gadsden, AL 35901',
            ]);

        $prisonerData = [
            'name'                 => 'Estefany Rodríguez Flórez',
            'first_name'           => 'Estefany',
            'middle_name'          => 'María',
            'last_name'            => 'Rodríguez Flórez',
            'aka'                  => 'Estefany Rodriguez',
            'gender'               => 'Female',
            'race'                 => 'Hispanic/Latina',
            'state'                => 'Tennessee',
            'era'                  => 'Modern',
            'ideologies'           => ['Press Freedom', 'First Amendment', 'Immigrant Rights'],
            'affiliation'          => ['Nashville Noticias'],
            'in_custody'           => false,
            'released'             => true,
            'in_exile'             => false,
            'currently_in_exile'   => false,
            'awaiting_trial'       => false,
            'description'          => 'Estefany Rodríguez Flórez is a Colombian-born journalist who worked as a reporter for Nashville Noticias, a Spanish-language news outlet in Nashville, Tennessee. She holds a journalism degree from Colombia, where she worked for several years covering armed and militant groups before receiving death threats that forced her to flee the country. She entered the United States legally on a tourist visa in March 2021 and applied for political asylum before it expired. She is married to a U.S. citizen, has a 7-year-old daughter, holds a valid work permit, and has a pending green card application.'
                . "\n\n"
                . 'On March 4, 2026, Rodríguez was arrested by approximately eight ICE agents outside a gym on Murfreesboro Pike in South Nashville. The arrest came one day after she had been in the field reporting on ICE raids targeting Latin American immigrants in apartment complexes southeast of Nashville. Her attorneys argued the detention was retaliation for her critical reporting on ICE, and filed both a First Amendment retaliation claim and an emergency habeas corpus petition in federal court.'
                . "\n\n"
                . 'Rodríguez was transferred to Etowah County Jail in Gadsden, Alabama, where she was held in isolation for five days and denied access to her attorney for ten days. She was subjected to a degrading strip search under the pretext of a lice inspection. On March 12, she was briefly transported toward Louisiana before being returned to Etowah. On March 16, an immigration judge granted her a $10,000 bond, though ICE initially threatened to appeal the decision. She was finally released on March 19, 2026, after 15 days in custody.'
                . "\n\n"
                . 'Her case drew widespread condemnation from press freedom organizations including the Committee to Protect Journalists (CPJ), Reporters Without Borders (RSF), and Free Press. Her asylum case, green card application, and federal constitutional claims remain active. She had no criminal history, a steady employment record, and strong community ties at the time of her arrest.',
            'website'              => 'https://cpj.org/2026/03/timeline-estefany-rodriguezs-arrest-and-ice-detention/',
        ];

        // Update existing or create new
        $prisoner = Prisoner::where('slug', 'estefany-rodriguez-florez')->first();
        if ($prisoner) {
            $prisoner->update($prisonerData);
        } else {
            $prisonerData['slug'] = 'estefany-rodriguez-florez';
            $prisoner = Prisoner::create($prisonerData);
        }

        // Create case if none exists
        if ($prisoner->cases()->count() === 0) {
            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'Immigration violation (overstayed tourist visa)',
                'arrest_date'        => '2026-03-04',
                'incarceration_date' => '2026-03-04',
                'release_date'       => '2026-03-19',
                'convicted'          => 'No — released on bond',
                'sentence'           => 'Released on $10,000 bond; asylum case, green card application, and federal constitutional claims pending',
                'imprisoned_for_days' => 15,
            ]);
        }
    }

    public function down(): void {
        $prisoner = Prisoner::where('slug', 'estefany-rodriguez-florez')->first();
        if ($prisoner) {
            PrisonerCase::where('prisoner_id', $prisoner->id)->delete();
            $prisoner->delete();
        }

        Institution::where('name', 'Etowah County Jail')
            ->whereDoesntHave('cases')
            ->delete();
    }
};
