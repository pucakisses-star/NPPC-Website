<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class AddEdStover extends Command {
    protected $signature = 'archive:add-ed-stover';
    protected $description = 'Add Robert "Ed" Stover (IWW, Bay Area 1970) — sourced from the 1972 Chicago ABC Bulletin';

    public function handle(): int {
        $name = 'Robert P. Stover';

        if (Prisoner::where('name', $name)->exists()) {
            $this->warn("Prisoner '{$name}' already exists; skipping.");

            return self::SUCCESS;
        }

        $data = [
            'name' => $name,
            'first_name' => 'Robert',
            'middle_name' => 'P.',
            'last_name' => 'Stover',
            'aka' => 'Ed Stover',
            'description' => 'Robert P. Stover, better known to friends and fellow workers as "Ed" Stover, was an Industrial Workers of the World (IWW) member and Bay Area anarchist organizer arrested in 1970 along with co-defendant Mike Lamm in a wave of anti-leftist police raids targeting Bay Area anarchists. The arrests featured large spreads in the local press with photographs of literature and weapons seized from anarchist homes and a heavily publicized high-speed police chase. Stover and Lamm faced a battery of charges including arson, robbery, receiving stolen weapons, and attempted murder. At the time of his arrest Stover was active in the anti-war and anti-draft movements on both the East and West coasts and was working to build organizing contacts between union militants and the student movement. Stover spent sixteen months in pretrial detention, unable to meet what supporters described as excessively high bail. Mike Lamm raised his own bail but, as the Chicago Anarchist Black Cross Bulletin reported in 1972, "depression over constant harassment of state and federal authorities drove him to jump into San Francisco Bay, where he died." A national Stover-Lamm Defence Fund was organized, and the General Defence Committee of the IWW raised additional money, but Stover was ultimately convicted and sentenced to a very lengthy prison term. He was held at San Quentin State Prison, initially incommunicado and moved frequently. While incarcerated he wrote essays published in the IWW\'s "Industrial Worker," studied labor law and National Labor Relations Board procedures, prepared his own appellate briefs, and worked on a book titled "Anarchist in Exile."',
            'gender' => 'Male',
            'race' => 'White',
            'state' => 'California',
            'ideologies' => ['Anarchism', 'Anti-Militarism', 'Anti-War'],
            'affiliation' => ['Industrial Workers of the World (IWW)'],
            'era' => 'Anti-War',
            'inmate_number' => 'B-341',
            'cases' => [[
                'institution_name' => 'San Quentin State Prison',
                'institution_city' => 'Tamal',
                'institution_state' => 'California',
                'charges' => 'Arson, robbery, receiving stolen weapons, attempted murder',
                'arrest_date' => '1970-01-01',
                'convicted' => 'Yes — convicted after 16 months of pretrial detention',
                'sentence' => 'Lengthy state prison term (specific length not documented in the 1972 ABC bulletin)',
            ]],
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $exit = Artisan::call('prisoner:add', ['json' => $json]);
        $this->line(Artisan::output());

        return $exit;
    }
}
