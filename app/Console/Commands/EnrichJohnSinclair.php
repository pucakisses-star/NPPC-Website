<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Enriches the existing John Sinclair record — the poet, MC5 manager, and White
 * Panther Party co-founder whose 9½–10-year sentence for giving two joints to
 * undercover officers became a national cause célèbre (the 1971 "Free John
 * Sinclair" rally with John Lennon). He is already in the database with a good
 * description but no case, no name split, no birth/death dates, and no
 * ideologies/affiliation. This fills those in.
 *
 * Timeline: offense December 1966; convicted July 1969 and imprisoned; released
 * on bond December 13, 1971, three days after the rally, after ~29 months; the
 * Michigan Supreme Court struck down the state marijuana law in March 1972.
 *
 * He is already in the DB, so this enriches rather than creating a duplicate.
 * Idempotent: only adds a case if he has none; safe to re-run.
 */
final class EnrichJohnSinclair extends Command
{
    protected $signature = 'prisoners:enrich-john-sinclair';

    protected $description = 'Enrich the existing John Sinclair record (name, dates, case) from his 2024 obituary';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'john-sinclair')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'John Sinclair')->first();

        if (! $prisoner) {
            $this->warn('No John Sinclair record found — nothing to enrich.');

            return self::SUCCESS;
        }

        $prisoner->first_name = 'John';
        $prisoner->last_name = 'Sinclair';
        $prisoner->birthdate = '1941-10-02';
        $prisoner->death_date = '2024-04-02';
        $prisoner->ideologies = ['Marijuana legalization', 'Counterculture', 'Anti-authoritarianism'];
        $prisoner->affiliation = ['White Panther Party'];
        $prisoner->in_custody = false;
        $prisoner->released = true;
        // Ensure the birth/death dates render at full precision.
        $precision = $prisoner->date_precision ?? [];
        unset($precision['birthdate'], $precision['death_date']);
        $prisoner->date_precision = $precision ?: null;
        $prisoner->save();

        if ($prisoner->cases()->count() > 0) {
            $this->info("{$prisoner->name} already has a case — updated bio only. View: /prisoner/{$prisoner->slug}");

            return self::SUCCESS;
        }

        $prisoner->cases()->create([
            'charges' => 'Possession of marijuana — for giving two joints to undercover Detroit narcotics officers in December 1966 (his third marijuana charge).',
            'arrest_date' => '1967-01-24',
            'convicted' => "Convicted July 1969. The conviction was overturned by the Michigan Supreme Court on March 9, 1972 (People v. Sinclair), which struck down the state's marijuana statute as unconstitutional.",
            'sentenced_date' => '1969-07-28',
            'incarceration_date' => '1969-07-28',
            'release_date' => '1971-12-13',
            'sentence' => "9½ to 10 years in state prison. Released on bond December 13, 1971 — three days after the \"Free John Sinclair\" rally at Ann Arbor's Crisler Arena (John Lennon, Yoko Ono, Stevie Wonder, Allen Ginsberg, Bob Seger, Phil Ochs and others, before 15,000 people) — after serving about 29 months.",
        ]);

        $case = $prisoner->cases()->first();
        $days = $case?->imprisoned_for_days;
        $this->info("Enriched {$prisoner->name}; added case (".($days ? "{$days} days" : 'n/a')."). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
