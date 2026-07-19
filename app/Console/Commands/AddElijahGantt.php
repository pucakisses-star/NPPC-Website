<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Elijah Gantt — a St. Louis–area minister arrested on August 9, 2024 at
 * the protests outside the Ferguson Police Department on the tenth anniversary
 * of the police killing of Michael Brown.
 *
 * Idempotent and update-capable: prisoner:add creates a missing record, then
 * this command backfills the status flags + case fields and attaches the photo
 * if present. The photo slot is pre-wired to prisoners/elijah-gantt.jpg, so it
 * attaches automatically once that image is committed (pending a usable URL).
 */
final class AddElijahGantt extends Command
{
    protected $signature = 'prisoner:add-elijah-gantt';

    protected $description = 'Add Elijah Gantt (Ferguson 10th-anniversary protest arrest)';

    private const SOURCE = 'data/photos/legacy/elijah-gantt.jpg';

    private const PHOTO = 'prisoners/elijah-gantt.jpg';

    public function handle(): int
    {
        $payload = [
            'name' => 'Elijah Gantt',
            'first_name' => 'Elijah',
            'last_name' => 'Gantt',
            'description' => "Elijah Gantt is a St. Louis–area minister who was arrested on August 9, 2024, during protests outside the Ferguson Police Department on the tenth anniversary of the police killing of Michael Brown. Prosecutors charged him with first-degree assault, resisting arrest, first-degree property damage, and two counts of fourth-degree assault, accusing him of charging a Ferguson officer who suffered a serious head injury during the demonstration. Gantt, whom supporters describe as a young man who turned his life around through the church, was held on a \$500,000 cash-only bond with an order to stay 1,500 feet from the Ferguson Police Department. His case, arising from the anniversary protests, remains pending; supporters and clergy have rallied for a reduction of his bond and a fair hearing.",
            'state' => 'Missouri',
            'gender' => 'Male',
            'ideologies' => ['Racial justice', 'Anti-police brutality'],
            'era' => '2020s',
            'in_custody' => true,
            'released' => false,
            'awaiting_trial' => true,
            'cases' => [[
                'institution_name' => 'St. Louis County Justice Center',
                'institution_city' => 'Clayton',
                'institution_state' => 'Missouri',
                'charges' => 'First-degree assault, resisting arrest, first-degree property damage, and two counts of fourth-degree assault, arising from the August 9, 2024 protest outside the Ferguson Police Department on the tenth anniversary of Michael Brown\'s killing. Held on a $500,000 cash-only bond.',
                'arrest_date' => '2024-08-09',
                'incarceration_date' => '2024-08-09',
            ]],
        ];

        $this->call('prisoner:add', ['json' => json_encode($payload)]);

        $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
        if (! $prisoner) {
            $this->warn('No Elijah Gantt record found after prisoner:add — nothing to enrich.');

            return self::SUCCESS;
        }

        $prisoner->in_custody = true;
        $prisoner->released = false;
        $prisoner->awaiting_trial = true;

        $source = database_path(self::SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
            $prisoner->photo = self::PHOTO;
            $this->info('Copied photo to public disk: '.self::PHOTO);
        } else {
            $this->warn('No committed photo yet (public/'.self::SOURCE.') — record saved without one.');
        }
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if ($case) {
            $caseData = $payload['cases'][0];
            foreach (['charges', 'arrest_date', 'incarceration_date'] as $f) {
                if (! empty($caseData[$f])) {
                    $case->{$f} = $caseData[$f];
                }
            }
            $case->save();
        }

        $this->info("Done. {$prisoner->name} ensured. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
