<?php

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        // Create or find institution
        $institution = Institution::where('name', 'Perry County Jail')->first()
            ?? Institution::create([
                'name'             => 'Perry County Jail',
                'city'             => 'Linden',
                'state'            => 'Tennessee',
                'mailing_address'  => '121 East Main Street, Linden, TN 37096',
                'physical_address' => '121 East Main Street, Linden, TN 37096',
            ]);

        $prisonerData = [
            'name'                => 'Larry Bushart',
            'first_name'          => 'Larry',
            'middle_name'         => 'G',
            'last_name'           => 'Bushart',
            'gender'              => 'Male',
            'race'                => 'White',
            'age'                 => 62,
            'state'               => 'Tennessee',
            'era'                 => 'Modern',
            'ideologies'          => ['Free Speech', 'First Amendment'],
            'affiliation'         => [],
            'in_custody'          => false,
            'released'            => true,
            'in_exile'            => false,
            'currently_in_exile'  => false,
            'awaiting_trial'      => false,
            'description'         => 'Larry Bushart is a retired Tennessee law enforcement officer with a 34-year career in policing and 24 years of service in the National Guard. In September 2025, Bushart was arrested after sharing a political meme on Facebook in the wake of the assassination of conservative activist Charlie Kirk. The meme, which Bushart did not create, featured a photo of Donald Trump and quoted his response to a 2024 school shooting at Perry High School in Iowa — "We have to get over it." Bushart shared it in a local Perry County, Tennessee Facebook group with the caption "This seems relevant today." Local authorities interpreted the meme as a threat to Perry County High School in Tennessee — a different school in a different state — despite Sheriff Nick Weems later acknowledging that he and his deputies knew the meme referred to the Iowa school. Bushart was charged with "threatening mass violence at a school" and held on a $2 million bond he could not afford. He spent 37 days in jail before the district attorney dropped all charges due to insufficient evidence. During his incarceration, Bushart missed his wedding anniversary and the birth of his grandchild, and he lost his post-retirement job in medical transportation. In December 2025, with representation from the Foundation for Individual Rights and Expression (FIRE), Bushart filed a federal civil rights lawsuit (Bushart v. Perry County) in the U.S. District Court for the Western District of Tennessee, alleging violations of his First Amendment right to free speech and his Fourth Amendment right against unlawful seizure.',
            'website'             => 'https://www.fire.org/cases/larry-bushart-v-perry-county',
        ];

        // Update existing or create new
        $prisoner = Prisoner::where('slug', 'larry-bushart')->first();
        if ($prisoner) {
            $prisoner->update($prisonerData);
        } else {
            $prisonerData['slug'] = 'larry-bushart';
            $prisoner = Prisoner::create($prisonerData);
        }

        // Create case if none exists
        if ($prisoner->cases()->count() === 0) {
            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'Threatening mass violence at a school',
                'arrest_date'        => '2025-09-21',
                'incarceration_date' => '2025-09-21',
                'release_date'       => '2025-10-28',
                'convicted'          => 'No — charges dropped',
                'sentence'           => 'Charges dropped by district attorney due to insufficient evidence',
                'imprisoned_for_days' => 37,
            ]);
        }
    }

    public function down(): void {
        $prisoner = Prisoner::where('slug', 'larry-bushart')->first();
        if ($prisoner) {
            PrisonerCase::where('prisoner_id', $prisoner->id)->delete();
            $prisoner->delete();
        }

        Institution::where('name', 'Perry County Jail')->delete();
    }
};
