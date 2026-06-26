<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds Benjamin Persky's co-defendants from the animal-rights property-damage
 * case — Joshua Schwartz and Jennifer Greenberg. The three were arrested
 * together for damaging property during a protest against a laboratory that
 * tests on animals (the anti-Huntingdon Life Sciences campaign). Schwartz and
 * Greenberg each served one-year sentences (Persky two to six). Persky is
 * already in the database and is skipped. Idempotent.
 */
final class AddHlsCodefendants extends Command
{
    protected $signature = 'prisoners:add-hls-codefendants';

    protected $description = "Add Benjamin Persky's anti-HLS co-defendants (Joshua Schwartz, Jennifer Greenberg)";

    public function handle(): int
    {
        $people = [
            ['name' => 'Joshua Schwartz', 'first' => 'Joshua', 'last' => 'Schwartz', 'gender' => 'Male',
                'desc' => 'Joshua Schwartz was an animal-rights activist arrested with Benjamin Persky and Jennifer Greenberg for damaging property during a protest against a laboratory that tests on animals — part of the campaign against Huntingdon Life Sciences (HLS). He served a one-year sentence.'],

            ['name' => 'Jennifer Greenberg', 'first' => 'Jennifer', 'last' => 'Greenberg', 'gender' => 'Female',
                'desc' => 'Jennifer Greenberg was a 17-year-old animal-rights activist arrested with Benjamin Persky and Joshua Schwartz for damaging property during a protest against a laboratory that tests on animals — part of the campaign against Huntingdon Life Sciences (HLS). She served a one-year sentence.'],

            // Persky is already in the database (added from the ABC 2003 list); included
            // here only so "add them all" is idempotent — he is skipped if present.
            ['name' => 'Benjamin Persky', 'first' => 'Benjamin', 'last' => 'Persky', 'gender' => 'Male',
                'desc' => 'Benjamin Persky was an animal-rights activist imprisoned in New York for property destruction during protests against Huntingdon Life Sciences (the anti-HLS campaign).'],
        ];

        $added = 0;
        foreach ($people as $p) {
            $existing = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($p['name']))
                ->orWhere('name', $p['name'])
                ->first();

            if ($existing) {
                $this->line("Skipped (already present): {$existing->name}");

                continue;
            }

            $prisoner = new Prisoner([
                'name' => $p['name'], 'first_name' => $p['first'], 'last_name' => $p['last'], 'gender' => $p['gender'],
                'state' => 'New York', 'era' => '2000s',
                'ideologies' => ['Animal liberation'], 'affiliation' => ['Animal liberation movement'],
                'in_custody' => false, 'released' => true, 'under_review' => false,
                'description' => $p['desc'],
            ]);
            $prisoner->save();
            $added++;
            $this->info("Created: {$prisoner->name} (/prisoner/{$prisoner->slug})");

            $prisoner->cases()->create([
                'charges' => 'Property damage during an animal-rights protest against a laboratory that tests on animals (anti-Huntingdon Life Sciences action).',
                'sentence' => 'One year',
            ]);
        }

        $this->info("\nDone. Added {$added} record(s).");

        return self::SUCCESS;
    }
}
