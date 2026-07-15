<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches the studio portrait of the Rev. Charles Billups — the Alabama
 * Christian Movement for Human Rights leader arrested with Fred Shuttlesworth
 * in Birmingham — to his existing prisoner record. The image is a mid-20th-
 * century studio photo, committed under a fair-use / memorial rationale in
 * photos/nonfree/ (see CREDITS-nonfree.md). Matched by name + the ACMHR
 * affiliation. Idempotent — always refreshes the stored copy.
 */
final class SetCharlesBillupsPhoto extends Command
{
    protected $signature = 'prisoners:set-charles-billups-photo';

    protected $description = 'Attach the portrait of Rev. Charles Billups (Birmingham / ACMHR)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'Charles Billups')
            ->get()
            ->first(fn ($p) => in_array('Alabama Christian Movement for Human Rights', (array) $p->affiliation, true))
            ?? Prisoner::withUnderReview()->where('name', 'Charles Billups')->first();

        if (! $prisoner) {
            $this->error('Charles Billups not found.');

            return self::FAILURE;
        }

        $src = database_path('data/photos/nonfree/charles-billups.jpg');
        if (! is_file($src)) {
            $this->error('Photo source not found: '.$src);

            return self::FAILURE;
        }

        Storage::disk('public')->makeDirectory('prisoners');
        Storage::disk('public')->put('prisoners/charles-billups.jpg', (string) file_get_contents($src));
        $prisoner->photo = 'prisoners/charles-billups.jpg';
        $prisoner->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("Set photo for {$prisoner->name} -> prisoners/charles-billups.jpg");

        return self::SUCCESS;
    }
}
