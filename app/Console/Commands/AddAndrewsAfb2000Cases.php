<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Andrews Air Force Base Open House (May 2000) — banner-and-leaflet
 * action. Three Catholic peace activists were convicted of trespass
 * on October 23, 2000 (Nuclear Resister #122).
 *
 *   - Sam Hochstetler   — 30 days
 *   - Kristin Betts     — 60 days (already in DB as "Kristen Betts")
 *   - Greg Boertje-Obed — 6 months (in DB for the 2012 Y-12 Plowshares
 *                                   action; this is an earlier case)
 *
 * Adds Hochstetler as a new prisoner and back-fills the 2000 case onto
 * Boertje-Obed's existing record. Idempotent.
 */
final class AddAndrewsAfb2000Cases extends Command {
    protected $signature = 'prisoners:add-andrews-afb-2000-cases';
    protected $description = 'Add Sam Hochstetler and back-fill Greg Boertje-Obed for the May 2000 Andrews AFB Open House protest.';

    private const SOURCE = 'Source: Nuclear Resister #122 (https://www.nukeresister.org/static/nr122/nr1226mos.html).';

    private const ACTION_STORY =
        "At the Andrews Air Force Base Open House in May 2000, Sam Hochstetler and Greg Boertje-Obed held a banner in front of a B-52 reading \"Swords into Plowshares\", while Kristin Betts handed out leaflets that read, \"Weapons of destruction. Nothing to celebrate.\" The three were convicted of trespass on October 23, 2000. The federal prosecutor argued the witness was not merely a criminal offense but a threat to military morale; the judge sentenced Hochstetler to 30 days, Betts to 60 days, and Boertje-Obed — because of his prior protest record — to a full six months at FCI Fort Dix.";

    public function handle(): int {
        $created = 0;
        $skipped = 0;
        $added   = 0;

        $bopVaried = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons (location varied)']);
        $fortDix   = Institution::firstOrCreate(['name' => 'FCI Fort Dix'], ['city' => 'Fort Dix', 'state' => 'New Jersey']);

        // ── 1. Sam Hochstetler — new prisoner + case ──
        DB::transaction(function () use ($bopVaried, &$created, &$skipped) {
            $existing = Prisoner::where('name', 'Sam Hochstetler')
                ->orWhere('name', 'Samuel Hochstetler')
                ->first();

            if ($existing) {
                $this->warn('Skipping Sam Hochstetler — already exists.');
                $skipped++;
                return;
            }

            $prisoner = Prisoner::create([
                'name'         => 'Sam Hochstetler',
                'first_name'   => 'Sam',
                'last_name'    => 'Hochstetler',
                'gender'       => 'Male',
                'race'         => 'White',
                'era'          => '2000s',
                'ideologies'   => ['Anti-nuclear', 'Pacifist', 'Catholic Worker'],
                'affiliation'  => ['Plowshares movement', 'Jonah House'],
                'in_custody'   => false,
                'released'     => true,
                'description'  => "Sam Hochstetler was one of three Catholic peace activists arrested at the Andrews Air Force Base Open House in May 2000 for a banner-and-leaflet action in front of a B-52 bomber. Convicted of trespass on October 23, 2000, he was sentenced to 30 days in federal custody.\n\n" . self::ACTION_STORY . "\n\n" . self::SOURCE,
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $bopVaried->id,
                'charges'        => 'Trespass at Andrews Air Force Base Open House (banner-holding in front of B-52)',
                'arrest_date'    => '2000-05-21',
                'sentenced_date' => '2000-10-23',
                'sentence'       => '30 days federal',
            ]);

            $this->info('Created Sam Hochstetler.');
            $created++;
        });

        // ── 2. Greg Boertje-Obed — back-fill the 2000 Andrews AFB case ──
        DB::transaction(function () use ($bopVaried, $fortDix, &$added, &$skipped) {
            $greg = Prisoner::where('name', 'Greg Boertje-Obed')->first();

            if (! $greg) {
                $this->warn('Greg Boertje-Obed not found in DB — skipping case back-fill.');
                $skipped++;
                return;
            }

            $existingCase = PrisonerCase::where('prisoner_id', $greg->id)
                ->where('charges', 'like', '%Andrews%')
                ->first();

            if ($existingCase) {
                $this->warn('Skipping Boertje-Obed Andrews AFB case — already on record.');
                $skipped++;
                return;
            }

            PrisonerCase::create([
                'prisoner_id'        => $greg->id,
                'institution_id'     => $fortDix->id,
                'charges'            => 'Trespass at Andrews Air Force Base Open House (banner-holding in front of B-52)',
                'arrest_date'        => '2000-05-21',
                'sentenced_date'     => '2000-10-23',
                'incarceration_date' => '2000-10-23',
                'release_date'       => '2001-04-23',
                'sentence'           => '6 months federal (enhanced for prior protest record)',
            ]);

            $this->info('Added 2000 Andrews AFB case to Greg Boertje-Obed.');
            $added++;
        });

        // ── 3. Kristen/Kristin Betts — spelling reconciliation note ──
        $betts = Prisoner::where('name', 'like', '%Betts%')
            ->where(function ($q) {
                $q->where('first_name', 'Kristen')->orWhere('first_name', 'Kristin');
            })
            ->first();

        if ($betts) {
            $this->line('');
            $this->info('Kristen/Kristin Betts already in DB as: '.$betts->name);
            $this->line('  Nuclear Resister #122 renders the name as "Kristin"; DB has "'.$betts->first_name.'".');
            $this->line('  No automatic rename — flag for manual confirmation against a primary source.');
        }

        $this->line('');
        $this->info("Done. Created {$created}, added {$added} case(s), skipped {$skipped}.");

        return self::SUCCESS;
    }
}
