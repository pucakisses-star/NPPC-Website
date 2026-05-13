<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Elias Rodriguez — Chicago anti-Zionist defendant charged in
 * the May 21, 2025 shooting deaths of two Israeli Embassy staff
 * outside the Capital Jewish Museum in Washington, D.C. — and
 * downloads the user-supplied LinkedIn profile photo into the
 * prisoner photo column.
 *
 * Federal pretrial detention since arrest; 13-count superseding
 * indictment as of February 2026 (first-degree murder, murder of
 * foreign officials, hate-crime + terrorism counts, firearms). DOJ
 * has signaled intent to seek the federal death penalty.
 */
final class AddEliasRodriguez extends Command {
    protected $signature = 'archive:add-elias-rodriguez';
    protected $description = 'Add the Elias Rodriguez prisoner record + LinkedIn photo';

    public function handle(): int {
        $payload = [
            'name' => 'Elias Rodriguez',
            'first_name' => 'Elias',
            'last_name' => 'Rodriguez',
            'description' => "Chicago resident charged in the May 21, 2025 shooting deaths of Israeli Embassy staff Yaron Lischinsky and Sarah Lynn Milgrim outside the Capital Jewish Museum in Washington, D.C. Rodriguez stayed at the scene after the shooting, dropped his weapon, identified himself to officers, and shouted 'Free, free Palestine.' Prosecutors attribute to him a 900-word document titled 'Escalate For Gaza, Bring The War Home,' arguing that armed action against U.S. support of Israel during the war on Gaza was a moral necessity, and citing the 2014 Gaza war as the origin of his political awakening. A 2018 English graduate of the University of Illinois at Chicago, he was briefly active with the Party for Socialism and Liberation's Chicago chapter through 2017 (the PSL has since publicly disavowed any current affiliation) and worked as an oral-history researcher at The HistoryMakers before taking an administrative role at the American Osteopathic Information Association. Held without bond in federal custody in Washington, D.C. since the arrest. Faces a 13-count federal superseding indictment (Feb 2026) including two counts of first-degree murder, murder of foreign officials, two hate-crime counts resulting in death, causing death with a firearm, discharge of a firearm during a crime of violence, and four counts of acts of terrorism while armed. The Department of Justice has signaled its intent to seek the federal death penalty.",
            'state' => 'Illinois',
            'race' => 'Latino',
            'gender' => 'Male',
            'ideologies' => ['Anti-Zionism', 'Pro-Palestine solidarity', 'Anti-imperialism'],
            'affiliation' => ['Party for Socialism and Liberation (former, through 2017)'],
            'era' => '2020s',
            'in_custody' => true,
            'released' => false,
            'cases' => [[
                'institution_name' => 'Federal pretrial detention (Washington, D.C.)',
                'institution_city' => 'Washington',
                'institution_state' => 'District of Columbia',
                'charges' => '13-count federal superseding indictment (Feb 2026): two counts first-degree murder; murder of foreign officials; two counts hate crime resulting in death; causing death through use of a firearm; discharge of a firearm during a crime of violence; four counts of acts of terrorism while armed; additional firearms counts. Federal aggravating factor of substantial planning and premeditation.',
                'arrest_date' => '2025-05-21',
                'incarceration_date' => '2025-05-21',
                'convicted' => 'Pretrial — pleaded not guilty',
                'judge' => 'Randolph D. Moss',
                'sentence' => 'Pending — death-eligible; DOJ has stated intent to seek the death penalty',
            ]],
        ];

        $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
        if ($exit !== self::SUCCESS) {
            $this->info('Prisoner already exists or could not be added — continuing to photo.');
        } else {
            $this->info('ADD: Elias Rodriguez');
        }

        $prisoner = Prisoner::where('slug', 'elias-rodriguez')->first();
        if (! $prisoner) {
            $this->error('Could not locate prisoner record for slug=elias-rodriguez. Aborting photo step.');

            return self::FAILURE;
        }

        if (! empty($prisoner->photo) && Storage::disk('public')->exists(ltrim($prisoner->photo, '/'))) {
            $this->info('Photo already present at '.$prisoner->photo.' — skipping download.');

            return self::SUCCESS;
        }

        $url = 'https://media.licdn.com/dms/image/v2/D5603AQHeSniaAwRaEA/profile-displayphoto-shrink_200_200/profile-displayphoto-shrink_200_200/0/1727459963536?e=2147483647&v=beta&t=xg9UV9rRmHFp3jBkguslAV9wQXnCgjpyAX39ifpQr6w';
        $disk = Storage::disk('public');
        $disk->makeDirectory('prisoners');
        $relative = 'prisoners/elias-rodriguez.jpg';
        $localPath = storage_path('app/public/'.$relative);
        $tmp = $localPath.'.partial';

        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (NPPC-Archive/1.0; +https://nationalpoliticalprisonercoalition.org)',
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
                'Referer' => 'https://www.linkedin.com/',
            ])
                ->withOptions(['sink' => $tmp])
                ->timeout(60)
                ->get($url);
            if (! $resp->successful()) {
                @unlink($tmp);
                $this->error("Photo HTTP {$resp->status()} — leaving photo column blank.");

                return self::SUCCESS;
            }
            $size = is_file($tmp) ? filesize($tmp) : 0;
            if ($size < 500) {
                @unlink($tmp);
                $this->error('Photo response suspiciously small ('.$size.' bytes) — leaving photo column blank.');

                return self::SUCCESS;
            }
            rename($tmp, $localPath);
            $prisoner->photo = $relative;
            $prisoner->save();
            $this->info('Saved photo: '.$relative.' ('.number_format($size / 1024, 1).' KB)');
        } catch (\Throwable $e) {
            @unlink($tmp);
            $this->error('Photo error: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
