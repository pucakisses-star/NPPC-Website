<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Enriches Juan Otero's thin record from the "Free Juan Otero" defense flyer
 * (1973) and the reported appellate record (People v. Otero, 45 A.D.2d 952, 359
 * N.Y.S.2d 318): a fuller sourced bio, the burglary/grand-larceny charges,
 * the March 20, 1973 Bronx County conviction (concurrent terms up to 5 years)
 * and its unanimous reversal on September 26, 1974, plus the portrait cropped
 * from the flyer (non-free/fair-use; see CREDITS-nonfree.md). Updates the
 * existing record in place (the movement-archive importer skips names that
 * already exist). Idempotent.
 */
final class EnrichJuanOtero extends Command
{
    protected $signature = 'prisoners:enrich-juan-otero';

    protected $description = 'Enrich Juan Otero from the 1973 defense flyer and attach his portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Juan Otero')->first();
        if (! $prisoner) {
            $this->warn('Juan Otero not found — run prisoners:add-movement-archive-prisoners first.');

            return self::SUCCESS;
        }

        $prisoner->fill([
            'first_name' => 'Juan',
            'last_name' => 'Otero',
            'aka' => 'Juan Otero Jr.',
            'gender' => 'Male',
            'race' => 'Hispanic',
            'state' => 'New York',
            'era' => '1970s',
            'ideologies' => ['Community organizing', 'Puerto Rican movement'],
            'affiliation' => ['Committee to Defend Juan Otero', 'Puerto Rican Socialist Party'],
            'description' => 'Juan Otero (Juan Otero Jr.) was a South Bronx Puerto Rican community activist and '
                .'construction-trades anti-discrimination organizer — a family man, educational specialist, and young '
                .'father with no prior police record — who led pickets against discriminatory hiring at New York '
                .'building sites. On March 20, 1973 he was convicted after a jury trial in Bronx County, on '
                .'consolidated indictments, of burglary in the second and third degrees, two counts of grand larceny '
                .'in the third degree, and attempted grand larceny, and sentenced to concurrent indeterminate terms '
                .'of up to five years. A defense campaign — the Committee to Defend Juan Otero and a Puerto Rican '
                .'Socialist Party statement carried in Workers World — held that he had been framed on the burglary '
                .'charges by "vengeful Bronx cops" and racist building-trades interests in retaliation for his '
                .'organizing. On September 26, 1974 the Appellate Division unanimously reversed his conviction and '
                .'ordered a new trial (People v. Otero, 45 A.D.2d 952, 359 N.Y.S.2d 318): his defense was alibi, the '
                .'conviction rested entirely on identification testimony whose conflicting character "casts '
                .'considerable doubt" on the charges, and the court found improper bolstering of that eyewitness '
                .'identification. His exact arrest and release dates are not documented; he was imprisoned during '
                .'1973, with a possible maximum documented custody of March 20, 1973 – September 26, 1974 (about 555 '
                .'days) if held continuously.',
            'in_custody' => false,
            'released' => true,
            'in_exile' => false,
            'awaiting_trial' => false,
        ]);
        $prisoner->save();

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
        $case->prisoner_id = $prisoner->id;
        $case->charges = 'Burglary in the second and third degrees, two counts of grand larceny in the third degree, '
            .'and attempted grand larceny (consolidated indictments, jury trial, Bronx County) — which the "Free Juan '
            .'Otero" campaign and a Puerto Rican Socialist Party statement called a frame-up by Bronx police and '
            .'building-trades interests in retaliation for his anti-discrimination organizing.';
        $case->convicted = 'Convicted March 20, 1973 (jury, Bronx County); conviction unanimously reversed and a new '
            .'trial ordered September 26, 1974 — People v. Otero, 45 A.D.2d 952, 359 N.Y.S.2d 318 (identification '
            .'testimony "casts considerable doubt"; improper bolstering of eyewitness ID).';
        $case->sentence = 'Concurrent indeterminate terms of up to five years (up to 5 years on each burglary count, '
            .'up to 4 years on each grand-larceny count; unconditional discharge on the attempt). Actual time served '
            .'not documented — possible maximum custody March 20, 1973 – September 26, 1974 (~555 days).';
        $case->setPartialDate('incarceration_date', 1973, 3, 20);
        $case->setPartialDate('sentenced_date', 1973, 3, 20);
        $case->setPartialDate('release_date', 1974, 9, 26);
        $case->save();

        $src = database_path('data/photos/nonfree/juan-otero.jpg');
        if (is_file($src)) {
            Storage::disk('public')->makeDirectory('prisoners');
            $rel = 'prisoners/juan-otero.jpg';
            Storage::disk('public')->put($rel, (string) file_get_contents($src));
            $prisoner->photo = $rel;
            $prisoner->save();
            $this->info('  attached portrait from the flyer.');
        } else {
            $this->warn('  photo source not found: '.$src);
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Enriched Juan Otero (slug: '.$prisoner->slug.').');

        return self::SUCCESS;
    }
}
