<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Colonial and Revolutionary-era Quaker political prisoners recorded in Peter
 * Brock's "Pacifism in the United States":
 *   - The "Virginia Exiles" — about twenty prominent Philadelphia Quakers
 *     arrested without charge in September 1777 and banished by Congress and
 *     the Pennsylvania Council to Winchester, Virginia, for their conscientious
 *     refusal to support the Revolutionary War. Two died in exile (Thomas
 *     Gilpin and John Hunt). This adds the best-documented of the group.
 *   - Samuel Rowland Fisher — an exile who was later imprisoned two years in
 *     Philadelphia (1779–81) for refusing to recognize the court.
 *   - Hatsell O'Kelley — a Cape Cod Quaker jailed six months in 1748 for
 *     refusing military service after being drafted.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddQuakerRevolutionPrisoners extends Command
{
    protected $signature = 'prisoners:add-quaker-revolution-prisoners';

    protected $description = 'Add the Virginia Exiles and other early Quaker conscientious-objector prisoners (from Brock)';

    public function handle(): int
    {
        $exile = 'was one of the "Virginia Exiles" — about twenty prominent Philadelphia Quakers whom the Continental Congress and the Pennsylvania Supreme Executive Council arrested in September 1777, as the British approached the city, and banished without any charge or trial to the frontier town of Winchester, Virginia, for the Society of Friends\' conscientious refusal to support the Revolutionary War. Held roughly seven months, the survivors were released in the spring of 1778.';

        $people = [
            [
                'name' => 'Thomas Gilpin', 'first' => 'Thomas', 'last' => 'Gilpin',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1770s',
                'birth' => [1728], 'death' => [1778, 3],
                'bio' => 'Thomas Gilpin (1728–1778), a Philadelphia merchant, inventor, and member of the American Philosophical Society, '.$exile.' He was one of the two exiles who did not survive it, dying at Winchester in March 1778.',
                'charges' => 'Banished without trial to Winchester, Virginia (September 1777) as a "Virginia Exile," for the Quaker refusal to support the Revolutionary War.',
                'convicted' => 'No — never charged or tried; interned by order of Congress and the Pennsylvania Council.',
                'sentence' => 'Banished to Winchester, Virginia; he died there in exile in March 1778.',
                'incarceration' => [1777, 9], 'died' => [1778, 3],
            ],
            [
                'name' => 'John Hunt', 'first' => 'John', 'last' => 'Hunt',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1770s',
                'birth' => [1715], 'death' => [1778, 3],
                'bio' => 'John Hunt (c. 1715–1778) was a Quaker minister and leader in the Philadelphia Yearly Meeting who '.$exile.' Like Thomas Gilpin, he died in exile at Winchester, in the spring of 1778.',
                'charges' => 'Banished without trial to Winchester, Virginia (September 1777) as a "Virginia Exile," for the Quaker refusal to support the Revolutionary War.',
                'convicted' => 'No — never charged or tried; interned by order of Congress and the Pennsylvania Council.',
                'sentence' => 'Banished to Winchester, Virginia; he died there in exile in 1778.',
                'incarceration' => [1777, 9], 'died' => [1778, 3],
            ],
            [
                'name' => 'Israel Pemberton', 'first' => 'Israel', 'last' => 'Pemberton',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1770s',
                'birth' => [1715], 'death' => [1779, 4, 22],
                'bio' => 'Israel Pemberton Jr. (1715–1779), a wealthy Philadelphia merchant and philanthropist so influential among Friends that critics called him "the King of the Quakers," '.$exile.' His health was broken by the ordeal, and he died within a year of his release.',
                'charges' => 'Banished without trial to Winchester, Virginia (September 1777) as a "Virginia Exile," for the Quaker refusal to support the Revolutionary War.',
                'convicted' => 'No — never charged or tried; interned by order of Congress and the Pennsylvania Council.',
                'sentence' => 'Banished about seven months to Winchester, Virginia; released in the spring of 1778.',
                'incarceration' => [1777, 9], 'release' => [1778, 4],
            ],
            [
                'name' => 'John Pemberton', 'first' => 'John', 'last' => 'Pemberton',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1770s',
                'birth' => [1727], 'death' => [1795],
                'bio' => 'John Pemberton (1727–1795), a Quaker minister and younger brother of Israel and James Pemberton, '.$exile,
                'charges' => 'Banished without trial to Winchester, Virginia (September 1777) as a "Virginia Exile," for the Quaker refusal to support the Revolutionary War.',
                'convicted' => 'No — never charged or tried; interned by order of Congress and the Pennsylvania Council.',
                'sentence' => 'Banished about seven months to Winchester, Virginia; released in the spring of 1778.',
                'incarceration' => [1777, 9], 'release' => [1778, 4],
            ],
            [
                'name' => 'James Pemberton', 'first' => 'James', 'last' => 'Pemberton',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1770s',
                'birth' => [1723], 'death' => [1809],
                'bio' => 'James Pemberton (1723–1809), a Philadelphia merchant, legislator, and later a founder of the Pennsylvania Abolition Society, '.$exile.' He kept a diary of the exile.',
                'charges' => 'Banished without trial to Winchester, Virginia (September 1777) as a "Virginia Exile," for the Quaker refusal to support the Revolutionary War.',
                'convicted' => 'No — never charged or tried; interned by order of Congress and the Pennsylvania Council.',
                'sentence' => 'Banished about seven months to Winchester, Virginia; released in the spring of 1778.',
                'incarceration' => [1777, 9], 'release' => [1778, 4],
            ],
            [
                'name' => 'Henry Drinker', 'first' => 'Henry', 'last' => 'Drinker',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1770s',
                'birth' => [1734], 'death' => [1809],
                'bio' => 'Henry Drinker (1734–1809), a Philadelphia merchant and Quaker (and husband of the diarist Elizabeth Drinker, whose journal records the ordeal), '.$exile,
                'charges' => 'Banished without trial to Winchester, Virginia (September 1777) as a "Virginia Exile," for the Quaker refusal to support the Revolutionary War.',
                'convicted' => 'No — never charged or tried; interned by order of Congress and the Pennsylvania Council.',
                'sentence' => 'Banished about seven months to Winchester, Virginia; released in the spring of 1778.',
                'incarceration' => [1777, 9], 'release' => [1778, 4],
            ],
            [
                'name' => 'Samuel Rowland Fisher', 'first' => 'Samuel', 'middle' => 'Rowland', 'last' => 'Fisher',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1770s',
                'birth' => [1745], 'death' => [1834],
                'bio' => 'Samuel Rowland Fisher (1745–1834), a Philadelphia Quaker merchant, '.$exile.' Fisher continued to refuse allegiance to the new government; in 1779 he was arrested and charged as a Tory over a letter to his brother in New York, and — refusing to recognize the court\'s authority — was imprisoned in Philadelphia for about two years, keeping a journal of his confinement.',
                'charges' => 'Refusing to recognize the Revolutionary court and government (1779) — charged as a Tory; earlier one of the 1777 "Virginia Exiles."',
                'convicted' => 'Yes — convicted after refusing to acknowledge the court\'s authority.',
                'sentence' => 'About two years\' imprisonment in Philadelphia (c. 1779–1781).',
                'incarceration' => [1779], 'release' => [1781],
            ],
            [
                'name' => 'Hatsell O\'Kelley', 'first' => 'Hatsell', 'last' => 'O\'Kelley',
                'race' => 'White', 'state' => 'Massachusetts', 'era' => '1740s',
                'bio' => 'Hatsell O\'Kelley was a Quaker husbandman from Yarmouth, on Cape Cod, who in 1748 — near the end of the War of the Austrian Succession — was drafted for military service and refused on conscientious grounds. Unable to make distraint on his goods for the ten-pound penalty, the authorities sentenced him to six months\' imprisonment in the Barnstable County jail and assessed him the costs.',
                'charges' => 'Refusing military service after being drafted (1748) as a Quaker conscientious objector.',
                'convicted' => 'Yes.',
                'sentence' => 'Six months in the Barnstable County jail, plus costs.',
                'incarceration' => [1748],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);
                $died = ! empty($p['died']);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'gender' => 'Male',
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'],
                    'ideologies' => ['Pacifism', 'Conscientious objection'],
                    'affiliation' => ['Quaker'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => ! $died,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['birth'])) {
                    $prisoner->setPartialDate('birthdate', ...$p['birth']);
                }
                if (! empty($p['death'])) {
                    $prisoner->setPartialDate('death_date', ...$p['death']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                if (! empty($p['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                }
                if (! empty($p['release'])) {
                    $case->setPartialDate('release_date', ...$p['release']);
                }
                if ($died) {
                    $case->setPartialDate('death_in_custody_date', ...$p['died']);
                }
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Set: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
