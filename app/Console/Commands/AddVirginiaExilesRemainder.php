<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The remaining Philadelphia "Virginia Exiles" of 1777 — the members of the
 * September-1777 preventive-detention group not already added by
 * prisoners:add-quaker-revolution-prisoners (which covers Gilpin, Hunt, the
 * three Pembertons, Drinker, and Samuel Rowland Fisher). As the British
 * advanced on Philadelphia, Congress and the Pennsylvania Supreme Executive
 * Council arrested some forty citizens — mostly Quakers who would not swear
 * loyalty oaths — as suspected Loyalists, and banished about twenty without
 * charge or trial to Winchester, Virginia (roughly September 1777 – late April
 * 1778). This adds the rest of the group, plus three men named in the later
 * exile records (Rodan, Patterson, Quigg). A handful were not Quakers (the
 * dancing-master Thomas Pike, the Anglican clergyman Thomas Coombe), and Coombe
 * was paroled for ill health rather than sent on to Virginia.
 *
 * Create-or-update matched by name + era 1770s (or a Virginia-Exile marker in
 * the description), so it won't clobber an unrelated modern person of the same
 * name. Rebuilds the single case. Idempotent.
 */
final class AddVirginiaExilesRemainder extends Command
{
    protected $signature = 'prisoners:add-virginia-exiles-remainder';

    protected $description = 'Add the remaining 1777 "Virginia Exiles" (Philadelphia Quakers banished to Winchester)';

    public function handle(): int
    {
        $exile = 'was one of the "Virginia Exiles" — the roughly twenty prominent Philadelphians whom the Continental Congress and the Pennsylvania Supreme Executive Council arrested without charge in September 1777, as the British approached the city, and banished without trial to the frontier town of Winchester, Virginia. Most were Quakers seized for the Society of Friends\' conscientious refusal to support the Revolutionary War. Held roughly seven months, the survivors were released in the spring of 1778.';

        // Quakers get the Friends affiliation and the pacifist framing; the few
        // non-Quakers (Pike, Coombe, and men whose faith the records don't fix)
        // are grouped under the "Virginia Exiles (1777)" affiliation instead.
        $quakerIdeo = ['Pacifism', 'Conscientious objection'];
        $otherIdeo = ['Political dissent'];

        $people = [
            [
                'name' => 'Miers Fisher', 'first' => 'Miers', 'last' => 'Fisher', 'aka' => 'Myers Fisher',
                'quaker' => true, 'birth' => [1748], 'death' => [1819],
                'bio' => 'Miers Fisher (1748–1819), a prominent Philadelphia Quaker lawyer and later a founder of the Bank of North America, '.$exile.' He was one of three Fisher brothers among the exiles.',
            ],
            [
                'name' => 'Thomas Fisher', 'first' => 'Thomas', 'last' => 'Fisher',
                'quaker' => true, 'birth' => [1741], 'death' => [1810],
                'bio' => 'Thomas Fisher (1741–1810), a Philadelphia Quaker merchant and brother of Miers and Samuel Rowland Fisher, '.$exile,
            ],
            [
                'name' => 'Samuel Pleasants', 'first' => 'Samuel', 'last' => 'Pleasants',
                'quaker' => true, 'birth' => [1737], 'death' => [1807],
                'bio' => 'Samuel Pleasants (1737–1807), a Philadelphia Quaker merchant, '.$exile,
            ],
            [
                'name' => 'Edward Pennington', 'first' => 'Edward', 'last' => 'Pennington',
                'quaker' => true, 'birth' => [1726], 'death' => [1796],
                'bio' => 'Edward Pennington (1726–1796), a Philadelphia sugar refiner and Quaker, '.$exile,
            ],
            [
                'name' => 'Thomas Affleck', 'first' => 'Thomas', 'last' => 'Affleck',
                'quaker' => true, 'birth' => [1740], 'death' => [1795],
                'bio' => 'Thomas Affleck (1740–1795), a Scottish-born master cabinetmaker counted among the finest furniture makers of colonial Philadelphia and a Quaker, '.$exile,
            ],
            [
                'name' => 'Elijah Brown', 'first' => 'Elijah', 'last' => 'Brown',
                'quaker' => true, 'birth' => [1740], 'death' => [1810],
                'bio' => 'Elijah Brown (1740–1810), a Philadelphia Quaker shopkeeper and conveyancer and the father of the novelist Charles Brockden Brown, '.$exile,
            ],
            [
                'name' => 'Thomas Wharton', 'first' => 'Thomas', 'last' => 'Wharton', 'aka' => 'Thomas Wharton (merchant)',
                'quaker' => true,
                'bio' => 'Thomas Wharton, a Philadelphia merchant of the Quaker Wharton family (a kinsman of, and not to be confused with, the Patriot Thomas Wharton Jr. who served as president of Pennsylvania), '.$exile,
            ],
            [
                'name' => 'Owen Jones Jr.', 'first' => 'Owen', 'last' => 'Jones', 'aka' => 'Owen Jones Junior',
                'quaker' => true,
                'bio' => 'Owen Jones Jr., a Philadelphia Quaker merchant and son of Owen Jones, the last provincial treasurer of Pennsylvania, '.$exile,
            ],
            [
                'name' => 'Charles Eddy', 'first' => 'Charles', 'last' => 'Eddy',
                'quaker' => true,
                'bio' => 'Charles Eddy, a Philadelphia Quaker merchant, '.$exile,
            ],
            [
                'name' => 'Charles Jervis', 'first' => 'Charles', 'last' => 'Jervis', 'aka' => 'Charles Jarvis',
                'quaker' => true,
                'bio' => 'Charles Jervis (also spelled Jarvis), a Philadelphia Quaker, '.$exile,
            ],
            [
                'name' => 'Thomas Coombe', 'first' => 'Thomas', 'last' => 'Coombe', 'aka' => 'Rev. Thomas Coombe',
                'quaker' => false, 'ideologies' => ['Loyalism'], 'affiliation' => ['Virginia Exiles (1777)'],
                'birth' => [1747], 'death' => [1822],
                'bio' => 'The Rev. Thomas Coombe (1747–1822) was an Anglican clergyman and poet, an assistant minister at Christ Church, Philadelphia. Arrested in the September 1777 roundup with the "Virginia Exiles," he was paroled on account of ill health rather than sent on to Winchester, and afterward emigrated to England, where he served as a royal chaplain.',
                'charges' => 'Arrested in the September 1777 Philadelphia roundup of suspected Loyalists ("Virginia Exiles"); paroled for ill health.',
                'convicted' => 'No — never charged or tried; arrested by order of Congress and the Pennsylvania Council.',
                'sentence' => 'Arrested and paroled for ill health; not banished to Virginia with the others.',
                'incarceration' => [1777, 9], 'release' => [1777, 9],
            ],
            [
                'name' => 'Thomas Pike', 'first' => 'Thomas', 'last' => 'Pike',
                'quaker' => false, 'ideologies' => $otherIdeo, 'affiliation' => ['Virginia Exiles (1777)'],
                'bio' => 'Thomas Pike, a Philadelphia dancing and fencing master, was one of the citizens arrested in the September 1777 roundup and sent toward Virginia with the exiles. Unlike most of the group he was not a Quaker.',
            ],
            [
                'name' => 'William Drewet Smith', 'first' => 'William', 'middle' => 'Drewet', 'last' => 'Smith', 'aka' => 'William Drew Smith',
                'quaker' => false, 'ideologies' => $otherIdeo, 'affiliation' => ['Virginia Exiles (1777)'],
                'bio' => 'William Drewet Smith, a Philadelphia druggist, was among those arrested in September 1777 and named in the exile records as ordered into the Virginia banishment of citizens suspected of disaffection to the American cause.',
            ],
            [
                'name' => 'William Smith', 'first' => 'William', 'last' => 'Smith', 'aka' => 'William Smith (broker)',
                'quaker' => false, 'ideologies' => $otherIdeo, 'affiliation' => ['Virginia Exiles (1777)'],
                'bio' => 'William Smith, a Philadelphia broker (so distinguished in the records from the other men of that common name), was one of the citizens arrested in September 1777 and banished with the "Virginia Exiles" to Winchester, Virginia.',
            ],
            [
                'name' => 'Phineas Bond', 'first' => 'Phineas', 'last' => 'Bond',
                'quaker' => false, 'ideologies' => $otherIdeo, 'affiliation' => ['Virginia Exiles (1777)'],
                'bio' => 'Phineas Bond was among the Philadelphians seized in the September 1777 arrests and named in the exile records as one of those ordered into the Virginia banishment of suspected Loyalists.',
            ],
            [
                'name' => 'William Rodan', 'first' => 'William', 'last' => 'Rodan',
                'quaker' => false, 'ideologies' => $otherIdeo, 'affiliation' => ['Virginia Exiles (1777)'],
                'bio' => 'William Rodan is named in the later exile records as one of the men sent to Virginia with the Philadelphia prisoners in the September 1777 roundup.',
            ],
            [
                'name' => 'Thomas Patterson', 'first' => 'Thomas', 'last' => 'Patterson',
                'quaker' => false, 'ideologies' => $otherIdeo, 'affiliation' => ['Virginia Exiles (1777)'],
                'bio' => 'Thomas Patterson is named in the later exile records as one of the men sent to Virginia with the Philadelphia prisoners in the September 1777 roundup.',
            ],
            [
                'name' => 'Jacobus Quigg', 'first' => 'Jacobus', 'last' => 'Quigg',
                'quaker' => false, 'ideologies' => $otherIdeo, 'affiliation' => ['Virginia Exiles (1777)'],
                'bio' => 'Jacobus Quigg is named in the later exile records as one of the men sent to Virginia with the Philadelphia prisoners in the September 1777 roundup.',
            ],
        ];

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($people, $exile, $quakerIdeo, &$added, &$updated) {
            foreach ($people as $p) {
                $existing = Prisoner::withUnderReview()
                    ->where('name', $p['name'])
                    ->get()
                    ->first(fn ($x) => $x->era === '1770s'
                        || str_contains((string) $x->description, 'Virginia Exile')
                        || str_contains((string) $x->description, 'Winchester'));
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => 'Pennsylvania',
                    'era' => '1770s',
                    'ideologies' => $p['ideologies'] ?? $quakerIdeo,
                    'affiliation' => $p['affiliation'] ?? ['Quaker'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
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
                    'charges' => $p['charges'] ?? 'Banished without trial to Winchester, Virginia (September 1777) as a "Virginia Exile" during the preventive detention of Philadelphians suspected of disaffection to the Revolutionary cause.',
                    'convicted' => $p['convicted'] ?? 'No — never charged or tried; interned by order of Congress and the Pennsylvania Council.',
                    'sentence' => $p['sentence'] ?? 'Banished about seven months to Winchester, Virginia; released in the spring of 1778.',
                ]);
                $case->setPartialDate('incarceration_date', ...($p['incarceration'] ?? [1777, 9]));
                $case->setPartialDate('release_date', ...($p['release'] ?? [1778, 4]));
                $case->save();

                if ($existing) {
                    $updated++;
                    $this->line('  updated: '.$p['name']);
                } else {
                    $added++;
                    $this->info('  added: '.$p['name'].' (slug: '.$prisoner->slug.')');
                }
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("\nVirginia Exiles (remainder) — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
