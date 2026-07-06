<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Eighteenth-century political prisoners — people jailed, banished or executed
 * for taking the losing side in the conflicts of colonial and early-republic
 * America. Researched at the maintainer’s request (“more 1700s prisoners —
 * British loyalists and other revolts”) and de-duplicated as best as possible;
 * the command is idempotent and skips any prisoner already present by name.
 *
 *   British Loyalists jailed or banished by the Revolution:
 *     William Franklin, David Mathews, Moses Dunbar (hanged),
 *     Thomas Brown, Samuel Seabury, Peter Van Schaack (exiled)
 *   North Carolina Regulators (1771):
 *     Benjamin Merrill (hanged), James Few (hanged), Herman Husband
 *   Shays’ Rebellion (1786–87):
 *     Daniel Shays, Job Shattuck, John Bly (hanged), Charles Rose (hanged)
 *   Whiskey Rebellion (1794):
 *     Philip Vigol, John Mitchell
 *   Fries’s Rebellion (1799):
 *     John Fries
 *
 * Sourced to the standard histories of the Loyalists, the Regulator movement,
 * and the three early tax/agrarian rebellions (Shays’, Whiskey, Fries’s).
 */
class Add1700sLoyalistsAndRevolts extends Command
{
    protected $signature = 'prisoners:add-1700s-loyalists-revolts';

    protected $description = 'Add 1700s political prisoners: British Loyalists and the Regulator, Shays, Whiskey and Fries revolts';

    public function handle(): int
    {
        $litchfield = Institution::firstOrCreate(['name' => 'Litchfield Gaol'], ['city' => 'Litchfield', 'state' => 'Connecticut']);

        $people = [
            // ---- British Loyalists ----
            [
                'name' => 'William Franklin', 'first' => 'William', 'last' => 'Franklin',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New Jersey', 'era' => '1770s',
                'ideologies' => ['Loyalism', 'Monarchism'],
                'affiliation' => ['Loyalists (American Revolution)'],
                'bio' => 'William Franklin was the last royal Governor of New Jersey and the loyalist son of Benjamin Franklin. When the Revolution came he refused to abandon the Crown, and in June 1776 the New Jersey Provincial Congress had him arrested as “a virulent enemy to this country.” Made a prisoner of the new state governments, he was held in Connecticut for more than two years — paroled at first, then, after he was accused of violating his parole, confined in May 1777 in solitary confinement in the Litchfield jail, in a cell normally kept for prisoners condemned to death. He was released in a prisoner exchange in 1778, and after the war lived the rest of his life in exile in England, permanently estranged from his father.',
                'charges' => 'Being an enemy of the American cause — arrested by order of the New Jersey Provincial Congress for refusing to renounce his allegiance to the Crown.',
                'convicted' => 'Held without criminal trial as a political prisoner of the revolutionary governments.',
                'sentence' => 'More than two years’ imprisonment in Connecticut (1776–1778), including solitary confinement in the Litchfield jail, before release in a prisoner exchange.',
                'institution_id' => $litchfield->id,
                'in_exile' => true,
            ],
            [
                'name' => 'David Mathews', 'first' => 'David', 'last' => 'Mathews',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1770s',
                'ideologies' => ['Loyalism', 'Monarchism'],
                'affiliation' => ['Loyalists (American Revolution)'],
                'bio' => 'David Mathews was the loyalist Mayor of New York City. In June 1776 he was arrested and accused of involvement in the so-called Hickey Plot, an alleged conspiracy to aid the British and, in the most sensational rumor, to assassinate General George Washington. Handed over to the Connecticut authorities, he was imprisoned at Litchfield, but escaped in November 1777 and made his way back to British-held New York. He never stood trial; after the war he was banished, his property was confiscated, and he resettled in Nova Scotia.',
                'charges' => 'Conspiracy against the American cause (the alleged “Hickey Plot”) — arrested as the Loyalist Mayor of New York City in 1776.',
                'convicted' => 'No — held as a political prisoner without trial; he escaped in 1777.',
                'sentence' => 'Imprisoned in Connecticut from 1776 until his escape in November 1777; later banished with his estate confiscated.',
                'institution_id' => $litchfield->id,
                'in_exile' => true,
            ],
            [
                'name' => 'Moses Dunbar', 'first' => 'Moses', 'last' => 'Dunbar',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut', 'era' => '1770s',
                'ideologies' => ['Loyalism', 'Monarchism'],
                'affiliation' => ['Loyalists (American Revolution)'],
                'bio' => 'Moses Dunbar was a Connecticut farmer and Anglican convert who took the side of the Crown. Having accepted a captain’s commission to recruit men for the British army, he was seized by the revolutionary authorities, tried before a Hartford court, and convicted of high treason against the state of Connecticut. He was hanged at Hartford on March 19, 1777 — the only person executed for treason by the state of Connecticut during the Revolution.',
                'charges' => 'High treason against the state of Connecticut — for accepting a British commission and recruiting men for the Crown.',
                'convicted' => 'Yes — convicted of high treason by a Hartford court (1777).',
                'sentence' => 'Death. He was hanged at Hartford on March 19, 1777, the only man executed for treason by Connecticut during the Revolution.',
                'death_date' => '1777-03-19',
                'released' => false,
            ],
            [
                'name' => 'Thomas Brown', 'first' => 'Thomas', 'last' => 'Brown',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Georgia', 'era' => '1770s',
                'ideologies' => ['Loyalism', 'Monarchism'],
                'affiliation' => ['Loyalists (American Revolution)'],
                'bio' => 'Thomas Brown was a young English-born settler in the Georgia backcountry who refused to join the Patriot cause. In August 1775 a party of Sons of Liberty came to his home near Augusta to force him to sign their association; when he refused, they beat him, fractured his skull with a musket, partially scalped him, burned his feet so badly that he lost two toes, and tarred and feathered him — earning him the derisive nickname “Burntfoot Brown.” He survived to become one of the most effective Loyalist partisan commanders in the southern backcountry, and after the war went into exile in the Bahamas.',
                'charges' => 'Refusing to sign the Revolutionary “association” and support the Patriot cause.',
                'convicted' => 'No trial — seized and tortured by a Sons of Liberty mob in 1775.',
                'sentence' => 'Beaten, partially scalped, burned (losing two toes) and tarred and feathered by his captors.',
                'released' => true,
                'in_exile' => true,
            ],
            [
                'name' => 'Samuel Seabury', 'first' => 'Samuel', 'last' => 'Seabury',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1770s',
                'ideologies' => ['Loyalism', 'Monarchism'],
                'affiliation' => ['Loyalists (American Revolution)'],
                'bio' => 'Samuel Seabury was an Anglican clergyman in Westchester County, New York, and the author of the “Letters of a Westchester Farmer,” loyalist pamphlets so effective that the young Alexander Hamilton wrote rebuttals to them. In November 1775 a band of armed Patriots seized him and carried him off to Connecticut, where he was held prisoner in New Haven for about six weeks. He was never charged with a crime. After the Revolution he became the first bishop of the Episcopal Church in the United States.',
                'charges' => 'Loyalist agitation — his “Letters of a Westchester Farmer” pamphlets — for which armed Patriots seized him in 1775.',
                'convicted' => 'No — held as a political prisoner without charge for about six weeks.',
                'sentence' => 'Roughly six weeks’ imprisonment in New Haven, Connecticut (1775).',
                'released' => true,
            ],
            [
                'name' => 'Peter Van Schaack', 'first' => 'Peter', 'last' => 'Van Schaack',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1770s',
                'ideologies' => ['Loyalism'],
                'affiliation' => ['Loyalists (American Revolution)'],
                'bio' => 'Peter Van Schaack was a distinguished New York lawyer who, though sympathetic to many American grievances, could not in conscience take up arms against the Crown and refused the revolutionary oath of allegiance. Summoned before the commissioners for detecting conspiracies at Albany, he again refused the oath in July 1778 and was banished from the state. He spent seven years in exile in England — much of it going blind — before New York restored his rights in 1784 and he returned home.',
                'charges' => 'Refusing the oath of allegiance to the revolutionary state of New York.',
                'convicted' => 'Banished by order of the commissioners for conspiracies (1778), without criminal trial.',
                'sentence' => 'Banishment and seven years of exile in England (1778–1785).',
                'released' => true,
                'in_exile' => true,
            ],

            // ---- North Carolina Regulators (1771) ----
            [
                'name' => 'Benjamin Merrill', 'first' => 'Benjamin', 'last' => 'Merrill',
                'gender' => 'Male', 'race' => 'White', 'state' => 'North Carolina', 'era' => '1770s',
                'ideologies' => ['Agrarian populism', 'Anti-corruption'],
                'affiliation' => ['Regulator Movement'],
                'bio' => 'Benjamin Merrill was a captain of the Rowan County militia and a leader of the Regulator movement — the backcountry farmers of North Carolina who rose up against corrupt colonial officials and extortionate fees in the years before the Revolution. After Governor William Tryon crushed the Regulators at the Battle of Alamance in May 1771, Merrill was among those tried for high treason at Hillsborough. He was convicted and, on June 19, 1771, hanged — one of six Regulators executed after the battle.',
                'charges' => 'High treason — for his leadership in the Regulator uprising against colonial officials in North Carolina.',
                'convicted' => 'Yes — convicted of high treason at Hillsborough (June 1771).',
                'sentence' => 'Death. Hanged (and sentenced to be drawn and quartered) at Hillsborough on June 19, 1771.',
                'death_date' => '1771-06-19',
                'released' => false,
            ],
            [
                'name' => 'James Few', 'first' => 'James', 'last' => 'Few',
                'gender' => 'Male', 'race' => 'White', 'state' => 'North Carolina', 'era' => '1770s',
                'ideologies' => ['Agrarian populism', 'Anti-corruption'],
                'affiliation' => ['Regulator Movement'],
                'bio' => 'James Few was a young Regulator captured at the Battle of Alamance on May 16, 1771. The day after the battle, without any trial, Governor William Tryon ordered him hanged in the militia camp as an example to the other prisoners. He became one of the first martyrs of the Regulator movement, the agrarian revolt against colonial corruption that many later remembered as a forerunner of the Revolution.',
                'charges' => 'Taking up arms in the Regulator uprising against the colonial government of North Carolina.',
                'convicted' => 'No trial — hanged by order of Governor Tryon the day after the Battle of Alamance.',
                'sentence' => 'Death. Hanged at Tryon’s camp on May 17, 1771.',
                'death_date' => '1771-05-17',
                'released' => false,
            ],
            [
                'name' => 'Herman Husband', 'first' => 'Herman', 'last' => 'Husband',
                'gender' => 'Male', 'race' => 'White', 'state' => 'North Carolina', 'era' => '1770s',
                'ideologies' => ['Agrarian populism', 'Anti-corruption'],
                'affiliation' => ['Regulator Movement', 'Whiskey Rebellion'],
                'bio' => 'Herman Husband was a Quaker farmer and pamphleteer who became the leading voice of the North Carolina Regulators. For his agitation against colonial corruption he was jailed and, in 1770, expelled from the colonial Assembly and outlawed; he fled the colony before the Battle of Alamance. A generation later, having settled in western Pennsylvania, he was swept up in the Whiskey Rebellion of 1794 — arrested as a suspected leader and imprisoned for months in Philadelphia. Released in failing health, he died on the journey home in 1795.',
                'charges' => 'Sedition and agitation as a Regulator (1768–70), and later suspected leadership of the Whiskey Rebellion (1794).',
                'convicted' => 'Outlawed and expelled from the North Carolina Assembly in 1770; imprisoned without conviction during the Whiskey Rebellion.',
                'sentence' => 'Jailed as a Regulator and again imprisoned for months in Philadelphia in 1794–95; released in failing health, he died on the way home.',
                'released' => true,
            ],

            // ---- Shays’ Rebellion (1786–87) ----
            [
                'name' => 'Daniel Shays', 'first' => 'Daniel', 'last' => 'Shays',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'era' => '1780s',
                'ideologies' => ['Agrarian populism', 'Anti-tax'],
                'affiliation' => ['Shays’ Rebellion'],
                'bio' => 'Daniel Shays was a farmer and veteran of the Revolutionary War — he had fought at Bunker Hill, Ticonderoga and Saratoga — who became the reluctant figurehead of the 1786–87 uprising of debt-ridden Massachusetts farmers that bears his name. Shays’ Rebellion, a revolt against aggressive debt collection and the seizure of farms, was put down by force in early 1787. Shays fled to Vermont, and the Massachusetts Supreme Judicial Court condemned him to death for treason in absentia. He was pardoned by Governor John Hancock in 1788 and lived out his days in poverty in New York State.',
                'charges' => 'Treason — for leading the armed uprising of Massachusetts farmers against the debt courts and tax collection.',
                'convicted' => 'Yes — condemned to death for treason in absentia (1787).',
                'sentence' => 'Death sentence, later pardoned by Governor John Hancock in 1788.',
                'released' => true,
            ],
            [
                'name' => 'Job Shattuck', 'first' => 'Job', 'last' => 'Shattuck',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'era' => '1780s',
                'ideologies' => ['Agrarian populism', 'Anti-tax'],
                'affiliation' => ['Shays’ Rebellion'],
                'bio' => 'Job Shattuck was a Revolutionary War veteran and one of the principal leaders of Shays’ Rebellion in eastern Massachusetts. On November 30, 1786 a mounted posse ran him down and captured him, wounding him with a sword slash in the process, and he was thrown into a Boston jail. Tried and sentenced to death for treason in 1787, he was pardoned that September.',
                'charges' => 'Treason — for leading Shays’ Rebellion farmers in the Massachusetts uprising.',
                'convicted' => 'Yes — convicted of treason and sentenced to death (1787).',
                'sentence' => 'Death sentence, pardoned in September 1787; he had been captured and wounded and held in the Boston jail.',
                'released' => true,
            ],
            [
                'name' => 'John Bly', 'first' => 'John', 'last' => 'Bly',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'era' => '1780s',
                'ideologies' => ['Agrarian populism', 'Anti-tax'],
                'affiliation' => ['Shays’ Rebellion'],
                'bio' => 'John Bly was a participant in Shays’ Rebellion. Unlike the movement’s leaders, who were pardoned, Bly was convicted and, together with Charles Rose, hanged at Lenox, Massachusetts, on December 6, 1787 — among the very few actually executed for the uprising. On the gallows the two men reportedly blamed the harshness of the government for driving them to rebellion.',
                'charges' => 'Crimes committed during Shays’ Rebellion (charges of treason and robbery arising from the uprising).',
                'convicted' => 'Yes — convicted and sentenced to death.',
                'sentence' => 'Death. Hanged at Lenox, Massachusetts, on December 6, 1787.',
                'death_date' => '1787-12-06',
                'released' => false,
            ],
            [
                'name' => 'Charles Rose', 'first' => 'Charles', 'last' => 'Rose',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'era' => '1780s',
                'ideologies' => ['Agrarian populism', 'Anti-tax'],
                'affiliation' => ['Shays’ Rebellion'],
                'bio' => 'Charles Rose was a participant in Shays’ Rebellion who, with John Bly, was hanged at Lenox, Massachusetts, on December 6, 1787 for his part in the uprising of debt-ridden farmers — one of the few rebels put to death while the movement’s leaders were pardoned.',
                'charges' => 'Crimes committed during Shays’ Rebellion (charges of treason and robbery arising from the uprising).',
                'convicted' => 'Yes — convicted and sentenced to death.',
                'sentence' => 'Death. Hanged at Lenox, Massachusetts, on December 6, 1787.',
                'death_date' => '1787-12-06',
                'released' => false,
            ],

            // ---- Whiskey Rebellion (1794) ----
            [
                'name' => 'Philip Vigol', 'first' => 'Philip', 'last' => 'Vigol', 'aka' => 'Philip Wigle',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s',
                'ideologies' => ['Agrarian populism', 'Anti-tax'],
                'affiliation' => ['Whiskey Rebellion'],
                'bio' => 'Philip Vigol (also spelled Wigle or Weigel) was a western Pennsylvania farmer who took part in the Whiskey Rebellion of 1794, the uprising against Alexander Hamilton’s federal excise tax on whiskey; he had assaulted a tax collector and burned his house. In 1795 he and John Mitchell became the first two people ever convicted of treason against the United States, under a sweeping definition of treason as “levying war” by resisting a federal law. Both were sentenced to hang, but President George Washington pardoned them.',
                'charges' => 'Treason against the United States — for armed resistance to the federal whiskey excise (assaulting a tax collector and burning his house).',
                'convicted' => 'Yes — one of the first two people ever convicted of treason against the United States (1795).',
                'sentence' => 'Death by hanging, pardoned by President George Washington in November 1795.',
                'released' => true,
            ],
            [
                'name' => 'John Mitchell', 'first' => 'John', 'last' => 'Mitchell',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s',
                'ideologies' => ['Agrarian populism', 'Anti-tax'],
                'affiliation' => ['Whiskey Rebellion'],
                'bio' => 'John Mitchell was a western Pennsylvania participant in the Whiskey Rebellion of 1794 who was persuaded to rob the U.S. mail during the uprising. In 1795 he and Philip Vigol became the first two people convicted of treason against the United States. Sentenced to death, Mitchell — whom Washington regarded as a simple, easily led man — was pardoned along with Vigol by the President in November 1795.',
                'charges' => 'Treason against the United States — for his part in the Whiskey Rebellion (robbing the U.S. mail).',
                'convicted' => 'Yes — one of the first two people ever convicted of treason against the United States (1795).',
                'sentence' => 'Death by hanging, pardoned by President George Washington in November 1795.',
                'released' => true,
            ],

            // ---- Fries’s Rebellion (1799) ----
            [
                'name' => 'John Fries', 'first' => 'John', 'last' => 'Fries',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1790s',
                'ideologies' => ['Agrarian populism', 'Anti-tax'],
                'affiliation' => ['Fries’s Rebellion'],
                'bio' => 'John Fries was a Pennsylvania Dutch auctioneer and militia veteran who led the resistance to the federal house tax of 1798 in an uprising known as Fries’s Rebellion — the third of the early Republic’s tax revolts, after Shays’ and the Whiskey Rebellion. When his neighbors were arrested for refusing to pay, Fries led an armed band that freed them from a federal marshal. He was captured, tried, and convicted of treason twice, and sentenced to hang — but in 1800 President John Adams, over the objections of his own cabinet, pardoned Fries and the others condemned with him.',
                'charges' => 'Treason against the United States — for leading armed resistance to the 1798 federal house tax and freeing prisoners from a federal marshal.',
                'convicted' => 'Yes — convicted of treason twice (1799 and 1800).',
                'sentence' => 'Death by hanging, pardoned by President John Adams in May 1800.',
                'released' => true,
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                // These people were pre-seeded as empty stubs, so fill the
                // existing record instead of skipping it. fill() touches only
                // the listed columns, preserving any photo already present.
                $existing = Prisoner::withUnderReview()->where('name', $p['name'])->first();
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?? null,
                    'last_name' => $p['last'] ?? null,
                    'aka' => $p['aka'] ?? null,
                    'description' => $p['bio'],
                    'gender' => $p['gender'] ?? null,
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'] ?? null,
                    'ideologies' => $p['ideologies'] ?? [],
                    'affiliation' => $p['affiliation'] ?? [],
                    'death_date' => $p['death_date'] ?? null,
                    'in_custody' => false,
                    'released' => $p['released'] ?? true,
                    'in_exile' => $p['in_exile'] ?? false,
                    'awaiting_trial' => false,
                ]);
                $prisoner->save();

                // Rebuild the single case so re-runs (and the empty stub's
                // placeholder case) collapse to exactly one.
                $prisoner->cases()->delete();
                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['institution_id'] ?? null,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);

                $this->info(($existing ? 'Filled: ' : 'Added: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
