<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fills out Shelly Miller — anti-imperialist and Puerto Rican independence
 * activist jailed for grand-jury contempt — with a sourced bio, her case, and
 * her portrait (from The Insurgent, Winter 1986). Also merges the duplicate
 * "Shelley Miller" record into this one.
 *
 * BOP: MICHELLE MILLER, register no. 16205-053, White, female; not in BOP
 * custody as of January 30, 1987. Subpoenaed February 2, 1983; three-year
 * criminal-contempt sentence for refusing to testify. Incarceration recorded at
 * February 1983 (month precision). Idempotent.
 */
final class FillShellyMiller extends Command
{
    protected $signature = 'prisoners:fill-shelly-miller';

    protected $description = 'Fill Shelly Miller (Puerto Rican independence grand-jury contempt) and merge the duplicate record';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Shelly Miller')->first();
        if (! $prisoner) {
            $this->error('Shelly Miller not found.');

            return self::FAILURE;
        }

        $bio = 'Shelly Miller was an anti-imperialist organizer and Puerto Rican independence activist who led the New Movement in Solidarity with Puerto Rican Independence. On February 2, 1983 she was ordered to appear before the federal grand jury in Brooklyn along with activist Silvia Baraldini to testify on the activities of the Puerto Rican independence movement. She refused to testify and instead submitted a statement arguing that the grand jury was an attack on the Puerto Rican independence struggle and served no legitimate purpose. She was found guilty of criminal contempt and sentenced to three years in federal prison for her refusal to testify.';

        DB::transaction(function () use ($prisoner, $bio) {
            // Merge the duplicate "Shelley Miller" record.
            $dup = Prisoner::withUnderReview()->where('name', 'Shelley Miller')->first();
            if ($dup && $dup->id !== $prisoner->id) {
                $dup->cases()->delete();
                $dup->delete();
                $this->info('Merged duplicate: Shelley Miller (deleted).');
            }

            $prisoner->fill([
                'name' => 'Shelly Miller',
                'aka' => 'Shelley Miller / Michelle Miller',
                'first_name' => 'Shelly',
                'last_name' => 'Miller',
                'gender' => 'Female',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1980s',
                'ideologies' => ['Anti-imperialism', 'Puerto Rican independence'],
                'affiliation' => ['New Movement in Solidarity with Puerto Rican Independence and Socialism', 'Anti-imperialist movement'],
                'description' => $bio,
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $prisoner->save();

            $prisoner->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Criminal contempt — for refusing to testify before a federal grand jury in Brooklyn investigating the Puerto Rican independence movement (subpoenaed February 2, 1983, alongside Silvia Baraldini).',
                'convicted' => 'Yes — found guilty of criminal contempt for refusing to testify.',
                'sentence' => 'Three years in federal prison, held at the Federal Prison Camp, Alderson, West Virginia (BOP register no. 16205-053). No longer in BOP custody as of January 30, 1987.',
            ]);
            $case->setPartialDate('incarceration_date', 1983, 2);
            $case->setPartialDate('release_date', 1987, 1, 30);
            $case->save();

            // Attach the portrait (fair-use, from The Insurgent Winter 1986).
            $src = database_path('data/photos/nonfree/shelly-miller.jpg');
            if (is_file($src) && empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/shelly-miller.jpg', (string) file_get_contents($src));
                $prisoner->photo = 'prisoners/shelly-miller.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/shelly-miller.jpg');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled Shelly Miller.');

        return self::SUCCESS;
    }
}
