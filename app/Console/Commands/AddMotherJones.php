<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddMotherJones extends Command
{
    protected $signature = 'prisoners:add-mother-jones';
    protected $description = 'Add Mary Harris "Mother" Jones to the prisoner database with her two major political imprisonments.';

    private const BIO = <<<'TXT'
Mary G. Harris Jones — known the world over as "Mother" Jones — was an Irish-born American labor organizer whose half-century of agitation against industrial exploitation made her, in the words of a West Virginia federal prosecutor in 1902, "the most dangerous woman in America."

Born in Cork, Ireland in 1837, she emigrated as a child with her family during the Irish famine, settling first in Canada and later in the United States, where she trained as a dressmaker and a schoolteacher. In 1867 her husband, an iron moulder and union member, and all four of their children died in the yellow fever epidemic that swept Memphis. Four years later she lost her dressmaking shop and everything she owned in the Great Chicago Fire of 1871. From those losses she built a new life entirely inside the labor movement, traveling town to town in support of striking workers and helping organize miners, garment workers, steelworkers, and railroad employees.

From 1900 onward she focused on the coalfields, organizing for the United Mine Workers of America in West Virginia, Pennsylvania, and Colorado. In 1903 she led the "March of the Mill Children" from Kensington, Pennsylvania to Theodore Roosevelt's home on Long Island to demand laws against child labor. In 1905 she was one of the founders of the Industrial Workers of the World. She was a powerful public speaker who built her organizing around the families of striking miners — and was repeatedly jailed for it.

During the Paint Creek–Cabin Creek strike in West Virginia in February 1913, she was arrested at age 75, tried by a military commission under martial law, and sentenced to twenty years in the state penitentiary on charges of conspiracy to commit murder. Held under house arrest at a boarding house in Pratt, West Virginia, she contracted pneumonia and was nearly killed. After 85 days a U.S. Senate investigation into conditions in the West Virginia coalfields — opened in direct response to her case — forced her release.

Months later she traveled to Colorado to organize miners against the Rockefeller-owned Colorado Fuel and Iron Company in what became known as the Colorado Coalfield War. She was arrested again in early 1914, held without charge in the San Rafael Hospital and the Mount San Rafael jail in Trinidad, and ultimately escorted out of the state by the militia. Her imprisonment in Colorado ended only weeks before company gunmen and National Guard troops attacked the strikers' tent colony at Ludlow, killing more than 20 people including women and children.

She continued organizing into her nineties. She published her Autobiography of Mother Jones in 1925 and died on November 30, 1930, at the age of 93, at a farm outside Washington, D.C. She is buried at the Union Miners Cemetery in Mount Olive, Illinois, alongside the miners killed in the 1898 Virden massacre. Her name endures on union halls, on a magazine, and in the labor movement's most cited rallying cry — her own: "Pray for the dead, and fight like hell for the living."
TXT;

    public function handle(): int
    {
        if (Prisoner::where('name', 'like', '%Mother Jones%')
            ->orWhere('name', 'like', '%Mary Harris Jones%')
            ->exists()) {
            $this->error('A Mother Jones / Mary Harris Jones record already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'                => 'Mary Harris Jones',
                'first_name'          => 'Mary',
                'middle_name'         => 'Harris',
                'last_name'           => 'Jones',
                'aka'                 => 'Mother Jones',
                'description'         => self::BIO,
                'gender'              => 'Female',
                'birthdate'           => '1837-08-01',
                'death_date'          => '1930-11-30',
                'state'               => 'Illinois',
                'era'                 => '1910s',
                'ideologies'          => ['Socialist', 'Labor', 'Anti-capitalist'],
                'affiliation'         => ['United Mine Workers of America', 'Industrial Workers of the World', 'Knights of Labor'],
                'in_custody'          => false,
                'released'            => true,
                'awaiting_trial'      => false,
            ]);

            $wv = Institution::firstOrCreate(
                ['name' => 'Pratt Boarding House (military custody)'],
                ['city' => 'Pratt', 'state' => 'West Virginia']
            );

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $wv->id,
                'charges'            => 'Conspiracy to commit murder; tried by military commission under martial law during the Paint Creek–Cabin Creek strike',
                'arrest_date'        => '1913-02-13',
                'incarceration_date' => '1913-02-13',
                'release_date'       => '1913-05-08',
                'convicted'          => 'Yes — convicted by military court (jurisdiction always disputed; she refused to recognize the court)',
                'sentence'           => '20 years in the West Virginia state penitentiary; released after 85 days following a U.S. Senate investigation into conditions in the coalfields',
                'judge'              => 'West Virginia military commission',
            ]);

            $co = Institution::firstOrCreate(
                ['name' => 'Mount San Rafael Hospital / Trinidad jail (militia custody)'],
                ['city' => 'Trinidad', 'state' => 'Colorado']
            );

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $co->id,
                'charges'            => 'Held without charge under martial law during the Colorado Coalfield War organizing strike against the Colorado Fuel and Iron Company',
                'arrest_date'        => '1914-01-12',
                'incarceration_date' => '1914-01-12',
                'release_date'       => '1914-03-16',
                'convicted'          => 'No charges filed; held by Colorado National Guard',
                'sentence'           => 'No sentence; released after approximately nine weeks of incommunicado detention and escorted out of the state',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            $this->info("  - West Virginia 1913 case");
            $this->info("  - Colorado 1914 case");
        });

        return self::SUCCESS;
    }
}
