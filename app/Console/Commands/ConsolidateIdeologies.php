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
        'Racial justice' => 'Anti-racism',
        'Racial Justice' => 'Anti-racism',
        'Antifascism' => 'Anti-fascism',
        'Anti-fascist' => 'Anti-fascism',
        'Antifascist' => 'Anti-fascism',
        'Anti-Nuclear' => 'Anti-nuclear',
        'Pacifist' => 'Anti-War',
        'Pacifism' => 'Anti-War',
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
        'Anti-Police Brutality' => 'Police Accountability',
        'Anti-Police Violence' => 'Police Accountability',
        'Anti-police brutality' => 'Police Accountability',
        'Prison abolition' => 'Prison Abolition',
        'Prison abolitionist' => 'Prison Abolition',
        'Abolitionist' => 'Abolitionism',
        'Chicano Liberation' => 'Chicano liberation',
        'Chicano Movement' => 'Chicano liberation',
        'Chicano movement' => 'Chicano liberation',
        'Chicano nationalism' => 'Chicano liberation',
        'La Raza' => 'Chicano liberation',
        'Mexicano national liberation' => 'Chicano liberation',
        'Trotskyist' => 'Trotskyism',
        'Nationalist' => 'Nationalism',
        'Revolutionary Nationalism' => 'Revolutionary nationalism',
        'Occupy movement' => 'Occupy Movement',
        'States\' rights' => 'States\' Rights',
        'Immigrant Rights' => 'Immigrant rights',
        'Migrant Solidarity' => 'Immigrant rights',
        'Migrant solidarity' => 'Immigrant rights',
        'Anti-deportation' => 'Immigrant rights',
        'Refugee rights' => 'Immigrant rights',
        'Prisoners\' Rights' => 'Prisoners\' rights',
        'Animal Liberation' => 'Animal Rights Activism',
        'Financial Privacy' => 'Financial privacy',
        'Catholic left' => 'Catholic',
        'Catholic Left' => 'Catholic',
        'Catholic Worker' => 'Catholic',
        'Catholic solidarity with Mexico' => 'Catholic',
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
        // The corpus settled on the title-cased spelling (816 records to
        // one), so that is the canonical and the lower-case variant folds
        // into it. 'Black Southern Labor Organizing' folds in too: it was
        // the ONLY label on all 47 records that carried it -- the 1910s
        // Arkansas sharecroppers organizing for the Progressive Farmers
        // and Household Union -- so retiring it outright would have left
        // them with no politics recorded at all.
        'Labor organizing' => 'Labor Organizing',
        'Black Southern Labor Organizing' => 'Labor Organizing',
        'Labor Activism' => 'Labor Organizing',
        'Labor activism' => 'Labor Organizing',
        'Labor rights' => 'Labor Organizing',
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
        'Indigenous Right\'s Activism' => 'Indigenous Sovereignty',
        'Native American rights' => 'Indigenous Sovereignty',

        // --- additional case / spelling variants ---
        'Forest defense' => 'Environmental Activism',
        'Forest Defense' => 'Environmental Activism',
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

        // --- anti-police variants -> Police Accountability (the largest form) ---
        'Anti-police' => 'Police Accountability',
        'Anti-police repression' => 'Police Accountability',
        'Anti-police violence' => 'Police Accountability',

        // --- other unambiguous synonyms ---
        'Reproductive justice' => 'Reproductive Rights',
        'Animal liberation' => 'Animal Rights Activism',
        'Abolition' => 'Abolitionism',
        'Anti-slavery' => 'Abolitionism',
        'Black freedom' => 'Black liberation',
        'Anti-colonial resistance' => 'Anti-colonial',
        'Anti-Vietnam War' => 'Anti-War',
        'Anti-Iraq War' => 'Anti-War',
        'Working-class organizing' => 'Labor Organizing',
        'Immigration justice' => 'Immigrant rights',
        'Migrant rights' => 'Immigrant rights',
        'Christian nonviolence' => 'Christian pacifism',
        'Indigenous treaty rights' => 'Indigenous Sovereignty',

        // --- environmental -> Environmental Activism ---
        'Environmental justice' => 'Environmental Activism',
        'Climate justice' => 'Environmental Activism',
        'Eco-defense' => 'Environmental Activism',
        'Radical ecology' => 'Environmental Activism',
        'Water protection' => 'Environmental Activism',
        'Anti-pipeline' => 'Environmental Activism',

        // --- organizational tags sitting in the ideology field -> the belief ---
        'IWW' => 'Industrial unionism',
        'UMWA' => 'Labor Organizing',
        'AIM' => 'Indigenous Sovereignty',
        'Catholic Worker / Plowshares' => 'Catholic',

        // --- broad-vs-variant belief merges ---
        'Labor' => 'Labor Organizing',
        'Unionism' => 'Trade unionism',
        'Nonviolence' => 'Anti-War',
        'Native American' => 'Indigenous Sovereignty',
        // whole Indigenous cluster collapsed to one canonical
        'Indigenous rights' => 'Indigenous Sovereignty',
        'Indigenous liberation' => 'Indigenous Sovereignty',
        'Indigenous resistance' => 'Indigenous Sovereignty',
        'Indigenous solidarity' => 'Indigenous Sovereignty',
        'Red Power' => 'Indigenous Sovereignty',
        'Treaty rights' => 'Indigenous Sovereignty',
        'New Afrikan' => 'New Afrikan independence',
        '2020 Uprising' => 'Black Lives Matter',
        'Black uprising' => 'Black liberation',
        'Revolutionary communism' => 'Communism',
        'Revolutionary anti-capitalism' => 'Anti-capitalism',
        'Anti-Yankee imperialism' => 'Anti-imperialism',
        'First Amendment' => 'Free speech',
        'War tax resistance' => 'Tax resistance',
        'Anti-drone' => 'Anti-War',
        'Open rescue' => 'Animal Rights Activism',
        'Eco-anarchism' => 'Anarchism',
        'Prison rebellion' => 'Prison movement',
        'Confederate States of America' => 'Confederate sympathies',
        'Monarchism' => 'Loyalism',
        'Populism' => 'Agrarian populism',
        'Anti-racist self-defense' => 'Self-defense',
        'Self-defense against racist violence' => 'Self-defense',
        'Community self-defense' => 'Self-defense',

        // --- farm/agricultural organizing -> Farm organizing ---
        'Farmers movement' => 'Farm organizing',
        'Farmworker organizing' => 'Farm organizing',
        'Farm labor organizing' => 'Farm organizing',

        // --- single-prisoner categories folded into an existing broad
        //     category so the person keeps a meaningful, filterable tag ---
        'Chiricahua Apache sovereignty' => 'Indigenous Sovereignty',
        'Diné sovereignty' => 'Indigenous Sovereignty',
        'Hawaiian sovereignty' => 'Indigenous Sovereignty',
        'Lakota sovereignty' => 'Indigenous Sovereignty',
        'Oglala Lakota sovereignty' => 'Indigenous Sovereignty',
        'Sauk sovereignty' => 'Indigenous Sovereignty',
        'Land back' => 'Indigenous Sovereignty',
        'Lakota Spiritual Tradition' => 'Indigenous Sovereignty',
        'Palestinian liberation' => 'Pro-Palestine',
        'Anti-Zionism' => 'Pro-Palestine',
        'Indian independence' => 'Anti-colonial',
        'Korean independence' => 'Anti-colonial',
        'Namibian independence' => 'Anti-colonial',
        'Christian socialism' => 'Socialism',
        'Democratic socialism' => 'Socialism',
        'Egoist Anarchism' => 'Anarchism',
        'Green anarchism' => 'Anarchism',
        'Magonista' => 'Anarchism',
        'Voluntaryism' => 'Libertarianism',
        'Black Arts Movement' => 'Black liberation',
        'Black Panther Party' => 'Black liberation',
        'Black political empowerment' => 'Black liberation',
        'Revolutionary Intercommunalism' => 'Black liberation',
        'Solidarity with Black liberation' => 'Black liberation',
        'New Afrikan Black Panther Party (Prison Chapter)' => 'New Afrikan independence',
        'Black Muslim' => 'Black Nationalism',
        'Garveyism' => 'Pan-Africanism',
        'Land Grant Movement' => 'Chicano liberation',
        'Mexican national liberation' => 'Chicano liberation',
        'Raza Unida' => 'Chicano liberation',
        'Zapatista solidarity' => 'Latin America solidarity',
        'Academic freedom' => 'Civil liberties',
        'Anti-HUAC' => 'Civil liberties',
        'Civil rights law' => 'Civil rights',
        'Grand Jury Resistance' => 'Civil disobedience',
        'Anabaptism' => 'Christian pacifism',
        'Catholic social teaching' => 'Catholic',
        'Anti-deployment protest' => 'Anti-War',
        'Military refuser' => 'Conscientious Objection',
        'Anti-fracking' => 'Environmental Activism',
        'Earth liberation' => 'Environmental Activism',
        'Birth control' => 'Reproductive Rights',
        'Anti-solitary' => 'Prisoners\' rights',
        'Political-prisoner advocacy' => 'Prisoners\' rights',
        'Prisoner self-governance' => 'Prison movement',
        'Police abolition' => 'Prison Abolition',
        'Armed self-defense' => 'Self-defense',
        'Digital Privacy' => 'Anti-surveillance',
        'Frontier farmer' => 'Agrarian populism',
        'Land reform' => 'Agrarian populism',
        'Knights of Labor' => 'Labor Organizing',
        'WFM' => 'Labor Organizing',
        'Irish republican socialism' => 'Irish republicanism',
        'Veterans\' organizing' => 'Veterans\' rights',
        'Housing justice' => 'Tenant rights',
    ];

    /**
     * Ideology labels removed outright (dropped from every record). These are
     * either explicitly retired categories or vague/one-off orphans with no
     * sensible broader category to fold into.
     */
    private const REMOVE = [
        // explicitly retired
        'Agorism',
        'Cryptocurrency freedom',
        'Cypherpunk',
        // vague or one-off orphans (single-prisoner, no sensible home)
        'Anti-communism',
        'Anti-Globalization',
        'Anti-Poverty',
        'New Left',
        // Retired: describes the circumstances of an arrest rather than a
        // politics. Both casings are listed because the corpus carried
        // 'Self-Defense' while MAP funnels four variants into
        // 'Self-defense'; removing only one spelling would let the other
        // come back. The narrower 'Black Armed Self-Defense' is retired
        // with them -- the records that carried it keep Black Nationalism
        // and Civil Rights, which is where their politics actually sits.
        'Self-Defense',
        'Self-defense',
        'Black Armed Self-Defense',
        // Retired: a theological school rather than a politics. The four
        // records that carried it (the Wilmington Ten defendants) all keep
        // Civil Rights, and one keeps Black Liberation.
        'Black Liberation Theology',
        'Anti-Stalinism',
        'Anti-system',
        'Anti-Díaz',
        'Armed struggle',
        'Binationalist Zionism',
        'Cuban revolution',
        'Democracy',
        'Economic justice',
        'Foreign-Policy Dissent',
        'Humanitarian',
        'Leftist',
        'Liberalism',
        'Marijuana legalization',
        'Naturalism',
        'Peace Democrats',
        'Pro-China',
        'Pro-Lebanese resistance',
        'Rastafari',
        'Reform Judaism',
        'Revolutionary',
        'Right-wing populism',
        'Social Justice',
        'Sovereign citizen',
        'Targeted by post-9/11 terrorism prosecution',
        'Traditional religion',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $prisoners = Prisoner::withoutGlobalScopes()->get(['id', 'ideologies']);

        $changed = 0;
        $remaps = 0;
        $removed = 0;

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
                if (in_array($canon, self::REMOVE, true)) {
                    $removed++;
                    $didRemap = true;

                    continue; // drop this label entirely
                }
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
            $this->info("Dry run — {$changed} prisoner rows would change; {$remaps} label remaps and {$removed} label removals across ".count(self::MAP).' mapped variants + '.count(self::REMOVE).' removed labels.');
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info("Consolidated ideologies on {$changed} prisoner rows ({$remaps} remaps, {$removed} removals). API cache cleared.");
        }

        return self::SUCCESS;
    }
}
