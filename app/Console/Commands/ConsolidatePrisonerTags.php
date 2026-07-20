<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Folds duplicate/variant ideology and affiliation tags into canonical
 * forms — case variants (Conscientious objection/Objection), dash variants
 * (Marxism–Leninism), abbreviations of the same organization (CORE, SEIU,
 * NABPP-PC), historical renames (Workers (Communist) Party → CPUSA), and
 * one-off phrasings (Labor rights → Labor organizing).
 *
 * Deliberately NOT merged, after checking the underlying records:
 * Communist Labor Party (William Bross Lloyd was convicted as its founder,
 * before the CPUSA merger), Provisional IRA vs IRA, Trade unionism vs Labor
 * organizing, case-cohort tags (Panther 21, MOVE 9, Elaine Twelve), local
 * union locals, and distinct textile/mine unions.
 *
 * Idempotent: mapping already-canonical values is a no-op; rows only save
 * when something changed.
 */
final class ConsolidatePrisonerTags extends Command {
    protected $signature = 'prisoners:consolidate-tags {--dry : Report what would change without saving}';
    protected $description = 'Fold variant ideology/affiliation tags into canonical forms';

    private const IDEOLOGY_MAP = [
        'Conscientious objection' => 'Conscientious Objection',
        'Anti-war' => 'Anti-War',
        'Anti-militarism' => 'Anti-Militarism',
        'Prison abolition' => 'Prison Abolition',
        'Puerto Rican independence' => 'Puerto Rican Independence',
        'Puerto Rican independence solidarity' => 'Puerto Rican Independence',
        'Puerto Rican rights' => 'Puerto Rican Independence',
        'Puerto Rican movement' => 'Puerto Rican Independence',
        'Marxism–Leninism' => 'Marxism-Leninism',
        'Prisoner rights' => "Prisoners' rights",
        'Labor Activism' => 'Labor organizing',
        'Labor movement' => 'Labor organizing',
        'Labor rights' => 'Labor organizing',
        'Labor' => 'Labor organizing',
        'Environmentalism' => 'Environmental Activism',
        'Animal liberation' => 'Animal Rights Activism',
        'Chicano movement' => 'Chicano liberation',
        'Native American rights' => 'Indigenous Sovereignty',
        'Anti-removal' => 'Indigenous Sovereignty',
        'Religious nonresistance' => 'Christian pacifism',
        'Veterans peace movement' => 'Peace movement',
        'Disarmament' => 'Anti-nuclear',
        'Black liberation solidarity' => 'Black liberation',
        'Anti–private prison' => 'Anti-private prison',
    ];

    private const AFFILIATION_MAP = [
        'Industrial Workers of the World' => 'Industrial Workers of the World (IWW)',
        'Inmates for Action' => 'Inmates For Action',
        "New Year's Gang" => 'New Years Gang',
        'CORE' => 'Congress of Racial Equality (CORE)',
        'Los Macheteros' => 'Los Macheteros (Ejército Popular Boricua)',
        'SEIU' => 'Service Employees International Union (SEIU)',
        'United Electrical Workers' => 'United Electrical Workers (UE)',
        'Mennonite Church' => 'Mennonite',
        'Socialist Party' => 'Socialist Party of America',
        'NABPP-PC' => 'New Afrikan Black Panther Party',
        'United States House of Representatives' => 'U.S. House of Representatives',
        'Workers (Communist) Party' => 'Communist Party USA',
        'Partido Socialista Puertorriqueño' => 'Puerto Rican Socialist Party',
        'Palestinian rights movement' => 'Pro-Palestine movement',
        'Philadelphia anarchist movement' => 'Anarchist movement',
        'Movimiento de Liberación Nacional (MLN)' => 'Movimiento de Liberación Nacional Puertorriqueño (MLN)',
    ];

    public function handle(): int {
        $dry = (bool) $this->option('dry');
        $changedRows = 0;
        $folds = ['ideologies' => 0, 'affiliation' => 0];

        Prisoner::withoutGlobalScopes()
            ->where(function ($q) {
                $q->whereNotNull('ideologies')->orWhereNotNull('affiliation');
            })
            ->chunkById(500, function ($prisoners) use ($dry, &$changedRows, &$folds) {
                foreach ($prisoners as $p) {
                    $dirty = false;
                    foreach (['ideologies' => self::IDEOLOGY_MAP, 'affiliation' => self::AFFILIATION_MAP] as $field => $map) {
                        $vals = $p->{$field};
                        if (! is_array($vals) || $vals === []) {
                            continue;
                        }
                        $out = [];
                        $fieldChanged = false;
                        foreach ($vals as $v) {
                            $v = is_string($v) ? trim($v) : $v;
                            $mapped = (is_string($v) && isset($map[$v])) ? $map[$v] : $v;
                            if ($mapped !== $v) {
                                $fieldChanged = true;
                                $folds[$field]++;
                            }
                            if (! in_array($mapped, $out, true)) {   // dedupe (e.g. both variants present)
                                $out[] = $mapped;
                            } else {
                                $fieldChanged = true;
                            }
                        }
                        if ($fieldChanged || $out !== $vals) {
                            $p->{$field} = $out;
                            $dirty = true;
                        }
                    }
                    if ($dirty) {
                        $changedRows++;
                        if (! $dry) {
                            $p->save();
                        }
                    }
                }
            });

        if (! $dry) {
            \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
        }

        $this->info(($dry ? '[dry run] Would update' : 'Updated')." {$changedRows} prisoner(s): "
            ."{$folds['ideologies']} ideology fold(s), {$folds['affiliation']} affiliation fold(s).");

        return self::SUCCESS;
    }
}
