<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Updates Aubrey Cottle ("Kirtaner," Anonymous) from his Wikipedia page: sets
 * date of birth (April 6, 1987) and photo, and records the incarceration date on
 * his case — he has been held at the Central East Correctional Centre in Lindsay,
 * Ontario since October 2024 (Canadian proceedings; U.S. charges over the 2021
 * Texas GOP website hack were unsealed in March 2025). His existing biography is
 * left untouched. Idempotent; matches the live record by slug/name/aka.
 */
final class UpdateAubreyCottle extends Command
{
    protected $signature = 'prisoners:update-aubrey-cottle';

    protected $description = 'Update Aubrey Cottle: DOB, photo, and case incarceration date';

    private const SOURCE = 'images/prisoners/aubrey-cottle.jpg';

    private const PHOTO = 'prisoners/aubrey-cottle.jpg';

    public function handle(): int
    {
        $source = public_path(self::SOURCE);
        if (! is_file($source)) {
            $this->error('Source image not found: public/'.self::SOURCE);

            return self::FAILURE;
        }
        Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
        $this->info('Copied photo to public disk: '.self::PHOTO);

        $p = Prisoner::withoutGlobalScopes()
            ->where('slug', 'aubrey-cottle')
            ->orWhere('name', 'like', '%Cottle%')
            ->orWhere('aka', 'like', '%Kirtaner%')
            ->with('cases')
            ->first();

        if (! $p) {
            $this->warn('No Aubrey Cottle record found — creating a minimal one.');
            $p = new Prisoner([
                'name' => 'Aubrey Cottle', 'first_name' => 'Aubrey', 'last_name' => 'Cottle', 'aka' => 'Kirtaner',
                'gender' => 'Male', 'state' => 'Ontario', 'era' => '2020s',
                'ideologies' => ['Hacktivism'], 'affiliation' => ['Anonymous'],
                'in_custody' => true, 'released' => false, 'under_review' => false,
                'description' => 'Aubrey Cottle, known online as "Kirtaner," is a Canadian website administrator and self-described early member of the hacktivist collective Anonymous, and the founder of 420chan. He was charged in the Western District of Texas over the 2021 hack and defacement of the Republican Party of Texas\'s website (charges unsealed March 2025).',
            ]);
        }

        $p->setPartialDate('birthdate', 1987, 4, 6);
        $p->photo = self::PHOTO;
        $p->in_custody = true;
        $p->released = false;
        $p->save();
        $this->info("Set DOB (1987-04-06) and photo on {$p->name}.");

        // Record the incarceration date on his case (create one if none).
        $case = $p->cases->first() ?? $p->cases()->make([
            'charges' => 'Hacking and defacement of the Republican Party of Texas website (2021); related Canadian charges (mischief to data, breach of bail).',
            'convicted' => 'Pleaded guilty (Canadian proceedings)',
        ]);
        if (! $case->institution_id) {
            $inst = Institution::firstOrCreate(
                ['name' => 'Central East Correctional Centre'],
                ['city' => 'Lindsay', 'state' => 'Ontario'],
            );
            $case->institution_id = $inst->id;
        }
        $case->setPartialDate('incarceration_date', 2024, 10); // held since October 2024
        $case->save();
        $this->info('Set incarceration date (October 2024) on his case.');

        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
