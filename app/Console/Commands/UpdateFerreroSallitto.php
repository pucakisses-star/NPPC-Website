<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fills out the two San Francisco Italian anarchists of the 1930s Ferrero–
 * Sallitto deportation case (the government tied them to Marcus Graham's
 * magazine MAN!). Both were already in the database with thin descriptions,
 * empty cases, and a wrong "1936" date — the arrest was April 11, 1934. This
 * corrects and fills them, and attaches Domenick Sallitto's portrait
 * (non-free, credited in CREDITS-nonfree.md).
 *
 * Sources: libcom.org, "Immigrants faced deportation for political activity."
 * Idempotent — updates in place / never overwrites an existing photo.
 */
class UpdateFerreroSallitto extends Command
{
    protected $signature = 'prisoners:update-ferrero-sallitto';

    protected $description = 'Fill the Ferrero–Sallitto 1930s anarchist deportation cases and attach Sallitto\'s photo';

    public function handle(): int
    {
        $ellis = Institution::firstOrCreate(
            ['name' => 'Ellis Island Immigration Station'],
            ['city' => 'New York', 'state' => 'New York']
        );

        // --- Domenick Sallitto ---
        DB::transaction(function () use ($ellis) {
            $s = Prisoner::withUnderReview()->where('name', 'Domenick Sallitto')->first();
            if (! $s) {
                $this->warn('Domenick Sallitto not found.');

                return;
            }
            $s->description = 'Domenick Sallitto was an Italian-born anarchist in the San Francisco Bay Area who chaired anarchist meetings and, with Vincent Ferrero, was a defendant in one of the most prominent anarchist deportation cases of the 1930s. After sixteen years in the United States he was arrested on April 11, 1934 by immigration inspectors in Oakland, California and ordered deported to Fascist Italy, where anarchists faced imprisonment or death. A four-year defense campaign — backed by the ACLU (his attorney was Austin Lewis), the labor movement, and the anarchist press — followed, and in 1938 the Department of Labor canceled his deportation. After a long naturalization battle he was granted U.S. citizenship in January 1954, and he later volunteered for the ACLU.';
            $s->state = 'California';
            $s->era = '1930s';
            $s->ideologies = ['Anarchism'];
            $s->affiliation = ['MAN!'];
            $s->released = true;
            $s->in_custody = false;
            $s->save();

            $case = $s->cases()->first() ?? new PrisonerCase(['prisoner_id' => $s->id]);
            $case->fill([
                'prisoner_id' => $s->id,
                'charges' => 'Ordered deported to Fascist Italy for his anarchist activity (chairing anarchist meetings in the San Francisco Bay Area). Arrested April 11, 1934 by immigration inspectors in Oakland, California.',
                'convicted' => 'Ordered deported; after a four-year defense campaign the Department of Labor canceled the deportation in 1938. He was never deported and became a U.S. citizen in January 1954.',
                'arrest_date' => '1934-04-11',
            ]);
            $case->save();

            // Attach the portrait (non-free) if he has none.
            $src = database_path('data/photos/nonfree/domenick-sallitto.jpg');
            if (is_file($src) && empty($s->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/domenick-sallitto.jpg', file_get_contents($src));
                $s->photo = 'prisoners/domenick-sallitto.jpg';
                $s->save();
                $this->info('Linked photo for Domenick Sallitto.');
            }
            $this->info('Updated Domenick Sallitto (case filled).');
        });

        // --- Vincent Ferrero ---
        DB::transaction(function () use ($ellis) {
            $f = Prisoner::withUnderReview()->where('name', 'Vincent Ferrero')->first();
            if (! $f) {
                $this->warn('Vincent Ferrero not found.');

                return;
            }
            $f->description = 'Vincent Ferrero (Vincenzo Ferrero) was an Italian-born anarchist and restaurant worker in San Francisco who, with Domenick Sallitto, was a defendant in a prominent 1930s anarchist deportation case; the government tied the pair to the anarchist magazine MAN! (edited by Marcus Graham), to whose publisher Ferrero had rented space. After more than three decades in the United States he was ordered to surrender at Ellis Island for deportation to Fascist Italy. Representative Emanuel Celler of New York introduced a bill to prevent his deportation, but in 1938 — after some thirty-five years in the country — Ferrero was deported, accepting voluntary departure to an undisclosed country rather than be sent to Italy, where he feared imprisonment or death.';
            $f->state = 'California';
            $f->era = '1930s';
            $f->ideologies = ['Anarchism'];
            $f->affiliation = ['MAN!'];
            $f->save();

            $case = $f->cases()->first() ?? new PrisonerCase(['prisoner_id' => $f->id]);
            $case->fill([
                'prisoner_id' => $f->id,
                'institution_id' => $ellis->id,
                'charges' => 'Ordered deported to Fascist Italy for his anarchist activity; the government linked him to the anarchist magazine MAN! He was ordered to surrender at Ellis Island for deportation.',
                'convicted' => 'Ordered deported and deported in 1938, after about thirty-five years in the United States. He accepted voluntary departure to an undisclosed country rather than be returned to Fascist Italy.',
            ]);
            $case->save();
            $this->info('Updated Vincent Ferrero (case filled).');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
