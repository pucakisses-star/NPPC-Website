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
 * GIs imprisoned in connection with the Vietnam-era GI underground press —
 * soldiers court-martialed and sent to Fort Leavenworth for antiwar protest or
 * for publishing an underground newspaper:
 *
 *  - Lt. Henry Howe (Fort Bliss) — 2 years' hard labor for marching in an
 *    antiwar demonstration with a sign, 1965.
 *  - PFC Bruce "Gypsy" Peterson (Fort Hood) — 8 years on a pretext marijuana
 *    charge after founding the underground paper Fatigue Press, 1968.
 *
 * (The Fort Hood Three and Captain Howard Levy, also named in the GI-press
 * story, are already in the database.)
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
final class AddGiUndergroundPressPrisoners extends Command
{
    protected $signature = 'prisoners:add-gi-underground-press-prisoners';

    protected $description = 'Add GIs imprisoned over the Vietnam-era GI underground press (Henry Howe, Bruce Peterson)';

    public function handle(): int
    {
        $leavenworth = Institution::firstOrCreate(
            ['name' => 'United States Disciplinary Barracks, Fort Leavenworth'],
            ['city' => 'Fort Leavenworth', 'state' => 'Kansas']
        )->id;

        $people = [
            [
                'name' => 'Henry Howe', 'first' => 'Henry', 'last' => 'Howe', 'aka' => 'Henry H. Howe Jr.',
                'ideologies' => ['Anti-War', 'Free speech', 'GI resistance'],
                'affiliation' => [],
                'bio' => 'Second Lieutenant Henry H. Howe Jr. was a U.S. Army officer stationed at Fort Bliss, Texas, and one of the first American servicemen court-martialed for antiwar protest. Off duty and in civilian clothes on November 6, 1965, he marched in a small antiwar demonstration in El Paso carrying a hand-lettered sign reading "End Johnson\'s Fascist Aggression in Vietnam." He was court-martialed for using contemptuous words against the President and conduct unbecoming an officer, sentenced to two years\' hard labor at Fort Leavenworth and dismissed from the Army. His case became an early cause célèbre of the GI antiwar movement; the sentence was reduced on review and he was released after a few months amid public pressure.',
                'charges' => 'Using contemptuous words against the President and conduct unbecoming an officer — for carrying an antiwar sign in an El Paso demonstration on November 6, 1965.',
                'convicted' => 'Yes — general court-martial.',
                'sentence' => 'Two years\' hard labor at Fort Leavenworth and dismissal from the Army (reduced on review); released after a few months amid public pressure.',
                'incarceration' => [1966], 'inst' => $leavenworth,
            ],
            [
                'name' => 'Bruce Peterson', 'first' => 'Bruce', 'last' => 'Peterson', 'aka' => 'Gypsy',
                'ideologies' => ['Anti-War', 'GI resistance', 'Press freedom'],
                'affiliation' => ['Fatigue Press'],
                'bio' => 'Private First Class Bruce "Gypsy" Peterson founded Fatigue Press, one of the best-known Vietnam-era GI underground antiwar newspapers, at Fort Hood, Texas. Targeted by the Army for his organizing, he was arrested three times on marijuana charges widely regarded as a frame-up — the final arrest, in August 1968, resting on a claim of a microscopic trace found in his pocket lint. He was court-martialed and sentenced to eight years\' hard labor at Fort Leavenworth. He was released on appeal after serving about two years.',
                'charges' => 'Marijuana possession — a pretext used against him after he founded the antiwar underground newspaper Fatigue Press at Fort Hood; arrested three times, the last (August 1968) on a claim of a microscopic amount in his pocket lint.',
                'convicted' => 'Yes — court-martialed.',
                'sentence' => 'Eight years\' hard labor at Fort Leavenworth; released on appeal after about two years.',
                'incarceration' => [1968, 8], 'inst' => $leavenworth,
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'era' => '1960s',
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'],
                    'description' => $p['bio'],
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
                    'institution_id' => $p['inst'],
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
