<?php

namespace App\Console\Commands;

use App\Models\Petition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Creates the Leonard Peltier clemency petition page, timestamped to
 * the September 12, 2024 national rally in Washington, DC — Peltier's
 * 80th birthday — when coordinated mobilizations at the White House
 * and in dozens of cities pushed President Biden to grant clemency
 * before leaving office. Biden commuted Peltier's sentence to home
 * confinement four months later, on January 20, 2025.
 *
 * Pulls Peltier's Wikipedia portrait if not already present.
 *
 * Idempotent — keyed by slug.
 */
final class AddPeltierPetition extends Command {
    protected $signature = 'archive:add-peltier-petition';
    protected $description = 'Create the Leonard Peltier clemency petition (dated to 2024 DC national rally)';

    public function handle(): int {
        $slug = 'free-leonard-peltier';

        // Pull Wikipedia photo if not already stored.
        $imagePath = 'petitions/leonard-peltier.jpg';
        if (! Storage::disk('public')->exists($imagePath)) {
            try {
                $url = 'https://upload.wikimedia.org/wikipedia/commons/thumb/5/54/Leonard_Peltier_Wet_Plate_Collodion_Photograph_by_Shane_Balkowitsch.jpg/1280px-Leonard_Peltier_Wet_Plate_Collodion_Photograph_by_Shane_Balkowitsch.jpg';
                $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])->timeout(45)->get($url);
                if ($resp->successful() && strlen($resp->body()) > 5000) {
                    Storage::disk('public')->put($imagePath, $resp->body());
                    $this->info('Saved petition image to '.$imagePath);
                } else {
                    $this->warn('Image fetch returned HTTP '.$resp->status());
                    $imagePath = null;
                }
            } catch (\Throwable $e) {
                $this->warn('Image fetch failed: '.$e->getMessage());
                $imagePath = null;
            }
        }

        $body = <<<'HTML'
<p><strong>Leonard Peltier — Anishinaabe and Dakota American Indian Movement (AIM) member — spent 49 years in federal prison for the 1975 deaths of two FBI agents at Jincala Creek on the Pine Ridge Lakota reservation: a prosecution built on coerced witnesses, fabricated ballistics, and admitted government misconduct that has been condemned by Amnesty International, the National Congress of American Indians, the European Parliament, Pope Francis, the Dalai Lama, Coretta Scott King, and three U.S. Attorneys who oversaw his case.</strong></p>

<p>On <strong>September 12, 2024 — Leonard Peltier's 80th birthday</strong> — Indigenous nations, allies, and human rights organizations from across the continent converged on Washington, DC for a national rally at the White House demanding that President Biden grant clemency before leaving office. Coordinated rallies were held in dozens of cities the same day. The NDN Collective–led <em>Walk to Justice</em> — a 1,000-mile walk from Minneapolis to DC two years earlier — had laid the groundwork.</p>

<p>The pressure worked: <strong>on January 20, 2025, President Biden commuted Leonard Peltier's federal sentence to home confinement</strong> at the Turtle Mountain Indian Reservation in Belcourt, North Dakota — bringing him home after nearly five decades inside.</p>

<p>The fight isn't over. Peltier remains under federal supervision and has never been pardoned or had his conviction vacated. We continue to call for:</p>

<ul>
    <li>A <strong>full presidential pardon</strong> from the sitting president, vacating the conviction obtained through documented government misconduct.</li>
    <li>Release from home-confinement supervision so Leonard can travel for medical care, ceremony, and family.</li>
    <li>A formal federal acknowledgment of the FBI's COINTELPRO-era war on the American Indian Movement and the prosecutorial misconduct that sustained Peltier's case.</li>
</ul>

<p>Add your name. Demand a pardon. Honor the fight that brought him home.</p>
HTML;

        $petition = Petition::firstOrNew(['slug' => $slug]);
        $petition->title = 'Free Leonard Peltier — Pardon Now';
        $petition->body = $body;
        $petition->recipients = 'The President of the United States; the U.S. Attorney General; the U.S. Pardon Attorney';
        $petition->suggested_subject = 'Grant Leonard Peltier a full presidential pardon';
        $petition->suggested_message = 'I am writing to urge you to grant Leonard Peltier a full presidential pardon. President Biden\'s January 20, 2025 commutation brought him home from federal prison after nearly 50 years, but Mr. Peltier — now 80 — remains under federal supervision for a conviction obtained through documented government misconduct. Amnesty International, the National Congress of American Indians, the European Parliament, Coretta Scott King, Pope Francis, the Dalai Lama, and three former U.S. Attorneys who handled his case have all called for his release. A pardon would acknowledge the wrong done and let an Indigenous elder live his remaining years in full dignity. Please act.';
        $petition->signature_goal = 50000;
        if ($imagePath) {
            $petition->image = $imagePath;
        }
        $petition->published = true;

        $isNew = ! $petition->exists;
        $petition->save();

        // Backdate created_at to the 2024 DC national rally on Peltier's 80th birthday.
        $rallyDate = '2024-09-12 12:00:00';
        if ($isNew) {
            $petition->created_at = $rallyDate;
            $petition->updated_at = $rallyDate;
            $petition->saveQuietly();
            $this->info('Created and backdated to '.$rallyDate);
        } else {
            $this->info('Updated existing petition.');
        }

        $this->info('Petition slug: /petition/'.$slug);
        return self::SUCCESS;
    }
}
