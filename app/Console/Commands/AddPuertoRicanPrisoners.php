<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Batch 3 of the historical political-prisoner additions: Puerto Rican
 * independentistas — FALN members/grand-jury resisters and Los Macheteros
 * defendants — from the 1970s–80s. All long since released (Clinton-era
 * clemencies of 1999/2000). Two names from the source list are omitted as
 * undocumentable: "Félix Rosa" (#N11373 — only Luis Rosa, already in the DB,
 * is traceable) and "Julio Veras y Delgadillo" (a John Doe). Idempotent.
 */
final class AddPuertoRicanPrisoners extends Command
{
    protected $signature = 'prisoners:add-puerto-rican';

    protected $description = 'Add the missing Puerto Rican (FALN / Los Macheteros) political prisoners';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Haydée Beltrán Torres', 'first' => 'Haydée', 'last' => 'Beltrán Torres', 'aka' => 'Marie Haydée Beltrán',
                'gender' => 'Female', 'state' => 'New York', 'inmate' => '88462-024', 'era' => '1970s', 'affiliation' => ['FALN'],
                'desc' => 'Marie Haydée Beltrán Torres was a member of the FALN (Fuerzas Armadas de Liberación Nacional), the clandestine Puerto Rican independence group. She was convicted of the 1977 bombing of the Mobil Oil Building in Manhattan, in which one person was killed, and sentenced to life. Arrested with ten other FALN members in Evanston, Illinois in 1980, she was among those offered conditional clemency by President Clinton in 1999; she declined its terms and was released some years later.',
                'prison' => ['FCI Pleasanton', 'Dublin', 'California'],
                'charges' => '1977 FALN bombing of the Mobil Oil Building, Manhattan (one person killed).', 'convicted' => 'Yes', 'sentence' => 'Life imprisonment',
                'arrest' => [1980, 4, 4], 'incarc' => [1980, 4, 4],
            ],
            [
                'name' => 'Steven Guerra', 'first' => 'Steven', 'last' => 'Guerra',
                'gender' => 'Male', 'state' => 'New York', 'inmate' => '1588-053', 'era' => '1980s', 'affiliation' => ['FALN'],
                'desc' => 'Steven Guerra was a Puerto Rican independence activist identified by prosecutors as a leader of FALN support work in the United States. In 1983 he was one of several people convicted of criminal contempt for refusing to testify before a federal grand jury investigating the FALN. He was sentenced to three years and released in 1986 after serving about 23 months; he was never charged with any of the group\'s attacks.',
                'prison' => ['FCI La Tuna', 'Anthony', 'Texas'],
                'charges' => 'Criminal contempt — refused to testify before a federal grand jury investigating the FALN (1983).', 'convicted' => 'Contempt (1983)', 'sentence' => '3 years (served ~23 months)',
                'arrest' => [1983, null, null], 'incarc' => [1983, null, null], 'release' => [1986, null, null],
            ],
            [
                'name' => 'Julio Rosado', 'first' => 'Julio', 'last' => 'Rosado',
                'gender' => 'Male', 'state' => 'New York', 'inmate' => '19793-053', 'era' => '1980s', 'affiliation' => ['FALN'],
                'desc' => 'Julio Rosado, with his brother Andrés, was a Puerto Rican independence activist and one of the well-known "grand jury resisters" of the late 1970s and 1980s — independentistas jailed for contempt after refusing, on principle, to testify before federal grand juries investigating the FALN.',
                'prison' => ['FCI Ray Brook', 'Ray Brook', 'New York'],
                'charges' => 'Contempt — refused to testify before federal grand juries investigating the FALN.', 'convicted' => 'Contempt',
            ],
            [
                'name' => 'Andrés Rosado', 'first' => 'Andrés', 'last' => 'Rosado', 'aka' => 'Andrew Rosado',
                'gender' => 'Male', 'state' => 'New York', 'inmate' => '19794-053', 'era' => '1980s', 'affiliation' => ['FALN'],
                'desc' => 'Andrés Rosado, with his brother Julio, was a Puerto Rican independence activist and grand jury resister, jailed for contempt after refusing to testify before federal grand juries investigating the FALN.',
                'prison' => ['Allenwood Federal Prison', 'Montgomery', 'Pennsylvania'],
                'charges' => 'Contempt — refused to testify before federal grand juries investigating the FALN.', 'convicted' => 'Contempt',
            ],
            [
                'name' => 'Isaac Camacho Negrón', 'first' => 'Isaac', 'last' => 'Camacho Negrón',
                'gender' => 'Male', 'state' => 'Puerto Rico', 'inmate' => '03174-069', 'era' => '1980s', 'affiliation' => ['Los Macheteros'],
                'desc' => 'Isaac Camacho Negrón was a member of Los Macheteros (the Boricua Popular Army), the clandestine Puerto Rican independence organization. He was charged in connection with the group\'s September 1983 robbery of the Wells Fargo depot in West Hartford, Connecticut — about $7 million taken to fund the independence struggle. He was acquitted of the robbery itself but convicted of conspiracy in 1989; his sentence was commuted under a 2000 clemency grant.',
                'prison' => ['FCI Talladega', 'Talladega', 'Alabama'],
                'charges' => 'Conspiracy in the 1983 Los Macheteros robbery of the Wells Fargo depot, West Hartford, CT ($7M). Acquitted of the robbery itself.', 'convicted' => 'Conspiracy (1989); sentence commuted by clemency (2000)',
                'arrest' => [1985, 8, 30], 'release' => [2000, null, null],
            ],
            [
                'name' => 'Ángel Díaz Ruiz', 'first' => 'Ángel', 'last' => 'Díaz Ruiz',
                'gender' => 'Male', 'state' => 'Puerto Rico', 'inmate' => '03175-069', 'era' => '1980s', 'affiliation' => ['Los Macheteros'],
                'desc' => 'Ángel Díaz Ruiz was a member of Los Macheteros (the Boricua Popular Army) charged in connection with the group\'s September 1983 robbery of the Wells Fargo depot in West Hartford, Connecticut, which the group carried out to fund the Puerto Rican independence movement.',
                'prison' => ['Metropolitan Correctional Center, New York', 'New York', 'New York'],
                'charges' => 'Charged in the 1983 Los Macheteros Wells Fargo robbery, West Hartford, CT.', 'convicted' => null,
                'arrest' => [1985, 8, 30],
            ],
        ];

        foreach ($people as $p) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($p['name']))
                ->orWhere('name', $p['name'])
                ->first();

            $attrs = [
                'name' => $p['name'], 'first_name' => $p['first'], 'last_name' => $p['last'], 'aka' => $p['aka'] ?? null,
                'gender' => $p['gender'], 'state' => $p['state'], 'inmate_number' => $p['inmate'], 'era' => $p['era'],
                'ideologies' => ['Puerto Rican independence'], 'affiliation' => $p['affiliation'],
                'in_custody' => false, 'released' => true, 'under_review' => false, 'description' => $p['desc'],
            ];

            if ($prisoner) {
                $prisoner->fill($attrs)->save();
                $this->info("Updated: {$prisoner->name}");
            } else {
                $prisoner = Prisoner::create($attrs);
                $this->info("Created: {$prisoner->name}");
            }

            if ($prisoner->cases()->count() === 0) {
                $inst = Institution::firstOrCreate(
                    ['name' => $p['prison'][0]],
                    ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                );
                $case = $prisoner->cases()->make([
                    'institution_id' => $inst->id,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'] ?? null,
                    'sentence' => $p['sentence'] ?? null,
                ]);
                foreach (['arrest' => 'arrest_date', 'incarc' => 'incarceration_date', 'release' => 'release_date'] as $k => $field) {
                    if (! empty($p[$k][0])) {
                        $case->setPartialDate($field, ...$p[$k]);
                    }
                }
                $case->save();
                $this->line("  + case at {$inst->name}");
            }
        }

        return self::SUCCESS;
    }
}
