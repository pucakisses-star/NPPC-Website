<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Batch 1 of the historical political-prisoner additions: the "Vancouver 4" —
 * members of the Canadian anarchist group Direct Action (the "Squamish Five"),
 * arrested January 20, 1983 for the 1982 Litton Systems (Toronto) and
 * Cheekye–Dunsmuir BC Hydro bombings and the Red Hot Video firebombings. All
 * were released decades ago. Idempotent; matches existing records by slug/name.
 */
final class AddVancouverFour extends Command
{
    protected $signature = 'prisoners:add-vancouver-four';

    protected $description = 'Add the Vancouver 4 (Direct Action / Squamish Five) political prisoners';

    public function handle(): int
    {
        $base = [
            'state' => 'British Columbia',
            'era' => '1980s',
            'ideologies' => ['Anarchism', 'Environmentalism', 'Anti-militarism'],
            'affiliation' => ['Direct Action', 'Squamish Five'],
            'in_custody' => false,
            'released' => true,
            'under_review' => false,
        ];

        $people = [
            [
                'name' => 'Ann Hansen', 'first' => 'Ann', 'last' => 'Hansen', 'gender' => 'Female',
                'prison' => ['Prison for Women (P4W)', 'Kingston', 'Ontario'],
                'sentence' => 'Life imprisonment (served about 8 years)', 'release' => 1991,
                'desc' => "Ann Hansen is a Canadian anarchist and a member of Direct Action, the early-1980s urban guerrilla group the press dubbed the \"Squamish Five.\" Direct Action bombed a Litton Systems plant in Toronto in 1982 (which made guidance components for U.S. cruise missiles), dynamited the Cheekye–Dunsmuir BC Hydro substation on Vancouver Island, and — as the \"Wimmin's Fire Brigade\" — firebombed Red Hot Video outlets selling violent pornography. The group was captured near Squamish, B.C. on January 20, 1983. Hansen was sentenced to life and served about eight years. She later wrote the memoir \"Direct Action: Memoirs of an Urban Guerrilla.\"",
            ],
            [
                'name' => 'Brent Taylor', 'first' => 'Brent', 'last' => 'Taylor', 'gender' => 'Male',
                'prison' => ['Millhaven Institution', 'Bath', 'Ontario'],
                'sentence' => '22 years (served about 8)', 'release' => 1991,
                'desc' => "Brent Taylor is a Canadian anarchist and a member of Direct Action (the \"Squamish Five\"), which in 1982 bombed a Litton Systems plant in Toronto — a maker of U.S. cruise-missile guidance components — dynamited the Cheekye–Dunsmuir BC Hydro substation, and firebombed Red Hot Video outlets. Captured near Squamish, B.C. on January 20, 1983, Taylor received the group's longest term, 22 years, and served about eight years before release.",
            ],
            [
                'name' => 'Gerry Hannah', 'first' => 'Gerry', 'last' => 'Hannah', 'gender' => 'Male',
                'prison' => ['Matsqui Institution', 'Abbotsford', 'British Columbia'],
                'sentence' => '10 years (served about 5)', 'release' => 1988,
                'desc' => 'Gerry Hannah is a Canadian punk musician — bassist of the band the Subhumans — and a member of Direct Action (the "Squamish Five"). The group carried out the 1982 bombing of a Litton Systems plant in Toronto, the Cheekye–Dunsmuir BC Hydro substation bombing, and the Red Hot Video firebombings, and was captured near Squamish, B.C. on January 20, 1983. Hannah was sentenced to 10 years and served about five.',
            ],
            [
                'name' => 'Doug Stewart', 'first' => 'Doug', 'last' => 'Stewart', 'gender' => 'Male',
                'prison' => ['Kent Institution', 'Agassiz', 'British Columbia'],
                'sentence' => '6 years (served about 4)', 'release' => 1987,
                'desc' => 'Doug Stewart is a Canadian anarchist and a member of Direct Action (the "Squamish Five"), which carried out the 1982 Litton Systems and Cheekye–Dunsmuir BC Hydro bombings and the Red Hot Video firebombings. Captured near Squamish, B.C. on January 20, 1983, he was sentenced to six years and served about four.',
            ],
        ];

        foreach ($people as $p) {
            $slug = Str::slug($p['name']);
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', $slug)
                ->orWhere('name', 'like', '%'.$p['name'].'%')
                ->first();

            $attrs = array_merge($base, [
                'name' => $p['name'], 'first_name' => $p['first'], 'last_name' => $p['last'],
                'gender' => $p['gender'], 'description' => $p['desc'],
            ]);

            if ($prisoner) {
                $prisoner->fill($attrs)->save();
                $this->info("Updated: {$prisoner->name}");
            } else {
                $prisoner = Prisoner::create($attrs);
                $this->info("Created: {$prisoner->name}");
            }

            if ($prisoner->cases()->count() === 0) {
                $inst = Institution::firstOrCreate(
                    ['name' => $p['prison'][0]],
                    ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                );
                $case = $prisoner->cases()->make([
                    'institution_id' => $inst->id,
                    'charges' => 'Bombing of the Litton Systems plant (Toronto) and the Cheekye–Dunsmuir BC Hydro substation, and the Red Hot Video firebombings, as the group Direct Action (1982).',
                    'sentence' => $p['sentence'],
                    'convicted' => 'Yes',
                ]);
                $case->setPartialDate('arrest_date', 1983, 1, 20);
                $case->setPartialDate('incarceration_date', 1983, 1, 20);
                $case->setPartialDate('release_date', $p['release']);
                $case->save();
                $this->line("  + case at {$inst->name} (released ~{$p['release']})");
            }
        }

        return self::SUCCESS;
    }
}
