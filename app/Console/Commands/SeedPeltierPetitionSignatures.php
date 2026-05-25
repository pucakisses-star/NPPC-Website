<?php

namespace App\Console\Commands;

use App\Models\Petition;
use App\Models\PetitionSignature;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Seeds ~250 demo signatures on the Free Leonard Peltier petition,
 * weighted toward the September 12, 2024 launch with a long tail
 * through to today.
 *
 * All demo signatures use the email pattern <handle>@nppc-demo.test
 * so they can be filtered out or purged later.
 *
 * Idempotent — skips any signature whose email already exists for
 * this petition.
 */
final class SeedPeltierPetitionSignatures extends Command {
    protected $signature = 'archive:seed-peltier-petition-signatures {--count=250}';
    protected $description = 'Seed demo signatures on the Free Leonard Peltier petition';

    private const FIRST_NAMES = [
        'Alex', 'Sam', 'Jamie', 'Taylor', 'Jordan', 'Casey', 'Morgan', 'Quinn',
        'Riley', 'Avery', 'Skyler', 'Drew', 'Hayden', 'Reese', 'Charlie',
        'Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan',
        'Jessica', 'Sarah', 'Karen', 'Nancy', 'Lisa', 'Margaret', 'Sandra',
        'James', 'Robert', 'John', 'Michael', 'William', 'David', 'Richard',
        'Joseph', 'Thomas', 'Charles', 'Christopher', 'Daniel', 'Matthew',
        'Anthony', 'Mark', 'Donald', 'Steven', 'Andrew', 'Paul', 'Joshua',
        'Maria', 'Carlos', 'Luis', 'Ana', 'Miguel', 'Sofia', 'Diego', 'Camila',
        'Mateo', 'Isabella', 'Sebastián', 'Valentina', 'Lucía', 'Emilio',
        'Aiyana', 'Tahkeome', 'Winona', 'Dakota', 'Cheyenne', 'Sakari',
        'Mahkah', 'Aponi', 'Nadie', 'Halona', 'Wematin', 'Yenene',
        'DeShawn', 'Imani', 'Malik', 'Zora', 'Jamal', 'Aaliyah', 'Tyrone',
        'Latoya', 'Andre', 'Tiana', 'Marcus', 'Keisha', 'Darnell',
        'Wei', 'Mei', 'Hiroshi', 'Yuki', 'Jin', 'Min', 'Priya', 'Arjun',
        'Fatima', 'Ahmed', 'Layla', 'Omar', 'Aisha', 'Hassan',
        'River', 'Sage', 'Phoenix', 'Sky', 'Rowan', 'Ash', 'Wren',
    ];

    private const LAST_NAMES = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller',
        'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez',
        'Wilson', 'Anderson', 'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin',
        'Lee', 'Perez', 'Thompson', 'White', 'Harris', 'Sanchez', 'Clark',
        'Ramirez', 'Lewis', 'Robinson', 'Walker', 'Young', 'Allen', 'King',
        'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill', 'Flores', 'Green',
        'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell', 'Mitchell',
        'Carter', 'Roberts', 'Gomez', 'Phillips', 'Evans', 'Turner', 'Diaz',
        'Parker', 'Cruz', 'Edwards', 'Collins', 'Reyes', 'Stewart', 'Morris',
        'Morales', 'Murphy', 'Cook', 'Rogers', 'Gutierrez', 'Ortiz', 'Morgan',
        'Cooper', 'Peterson', 'Bailey', 'Reed', 'Kelly', 'Howard', 'Ramos',
        'Two Bulls', 'Eagle Heart', 'Spotted Tail', 'Red Cloud', 'Brave Bull',
        'Iron Shell', 'Black Elk', 'Bear Runner', 'Means', 'Trudell',
        'Banks', 'Walking Eagle', 'Standing Bear', 'Yellow Hawk', 'Looking Horse',
        'Chen', 'Wang', 'Patel', 'Singh', 'Khan', 'Ali', 'Park', 'Kim',
        'Washington', 'Jefferson', 'Jackson', 'Du Bois', 'Hamer', 'Garvey',
    ];

    /** @var array<int, array{0:string,1:string}> [city, state] */
    private const CITIES = [
        ['Minneapolis', 'Minnesota'], ['Saint Paul', 'Minnesota'], ['Duluth', 'Minnesota'],
        ['Rapid City', 'South Dakota'], ['Pine Ridge', 'South Dakota'], ['Sioux Falls', 'South Dakota'],
        ['Bismarck', 'North Dakota'], ['Belcourt', 'North Dakota'], ['Fargo', 'North Dakota'],
        ['Albuquerque', 'New Mexico'], ['Santa Fe', 'New Mexico'], ['Gallup', 'New Mexico'],
        ['Phoenix', 'Arizona'], ['Tucson', 'Arizona'], ['Flagstaff', 'Arizona'], ['Window Rock', 'Arizona'],
        ['Oklahoma City', 'Oklahoma'], ['Tulsa', 'Oklahoma'], ['Tahlequah', 'Oklahoma'],
        ['Seattle', 'Washington'], ['Olympia', 'Washington'], ['Spokane', 'Washington'],
        ['Portland', 'Oregon'], ['Eugene', 'Oregon'], ['Bend', 'Oregon'],
        ['Oakland', 'California'], ['Berkeley', 'California'], ['Los Angeles', 'California'],
        ['San Francisco', 'California'], ['San Diego', 'California'], ['Fresno', 'California'],
        ['Denver', 'Colorado'], ['Boulder', 'Colorado'], ['Colorado Springs', 'Colorado'],
        ['Chicago', 'Illinois'], ['Evanston', 'Illinois'], ['Urbana', 'Illinois'],
        ['New York', 'New York'], ['Brooklyn', 'New York'], ['Albany', 'New York'], ['Buffalo', 'New York'],
        ['Philadelphia', 'Pennsylvania'], ['Pittsburgh', 'Pennsylvania'], ['State College', 'Pennsylvania'],
        ['Washington', 'District of Columbia'],
        ['Boston', 'Massachusetts'], ['Cambridge', 'Massachusetts'], ['Northampton', 'Massachusetts'],
        ['Burlington', 'Vermont'], ['Brattleboro', 'Vermont'],
        ['Atlanta', 'Georgia'], ['Athens', 'Georgia'], ['Savannah', 'Georgia'],
        ['Austin', 'Texas'], ['Houston', 'Texas'], ['Dallas', 'Texas'], ['El Paso', 'Texas'],
        ['Madison', 'Wisconsin'], ['Milwaukee', 'Wisconsin'], ['Eau Claire', 'Wisconsin'],
        ['Detroit', 'Michigan'], ['Ann Arbor', 'Michigan'], ['Marquette', 'Michigan'],
        ['Asheville', 'North Carolina'], ['Durham', 'North Carolina'], ['Charlotte', 'North Carolina'],
        ['Honolulu', 'Hawaii'], ['Hilo', 'Hawaii'],
        ['Anchorage', 'Alaska'], ['Juneau', 'Alaska'], ['Fairbanks', 'Alaska'],
        ['Missoula', 'Montana'], ['Bozeman', 'Montana'], ['Browning', 'Montana'],
        ['Salt Lake City', 'Utah'], ['Moab', 'Utah'],
        ['Las Vegas', 'Nevada'], ['Reno', 'Nevada'],
        ['Toronto', 'Ontario'], ['Vancouver', 'British Columbia'], ['Winnipeg', 'Manitoba'],
    ];

    private const SAMPLE_MESSAGES = [
        '', '', '', '', '', '', '', '',
        'Free Leonard. He has paid more than enough.',
        'It is long past time for justice. Pardon Leonard Peltier.',
        'Bring our elder home.',
        'No more delay. Pardon now.',
        'For my children and my grandchildren — pardon Leonard.',
        'Justice for Leonard. Justice for Pine Ridge.',
        'The American Indian Movement\'s prisoners are this country\'s shame.',
        'Mr. President, please act before it is too late.',
        '49 years is enough.',
        'Mitakuye Oyasin. Free Leonard Peltier.',
    ];

    public function handle(): int {
        $petition = Petition::where('slug', 'free-leonard-peltier')->first();
        if (! $petition) {
            $this->error('Petition not found — run archive:add-peltier-petition first.');
            return self::FAILURE;
        }

        $count = max(1, (int) $this->option('count'));
        $start = Carbon::parse('2024-09-12 09:00:00');
        $end   = now();
        $totalSeconds = $end->diffInSeconds($start);

        $added = 0; $skipped = 0;

        for ($i = 0; $i < $count; $i++) {
            $first = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
            $last  = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
            [$city, $state] = self::CITIES[array_rand(self::CITIES)];
            $email = strtolower(preg_replace('/[^a-z0-9]/i', '.', $first.'.'.$last)).'-'.Str::random(6).'@nppc-demo.test';

            if (PetitionSignature::where('petition_id', $petition->id)->where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            // Weighted toward the early days of the campaign (sqrt distribution
            // produces a heavy front-load of timestamps).
            $r = mt_rand() / mt_getrandmax();
            $offsetSeconds = (int) (sqrt($r) * $totalSeconds);
            $signedAt = (clone $start)->addSeconds($offsetSeconds);

            $sig = new PetitionSignature();
            $sig->petition_id      = $petition->id;
            $sig->first_name       = $first;
            $sig->last_name        = $last;
            $sig->email            = $email;
            $sig->city             = $city;
            $sig->state            = $state;
            $sig->zip_code         = (string) random_int(10000, 99999);
            $sig->custom_message   = self::SAMPLE_MESSAGES[array_rand(self::SAMPLE_MESSAGES)] ?: null;
            $sig->display_publicly = mt_rand(1, 100) <= 70; // ~70% opt in (mirrors realistic petition data)
            $sig->save();

            $sig->created_at = $signedAt;
            $sig->updated_at = $signedAt;
            $sig->saveQuietly();

            $added++;
        }

        $this->info("Done — added {$added} demo signatures, skipped {$skipped} duplicates.");
        $this->line('All demo signatures use @nppc-demo.test emails so they can be purged later with:');
        $this->line("  PetitionSignature::where('petition_id', '{$petition->id}')->where('email','like','%@nppc-demo.test')->delete();");

        return self::SUCCESS;
    }
}
