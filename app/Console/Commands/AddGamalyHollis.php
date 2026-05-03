<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddGamalyHollis extends Command
{
    protected $signature = 'prisoners:add-gamaly-hollis';
    protected $description = 'Add Gamaly Hollis, the Miami mother jailed for posting Facebook messages calling the Miami-Dade police officer who killed her son a murderer.';

    private const BIO = <<<'TXT'
Gamaly Hollis is a Miami-Dade mother who served 364 days in the Miami-Dade County jail for violating a judge's stay-away order against the Miami-Dade Police Department officer who shot and killed her only child.

On June 15, 2022, Hollis's 21-year-old son Richard Hollis — a Terra High School graduate, Miami Dade College student, and longtime sufferer of schizophrenia — was in psychiatric crisis in the kitchen of the family's small West Kendall apartment, holding a steak knife and shouting that strangers were poisoning his food. Neighbors called the police. Officer Jaime Pino, who had been dispatched to the family's apartment many times before, arrived, kicked open the door without speaking, fired a Taser at Richard and missed, then shot him five times at close range, killing him in front of his mother. The shooting was captured on multiple body cameras. Pino had warned Hollis nine months earlier, during a previous mental-health call, that "if your son takes a BB gun or a real gun out on me, I'm going to kill your son." The Miami-Dade State Attorney's Office cleared Pino of excessive force; he returned to active duty.

In August 2022, two months after the shooting, Hollis saw Pino on a Kendall street at the scene of an unrelated traffic stop. She rolled down her window and told him "You killed my son." After she drove away and circled back, a group of Miami-Dade officers pulled her from her car, Tased her, and pinned her to the ground while she cried out that she was driving home. She was arrested and a stay-away order was entered against her.

Hollis, represented by the Miami-Dade County Public Defender's Office, continued to post on her own Facebook page photographs of Pino taken from his public Facebook account, accompanied by the words "Murderer." Prosecutors charged her with criminal violation of the stay-away order. At a July 31, 2023 jury trial in Miami-Dade County criminal court, the Public Defender argued she was exercising her First Amendment right to warn the community about police violence; the State Attorney's Office argued that Pino was rightly concerned for his safety and chastised Hollis for failing to be grateful that he had "put his life on the line." She was convicted and sentenced to 364 days in the Miami-Dade County jail — the maximum the misdemeanor charge could carry. She served the full sentence.

After her release she continued to face two additional charges, stalking and resisting arrest, which carried the prospect of returning her to jail. She refused a plea deal that would have spared her further incarceration in exchange for admitting wrongdoing, insisting she had done nothing wrong by speaking to Pino from her car or by criticizing him on Facebook. On April 24, 2026, Miami-Dade prosecutors dropped both remaining charges, ending the prosecution.

Hollis has spoken publicly about her experience and has called for the FBI to reinvestigate her son's killing, for changes to police response protocols on mental-health calls (including the dispatch of trained peer counselors and clinicians instead of armed officers), and for her own case to be understood as a First Amendment case about a grieving mother criminalized for criticizing the police officer who killed her son. Her 2024 prosecution and conviction were the subject of the Miami Herald investigative series Guilty of Grief.
TXT;

    public function handle(): int
    {
        if (Prisoner::where('name', 'Gamaly Hollis')->exists()) {
            $this->error('Gamaly Hollis already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $jail = Institution::firstOrCreate(
                ['name' => 'Miami-Dade County Jail'],
                ['city' => 'Miami', 'state' => 'Florida']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Gamaly Hollis',
                'first_name'     => 'Gamaly',
                'last_name'      => 'Hollis',
                'description'    => self::BIO,
                'gender'         => 'Female',
                'birthdate'      => '1972-01-01', // age 53 as of April 2026 article (year-only known)
                'state'          => 'Florida',
                'era'            => '2020s',
                'ideologies'     => ['Anti-police violence', 'First Amendment', 'Mental health justice'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $jail->id,
                'charges'            => 'Misdemeanor criminal violation of a stay-away injunction (Florida Statutes § 784.047) — for posting on her Facebook page photographs and criticism of Miami-Dade Police Officer Jaime Pino, the officer who fatally shot her son Richard Hollis on June 15, 2022. Plus subsequent stalking and resisting-arrest charges, both dismissed by the Miami-Dade State Attorney\'s Office on April 24, 2026',
                'arrest_date'        => '2022-08-15',
                'incarceration_date' => '2023-07-31',
                'release_date'       => '2024-07-30',
                'convicted'          => 'Yes — Miami-Dade County jury verdict, July 31, 2023 (violation of injunction); subsequent stalking and resisting-arrest charges dismissed by the State Attorney\'s Office on April 24, 2026',
                'sentence'           => '364 days in the Miami-Dade County jail (the statutory maximum for the misdemeanor); served the full sentence',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
