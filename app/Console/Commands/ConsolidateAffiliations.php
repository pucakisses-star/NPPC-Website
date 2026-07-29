<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Consolidates duplicate/variant affiliation labels across all prisoner records
 * into a single canonical form each — the affiliation-field counterpart to
 * prisoners:consolidate-ideologies. Merges are case/punctuation variants
 * ("Fries's" curly vs straight apostrophe), acronym doublets ("SDS" vs
 * "Students for a Democratic Society", "FALN" three ways), and clear
 * same-organization aliases (Watch Tower Society = Jehovah's Witnesses,
 * Weatherman = Weather Underground, Sam Melville/Jonathan Jackson Unit =
 * United Freedom Front). Distinct-but-related organizations (BLA vs Black
 * Liberation Front, ILWU vs ILA, New Black Panther Party vs BPP) are left
 * alone, as are named case-groups (Catonsville 9, MOVE 9, Panther 21).
 *
 * For each prisoner, every affiliation is remapped through MAP and the list is
 * de-duplicated (order preserved). Writes via a raw JSON update. Idempotent —
 * canonical values are never keys. Use --dry-run to preview.
 */
class ConsolidateAffiliations extends Command
{
    protected $signature = 'prisoners:consolidate-affiliations {--dry-run : Show what would change without writing}';

    protected $description = 'Merge duplicate/variant affiliation labels into canonical forms';

    /** variant => canonical. Canonical forms never appear as a key. */
    private const MAP = [
        // --- case / punctuation / plural variants ---
        'Anarchist Movement' => 'Anarchist movement',
        // These two ran BACKWARDS: they rewrote the title-cased spelling the
        // corpus actually uses (18 records for Black Liberation Movement, 4
        // for Animal Liberation Movement) into a lower-cased one carried by
        // NOBODY, so running this command would have invented two labels and
        // emptied two good ones. Reversed, so the stray lower-case spellings
        // fold into the established forms instead.
        'Animal liberation movement' => 'Animal Liberation Movement',
        'Black liberation movement' => 'Black Liberation Movement',
        'Fries’s Rebellion' => 'Fries\'s Rebellion',
        'Shays’ Rebellion' => 'Shays\' Rebellion',
        'Fugitives from enslavement' => 'Fugitive from enslavement',
        'Janes Revenge' => 'Jane\'s Revenge',
        'Sanctuary movement' => 'Sanctuary Movement',
        'New Afrikan independence movement' => 'New Afrikan Independence Movement',
        'Southern Tenant Farmers Union' => 'Southern Tenant Farmers\' Union',
        'United Farmers\' League' => 'United Farmers League',
        'Wilmington 10' => 'Wilmington Ten',
        'Catonsville Nine' => 'Catonsville 9',
        'Cleveland Four' => 'Cleveland 4',
        'Pendleton 14' => 'Camp Pendleton 14',
        'Chicano/Mexicano movement' => 'Chicano movement',

        // --- acronym / parenthetical doublets ---
        'American Indian Movement (AIM)' => 'American Indian Movement',
        'Anti-Racist Action (ARA)' => 'Anti-Racist Action',
        'Communist Party USA (CPUSA)' => 'Communist Party USA',
        'Earth Liberation Front (ELF)' => 'Earth Liberation Front',
        'International Labor Defense (ILD)' => 'International Labor Defense',
        'Irish Republican Army (IRA)' => 'Irish Republican Army',
        'National Textile Workers Union (NTW)' => 'National Textile Workers Union',
        'Partido Liberal Mexicano (PLM)' => 'Partido Liberal Mexicano',
        'Puerto Rican Socialist Party (PSP)' => 'Puerto Rican Socialist Party',
        'Student Nonviolent Coordinating Committee (SNCC)' => 'Student Nonviolent Coordinating Committee',
        'SNCC' => 'Student Nonviolent Coordinating Committee',
        'SCLC' => 'Southern Christian Leadership Conference',
        'SDS' => 'Students for a Democratic Society',
        'Textile Workers Union of America (TWUA)' => 'Textile Workers Union of America',
        'United Mine Workers of America (UMWA)' => 'United Mine Workers of America',
        'United Steelworkers (USWA)' => 'United Steelworkers',
        'Vietnam Veterans Against the War (VVAW)' => 'Vietnam Veterans Against the War',
        'FALN' => 'FALN (Fuerzas Armadas de Liberación Nacional)',
        'Fuerzas Armadas de Liberación Nacional (FALN)' => 'FALN (Fuerzas Armadas de Liberación Nacional)',
        'Armed Forces of Popular Resistance (FARP)' => 'Fuerzas Armadas de Resistencia Popular (FARP)',
        'San Patricio Battalion / Batallón de San Patricio' => 'San Patricio Battalion',
        'Industrial Workers of the World' => 'Industrial Workers of the World (IWW)',
        'Marine Transport Workers (IWW)' => 'Industrial Workers of the World (IWW)',

        // --- same organization under different names ---
        'Communist Party' => 'Communist Party USA',
        'Communist Party of America' => 'Communist Party USA',
        'Workers (Communist) Party' => 'Communist Party USA',
        'Watch Tower Society' => 'Jehovah\'s Witnesses',
        'Religious Society of Friends' => 'Quaker',
        'Religious Society of Friends (Quaker)' => 'Quaker',
        'Weather Underground Organization' => 'Weather Underground',
        'Weatherman' => 'Weather Underground',
        'Sam Melville/Jonathan Jackson Unit' => 'United Freedom Front',
        'MOVE Organization' => 'MOVE',
        'Black Panther Party (Baltimore chapter)' => 'Black Panther Party',
        'Provisional Government of the Republic of New Afrika' => 'Republic of New Afrika',
        'Black Nationalists of New Libya' => 'Republic of New Libya',
        'Provisional Irish Republican Army' => 'Provisional IRA',
        'Provisional Irish Republican Army (alleged)' => 'Provisional IRA',
        'Irish Northern Aid (alleged)' => 'Irish Northern Aid',
        'Irish-American support movement' => 'Irish republican solidarity',
        'Animal Liberation Front (alleged)' => 'Animal Liberation Front',
        'Anarchist Black Cross supporters' => 'Anarchist Black Cross',
        'Commonweal of Christ' => 'Coxey\'s Army',
        'Calico Indians' => 'Anti-Rent Association',
        'Winter Soldiers Organization' => 'Vietnam Veterans Against the War',
        'United Klans of America' => 'Ku Klux Klan',
        'National Civil Liberties Bureau' => 'American Civil Liberties Union',
        'Michigan Committee for Protection of Foreign Born' => 'American Committee for Protection of Foreign Born',
        'Civil Rights Congress Bail Fund' => 'Civil Rights Congress',
        'Young Socialist' => 'Young Socialist Alliance',
        'Young Workers League' => 'Young Communist League',
        'Socialist Party' => 'Socialist Party of America',
        'CIO' => 'Congress of Industrial Organizations',
        'CIO Woodworkers' => 'International Woodworkers of America',
        'International Fur & Leather Workers Union' => 'Furriers Union',
        'Marine Firemen\'s Union' => 'Marine Firemen, Oilers and Watertenders Union',
        'Mine, Mill and Smelter Workers' => 'Mine, Mill & Smelter Workers Union',
        'Mine, Mill and Smelter Workers Union' => 'Mine, Mill & Smelter Workers Union',
        'United Electrical Workers' => 'United Electrical Workers (UE)',
        'United Electrical and Radio Workers' => 'United Electrical Workers (UE)',
        'United Electrical Radio and Machine Workers' => 'United Electrical Workers (UE)',
        'United Electrical, Radio and Machine Workers (UE) Local 259' => 'United Electrical Workers (UE)',
        'United Auto Workers (UAW) Local 372' => 'United Auto Workers',
        'United Packinghouse Workers' => 'United Packinghouse Workers of America',
        'United Packinghouse Workers of America (UPWA-CIO)' => 'United Packinghouse Workers of America',
        'Workers Alliance' => 'Workers Alliance of America',
        'United States Army' => 'U.S. Army',
        'Korean Independence News' => 'Korean Independence',
        'African Communities League' => 'Universal Negro Improvement Association',
        'Negro World' => 'Universal Negro Improvement Association',
        'Black Star Line' => 'Universal Negro Improvement Association',

        // --- Puerto Rico / Cuba ---
        'Los Macheteros' => 'Los Macheteros (Ejército Popular Boricua)',
        'Ejército Popular Boricua (Los Macheteros)' => 'Los Macheteros (Ejército Popular Boricua)',
        'Boricua Popular Army' => 'Los Macheteros (Ejército Popular Boricua)',
        'The Cuban Five' => 'Cuban Five',
        'Wasp Network' => 'Cuban Five',
        'Crusade for the Rescue of Vieques' => 'Vieques civil disobedience movement',
        'Movimiento de Liberacion Nacional Mexicano' => 'Movimiento de Liberación Nacional Mexicano (MLN)',

        // --- named case-groups with variant spellings ---
        'Kingsbay Ploughshares' => 'Kings Bay Plowshares 7',
        'Virgin Island 5' => 'Virgin Islands Five',
        'Virgin Islands 5' => 'Virgin Islands Five',
        'Fountain Valley 5' => 'Virgin Islands Five',
        'May 19 Communist Organization' => 'May 19th Communist Organization',

        // --- one-off Plowshares actions -> the movement (famous named groups kept) ---
        'ANZUS Plowshares' => 'Plowshares Movement',
        'Pershing Plowshares' => 'Plowshares Movement',
        'Transform Now Plowshares' => 'Plowshares Movement',
        'Silo Pruning Hooks' => 'Plowshares Movement',

        // --- movements / uprisings ---
        '2020 George Floyd Uprising' => 'George Floyd Uprising',
        '2020 George Floyd uprising / Atlanta' => 'George Floyd Uprising',
        '2020 Portland uprising' => 'George Floyd Uprising',
        'Stop Cop City' => 'Defend the Atlanta Forest',
        'Atlanta Forest Defenders' => 'Defend the Atlanta Forest',
        'Sacred Stone Camp' => 'NoDAPL',
        'Occupy' => 'Occupy Movement',
        'Occupy Wall Street' => 'Occupy Movement',
        'Occupy Cleveland' => 'Occupy Movement',
        'ACT UP/San Francisco' => 'ACT UP',
        'ACT UP/Tampa Bay' => 'ACT UP',
        'AntiSec' => 'Anonymous',
        'Attica Uprising' => 'Attica Brothers',
        'Attica Liberation Faction' => 'Attica Brothers',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $prisoners = Prisoner::withoutGlobalScopes()->get(['id', 'affiliation']);

        $changed = 0;
        $remaps = 0;

        foreach ($prisoners as $p) {
            $ids = $p->affiliation;
            if (! is_array($ids)) {
                $ids = ($ids === null || $ids === '') ? [] : [$ids];
            }
            if (! $ids) {
                continue;
            }

            $new = [];
            $didRemap = false;
            foreach ($ids as $i) {
                $canon = self::MAP[$i] ?? $i;
                if ($canon !== $i) {
                    $didRemap = true;
                    $remaps++;
                }
                if (! in_array($canon, $new, true)) {
                    $new[] = $canon;
                }
            }

            if ($new !== array_values($ids)) {
                $changed++;
                if (! $dryRun) {
                    DB::table('prisoners')->where('id', $p->id)->update(['affiliation' => json_encode($new)]);
                }
            } elseif ($didRemap) {
                $changed++;
            }
        }

        if ($dryRun) {
            $this->info("Dry run — {$changed} prisoner rows would change; {$remaps} individual label remaps across ".count(self::MAP).' mapped variants.');
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info("Consolidated affiliations on {$changed} prisoner rows ({$remaps} label remaps). API cache cleared.");
        }

        return self::SUCCESS;
    }
}
