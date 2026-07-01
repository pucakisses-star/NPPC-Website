<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds the civilian political prisoners named in the Rev. Isaac W. K. Handy's
 * 1863–1864 Fort Delaware prison diary, "United States Bonds; or, Duress by
 * Federal Authority" — fellow citizens held without trial during the Civil War
 * under the wartime suspension of habeas corpus (Southern sympathies, refusing
 * the loyalty oath, sheltering Confederates, blockade running, or as hostages).
 *
 * Era is recorded as "1800s" per project preference. Idempotent: prisoner:add
 * refuses duplicates by name, and this command backfills the released/in-custody
 * flags so re-runs are safe. Details are drawn from the diary (a primary source);
 * some spellings/dates are approximate.
 */
final class AddHandyDiaryPrisoners extends Command
{
    protected $signature = 'prisoner:add-handy-diary';

    protected $description = 'Add the civilian political prisoners named in Handy\'s Fort Delaware diary (1800s)';

    /** name, first, last, state, home, reason (lowercase clause after "was"), arrest, release */
    private const PEOPLE = [
        ['name' => 'John H. Waring', 'first' => 'John', 'last' => 'Waring', 'state' => 'Maryland', 'home' => "Prince George's County, Maryland", 'reason' => 'held roughly eight months without trial on charges of Southern sympathy, sheltering and feeding Confederate soldiers, and directing contraband mail, his property confiscated and his family banished, before being pardoned by President Lincoln', 'arrest' => null, 'release' => '1864-02-13'],
        ['name' => 'Henry W. Long', 'first' => 'Henry', 'last' => 'Long', 'state' => 'Delaware', 'home' => 'Sussex County, Delaware', 'reason' => "an outspoken Southern sympathizer arrested as a 'prisoner of State' to suppress his influence before the 1863 congressional election", 'arrest' => '1863-09-12', 'release' => '1864-03-25'],
        ['name' => 'Stephen J. Joice', 'first' => 'Stephen', 'last' => 'Joice', 'state' => 'Maryland', 'home' => 'Baltimore, Maryland', 'reason' => "the former editor of the Baltimore Republican, held under a sentence of banishment as a 'prisoner of State'", 'arrest' => '1864-02-04', 'release' => null],
        ['name' => 'Francis A. Richardson', 'first' => 'Francis', 'last' => 'Richardson', 'state' => 'Maryland', 'home' => 'Baltimore, Maryland', 'reason' => "the former editor of the Baltimore Republican, held under a sentence of banishment as a 'prisoner of State'", 'arrest' => '1864-02-04', 'release' => null],
        ['name' => 'Robert W. Rasin', 'first' => 'Robert', 'last' => 'Rasin', 'state' => 'Maryland', 'home' => 'Baltimore, Maryland', 'reason' => "held as a 'prisoner of State' with, by his account, no charge or record ever produced against him", 'arrest' => '1863-11-26', 'release' => null],
        ['name' => 'Carlos M. De La Mar', 'first' => 'Carlos', 'last' => 'De La Mar', 'state' => 'Delaware', 'home' => 'Wilmington, Delaware', 'reason' => 'a British subject held as a political prisoner whose stated grounds for detention were never revealed', 'arrest' => '1863-11-26', 'release' => '1864-03-01'],
        ['name' => 'William Bright', 'first' => 'William', 'last' => 'Bright', 'state' => 'Delaware', 'home' => 'Wilmington, Delaware', 'reason' => 'a Methodist layman held for Southern sympathies on much the same grounds as Handy himself', 'arrest' => '1863-07-06', 'release' => '1863-08-11'],
        ['name' => 'James S. Pleasants', 'first' => 'James', 'last' => 'Pleasants', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => 'charged with harboring a Confederate soldier and giving aid and comfort to the enemy, and at first condemned by a court-martial to be hanged', 'arrest' => null, 'release' => '1863-07-01'],
        ['name' => 'Alfred Campbell Belt', 'first' => 'Alfred', 'last' => 'Belt', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => 'a Loudoun County civilian detainee for whom two Union men were taken to Richmond as hostages', 'arrest' => null, 'release' => null],
        ['name' => 'Slaughter Bradford', 'first' => 'Slaughter', 'last' => 'Bradford', 'state' => 'Virginia', 'home' => 'Culpeper County, Virginia', 'reason' => 'a Culpeper County civilian held as a prisoner who refused the oath of allegiance and was released on parole', 'arrest' => '1863-11-12', 'release' => '1863-11-17'],
        ['name' => 'Charles H. Drummond', 'first' => 'Charles', 'last' => 'Drummond', 'state' => 'Virginia', 'home' => 'Norfolk, Virginia', 'reason' => "brought from Fort Norfolk after 'a sort of trial' and confined among the political prisoners", 'arrest' => '1863-11-08', 'release' => null],
        ['name' => 'John Shanks', 'first' => 'John', 'last' => 'Shanks', 'state' => 'Virginia', 'home' => 'Norfolk, Virginia', 'reason' => "a Norfolk 'underground' mail carrier condemned to imprisonment after a summary trial", 'arrest' => '1863-11-08', 'release' => null],
        ['name' => 'Jesse D. Sykes', 'first' => 'Jesse', 'last' => 'Sykes', 'state' => 'Virginia', 'home' => 'Princess Anne County, Virginia', 'reason' => 'sentenced to about a year for aiding men he believed were Confederate deserters by giving them a map toward Richmond', 'arrest' => '1863-11-08', 'release' => '1864-03-18'],
        ['name' => 'B. H. McCown', 'first' => 'B. H.', 'last' => 'McCown', 'state' => 'Delaware', 'home' => 'Smyrna, Delaware', 'reason' => 'a Smyrna dentist of strong Southern sympathies who circulated a petition on Handy\'s behalf', 'arrest' => '1863-09-28', 'release' => '1863-11-17'],
        ['name' => 'Thomas Cannon', 'first' => 'Thomas', 'last' => 'Cannon', 'state' => 'Delaware', 'home' => 'Sussex County, Delaware', 'reason' => "one of six Sussex County, Delaware citizens brought in together 'charged with disloyalty' — Southern sympathies, refusing to walk under the U.S. flag, and aiding a Confederate's escape", 'arrest' => '1863-10-10', 'release' => null],
        ['name' => 'William H. Hitch', 'first' => 'William', 'last' => 'Hitch', 'state' => 'Delaware', 'home' => 'Sussex County, Delaware', 'reason' => "one of six Sussex County, Delaware citizens brought in together 'charged with disloyalty' for Southern sympathies", 'arrest' => '1863-10-10', 'release' => null],
        ['name' => 'Patrick H. Hearne', 'first' => 'Patrick', 'last' => 'Hearne', 'state' => 'Delaware', 'home' => 'Laurel, Sussex County, Delaware', 'reason' => "one of six Sussex County, Delaware citizens 'charged with disloyalty' for Southern sympathies", 'arrest' => '1863-10-10', 'release' => null],
        ['name' => 'Henry C. Hearne', 'first' => 'Henry', 'last' => 'Hearne', 'state' => 'Delaware', 'home' => 'Sussex County, Delaware', 'reason' => "one of six Sussex County, Delaware citizens 'charged with disloyalty' for Southern sympathies", 'arrest' => '1863-10-10', 'release' => null],
        ['name' => 'William Bradley', 'first' => 'William', 'last' => 'Bradley', 'state' => 'Delaware', 'home' => 'Sussex County, Delaware', 'reason' => "one of six Sussex County, Delaware citizens 'charged with disloyalty' for Southern sympathies", 'arrest' => '1863-10-10', 'release' => null],
        ['name' => 'William C. Rust', 'first' => 'William', 'last' => 'Rust', 'state' => 'Delaware', 'home' => 'Sussex County, Delaware', 'reason' => "one of six Sussex County, Delaware citizens 'charged with disloyalty' for Southern sympathies", 'arrest' => '1863-10-10', 'release' => null],
        ['name' => 'John A. Atwood', 'first' => 'John', 'last' => 'Atwood', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => 'held about eight months, never brought to trial, on charges of harboring Union deserters, guiding troops, and spying', 'arrest' => null, 'release' => '1863-11-23'],
        ['name' => 'John Mason', 'first' => 'John', 'last' => 'Mason', 'state' => 'Virginia', 'home' => 'Accomac County, Virginia', 'reason' => 'confined about six months on a charge of smuggling goods to the South, arrested near his own home', 'arrest' => null, 'release' => '1863-08-01'],
        ['name' => 'Charles Wright', 'first' => 'Charles', 'last' => 'Wright', 'state' => 'Virginia', 'home' => 'Accomac County, Virginia', 'reason' => 'an Eastern Shore citizen confined for blockade running', 'arrest' => null, 'release' => '1863-08-24'],
        ['name' => 'Gillet Thorn', 'first' => 'Gillet', 'last' => 'Thorn', 'state' => 'Virginia', 'home' => 'Accomac County, Virginia', 'reason' => 'an Eastern Shore citizen confined for blockade running', 'arrest' => null, 'release' => '1863-08-24'],
        ['name' => 'T. Jefferson Shreve', 'first' => 'T. Jefferson', 'last' => 'Shreve', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => 'imprisoned for guiding the Confederate Army on the Potomac and otherwise aiding the South', 'arrest' => null, 'release' => null],
        ['name' => 'Joseph W. Boucher', 'first' => 'Joseph', 'last' => 'Boucher', 'state' => 'Washington, D.C.', 'home' => 'Georgetown, D.C.', 'reason' => 'a Georgetown merchant sentenced for helping a woman travel south and for blockade running', 'arrest' => '1864-04-19', 'release' => '1864-10-04'],
        ['name' => 'Samuel H. Pairo', 'first' => 'Samuel', 'last' => 'Pairo', 'state' => 'Maryland', 'home' => 'Baltimore, Maryland', 'reason' => 'a young Baltimorean sentenced by Gen. Lew Wallace for blockade running', 'arrest' => '1864-04-19', 'release' => null],
        ['name' => 'Kent Williams', 'first' => 'Kent', 'last' => 'Williams', 'state' => 'Maryland', 'home' => 'Baltimore, Maryland', 'reason' => 'a young Baltimorean sentenced by Gen. Lew Wallace for blockade running', 'arrest' => '1864-04-19', 'release' => null],
        ['name' => 'William T. Aud', 'first' => 'William', 'last' => 'Aud', 'state' => 'Maryland', 'home' => 'Montgomery County, Maryland', 'reason' => "sentenced to a year for aiding a woman's escape to the South, roughly \$3,000 of his property and servants seized", 'arrest' => '1864-04-23', 'release' => null],
        ['name' => 'Hazwell Magruder', 'first' => 'Hazwell', 'last' => 'Magruder', 'state' => 'Maryland', 'home' => "Prince George's County, Maryland", 'reason' => 'sentenced to a year for harboring and assisting Confederates, and denied parole to visit his dying wife', 'arrest' => '1864-04-07', 'release' => null],
        ['name' => 'Edward J. Devitt', 'first' => 'Edward', 'last' => 'Devitt', 'state' => null, 'home' => null, 'reason' => "a political prisoner condemned to hard labor 'during the war' with the fort's punishment detail", 'arrest' => null, 'release' => null],
        ['name' => 'Edward Worrell', 'first' => 'Edward', 'last' => 'Worrell', 'state' => 'Delaware', 'home' => 'Delaware City, Delaware', 'reason' => "a physician and Southern man charged, without evidence, with helping a prisoner escape from the fort, held in solitary confinement and freed only by President Lincoln's order in 1865", 'arrest' => '1864-07-12', 'release' => '1865-03-22'],
        ['name' => 'William Cross', 'first' => 'William', 'last' => 'Cross', 'state' => 'Virginia', 'home' => 'Leesburg, Virginia', 'reason' => 'a Leesburg citizen held as a hostage for civilians captured by Confederate ranger John Mosby', 'arrest' => '1864-08-21', 'release' => null],
        ['name' => 'E. L. Bentley', 'first' => 'E. L.', 'last' => 'Bentley', 'state' => 'Virginia', 'home' => 'Leesburg, Virginia', 'reason' => 'a Leesburg citizen held as a hostage for civilians captured by Mosby, later removed from confinement and unconditionally released', 'arrest' => '1864-08-21', 'release' => '1864-08-30'],
        ['name' => 'Charles F. Fadeley', 'first' => 'Charles', 'last' => 'Fadeley', 'state' => 'Virginia', 'home' => 'Leesburg, Virginia', 'reason' => 'a Leesburg citizen held as a hostage for civilians captured by Mosby', 'arrest' => '1864-08-21', 'release' => null],
        ['name' => 'George W. Ryan', 'first' => 'George', 'last' => 'Ryan', 'state' => 'Virginia', 'home' => 'Leesburg, Virginia', 'reason' => 'a Leesburg citizen held as a hostage for civilians captured by Mosby', 'arrest' => '1864-08-21', 'release' => null],
        ['name' => 'William S. Pickett', 'first' => 'William', 'last' => 'Pickett', 'state' => 'Virginia', 'home' => 'Leesburg, Virginia', 'reason' => 'a Leesburg citizen held as a hostage for civilians captured by Mosby', 'arrest' => '1864-08-21', 'release' => null],
        ['name' => 'John L. Rinker', 'first' => 'John', 'last' => 'Rinker', 'state' => 'Virginia', 'home' => 'Leesburg, Virginia', 'reason' => 'a Leesburg citizen held as a hostage for civilians captured by Mosby', 'arrest' => '1864-08-21', 'release' => null],
        ['name' => 'Thomas W. Edwards', 'first' => 'Thomas', 'last' => 'Edwards', 'state' => 'Virginia', 'home' => 'Leesburg, Virginia', 'reason' => 'a Leesburg citizen held as a hostage for civilians captured by Mosby, later removed from confinement and unconditionally released', 'arrest' => '1864-08-21', 'release' => '1864-08-30'],
        ['name' => 'Asa Rogers', 'first' => 'Asa', 'last' => 'Rogers', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => 'an aged militia officer seized without warning and held as a hostage, who refused a parole binding him to wartime neutrality', 'arrest' => '1864-09-10', 'release' => null],
        ['name' => 'O. A. Kinsolving', 'first' => 'O. A.', 'last' => 'Kinsolving', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => "an Episcopal minister (Gen. Rogers's son-in-law) seized as a hostage, whose habitual prayer for the Confederate President may have prompted his arrest", 'arrest' => '1864-09-10', 'release' => null],
        ['name' => 'George W. Harris', 'first' => 'George', 'last' => 'Harris', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => 'a Baptist minister seized as a hostage with the Loudoun County party', 'arrest' => '1864-09-10', 'release' => null],
        ['name' => 'F. L. Galleher', 'first' => 'F. L.', 'last' => 'Galleher', 'state' => 'Virginia', 'home' => 'Loudoun County, Virginia', 'reason' => "a Loudoun County citizen prisoner captured with Gen. Rogers's hostage party", 'arrest' => '1864-09-01', 'release' => null],
        ['name' => 'Dougherty', 'first' => null, 'last' => 'Dougherty', 'state' => 'Washington, D.C.', 'home' => 'Washington, D.C.', 'reason' => "an aged, ailing man imprisoned, as Handy records, 'for the crime of having built a State-house in South Carolina, and then presuming to return to his home'", 'arrest' => null, 'release' => null],
        ['name' => 'Frank A. Price', 'first' => 'Frank', 'last' => 'Price', 'state' => 'Delaware', 'home' => 'Delaware', 'reason' => "one of the Delaware 'politicals,' whose trunk and clothing were withheld by the authorities", 'arrest' => null, 'release' => null],
        ['name' => 'Noel', 'first' => null, 'last' => 'Noel', 'state' => null, 'home' => null, 'reason' => 'one of a group of political prisoners removed from confinement and unconditionally released', 'arrest' => null, 'release' => '1864-08-30'],
        ['name' => 'Mortimer C. Lovett', 'first' => 'Mortimer', 'last' => 'Lovett', 'state' => 'Maryland', 'home' => 'Maryland', 'reason' => 'held roughly twenty months, north and south, before being released on the oath of amnesty', 'arrest' => null, 'release' => '1864-02-26'],
        ['name' => 'William H. Gibson', 'first' => 'William', 'last' => 'Gibson', 'state' => 'Maryland', 'home' => 'Maryland', 'reason' => 'held without trial and released on the oath of amnesty', 'arrest' => null, 'release' => '1864-02-26'],
        ['name' => 'William H. Griffith', 'first' => 'William', 'last' => 'Griffith', 'state' => null, 'home' => null, 'reason' => "held without trial, fearing indefinite detention 'for the war,' and released on the oath of amnesty", 'arrest' => null, 'release' => '1864-02-26'],
        ['name' => 'Frank T. Grady', 'first' => 'Frank', 'last' => 'Grady', 'state' => 'Maryland', 'home' => 'Maryland', 'reason' => 'held without trial; he firmly refused the oath and was released on parole to remain in Maryland', 'arrest' => null, 'release' => '1864-03-11'],
        ['name' => 'Solomon Porter', 'first' => 'Solomon', 'last' => 'Porter', 'state' => 'Maryland', 'home' => 'Maryland', 'reason' => 'held as a political prisoner and released on the oath of amnesty', 'arrest' => null, 'release' => '1864-02-26'],
        ['name' => 'William L. Wier', 'first' => 'William', 'last' => 'Wier', 'state' => 'Delaware', 'home' => 'Delaware', 'reason' => 'a young Delawarean of strong Southern proclivities held as a political prisoner and released on the oath', 'arrest' => null, 'release' => '1864-04-01'],
        ['name' => 'Richards', 'first' => null, 'last' => 'Richards', 'state' => 'Delaware', 'home' => 'Delaware', 'reason' => 'a young Delawarean of strong Southern proclivities held as a political prisoner and released on the oath', 'arrest' => null, 'release' => '1864-04-01'],
    ];

    public function handle(): int
    {
        $count = 0;

        foreach (self::PEOPLE as $r) {
            $home = $r['home'] ?? null;
            $lead = $home ? "{$r['name']}, of {$home}, was " : "{$r['name']} was ";
            $desc = $lead.$r['reason'].". He was held as a civilian political prisoner at Fort Delaware during the U.S. Civil War, detained without trial under the wartime suspension of habeas corpus, and is named among the fellow prisoners in the Rev. Isaac W. K. Handy's 1863–1864 prison diary, 'United States Bonds; or, Duress by Federal Authority.'";

            $case = [
                'institution_name' => 'Fort Delaware',
                'institution_city' => 'Delaware City',
                'institution_state' => 'Delaware',
                'charges' => ucfirst($r['reason']).'.',
            ];
            if (! empty($r['arrest'])) {
                $case['arrest_date'] = $r['arrest'];
                $case['incarceration_date'] = $r['arrest'];
            }
            if (! empty($r['release'])) {
                $case['release_date'] = $r['release'];
            }

            $payload = [
                'name' => $r['name'],
                'description' => $desc,
                'gender' => 'Male',
                'ideologies' => ['Confederate sympathies'],
                'era' => '1800s',
                'in_custody' => false,
                'released' => true,
                'cases' => [$case],
            ];
            if (! empty($r['first'])) {
                $payload['first_name'] = $r['first'];
            }
            if (! empty($r['last'])) {
                $payload['last_name'] = $r['last'];
            }
            if (! empty($r['state'])) {
                $payload['state'] = $r['state'];
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $r['name'])->first();
            if ($prisoner) {
                $prisoner->in_custody = false;
                $prisoner->released = true;
                $prisoner->save();
                $count++;
            }
        }

        $this->info("\nDone. Ensured {$count} Handy-diary political prisoner(s), era 1800s.");

        return self::SUCCESS;
    }
}
