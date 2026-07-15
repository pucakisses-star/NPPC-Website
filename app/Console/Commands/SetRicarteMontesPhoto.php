<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches a portrait for Ricarte Montes García, the Liga Socialista
 * Puertorriqueña member expatriated and imprisoned in New York for resisting
 * the federal grand jury in Puerto Rico. The image is cropped from the news
 * photo of his arrest (fist raised, handcuffed) that runs alongside Carlos
 * Noya's essay "Judgment of the Grand Jury" in Breakthrough, scanned by the
 * Marxists Internet Archive (marxists.org/history/erol/ncm-8/noya.pdf).
 * Non-free / fair-use archival image (see CREDITS-nonfree.md). Only sets the
 * photo when the prisoner currently has none.
 */
final class SetRicarteMontesPhoto extends Command
{
    protected $signature = 'prisoners:set-ricarte-montes-photo';

    protected $description = 'Attach the cropped arrest portrait of Ricarte Montes García';

    public function handle(): int
    {
        $src = database_path('data/photos/nonfree/ricarte-montes-garcia.jpg');
        if (! is_file($src)) {
            $this->error('Source image not found: '.$src);

            return self::FAILURE;
        }

        $prisoner = Prisoner::withUnderReview()->where('slug', 'ricarte-montes-garcia')->first();
        if (! $prisoner) {
            $this->warn('Ricarte Montes García not found (slug ricarte-montes-garcia).');

            return self::SUCCESS;
        }

        if (! empty($prisoner->photo)) {
            $this->info('Ricarte Montes García already has a photo — leaving alone.');

            return self::SUCCESS;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        $relative = 'prisoners/ricarte-montes-garcia.jpg';
        Storage::disk('public')->put($relative, file_get_contents($src));
        $prisoner->photo = $relative;
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Linked photo for Ricarte Montes García.');

        return self::SUCCESS;
    }
}
