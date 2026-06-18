<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Adds (and enriches) animal-liberation prisoners from a March 6, 1998
 * ALF prisoner-support mailing list, from database/data/alf-1998-prisoners.json.
 *
 * New names are created in full; names already in the database are left as-is
 * except for filling in a missing inmate number and attaching the listed
 * facility (with its mailing address) to a case that has no institution.
 * Idempotent.
 */
class AddAlf1998Prisoners extends Command
{
    protected $signature = 'prisoners:add-alf-1998';

    protected $description = 'Add/enrich animal-liberation prisoners from the March 1998 ALF support list';

    public function handle(): int
    {
        $file = database_path('data/alf-1998-prisoners.json');
        if (! file_exists($file)) {
            $this->error('alf-1998-prisoners.json not found.');

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error("Could not parse {$file}");

            return self::FAILURE;
        }

        $added = 0;
        $enriched = 0;

        foreach ($rows as $r) {
            $prisoner = Prisoner::withUnderReview()->where('name', $r['name'])->first();

            if (! $prisoner) {
                $prisoner = Prisoner::create([
                    'name' => $r['name'],
                    'first_name' => $r['first_name'] ?? null,
                    'last_name' => $r['last_name'] ?? null,
                    'description' => $r['bio'] ?? null,
                    'gender' => $r['gender'] ?? null,
                    'race' => $r['race'] ?? null,
                    'state' => $r['state'] ?? null,
                    'era' => $r['era'] ?? '1990s',
                    'ideologies' => $r['ideologies'] ?? [],
                    'affiliation' => $r['affiliation'] ?? [],
                    'in_custody' => $r['in_custody'] ?? false,
                    'released' => $r['released'] ?? true,
                    'awaiting_trial' => false,
                ]);
                $this->info("Added: {$r['name']}");
                $added++;
            } else {
                $this->line("Exists: {$r['name']} (enriching)");
                $enriched++;
            }

            // Fill in a missing inmate number.
            if (! empty($r['inmate_number']) && empty($prisoner->inmate_number)) {
                $prisoner->inmate_number = $r['inmate_number'];
                $prisoner->save();
            }

            // Facility + mailing address.
            $inst = null;
            if (! empty($r['institution']['name'])) {
                $i = $r['institution'];
                $inst = Institution::firstOrCreate(
                    ['name' => $i['name']],
                    ['city' => $i['city'] ?? null, 'state' => $i['state'] ?? null, 'mailing_address' => $i['mailing_address'] ?? null]
                );
                if (empty($inst->mailing_address) && ! empty($i['mailing_address'])) {
                    $inst->mailing_address = $i['mailing_address'];
                    $inst->city = $inst->city ?: ($i['city'] ?? null);
                    $inst->state = $inst->state ?: ($i['state'] ?? null);
                    $inst->save();
                }
            }

            // Ensure a case; create one for new prisoners, then attach the
            // facility to any case that lacks an institution.
            $case = $prisoner->cases()->first();
            if (! $case) {
                $case = PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges' => $r['charges'] ?? null,
                    'sentence' => $r['sentence'] ?? null,
                ]);
            }
            if ($inst && empty($case->institution_id)) {
                $case->institution_id = $inst->id;
                $case->save();
            }
        }

        $this->info("\nDone. Added={$added} Enriched={$enriched}");

        return self::SUCCESS;
    }
}
