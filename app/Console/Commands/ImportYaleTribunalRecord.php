<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportYaleTribunalRecord extends Command {
    protected $signature = 'archive:import-yale-tribunal';
    protected $description = 'Import the 1991 Yale Journal of Law & Liberation reprint of the International Tribunal verdict (idempotent)';

    public function handle(): int {
        $slug = 'yale-jl-liberation-1991-international-tribunal-verdict';

        $payload = [
            'title' => 'Verdict of the International Tribunal on Political Prisoners and Prisoners of War in the United States',
            'description' => 'Verdict of the Special International Tribunal on the Human Rights Violations of Political Prisoners and Prisoners of War in the United States, held at Hunter College in New York City from December 7–10, 1990, attended by over 1200 people from 10 countries. The Tribunal examined the situation of the national liberation movements of the New Afrikan, Native American, and Puerto Rican sectors. Convened by a coalition of 88 organizations, it found the U.S. government had denied the right to self-determination, conducted illegal programs of repression including COINTELPRO, and refused to recognize over 100 political prisoners and prisoners of war held in its prisons. Petitioners included Sundiata Acoli, the MOVE 9, the FALN, Mumia Abu-Jamal, Leonard Peltier, Oscar López-Rivera, David Gilbert, Marilyn Buck, Mutulu Shakur, Jaan Laaman, Thomas Manning, and dozens of others. Reprinted from La Patria Radical (January 1991) in the Yale Journal of Law and Liberation, Vol. 2, p. 47 (1991).',
            'record_type' => 'document',
            'source_format' => 'article',
            'file' => '/pdfs/archive/yale-jl-liberation-1991-tribunal-verdict.pdf',
            'thumbnail' => '/images/archive/general/yale-jl-tribunal-1991-cover.jpg',
            'year' => 1991,
            'date' => '1991-01-01',
            'publisher' => 'Yale Journal of Law and Liberation',
            'authors' => 'Frank Badohu, Jawad Boulus, Norman Paech, José R. Rendón, Celina Romany, Toshi Yuki Tanaka, George Wald, Lord Anthony Gifford; coordinated by Luis Nieves Falcón',
            'collection' => 'International Tribunals',
            'volume' => 'Vol. 2, p. 47',
            'subjects' => ['Political Prisoners', 'International Tribunal', 'Human Rights', 'COINTELPRO', 'National Liberation', 'Self-Determination'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => 0,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();

        if ($existing) {
            $existing->update($payload);
            $this->info("updated: {$slug}");
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info("created: {$slug}");
        }

        return self::SUCCESS;
    }
}
