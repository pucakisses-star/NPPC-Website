<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Imports prisoners from a curated subset of The Intercept's "Trial and
 * Terror" dataset (Trevor Aaronson and Margot Williams), licensed under
 * Creative Commons BY-NC. Source data:
 *   https://github.com/theintercept/trial-and-terror-data
 *
 * The bundled JSON file (database/data/trial_terror_candidates.json)
 * is a hand-curated subset of the 950-entry dataset, narrowed to people
 * whose prosecution targeted them for pre-existing political/religious
 * activities or beliefs — speech, publishing, organizing, charity work,
 * professional roles, or diaspora solidarity. Specifically excluded:
 *
 *   - Cases where the defendant actively pursued violence (the FBI
 *     just provided the means)
 *   - People who actually traveled abroad to fight or train
 *   - Operational al-Qaeda / ISIS / Shabab figures
 *   - Random sting victims with no prior political involvement
 *   - Arms-trade and drug-cartel cases swept into terror enforcement
 *
 * Categories of who's IN: Tamil-Tigers diaspora support cases,
 * FARC-solidarity defendants from Operation White Terror, Bosnian-
 * immigrant fundraisers, the Holy Land Foundation cluster's lesser-
 * known co-defendants, the Hafiz Khan South Florida imam family, and
 * professional/speech/translation cases (Hassoun, Jayyousi, Chandia,
 * Al-Timimi, Abu-Jihaad, Malki, Muhtorov, Niazi, Al-Wahaidy, etc.).
 */
class AddTrialTerrorPrisoners extends Command
{
    protected $signature = 'prisoners:add-trial-terror {--dry-run : Print what would be added without writing}';

    protected $description = "Import prisoners from The Intercept's Trial and Terror dataset (curated for political-prosecution profile).";

    public function handle(): int
    {
        $path = database_path('data/trial_terror_candidates.json');

        if (! file_exists($path)) {
            $this->error("Data file not found at {$path}");
            return self::FAILURE;
        }

        $records = json_decode(file_get_contents($path), true);
        if (! is_array($records)) {
            $this->error('Failed to decode JSON');
            return self::FAILURE;
        }

        $this->info("Records to process: ".count($records));

        $created = 0;
        $skippedExisting = 0;
        $errors = 0;

        foreach ($records as $r) {
            try {
                $name = trim($r['name']);
                if (! $name) {
                    continue;
                }

                if (Prisoner::where('name', $name)->exists()) {
                    $skippedExisting++;
                    continue;
                }

                if ($this->option('dry-run')) {
                    $this->line("  would add: {$name} ({$r['years']}y)");
                    $created++;
                    continue;
                }

                DB::transaction(function () use ($r, &$created) {
                    $prisoner = $this->createPrisoner($r);
                    $this->createCase($prisoner, $r);
                    $created++;
                });
            } catch (\Throwable $e) {
                $this->error("  failed: {$r['name']} — {$e->getMessage()}");
                $errors++;
            }
        }

        $this->line('');
        $this->info("Done.");
        $this->line("  created:                   {$created}");
        $this->line("  skipped (already in DB):   {$skippedExisting}");
        $this->line("  errors:                    {$errors}");

        return self::SUCCESS;
    }

    private function createPrisoner(array $r): Prisoner
    {
        $era = $this->eraFromSentenceDate($r['sentence_date'] ?? '');
        $tags = $this->buildTags($r);

        $bio = $this->composeBio($r);

        // Most Trial-and-Terror defendants were prosecuted under
        // post-9/11 material-support / conspiracy statutes targeting
        // perceived support for designated foreign organizations.
        // Use a generic ideology label since the dataset doesn't
        // record self-described political identity.
        $ideologies = ['Targeted by post-9/11 terrorism prosecution'];

        // Set status: released by default unless we know they're still in.
        // The Intercept's `released` field tracks BOP custody status as
        // of the dataset's last update; the user can refine in admin.
        $released = (bool) ($r['released'] ?? false);

        return Prisoner::create(array_filter([
            'name'         => $r['name'],
            'first_name'   => $r['first_name'] ?: null,
            'last_name'    => $r['last_name'] ?: null,
            'description'  => $bio,
            'gender'       => $r['gender'] ?: null,
            'race'         => $this->normalizeRace($r['race'] ?? '') ?: null,
            'state'        => $r['state'] ?: null,
            'era'          => $era,
            'ideologies'   => $ideologies,
            'in_custody'   => ! $released,
            'released'     => $released,
            'awaiting_trial' => false,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    private function createCase(Prisoner $prisoner, array $r): void
    {
        $institution = $this->resolveInstitution($r);

        $charges = array_filter($r['charges'] ?? []);
        $chargesText = $charges
            ? implode('; ', array_map('trim', $charges))
            : null;

        $sentenceText = trim($r['sentence'] ?? '') ?: null;

        $convicted = trim($r['sentence'] ?? '')
            ? "Yes — federal conviction; sentenced to ".trim($r['sentence'])
            : null;

        $releaseDate = $this->normalizeDate($r['release_date'] ?? '');

        PrisonerCase::create(array_filter([
            'prisoner_id'        => $prisoner->id,
            'institution_id'     => $institution?->id,
            'charges'            => $chargesText,
            'sentenced_date'     => $this->normalizeDate($r['sentence_date'] ?? ''),
            'release_date'       => $releaseDate,
            'convicted'          => $convicted,
            'sentence'           => $sentenceText,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    private function composeBio(array $r): string
    {
        $body = trim($r['description']);

        $context = [];
        $tags = [];
        if ($r['sting'])      { $tags[] = 'FBI sting operation'; }
        if ($r['informant'])  { $tags[] = 'FBI confidential informant'; }
        if ($r['fisa'])       { $tags[] = 'FISA surveillance'; }
        if ($r['cooperator']) { $tags[] = 'cooperated with prosecutors'; }

        if ($tags) {
            $context[] = 'Case characteristics: '.implode(', ', $tags).'.';
        }

        if (! empty($r['org'])) {
            $context[] = 'Organization referenced in charging documents: '.$r['org'].'.';
        }

        $attribution = "Case data and description from The Intercept's Trial and Terror project (Trevor Aaronson and Margot Williams), licensed CC BY-NC. Source: https://github.com/theintercept/trial-and-terror-data";

        $parts = array_filter([$body, implode(' ', $context), $attribution]);
        return implode("\n\n", $parts);
    }

    private function resolveInstitution(array $r): ?Institution
    {
        $name = trim($r['institution'] ?? '');
        if (! $name) {
            return null;
        }

        return Institution::firstOrCreate(
            ['name' => $name],
            array_filter([
                'city'  => $r['institution_city'] ?? null,
                'state' => $r['institution_state'] ?? null,
            ])
        );
    }

    private function eraFromSentenceDate(string $date): string
    {
        if (! $date || ! preg_match('/^(\d{4})/', $date, $m)) {
            return '2000s';
        }
        $y = (int) $m[1];
        return match (true) {
            $y >= 2020 => '2020s',
            $y >= 2010 => '2010s',
            $y >= 2000 => '2000s',
            default    => '1990s',
        };
    }

    private function buildTags(array $r): array
    {
        return [];
    }

    private function normalizeRace(string $race): string
    {
        $race = trim($race);
        if ($race === '') return '';
        // Pass through; admin can refine.
        return $race;
    }

    private function normalizeDate(string $date): ?string
    {
        $date = trim($date);
        if (! $date) return null;
        // Accept YYYY-MM-DD or ISO timestamps; return YYYY-MM-DD.
        try {
            return Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
