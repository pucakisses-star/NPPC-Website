<?php

namespace App\Console\Commands;

use App\Models\Petition;
use App\Models\PetitionSignature;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Seeds demo signatures on the 17 non-Peltier petitions, scaled by
 * the rough public interest each campaign carries. Counts range from
 * 75 (smaller individual-clemency campaigns) to 500 (Mumia,
 * Assange, Bushnell, Aafia, Cop City RICO, Gaza encampments).
 *
 * All demo signatures use the @nppc-demo.test email pattern so they
 * can be filtered out or purged later with one line of tinker.
 *
 * Each petition's sign timestamps land within a ~3-month window
 * starting at the petition's anchor date (created_at), with a sqrt
 * front-load so launch week is heaviest.
 *
 * Idempotent + safe to re-run — uses --target=N semantics per slug
 * (trim down if existing demo sigs exceed target).
 */
final class SeedOtherPetitionSignatures extends Command {
    protected $signature = 'archive:seed-other-petition-signatures
        {--slug= : Only seed this one slug}
        {--apply : Required for the seed to actually write}';
    protected $description = 'Seed demo signatures on the non-Peltier petitions (75-500 each, scaled by public interest)';

    /** slug => target demo-signature count */
    private array $targets = [
        // High interest (300-500)
        'free-mumia-abu-jamal'                          => 500,
        'pardon-julian-assange'                         => 450,
        'aaron-bushnell-memorial-ceasefire'             => 480,
        'drop-charges-gaza-encampment-defendants'       => 420,
        'free-aafia-siddiqui'                           => 400,
        'drop-stop-cop-city-rico-charges'               => 380,
        'justice-for-tortuguita'                        => 350,

        // Medium interest (150-300)
        'full-pardon-chelsea-manning'                   => 280,
        'pardon-steven-donziger'                        => 230,
        'free-marius-mason'                             => 200,
        'pardon-daniel-hale'                            => 200,
        'end-espionage-act-prosecutions-of-journalists' => 180,
        'restore-physical-mail-prisons'                 => 140,
        'end-bop-communications-management-units'       => 130,

        // Lower interest (75-110)
        'free-veronza-bowers'                           => 110,
        'medical-clemency-kamau-sadiki'                 => 95,
        'free-oso-blanco-byron-chubbuck'                => 80,
    ];

    private const FIRST_NAMES = [
        'Alex','Sam','Jamie','Taylor','Jordan','Casey','Morgan','Quinn',
        'Riley','Avery','Skyler','Drew','Hayden','Reese','Charlie',
        'Mary','Patricia','Jennifer','Linda','Elizabeth','Barbara','Susan',
        'Jessica','Sarah','Karen','Nancy','Lisa','Margaret','Sandra',
        'James','Robert','John','Michael','William','David','Richard',
        'Joseph','Thomas','Charles','Christopher','Daniel','Matthew',
        'Anthony','Mark','Donald','Steven','Andrew','Paul','Joshua',
        'Maria','Carlos','Luis','Ana','Miguel','Sofia','Diego','Camila',
        'Mateo','Isabella','Sebastián','Valentina','Lucía','Emilio',
        'Aiyana','Tahkeome','Winona','Dakota','Cheyenne','Sakari',
        'DeShawn','Imani','Malik','Zora','Jamal','Aaliyah','Tyrone',
        'Latoya','Andre','Tiana','Marcus','Keisha','Darnell',
        'Wei','Mei','Hiroshi','Yuki','Jin','Min','Priya','Arjun',
        'Fatima','Ahmed','Layla','Omar','Aisha','Hassan',
        'River','Sage','Phoenix','Sky','Rowan','Ash','Wren',
    ];

    private const LAST_NAMES = [
        'Smith','Johnson','Williams','Brown','Jones','Garcia','Miller',
        'Davis','Rodriguez','Martinez','Hernandez','Lopez','Gonzalez',
        'Wilson','Anderson','Thomas','Taylor','Moore','Jackson','Martin',
        'Lee','Perez','Thompson','White','Harris','Sanchez','Clark',
        'Ramirez','Lewis','Robinson','Walker','Young','Allen','King',
        'Wright','Scott','Torres','Nguyen','Hill','Flores','Green',
        'Adams','Nelson','Baker','Hall','Rivera','Campbell','Mitchell',
        'Carter','Roberts','Gomez','Phillips','Evans','Turner','Diaz',
        'Parker','Cruz','Edwards','Collins','Reyes','Stewart','Morris',
        'Morales','Murphy','Cook','Rogers','Gutierrez','Ortiz','Morgan',
        'Cooper','Peterson','Bailey','Reed','Kelly','Howard','Ramos',
        'Chen','Wang','Patel','Singh','Khan','Ali','Park','Kim',
        'Washington','Jefferson','Du Bois','Hamer','Garvey',
    ];

    /** @var array<int, array{0:string,1:string}> */
    private const CITIES = [
        ['Minneapolis','Minnesota'],['Saint Paul','Minnesota'],
        ['Rapid City','South Dakota'],['Sioux Falls','South Dakota'],
        ['Bismarck','North Dakota'],['Fargo','North Dakota'],
        ['Albuquerque','New Mexico'],['Santa Fe','New Mexico'],
        ['Phoenix','Arizona'],['Tucson','Arizona'],
        ['Oklahoma City','Oklahoma'],['Tulsa','Oklahoma'],
        ['Seattle','Washington'],['Olympia','Washington'],['Spokane','Washington'],
        ['Portland','Oregon'],['Eugene','Oregon'],
        ['Oakland','California'],['Berkeley','California'],['Los Angeles','California'],
        ['San Francisco','California'],['San Diego','California'],['Fresno','California'],
        ['Denver','Colorado'],['Boulder','Colorado'],
        ['Chicago','Illinois'],['Evanston','Illinois'],
        ['New York','New York'],['Brooklyn','New York'],['Albany','New York'],['Buffalo','New York'],
        ['Philadelphia','Pennsylvania'],['Pittsburgh','Pennsylvania'],
        ['Washington','District of Columbia'],
        ['Boston','Massachusetts'],['Cambridge','Massachusetts'],
        ['Atlanta','Georgia'],['Athens','Georgia'],['Savannah','Georgia'],
        ['Austin','Texas'],['Houston','Texas'],['Dallas','Texas'],['El Paso','Texas'],
        ['Madison','Wisconsin'],['Milwaukee','Wisconsin'],
        ['Detroit','Michigan'],['Ann Arbor','Michigan'],
        ['Asheville','North Carolina'],['Durham','North Carolina'],['Charlotte','North Carolina'],
        ['Honolulu','Hawaii'],['Anchorage','Alaska'],
        ['Missoula','Montana'],['Bozeman','Montana'],
        ['Salt Lake City','Utah'],
        ['Las Vegas','Nevada'],
    ];

    private const MESSAGES = [
        '','','','','','','','',
        'Justice now.',
        'I stand in solidarity.',
        'Free them.',
        'Drop the charges.',
        'This is overdue.',
        'Please act before more time is lost.',
        'For the next generation.',
        'No more delay.',
    ];

    public function handle(): int {
        if (! $this->option('apply')) {
            $this->warn('Dry-run; pass --apply to actually write. Targets:');
            foreach ($this->targets as $slug => $n) {
                $this->line(sprintf('  %-50s  →  %d', $slug, $n));
            }
            return self::SUCCESS;
        }

        $onlySlug = $this->option('slug');
        $totalAdded = 0; $totalTrimmed = 0;

        foreach ($this->targets as $slug => $target) {
            if ($onlySlug && $slug !== $onlySlug) continue;

            $petition = Petition::where('slug', $slug)->first();
            if (! $petition) {
                $this->warn("SKIP: petition not found — {$slug}");
                continue;
            }

            $existing = PetitionSignature::where('petition_id', $petition->id)
                ->where('email', 'like', '%@nppc-demo.test')
                ->count();

            // Window: petition created_at to created_at + 90 days.
            $start = $petition->created_at ? Carbon::parse($petition->created_at) : Carbon::now()->subMonths(3);
            $end   = (clone $start)->addDays(90);
            $totalSeconds = max(1, $end->diffInSeconds($start));

            if ($existing > $target) {
                $toDelete = $existing - $target;
                $ids = PetitionSignature::where('petition_id', $petition->id)
                    ->where('email', 'like', '%@nppc-demo.test')
                    ->orderByDesc('created_at')
                    ->limit($toDelete)
                    ->pluck('id');
                PetitionSignature::whereIn('id', $ids)->delete();
                $totalTrimmed += $toDelete;
                $this->line(sprintf('TRIM  %-50s  existing=%d → target=%d (deleted %d)', $slug, $existing, $target, $toDelete));
                continue;
            }

            $toAdd = $target - $existing;
            if ($toAdd <= 0) {
                $this->line(sprintf('OK    %-50s  already at %d/%d', $slug, $existing, $target));
                continue;
            }

            $added = 0;
            for ($i = 0; $i < $toAdd; $i++) {
                $first = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
                $last  = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
                [$city, $state] = self::CITIES[array_rand(self::CITIES)];
                $email = strtolower(preg_replace('/[^a-z0-9]/i', '.', $first.'.'.$last)).'-'.Str::random(6).'@nppc-demo.test';

                if (PetitionSignature::where('petition_id', $petition->id)->where('email', $email)->exists()) continue;

                $offsetSeconds = (int) (sqrt(mt_rand() / mt_getrandmax()) * $totalSeconds);
                $signedAt = (clone $start)->addSeconds($offsetSeconds);

                $sig = new PetitionSignature();
                $sig->petition_id    = $petition->id;
                $sig->first_name     = $first;
                $sig->last_name      = $last;
                $sig->email          = $email;
                $sig->city           = $city;
                $sig->state          = $state;
                $sig->zip_code       = (string) random_int(10000, 99999);
                $sig->custom_message = self::MESSAGES[array_rand(self::MESSAGES)] ?: null;
                $sig->display_publicly = mt_rand(1, 100) <= 70;
                $sig->save();

                $sig->created_at = $signedAt;
                $sig->updated_at = $signedAt;
                $sig->saveQuietly();

                $added++;
            }

            $totalAdded += $added;
            $this->line(sprintf('ADD   %-50s  +%d → %d/%d', $slug, $added, $existing + $added, $target));
        }

        $this->newLine();
        $this->info("Done — added {$totalAdded}, trimmed {$totalTrimmed} demo signature(s).");
        $this->line('To purge ALL demo sigs later:');
        $this->line("  PetitionSignature::where('email','like','%@nppc-demo.test')->delete();");

        return self::SUCCESS;
    }
}
