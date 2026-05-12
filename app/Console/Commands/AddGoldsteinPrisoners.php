<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Imports 174 US political prisoners surfaced from Robert Justin
 * Goldstein, "Political Repression in Modern America From 1870 to 1976"
 * (Schenkman, 1978). Spans the Haymarket Eight (1886) through the
 * Brink's-era anti-imperialists of the 1980s — every major US political
 * prosecution and movement-defense case the book documents.
 *
 * Clusters covered:
 *   - Haymarket Eight (1886)
 *   - Anarchist Exclusion Act / pre-WWI (Turner, Goldman, Berkman,
 *     Magón brothers, Marcus Garvey, Ruthenberg, Whitney)
 *   - Scottsboro Nine (1931); Herndon, DeJonge (1930s landmark cases)
 *   - Minneapolis Smith Act 1941 / SWP 18
 *   - Sleepy Lagoon defendants (1942)
 *   - Hollywood Ten (1947)
 *   - Japanese-American internment test cases (Korematsu, Hirabayashi,
 *     Yasui, Endo)
 *   - Earl Browder
 *   - Rosenbergs + Sobell, Gold, Greenglass
 *   - Cold War perjury (Hiss, Coplon, Remington, Lattimore)
 *   - Steve Nelson
 *   - Puerto Rican Nationalists (Albizu Campos, Collazo, Torresola,
 *     Lebrón, Cancel Miranda, Figueroa Cordero, Flores)
 *   - Boston Five / Spock case (1968)
 *   - Catonsville Nine (1968)
 *   - Chicago Seven/Eight (1969–70)
 *   - Camden 28; Harrisburg Seven; Gainesville Eight
 *   - Pentagon Papers (Ellsberg, Russo)
 *   - Black Panther Party individual cases (Newton, Cleaver, Hilliard,
 *     Pratt, Hampton, Clark, Bunchy Carter, Panther 21 named leads)
 *   - Angela Davis
 *   - Soledad Brothers / San Quentin Six
 *   - American Indian Movement / Wounded Knee
 *   - Republic of New Afrika (RNA-11)
 *   - Weather Underground / May 19th Communist Org / Resistance
 *     Conspiracy / Brink's defendants
 *   - Wilmington 10
 *   - Sterling Hall / New Year's Gang
 *   - SLA / Patty Hearst case
 *   - Reies López Tijerina
 *
 * Idempotent — prisoner:add refuses duplicates by name.
 */
final class AddGoldsteinPrisoners extends Command {
    protected $signature = 'archive:add-goldstein-prisoners';
    protected $description = 'Add 174 political prisoners surfaced from Goldstein, Political Repression in Modern America (1978)';

    public function handle(): int {
        $path = database_path('data/goldstein-prisoners.json');
        if (! is_file($path)) {
            $this->error("Data file not found: {$path}");

            return self::FAILURE;
        }

        $payloads = json_decode(file_get_contents($path), true);
        if (! is_array($payloads)) {
            $this->error('Could not parse JSON.');

            return self::FAILURE;
        }

        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped} (skipped = duplicates by name)");

        return self::SUCCESS;
    }
}
