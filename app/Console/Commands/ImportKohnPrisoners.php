<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Bulk-imports Espionage Act and Sedition Act prisoners (WWI era) from
 * Stephen M. Kohn's "American Political Prisoners: Prosecutions Under the
 * Espionage and Sedition Acts" (Praeger, 1994), Part III "The Prisoners".
 *
 * Source data lives at database/data/kohn-prisoners.json — one record per
 * detected entry from Kohn's Part III, with the prisoner name and Kohn's
 * full biographical text.
 */
final class ImportKohnPrisoners extends Command {
    protected $signature = 'archive:import-kohn {--limit=0 : process at most N entries (0 = all)} {--dry : do not create records, just report counts}';
    protected $description = 'Bulk-import WWI Espionage/Sedition Act prisoners from Stephen M. Kohn (1994)';

    public function handle(): int {
        $path = database_path('data/kohn-prisoners.json');
        if (! is_file($path)) {
            $this->error("Source data not found at {$path}");

            return self::FAILURE;
        }
        $records = json_decode(file_get_contents($path), true);
        if (! is_array($records)) {
            $this->error('Could not parse kohn-prisoners.json');

            return self::FAILURE;
        }

        $limit = (int) $this->option('limit');
        $dry = (bool) $this->option('dry');

        if ($limit > 0) {
            $records = array_slice($records, 0, $limit);
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($records as $r) {
            $name = trim($r['name'] ?? '');
            $text = trim($r['text'] ?? '');
            if ($name === '' || $text === '') {
                continue;
            }

            if (Prisoner::where('name', $name)->exists()) {
                $skipped++;

                continue;
            }

            $data = $this->buildPayload($name, $text);

            if ($dry) {
                $created++;

                continue;
            }

            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $exit = Artisan::call('prisoner:add', ['json' => $json]);
            Artisan::output();

            if ($exit === self::SUCCESS) {
                $created++;
            } else {
                $failed++;
                $this->warn("failed: {$name}");
            }
        }

        $this->info("Done. Created={$created} Skipped(existing)={$skipped} Failed={$failed}");

        return self::SUCCESS;
    }

    private function buildPayload(string $name, string $text): array {
        [$first, $middle, $last] = $this->splitName($name);

        $description = $text.' Sourced from Stephen M. Kohn, "American Political Prisoners: Prosecutions Under the Espionage and Sedition Acts" (Praeger, 1994).';

        // Heuristic field extraction
        $state = $this->extractState($text);
        $ideologies = $this->extractIdeologies($text);
        $affiliation = $this->extractAffiliation($text);
        [$institutionName, $institutionState] = $this->extractInstitution($text);
        $sentence = $this->extractSentence($text);
        $charges = $this->buildCharges($text);
        $arrestDate = $this->extractArrestDate($text);

        $payload = [
            'name' => $name,
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'description' => $description,
            'era' => 'World War I (Espionage & Sedition Acts)',
            'ideologies' => $ideologies,
        ];

        if ($state) {
            $payload['state'] = $state;
        }
        if ($affiliation) {
            $payload['affiliation'] = $affiliation;
        }

        $case = ['charges' => $charges];
        if ($institutionName) {
            $case['institution_name'] = $institutionName;
        }
        if ($institutionState) {
            $case['institution_state'] = $institutionState;
        }
        if ($sentence) {
            $case['sentence'] = $sentence;
        }
        if ($arrestDate) {
            $case['arrest_date'] = $arrestDate;
        }

        $payload['cases'] = [$case];

        return $payload;
    }

    /**
     * @return array{0:?string,1:?string,2:?string}
     */
    private function splitName(string $name): array {
        // Strip "Jr." / "Sr." suffixes
        $name = preg_replace('/\s+(Jr\.|Sr\.|II|III)\s*$/', '', $name);
        $parts = preg_split('/\s+/', $name);
        $first = $parts[0] ?? null;
        $last = count($parts) > 1 ? end($parts) : null;
        $middle = count($parts) > 2 ? implode(' ', array_slice($parts, 1, -1)) : null;

        return [$first, $middle, $last];
    }

    private function extractState(string $text): ?string {
        $states = [
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado',
            'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho',
            'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana',
            'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
            'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada',
            'New Hampshire', 'New Jersey', 'New Mexico', 'New York',
            'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon',
            'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
            'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
            'West Virginia', 'Wisconsin', 'Wyoming', 'Puerto Rico',
        ];
        // The first state mentioned in the text is usually the prisoner's home state
        foreach (preg_split('/[,\.\s]+/', $text) as $tok) {
            // Match multi-word states ourselves below
        }
        // Order states longest-first so "New York" wins over "New" misses
        usort($states, fn ($a, $b) => strlen($b) <=> strlen($a));
        foreach ($states as $st) {
            if (preg_match('/\b'.preg_quote($st, '/').'\b/', $text)) {
                return $st;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function extractIdeologies(string $text): array {
        $tags = [];
        if (preg_match('/\bIWW\b|Industrial Workers of the World|Wobbly/i', $text)) {
            $tags[] = 'Anarcho-Syndicalism';
        }
        if (preg_match('/\banarchist\b|anarchism/i', $text)) {
            $tags[] = 'Anarchism';
        }
        if (preg_match('/\bSocialist\b|Socialist Party|socialism/i', $text)) {
            $tags[] = 'Socialism';
        }
        if (preg_match('/\bCommunist\b|Communist Party|communism/i', $text)) {
            $tags[] = 'Communism';
        }
        if (preg_match('/conscientious objector|refused.*draft|opposed.*draft|antimilitarist|anti-?war/i', $text)) {
            $tags[] = 'Anti-War';
        }
        if (preg_match('/Universal Union|Working Class Union/i', $text)) {
            $tags[] = 'Anti-War';
        }
        // Always include
        $tags[] = 'Anti-Militarism';

        return array_values(array_unique($tags));
    }

    /**
     * @return list<string>
     */
    private function extractAffiliation(string $text): array {
        $tags = [];
        if (preg_match('/\bIWW\b|Industrial Workers of the World|Wobbly/i', $text)) {
            $tags[] = 'Industrial Workers of the World (IWW)';
        }
        if (preg_match('/Socialist Party/i', $text)) {
            $tags[] = 'Socialist Party of America';
        }
        if (preg_match('/Socialist Labor Party/i', $text)) {
            $tags[] = 'Socialist Labor Party';
        }
        if (preg_match('/\bWorking Class Union\b/i', $text)) {
            $tags[] = 'Working Class Union';
        }
        if (preg_match('/Universal Union/i', $text)) {
            $tags[] = 'Universal Union';
        }
        if (preg_match('/Anarchist Red Cross|Anarchist Black Cross/i', $text)) {
            $tags[] = 'Anarchist Red Cross';
        }

        return array_values(array_unique($tags));
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function extractInstitution(string $text): array {
        if (preg_match('/Leavenworth Penitentiary|United States Penitentiary at Leavenworth|U\.?S\.?P\.? Leavenworth/i', $text)) {
            return ['USP Leavenworth', 'Kansas'];
        }
        if (preg_match('/Atlanta Penitentiary|United States Penitentiary at Atlanta/i', $text)) {
            return ['USP Atlanta', 'Georgia'];
        }
        if (preg_match('/McNeil Island/i', $text)) {
            return ['USP McNeil Island', 'Washington'];
        }
        if (preg_match('/San Quentin/i', $text)) {
            return ['San Quentin State Prison', 'California'];
        }
        if (preg_match('/Folsom Prison/i', $text)) {
            return ['Folsom State Prison', 'California'];
        }
        if (preg_match('/Moundsville/i', $text)) {
            return ['West Virginia State Penitentiary at Moundsville', 'West Virginia'];
        }
        if (preg_match('/Walla Walla/i', $text)) {
            return ['Washington State Penitentiary at Walla Walla', 'Washington'];
        }
        if (preg_match('/Missouri State Penitentiary/i', $text)) {
            return ['Missouri State Penitentiary', 'Missouri'];
        }
        if (preg_match('/Fort Leavenworth/i', $text)) {
            return ['Fort Leavenworth Military Prison', 'Kansas'];
        }
        if (preg_match('/Alcatraz/i', $text)) {
            return ['Alcatraz Federal Penitentiary', 'California'];
        }

        return [null, null];
    }

    private function extractSentence(string $text): ?string {
        // Look for "sentenced to N years"
        if (preg_match('/sentenced to ([a-z\-\s]+? years?)( in (?:prison|jail|the penitentiary))?/i', $text, $m)) {
            $sentence = ucfirst(trim($m[1]));
            // Trim noise
            $sentence = preg_replace('/\s+/', ' ', $sentence);

            return $sentence.' in prison (Espionage/Sedition Act conviction).';
        }
        if (preg_match('/received a prison sentence of (.+?)[\.,;]/i', $text, $m)) {
            return ucfirst(trim($m[1])).'.';
        }
        if (preg_match('/began serving a prison sentence/i', $text)) {
            return 'Prison sentence (length not specified in source).';
        }

        return null;
    }

    private function buildCharges(string $text): string {
        $base = 'Federal prosecution under the Espionage Act of 1917 and/or the Sedition Act of 1918';
        if (preg_match('/mass IWW trial in (Chicago|Sacramento|Wichita|Omaha)/i', $text, $m)) {
            $base .= ' (mass IWW trial in '.$m[1].')';
        }

        return $base.'.';
    }

    private function extractArrestDate(string $text): ?string {
        // Many entries say "arrested in [Month] [Year]" or just "in [Year]" referring to a known WWI period
        if (preg_match('/arrested(?:[^.]{0,60}?)(January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2})?\s*,?\s*(191[7-9]|192[0-4])/i', $text, $m)) {
            $month = date('m', strtotime($m[1]));
            $day = ! empty($m[2]) ? str_pad($m[2], 2, '0', STR_PAD_LEFT) : '01';
            $year = $m[3];

            return "$year-$month-$day";
        }
        if (preg_match('/arrested in (191[7-9]|192[0-4])/i', $text, $m)) {
            return $m[1].'-01-01';
        }
        if (preg_match('/began serving (?:a prison sentence|his sentence) on (January|February|March|April|May|June|July|August|September|October|November|December)\s+(\d{1,2})?,?\s*(191[7-9]|192[0-4])/i', $text, $m)) {
            $month = date('m', strtotime($m[1]));
            $day = ! empty($m[2]) ? str_pad($m[2], 2, '0', STR_PAD_LEFT) : '01';

            return "{$m[3]}-{$month}-{$day}";
        }

        return null;
    }
}
