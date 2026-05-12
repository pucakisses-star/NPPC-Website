<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Installs the three Irish-3 mug-shot photos cropped from the 1990
 * Constitutional Defense Fund flyer (Richard Johnson, Christina Reid,
 * Martin Quigley) and links them on the prisoner records.
 */
final class AddBostonIrish3Photos extends Command {
    protected $signature = 'archive:add-boston-irish-3-photos';
    protected $description = 'Install Richard Johnson / Christina Reid / Martin Quigley photos and link them on the prisoner records';

    public function handle(): int {
        Storage::disk('public')->makeDirectory('prisoners');

        $map = [
            'Richard Clark Johnson' => 'richard-clark-johnson.jpg',
            'Christina L. Reid' => 'christina-l-reid.jpg',
            'Martin Quigley' => 'martin-quigley.jpg',
        ];

        $linked = 0;
        foreach ($map as $name => $file) {
            $src = database_path("photos/boston-irish-3/{$file}");
            if (! is_file($src)) {
                $this->warn("Source not found: {$src}");

                continue;
            }
            $relative = 'prisoners/'.$file;
            Storage::disk('public')->put($relative, file_get_contents($src));

            $p = Prisoner::where('name', $name)->first();
            if (! $p) {
                $this->warn("Prisoner not found: {$name}");

                continue;
            }
            if (empty($p->photo)) {
                $p->photo = $relative;
                $p->save();
                $this->info("Linked photo for {$name}.");
                $linked++;
            } else {
                $this->info("{$name} already has a photo — leaving alone.");
            }
        }

        $this->info("\nDone. Linked={$linked}");

        return self::SUCCESS;
    }
}
