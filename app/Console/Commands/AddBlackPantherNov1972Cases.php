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
 * Political-prisoner cases drawn from The Black Panther (Black Panther Party
 * newspaper), Vol. IX No. 6, November 23, 1972:
 *
 *  - The "Atlanta 8": eight Atlanta-chapter Black Panther Party members jailed
 *    on November 9, 1972 on fabricated explosives-possession charges after a
 *    SWAT / ATF raid (page 7, "Southern Racists Attack Black Panther Party").
 *  - A portrait for David Hilliard, cropped from the parole-petition page
 *    (page 8), attached to his existing entry.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
final class AddBlackPantherNov1972Cases extends Command
{
    protected $signature = 'prisoners:add-bp-nov1972-cases';

    protected $description = 'Add the Atlanta 8 Black Panthers (Nov 9, 1972) and attach David Hilliard\'s portrait, from The Black Panther Nov 23, 1972';

    public function handle(): int
    {
        $atlanta = Institution::firstOrCreate(['name' => 'Atlanta City Jail'], ['city' => 'Atlanta', 'state' => 'Georgia'])->id;

        $context = 'was one of eight Atlanta-chapter members of the Black Panther Party arrested on November 9, 1972, when Atlanta police and federal agents — a SWAT squad and detectives from the Bureau of Alcohol, Tobacco and Firearms — raided the chapter\'s office. The stated pretext was a search for a .45-caliber pistol supposedly used to shoot a policeman on October 26; the eight were instead jailed on charges of possessing explosives, though the Party noted that no such weapon was ever found. Several of the eight were released on bail pending trial. Their arrests were reported in The Black Panther (Vol. IX No. 6, November 23, 1972).';

        // The Atlanta 8. Sex noted where the article makes it clear.
        $eight = [
            ['name' => 'Ronald Carter', 'first' => 'Ronald', 'last' => 'Carter', 'sex' => 'Male'],
            ['name' => 'Alton Deville', 'first' => 'Alton', 'last' => 'Deville', 'sex' => 'Male'],
            ['name' => 'Thomas Freeman', 'first' => 'Thomas', 'last' => 'Freeman', 'sex' => 'Male'],
            ['name' => 'George Gordon', 'first' => 'George', 'last' => 'Gordon', 'sex' => 'Male'],
            ['name' => 'Jennifer Termon', 'first' => 'Jennifer', 'last' => 'Termon', 'sex' => 'Female'],
            ['name' => 'Philip Lester', 'first' => 'Philip', 'last' => 'Lester', 'sex' => 'Male'],
            ['name' => 'Frank Scruggs', 'first' => 'Frank', 'last' => 'Scruggs', 'sex' => 'Male',
                'extra' => ' He was jailed alongside Patricia Scruggs.'],
            ['name' => 'Patricia Scruggs', 'first' => 'Patricia', 'last' => 'Scruggs', 'sex' => 'Female',
                'extra' => ' At the time of her arrest she was eight months pregnant.'],
        ];

        DB::transaction(function () use ($eight, $context, $atlanta) {
            foreach ($eight as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'last_name' => $p['last'],
                    'gender' => $p['sex'],
                    'race' => 'Black',
                    'state' => 'Georgia',
                    'era' => '1970s',
                    'ideologies' => ['Black liberation'],
                    'affiliation' => ['Black Panther Party'],
                    'description' => $p['first'].' '.$p['last'].' '.$context.($p['extra'] ?? ''),
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
                    'institution_id' => $atlanta,
                    'charges' => 'Possession of explosives — a charge the Black Panther Party said was fabricated after a SWAT/ATF raid on the Atlanta chapter office; no explosives were found.',
                    'convicted' => 'Arrested and jailed November 9, 1972; several of the eight were released on bail pending trial.',
                    'sentence' => 'Held after the November 9, 1972 raid; released on bail pending trial.',
                ]);
                $case->setPartialDate('incarceration_date', 1972, 11, 9);
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        // Attach David Hilliard's portrait (from p.8) to his existing entry.
        $hilliard = Prisoner::withUnderReview()->where('name', 'David Hilliard')->first();
        if ($hilliard) {
            if (empty($hilliard->photo)) {
                $src = database_path('data/photos/nonfree/david-hilliard.jpg');
                if (is_file($src)) {
                    Storage::disk('public')->makeDirectory('prisoners');
                    Storage::disk('public')->put('prisoners/david-hilliard.jpg', file_get_contents($src));
                    $hilliard->photo = 'prisoners/david-hilliard.jpg';
                    $hilliard->save();
                    $this->info('Attached portrait to David Hilliard.');
                }
            } else {
                $this->info('David Hilliard already has a photo; left as-is.');
            }
        } else {
            $this->warn('David Hilliard not found; skipped photo.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
