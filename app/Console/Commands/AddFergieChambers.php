<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Fergie Chambers (James Cox Chambers Jr., b. c. 1985) — a Cox Enterprises heir
 * who left the family media company in 2023 with a reported ~$250 million payout
 * and became an openly communist financier of the U.S. left. On July 10, 2026 he
 * was arrested in Ibiza, Spain, at the request of the U.S. Department of Justice
 * and held pending extradition on a sealed federal indictment charging
 * international money laundering to provide material support to foreign terrorist
 * organizations — charges tied to his funding of pro-Palestinian direct action
 * (Palestine Action US / Unity of Fields) and reportedly to fund transfers routed
 * through Tunisia. He has not been tried; supporters and family describe the case
 * as politically motivated repression of a pro-Palestine donor.
 *
 * Idempotent: skips creating the record if a "Fergie Chambers" already exists,
 * but will still attach the committed non-free portrait if it is missing.
 * Sources: Wikipedia ("Fergie Chambers"); The Grayzone (July 12, 2026); Air Mail
 * ("Heir to the Revolution," 2024). Portrait: see CREDITS-nonfree.md.
 */
final class AddFergieChambers extends Command
{
    protected $signature = 'prisoners:add-fergie-chambers';

    protected $description = 'Add Fergie Chambers, the Cox heir/communist financier arrested in Spain pending US extradition (July 2026)';

    private const PHOTO_FILE = 'chambers-fergie.jpg';

    public function handle(): int
    {
        $name = 'Fergie Chambers';

        DB::transaction(function () use ($name) {
            $existing = Prisoner::withUnderReview()->where('name', $name)->first();

            if ($existing) {
                $this->warn('Skipped (already exists): '.$name);
                $this->attachPhoto($existing);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Fergie',
                'last_name' => 'Chambers',
                'aka' => 'James Cox Chambers Jr.',
                'description' => 'Fergie Chambers (James Cox Chambers Jr.) is an American communist activist and financier '
                    .'and an heir to the Cox family fortune built on Cox Enterprises. He separated from the company in '
                    .'2023, reportedly receiving a payout estimated at more than $250 million, and used that wealth to '
                    .'bankroll the U.S. far left. He founded the Berkshire Communists collective and ran a communal farm '
                    .'and "people\'s gym" in Alford, Massachusetts, and later launched the Babochki Collective grantmaking '
                    .'initiative. He became one of the largest private donors to pro-Palestinian and anti-police-militarization '
                    .'organizing — funding bail, legal defense, and direct-action campaigns associated with Palestine Action US '
                    .'(later Unity of Fields), the Stop Cop City movement, and protests against the Israeli defense firm Elbit '
                    .'Systems. An outspoken and polarizing figure, he has openly identified as a Marxist–Leninist. On July 10, '
                    .'2026, Spanish National Police arrested him in Ibiza at the request of the U.S. Department of Justice, and '
                    .'a court ordered him held pending an extradition hearing. According to reporting on a sealed federal '
                    .'indictment, he faces charges of international money laundering with intent to provide material support and '
                    .'resources to foreign terrorist organizations, arising from fund transfers connected to his Palestinian '
                    .'solidarity work; if extradited and convicted he could face up to 30 years in prison. He has not been tried, '
                    .'and his family and supporters characterize the prosecution as politically motivated repression of a '
                    .'pro-Palestine donor. The specific factual allegations underlying the indictment had not been publicly '
                    .'disclosed as of his arrest.',
                'state' => 'Massachusetts',
                'era' => '2020s',
                'gender' => 'Male',
                'ideologies' => ['Communism', 'Marxism–Leninism', 'Anti-imperialism'],
                'affiliation' => ['Berkshire Communists', 'Babochki Collective', 'Palestine Action US'],
                'in_custody' => true,
                'released' => false,
                'in_exile' => false,
                'awaiting_trial' => true,
            ]);

            // Year-only birth date (widely reported as circa 1985) -> year precision.
            $prisoner->setPartialDate('birthdate', 1985);
            $prisoner->save();

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'International money laundering with intent to provide material support and resources to '
                    .'foreign terrorist organizations (sealed U.S. federal indictment), tied to fund transfers connected '
                    .'to his financing of pro-Palestinian activism. Arrested in Ibiza, Spain, on July 10, 2026 at the '
                    .'request of the U.S. Department of Justice and held pending extradition.',
                'arrest_date' => '2026-07-10',
                'convicted' => 'Not convicted — charges pending. Held in Spanish custody after his July 10, 2026 arrest, '
                    .'awaiting an extradition hearing; he has not been tried.',
                'sentence' => 'Faces up to 30 years in U.S. federal prison if extradited and convicted.',
            ]);

            $this->attachPhoto($prisoner);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }

    /** Copy the committed non-free portrait onto the public disk if the record has no photo. */
    private function attachPhoto(Prisoner $prisoner): void
    {
        if ($prisoner->photo) {
            return;
        }

        $src = database_path('data/photos/nonfree/'.self::PHOTO_FILE);
        if (! is_file($src)) {
            $this->warn('  Portrait file missing: '.self::PHOTO_FILE);

            return;
        }

        $path = 'prisoners/'.Str::slug($prisoner->name).'.jpg';
        Storage::disk('public')->put($path, (string) file_get_contents($src));
        $prisoner->photo = $path;
        $prisoner->save();
        $this->info('  Portrait set ← nonfree/'.self::PHOTO_FILE);
    }
}
