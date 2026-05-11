<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Compares the 28 U.S. prisoners shared by prisonersolidarity.com and the
 * NPPC database, reports gaps (missing photo / institution / address /
 * birthday / dates / status), and optionally fills them in.
 *
 * Source: database/data/prisoner-solidarity-data.json
 */
final class CrossCheckPrisonerSolidarity extends Command {
    protected $signature = 'archive:crosscheck-ps {--apply : write missing values back to the DB} {--name= : only process this one NPPC name}';
    protected $description = 'Compare NPPC records against prisonersolidarity.com data; report (and optionally fix) gaps';

    public function handle(): int {
        $path = database_path('data/prisoner-solidarity-data.json');
        if (! is_file($path)) {
            $this->error("Source data not found at {$path}");

            return self::FAILURE;
        }
        $records = json_decode(file_get_contents($path), true);
        if (! is_array($records)) {
            $this->error('Could not parse JSON.');

            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');
        $onlyName = $this->option('name');

        $totalGaps = 0;
        $filled = 0;

        foreach ($records as $r) {
            $nppcName = $r['nppc_name'] ?? null;
            if (! $nppcName) {
                continue;
            }
            if ($onlyName && $onlyName !== $nppcName) {
                continue;
            }

            $prisoner = Prisoner::where('name', $nppcName)->first();
            if (! $prisoner) {
                $this->warn("not in NPPC: {$nppcName}");

                continue;
            }

            $gaps = $this->findGaps($prisoner, $r);
            if (! $gaps) {
                continue;
            }

            $this->line("\n→ {$prisoner->name} (https://prisonersolidarity.com/prisoner/{$r['ps_slug']})");
            foreach ($gaps as $field => $value) {
                $this->line(sprintf('   gap: %-22s = %s', $field, $this->truncate($value)));
                $totalGaps++;
            }

            if ($apply) {
                $filled += $this->applyGaps($prisoner, $r, $gaps);
            }
        }

        $verb = $apply ? 'filled' : 'would fill';
        $this->info("\nTotal gaps: {$totalGaps}");
        if ($apply) {
            $this->info("Filled: {$filled}");
        } else {
            $this->info("(rerun with --apply to {$verb} these fields)");
        }

        return self::SUCCESS;
    }

    /**
     * @return array<string,string>
     */
    private function findGaps(Prisoner $p, array $r): array {
        $gaps = [];

        if (empty($p->photo) && ! empty($r['picture'])) {
            $gaps['photo'] = $r['picture'];
        }
        if (empty($p->birthdate) && ! empty($r['birthday'])) {
            $gaps['birthdate'] = $r['birthday'];
        }
        if (empty($p->address) && ! empty($r['address'])) {
            $gaps['address'] = $this->formatAddress($r['address']);
        }
        if (empty($p->state) && ! empty($r['address']['state'])) {
            $gaps['state'] = $this->expandStateAbbrev($r['address']['state']);
        }

        // Status flags
        $isImprisoned = ($r['status'] ?? '') === 'Imprisoned';
        $isReleased = ($r['status'] ?? '') === 'Released';
        if ($isImprisoned && ! $p->in_custody) {
            $gaps['in_custody'] = 'true (PS says Imprisoned)';
        }
        if ($isReleased && ! $p->released) {
            $gaps['released'] = 'true (PS says Released)';
        }

        // First-case gaps
        $case = $p->cases->first();
        if ($case) {
            if (empty($case->institution_id) && ! empty($r['prison'])) {
                $gaps['institution'] = $r['prison'];
            }
            if (empty($case->release_date) && ! empty($r['projected_release'])) {
                $gaps['projected_release'] = $r['projected_release'].' (projected)';
            }
        }

        return $gaps;
    }

    private function applyGaps(Prisoner $p, array $r, array $gaps): int {
        $count = 0;

        if (isset($gaps['photo'])) {
            // Don't try to download — just log; admin can fetch.
        }
        if (isset($gaps['birthdate']) && ! empty($r['birthday'])) {
            $p->birthdate = substr($r['birthday'], 0, 10);
            $count++;
        }
        if (isset($gaps['address'])) {
            $p->address = $this->formatAddress($r['address']);
            $count++;
        }
        if (isset($gaps['state']) && ! empty($r['address']['state'])) {
            $p->state = $this->expandStateAbbrev($r['address']['state']);
            $count++;
        }
        if (isset($gaps['in_custody'])) {
            $p->in_custody = true;
            $count++;
        }
        if (isset($gaps['released'])) {
            $p->released = true;
            $count++;
        }
        if ($count > 0) {
            $p->save();
        }

        $case = $p->cases->first();
        if ($case) {
            if (isset($gaps['institution']) && ! empty($r['prison'])) {
                $inst = Institution::firstOrCreate(['name' => $r['prison']]);
                $case->institution_id = $inst->id;
                $count++;
            }
            if (isset($gaps['projected_release']) && ! empty($r['projected_release'])) {
                // Store only if there's no current value; PS's projected is approximate
                $case->release_date = substr($r['projected_release'], 0, 10);
                $count++;
            }
            $case->save();
        }

        return $count;
    }

    private function formatAddress(array $a): string {
        $parts = [];
        if (! empty($a['name_line'])) {
            $parts[] = $a['name_line'];
        }
        $cityState = trim(($a['city'] ?? '').(! empty($a['state']) ? ', '.$a['state'] : ''));
        if ($cityState) {
            $parts[] = $cityState.(! empty($a['zip']) ? ' '.$a['zip'] : '');
        }
        if (! empty($a['country'])) {
            $parts[] = $a['country'];
        }

        return implode("\n", $parts);
    }

    private function expandStateAbbrev(string $abbrev): string {
        $map = [
            'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
            'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
            'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
            'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
            'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
            'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
            'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
            'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
            'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
            'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
            'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
            'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'PR' => 'Puerto Rico', 'VI' => 'U.S. Virgin Islands',
        ];

        return $map[strtoupper($abbrev)] ?? $abbrev;
    }

    private function truncate(string $s, int $n = 80): string {
        $s = preg_replace('/\s+/', ' ', $s);

        return mb_strlen($s) > $n ? mb_substr($s, 0, $n).'…' : $s;
    }
}
