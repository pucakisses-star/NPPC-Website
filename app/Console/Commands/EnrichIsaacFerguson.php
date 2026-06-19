<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Enriches the existing Isaac E. Ferguson record (slug: isaac-e-ferguson).
 *
 * The existing record was sparse and its case charge was inaccurate — it
 * listed a "federal Espionage/Sedition Act" prosecution, but Ferguson's
 * five-year Sing Sing sentence was actually for NEW YORK criminal anarchy
 * (tried jointly with C.E. Ruthenberg; conviction reversed by the N.Y. Court
 * of Appeals in 1922 as a companion to People v. Gitlow). This fills in his
 * full name, dates, and home state and corrects the case. Idempotent.
 */
final class EnrichIsaacFerguson extends Command
{
    protected $signature = 'prisoners:enrich-isaac-ferguson';

    protected $description = 'Enrich and correct the existing Isaac E. Ferguson record (NY criminal anarchy case)';

    public function handle(): int
    {
        $p = Prisoner::withUnderReview()->where('slug', 'isaac-e-ferguson')->first();

        if (! $p) {
            $this->error('isaac-e-ferguson not found — aborting.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($p) {
            $p->first_name = 'Isaac';
            $p->middle_name = 'Edward';
            $p->last_name = 'Ferguson';
            $p->aka = 'Caxton';
            $p->birthdate = '1888-11-23';
            $p->death_date = '1964-02-11';
            $p->gender = 'Male';
            $p->race = 'White';
            $p->state = 'Illinois';
            $p->era = '1910s';
            $p->ideologies = ['Communism', 'Anti-Militarism'];
            $p->affiliation = ['Communist Party of America'];
            $p->in_custody = false;
            $p->released = true;
            $p->description = "Isaac Edward \"Ed\" Ferguson (1888–1964) was a Canadian-born, Chicago-based attorney and a founding leader of the Communist Party of America in 1919, serving on its Central Executive Committee as International Secretary and party legal counsel. During the First Red Scare he was indicted in New York and, in October 1920, tried jointly with party leader C.E. Ruthenberg in the Supreme Court of New York County under the state's 1902 Criminal Anarchy statute for their role in publishing the \"Left Wing Manifesto\"; both were convicted on October 29, 1920, sentenced to five years, and Ferguson was imprisoned at Sing Sing. He was released on bond in April 1922, and the New York Court of Appeals reversed both convictions in July 1922 — a companion decision to the landmark Gitlow case — for lack of evidence linking the men to the Manifesto's publication. After his release Ferguson withdrew from radical politics and built a long career as a prominent Chicago attorney; he died in Chicago in 1964.";
            $p->save();

            $singSing = Institution::firstOrCreate(['name' => 'Sing Sing Prison']);

            $case = $p->cases()->first() ?? new PrisonerCase(['prisoner_id' => $p->id]);
            $case->prisoner_id = $p->id;
            $case->institution_id = $singSing->id;
            $case->charges = "Criminal anarchy, under New York's 1902 Criminal Anarchy Law (Penal Law §§ 160–161), for serving on the National Council of the Left Wing, which published the \"Left Wing Manifesto\" in The Revolutionary Age (1919)";
            $case->judge = 'Bartow S. Weeks';
            $case->convicted = 'Convicted October 29, 1920 (tried jointly with C.E. Ruthenberg); conviction reversed by the New York Court of Appeals in July 1922 (companion to People v. Gitlow)';
            $case->sentence = 'Five years; imprisoned at Sing Sing, released on $5,000 bond in April 1922, conviction later reversed';
            $case->incarceration_date = '1920-10-29';
            $case->release_date = '1922-04-24';
            $case->save();
        });

        $this->info('Enriched Isaac E. Ferguson (corrected to NY criminal anarchy; added full name, dates, Sing Sing case).');

        return self::SUCCESS;
    }
}
