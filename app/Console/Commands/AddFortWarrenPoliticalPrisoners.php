<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the Confederate political prisoners named on the "Political Prisoners"
 * historical marker at Fort Warren, Boston Harbor (Boston Harbor Islands State
 * Park):
 *
 *  - James Murray Mason and John Slidell, the two Confederate envoys seized
 *    from the British mail steamer RMS Trent in November 1861 (the Trent
 *    Affair) and held at Fort Warren until released in January 1862.
 *  - Alexander H. Stephens, Vice President of the Confederacy, imprisoned at
 *    Fort Warren for about five months after the war's end in 1865.
 *
 * Facts were researched from Wikipedia and corroborating sources; dates are
 * stored at the precision actually documented and left blank where unverified.
 *
 * Idempotent: each prisoner is skipped if a record with the same name already
 * exists. Safe to re-run.
 */
final class AddFortWarrenPoliticalPrisoners extends Command
{
    protected $signature = 'prisoners:add-fort-warren-political-prisoners';

    protected $description = 'Add the Fort Warren political prisoners (Mason, Slidell, Alexander H. Stephens)';

    public function handle(): int
    {
        $fortWarren = Institution::firstOrCreate(['name' => 'Fort Warren'], ['city' => 'Boston', 'state' => 'Massachusetts']);

        $figures = [
            [
                'name' => 'James Murray Mason', 'first_name' => 'James', 'middle_name' => 'Murray', 'last_name' => 'Mason',
                'gender' => 'Male', 'birth' => '1798-11-03', 'death' => '1871-04-28',
                'state' => 'Virginia', 'ideologies' => ['Secessionism'], 'affiliation' => ['Confederate States of America'],
                'description' => "James Murray Mason was a Virginia politician who served in the U.S. House and Senate — where he authored the Fugitive Slave Act of 1850 — before becoming a Confederate diplomat during the Civil War. In 1861 the Confederacy appointed him envoy to Great Britain to seek diplomatic recognition. Traveling with fellow commissioner John Slidell, Mason boarded the British mail steamer RMS Trent. On November 8, 1861, the USS San Jacinto, commanded by Captain Charles Wilkes, stopped the Trent and removed the two envoys, whom Wilkes treated as 'contraband' subject to seizure. They were taken to Fort Warren in Boston Harbor and held without trial. The seizure provoked the 'Trent Affair,' a grave diplomatic crisis that nearly brought Great Britain into the war on the Confederate side. To defuse it, the Lincoln administration released the envoys, and Mason and Slidell set sail for England on January 1, 1862. Mason never secured British recognition of the Confederacy and died in Virginia in 1871.",
                'charges' => "No formal criminal charges; seized as 'contraband' under Captain Charles Wilkes's interpretation of maritime law during the Trent Affair.",
                'arrest' => '1861-11-08', 'incarceration' => '1861-11', 'release' => '1862-01-01',
                'convicted' => 'Held without trial; released by the Lincoln administration and set sail for England on January 1, 1862.',
            ],
            [
                'name' => 'John Slidell', 'first_name' => 'John', 'middle_name' => null, 'last_name' => 'Slidell',
                'gender' => 'Male', 'birth' => '1793', 'death' => '1871-07-09',
                'state' => 'Louisiana', 'ideologies' => ['Secessionism'], 'affiliation' => ['Confederate States of America'],
                'description' => "John Slidell was a New York-born Louisiana politician and former U.S. senator who became a Confederate diplomat during the Civil War. In 1861 the Confederacy appointed him commissioner to France to seek diplomatic recognition and support. Traveling with fellow envoy James Murray Mason, Slidell sailed aboard the British mail steamer RMS Trent. On November 8, 1861, the USS San Jacinto under Captain Charles Wilkes stopped the Trent and removed the two commissioners as 'contraband.' Slidell and Mason were imprisoned without trial at Fort Warren in Boston Harbor. The seizure triggered the Trent Affair, a serious crisis between the United States and Great Britain, which protested the violation of its neutral rights and prepared for possible war. To avert conflict, the Lincoln administration released the envoys, and they set sail for England on January 1, 1862; Slidell continued on to France. He never secured French recognition of the Confederacy, remained in Europe after the war, and died on the Isle of Wight in 1871.",
                'charges' => "No formal criminal charges; seized as 'contraband' under Captain Charles Wilkes's interpretation of maritime law during the Trent Affair.",
                'arrest' => '1861-11-08', 'incarceration' => '1861-11', 'release' => '1862-01-01',
                'convicted' => 'Held without trial; released by the Lincoln administration and set sail for England on January 1, 1862.',
            ],
            [
                'name' => 'Alexander H. Stephens', 'first_name' => 'Alexander', 'middle_name' => 'Hamilton', 'last_name' => 'Stephens',
                'aka' => 'Little Aleck', 'gender' => 'Male', 'birth' => '1812-02-11', 'death' => '1883-03-04',
                'state' => 'Georgia', 'ideologies' => ['Secessionism'], 'affiliation' => ['Confederate States of America'],
                'description' => "Alexander Hamilton Stephens was a Georgia politician who served as Vice President of the Confederate States of America from 1861 to 1865. He is widely remembered for his March 1861 'Cornerstone Speech,' in which he declared that slavery and white supremacy formed the foundation of the new Confederate government. After the collapse of the Confederacy, Stephens was arrested for treason against the United States at his home in Crawfordville, Georgia, on May 11, 1865. He was transported north and imprisoned at Fort Warren, the granite fortress in Boston Harbor used to hold high-profile Confederate prisoners, where he was held for about five months. He was released in October 1865 after being pardoned by President Andrew Johnson, and was never brought to trial. He later returned to politics, serving again in the U.S. House of Representatives and, briefly, as Governor of Georgia.",
                'charges' => 'Treason against the United States; held as a high-profile Confederate prisoner. No trial took place.',
                'arrest' => '1865-05-11', 'incarceration' => '1865-05', 'release' => '1865-10',
                'convicted' => 'Held without trial; released in October 1865 after a pardon by President Andrew Johnson.',
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($figures as $f) {
            DB::transaction(function () use ($f, $fortWarren, &$created, &$skipped) {
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
                    'institution_id' => $fortWarren->id,
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
