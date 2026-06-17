<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Enriches Moana Cole's profile (the New Zealand member of the 1991 ANZUS
 * Plowshares action at Griffiss Air Force Base) with sourced case details —
 * arrest, conviction, sentence and deportation — and a portrait photo.
 *
 * Sources: lestweforget.org.nz/profiles/moana-cole and The Nuclear Resister.
 * Idempotent: re-running re-applies the same data and reuses the cached photo.
 */
final class UpdateMoanaCole extends Command
{
    protected $signature = 'prisoners:update-moana-cole';

    protected $description = "Enrich Moana Cole's profile (ANZUS Plowshares) with case details and a photo";

    private const PHOTO_SRC = 'https://lestweforget.org.nz/wp-content/uploads/2025/01/unnamed-1.jpg';

    private const PHOTO_PATH = 'prisoners/moana-cole.jpg';

    private const BIO = "Moana Cole is a New Zealand peace activist and one of the four Catholic Worker members of the ANZUS (Australia, New Zealand, U.S.) Peace Force Plowshares who entered Griffiss Air Force Base in Rome, New York, on January 1, 1991 to protest preparations for the Gulf War. While Susan Frankel and Bill Streit hammered and poured blood on a KC-135 refueling plane and a cruise-missile-armed B-52 bomber, Cole and Ciaron O'Reilly entered from the opposite end of the runway, marked it with a cross of blood, spray-painted messages including \"Love Your Enemies — Jesus Christ\" and \"Isaiah Strikes Again,\" and hammered on the runway for about an hour before being detained. The four were indicted on January 9, 1991 on federal charges of conspiracy and destruction of government property, facing up to 15 years. Held for two months and then released pre-trial, they were convicted by a jury in Syracuse in May 1991 and on August 20, 1991 sentenced to twelve months in prison and \$1,800 in restitution. Released from federal prison on June 15, 1992 after serving about ten months, she was then held for immigration proceedings and freed on bail pending a deportation hearing, returning to New Zealand under a court-ordered voluntary deportation in October 1992. She later completed a master's degree in law and works as a barrister providing legal aid in Christchurch.";

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where(function ($q) {
                $q->where('slug', 'moana-cole')->orWhere('name', 'Moana Cole');
            })
            ->first();

        if (! $prisoner) {
            $this->error('Moana Cole not found (slug "moana-cole" / name "Moana Cole").');

            return self::FAILURE;
        }

        $prisoner->description = self::BIO;
        $prisoner->era = '1990s';
        $prisoner->gender = 'Female';
        $prisoner->inmate_number = '03807-052'; // Federal Bureau of Prisons register number
        $prisoner->released = true;
        $prisoner->in_custody = false;
        $prisoner->save();

        $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
        $case->prisoner_id = $prisoner->id;
        $case->charges = 'Conspiracy and destruction of U.S. government property (federal sabotage / depredation) — the ANZUS Plowshares disarmament action at Griffiss Air Force Base, Rome, New York';
        $case->arrest_date = '1991-01-01';
        $case->incarceration_date = '1991-01-01';
        $case->release_date = '1992-06-15';
        $case->convicted = 'Yes — convicted by a jury in Syracuse, May 1991';
        $case->sentence = 'Twelve months in prison and $1,800 restitution (sentenced August 20, 1991); out of federal (BOP) custody June 15, 1992 after about ten months, then freed on bail pending a deportation hearing and voluntarily deported to New Zealand in October 1992';
        $case->save();

        $disk = Storage::disk('public');
        if (! $disk->exists(self::PHOTO_PATH)) {
            try {
                $resp = Http::withHeaders([
                    'User-Agent' => 'NPPC-Archive/1.0 (advocacy nonprofit)',
                    'Referer' => 'https://lestweforget.org.nz/profiles/moana-cole/',
                ])->timeout(60)->get(self::PHOTO_SRC);

                if ($resp->successful() && strlen($resp->body()) >= 1500) {
                    $disk->put(self::PHOTO_PATH, $resp->body());
                } else {
                    $this->warn('Photo download failed (HTTP '.$resp->status().'); profile text still updated.');
                }
            } catch (\Throwable $e) {
                $this->warn('Photo fetch error: '.$e->getMessage().'; profile text still updated.');
            }
        }

        if ($disk->exists(self::PHOTO_PATH)) {
            $prisoner->photo = self::PHOTO_PATH;
            $prisoner->save();
            $this->info('Photo set: '.self::PHOTO_PATH);
        }

        $this->info('Updated Moana Cole.');

        return self::SUCCESS;
    }
}
