<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Rubin "Hurricane" Carter and John Artis, convicted of the 1966 Lafayette
 * Bar triple murder in Paterson, NJ in a case federal courts found was
 * "predicated upon an appeal to racism rather than reason." Surfaced from the
 * Workers Vanguard / Militant "Free Hurricane Carter / John Artis" coverage;
 * sourced to Wikipedia. Idempotent (skips by name).
 */
class AddHurricaneCarterArtis extends Command {
    protected $signature = 'prisoners:add-hurricane-carter';
    protected $description = 'Add Rubin "Hurricane" Carter and John Artis (1966 Lafayette Bar frame-up, NJ)';

    private const CONTEXT = <<<'TXT'
On June 17, 1966, three people were shot to death at the Lafayette Bar and Grill in Paterson, New Jersey. Rubin "Hurricane" Carter, a nationally ranked middleweight boxer, and John Artis, a young man with no criminal record, were arrested and — on the testimony of two petty criminals, Alfred Bello and Arthur Bradley — convicted of the triple murder in 1967 and sentenced to life. No forensic evidence linked either man to the killings.

Bello and Bradley recanted in 1974, but a 1976 retrial again convicted both men. The case became an international cause célèbre — championed in Bob Dylan's 1975 song "Hurricane" and by a broad civil-rights and labor coalition — as an emblem of racist wrongful conviction. In November 1985, federal judge H. Lee Sarokin overturned the convictions, finding the prosecution had been "predicated upon an appeal to racism rather than reason, and concealment rather than disclosure." Carter was freed, and the charges were dismissed in 1988.
TXT;

    public function handle(): int {
        DB::transaction(function () {
            $njsp = Institution::firstOrCreate(
                ['name' => 'New Jersey State Prison'],
                ['city' => 'Trenton', 'state' => 'New Jersey']
            );

            $defendants = [
                [
                    'name' => 'Rubin Carter', 'first' => 'Rubin', 'last' => 'Carter',
                    'birthdate' => '1937-05-06', 'death' => '2014-04-20',
                    'lead' => 'Rubin "Hurricane" Carter (May 6, 1937 – April 20, 2014) was a nationally ranked middleweight boxer when he was convicted of the Lafayette Bar killings.',
                    'sentence' => 'Life imprisonment; served roughly 19 years before being freed when the convictions were overturned in November 1985.',
                ],
                [
                    'name' => 'John Artis', 'first' => 'John', 'last' => 'Artis',
                    'birthdate' => null, 'death' => '2021-11-07',
                    'lead' => 'John Artis (died November 7, 2021) was a 19-year-old with no criminal record, convicted alongside Carter; he stood by Carter\'s innocence and his own for decades.',
                    'sentence' => 'Life imprisonment; paroled in 1981 and cleared when the convictions were overturned in 1985.',
                ],
            ];

            foreach ($defendants as $d) {
                if (Prisoner::where('name', $d['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$d['name']}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name'           => $d['name'],
                    'first_name'     => $d['first'],
                    'last_name'      => $d['last'],
                    'description'    => $d['lead']."\n\n".self::CONTEXT,
                    'gender'         => 'Male',
                    'race'           => 'Black',
                    'birthdate'      => $d['birthdate'],
                    'death_date'     => $d['death'],
                    'state'          => 'New Jersey',
                    'era'            => '1960s',
                    'ideologies'     => ['Civil rights'],
                    'affiliation'    => [],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $njsp->id,
                    'charges'        => 'Triple murder in the June 17, 1966 shooting at the Lafayette Bar and Grill in Paterson, New Jersey — a conviction widely condemned as a racist frame-up (no forensic evidence; the two key witnesses recanted; the federal courts found the prosecution had appealed to racism and concealed evidence).',
                    'convicted'      => 'Convicted of triple murder in 1967 and again at a 1976 retrial; sentenced to life. The convictions were overturned by federal Judge H. Lee Sarokin in November 1985, and the charges were dismissed in 1988.',
                    'sentence'       => $d['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
