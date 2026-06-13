<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Folds two additional, widely reported details into Micah Legnon's profile
 * bio: that he was also a former New Iberia police officer (not only a former
 * Marine), and that the threatening-ICE charge stems in part from a social
 * media post invoking the 1993 Waco siege. Updates the existing record's
 * description in place — the name is untouched, so the slug (and URL) are
 * unchanged. Idempotent: re-running re-sets the same text.
 */
class UpdateLegnonBio extends Command {
    protected $signature = 'prisoners:update-legnon-bio';
    protected $description = 'Add the former-police-officer and Waco-post details to Micah Legnon\'s bio';

    public function handle(): int {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'micah-legnon')->first();
        if (! $prisoner) {
            $this->error('Prisoner micah-legnon not found — run prisoners:add-legnon first.');

            return self::FAILURE;
        }

        $prisoner->description = "Micah James Legnon, of New Iberia, Louisiana, is a former U.S. Marine and former New Iberia police officer, and a self-described member of the Turtle Island Liberation Front (TILF) — the far-left, pro-Palestine, anti-capitalist, and anti-government network whose California faction was separately charged in the alleged \"Operation Midnight Sun\" New Year's Eve bombing plot. On December 13, 2025, he was arrested on a federal criminal complaint in the Western District of Louisiana charging him with threatening ICE officers; the charge stems in part from a social media post invoking the 1993 Waco siege and expressing violent intent toward federal immigration authorities.\n\nAccording to court documents, Legnon — who used the aliases \"Dark Witch\" and \"Kateri the Witch\" — participated in an online chat called the \"Order of the Black Lotus,\" where prosecutors say he discussed teaching other TILF members urban warfare and wrote threatening messages about ICE officers. The FBI said that on December 12, 2025 it observed him leaving his New Iberia home with an assault rifle and body armor in his vehicle in what agents described as an apparent attempt to carry out an attack; he was stopped and arrested by the Terrebonne Parish Sheriff's Office, and an assault rifle, a pistol, a gas canister, and body armor were recovered from his vehicle. A search of his residence turned up sniper- and SWAT-training manuals, assault rifles, and ammunition. He was taken into federal custody and is awaiting trial.";
        $prisoner->save();

        $this->info("Updated bio for {$prisoner->name} (slug: {$prisoner->slug}).");

        return self::SUCCESS;
    }
}
