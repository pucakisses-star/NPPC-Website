<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * New Masses mining, batch 4 — 1936.
 *
 * As with the other weekly years, the ILD Labor Defender run already holds most
 * of 1936's cases (Scottsboro, the Sacramento CAWIU group, Herndon, Mooney &
 * Billings, the Gallup NM defendants, Brown v. Mississippi, the Tampa flogging,
 * the King-Ramsay-Conner maritime frame-up, Jack Barton, Pedro Albizu Campos,
 * Powers Hapgood, Krumbein, the Modesto pair, Ferrero & Sallitto, the German
 * deportation detainees, Murray Melvin, Emma Cutler, Louise Todd, Wilma
 * Conners, Josephine Johnson, Joe Jones, James Carey, Earl Browder) — all
 * skipped here.
 *
 * This adds the genuinely-new US class-war prisoners of 1936: two Alabama cases
 * (the "stole his own cotton" sharecropper Pierce White and the framed textile
 * organizer Homer Welch), three more Arkansas Southern Tenant Farmers' Union
 * arrests at Forrest City (Dave Benson, Clay East, Caroline Drew), a Camden
 * RCA-Victor striker (Joseph Baker), the Terre Haute "vagrancy" jailing of
 * writers accompanying Earl Browder's campaign (Waldo Frank, Seymour Waldman),
 * the Milwaukee anti-Nazi swastika case (George Loh, Elmer Lochner), and the
 * Workers Alliance president David Lasser.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddNewMasses1936Cases extends Command
{
    protected $signature = 'prisoners:add-new-masses-1936';

    protected $description = 'Add the genuinely-new US class-war prisoners surfaced mining New Masses 1936 (Alabama sharecropper/textile cases, more Arkansas STFU, the Camden RCA strike, the Terre Haute writers, the Milwaukee anti-Nazi case, and the Workers Alliance)';

    public function handle(): int
    {
        $people = [];
        $mk = function (array $p, array $dates = []) use (&$people) {
            $people[] = ['payload' => $p, 'dates' => $dates];
        };

        // ── ALABAMA ─────────────────────────────────────────────────────
        $mk([
            'name' => 'Pierce White', 'first_name' => 'Pierce', 'last_name' => 'White',
            'description' => "Pierce White was a white sharecropper of Lafayette, Chambers County, Alabama, sentenced around 1935–36 to six months in the penitentiary for 'stealing his own cotton' — selling 200 pounds of seed cotton he had raised himself. The prosecution was a frame-up mounted through the federal Rural Rehabilitation Administration after he protested graft in the program; he was jailed while his wife and four children were evicted.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Farm organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Framed for 'stealing his own cotton' after protesting Rural Rehabilitation graft.",
                'convicted' => 'Convicted, 1935',
                'sentence' => 'Six months in the penitentiary.',
                'institution_city' => 'Lafayette', 'institution_state' => 'Alabama',
            ]],
        ], ['incarceration_date' => [1935, null, null]]);

        $mk([
            'name' => 'Homer Welch', 'first_name' => 'Homer', 'last_name' => 'Welch',
            'description' => "Homer Welch was a textile-union organizer in Alabama who in 1936 was convicted by a jury and sentenced to ten years in prison on a manslaughter charge arising from the death of a deputy during a police attack on a picket line. Labor defenders treated the conviction as a frame-up meant to break union organizing in the Southern textile industry.",
            'state' => 'Alabama', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Convicted of manslaughter over a deputy killed in a police attack on a picket line.',
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Ten years.',
                'institution_state' => 'Alabama',
            ]],
        ], ['incarceration_date' => [1936, null, null]]);

        // ── ARKANSAS — SOUTHERN TENANT FARMERS' UNION (Forrest City) ────
        $mk([
            'name' => 'Dave Benson', 'first_name' => 'Dave', 'last_name' => 'Benson',
            'description' => "Dave Benson was an organizer for the Southern Tenant Farmers' Union arrested during the June 1936 cotton-choppers' strike in eastern Arkansas. He was tried at Forrest City amid the planter terror against the STFU, and the jury returned a guilty verdict with a recommendation of the full penalty.",
            'state' => 'Arkansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => 'Tried during the 1936 cotton-choppers strike; convicted.',
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Jury recommended the full penalty.',
                'institution_city' => 'Forrest City', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1936, 6, null]]);

        $mk([
            'name' => 'Clay East', 'first_name' => 'Clay', 'last_name' => 'East',
            'description' => "Clay East was a co-founder of the Southern Tenant Farmers' Union, which he helped organize in 1934 at Tyronza, Arkansas with H. L. Mitchell. During the June 1936 trial of fellow organizer Dave Benson at Forrest City, East was struck over the head and jailed 'for safe keeping' amid the planter terror against the union — a leading white Socialist of the Arkansas sharecropper movement.",
            'state' => 'Arkansas', 'gender' => 'Male',
            'ideologies' => ['Labor organizing', 'Socialism'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed 'for safe keeping' during the STFU terror at the Benson trial.",
                'convicted' => 'Jailed, 1936',
                'sentence' => 'Held.',
                'institution_city' => 'Forrest City', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1936, 6, null]]);

        $mk([
            'name' => 'Caroline Drew', 'first_name' => 'Caroline', 'last_name' => 'Drew',
            'description' => "Caroline Drew was a labor organizer arrested 'on suspicion' at Forrest City, Arkansas during the June 1936 Benson trial, reportedly for talking with a Black striker. She was jailed briefly, searched, and released — one of several outside sympathizers detained in the Southern Tenant Farmers' Union terror.",
            'state' => 'Arkansas', 'gender' => 'Female',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ["Southern Tenant Farmers' Union"],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested 'on suspicion' for talking with a striker during the STFU terror.",
                'convicted' => 'Arrested, 1936',
                'sentence' => 'Jailed briefly and released.',
                'institution_city' => 'Forrest City', 'institution_state' => 'Arkansas',
            ]],
        ], ['arrest_date' => [1936, 6, null]]);

        // ── NEW JERSEY — CAMDEN RCA-VICTOR STRIKE ───────────────────────
        $mk([
            'name' => 'Joseph Baker', 'first_name' => 'Joseph', 'last_name' => 'Baker',
            'description' => "Joseph Baker was among the roughly ninety strikers arrested during the June–July 1936 RCA-Victor strike at Camden, New Jersey, led by the United Electrical and Radio Workers (CIO). He was seized while seated in his car and charged with obstructing traffic and joining an 'unlawful enterprise,' held under $4,000 bail.",
            'state' => 'New Jersey', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['United Electrical and Radio Workers'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Charged with obstructing traffic and 'unlawful enterprise' in the RCA-Victor strike.",
                'convicted' => 'Arrested, 1936',
                'sentence' => 'Held under $4,000 bail.',
                'institution_city' => 'Camden', 'institution_state' => 'New Jersey',
            ]],
        ], ['arrest_date' => [1936, null, null]]);

        // ── INDIANA — TERRE HAUTE (Browder campaign "vagrancy" arrests) ─
        $mk([
            'name' => 'Waldo Frank', 'first_name' => 'Waldo', 'last_name' => 'Frank',
            'description' => "Waldo Frank (1889–1967) was an American novelist and essayist and the first chairman of the League of American Writers. In September 1936 he was arrested and jailed on a 'vagrancy' charge at Terre Haute, Indiana, alongside the Communist presidential candidate Earl Browder, in a police effort to stop Browder's campaign from speaking. The arrests of prominent literary and political figures drew national free-speech protest.",
            'state' => 'Indiana', 'gender' => 'Male',
            'ideologies' => ['Socialism'],
            'affiliation' => ['League of American Writers'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed on a 'vagrancy' charge to suppress the Browder campaign.",
                'convicted' => 'Arrested, 1936',
                'sentence' => 'Jailed.',
                'institution_city' => 'Terre Haute', 'institution_state' => 'Indiana',
            ]],
        ], ['arrest_date' => [1936, 9, null]]);

        $mk([
            'name' => 'Seymour Waldman', 'first_name' => 'Seymour', 'last_name' => 'Waldman',
            'description' => "Seymour Waldman was a journalist — author of exposés of the munitions industry — traveling with Earl Browder's 1936 presidential campaign. He was among Browder's party jailed as 'vagrants' at Terre Haute, Indiana in September 1936 in the police drive to keep the campaign from speaking.",
            'state' => 'Indiana', 'gender' => 'Male',
            'ideologies' => ['Communism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Jailed as a 'vagrant' with the Browder campaign party.",
                'convicted' => 'Arrested, 1936',
                'sentence' => 'Jailed.',
                'institution_city' => 'Terre Haute', 'institution_state' => 'Indiana',
            ]],
        ], ['arrest_date' => [1936, 9, null]]);

        // ── WISCONSIN — MILWAUKEE ANTI-NAZI SWASTIKA CASE ───────────────
        $mk([
            'name' => 'George Loh', 'first_name' => 'George', 'last_name' => 'Loh',
            'description' => "George Loh was the editor of the German-language liberal newspaper Arbeiter in Milwaukee. In 1936, after tearing down a Nazi swastika at a picnic, he was beaten by Storm Troopers, then arrested and held about a day on an 'inciting to riot' charge; found guilty in October 1936, he appealed — an anti-fascist free-speech case.",
            'state' => 'Wisconsin', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested for 'inciting to riot' after tearing down a Nazi swastika.",
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Held about a day; convicted and appealed.',
                'institution_city' => 'Milwaukee', 'institution_state' => 'Wisconsin',
            ]],
        ], ['arrest_date' => [1936, null, null]]);

        $mk([
            'name' => 'Elmer Lochner', 'first_name' => 'Elmer', 'last_name' => 'Lochner',
            'description' => "Elmer Lochner was a well-known anti-fascist of Milwaukee and a co-defendant with George Loh in the same 1936 swastika incident. He was arrested, convicted of 'inciting to riot,' and appealed alongside Loh.",
            'state' => 'Wisconsin', 'gender' => 'Male',
            'ideologies' => ['Anti-fascism'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested and convicted of 'inciting to riot' in the anti-Nazi swastika case.",
                'convicted' => 'Convicted, 1936',
                'sentence' => 'Convicted and appealed.',
                'institution_city' => 'Milwaukee', 'institution_state' => 'Wisconsin',
            ]],
        ], ['arrest_date' => [1936, null, null]]);

        // ── DISTRICT OF COLUMBIA — WORKERS ALLIANCE ─────────────────────
        $mk([
            'name' => 'David Lasser', 'first_name' => 'David', 'last_name' => 'Lasser',
            'description' => "David Lasser (1902–1996) was the national president of the Workers Alliance of America, the organization of the unemployed and WPA workers. In late 1936 he was arrested in Washington, D.C. for 'parading without a permit' after leading a delegation attempting to see President Roosevelt.",
            'state' => 'District of Columbia', 'gender' => 'Male',
            'ideologies' => ['Labor organizing'],
            'affiliation' => ['Workers Alliance of America'],
            'era' => '1930s', 'in_custody' => false, 'released' => true,
            'cases' => [[
                'charges' => "Arrested for 'parading without a permit' leading a Workers Alliance delegation.",
                'convicted' => 'Arrested, 1936',
                'sentence' => 'Held.',
                'institution_city' => 'Washington', 'institution_state' => 'District of Columbia',
            ]],
        ], ['arrest_date' => [1936, null, null]]);

        // ── INSERT ───────────────────────────────────────────────────────
        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} of ".count($people)." New Masses 1936 prisoner(s).");

        return self::SUCCESS;
    }
}
