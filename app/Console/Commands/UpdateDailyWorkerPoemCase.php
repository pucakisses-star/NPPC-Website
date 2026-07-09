<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fills in dates for the three staffers jailed in the 1927–28 Daily Worker
 * "America" poem obscenity prosecution: the poet David Gordon, editor William
 * F. Dunne, and business manager Benjamin Mandel (party name "Bert Miller").
 * Gordon and Dunne already exist (from prisoners:add-labor-defender-1927-cases,
 * which skips existing names) and are updated in place; Mandel is created.
 * Dunne is matched by his exact full name so the unrelated later anarchist Bill
 * Dunne (b. 1954) is left untouched. Idempotent.
 */
final class UpdateDailyWorkerPoemCase extends Command
{
    protected $signature = 'prisoners:update-daily-worker-poem-case';

    protected $description = 'Add dates for the 1927 Daily Worker "America" poem prisoners (Gordon, Dunne, Mandel)';

    public function handle(): int
    {
        DB::transaction(function () {
            $tombs = Institution::firstOrCreate(['name' => 'The Tombs'], ['city' => 'New York', 'state' => 'New York']);

            // David Gordon — the 18-year-old worker-poet; born 1909, died 1973.
            $gordon = Prisoner::withUnderReview()
                ->where('name', 'David Gordon')
                ->get()
                ->first(fn ($x) => $x->era === '1920s'
                    || str_contains((string) $x->description, 'America')
                    || str_contains((string) $x->description, 'Daily Worker'));
            if ($gordon) {
                $gordon->setPartialDate('birthdate', 1909);
                $gordon->setPartialDate('death_date', 1973, 6, 21);
                $gordon->save();
                $case = $gordon->cases()->first();
                if ($case) {
                    $case->sentence = 'Sentenced to up to three years in the New York reformatory for the poem "America"; '
                        .'actually held about 35 days (roughly April 5 – May 10, 1928) before release after a broad '
                        .'free-speech campaign.';
                    $case->setPartialDate('incarceration_date', 1928, 4, 5);
                    $case->setPartialDate('release_date', 1928, 5, 10);
                    $case->save();
                }
                $this->info('  updated: David Gordon');
            } else {
                $this->warn('  not found: David Gordon');
            }

            // William F. Dunne — Daily Worker editor; born 1887, died 1953.
            // Match the EXACT name to avoid the later anarchist "Bill Dunne".
            $dunne = Prisoner::withUnderReview()->where('name', 'William F. Dunne')->first();
            if ($dunne) {
                $dunne->setPartialDate('birthdate', 1887, 10, 15);
                $dunne->setPartialDate('death_date', 1953, 9, 23);
                $dunne->save();
                $case = $dunne->cases()->first() ?? new PrisonerCase(['prisoner_id' => $dunne->id]);
                $case->prisoner_id = $dunne->id;
                if (! $case->institution_id) {
                    $case->institution_id = $tombs->id;
                }
                $case->convicted = 'Convicted June 3, 1927; held pending appeal, later reversed by the Appellate Division.';
                $case->sentence = 'Sentenced to 30 days in the New York City workhouse (June 3, 1927); held in the Tombs '
                    .'pending appeal and released on $1,000 bail by June 15, 1927 — about 18–19 days in actual custody. '
                    .'The conviction was reversed by the Appellate Division and the workhouse sentence was not served.';
                $case->setPartialDate('arrest_date', 1927, 5, 27);
                $case->setPartialDate('incarceration_date', 1927, 5, 27);
                $case->setPartialDate('release_date', 1927, 6, 15);
                $case->save();
                $this->info('  updated: William F. Dunne');
            } else {
                $this->warn('  not found: William F. Dunne');
            }

            // Benjamin Mandel ("Bert Miller") — Daily Worker business manager; create.
            $mandel = Prisoner::withUnderReview()
                ->where('name', 'Benjamin Mandel')
                ->orWhere('aka', 'like', '%Bert Miller%')
                ->first() ?? new Prisoner(['name' => 'Benjamin Mandel']);
            $mandel->fill([
                'name' => 'Benjamin Mandel',
                'first_name' => 'Benjamin',
                'last_name' => 'Mandel',
                'aka' => 'Bert Miller',
                'gender' => 'Male',
                'state' => 'New York',
                'era' => '1920s',
                'ideologies' => ['Communism'],
                'affiliation' => ['Workers (Communist) Party', 'Daily Worker'],
                'description' => 'Benjamin Mandel (who used the party name "Bert Miller") was the business manager of the '
                    .'Daily Worker, jailed in the 1927 obscenity prosecution over David Gordon\'s poem "America." '
                    .'Arrested May 27, 1927 and held in the Tombs, he had served seven days by his June 3, 1927 '
                    .'sentencing, when the court suspended his sentence, calling the time already served sufficient '
                    .'punishment; his conviction was later reversed by the Appellate Division. Mandel broke with the '
                    .'Communist Party in the 1930s and went on to become a research director for the House Un-American '
                    .'Activities Committee and the Senate Internal Security Subcommittee.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $mandel->setPartialDate('birthdate', 1891, 10, 2);
            $mandel->setPartialDate('death_date', 1973, 8, 8);
            $wasNew = ! $mandel->exists;
            $mandel->save();

            $case = $mandel->cases()->where('institution_id', $tombs->id)->first()
                ?? $mandel->cases()->first()
                ?? new PrisonerCase(['prisoner_id' => $mandel->id]);
            $case->prisoner_id = $mandel->id;
            $case->institution_id = $tombs->id;
            $case->charges = 'Jailed in the obscenity prosecution over the publication of David Gordon\'s poem "America" '
                .'in the Daily Worker (as the paper\'s business manager).';
            $case->convicted = 'Convicted June 3, 1927 (sentence suspended); conviction later reversed by the Appellate Division.';
            $case->sentence = 'Seven days in the Tombs (May 27 – June 3, 1927); sentence suspended as the time already '
                .'served was deemed sufficient. Conviction later reversed.';
            $case->setPartialDate('arrest_date', 1927, 5, 27);
            $case->setPartialDate('incarceration_date', 1927, 5, 27);
            $case->setPartialDate('release_date', 1927, 6, 3);
            $case->save();
            $this->info(($wasNew ? '  added: ' : '  updated: ').'Benjamin Mandel (Bert Miller)');
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("\nDaily Worker \"America\" poem case — dates updated.");

        return self::SUCCESS;
    }
}
