<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Consolidates duplicate/variant ideology labels across all prisoner records
 * into a single canonical form each. Most merges are pure case, hyphenation, or
 * singular/plural variants ("Anti-war" → "Anti-War", "Communist" → "Communism");
 * a smaller set are unambiguous synonyms ("Native sovereignty" → "Indigenous
 * Sovereignty", "Pro-Palestinian" → "Pro-Palestine", "Pro-life" →
 * "Anti-abortion"). Related-but-distinct movements (Black liberation vs Black
 * Power vs Black nationalism, Anti-war vs Anti-militarism, etc.) are left alone.
 *
 * For each prisoner, every ideology is remapped through MAP and the list is
 * de-duplicated (order preserved). Writes via a raw JSON update (no model
 * events). Idempotent — canonical values are not themselves keys, so re-running
 * changes nothing. Use --dry-run to preview.
 */
class ConsolidateIdeologies extends Command
{
    protected $signature = 'prisoners:consolidate-ideologies {--dry-run : Show what would change without writing}';

    protected $description = 'Merge duplicate/variant ideology labels into canonical forms';

    /** variant => canonical. Canonical forms never appear as a key. */
    private const MAP = [
        // --- case / hyphenation / plural variants ---
        'Anti-war' => 'Anti-War',
        'Anti-militarism' => 'Anti-Militarism',
        'Anti-Imperialism' => 'Anti-imperialism',
        'Anti-imperialist' => 'Anti-imperialism',
        'Black Liberation' => 'Black liberation',
        'Black nationalism' => 'Black Nationalism',
        'New Afrikan Independence' => 'New Afrikan independence',
        'Civil Rights' => 'Civil rights',
        'Civil Liberties' => 'Civil liberties',
        'Anti-racist' => 'Anti-racism',
        'Racial justice' => 'Racial Justice',
        'Antifascism' => 'Anti-fascism',
        'Anti-fascist' => 'Anti-fascism',
        'Antifascist' => 'Anti-fascism',
        'Anti-Nuclear' => 'Anti-nuclear',
        'Pacifist' => 'Pacifism',
        'Conscientious objection' => 'Conscientious Objection',
        'Communist' => 'Communism',
        'Socialist' => 'Socialism',
        'Revolutionary Socialism' => 'Revolutionary socialism',
        'Marxist-Leninist' => 'Marxism-Leninism',
        'Anarchist' => 'Anarchism',
        'Anarcho-syndicalism' => 'Anarcho-Syndicalism',
        'Anarcho-syndicalist' => 'Anarcho-Syndicalism',
        'Puerto Rican independence' => 'Puerto Rican Independence',
        'Irish Republicanism' => 'Irish republicanism',
        'Women\'s suffrage' => 'Feminism',
        'Feminist' => 'Feminism',
        'Reproductive Justice' => 'Reproductive Rights',
        'LGBTQ rights' => 'LGBTQ liberation',
        'LGBTQ+ liberation' => 'LGBTQ liberation',
        'Trans Liberation' => 'LGBTQ liberation',
        'Press Freedom' => 'Press freedom',
        'Free Speech' => 'Free speech',
        'Anti-Police Brutality' => 'Anti-police brutality',
        'Anti-Police Violence' => 'Anti-police brutality',
        'Prison abolition' => 'Prison Abolition',
        'Prison abolitionist' => 'Prison Abolition',
        'Abolitionist' => 'Abolitionism',
        'Chicano Liberation' => 'Chicano liberation',
        'Chicano Movement' => 'Chicano movement',
        'Trotskyist' => 'Trotskyism',
        'Nationalist' => 'Nationalism',
        'Revolutionary Nationalism' => 'Revolutionary nationalism',
        'Occupy movement' => 'Occupy Movement',
        'States\' rights' => 'States\' Rights',
        'Immigrant Rights' => 'Immigrant rights',
        'Migrant Solidarity' => 'Migrant solidarity',
        'Prisoners\' Rights' => 'Prisoners\' rights',
        'Animal Liberation' => 'Animal Rights Activism',
        'Financial Privacy' => 'Financial privacy',
        'Catholic left' => 'Catholic Left',
        'Quaker' => 'Quakerism',
        'Anti-colonialism' => 'Anti-colonial',
        'Trade Unionism' => 'Trade unionism',
        'Anti-capitalist' => 'Anti-capitalism',
        'Anti-Capitalism' => 'Anti-capitalism',
        'Anti-authoritarianism' => 'Anti-Authoritarian',
        'Catholicism' => 'Catholic',
        'Islamic' => 'Islam',
        'Anti-Surveillance' => 'Anti-surveillance',

        // --- unambiguous synonyms ---
        'Environmental' => 'Environmental Activism',
        'Environmentalism' => 'Environmental Activism',
        'Environmentalist' => 'Environmental Activism',
        'Animal rights' => 'Animal Rights Activism',
        'Tax protest' => 'Tax resistance',
        'Labor Activism' => 'Labor organizing',
        'Labor activism' => 'Labor organizing',
        'Labor rights' => 'Labor organizing',
        'Peace activism' => 'Peace movement',
        'Pro-life' => 'Anti-abortion',
        'Pro-life activism' => 'Anti-abortion',
        'Pro-Palestine activism' => 'Pro-Palestine',
        'Pro-Palestine solidarity' => 'Pro-Palestine',
        'Pro-Palestinian' => 'Pro-Palestine',
        'Palestinian solidarity' => 'Pro-Palestine',
        'Palestine solidarity' => 'Pro-Palestine',
        'Palestine Solidarity' => 'Pro-Palestine',
        'Indigenous sovereignty' => 'Indigenous Sovereignty',
        'Native sovereignty' => 'Indigenous Sovereignty',
        'Native American sovereignty' => 'Indigenous Sovereignty',
        'Indigenous Right\'s Activism' => 'Indigenous rights',
        'Native American rights' => 'Indigenous rights',

        // --- additional case / spelling variants ---
        'Forest defense' => 'Forest Defense',
        'Latin American solidarity' => 'Latin America solidarity',
        'Anti-Federalism' => 'Anti-Federalist',
        'Homeless rights' => 'Houseless rights',

        // --- women's movement -> Feminism ---
        'Women\'s Suffrage' => 'Feminism',
        'Women\'s liberation' => 'Feminism',
        'Women\'s rights' => 'Feminism',
        'Gender equality' => 'Feminism',

        // --- LGBTQ movements -> LGBTQ liberation ---
        'Gay liberation' => 'LGBTQ liberation',
        'Queer liberation' => 'LGBTQ liberation',
        'Trans liberation' => 'LGBTQ liberation',
        'LGBTQ Rights' => 'LGBTQ liberation',

        // --- anti-police variants -> Anti-police brutality ---
        'Anti-police' => 'Anti-police brutality',
        'Anti-police repression' => 'Anti-police brutality',
        'Anti-police violence' => 'Anti-police brutality',

        // --- other unambiguous synonyms ---
        'Reproductive justice' => 'Reproductive Rights',
        'Animal liberation' => 'Animal Rights Activism',
        'Abolition' => 'Abolitionism',
        'Anti-slavery' => 'Abolitionism',
        'Black freedom' => 'Black liberation',
        'Anti-colonial resistance' => 'Anti-colonial',
        'Anti-Vietnam War' => 'Anti-War',
        'Anti-Iraq War' => 'Anti-War',
        'Working-class organizing' => 'Labor organizing',
        'Immigration justice' => 'Immigrant rights',
        'Migrant rights' => 'Immigrant rights',
        'Christian nonviolence' => 'Christian pacifism',
        'Indigenous treaty rights' => 'Treaty rights',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $prisoners = Prisoner::withoutGlobalScopes()->get(['id', 'ideologies']);

        $changed = 0;
        $remaps = 0;

        foreach ($prisoners as $p) {
            $ids = $p->ideologies;
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
                    DB::table('prisoners')->where('id', $p->id)->update(['ideologies' => json_encode($new)]);
                }
            } elseif ($didRemap) {
                $changed++;
            }
        }

        if ($dryRun) {
            $this->info("Dry run — {$changed} prisoner rows would change; {$remaps} individual label remaps across ".count(self::MAP).' mapped variants.');
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info("Consolidated ideologies on {$changed} prisoner rows ({$remaps} label remaps). API cache cleared.");
        }

        return self::SUCCESS;
    }
}
