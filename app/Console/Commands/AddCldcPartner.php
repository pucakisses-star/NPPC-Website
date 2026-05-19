<?php

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the Civil Liberties Defense Center as a partner. Downloads
 * the logo from cldc.org into the public disk at runtime so the
 * binary isn't tracked in git.
 *
 * Idempotent: matches by name.
 */
final class AddCldcPartner extends Command {
    protected $signature = 'archive:add-cldc-partner';
    protected $description = 'Add the Civil Liberties Defense Center as a partner';

    private const NAME = 'Civil Liberties Defense Center';
    private const LOGO_SRC = 'https://cldc.org/wp-content/uploads/2025/04/CLDC-Brick-Raven-Logo.png';
    private const LOGO_PATH = 'partners/cldc-logo.png';

    public function handle(): int {
        if (! Storage::disk('public')->exists(self::LOGO_PATH)) {
            $bytes = @file_get_contents(self::LOGO_SRC);
            if ($bytes === false) {
                $this->error('Failed to download logo from '.self::LOGO_SRC);
                return self::FAILURE;
            }
            Storage::disk('public')->put(self::LOGO_PATH, $bytes);
            $this->info('Downloaded logo to '.self::LOGO_PATH);
        } else {
            $this->info('Logo already present at '.self::LOGO_PATH);
        }

        $payload = [
            'logo' => self::LOGO_PATH,
            'url' => 'https://cldc.org',
            'description' => 'Movement-lawyering nonprofit based in Eugene, Oregon, providing legal defense and education to climate activists, forest defenders, water protectors, anti-war organizers, and other people facing prosecution for political activity. CLDC has represented Valve Turners, Stop Cop City defendants, Standing Rock water protectors, Green Scare prisoners, Jane\'s Revenge defendants, and many others.',
            'category' => 'Legal Defense',
            'published' => true,
            'sort_order' => 0,
        ];

        $existing = Partner::query()->where('name', self::NAME)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing partner: '.self::NAME);
        } else {
            Partner::create(['name' => self::NAME] + $payload);
            $this->info('Created partner: '.self::NAME);
        }

        return self::SUCCESS;
    }
}
