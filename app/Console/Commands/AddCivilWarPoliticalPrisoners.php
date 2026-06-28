<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds wartime (1861-1865) political prisoners of the U.S. Civil War — people
 * detained for political reasons rather than ordinary crime. Two groups are
 * represented:
 *
 *  - Civilians held by the Union under President Lincoln's suspension of the
 *    writ of habeas corpus (Baltimore officials and editors, Copperhead
 *    Democrats): Francis Key Howard, Dennis A. Mahony, George William Brown,
 *    George P. Kane, Edson B. Olds, Ross Winans, Henry May.
 *  - Southern Unionists imprisoned by the Confederate government for their
 *    loyalty to the Union: John Minor Botts and William G. "Parson" Brownlow.
 *
 * Vallandigham, Merryman and Milligan are already in the database and are not
 * re-added here. Facts were researched from Wikipedia and corroborating
 * sources; where a precise date could not be verified it is stored at the
 * coarser precision actually documented (or left blank) rather than guessed.
 *
 * Idempotent: each prisoner is skipped if a record with the same name already
 * exists. Safe to re-run.
 */
final class AddCivilWarPoliticalPrisoners extends Command
{
    protected $signature = 'prisoners:add-civil-war-political-prisoners';

    protected $description = 'Add Civil War (1861-1865) political prisoners — Union habeas-corpus detainees and Confederate-held Unionists';

    public function handle(): int
    {
        $fortMcHenry = Institution::firstOrCreate(['name' => 'Fort McHenry'], ['city' => 'Baltimore', 'state' => 'Maryland']);
        $oldCapitol = Institution::firstOrCreate(['name' => 'Old Capitol Prison'], ['city' => 'Washington', 'state' => 'District of Columbia']);
        $fortWarren = Institution::firstOrCreate(['name' => 'Fort Warren'], ['city' => 'Boston', 'state' => 'Massachusetts']);
        $fortLafayette = Institution::firstOrCreate(['name' => 'Fort Lafayette'], ['city' => 'New York', 'state' => 'New York']);
        $castleGodwin = Institution::firstOrCreate(['name' => 'Castle Godwin'], ['city' => 'Richmond', 'state' => 'Virginia']);
        $knoxJail = Institution::firstOrCreate(['name' => 'Knox County Jail'], ['city' => 'Knoxville', 'state' => 'Tennessee']);

        $figures = [
            [
                'name' => 'Francis Key Howard', 'first_name' => 'Francis', 'middle_name' => 'Key', 'last_name' => 'Howard',
                'aka' => 'Frank Key Howard', 'gender' => 'Male', 'birth' => '1826-10-25', 'death' => '1872-05-29',
                'state' => 'Maryland', 'ideologies' => ['Press Freedom', "States' Rights"], 'affiliation' => ['Baltimore Daily Exchange'],
                'description' => "Francis Key Howard was a Baltimore newspaper editor and a grandson of Francis Scott Key. As editor of the Daily Exchange, he wrote an editorial criticizing President Abraham Lincoln's suspension of the writ of habeas corpus and the imposition of martial law in Baltimore — including the arrests of the city's mayor, a sitting congressman, the police commissioners, and members of the city council. Just after midnight on September 13, 1861, he was arrested at his home without a warrant and held without any formal charge or trial. Howard was first confined at Fort McHenry in Baltimore — the same fort his grandfather had watched withstand British bombardment in 1814, an irony Howard noted bitterly — then transferred to Fort Lafayette in New York Bay and afterward to Fort Warren in Boston. He was imprisoned for fourteen months and released in November 1862, never having been told the basis for his detention. He recounted the experience in 'Fourteen Months in American Bastiles' (1863), which became a notable critique of the wartime suppression of civil liberties.",
                'institution' => $fortMcHenry,
                'charges' => "No formal charge; detained over an editorial criticizing Lincoln's suspension of habeas corpus and martial law in Baltimore. Never tried or told the basis for his arrest.",
                'arrest' => '1861-09-13', 'incarceration' => '1861-09-13', 'release' => '1862-11',
                'convicted' => 'Held without trial or formal charge; released November 1862.',
            ],
            [
                'name' => 'Dennis A. Mahony', 'first_name' => 'Dennis', 'middle_name' => 'Aloysius', 'last_name' => 'Mahony',
                'gender' => 'Male', 'birth' => '1821-01-20', 'death' => '1879-11-06',
                'state' => 'Iowa', 'ideologies' => ['Press Freedom', 'Anti-war'], 'affiliation' => ['Democratic Party (Copperhead faction)', 'Dubuque Herald'],
                'description' => "Dennis A. Mahony was an Irish-born newspaper editor and Democratic politician in Iowa, best known as editor of the Dubuque Herald. A sharp critic of President Abraham Lincoln and the Union conduct of the Civil War, he was arrested on August 14, 1862 by a U.S. marshal for publishing editorials alleged to be disloyal — part of the wartime suppression of dissenting political speech. He was transported from Dubuque to Washington, D.C., and held at the Old Capitol Prison without trial for roughly three months. He was released on November 10, 1862 after signing a loyalty oath, never having been tried or convicted. In 1863 he published 'The Prisoner of State,' a memoir of his arrest and confinement that became a widely cited account of Civil War-era restrictions on the press and political dissent in the North. He had earlier served in the Iowa House of Representatives and as sheriff of Dubuque County.",
                'institution' => $oldCapitol,
                'charges' => 'No formal charge or trial; arrested for publishing editorials alleged to be disloyal to the government.',
                'arrest' => '1862-08-14', 'incarceration' => '1862-08', 'release' => '1862-11-10',
                'convicted' => 'Held without trial; released November 10, 1862 after signing a loyalty oath.',
            ],
            [
                'name' => 'George William Brown', 'first_name' => 'George', 'middle_name' => 'William', 'last_name' => 'Brown',
                'aka' => 'George W. Brown', 'gender' => 'Male', 'birth' => '1812-10-13', 'death' => '1890',
                'state' => 'Maryland', 'ideologies' => ["States' Rights"], 'affiliation' => null,
                'description' => "George William Brown was Mayor of Baltimore (1860-1861) at the outbreak of the Civil War. He is most associated with the Pratt Street Riot of April 19, 1861, when secessionist crowds attacked the Sixth Massachusetts Regiment passing through Baltimore toward Washington — the war's first bloodshed — after which city authorities ordered railroad bridges north of Baltimore burned to halt the movement of further Union troops. With habeas corpus suspended, Brown was arrested at his home on September 12, 1861 as part of a wider roundup of Maryland officials suspected of Southern sympathies, and was held without any formal charge or trial. He was imprisoned briefly at Fort McHenry, then at Fort Monroe in Virginia for about two weeks, and finally at Fort Warren in Boston Harbor, where he was held for roughly fourteen months. He was released on November 27, 1862, never having been tried, and returned to Baltimore, where he later became a respected judge and a trustee of Johns Hopkins University.",
                'institution' => $fortWarren,
                'charges' => 'No formal charge; detained without trial under suspension of habeas corpus as a Maryland official suspected of Southern sympathies.',
                'arrest' => '1861-09-12', 'incarceration' => '1861-09-12', 'release' => '1862-11-27',
                'convicted' => 'Held without trial; never charged or convicted; released November 27, 1862.',
            ],
            [
                'name' => 'George P. Kane', 'first_name' => 'George', 'middle_name' => 'Proctor', 'last_name' => 'Kane',
                'aka' => 'George Proctor Kane', 'gender' => 'Male', 'birth' => '1817-08-04', 'death' => '1878-06-23',
                'state' => 'Maryland', 'ideologies' => ["States' Rights"], 'affiliation' => null,
                'description' => 'George Proctor Kane was Marshal of Police — head of the Baltimore City police force — at the start of the Civil War. Though he held Southern sympathies, his officers helped quell further violence during the Pratt Street Riot of April 1861. With Maryland a contested border state and Baltimore under Union military control, federal authorities regarded Kane as a security risk. On June 27, 1861 he was arrested at his home on St. Paul Street by federal soldiers in the night and taken to Fort McHenry; he was subsequently transferred to Fort Lafayette in New York and then to Fort Warren in Boston. He was confined for about fourteen months without trial, contracting malaria during his imprisonment and writing letters of protest to President Lincoln and Secretary of State Seward. Released in 1862, he went to Montreal. His detention is frequently cited as an example of the suspension of habeas corpus and the arrest of suspected Southern sympathizers in Maryland during the Civil War.',
                'institution' => $fortMcHenry,
                'charges' => 'No formal charge or trial; arrested as a suspected Confederate sympathizer and security risk under suspended habeas corpus.',
                'arrest' => '1861-06-27', 'incarceration' => null, 'release' => '1862',
                'convicted' => 'Held without trial about fourteen months; released in 1862.',
            ],
            [
                'name' => 'Edson B. Olds', 'first_name' => 'Edson', 'middle_name' => 'Baldwin', 'last_name' => 'Olds',
                'gender' => 'Male', 'birth' => '1802-06-03', 'death' => '1869-01-24',
                'state' => 'Ohio', 'ideologies' => ['Anti-war'], 'affiliation' => ['Democratic Party (Copperhead faction)'],
                'description' => "Edson Baldwin Olds was a physician and former Democratic U.S. Representative from Ohio who became a leader of the anti-war 'Copperhead' movement during the Civil War. On August 12, 1862, on the recommendation of Ohio Governor David Tod, federal authorities arrested him for allegedly discouraging enlistments in the Union army. He was imprisoned at Fort Lafayette in New York Harbor for roughly four months, including a period of solitary confinement, and was never formally charged or brought to trial. While he remained imprisoned, voters in Fairfield and Hocking counties elected him to the Ohio House of Representatives — an act widely seen as a protest against his arbitrary detention. He was released in December 1862 and returned to Lancaster, Ohio, to take his seat in the legislature. His case became a notable example of military detention used against political dissenters in the North.",
                'institution' => $fortLafayette,
                'charges' => 'Alleged treasonable acts, principally discouraging enlistments in the army; no formal criminal charge was filed.',
                'arrest' => '1862-08-12', 'incarceration' => '1862-08', 'release' => '1862-12',
                'convicted' => 'Held without trial; never formally charged; released December 1862.',
            ],
            [
                'name' => 'Ross Winans', 'first_name' => 'Ross', 'middle_name' => null, 'last_name' => 'Winans',
                'gender' => 'Male', 'birth' => '1796-10-17', 'death' => '1877-04-11',
                'state' => 'Maryland', 'ideologies' => ['Secessionism', "States' Rights"], 'affiliation' => ['Maryland House of Delegates'],
                'description' => "Ross Winans was a wealthy Baltimore inventor and railroad-equipment manufacturer who, at the outbreak of the Civil War, served as a pro-secession member of the Maryland House of Delegates. An outspoken states'-rights advocate, he introduced a resolution protesting the use of Northern militia to garrison Southern forts, and reports circulated that his works were manufacturing weapons for Baltimore's defense against Union troops. He was briefly arrested after the Baltimore riot of April 19, 1861, released, and promptly re-elected on a States Rights ticket. While returning to Baltimore on May 14, 1861 — a day after martial law had been declared in the city — he was arrested a second time by federal troops under General Benjamin Butler. Unlike the contemporaneous habeas corpus case of John Merryman, Winans's detention was not legally challenged, and he was quickly released after signing a parole pledging loyalty to the federal government. His arrest reflected the Lincoln administration's broader effort to suppress secessionist activity and hold Maryland in the Union.",
                'institution' => null,
                'charges' => "No formal charges; arrested for pro-secession political activity and suspicion of supplying weapons for Baltimore's defense.",
                'arrest' => '1861-05-14', 'incarceration' => null, 'release' => null,
                'convicted' => 'Not convicted; held briefly and quickly released after signing a parole pledging loyalty to the federal government.',
            ],
            [
                'name' => 'Henry May', 'first_name' => 'Henry', 'middle_name' => null, 'last_name' => 'May',
                'gender' => 'Male', 'birth' => '1816-02-13', 'death' => '1866-09-25',
                'state' => 'Maryland', 'ideologies' => ['Anti-war'], 'affiliation' => null,
                'description' => 'Henry May was a United States Congressman from Maryland representing a Baltimore district, elected as a Unionist to the Thirty-seventh Congress. In September 1861 he was arrested as part of a federal roundup, beginning shortly after midnight on September 12, that also detained Baltimore Mayor George William Brown and eleven Baltimore members of the Maryland legislature. May was taken into custody on suspicion of treason — without formal charges and without recourse to habeas corpus, which President Lincoln had suspended in Maryland — and held at Fort Lafayette in New York Harbor. He was released in December 1861 without any charges ever being brought or evidence produced, and returned to his seat in Congress. He was never tried or convicted. Drawing on his own imprisonment, in March 1862 May introduced legislation requiring the government either to indict political prisoners by grand jury or release them; the principle was incorporated into the Habeas Corpus Act of March 1863.',
                'institution' => $fortLafayette,
                'charges' => 'Suspicion of treason; no formal charges were ever brought.',
                'arrest' => '1861-09-12', 'incarceration' => '1861-09', 'release' => '1861-12',
                'convicted' => 'Held without trial; released December 1861 — no charges brought or evidence produced.',
            ],
            [
                'name' => 'John Minor Botts', 'first_name' => 'John', 'middle_name' => 'Minor', 'last_name' => 'Botts',
                'gender' => 'Male', 'birth' => '1802-09-16', 'death' => '1869-01-08',
                'state' => 'Virginia', 'ideologies' => ['Unionism'], 'affiliation' => ['Constitutional Union Party'],
                'description' => "John Minor Botts was a Virginia politician, lawyer, and former U.S. Congressman who became one of the most prominent Southern Unionists during the Civil War. A vocal opponent of secession, he remained loyal to the Union and wrote letters criticizing the Confederate government. After the Confederacy suspended the writ of habeas corpus, Richmond's Confederate provost marshal John H. Winder had Botts arrested without trial on March 2, 1862, jailing him alongside fellow Unionist Franklin Stearns. He was held in Richmond at Castle Godwin — a converted former slave jail — where he spent roughly eight weeks in solitary confinement. He was never tried or convicted, and was released after promising not to publish further incendiary letters. In 1863 he moved to a plantation in Culpeper County; he was briefly detained again in October 1863 on the order of General J.E.B. Stuart but released the same day. Botts stands as a leading example of wartime Unionist dissent suppressed by the Confederate government within the South.",
                'institution' => $castleGodwin,
                'charges' => 'No formal charge or trial; detained by Confederate authorities for his Unionist positions and letters opposing the Confederacy after habeas corpus was suspended.',
                'arrest' => '1862-03-02', 'incarceration' => null, 'release' => null,
                'convicted' => 'Held without trial by Confederate authorities (about eight weeks in solitary confinement); released after promising not to publish further letters against the Confederacy.',
            ],
            [
                'name' => 'William G. Brownlow', 'first_name' => 'William', 'middle_name' => 'Gannaway', 'last_name' => 'Brownlow',
                'aka' => 'Parson Brownlow', 'gender' => 'Male', 'birth' => '1805-08-29', 'death' => '1877-04-29',
                'state' => 'Tennessee', 'ideologies' => ['Unionism', 'Press Freedom'], 'affiliation' => ['Knoxville Whig'],
                'description' => "William Gannaway 'Parson' Brownlow was a Tennessee Unionist, Methodist preacher, and editor of the Knoxville Whig, through which he fiercely opposed secession and the Confederacy in East Tennessee. After Tennessee joined the Confederacy, his pro-Union editorials made him a target of Confederate authorities. On December 6, 1861, as he prepared to leave Knoxville, he was arrested and jailed on a charge of treason by Knox County Commissioner Robert B. Reynolds and Confederate States District Attorney John Crozier Ramsey, and held in the Knox County jail. After Confederate Secretary of War Judah P. Benjamin intervened, Brownlow was released from jail in late December 1861, and Confederate authorities — wary of making him a Unionist martyr — ordered his expulsion rather than risk a trial. He crossed into Union-controlled territory near Nashville on March 3, 1862, and in the North became a celebrated symbol of Unionist resistance. He was never tried or convicted, and later served as Governor of Tennessee (1865-1869) and a United States Senator (1869-1875).",
                'institution' => $knoxJail,
                'charges' => 'Treason against the Confederacy; never tried or convicted.',
                'arrest' => '1861-12-06', 'incarceration' => '1861-12-06', 'release' => '1862-03-03',
                'convicted' => 'Not convicted; jailed by Confederate authorities, released from jail in late December 1861 and expelled to Union-controlled territory on March 3, 1862.',
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($figures as $f) {
            DB::transaction(function () use ($f, &$created, &$skipped) {
                if (Prisoner::withoutGlobalScopes()->where('name', $f['name'])->exists()) {
                    $this->warn("Skipping {$f['name']} — already exists.");
                    $skipped++;

                    return;
                }

                $prisoner = Prisoner::create(array_filter([
                    'name' => $f['name'],
                    'first_name' => $f['first_name'],
                    'middle_name' => $f['middle_name'] ?? null,
                    'last_name' => $f['last_name'],
                    'aka' => $f['aka'] ?? null,
                    'gender' => $f['gender'],
                    'state' => $f['state'],
                    'era' => '1800s',
                    'ideologies' => $f['ideologies'],
                    'affiliation' => $f['affiliation'] ?? null,
                    'in_custody' => false,
                    'released' => true,
                    'description' => $f['description'],
                ], fn ($v) => $v !== null));

                $this->applyDate($prisoner, 'birthdate', $f['birth'] ?? null);
                $this->applyDate($prisoner, 'death_date', $f['death'] ?? null);
                $prisoner->save();

                $case = new PrisonerCase([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $f['institution']?->id,
                    'charges' => $f['charges'],
                    'convicted' => $f['convicted'],
                ]);
                $this->applyDate($case, 'arrest_date', $f['arrest'] ?? null);
                $this->applyDate($case, 'incarceration_date', $f['incarceration'] ?? null);
                $this->applyDate($case, 'release_date', $f['release'] ?? null);
                $case->save();

                $this->info("Added {$prisoner->name} ({$prisoner->slug}).");
                $created++;
            });
        }

        $this->newLine();
        $this->info("Done. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * Apply a date string of precision YYYY, YYYY-MM, or YYYY-MM-DD to a model
     * field using the HasPartialDates trait, so the stored precision matches
     * what is actually documented. No-op for empty values.
     */
    private function applyDate(object $model, string $field, ?string $value): void
    {
        if (! $value) {
            return;
        }

        $parts = explode('-', $value);
        $year = (int) $parts[0];
        $month = isset($parts[1]) ? (int) $parts[1] : null;
        $day = isset($parts[2]) ? (int) $parts[2] : null;

        $model->setPartialDate($field, $year, $month, $day);
    }
}
