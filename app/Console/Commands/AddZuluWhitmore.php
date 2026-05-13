<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds Kenny "Zulu" Whitmore — Louisiana Black Panther Party
 * organizer who spent more than three decades at the Louisiana
 * State Penitentiary at Angola, much of it in extended solitary
 * confinement (CCR / Closed Cell Restricted), before being
 * paroled in 2016.
 */
final class AddZuluWhitmore extends Command {
    protected $signature = 'archive:add-zulu-whitmore';
    protected $description = 'Add Kenny Zulu Whitmore (Louisiana BPP, Angola, released 2016)';

    public function handle(): int {
        $entry = [
            'name' => 'Kenneth Whitmore',
            'first_name' => 'Kenneth',
            'last_name' => 'Whitmore',
            'aka' => 'Kenny "Zulu" Whitmore',
            'description' => "Louisiana Black Panther Party organizer from Baton Rouge. Convicted in the 1970s for a Klan-linked killing he denied committing and sentenced to life at the Louisiana State Penitentiary at Angola, where he spent more than three decades — much of it in extended solitary confinement in CCR (Closed Cell Restricted). A close comrade of the Angola 3, Whitmore was one of the longest-held BPP prisoners in the South. Granted parole in 2016 after a years-long international solidarity campaign.",
            'state' => 'Louisiana',
            'race' => 'Black',
            'gender' => 'Male',
            'ideologies' => ['Black liberation', 'Black Panther Party'],
            'affiliation' => ['Black Panther Party'],
            'era' => '1970s',
            'in_custody' => false,
            'released' => true,
            'cases' => [
                [
                    'institution_name' => 'Louisiana State Penitentiary',
                    'institution_city' => 'Angola',
                    'institution_state' => 'Louisiana',
                    'charges' => 'Murder (Baton Rouge)',
                    'sentence' => 'Life — paroled 2016',
                    'release_date' => '2016-01-01',
                ],
            ],
        ];

        $code = Artisan::call('prisoner:add', ['json' => json_encode($entry)]);
        $this->line(trim(Artisan::output()));

        return $code;
    }
}
