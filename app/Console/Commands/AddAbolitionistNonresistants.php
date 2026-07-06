<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Antebellum abolitionist "non-resistants" — pacifists in the Garrisonian
 * tradition who were jailed (or, in Dresser's case, flogged) for their
 * antislavery and anti-war witness, recorded in Peter Brock's "Pacifism in the
 * United States." Fills William Lloyd Garrison's existing stub.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddAbolitionistNonresistants extends Command
{
    protected $signature = 'prisoners:add-abolitionist-nonresistants';

    protected $description = 'Add Garrisonian abolitionist non-resistants jailed for their witness (Thoreau, Garrison, Foster, etc.)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Henry David Thoreau', 'first' => 'Henry', 'middle' => 'David', 'last' => 'Thoreau',
                'race' => 'White', 'state' => 'Massachusetts', 'era' => '1840s',
                'birth' => [1817, 7, 12], 'death' => [1862, 5, 6],
                'ideologies' => ['Abolitionism', 'Pacifism', 'Tax resistance', 'Anti-War'],
                'bio' => 'Henry David Thoreau (1817–1862), the Transcendentalist writer and naturalist, spent a night in the Concord, Massachusetts jail in July 1846 for refusing to pay the poll tax in protest of slavery and the Mexican War. A relative paid the tax over his objection and he was released the next morning; the episode inspired his enduring essay "Civil Disobedience" (1849).',
                'charges' => 'Refusing to pay the Massachusetts poll tax, in protest of slavery and the Mexican War.',
                'convicted' => 'Held overnight in the Concord town jail (July 1846).',
                'sentence' => 'One night in jail; released the next day when a relative paid the tax over his objection.',
                'incarceration' => [1846, 7],
            ],
            [
                'name' => 'William Lloyd Garrison', 'first' => 'William', 'middle' => 'Lloyd', 'last' => 'Garrison',
                'race' => 'White', 'state' => 'Massachusetts', 'era' => '1830s',
                'birth' => [1805, 12, 10], 'death' => [1879, 5, 24],
                'ideologies' => ['Abolitionism', 'Pacifism'],
                'affiliation' => ['New England Non-Resistance Society', 'American Anti-Slavery Society'],
                'bio' => 'William Lloyd Garrison (1805–1879) was the foremost American abolitionist — founder of the newspaper The Liberator and a leading Christian pacifist who helped found the New England Non-Resistance Society. In 1830, while co-editing the Genius of Universal Emancipation in Baltimore, he was convicted of criminal libel for naming a Newburyport merchant, Francis Todd, as a participant in the coastal slave trade. Unable to pay the fine, he spent about seven weeks in the Baltimore jail until the philanthropist Arthur Tappan paid it and secured his release.',
                'charges' => 'Criminal libel — for naming the merchant Francis Todd as a participant in the domestic slave trade in the Genius of Universal Emancipation (Baltimore).',
                'convicted' => 'Yes — convicted of libel in 1830.',
                'sentence' => 'About seven weeks in the Baltimore city jail, until the fine was paid by Arthur Tappan.',
                'incarceration' => [1830, 4], 'release' => [1830, 6],
            ],
            [
                'name' => 'Stephen S. Foster', 'first' => 'Stephen', 'middle' => 'Symonds', 'last' => 'Foster',
                'race' => 'White', 'state' => 'Massachusetts', 'era' => '1840s',
                'birth' => [1809, 11, 17], 'death' => [1881, 9, 8],
                'ideologies' => ['Abolitionism', 'Pacifism'],
                'affiliation' => ['American Anti-Slavery Society'],
                'bio' => 'Stephen Symonds Foster (1809–1881) was a radical abolitionist and nonresistant — a "come-outer" who repeatedly rose in New England churches, uninvited, to denounce their complicity with slavery, and who wrote the fierce pamphlet "The Brotherhood of Thieves." For these disruptions he was arrested many times, jailed, and often violently thrown from the meetinghouses.',
                'charges' => 'Disturbing public worship — for rising in churches to denounce slavery (and, as a nonresistant, war).',
                'convicted' => 'Arrested and jailed repeatedly across New England.',
                'sentence' => 'Numerous short jail terms; he was frequently ejected from meetinghouses by force.',
            ],
            [
                'name' => 'Parker Pillsbury', 'first' => 'Parker', 'last' => 'Pillsbury',
                'race' => 'White', 'state' => 'New Hampshire', 'era' => '1840s',
                'birth' => [1809, 9, 22], 'death' => [1898, 7, 7],
                'ideologies' => ['Abolitionism', 'Pacifism'],
                'affiliation' => ['American Anti-Slavery Society'],
                'bio' => 'Parker Pillsbury (1809–1898) was a New Hampshire abolitionist lecturer and nonresistant who, with Stephen Foster and others, carried the antislavery message into hostile churches and towns. For their abolitionist militancy he and his companions were assaulted by mobs and jailed by local authorities; he credited their nonresistance principles with saving their lives in many a confrontation.',
                'charges' => 'Abolitionist agitation — jailed by local authorities and assaulted by mobs for his antislavery lecturing.',
                'convicted' => 'Jailed by the authorities on several occasions.',
                'sentence' => 'Repeated short imprisonments and mob violence.',
            ],
            [
                'name' => 'Thomas P. Beach', 'first' => 'Thomas', 'middle' => 'P.', 'last' => 'Beach',
                'race' => 'White', 'state' => 'Massachusetts', 'era' => '1840s',
                'ideologies' => ['Abolitionism', 'Pacifism'],
                'bio' => 'Thomas P. Beach was a Garrisonian abolitionist and nonresistant who was among those jailed for carrying the antislavery agitation into religious meetings. He served three months in jail for disturbing the Quaker meeting at Lynn, Massachusetts.',
                'charges' => 'Disturbing a religious meeting — for interrupting the Quaker meeting at Lynn, Massachusetts to denounce its stance on slavery.',
                'convicted' => 'Yes.',
                'sentence' => 'Three months in jail.',
            ],
            [
                'name' => 'Charles Stearns', 'first' => 'Charles', 'last' => 'Stearns',
                'race' => 'White', 'state' => 'Massachusetts', 'era' => '1840s',
                'ideologies' => ['Abolitionism', 'Pacifism', 'Conscientious objection'],
                'bio' => 'Charles Stearns was an abolitionist and absolute nonresistant who refused, on principle, to perform militia service or pay the fine in lieu of it, and was jailed for his refusal. He held to his pacifist convictions before later joining the free-state struggle in "Bleeding Kansas."',
                'charges' => 'Refusing militia service on nonresistance principle.',
                'convicted' => 'Jailed for his refusal.',
                'sentence' => 'Imprisoned for declining militia duty.',
            ],
            [
                'name' => 'Amos Dresser', 'first' => 'Amos', 'last' => 'Dresser',
                'race' => 'White', 'state' => 'Tennessee', 'era' => '1830s',
                'birth' => [1812], 'death' => [1904],
                'ideologies' => ['Abolitionism', 'Pacifism'],
                'bio' => 'Amos Dresser (1812–1904), a student at Lane Seminary, was seized in Nashville, Tennessee in 1835 while traveling as a colporteur carrying abolitionist literature. Convicted by a Nashville "vigilance committee," he was publicly given twenty lashes on the bare back and driven from the state — one of the most notorious punishments inflicted on an abolitionist in the South.',
                'charges' => 'Possessing and circulating abolitionist literature in a slave state (Nashville, Tennessee, 1835).',
                'convicted' => 'Convicted by a Nashville vigilance committee.',
                'sentence' => 'Publicly given twenty lashes on the bare back and expelled from the state.',
                'incarceration' => [1835],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'gender' => 'Male',
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'],
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'] ?? [],
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
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Set: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
