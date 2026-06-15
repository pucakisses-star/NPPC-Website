<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 8 (final sweep) of the comprehensive-sweep additions: the "Livernois 5,"
 * five young Black men swept up in a Detroit police dragnet after the July 1975
 * Livernois–Fenkell rebellion and tried (and acquitted) for a death during the
 * unrest — a documented racist frame-up. This is the one case from the obscure
 * "Black-liberation long tail" that corroborated across multiple independent
 * sources (Wikipedia, the University of Michigan "Policing" history project, and
 * Detroit newspaper archives); the rest of that tail (e.g. Donald Thigpen, and
 * the Gary, Indiana Dean/Harper case — a violent-crime conviction the Seventh
 * Circuit upheld) did not meet the bar and were dropped. Idempotent.
 */
class AddLivernoisFivePrisoners extends Command {
    protected $signature = 'prisoners:add-livernois-five';
    protected $description = 'Add the Livernois 5 (1975 Detroit racist police-dragnet frame-up; all acquitted)';

    private const LIVERNOIS = "%s was one of the \"Livernois 5,\" five young Black men swept up in a Detroit police dragnet after the July 1975 Livernois–Fenkell rebellion and charged in the death of Marian Pyszko, a white autoworker killed by a crowd during the unrest. The uprising had erupted when Andrew Chinarian, the white owner of Bolton's Bar on Livernois Avenue, shot 18-year-old Obie Wynn in the back of the head in his parking lot — a killing for which Chinarian ultimately served only six months on a misdemeanor charge of careless use of a firearm. As the city demanded answers for Pyszko's death, police rounded up young Black men who could be tied to the area; the five defendants were held for about eleven months and tried three times before all of them were acquitted and released.";

    private const CHARGES = 'Charged in connection with the death of Marian Pyszko, a white autoworker beaten to death by a crowd during the July 1975 Livernois–Fenkell rebellion in Detroit — one of five young Black men rounded up in a police dragnet that critics condemned as a racist sweep of anyone who could be tied to the area, even as the white bar owner whose killing of Obie Wynn set off the rebellion faced only a misdemeanor.';
    private const CONVICTED = 'No — held for about eleven months and tried three times; all five defendants were acquitted and released.';
    private const SENTENCE = 'None — acquitted after roughly eleven months in custody awaiting and standing trial.';

    public function handle(): int {
        $names = [
            ['Raymond Peoples', 'Raymond', 'Peoples'],
            ['James Henderson', 'James', 'Henderson'],
            ['Ronald Jordan', 'Ronald', 'Jordan'],
            ['George Young', 'George', 'Young'],
            ['Douglas Lane', 'Douglas', 'Lane'],
        ];

        DB::transaction(function () use ($names) {
            foreach ($names as [$name, $first, $last]) {
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("Skipped (already exists): {$name}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name'           => $name,
                    'first_name'     => $first,
                    'last_name'      => $last,
                    'description'    => sprintf(self::LIVERNOIS, $name),
                    'gender'         => 'Male',
                    'race'           => 'Black',
                    'state'          => 'Michigan',
                    'era'            => '1970s',
                    'ideologies'     => [],
                    'affiliation'    => [],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => self::CHARGES,
                    'convicted'   => self::CONVICTED,
                    'sentence'    => self::SENTENCE,
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
