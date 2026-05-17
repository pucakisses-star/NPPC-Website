<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register a focused batch of foundational U.S. political-prisoner
 * texts surfaced from the Internet Archive in May 2026 that the
 * NPPC archive did not previously hold. Seven PDFs + one external
 * IA reference for the bulk TWWA COINTELPRO FOIA collection (too
 * many individual files to mirror as separate records).
 *
 * All seven PDFs are checked into public/pdfs/books/ and rooted at
 * absolute URL paths so they serve directly from /public/ without
 * round-tripping through Storage::url().
 *
 * Idempotent — re-runs update by slug.
 */
final class AddIaPpBooksMay2026 extends Command {
    protected $signature = 'archive:add-ia-pp-books-may-2026';
    protected $description = 'Add 8 Internet Archive political-prisoner records (Levasseur, COINTELPRO Papers, Let Freedom Ring, War at Home, In the Spirit of Crazy Horse, Profiles of Provocateurs, Ervin, TWWA FOIA set)';

    public function handle(): int {
        $records = [
            [
                'slug' => 'letters-from-exile-levasseur-marion-prison',
                'title' => "Letters From Exile — Raymond Luc Levasseur (Marion Prison writings)",
                'description' => "Collected Marion-prison-era writings of Raymond Luc Levasseur, United Freedom Front / Sam Melville–Jonathan Jackson Unit prisoner held in U.S. federal custody from 1984 to 2004 (and earlier in Tennessee state custody 1969–1971). Includes 'The Uprising' and Levasseur's letters from Marion, where he was held in the federal control-unit prison's special housing for years. Levasseur was one of the seven 'Ohio 7' defendants — anti-imperialist working-class revolutionaries charged with bombings of U.S. military and corporate targets in solidarity with South Africa and Central America — and is among the most articulate prison-letter writers of the late-20th-century U.S. political-prisoner generation. Mirrored from the Internet Archive item `ray-luc-levasseur`.",
                'record_type' => 'book',
                'source_format' => 'zine',
                'file' => '/pdfs/books/letters-from-exile-levasseur-marion-prison.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Raymond Luc Levasseur',
                'publisher' => 'self-published / movement zine',
                'subjects' => ['United Freedom Front', 'Ohio 7', 'Sam Melville–Jonathan Jackson Unit', 'Marion', 'Control Units', 'Political Prisoners', 'Anti-Imperialism'],
            ],
            [
                'slug' => 'cointelpro-papers-churchill-vander-wall-1990',
                'title' => "The COINTELPRO Papers: Documents from the FBI's Secret Wars Against Dissent in the United States",
                'description' => "Companion volume to Agents of Repression (1988). Ward Churchill and Jim Vander Wall's 1990 South End Press compilation reproduces hundreds of pages of FBI COINTELPRO documents — released under FOIA — covering the Bureau's operations against the Communist Party, the Socialist Workers Party, the Puerto Rican independence movement, the New Left and the anti-war movement, the Black liberation movement (the Panthers, US Organization, Republic of New Africa, Black nationalists generally), and the American Indian Movement. The single most cited published COINTELPRO primary-source compendium and a core reference for political-prisoner support and movement history.",
                'record_type' => 'book',
                'source_format' => 'monograph',
                'file' => '/pdfs/books/cointelpro-papers-churchill-vander-wall-1990.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Ward Churchill; Jim Vander Wall',
                'publisher' => 'South End Press',
                'year' => 1990,
                'date' => '1990-01-01',
                'subjects' => ['COINTELPRO', 'FBI', 'Black Panther Party', 'American Indian Movement', 'AIM', 'Puerto Rican Independence', 'Socialist Workers Party', 'Communist Party USA', 'New Left', 'State Repression'],
            ],
            [
                'slug' => 'let-freedom-ring-meyer-2008',
                'title' => "Let Freedom Ring: A Collection of Documents from the Movements to Free U.S. Political Prisoners",
                'description' => "900+ page omnibus edited by Matt Meyer and published by PM Press / Kersplebedeb in 2008, with a foreword by Adolfo Pérez Esquivel and an afterword by Ashanti Alston. The single most comprehensive published anthology of statements, letters, prison writings, support-committee documents, and movement-historical essays on U.S. political prisoners — covering the Black liberation movement (Panthers, BLA, MOVE), Native American Movement (Leonard Peltier and the Pine Ridge cases), Puerto Rican independentistas (FALN, Macheteros), white anti-imperialists (Resistance Conspiracy, Ohio 7 / UFF, Plowshares), the Earth and Animal Liberation prisoners of the Green Scare, and beyond. Core reference for prison-support organizing.",
                'record_type' => 'book',
                'source_format' => 'anthology',
                'file' => '/pdfs/books/let-freedom-ring-meyer-2008.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Matt Meyer (ed.)',
                'publisher' => 'PM Press / Kersplebedeb',
                'year' => 2008,
                'date' => '2008-01-01',
                'subjects' => ['Political Prisoners', 'Black Liberation Army', 'Black Panther Party', 'MOVE', 'Leonard Peltier', 'AIM', 'FALN', 'Macheteros', 'Resistance Conspiracy', 'Ohio 7', 'Plowshares', 'Green Scare', 'Jericho Movement'],
            ],
            [
                'slug' => 'war-at-home-brian-glick-1989',
                'title' => "War at Home: Covert Action Against U.S. Activists and What We Can Do About It",
                'description' => "Brian Glick's 1989 South End Press handbook on FBI/COINTELPRO-style covert action against U.S. social movements, written for movement participants. Combines a historical synthesis of COINTELPRO and its successors with concrete advice for activists on recognizing infiltrators and provocateurs, defending against grand juries, protecting communications, supporting political prisoners, and surviving state-repression cycles. A standard movement security-culture reference cited by every generation of U.S. activist trainers since publication.",
                'record_type' => 'book',
                'source_format' => 'handbook',
                'file' => '/pdfs/books/war-at-home-brian-glick-1989.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Brian Glick',
                'publisher' => 'South End Press',
                'year' => 1989,
                'date' => '1989-01-01',
                'subjects' => ['COINTELPRO', 'FBI', 'State Repression', 'Security Culture', 'Grand Juries', 'Provocateurs', 'Movement Defense'],
            ],
            [
                'slug' => 'in-the-spirit-of-crazy-horse-matthiessen-1983',
                'title' => "In the Spirit of Crazy Horse",
                'description' => "Peter Matthiessen's landmark 1983 (Viking) book-length investigation of the FBI's war against the American Indian Movement on the Pine Ridge Lakota reservation in the 1970s, the 1975 Oglala firefight that left FBI agents Jack Coler and Ronald Williams and AIM member Joseph Stuntz dead, and the federal frame-up of Leonard Peltier. Held off bookstore shelves for years by litigation from FBI agents and former South Dakota governor William Janklow — both lawsuits ultimately dismissed — the book remains the definitive long-form journalistic account of the AIM-era state-repression campaign and the case against Peltier.",
                'record_type' => 'book',
                'source_format' => 'monograph',
                'file' => '/pdfs/books/in-the-spirit-of-crazy-horse-matthiessen-1983.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Peter Matthiessen',
                'publisher' => 'Viking',
                'year' => 1983,
                'date' => '1983-01-01',
                'subjects' => ['American Indian Movement', 'AIM', 'Leonard Peltier', 'Pine Ridge', 'Oglala', 'FBI', 'COINTELPRO', 'Native American Political Prisoners'],
            ],
            [
                'slug' => 'profiles-of-provocateurs-williams-2011',
                'title' => "Profiles of Provocateurs",
                'description' => "Kristian Williams's 2011 PM Press pamphlet documenting law-enforcement and informant infiltration of U.S. social movements — case studies of named provocateurs (Brandon Darby, Anna Davies, Andrew Darst, etc.) who infiltrated antiwar, Earth/Animal Liberation, and anarchist organizing, the techniques they used, and the prosecutions and entrapment cases their work produced. Written for and circulated heavily by Green Scare and anti-summit defense networks; a companion / update to Brian Glick's War at Home for the post-9/11 organizing generation.",
                'record_type' => 'book',
                'source_format' => 'pamphlet',
                'file' => '/pdfs/books/profiles-of-provocateurs-williams-2011.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Kristian Williams',
                'publisher' => 'PM Press',
                'year' => 2011,
                'date' => '2011-01-01',
                'subjects' => ['Provocateurs', 'Informants', 'FBI', 'Entrapment', 'Green Scare', 'Anarchist Movement', 'State Repression', 'Security Culture'],
            ],
            [
                'slug' => 'anarchism-and-the-black-revolution-ervin',
                'title' => "Anarchism and the Black Revolution",
                'description' => "Lorenzo Kom'boa Ervin's foundational text linking anarchism with Black liberation, written and revised across the late 1970s and 1980s while Ervin was a Black Panther Party-aligned political prisoner in U.S. federal custody following his hijacking conviction. Originally circulated as a movement pamphlet and republished in expanded book form (most recently by Pluto Press in 2021), it is the single most cited entry point for the Black Anarchist tradition in the United States and a core text for the Jericho Movement, Anarchist Black Cross, and Black Autonomy support tradition.",
                'record_type' => 'book',
                'source_format' => 'pamphlet',
                'file' => '/pdfs/books/anarchism-and-the-black-revolution-ervin.pdf',
                'collection' => 'Movement Reference',
                'authors' => "Lorenzo Kom'boa Ervin",
                'publisher' => 'movement pamphlet / Pluto Press',
                'subjects' => ['Black Anarchism', 'Black Liberation', 'Anarchism', 'Black Panther Party', 'Political Prisoners', 'Jericho Movement'],
            ],
            [
                'slug' => 'fbi-cointelpro-twwa-foia-set',
                'title' => "FBI COINTELPRO surveillance of the Third World Women's Alliance (TWWA) — FOIA file set",
                'description' => "Internet Archive collection mirroring 70+ FBI documents released under FOIA that comprise the Bureau's COINTELPRO and related surveillance file on the Third World Women's Alliance (TWWA), the multiracial socialist-feminist organization that grew out of SNCC's Black Women's Liberation Committee and was active 1970–1980. Includes Bureau memoranda, analyst reports, characterizations, ALSC (African Liberation Support Committee) conference reports, and complete scans of the TWWA's own newspaper Triple Jeopardy intercepted by the FBI. A primary-source set that documents how the federal government's repression apparatus extended its 1960s anti-Black operations directly into the 1970s women-of-color left. Indexed for the NPPC archive as an external IA collection — too many individual files to mirror locally.",
                'record_type' => 'collection',
                'source_format' => 'FOIA file set',
                'file' => 'https://archive.org/details/twwa_cointelpro',
                'collection' => 'FBI FOIA — COINTELPRO',
                'authors' => 'Federal Bureau of Investigation (FBI)',
                'publisher' => 'FBI / mirrored on Internet Archive',
                'subjects' => ['COINTELPRO', 'FBI', "Third World Women's Alliance", 'TWWA', 'SNCC', 'Black Feminism', 'Women of Color Left', 'Triple Jeopardy', 'State Repression'],
            ],
        ];

        $base = [
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($records as $r) {
            $slug = $r['slug']; unset($r['slug']);
            $payload = $r + $base;
            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($payload);
                $this->info("RECORD updated: {$payload['title']}");
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $slug] + $payload);
                $this->info("RECORD added: {$payload['title']}");
                $added++;
            }
        }
        $this->line("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
