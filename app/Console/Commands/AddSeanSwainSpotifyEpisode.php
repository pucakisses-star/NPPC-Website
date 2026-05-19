<?php

namespace App\Console\Commands;

use App\Models\PodcastEpisode;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Register the "Political Prisoner Sean Swain Speaks" episode from
 * the Spotify show "Political Prisoners: A Deep Dive" via embed iframe
 * (no audio redistribution, no license issue).
 *
 * Idempotent — matches by title.
 */
final class AddSeanSwainSpotifyEpisode extends Command {
    protected $signature = 'podcast:add-sean-swain-spotify';
    protected $description = 'Register Sean Swain Spotify episode as an embed';

    public function handle(): int {
        $prisoner = Prisoner::query()->where('slug', 'like', '%sean-swain%')->first();

        $title = 'Political Prisoner Sean Swain Speaks';
        $embed = '<iframe style="border-radius:12px" src="https://open.spotify.com/embed/episode/0r408Nb8RFErQSL55ARdLA?utm_source=generator" width="100%" height="232" frameborder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';

        $payload = [
            'show_name' => 'Political Prisoners: A Deep Dive',
            'description' => 'Sean Swain and the host discuss the political nature of prosecutors\' roles and how politics influenced his case. Published Aug 17, 2023.',
            'embed_code' => $embed,
            'audio_url' => null,
            'prisoner_id' => $prisoner?->id,
            'published' => true,
        ];

        $existing = PodcastEpisode::query()->where('title', $title)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing episode.');
        } else {
            PodcastEpisode::create(['title' => $title] + $payload);
            $this->info('Created episode.');
        }

        return self::SUCCESS;
    }
}
