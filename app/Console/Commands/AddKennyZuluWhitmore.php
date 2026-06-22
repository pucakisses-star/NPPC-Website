<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Kenny "Zulu" Whitmore — a member of the Angola chapter of the Black
 * Panther Party held at the Louisiana State Penitentiary (Angola). Befriended in
 * prison by Angola 3 member Herman Wallace, he was convicted in January 1977 of
 * the August 1973 murder of former Zachary, Louisiana mayor Marshall Bond — a
 * conviction he and his lawyers have always maintained is wrongful. He arrived
 * at Angola on March 14, 1978 and spent roughly 37 years in solitary confinement
 * (one of the longest such stints in U.S. history) until he was moved to the
 * general population in November 2015. He is alive and remains incarcerated.
 *
 * Born c. 1954; an exact date of birth is not established in accessible sources,
 * so no precise birthdate is recorded (the circa year is noted in the bio). He
 * has not died, so no death date is set.
 *
 * Upsert + idempotent: if a Whitmore record already exists (e.g. on production),
 * the command fills only blank fields and never clobbers existing prose; status
 * flags (in custody, not released, not awaiting trial) are set authoritatively.
 * His portrait is attached separately via prisoners:attach-nonfree-photos.
 */
final class AddKennyZuluWhitmore extends Command
{
    protected $signature = 'prisoners:add-kenny-zulu-whitmore';

    protected $description = 'Add Kenny "Zulu" Whitmore (Angola chapter, Black Panther Party; Louisiana State Penitentiary)';

    public function handle(): int
    {
        $description = 'Kenny "Zulu" Whitmore (born c. 1954) is a member of the Angola chapter of the Black Panther Party, '
            .'held at the Louisiana State Penitentiary at Angola. Befriended in prison by Herman Wallace of the "Angola 3," '
            .'he was sentenced in January 1977 to life imprisonment for the August 1973 killing of former Zachary, Louisiana '
            .'mayor Marshall Bond — a crime he has always maintained he did not commit and which his legal team calls a '
            .'wrongful conviction. He arrived at Angola on March 14, 1978 and was held in solitary confinement for roughly '
            .'37 years — one of the longest such stints in U.S. history, condemned by the U.N. Special Rapporteur on Torture — '
            .'until he was finally moved to the general population in November 2015. He remains incarcerated.';

        $fields = [
            'name' => 'Kenny "Zulu" Whitmore',
            'first_name' => 'Kenny',
            'last_name' => 'Whitmore',
            'aka' => 'Kenneth Whitmore; Zulu',
            'gender' => 'Male',
            'race' => 'Black',
            'state' => 'Louisiana',
            'era' => '1970s',
            'ideologies' => ['Black Power'],
            'affiliation' => ['Black Panther Party'],
            'inmate_number' => '86468',
            'description' => $description,
        ];

        // Status: alive and still incarcerated (no parole, no death).
        $status = [
            'in_custody' => true,
            'released' => false,
            'in_exile' => false,
            'currently_in_exile' => false,
            'awaiting_trial' => false,
        ];

        $case = [
            'charges' => 'First-degree murder of former Zachary, Louisiana mayor Marshall Bond (killed August 1973); Whitmore has always maintained his innocence.',
            'convicted' => 'Yes — convicted January 1977 (trial January 3–6, 1977); his lawyers maintain it is a wrongful conviction.',
            'sentenced_date' => '1977-01-06',
            'incarceration_date' => '1978-03-14',
            'sentence' => 'Life imprisonment plus 99 years. Arrested December 1973; arrived at the Louisiana State Penitentiary (Angola) on March 14, 1978; held in solitary confinement for roughly 37 years until moved to the general population in November 2015. Still incarcerated.',
        ];

        $existing = Prisoner::withUnderReview()
            ->where('name', 'like', '%Whitmore%')
            ->where(function ($q) {
                $q->where('name', 'like', '%Zulu%')
                    ->orWhere('name', 'like', '%Kenny%')
                    ->orWhere('name', 'like', '%Kenneth%')
                    ->orWhere('aka', 'like', '%Zulu%');
            })
            ->first();

        DB::transaction(function () use ($existing, $fields, $status, $case) {
            $institution = Institution::firstOrCreate(
                ['name' => 'Louisiana State Penitentiary'],
                ['city' => 'Angola', 'state' => 'Louisiana']
            );
            $case['institution_id'] = $institution->id;

            if (! $existing) {
                $prisoner = Prisoner::create(array_merge($fields, $status));
                $case['prisoner_id'] = $prisoner->id;
                PrisonerCase::create($case);
                $this->info('Added Kenny "Zulu" Whitmore.');

                return;
            }

            // Update path: fill only blank prose/array fields, set status flags.
            $fill = [];
            foreach ($fields as $key => $value) {
                $current = $existing->getAttribute($key);
                $blank = $current === null || $current === ''
                    || (is_array($current) && count($current) === 0);
                if ($blank) {
                    $fill[$key] = $value;
                }
            }
            $existing->fill(array_merge($fill, $status))->save();

            $row = $existing->cases()->first();
            if (! $row) {
                $case['prisoner_id'] = $existing->id;
                PrisonerCase::create($case);
            } else {
                $caseFill = [];
                foreach ($case as $key => $value) {
                    if ($key === 'prisoner_id') {
                        continue;
                    }
                    $current = $row->getAttribute($key);
                    if ($current === null || $current === '') {
                        $caseFill[$key] = $value;
                    }
                }
                if ($caseFill !== []) {
                    $row->fill($caseFill)->save();
                }
            }
            $this->info('Updated existing Whitmore record: '.$existing->name);
        });

        return self::SUCCESS;
    }
}
