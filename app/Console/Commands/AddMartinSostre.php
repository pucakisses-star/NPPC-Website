<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds Martin Sostre — Afro-Puerto Rican anarchist, Black-nationalist
 * bookseller, and prisoners'-rights jailhouse lawyer — surfaced from
 * the JHU Sheridan Libraries' Political Prisoners Collection MS-1058
 * (resource 1749). The collection holds Martin Sostre Defense
 * Committee material from his 1967-1976 Buffalo case.
 */
final class AddMartinSostre extends Command {
    protected $signature = 'archive:add-martin-sostre';
    protected $description = 'Add Martin Sostre to the prisoner database';

    public function handle(): int {
        $payload = [
            'name' => 'Martin Sostre',
            'first_name' => 'Martin',
            'last_name' => 'Sostre',
            'description' => "Afro-Puerto Rican anarchist, Black-nationalist bookseller, and jailhouse lawyer whose civil-rights litigation from inside New York prisons produced a series of landmark prisoners'-rights precedents — religious freedom for incarcerated Muslims, the first significant federal restrictions on punitive solitary confinement (Sostre v. McGinnis, 1969-71), and protection of attorney-client correspondence. Convicted in 1952 at age 28 on heroin charges he disputed, he served the full 12-year term at Attica and other New York state prisons; while inside he became a follower of Malcolm X, organized incarcerated Black Muslims, and filed the litigation that ended the prison system's bar on Muslim religious practice. Released in 1964, Sostre opened the Afro-Asian Book Shop in Buffalo, a center for Black-nationalist and anti-imperialist organizing. Arrested July 14, 1967 during the Buffalo uprising and convicted in 1968 of inciting to riot, arson, and heroin sale — the key prosecution witness, Arto Williams, later recanted under oath, swearing he had been pressured by Buffalo police to frame Sostre. Sentenced to up to 41 years. Amnesty International declared him a prisoner of conscience in 1973 — the first U.S. citizen ever so designated by AI. After a sustained international campaign, Governor Hugh Carey commuted his sentence and he was released February 9, 1976. He continued organizing for tenants' rights and prison abolition until his death August 12, 2015.",
            'state' => 'New York',
            'race' => 'Afro-Puerto Rican',
            'gender' => 'Male',
            'birthdate' => '1923-03-20',
            'death_date' => '2015-08-12',
            'ideologies' => ['Anarchism', 'Black nationalism', 'Prison abolition', 'Revolutionary socialism'],
            'affiliation' => ['Afro-Asian Book Shop', 'Martin Sostre Defense Committee', 'Nation of Islam (during first incarceration)'],
            'era' => '1960s',
            'in_custody' => false,
            'released' => true,
            'cases' => [
                [
                    'institution_name' => 'New York State Department of Correctional Services',
                    'institution_state' => 'New York',
                    'charges' => 'Possession and sale of heroin (Sostre always maintained the case was a frame; while incarcerated he led the Muslim prison-organizing litigation that produced Pierce v. La Vallee and similar rulings)',
                    'arrest_date' => '1952-01-01',
                    'convicted' => 'Yes — 1952',
                    'sentence' => '12 years (served full term, released 1964)',
                ],
                [
                    'institution_name' => 'Green Haven Correctional Facility',
                    'institution_city' => 'Stormville',
                    'institution_state' => 'New York',
                    'charges' => 'Inciting to riot, arson, possession and sale of heroin (Buffalo uprising prosecution — main prosecution witness Arto Williams later recanted, testifying that Buffalo police pressured him to frame Sostre)',
                    'arrest_date' => '1967-07-14',
                    'convicted' => 'Yes — convicted 1968; sentence commuted by Governor Hugh Carey, released Feb 9, 1976',
                    'sentence' => 'Up to 41 years; served ~9 years before commutation',
                ],
            ],
        ];

        $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
        if ($exit === self::SUCCESS) {
            $this->info('ADD: Martin Sostre');
        } else {
            $this->info('SKIP: Martin Sostre (already exists).');
        }

        return self::SUCCESS;
    }
}
