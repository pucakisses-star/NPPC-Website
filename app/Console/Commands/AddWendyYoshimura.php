<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Wendy Yoshimura, the Japanese-American radical (born at Manzanar) convicted
 * in 1977 on 1972 Berkeley explosives/weapons charges and supported as a political
 * prisoner by the Asian-American movement. Surfaced from the Workers Vanguard "Free
 * Wendy Yoshimura" coverage; sourced to Wikipedia. Recorded honestly: a real
 * weapons/explosives case, not a pure frame-up. Idempotent.
 */
class AddWendyYoshimura extends Command {
    protected $signature = 'prisoners:add-wendy-yoshimura';
    protected $description = 'Add Wendy Yoshimura (1972 Berkeley explosives case; convicted 1977)';

    private const BIO = <<<'TXT'
Wendy Yoshimura (born January 17, 1943, at the Manzanar incarceration camp, where her Japanese-American parents were imprisoned during World War II) became a cause célèbre for the Japanese-American and Asian-American movements. In 1972, police found a cache of weapons and explosives in a Berkeley, California garage she had rented, along with writings about planned bombings; after her co-defendant was arrested in March 1972, Yoshimura went underground, living under an alias and later among the remnants of the Symbionese Liberation Army.

She was arrested on September 18, 1975 in San Francisco, in the same operation that captured Patty Hearst. Tried on the 1972 Berkeley explosives and weapons charges, she was convicted in 1977 and sentenced to one-to-fifteen years. Japanese-American communities — many drawing a parallel to their own wartime incarceration — raised some $150,000 through the Wendy Yoshimura Fair Trial Committee. She was paroled in September 1980.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Wendy Yoshimura')->exists()) {
            $this->error('Wendy Yoshimura already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'Wendy Yoshimura',
                'first_name'     => 'Wendy',
                'last_name'      => 'Yoshimura',
                'description'    => self::BIO,
                'gender'         => 'Female',
                'race'           => 'Asian',
                'birthdate'      => '1943-01-17',
                'state'          => 'California',
                'era'            => '1970s',
                'ideologies'     => ['Anti-imperialism', 'Asian American movement'],
                'affiliation'    => ['Wendy Yoshimura Fair Trial Committee'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'Possession of explosives and weapons and related conspiracy charges arising from a 1972 weapons-and-explosives cache found in a Berkeley, California garage she had rented; she was a fugitive until her 1975 arrest.',
                'arrest_date' => '1975-09-18',
                'convicted'   => 'Convicted in 1977 on the 1972 Berkeley explosives and weapons charges; sentenced to one-to-fifteen years; paroled in September 1980.',
                'sentence'    => 'One to fifteen years; paroled in September 1980.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
