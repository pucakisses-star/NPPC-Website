<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Restores the missing Ezra Heywood photograph from Wikimedia Commons.
 * Used to re-create a deleted file. The image is in the public domain
 * (Heywood died 1893).
 *
 * Wikimedia source page:
 *   https://commons.wikimedia.org/wiki/File:Ezra_Heywood.jpg
 */
final class RestoreEzraHeywoodPhoto extends Command {
    protected $signature = 'prisoners:restore-ezra-heywood-photo';
    protected $description = 'Re-download the Ezra Heywood portrait from Wikimedia Commons and link it on the prisoner record';

    public function handle(): int {
        $url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Ezra_Heywood.jpg/600px-Ezra_Heywood.jpg';

        $response = Http::withHeaders([
            'User-Agent' => 'NPPC-Website/1.0 (https://nationalpoliticalprisonercoalition.org)',
        ])->timeout(30)->get($url);

        if (! $response->successful()) {
            $this->error("Wikimedia returned HTTP {$response->status()} — falling back to the full-resolution upload URL.");
            $response = Http::withHeaders([
                'User-Agent' => 'NPPC-Website/1.0 (https://nationalpoliticalprisonercoalition.org)',
            ])->timeout(30)->get('https://upload.wikimedia.org/wikipedia/commons/4/4f/Ezra_Heywood.jpg');
            if (! $response->successful()) {
                $this->error("Both URLs failed (status={$response->status()}). Aborting.");

                return self::FAILURE;
            }
        }

        $bytes = $response->body();
        if (strlen($bytes) < 1000) {
            $this->error('Downloaded file is suspiciously small ('.strlen($bytes).' bytes). Aborting.');

            return self::FAILURE;
        }

        $relative = 'prisoners/ezra-heywood.jpg';
        Storage::disk('public')->makeDirectory('prisoners');
        Storage::disk('public')->put($relative, $bytes);
        $this->info('Saved '.strlen($bytes).' bytes to '.$relative);

        $prisoner = Prisoner::where('slug', 'ezra-heywood')->orWhere('name', 'Ezra Heywood')->first();
        if (! $prisoner) {
            $this->warn('Prisoner record for Ezra Heywood not found — file saved but not linked.');

            return self::SUCCESS;
        }

        $prisoner->photo = $relative;
        $prisoner->save();
        $this->info('Linked '.$relative.' on prisoner '.$prisoner->name.' ('.$prisoner->slug.').');

        return self::SUCCESS;
    }
}
