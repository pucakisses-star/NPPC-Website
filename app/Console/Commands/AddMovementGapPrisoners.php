<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Adds the genuine gaps surfaced by a cross-movement sweep of the Chicano
 * Movement, Puerto Rican independentistas beyond the Macheteros, and the
 * Cuba-exile/hijacker wave. Most of those categories were already complete:
 *
 *  - Puerto Rican: the entire Nationalist Party (Lebrón, Cancel Miranda,
 *    Flores, Figueroa Cordero, Collazo, Albizu Campos), nearly the whole
 *    FALN clemency cohort, Haydée Beltrán Torres, and the Macheteros are all
 *    already in — no additions needed.
 *  - Cuba-hijacker wave: a dead end. Tony Bryant became an anti-Castro
 *    right-wing militant and Ishmael LaBeet was the Fountain Valley mass
 *    murderer; neither fits. The clean cases (Brent, the RNA trio, Assata,
 *    Nehanda Abiodun) are already present.
 *
 * The real gaps:
 *  - Mabel Williams — civil-rights organizer exiled to Cuba and China with
 *    Robert F. Williams (Radio Free Dixie); returned 1969; died 2014.
 *  - Francisco "Kiko" Martínez — Chicano-movement attorney, protracted
 *    federal bombing prosecution that collapsed amid judicial misconduct.
 *  - Los Tres del Barrio (Rodolfo Sánchez, Juan Fernández, Alberto Ortiz) —
 *    La Casa de Carnalismo anti-drug activists convicted of shooting an
 *    undercover narcotics agent; the "Free Los Tres" cause.
 *  - Los Siete de la Raza (Gary Lescallett, Tony Martínez, Mario Martínez,
 *    Nelson Rodríguez, Danilo Melendez) — jailed over the 1969 killing of
 *    SFPD officer Joseph Brodnik, all acquitted in 1970. (José Ríos is
 *    already in the DB.)
 *
 * Idempotent: prisoner:add refuses duplicate names.
 */
final class AddMovementGapPrisoners extends Command {
    protected $signature = 'prisoners:add-movement-gaps';
    protected $description = 'Add the Chicano / Cuba-exile gaps from the cross-movement sweep';

    public function handle(): int {
        $losSiete = function (string $name, string $first, string $last, string $aka = ''): array {
            $e = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'description' => $name.' was one of Los Siete de la Raza, a group of young Latino men from the Mission District of San Francisco charged in the May 1, 1969 death of plainclothes police officer Joseph Brodnik. Brodnik and his partner had confronted the group over a suspected burglary when a struggle broke out and Brodnik was fatally shot; the defense argued the fatal shot came from the gun of his own partner during the scuffle. '.$first.' '.$last.' was arrested and jailed pending trial, and the case became a major cause of the Bay Area Chicano and Latino movement, which built a defense committee, the newspaper Basta Ya, and community survival programs modeled on the Black Panthers. After a lengthy trial, the defendants were acquitted in 1970.',
                'race' => 'Latino',
                'gender' => 'Male',
                'state' => 'California',
                'ideologies' => ['Chicano liberation', 'La Raza'],
                'affiliation' => ['Los Siete de la Raza'],
                'era' => '1960s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Murder of San Francisco police officer Joseph Brodnik (1969)',
                    'arrest_date' => '1969-05-01',
                    'convicted' => 'Acquitted at trial in 1970',
                    'sentence' => 'Jailed roughly eighteen months pending trial before acquittal',
                ]],
            ];
            if ($aka !== '') {
                $e['aka'] = $aka;
            }

            return $e;
        };

        $losTres = function (string $name, string $first, string $last, string $years): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'description' => $name.' was a member of La Casa de Carnalismo, a Chicano community organization in East Los Angeles that worked to drive heroin dealers out of the barrio. In 1971 '.$first.' '.$last.', with two others known together as Los Tres del Barrio, shot and robbed a man at the Estrada Courts housing project who proved to be an undercover federal narcotics agent; the agent was left paralyzed. Charged in federal court, the three were convicted and given sentences of forty, twenty-five, and ten years. The Committee to Free Los Tres made them a major cause of the Chicano movement, which argued they had been entrapped and targeted for their anti-drug community organizing.',
                'race' => 'Latino',
                'gender' => 'Male',
                'state' => 'California',
                'ideologies' => ['Chicano liberation', 'Community self-defense'],
                'affiliation' => ['La Casa de Carnalismo', 'Los Tres del Barrio'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Shooting and robbery of an undercover federal narcotics agent at Estrada Courts, Los Angeles (1971)',
                    'convicted' => 'Convicted in federal court; supporters maintained the three were entrapped and targeted for anti-drug community organizing',
                    'sentence' => $years.' in federal prison',
                ]],
            ];
        };

        $prisoners = [
            [
                'name' => 'Mabel Williams',
                'first_name' => 'Mabel',
                'last_name' => 'Williams',
                'aka' => 'Mabel Robinson Williams',
                'description' => 'Mabel Williams (1931-2014) was a civil-rights organizer who, with her husband Robert F. Williams, championed armed Black self-defense against white-supremacist violence in Monroe, North Carolina. After the couple sheltered a white couple during racial unrest in 1961, the FBI issued kidnapping warrants and the Williams family fled the United States. Granted political asylum in Cuba, Mabel and Robert broadcast Radio Free Dixie from Havana — beaming news, music, and commentary to Black listeners across the American South — and Mabel helped edit their newsletter The Crusader. The family later lived in China before returning to the United States in 1969. An organizer and movement worker in her own right, she continued civil-rights and community work until her death in 2014.',
                'race' => 'Black',
                'gender' => 'Female',
                'state' => 'North Carolina',
                'ideologies' => ['Black liberation', 'Armed self-defense', 'Civil rights'],
                'era' => '1960s',
                'in_custody' => false,
                'released' => true,
                'in_exile' => true,
                'currently_in_exile' => false,
                'imprisoned_or_exiled' => true,
                'death_date' => '2014-04-19',
                'cases' => [[
                    'charges' => 'Fled the United States with Robert F. Williams after FBI kidnapping warrants stemming from their armed-self-defense organizing in Monroe, North Carolina',
                    'convicted' => 'Never tried; lived in political exile in Cuba and then China, 1961-1969',
                    'in_exile_since' => '1961-08-27',
                ]],
            ],
            [
                'name' => 'Francisco Kiko Martinez',
                'first_name' => 'Francisco',
                'last_name' => 'Martinez',
                'aka' => 'Kiko Martinez',
                'description' => 'Francisco Kiko Martinez was a Chicano-movement attorney from southern Colorado who became the target of one of the most protracted federal prosecutions of the era. In 1973 he was indicted on charges of mailing package bombs in Denver; fearing for his life, he fled to Mexico and lived underground for seven years. Captured at the Nogales, Arizona border crossing in 1980 under an assumed name, he was prosecuted across a series of trials in the early 1980s. Most charges were dismissed for insufficient evidence or because police had lost key evidence, and juries acquitted him of others; his first trial collapsed into a mistrial after it emerged that the federal judge had secretly met with prosecutors and government witnesses. Martinez was ultimately cleared of the bombing charges in 1983, though he served time on a related document conviction. His case became a touchstone of Chicano-movement complaints of political repression, and he resumed practicing law after his release.',
                'race' => 'Latino',
                'gender' => 'Male',
                'state' => 'Colorado',
                'ideologies' => ['Chicano liberation', 'La Raza'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Mailing package bombs (1973 indictment); passport and document offenses',
                    'convicted' => 'Cleared of the bombing charges by 1983 (amid findings of judicial misconduct); convicted on a document offense',
                    'sentence' => 'Served time on the document conviction; acquitted or charges dismissed on the bombing counts',
                ]],
            ],
            $losTres('Rodolfo Sanchez', 'Rodolfo', 'Sanchez', 'Forty years'),
            $losTres('Juan Fernandez', 'Juan', 'Fernandez', 'Twenty-five years'),
            $losTres('Alberto Ortiz', 'Alberto', 'Ortiz', 'Ten years'),
            $losSiete('Gary Lescallett', 'Gary', 'Lescallett'),
            $losSiete('Tony Martinez', 'Tony', 'Martinez', 'Rodolfo Antonio Martinez'),
            $losSiete('Mario Martinez', 'Mario', 'Martinez'),
            $losSiete('Nelson Rodriguez', 'Nelson', 'Rodriguez'),
            $losSiete('Danilo Melendez', 'Danilo', 'Melendez'),
        ];

        $added = 0;
        $skipped = 0;
        foreach ($prisoners as $p) {
            $this->line("\n— {$p['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone — added {$added}, skipped {$skipped} (already present).");

        return self::SUCCESS;
    }
}
