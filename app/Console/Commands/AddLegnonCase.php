<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Adds Micah James Legnon — a New Iberia, Louisiana former Marine associated
 * with the Turtle Island Liberation Front (TILF) — arrested Dec. 13, 2025 and
 * charged in the Western District of Louisiana with threatening ICE officers.
 * Adds both a dashboard arrest marker and a full prisoner profile (he is
 * awaiting trial after arrest, so awaiting_trial/in_custody are set and
 * released is false). Idempotent: the dashboard link uses updateOrCreate and
 * the prisoner is deduped by name.
 */
class AddLegnonCase extends Command {
    protected $signature = 'prisoners:add-legnon';
    protected $description = 'Add Micah Legnon (TILF, threatening ICE, W.D. La.) — dashboard marker + profile';

    public function handle(): int {
        // 1) Dashboard arrest marker.
        $link = DashboardLink::updateOrCreate(
            ['url' => 'https://www.justice.gov/opa/pr/member-anti-capitalist-and-anti-government-group-arrested-and-charged-threatening-ice'],
            [
                'title'          => 'Member of Anti-Capitalist and Anti-Government Group Arrested and Charged with Threatening ICE Officers',
                'source'         => 'U.S. Department of Justice',
                'category'       => 'arrest',
                'published_at'   => Carbon::parse('2025-12-19'),
                'location_label' => 'New Iberia, LA',
                'lat'            => 30.0035,
                'lng'            => -91.8187,
            ],
        );
        $this->info(($link->wasRecentlyCreated ? 'Added' : 'Updated').' dashboard arrest marker.');

        // 2) Prisoner profile.
        $name = 'Micah Legnon';
        if (Prisoner::withoutGlobalScopes()->where('name', $name)->exists()) {
            $this->warn("{$name} already exists — skipping profile.");

            return self::SUCCESS;
        }

        $wdla = Institution::firstOrCreate(
            ['name' => 'U.S. District Court, Western District of Louisiana (Lafayette)'],
            ['city' => 'Lafayette', 'state' => 'Louisiana']
        );

        DB::transaction(function () use ($name, $wdla) {
            $prisoner = Prisoner::create([
                'name'           => $name,
                'first_name'     => 'Micah',
                'middle_name'    => 'James',
                'last_name'      => 'Legnon',
                'aka'            => 'Dark Witch; Kateri the Witch',
                'age'            => 28,
                'gender'         => 'Male',
                'state'          => 'Louisiana',
                'era'            => '2020s',
                'ideologies'     => ['Anti-capitalist', 'Anti-government', 'Pro-Palestine'],
                'affiliation'    => ['Turtle Island Liberation Front'],
                'in_custody'     => true,
                'released'       => false,
                'awaiting_trial' => true,
                'description'    => "Micah James Legnon, of New Iberia, Louisiana, is a former U.S. Marine and a self-described member of the Turtle Island Liberation Front (TILF) — the far-left, pro-Palestine, anti-capitalist, and anti-government network whose California faction was separately charged in the alleged \"Operation Midnight Sun\" New Year's Eve bombing plot. On December 13, 2025, he was arrested on a federal criminal complaint in the Western District of Louisiana charging him with threatening ICE officers.\n\nAccording to court documents, Legnon — who used the aliases \"Dark Witch\" and \"Kateri the Witch\" — participated in an online chat called the \"Order of the Black Lotus,\" where prosecutors say he discussed teaching other TILF members urban warfare and wrote threatening messages about ICE officers. The FBI said that on December 12, 2025 it observed him leaving his New Iberia home with an assault rifle and body armor in his vehicle in what agents described as an apparent attempt to carry out an attack; he was stopped and arrested by the Terrebonne Parish Sheriff's Office, and an assault rifle, a pistol, a gas canister, and body armor were recovered from his vehicle. A search of his residence turned up sniper- and SWAT-training manuals, assault rifles, and ammunition. He was taken into federal custody and is awaiting trial.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $wdla->id,
                'charges'            => 'Threatening ICE (Immigration and Customs Enforcement) officers — federal criminal complaint, Western District of Louisiana',
                'arrest_date'        => '2025-12-13',
                'incarceration_date' => '2025-12-13',
                'convicted'          => 'No — awaiting trial',
                'plead'              => 'Arrested December 13, 2025 on a federal criminal complaint; taken into federal custody pending further proceedings in the U.S. District Court for the Western District of Louisiana.',
            ]);

            $this->info("Added {$prisoner->name} (slug: {$prisoner->slug}).");
        });

        return self::SUCCESS;
    }
}
