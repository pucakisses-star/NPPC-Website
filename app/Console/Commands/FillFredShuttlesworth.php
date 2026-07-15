<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Expands the Rev. Fred Shuttlesworth entry — Birmingham civil-rights leader,
 * ACMHR co-founder, and SCLC figure — with his full name, birth/death dates
 * (Mar 18, 1922 – Oct 5, 2011), a fuller biography, his Searchable Museum
 * portrait, and an incarceration ledger of his many arrests. He was arrested
 * more than 30 times; this records the documented and datable episodes, noting
 * where the sentence was imposed but actual time served is not verified (several
 * convictions were later reversed by the U.S. Supreme Court).
 *
 * Idempotent — rebuilds the case list and refreshes the portrait each run.
 */
final class FillFredShuttlesworth extends Command
{
    protected $signature = 'prisoners:fill-fred-shuttlesworth';

    protected $description = 'Fill Fred Shuttlesworth: dates, bio, portrait, and full arrest ledger';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Fred Shuttlesworth')->first();
        if (! $prisoner) {
            $this->error('Fred Shuttlesworth not found.');

            return self::FAILURE;
        }

        $bham = Institution::firstOrCreate(['name' => 'Birmingham City Jail'], ['city' => 'Birmingham', 'state' => 'Alabama'])->id;
        $mont = Institution::firstOrCreate(['name' => 'Montgomery City Jail'], ['city' => 'Montgomery', 'state' => 'Alabama'])->id;

        $bio = 'Rev. Fred Shuttlesworth was one of the central leaders of the Birmingham civil-rights movement. He became pastor of Bethel Baptist Church in Birmingham in 1953, helped create the Alabama Christian Movement for Human Rights in 1956 after Alabama banned the NAACP, and later became a key SCLC leader. He worked with CORE during the Freedom Rides, helped force the Birmingham desegregation crisis of 1963, and later helped organize the Selma-to-Montgomery voting-rights campaign. Martin Luther King Jr. called him "the most courageous civil rights fighter in the South." He was arrested and jailed more than 30 separate times for his activism.';

        DB::transaction(function () use ($prisoner, $bio, $bham, $mont) {
            $prisoner->fill([
                'first_name' => 'Fred',
                'middle_name' => 'Lee',
                'last_name' => 'Shuttlesworth',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'Alabama',
                'era' => '1960s',
                'description' => $bio,
                'in_custody' => false,
                'released' => true,
            ]);
            $prisoner->setPartialDate('birthdate', 1922, 3, 18);
            $prisoner->setPartialDate('death_date', 2011, 10, 5);
            $prisoner->save();

            $prisoner->cases()->delete();

            foreach ($this->cases($bham, $mont) as $c) {
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $c['inst'],
                    'charges' => $c['charges'],
                    'convicted' => $c['convicted'],
                    'sentence' => $c['sentence'],
                ]);
                foreach (['arrest_date', 'sentenced_date', 'incarceration_date', 'release_date'] as $f) {
                    if (! empty($c[$f])) {
                        $case->setPartialDate($f, ...$c[$f]);
                    }
                }
                $case->save();
            }

            // Refresh the portrait (fair-use, Searchable Museum / NMAAHC).
            $src = database_path('data/photos/nonfree/fred-shuttlesworth.jpg');
            if (is_file($src)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/fred-shuttlesworth.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/fred-shuttlesworth.jpg';
                $prisoner->save();
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled Fred Shuttlesworth with '.count($this->cases($bham, $mont)).' cases.');

        return self::SUCCESS;
    }

    private function cases(string $bham, string $mont): array
    {
        return [
            [
                'inst' => $bham,
                'charges' => 'Violating Birmingham\'s bus-segregation ordinance — arrested with 21 others the day after his Bethel Baptist parsonage was bombed, in the campaign to desegregate the city buses.',
                'convicted' => 'Arrested and booked.',
                'sentence' => 'Held in brief booking custody; exact jail duration not documented.',
                'arrest_date' => [1956, 12, 26], 'incarceration_date' => [1956, 12, 26],
            ],
            [
                'inst' => $bham,
                'charges' => 'Violating Birmingham\'s bus-segregation laws — the October 1958 bus-seating case, tried with Rev. J. S. Phifer.',
                'convicted' => 'Yes — convicted and sentenced to 90 days (Phifer received 60). After the Supreme Court initially declined review, they surrendered on January 25, 1962.',
                'sentence' => 'Ninety days at hard labor. Served 36 days, January 25 – March 1, 1962 (released on $300 bond on March 1). After William Kunstler\'s federal habeas petition led the Supreme Court to direct that bail be available, he served no further time.',
                'sentenced_date' => [1958, 10], 'incarceration_date' => [1962, 1, 25], 'release_date' => [1962, 3, 1],
            ],
            [
                'inst' => $bham,
                'charges' => 'Vagrancy and aiding and abetting others to break the law — arrested March 31, 1960 during the Birmingham sit-in campaign and again on April 2 (Shuttlesworth & Billups v. City of Birmingham).',
                'convicted' => 'Yes — convicted April 4, 1960; the conviction was reversed by the U.S. Supreme Court in 1963.',
                'sentence' => '180 days at hard labor plus a $100 fine. Actual time served is not documented; the conviction was overturned.',
                'arrest_date' => [1960, 3, 31], 'sentenced_date' => [1960, 4, 4], 'incarceration_date' => [1960, 3, 31],
            ],
            [
                'inst' => $bham,
                'charges' => 'Interfering with a police officer — arrested during the Freedom Rides after objecting when Commissioner Bull Connor ordered the riders arrested.',
                'convicted' => 'Yes — convicted; the conviction was reversed by the Supreme Court in 1964.',
                'sentence' => '180 days at hard labor; actual time served not documented (conviction reversed).',
                'incarceration_date' => [1961, 5],
            ],
            [
                'inst' => $bham,
                'charges' => 'Refusing to move — arrested at the Birmingham Greyhound station when he tried to board the bus with the Freedom Riders.',
                'convicted' => 'Arrested.',
                'sentence' => 'Jail duration not documented.',
                'incarceration_date' => [1961, 5, 20],
            ],
            [
                'inst' => $mont,
                'charges' => 'Arrested with seven Freedom Riders and two other local leaders after requesting service at the Montgomery bus-station lunch counter (arrest no. 11941).',
                'convicted' => 'Booked / arrested.',
                'sentence' => 'Release time not documented.',
                'incarceration_date' => [1961, 5, 25],
            ],
            [
                'inst' => $bham,
                'charges' => 'Refusing to promise not to violate the segregation laws for the coming year.',
                'convicted' => 'Yes.',
                'sentence' => 'One day in jail plus a $10 fine.',
                'incarceration_date' => [1961, 6, 2], 'release_date' => [1961, 6, 3],
            ],
            [
                'inst' => $bham,
                'charges' => 'Loitering / refusing an officer\'s order to "move on" — arrested near a downtown department store (Shuttlesworth v. City of Birmingham, 382 U.S. 87).',
                'convicted' => 'Yes — sentenced; conviction reversed by the Supreme Court in 1965.',
                'sentence' => '180 days at hard labor, plus 61 days in default of the fine and costs. Actual time served not documented (conviction reversed).',
                'arrest_date' => [1962, 4, 4], 'sentenced_date' => [1962, 4, 4], 'incarceration_date' => [1962, 4, 4],
            ],
            [
                'inst' => $bham,
                'charges' => 'Arrested in connection with the first march on city hall of the 1963 Birmingham Campaign, which he led.',
                'convicted' => 'Arrested.',
                'sentence' => 'Custody time not documented.',
                'incarceration_date' => [1963, 4, 6],
            ],
            [
                'inst' => $bham,
                'charges' => 'Parading without a permit — for helping lead a Good Friday civil-rights march of 52 people without a permit, alongside Martin Luther King Jr. and Ralph Abernathy (Shuttlesworth v. City of Birmingham, 394 U.S. 147).',
                'convicted' => 'Yes — convicted under Birmingham\'s parade ordinance; the conviction was reversed by the Supreme Court in 1969.',
                'sentence' => '90 days at hard labor, plus 48 days in default of the fine and costs. Actual time served not documented (conviction reversed).',
                'arrest_date' => [1963, 4, 12], 'incarceration_date' => [1963, 4, 12],
            ],
            [
                'inst' => $bham,
                'charges' => 'Criminal contempt — for violating a state-court injunction against the Birmingham marches (Walker v. City of Birmingham, 388 U.S. 307).',
                'convicted' => 'Yes — the contempt convictions were affirmed by the Supreme Court in 1967, and the defendants were ordered back to the Birmingham jail to serve the sentence.',
                'sentence' => 'Five days in jail plus a $50 fine. His exact service dates are not documented.',
                'sentenced_date' => [1963, 4],
            ],
        ];
    }
}
