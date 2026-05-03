<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class UpdateNuclearResisterDetails extends Command
{
    protected $signature = 'prisoners:update-nuclear-resister-details';
    protected $description = 'Update existing prisoner records with BOP / state inmate ID numbers extracted from the nukeresister.org Inside & Out source data.';

    public function handle(): int
    {
        // name => inmate_number, extracted from nukeresister.org Inside & Out source data
        // (issues 136-207, 2014-2025) at /tmp/nukeresister-research/all-inside-out.txt
        $idMap = [
            'Albert L. Simmons'           => '93614-020',
            'Alice Gerard'                => '92095-020',
            'Anika D. Cunningham'         => '92567-020',
            'Anne Montgomery'             => '03827-018',
            'Ardeth Platte'               => '10857-039',
            'Arthur Landis'               => '93660-020',
            'Ayman Jarwan'                => '11920-052',
            'Bonnie Urfer'                => '04970-045',
            'Brendan Walsh'               => '12473-052',
            'Brent Vincent Betterly'      => '2012-0519001',
            'Brian "Jacob" Church'        => '2012-0519002',
            'Brian Terrell'               => '06125-026',
            'Bridget Shergalis'           => '67968',
            'Buddy Bell'                  => '92561-020',
            'Buddy R. Bell'               => '92561-020',
            'Calla Walsh'                 => '67970',
            'Carlos Steward'              => '09105-088',
            'Carl W. Steward'             => '09105-088',
            'Carmen Trotta'               => '22561-021',
            'Carna Yipe'                  => '93646-020',
            'Carol Gilbert'               => '10856-039',
            'Chelsea Manning'             => '89289',
            'Chelsea E. Manning'          => '89289',
            'Cheryl Sommers'              => '91437-020',
            'Christopher Spicer'          => '94642-020',
            'Clare Grady'                 => '01264-052',
            'Daniel Burns'                => '13182-052',
            'Daniel E. Hale'              => '26069-075',
            'David A. Sylvester'          => '91441-020',
            'Delmar Schwaller'            => '91435-020',
            'Sister Diane Pinchot'        => '93612-020',
            'Diane T. Pinchot'            => '93612-020',
            'Donald W. Nelson'            => '92559-020',
            'Donte Smith'                 => '91436-020',
            'Dorothy Parker'              => '91432-020',
            'Edward Smith'                => '46994-083',
            'Edwin R. Lewinson'           => '92126-020',
            'Elizabeth Ann Lentsch'       => '30147-074',
            'Elton Davis'                 => '19777-047',
            'Francis Donnelly'            => '01787-036',
            'Francis Woolever'            => '91438-020',
            'Fredrick Brancel'            => '92562-020',
            'Gail Phares'                 => '91433-020',
            'Gail S. Phares'              => '91433-020',
            'Gerald "Jerry" Ebner'        => '24467-045',
            'Greg Boertje-Obed'           => '08052-016',
            'Helen Woodson'               => '03231-045',
            'Inge Donato'                 => '40885-050',
            'Jackie Hudson'               => '08808-039',
            'Jane Hosking'                => '05331-090',
            'Jared "Jay" Chase'           => '2012-0519003',
            'Jedidiah Poole'              => '74334-112',
            'Jerome Zawada'               => '04995-045',
            'Jerome A. Zawada'            => '04995-045',
            'Joan Anderson'               => '93649-020',
            'Joanne Cowan'                => '92566-020',
            'John D. Apel'                => '26142-112',
            'Jorge Cruz Hernandez'        => '26318-069',
            'Joseph Donato'               => '40884-050',
            'José Montañez Sanes'         => '26317-069',
            'José Pérez González'         => '21519-069',
            'José Vélez Acosta'           => '23883-069',
            'Judith Ruland'               => '91434-020',
            'Kenneth F. Crowley'          => '90963-020',
            'Kenneth Hayes'               => '94045-020',
            'Kristin Holm'                => '93610-020',
            'Laro Nicol'                  => '80430-008',
            'Lelia Mattingly'             => '92460-020',
            'Leonard Peltier'             => '89637-132',
            'Linda Mashburn'              => '91430-020',
            'Lindis Percy'                => 'KP5753',
            'Louis Vitale'                => '25803-048',
            'Luis Barrios'                => '93613-020',
            'Lynne Greenwald'             => '40672-086',
            'Mark Colville'               => '03610-036',
            'Mark Kenney'                 => '14018-047',
            'Martha Hennessy'             => '22560-021',
            'Mary Anne Grady-Flores'      => '12001966',
            'Megan Rice'                  => '88101-020',
            'Michael D. Poulin'           => '14793-097',
            'Michael David Omondi'        => '94638-020',
            'Michael Lee Gayman'          => '92570-020',
            'Michael Walli'               => '92108-020',
            'Nancy Epling'                => '00003367',
            'Nancy Gwin'                  => '94046-020',
            'Nancy H. Smith'              => '94641-020',
            'Ozone Bhaguan'               => '92123-020',
            'Paige Belanger'              => '68132',
            'Dr. Rafil Dhafir'            => '11921-052',
            'Rita Hohenshell'             => '90280-020',
            'Robert Call'                 => '92563-020',
            'Robert Chantal'              => '92461-020',
            'Robert J. Dietrich'          => '81196-012',
            'Robin Lloyd'                 => '92572-020',
            'Samuel Foster'               => '91439-020',
            'Sarah C. Harper'             => '92571-020',
            'Scott Dempsky'               => '92568-020',
            'Shakir Hamoodi'              => '21901-045',
            'Sophie Ross'                 => '67969',
            'Stephen Douglas Clements'    => '92565-020',
            'Steve Kelly'                 => '00816-111',
            'Stephen Schweitzer'          => '93647-020',
            'Steve Baggarly'              => '03611-036',
            'Susan Crane'                 => '87783-011',
            'Teresa Grady'                => '13183-052',
            'Teri Rainelli'               => '93552-020',
            'Theresa Cusimano'            => '93611-020',
            'William Bichsel'             => '86275-020',
            // NATO 5 / NATO summit Chicago 2012 protesters (additional)
            'Christopher French'          => '2012-0522081',
            'Mark Neiweem'                => '2012-0520023',
            'Sebastian Senakiewicz'       => '2012-0520030',
            'Yonte Harris'                => '2012-0521086',
            'Raziel Azuara'               => '2012-0521087',
            // Military refusers (additional VP IDs from Iraq War)
            'Robert Alford'               => 'VP7552',
            'Elijah Smith'                => 'VP7551',
            'Elijah James Smith'          => 'VP7551',
        ];

        $updated = 0;
        $alreadySet = 0;
        $notFound = 0;

        foreach ($idMap as $name => $bopId) {
            $prisoner = Prisoner::where('name', $name)->first();

            if (! $prisoner) {
                $this->warn("Not in DB: {$name}");
                $notFound++;
                continue;
            }

            if (! empty($prisoner->inmate_number)) {
                $this->line("Already set: {$name} ({$prisoner->inmate_number})");
                $alreadySet++;
                continue;
            }

            $prisoner->inmate_number = $bopId;
            $prisoner->save();

            $this->info("Updated: {$name} → {$bopId}");
            $updated++;
        }

        $this->info("\nDone. Updated {$updated}, already set {$alreadySet}, not found {$notFound}.");

        return self::SUCCESS;
    }
}
