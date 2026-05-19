<?php

namespace App\Console\Commands;

use App\Models\PodcastEpisode;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Register three CC-licensed Sean Swain episodes from The Final Straw
 * Radio (TFSR) as PodcastEpisode rows. Audio files are self-hosted
 * under /audio/podcast/sean-swain/, sourced from archive.org.
 *
 * All three episodes are explicitly Creative Commons licensed by TFSR
 * (CC BY-SA 4.0 / CC BY-NC-SA 4.0). Attribution is encoded in the
 * description field per the share-alike requirement.
 *
 * Idempotent — re-runs update by title.
 */
final class AddSeanSwainTfsrEpisodes extends Command {
    protected $signature = 'podcast:add-sean-swain-tfsr';
    protected $description = 'Register 3 CC-licensed Sean Swain episodes from The Final Straw Radio';

    public function handle(): int {
        $prisoner = Prisoner::query()->where('slug', 'like', '%sean-swain%')->first();
        $prisonerId = $prisoner?->id;

        if ($prisoner) {
            $this->info("Linking episodes to prisoner: {$prisoner->name} ({$prisoner->slug}).");
        } else {
            $this->warn('No prisoner record found for Sean Swain — episodes will be created without a prisoner link.');
        }

        $episodes = [
            [
                'title' => 'Sean Swain\'s 10-Year TFSR Anniversary',
                'description' => 'Comprehensive anniversary episode celebrating ten years of Sean Swain segments on The Final Straw Radio (2014–2024). Anarchist political prisoner Sean Swain has been in Ohio state custody since 1991; TFSR has featured his radio commentaries, court updates, hunger-strike coverage, and political analysis weekly. This anthology episode draws on highlights from the run.'."\n\n".'Source: archive.org/details/tfsrpodcast-20240119-SeanSwainiversary'."\n".'Producer: The Final Straw Radio (thefinalstrawradio.noblogs.org)'."\n".'License: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)',
                'file' => 'tfsr-2024-01-17-sean-swain-anniversary.mp3',
                'date' => '2024-01-17',
            ],
            [
                'title' => 'Hunger Strike at Red Onion + Military Refuser in Israel',
                'description' => 'TFSR coverage of the Red Onion State Prison hunger strike in Virginia, alongside an interview with an Israeli military refuser. Episode includes a Sean Swain segment.'."\n\n".'Source: archive.org/details/tfsr20240121-RedOnion-IsraeliResister'."\n".'Producer: The Final Straw Radio'."\n".'License: Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)',
                'file' => 'tfsr-2024-01-21-red-onion-israeli-resister.mp3',
                'date' => '2024-01-21',
            ],
            [
                'title' => 'Free Alex Stokes + Russian Anti-War Saboteur Ruslan Siddiqui',
                'description' => 'TFSR episode featuring updates on Alex Stokes (J20 defendant prosecuted at the 2017 inauguration) and Ruslan Siddiqui (Russian anti-war saboteur). Sean Swain segment included.'."\n\n".'Source: archive.org/details/tfsr-20240204-alex-stokes-rusiln-siddiqui'."\n".'Producer: The Final Straw Radio'."\n".'License: Creative Commons Attribution-ShareAlike 4.0 International (CC BY-SA 4.0)',
                'file' => 'tfsr-2024-02-04-alex-stokes-ruslan-siddiqui.mp3',
                'date' => '2024-02-04',
            ],
        ];

        $added = 0; $updated = 0;
        foreach ($episodes as $e) {
            $payload = [
                'show_name' => 'The Final Straw Radio (TFSR)',
                'description' => $e['description'],
                'audio_url' => '/audio/podcast/sean-swain/'.$e['file'],
                'duration' => null,
                'prisoner_id' => $prisonerId,
                'published' => true,
                'sort_order' => 0,
            ];

            $existing = PodcastEpisode::query()->where('title', $e['title'])->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                PodcastEpisode::create(['title' => $e['title']] + $payload);
                $added++;
            }
        }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
