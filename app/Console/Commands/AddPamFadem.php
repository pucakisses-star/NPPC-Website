<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Pam Fadem, a white anti-imperialist and founding member of the John Brown
 * Anti-Klan Committee, who in 1984 — while living in Austin, Texas — was charged
 * with criminal contempt for resisting a federal grand jury investigating the
 * Puerto Rican independence movement and allied clandestine groups (one of
 * several JBAKC members subpoenaed in that wave). She was convicted and sentenced
 * in November 1984. She later became a longtime organizer with the California
 * Coalition for Women Prisoners. Delegates to prisoner:add (refuses duplicates by
 * name).
 */
final class AddPamFadem extends Command
{
    protected $signature = 'prisoners:add-pam-fadem';

    protected $description = 'Add grand-jury resister Pam Fadem (John Brown Anti-Klan Committee) as a political prisoner';

    public function handle(): int
    {
        $payload = [
            'name' => 'Pam Fadem',
            'first_name' => 'Pam',
            'last_name' => 'Fadem',
            'aka' => 'Pamela Fadem',
            'gender' => 'Female',
            'race' => 'White',
            'state' => 'Texas',
            'era' => '1980s',
            // Only the birth year (1952) is known; stored as Jan 1 per the
            // codebase convention for year-only dates of birth.
            'birthdate' => '1952-01-01',
            'ideologies' => ['Anti-imperialism', 'Puerto Rican independence', 'Anti-racism'],
            'affiliation' => ['John Brown Anti-Klan Committee'],
            'in_custody' => false,
            'released' => true,
            'description' => 'Pam Fadem (Pamela Fadem) is a white anti-imperialist activist and a founding member of '
                .'the John Brown Anti-Klan Committee. In 1984, while living in Austin, Texas, she was charged with '
                .'criminal contempt for resisting a federal grand jury investigating the Puerto Rican independence '
                .'movement and allied clandestine organizations — part of a wave of subpoenas that targeted John Brown '
                .'Anti-Klan Committee members and Puerto Rican solidarity activists. She was convicted and sentenced in '
                .'November 1984. Fadem went on to decades of movement work, including as a longtime organizer with the '
                .'California Coalition for Women Prisoners and its newsletter The Fire Inside, focused on prison '
                .'abolition, women prisoners\' rights, and disability justice.',
            'cases' => [[
                'charges' => 'Criminal contempt for resisting a 1984 federal grand jury investigating the Puerto Rican '
                    .'independence movement and allied clandestine organizations; one of several John Brown Anti-Klan '
                    .'Committee members subpoenaed who refused to cooperate.',
                'convicted' => 'Yes — convicted of criminal contempt and sentenced in November 1984. (The exact '
                    .'sentence is not documented in available sources.)',
            ]],
        ];

        $result = $this->call('prisoner:add', ['json' => json_encode($payload)]);

        // prisoner:add refuses to update an existing record, so if she was
        // already created before her birth year was known, backfill the DOB
        // here (only when it's blank, so nothing is overwritten).
        $prisoner = Prisoner::withUnderReview()->where('slug', 'pam-fadem')->first();
        if ($prisoner && empty($prisoner->birthdate)) {
            $prisoner->birthdate = '1952-01-01';
            $prisoner->save();
            $this->info('Backfilled Pam Fadem birthdate → 1952-01-01');
        }

        return $result;
    }
}
