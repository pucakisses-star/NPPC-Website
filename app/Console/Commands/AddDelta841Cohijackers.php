<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Sets Melvin McNair's photo and adds his three Delta 841 co-hijackers who were
 * missing from the database.
 *
 * On July 31, 1972, five Black Liberation Army members hijacked Delta Air Lines
 * Flight 841 (Detroit–Miami) with pistols hidden in hollowed-out Bibles,
 * collected a $1 million ransom, and forced the plane to Algeria (which seized
 * and returned the ransom). Melvin and Jean McNair are already in the database;
 * this adds George Brown, Joyce Tillerson, and George Wright. Four of the five
 * settled in France (convicted there in 1978, served short terms, and stayed);
 * George Wright fled on to Portugal, where he was found in 2011 and shielded
 * from extradition as a Portuguese citizen. All remain in exile. Idempotent.
 */
final class AddDelta841Cohijackers extends Command
{
    protected $signature = 'prisoners:add-delta841-cohijackers';

    protected $description = "Set Melvin McNair's photo and add the missing Delta 841 co-hijackers";

    private const MCNAIR_SOURCE = 'images/prisoners/melvin-mcnair.jpg';

    private const MCNAIR_PHOTO = 'prisoners/melvin-mcnair.jpg';

    public function handle(): int
    {
        // 1) Melvin McNair's photo (already in the DB; set only the photo).
        $source = public_path(self::MCNAIR_SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::MCNAIR_PHOTO, file_get_contents($source));
            $mcnair = Prisoner::withoutGlobalScopes()->where('slug', 'melvin-mcnair')->first()
                ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%McNair%')->first();
            if ($mcnair) {
                $mcnair->photo = self::MCNAIR_PHOTO;
                $mcnair->save();
                $this->info("Set photo on {$mcnair->name}.");
            } else {
                $this->warn('No Melvin McNair record found to set the photo on.');
            }
        } else {
            $this->warn('Melvin McNair source image not found: public/'.self::MCNAIR_SOURCE);
        }

        // 2) Add the three missing co-hijackers (all still in exile).
        $people = [
            ['name' => 'George Brown', 'first' => 'George', 'last' => 'Brown', 'gender' => 'Male', 'state' => null,
                'desc' => 'George Brown was a member of the Black Liberation Army and one of five who hijacked Delta Air Lines Flight 841 on July 31, 1972, diverting it to Algeria after collecting a $1 million ransom (which Algeria seized and returned). He settled in France, where he was arrested in 1976 and convicted of the hijacking in 1978, serving a short sentence. He remained in France and has not returned to the United States.',
                'charges' => 'Air piracy — the July 31, 1972 hijacking of Delta Air Lines Flight 841 for a $1 million ransom.', 'convicted' => 'Convicted in France (1978); served a short term'],

            ['name' => 'Joyce Tillerson', 'first' => 'Joyce', 'last' => 'Tillerson', 'aka' => 'Joyce Brown', 'gender' => 'Female', 'state' => null,
                'desc' => 'Joyce Tillerson (later known as Joyce Brown) was a member of the Black Liberation Army and one of the five who hijacked Delta Air Lines Flight 841 on July 31, 1972 — the group boarded with young children and pistols concealed in hollowed-out Bibles — and diverted it to Algeria for a $1 million ransom. She lived in exile in France, where she was arrested in 1976 and convicted in 1978, and she remained there.',
                'charges' => 'Air piracy — the July 31, 1972 hijacking of Delta Air Lines Flight 841 for a $1 million ransom.', 'convicted' => 'Convicted in France (1978)'],

            ['name' => 'George Wright', 'first' => 'George', 'last' => 'Wright', 'aka' => 'José Luís Jorge dos Santos', 'gender' => 'Male', 'state' => 'New Jersey',
                'desc' => 'George Wright was a member of the Black Liberation Army who — while a fugitive, having escaped a New Jersey prison where he was serving time for a 1962 robbery-murder — helped hijack Delta Air Lines Flight 841 on July 31, 1972 disguised as a priest, diverting it to Algeria for a $1 million ransom. He later lived underground in France and then Portugal under an assumed identity, becoming a Portuguese citizen. Arrested near Lisbon in 2011, he avoided extradition when Portugal refused to surrender him, and he remains in Portugal.',
                'charges' => 'Air piracy — the July 31, 1972 hijacking of Delta Air Lines Flight 841 (disguised as a priest); earlier escape from a New Jersey prison while serving time for a 1962 robbery-murder.', 'convicted' => 'Fugitive; found in Portugal in 2011, extradition refused'],
        ];

        foreach ($people as $d) {
            // Add-only: skip anyone already in the database (e.g. George Wright,
            // who is already recorded) so we never overwrite a curated record.
            $existing = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($d['name']))
                ->orWhere('name', $d['name'])
                ->first();

            if ($existing) {
                $this->line("Skipped (already in the database): {$existing->name}");

                continue;
            }

            $prisoner = new Prisoner([
                'name' => $d['name'], 'first_name' => $d['first'], 'last_name' => $d['last'],
                'aka' => $d['aka'] ?? null, 'gender' => $d['gender'], 'state' => $d['state'],
                'era' => '1970s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Liberation Army'],
                'in_custody' => false, 'released' => false, 'in_exile' => true, 'under_review' => false,
                'description' => $d['desc'],
            ]);
            $prisoner->save();
            $this->info('Created: '.$prisoner->name);

            $case = $prisoner->cases()->make([
                'charges' => $d['charges'],
                'convicted' => $d['convicted'],
            ]);
            $case->setPartialDate('in_exile_since', 1972, 7, 31); // the hijacking
            $case->save();
        }

        return self::SUCCESS;
    }
}
