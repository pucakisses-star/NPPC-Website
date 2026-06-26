<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Batch 5 (final) of the historical political-prisoner additions: the remaining
 * Mexicano (María Cueto), North American anti-imperialist (Richard Picariello,
 * Timothy Blunk), Ohio 7 (Pat Gros), and Plowshares (Per Herngren) names, plus
 * one minimal, flagged directory entry (Shelly Miller — anti-imperialist PP at
 * Alderson whose case details weren't reliably sourced). All long since
 * released. Idempotent.
 */
final class AddPpBatch5 extends Command
{
    protected $signature = 'prisoners:add-pp-batch5';

    protected $description = 'Add the remaining historical PPs (Cueto, Picariello, Gros, Herngren, Blunk, Miller)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'María Cueto', 'first' => 'María', 'last' => 'Cueto', 'gender' => 'Female',
                'state' => 'New York', 'inmate' => '15884-053', 'era' => '1980s',
                'ideologies' => ['Puerto Rican independence'], 'affiliation' => ['Movimiento de Liberación Nacional (MLN)'],
                'desc' => 'María Cueto was the director of the National Commission on Hispanic Affairs of the Episcopal Church and a well-known grand jury resister. Subpoenaed in 1977 and again in 1981 before federal grand juries investigating the FALN and the Puerto Rican independence movement, she refused to testify on principle. After jailing for civil contempt, she was among five resisters (with Ricardo Romero, Julio and Andrés Rosado, and Steven Guerra) indicted for criminal contempt and each sentenced to three years.',
                'prison' => ['FCI Pleasanton', 'Dublin', 'California'],
                'charges' => 'Criminal contempt — refused to testify before federal grand juries investigating the FALN / Puerto Rican independence movement (1977, 1981).',
                'convicted' => 'Contempt', 'sentence' => '3 years',
            ],
            [
                'name' => 'Richard Picariello', 'first' => 'Richard', 'last' => 'Picariello', 'gender' => 'Male',
                'state' => 'Massachusetts', 'inmate' => '05812', 'era' => '1970s',
                'ideologies' => ['Anti-imperialism'], 'affiliation' => ["Fred Hampton Unit of the People's Forces"],
                'desc' => "Richard Joseph Picariello led the Fred Hampton Unit of the People's Forces, which set off a series of bombings of government and corporate targets in Massachusetts and New Hampshire around the 1976 U.S. Bicentennial. The group described itself as dedicated to fighting \"imperialism/capitalism, racism, sexism, and the fascist judicial/prison system.\" Placed on the FBI's Ten Most Wanted list, he was arrested in Fall River, Massachusetts on October 21, 1976, and was convicted of interstate transportation of explosives and sentenced to ten years.",
                'prison' => ['MCI-Cedar Junction (Walpole)', 'Walpole', 'Massachusetts'],
                'charges' => '1976 bombings of government targets in Massachusetts and New Hampshire as the Fred Hampton Unit of the People\'s Forces; interstate transportation of explosives.',
                'convicted' => 'Yes', 'sentence' => '10 years', 'arrest' => [1976, 10, 21], 'incarc' => [1976, null, null],
            ],
            [
                'name' => 'Pat Gros', 'first' => 'Pat', 'last' => 'Gros', 'aka' => 'Patricia Levasseur; Patricia Rowbottom', 'gender' => 'Female',
                'state' => 'Massachusetts', 'inmate' => null, 'era' => '1980s',
                'ideologies' => ['Anti-imperialism'], 'affiliation' => ['United Freedom Front', 'Ohio 7'],
                'desc' => 'Pat Gros (born Patricia Rowbottom, 1948), partner of Raymond Luc Levasseur, spent a decade underground as part of the United Freedom Front. Arrested with Levasseur and their three daughters near Deerfield, Ohio on November 4, 1984, she received a five-year sentence for harboring a fugitive and possessing fraudulent identification. As one of the "Ohio 7," she stood trial on sedition and racketeering charges and was acquitted of sedition in November 1989.',
                'prison' => ['Metropolitan Correctional Center, New York', 'New York', 'New York'],
                'charges' => 'Harboring a fugitive (Raymond Levasseur) and fraudulent identification (United Freedom Front); later tried and acquitted of sedition in the Ohio 7 case (1989).',
                'convicted' => '5 years (harboring/ID); acquitted of sedition (1989)', 'sentence' => '5 years', 'arrest' => [1984, 11, 4],
            ],
            [
                'name' => 'Per Herngren', 'first' => 'Per', 'last' => 'Herngren', 'aka' => 'Pat Herngren', 'gender' => 'Male',
                'state' => 'Sweden', 'inmate' => '03824-018', 'era' => '1980s',
                'ideologies' => ['Anti-militarism', 'Christian pacifism'], 'affiliation' => ['Plowshares'],
                'desc' => 'Per Herngren is a Swedish peace activist and writer on nonviolence. On Easter morning, April 22, 1984, he was one of eight Pershing Plowshares activists who entered a Martin Marietta plant in Orlando, Florida, and hammered and poured blood on Pershing II and Patriot missile components. He received an eight-year U.S. sentence — longer than his co-defendants — and served about fifteen months across eleven prisons before being deported to Sweden.',
                'prison' => ['FCI Danbury', 'Danbury', 'Connecticut'],
                'charges' => 'Pershing Plowshares disarmament action at Martin Marietta, Orlando, FL (April 22, 1984) — hammering and pouring blood on Pershing II / Patriot missile components.',
                'convicted' => 'Yes', 'sentence' => '8 years (served ~15 months, then deported to Sweden)', 'arrest' => [1984, 4, 22], 'incarc' => [1984, null, null], 'release' => [1985, null, null],
            ],
            // NOTE: "Tim Blank" (#09429-050) from the list is Timothy Blunk, who is
            // already in the database (the audit missed him on the Blank/Blunk
            // spelling). Omitted here so his existing record isn't overwritten.
            [
                'name' => 'Shelly Miller', 'first' => 'Shelly', 'last' => 'Miller', 'gender' => 'Female',
                'state' => null, 'inmate' => '16205-053', 'era' => '1980s',
                'ideologies' => ['Anti-imperialism'], 'affiliation' => ['Anti-imperialist movement'],
                'desc' => 'Shelly Miller was listed as an anti-imperialist political prisoner, held at the Federal Prison Camp in Alderson, West Virginia, in U.S. political-prisoner directories of the 1980s, and gave testimony about control-unit prison conditions. Detailed, reliably-sourced information about her case was not available; this entry preserves the directory listing.',
                'prison' => ['FPC Alderson', 'Alderson', 'West Virginia'],
            ],
        ];

        foreach ($people as $p) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($p['name']))
                ->orWhere('name', $p['name'])
                ->first();

            $attrs = [
                'name' => $p['name'], 'first_name' => $p['first'], 'last_name' => $p['last'], 'aka' => $p['aka'] ?? null,
                'gender' => $p['gender'], 'state' => $p['state'], 'inmate_number' => $p['inmate'] ?? null, 'era' => $p['era'],
                'ideologies' => $p['ideologies'], 'affiliation' => $p['affiliation'],
                'in_custody' => false, 'released' => true, 'under_review' => false, 'description' => $p['desc'],
            ];

            $prisoner ? $prisoner->fill($attrs) : $prisoner = new Prisoner($attrs);
            $prisoner->save();
            $this->info(($prisoner->wasRecentlyCreated ? 'Created: ' : 'Updated: ').$prisoner->name);

            if ($prisoner->cases()->count() === 0) {
                $inst = Institution::firstOrCreate(
                    ['name' => $p['prison'][0]],
                    ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                );
                $case = $prisoner->cases()->make([
                    'institution_id' => $inst->id,
                    'charges' => $p['charges'] ?? null,
                    'convicted' => $p['convicted'] ?? null,
                    'sentence' => $p['sentence'] ?? null,
                ]);
                foreach (['arrest' => 'arrest_date', 'incarc' => 'incarceration_date', 'release' => 'release_date'] as $k => $field) {
                    if (! empty($p[$k][0])) {
                        $case->setPartialDate($field, ...$p[$k]);
                    }
                }
                $case->save();
            }
        }

        return self::SUCCESS;
    }
}
