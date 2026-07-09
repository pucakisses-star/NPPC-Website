<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The 1901 Guam deportees — Filipino revolutionary leaders and officials whom
 * U.S. Military Governor Gen. Arthur MacArthur Jr. ordered deported to Guam
 * during the Philippine–American War for refusing to swear allegiance to the
 * United States (remembered as the "pioneer Filipino political prisoners" held
 * by the U.S. on Guam). Includes the main January-1901 group and the eleven
 * additional Ilocos Norte men sent later aboard the U.S.S. Solace and listed in
 * the U.S. War Department report. Held by the United States, so in scope as
 * U.S. political prisoners.
 *
 * Create-or-update matched by name + era 1900s (or a Guam/MacArthur marker in
 * the description) so it won't clobber an unrelated person of the same name.
 * Rebuilds the single Guam case (matched by institution). Idempotent.
 */
final class AddGuamDeportees1901 extends Command
{
    protected $signature = 'prisoners:add-guam-deportees-1901';

    protected $description = 'Add the 1901 Guam deportees (Filipino political prisoners deported by the U.S.)';

    public function handle(): int
    {
        $shared = ' was among the Filipino revolutionary leaders and officials whom U.S. Military Governor Gen. Arthur MacArthur Jr. ordered deported to Guam in 1901, during the Philippine–American War, for refusing to swear allegiance to the United States — remembered among the "pioneer Filipino political prisoners" the U.S. held on Guam. Most were released to return home after taking the oath of allegiance following the war (1902–1903).';
        $sharedSolace = ' was one of eleven additional men from Ilocos Norte deported to Guam aboard the U.S.S. Solace and listed in the U.S. War Department report among the Filipino political prisoners deported by U.S. Military Governor Gen. Arthur MacArthur Jr. during the Philippine–American War for refusing allegiance to the United States. They were freed to return home after the war (1902–1903).';

        // The main January-1901 deportee group.
        $group = [
            ['name' => 'Apolinario Mabini', 'first' => 'Apolinario', 'last' => 'Mabini',
             'birth' => '1864-07-23', 'death' => '1903-05-13', 'release' => '1903-02',
             'lead' => 'Apolinario Mabini (1864–1903), the paralyzed chief adviser and first head of government of the First Philippine Republic, revered as the "Sublime Paralytic,"',
             'extra' => ' After nearly two years he took the oath of allegiance and returned to Manila in February 1903, only to die of cholera three months later.'],
            ['name' => 'Artemio Ricarte', 'first' => 'Artemio', 'last' => 'Ricarte', 'aka' => 'El Vibora',
             'birth' => '1866-10-20', 'death' => '1945-07-31', 'release' => '1903',
             'lead' => 'Artemio Ricarte (1866–1945), the revolutionary general known as "El Vibora" (The Viper) and the first captain-general of the Philippine Army,',
             'extra' => ' Alone among the deportees he refused to swear allegiance even after the war; expelled from Guam, he spent decades in exile in Hong Kong and Japan.',
             'sentence' => 'Exiled to Guam; refused the oath of allegiance and was expelled into further exile (Hong Kong, then Japan).'],
            ['name' => 'Pío del Pilar', 'first' => 'Pío', 'last' => 'del Pilar', 'birth' => '1860', 'death' => '1931',
             'lead' => 'Pío del Pilar (1860–1931), a Philippine revolutionary general from Makati,'],
            ['name' => 'Maximino Hizon', 'first' => 'Maximino', 'last' => 'Hizon', 'aka' => 'Maximo Hizon', 'birth' => '1869', 'death' => '1901',
             'diedGuam' => true,
             'lead' => 'Maximino Hizon (1869–1901), a revolutionary general from Pampanga,',
             'sentence' => 'Deported to Guam, where he died in exile in 1901.'],
            ['name' => 'Mariano Llanera', 'first' => 'Mariano', 'last' => 'Llanera', 'birth' => '1855', 'death' => '1942',
             'lead' => 'Mariano Llanera (1855–1942), a revolutionary general from Nueva Ecija,'],
            ['name' => 'Francisco de los Santos', 'first' => 'Francisco', 'last' => 'de los Santos'],
            ['name' => 'Macario de Ocampo', 'first' => 'Macario', 'last' => 'de Ocampo'],
            ['name' => 'Lucas Camerino', 'first' => 'Lucas', 'last' => 'Camerino'],
            ['name' => 'Julián Gerona', 'first' => 'Julián', 'last' => 'Gerona',
             'lead' => 'Julián Gerona, a Philippine revolutionary officer,'],
            ['name' => 'Pedro Cubarrubias', 'first' => 'Pedro', 'last' => 'Cubarrubias', 'aka' => 'Pedro Cobarrubias'],
            ['name' => 'Mariano Barruga', 'first' => 'Mariano', 'last' => 'Barruga', 'aka' => 'Mariano Barroga'],
            ['name' => 'Hermogenes Plata', 'first' => 'Hermogenes', 'last' => 'Plata'],
            ['name' => 'Cornelio Riquiestas', 'first' => 'Cornelio', 'last' => 'Riquiestas', 'aka' => 'Cornelio Requestis; Cornelio Renuestis'],
            ['name' => 'Juan Villarino', 'first' => 'Juan', 'last' => 'Villarino', 'aka' => 'Juan Leandro Villarino'],
            ['name' => 'Alipio Tecson', 'first' => 'Alipio', 'last' => 'Tecson',
             'lead' => 'Alipio Tecson, a revolutionary officer from Bulacan,'],
            ['name' => 'Anastacio Carmona', 'first' => 'Anastacio', 'last' => 'Carmona'],
            ['name' => 'Pablo Ocampo', 'first' => 'Pablo', 'last' => 'Ocampo', 'birth' => '1853', 'death' => '1925',
             'lead' => 'Pablo Ocampo (1853–1925), a lawyer and revolutionary official who would later become the first Filipino Resident Commissioner to the U.S. Congress,'],
            ['name' => 'Simón Tecson', 'first' => 'Simón', 'last' => 'Tecson',
             'lead' => 'Simón Tecson, a revolutionary general from Bulacan,'],
            ['name' => 'Lucino Almeida', 'first' => 'Lucino', 'last' => 'Almeida'],
            ['name' => 'Pío Varican', 'first' => 'Pío', 'last' => 'Varican', 'aka' => 'Pío Barican'],
            ['name' => 'Fabián Villaruel', 'first' => 'Fabián', 'last' => 'Villaruel'],
            ['name' => 'Mariano Trías', 'first' => 'Mariano', 'last' => 'Trías', 'birth' => '1868', 'death' => '1914',
             'lead' => 'Mariano Trías (1868–1914), a revolutionary general and the first Vice President of the Philippine Republic,'],
            ['name' => 'Norberto Dimayuga', 'first' => 'Norberto', 'last' => 'Dimayuga',
             'lead' => 'Norberto Dimayuga, a revolutionary general from Batangas,'],
            ['name' => 'Antonio Reyes', 'first' => 'Antonio', 'last' => 'Reyes', 'aka' => 'Antonio Prisco Reyes'],
            ['name' => 'José Florante', 'first' => 'José', 'last' => 'Florante'],
            ['name' => 'José Buenaventura', 'first' => 'José', 'last' => 'Buenaventura'],
            ['name' => 'Doroteo Espino', 'first' => 'Doroteo', 'last' => 'Espino', 'aka' => 'Doroteo Espina'],
            ['name' => 'Juan Mauricio', 'first' => 'Juan', 'last' => 'Mauricio'],
            // Additional main-group names from the corrected War Department roster.
            ['name' => 'Esteban Consortes', 'first' => 'Esteban', 'last' => 'Consortes'],
            ['name' => 'José Mata', 'first' => 'José', 'last' => 'Mata'],
            ['name' => 'Igmidio de Jesus', 'first' => 'Igmidio', 'last' => 'de Jesus', 'aka' => 'Ygmidio de Jesus'],
            ['name' => 'Silvestre Legaspi', 'first' => 'Silvestre', 'last' => 'Legaspi'],
            ['name' => 'Bartolome de la Rosa', 'first' => 'Bartolome', 'last' => 'de la Rosa'],
            ['name' => 'Maximino Trías', 'first' => 'Maximino', 'last' => 'Trías'],
        ];

        // The eleven additional Ilocos Norte men sent later on the U.S.S. Solace.
        $solace = [
            ['name' => 'Roberto Salvante', 'first' => 'Roberto', 'last' => 'Salvante'],
            ['name' => 'Marcelo Quintas', 'first' => 'Marcelo', 'last' => 'Quintas', 'aka' => 'Marcelo Quintos'],
            ['name' => 'Pancracio Palting', 'first' => 'Pancracio', 'last' => 'Palting'],
            ['name' => 'Jayme Morales', 'first' => 'Jayme', 'last' => 'Morales', 'aka' => 'Jaime Morales'],
            ['name' => 'Gavino Domingo', 'first' => 'Gavino', 'last' => 'Domingo', 'aka' => 'Gabino Domingo'],
            ['name' => 'Leon Flores', 'first' => 'Leon', 'last' => 'Flores'],
            ['name' => 'Florencio Castro', 'first' => 'Florencio', 'last' => 'Castro'],
            ['name' => 'Pedro Erando', 'first' => 'Pedro', 'last' => 'Erando', 'aka' => 'Pedro Hernando'],
            ['name' => 'Inocente Cayetano', 'first' => 'Inocente', 'last' => 'Cayetano'],
            ['name' => 'Pancracio Adiarte', 'first' => 'Pancracio', 'last' => 'Adiarte', 'aka' => 'Pancrasio Adiarte'],
            ['name' => 'Faustino Adiarte', 'first' => 'Faustino', 'last' => 'Adiarte'],
        ];

        $chargesMain = 'Deported to Guam by order of U.S. Military Governor Gen. Arthur MacArthur Jr. (1901) for refusing to swear allegiance to the United States during the Philippine–American War.';
        $chargesSolace = 'Deported to Guam aboard the U.S.S. Solace (from Ilocos Norte) and listed in the U.S. War Department report among the Filipino political prisoners deported during the Philippine–American War.';

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($group, $solace, $shared, $sharedSolace, $chargesMain, $chargesSolace, &$added, &$updated) {
            $inst = Institution::firstOrCreate(
                ['name' => 'Deportation camp, Guam'],
                ['city' => 'Asan', 'state' => 'Guam'],
            );

            $run = function (array $rows, string $sharedText, string $charges) use ($inst, &$added, &$updated) {
                foreach ($rows as $p) {
                    $died = ! empty($p['diedGuam']);
                    $desc = ($p['lead'] ?? $p['name']).$sharedText.($p['extra'] ?? '');

                    $existing = Prisoner::withUnderReview()
                        ->where('name', $p['name'])
                        ->get()
                        ->first(fn ($x) => $x->era === '1900s'
                            || str_contains((string) $x->description, 'Guam')
                            || str_contains((string) $x->description, 'MacArthur'));
                    $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                    $prisoner->fill([
                        'name' => $p['name'],
                        'first_name' => $p['first'],
                        'last_name' => $p['last'],
                        'aka' => $p['aka'] ?? null,
                        'gender' => 'Male',
                        'race' => 'Asian',
                        'state' => 'Philippines',
                        'era' => '1900s',
                        'ideologies' => ['Philippine independence', 'Anti-colonial'],
                        'affiliation' => ['Philippine Revolution', 'Guam deportees (1901)'],
                        'description' => $desc,
                        'in_custody' => false,
                        'released' => ! $died,
                        'in_exile' => false,
                        'awaiting_trial' => false,
                    ]);
                    $this->setDate($prisoner, 'birthdate', $p['birth'] ?? null);
                    $this->setDate($prisoner, 'death_date', $p['death'] ?? null);
                    $prisoner->save();

                    $case = $prisoner->cases()->where('institution_id', $inst->id)->first()
                        ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                    $case->prisoner_id = $prisoner->id;
                    $case->institution_id = $inst->id;
                    $case->charges = $charges;
                    $case->convicted = 'No — deported and held without trial as a political prisoner.';
                    $case->sentence = $p['sentence'] ?? 'Exile and imprisonment on Guam; released to return home after the war (1902–1903).';
                    $this->setDate($case, 'incarceration_date', '1901');
                    if ($died) {
                        $this->setDate($case, 'death_in_custody_date', $p['death'] ?? null);
                    } else {
                        $this->setDate($case, 'release_date', $p['release'] ?? '1903');
                    }
                    $case->save();

                    if ($existing) {
                        $updated++;
                        $this->line('  updated: '.$p['name']);
                    } else {
                        $added++;
                        $this->info('  added: '.$p['name'].' (slug: '.$prisoner->slug.')');
                    }
                }
            };

            $run($group, $shared, $chargesMain);
            $run($solace, $sharedSolace, $chargesSolace);
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("\n1901 Guam deportees — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }

    /** Set a partial date from a "YYYY", "YYYY-MM", or "YYYY-MM-DD" string. */
    private function setDate($model, string $field, ?string $value): void
    {
        if (! $value) {
            return;
        }
        $parts = array_map('intval', explode('-', $value));
        $model->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
    }
}
