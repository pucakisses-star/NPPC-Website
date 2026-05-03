<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class UpdateKeithMcHenryBio extends Command
{
    protected $signature = 'prisoners:update-keith-mchenry {--dry-run : Show what would change without writing}';
    protected $description = 'Fill out Keith McHenry\'s bio (description and unset biographical fields).';

    private const BIO = <<<'TXT'
Keith McHenry is an American activist and co-founder of Food Not Bombs, a global all-volunteer movement that recovers surplus food and shares free vegan and vegetarian meals in public spaces as a protest against war, poverty, and the destruction of the environment.

Born in Frankfurt, West Germany in 1957 while his father was stationed there with the U.S. Army, McHenry grew up moving between U.S. National Parks where his father later worked as a park ranger, including Yosemite, Grand Canyon, Big Bend, Shenandoah, and the Everglades. He began studying painting and sculpture at Boston University in 1975, where he also took a course in American history with Howard Zinn. While in Boston he became active with the Clamshell Alliance, traveling repeatedly to Seabrook, New Hampshire to protest nuclear power. In 1980 he and seven friends co-founded the first Food Not Bombs chapter in Cambridge, Massachusetts, distributing uncooked food to housing projects and shelters and serving vegetarian meals in Harvard Square and on Boston Common.

After moving to San Francisco in 1988, McHenry started a second Food Not Bombs group. On August 15, 1988 he was among nine volunteers arrested in Golden Gate Park for sharing food and literature without a permit. Over the following years he was arrested more than 100 times for serving free food in city parks and spent over 500 nights in jail. Under California's Three Strikes Law he faced a sentence of 25 years to life. In 1995, after a sustained international campaign by Amnesty International and the United Nations Human Rights Commission — which classified him as a prisoner of conscience — the charges were ultimately dropped.

McHenry also co-founded Homes Not Jails, a group that helps homeless people occupy abandoned buildings, and contributed to the founding of the Independent Media Center. In 2005 he coordinated Food Not Bombs' food, clothing, and supply relief for survivors of Hurricane Katrina. He has received the San Francisco Bay Guardian's 1999 Local Hero Award, the War Resisters League's Resister of the Year award in 1995, and the Justice Studies Association's 2012 Noam Chomsky Award.

Food Not Bombs has grown to hundreds of autonomous chapters across the United States, Canada, Europe, Latin America, Asia, Africa, and Australia.
TXT;

    private const FIELD_DEFAULTS = [
        'birthdate'   => '1957-01-01', // Year known; specific day not public
        'gender'      => 'Male',
        'state'       => 'California',
        'era'         => 'Modern',
        'ideologies'  => ['Anarchist', 'Anti-war', 'Pacifist'],
        'affiliation' => ['Food Not Bombs', 'Homes Not Jails'],
    ];

    public function handle(): int
    {
        $keith = Prisoner::where('name', 'like', '%Keith%McHenry%')
            ->orWhere('name', 'like', '%McHenry%Keith%')
            ->first();

        if (! $keith) {
            $this->error('No prisoner found whose name contains "Keith" and "McHenry".');
            $this->line('Run prisoner:add first if he is not yet in the database.');
            return self::FAILURE;
        }

        $this->info("Found: {$keith->name} (slug: {$keith->slug})");

        $changes = ['description' => self::BIO];

        foreach (self::FIELD_DEFAULTS as $field => $value) {
            $current = $keith->{$field};
            $isEmpty = $current === null || $current === '' || $current === [];
            if ($isEmpty) {
                $changes[$field] = $value;
            } else {
                $shown = is_array($current) ? json_encode($current) : $current;
                $this->line("  Keeping existing {$field}: {$shown}");
            }
        }

        $this->info('Will update:');
        foreach ($changes as $field => $value) {
            if ($field === 'description') {
                $this->line('  description: <bio, '.strlen($value).' chars>');
            } else {
                $shown = is_array($value) ? json_encode($value) : $value;
                $this->line("  {$field}: {$shown}");
            }
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
            return self::SUCCESS;
        }

        $oldLen = strlen((string) $keith->description);

        // Direct property assignment + save (avoids any fill() filtering surprises).
        foreach ($changes as $field => $value) {
            $keith->{$field} = $value;
        }
        $saved = $keith->save();

        // Read back from the DB through a brand-new query to verify the write
        // actually landed (not just that save() returned true).
        $reloaded = Prisoner::query()->where('id', $keith->id)->first();
        $newLen = strlen((string) $reloaded->description);
        $matches = $reloaded->description === self::BIO;

        $this->info("save() returned: ".($saved ? 'true' : 'false'));
        $this->info("description length before: {$oldLen} -> after re-fetch: {$newLen}");
        $this->info("DB description matches expected bio: ".($matches ? 'YES' : 'NO'));

        if (! $matches) {
            $this->error('Description in DB does not match what we tried to write.');
            $this->line('First 200 chars actually in DB:');
            $this->line(substr((string) $reloaded->description, 0, 200));
            return self::FAILURE;
        }

        $this->info("Done. Updated {$keith->name}.");

        return self::SUCCESS;
    }
}
