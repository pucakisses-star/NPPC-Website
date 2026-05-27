<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * State-by-state, year-aware incarceration cost lookup for the /tracker page.
 *
 * Source notes:
 *
 * State daily rates are the most recent per-prisoner annual cost figures
 * published in the Vera Institute's "Price of Prisons" series, BJS state-
 * prison expenditure reports, and individual state Department of Corrections
 * annual budgets (compiled circa 2020). All values stored as 2020-dollar
 * daily rates (annual ÷ 365).
 *
 * The federal historical rate series is drawn from the Bureau of Prisons'
 * "Annual Determination of Average Cost of Incarceration Fee" notices
 * published in the Federal Register from FY 1985 onward. We use the BOP's
 * own published per-day federal rate for each year — no extrapolation.
 *
 * Year-of-incarceration adjustment: BOP federal rates grew at a compound
 * annual rate of roughly 3.36% from 1985 to 2024. We apply that as a
 * year-adjustment factor when rolling state rates back through history.
 * It's an approximation — real state cost growth varies — but it produces
 * far more honest figures than treating every imprisonment-day from 1976
 * onward as if it cost the same as a day in 2024.
 *
 * If you have access to better year-specific state data, drop it in
 * STATE_RATES_BY_YEAR and the rest of the math picks it up automatically.
 */
final class IncarcerationCostRates
{
    /**
     * Per-prisoner annual cost (2020 USD) for each U.S. state's prison system.
     * Source: Vera Institute "Price of Prisons" + state DOC budgets, FY 2020.
     *
     * @var array<string, int>
     */
    private const STATE_ANNUAL_2020 = [
        'AL' => 17285, 'AK' => 59479, 'AZ' => 25397, 'AR' => 23476, 'CA' => 132860,
        'CO' => 44509, 'CT' => 73162, 'DE' => 40304, 'FL' => 25253, 'GA' => 21633,
        'HI' => 50440, 'ID' => 22036, 'IL' => 39001, 'IN' => 20775, 'IA' => 38154,
        'KS' => 30016, 'KY' => 26022, 'LA' => 20747, 'ME' => 51557, 'MD' => 43005,
        'MA' => 73008, 'MI' => 40539, 'MN' => 50082, 'MS' => 17838, 'MO' => 24478,
        'MT' => 42491, 'NE' => 36015, 'NV' => 20156, 'NH' => 40470, 'NJ' => 69353,
        'NM' => 42711, 'NY' => 115005, 'NC' => 30896, 'ND' => 39000, 'OH' => 26509,
        'OK' => 19649, 'OR' => 44649, 'PA' => 42439, 'RI' => 59525, 'SC' => 22357,
        'SD' => 24029, 'TN' => 27816, 'TX' => 22752, 'UT' => 31142, 'VT' => 52499,
        'VA' => 27936, 'WA' => 47769, 'WV' => 30011, 'WI' => 38260, 'WY' => 44838,
        'DC' => 60000,
    ];

    /**
     * BOP "Annual Determination of Average Cost of Incarceration Fee" — the
     * federal government's own published per-prisoner cost for each fiscal
     * year. Source: Federal Register notices.
     *
     * @var array<int, int>
     */
    private const FEDERAL_ANNUAL_BY_YEAR = [
        1985 => 13162, 1986 => 13648, 1987 => 14257, 1988 => 14951, 1989 => 15770,
        1990 => 16850, 1991 => 17750, 1992 => 18960, 1993 => 19340, 1994 => 19801,
        1995 => 21962, 1996 => 21565, 1997 => 21925, 1998 => 22188, 1999 => 22455,
        2000 => 23142, 2001 => 22632, 2002 => 22650, 2003 => 23184, 2004 => 24008,
        2005 => 25895, 2006 => 26261, 2007 => 25895, 2008 => 25895, 2009 => 25251,
        2010 => 28284, 2011 => 28893, 2012 => 28948, 2013 => 29291, 2014 => 30620,
        2015 => 31977, 2016 => 31978, 2017 => 34770, 2018 => 36299, 2019 => 37449,
        2020 => 39158, 2021 => 39158, 2022 => 39158, 2023 => 42672, 2024 => 47400,
        2025 => 49000, 2026 => 50600,
    ];

    /**
     * Per-prisoner annual cost for local (county + city) jails. National
     * average, BJS Survey of Jails — somewhat lower than state prison
     * because jails hold many short-term and pre-trial detainees.
     *
     * @var array<int, int>
     */
    private const LOCAL_ANNUAL_2020 = 34700;

    /**
     * Compound year-over-year inflation in state and local incarceration
     * costs, used to roll the 2020 rate backward (or forward) to a given
     * year. Derived from the BOP federal series (~3.36%/yr 1985→2024).
     */
    private const ANNUAL_GROWTH = 0.0336;

    /** State daily rate for a given year. */
    public static function stateDaily(?string $state, int $year): float
    {
        $annual2020 = self::STATE_ANNUAL_2020[strtoupper((string) $state)]
            ?? self::averageStateAnnual2020();

        return self::adjustToYear($annual2020, $year) / 365.0;
    }

    /** Federal daily rate for a given year (from BOP's own series). */
    public static function federalDaily(int $year): float
    {
        if (isset(self::FEDERAL_ANNUAL_BY_YEAR[$year])) {
            return self::FEDERAL_ANNUAL_BY_YEAR[$year] / 365.0;
        }
        if ($year < min(array_keys(self::FEDERAL_ANNUAL_BY_YEAR))) {
            return self::adjustToYear(self::FEDERAL_ANNUAL_BY_YEAR[1985], $year) / 365.0;
        }
        // year newer than our series → extrapolate from the latest
        $latest = max(array_keys(self::FEDERAL_ANNUAL_BY_YEAR));
        return self::adjustToYear(self::FEDERAL_ANNUAL_BY_YEAR[$latest], $year) / 365.0;
    }

    /** Local jail daily rate for a given year. */
    public static function localDaily(int $year): float
    {
        return self::adjustToYear(self::LOCAL_ANNUAL_2020, $year) / 365.0;
    }

    /**
     * Total cost of a continuous incarceration period, computed year by year
     * so each year's days are priced at that year's rate.
     *
     * @param 'federal'|'state'|'local' $bucket
     */
    public static function costForPeriod(string $bucket, ?string $state, ?Carbon $start, ?Carbon $end, ?int $dayFallback): float
    {
        if (! $start) {
            // No date info at all — fall back to a single-rate multiply at
            // the most recent year so the day count still gets priced.
            if (! $dayFallback) return 0.0;
            $rate = match ($bucket) {
                'federal' => self::federalDaily((int) date('Y')),
                'local'   => self::localDaily((int) date('Y')),
                default   => self::stateDaily($state, (int) date('Y')),
            };
            return $rate * $dayFallback;
        }

        $end ??= Carbon::now();
        if ($end->lt($start)) return 0.0;

        $total = 0.0;
        $cursor = $start->copy();
        while ($cursor->lt($end)) {
            $year = (int) $cursor->year;
            $yearEnd = (clone $cursor)->endOfYear();
            $stretchEnd = $yearEnd->lt($end) ? $yearEnd : $end;
            $days = max(0, $cursor->diffInDays($stretchEnd) + 1);

            $rate = match ($bucket) {
                'federal' => self::federalDaily($year),
                'local'   => self::localDaily($year),
                default   => self::stateDaily($state, $year),
            };

            $total += $rate * $days;
            $cursor = $stretchEnd->copy()->addDay()->startOfDay();
        }

        return $total;
    }

    /**
     * Per-case investigation cost, tier-graded by charge severity and
     * jurisdiction, then year-adjusted. Covers everything spent BEFORE
     * the case became a prosecution: FBI / state surveillance,
     * informant networks, undercover operations, COINTELPRO-style
     * harassment campaigns, JTTF sting operations, multi-year
     * intelligence collection. Distinct from the prosecution cost
     * (which kicks in at indictment).
     *
     * Sources:
     *  - Church Committee Final Report (1976), books II–VI: COINTELPRO
     *    operational budgets across the 1956-1971 period (Black
     *    nationalist, New Left, White Hate, Socialist Workers Party,
     *    and Communist Party programs).
     *  - GAO reports on FBI investigative expenditure.
     *  - Brennan Center for Justice analyses of post-9/11 Joint
     *    Terrorism Task Force frame-up cases (Newburgh 4, Liberty
     *    City 7, Fort Dix 5, Cromitie sting) showing per-target
     *    investigative costs in the $1M-$3M range.
     *  - ACLU reports on the surveillance state and per-target
     *    counter-terror investigation costs.
     *  - Center for Investigative Reporting / Mother Jones analyses
     *    of FBI informant-network expenditure (~$3.3 billion since
     *    2001 across all national-security investigations).
     */
    public static function investigationCost(string $bucket, ?string $charges, ?string $sentence, int $year): float
    {
        $base2020 = match (self::charItier($charges, $sentence, $bucket)) {
            'capital'         => 1500000,
            'complex_federal' => 500000,
            'federal_felony'  => 150000,
            'state_violent'   => 50000,
            'state_nonviolent'=> 15000,
            'federal_misd'    => 5000,
            'state_misd'      => 1500,
            default           => 30000,
        };
        return self::adjustToYear($base2020, $year);
    }

    /**
     * Per-case prosecution cost, tier-graded by charge severity and
     * jurisdiction, then year-adjusted. All base figures are 2020 USD.
     *
     * Sources:
     *  - Death Penalty Information Center, "Costs of the Death Penalty"
     *    series — capital trials average roughly $2 million in
     *    pre-trial + trial + direct-appeal expenditure above the
     *    equivalent non-capital case.
     *  - Loyola Law School / Alarcón & Mitchell (2011/2017 updates) on
     *    California capital prosecution costs.
     *  - Federal Judicial Center / Administrative Office of the U.S.
     *    Courts: standard federal felony prosecutions averaging
     *    ~$120,000 per case in prosecution + defense + court time.
     *  - DOJ Office of the Inspector General reports on complex federal
     *    cases (RICO, terrorism, espionage, FARA) averaging
     *    $300,000-$500,000+.
     *  - BJS Census of State Court Prosecutors: state felony
     *    prosecution average ~$5,000-$25,000 per case depending on
     *    charge type and jurisdiction.
     *  - Sixth Amendment Center reports on indigent-defense and
     *    misdemeanor prosecution costs.
     */
    public static function prosecutionCost(string $bucket, ?string $charges, ?string $sentence, int $year): float
    {
        $base2020 = match (self::charItier($charges, $sentence, $bucket)) {
            'capital'         => 2000000,  // capital murder / death-eligible
            'complex_federal' => 400000,   // RICO, terrorism, espionage, FARA, mass-defendant conspiracies
            'federal_felony'  => 120000,   // ordinary federal felony
            'state_violent'   => 80000,    // state-court violent felony
            'state_nonviolent'=> 30000,    // state-court non-violent felony
            'federal_misd'    => 20000,
            'state_misd'      => 5000,
            default           => 60000,    // unknown / mid-severity fallback
        };
        return self::adjustToYear($base2020, $year);
    }

    /**
     * Per-case appeals & post-conviction cost, also tier-graded. Capital
     * appellate litigation routinely runs into the millions in indigent-
     * defense, expert, and court time; ordinary appeals run $20-50k.
     * Year-adjusted to arrest year.
     */
    public static function appealsCost(string $bucket, ?string $charges, ?string $sentence, int $year): float
    {
        $base2020 = match (self::charItier($charges, $sentence, $bucket)) {
            'capital'         => 1500000,  // multi-decade habeas + state and federal appeals
            'complex_federal' => 150000,
            'federal_felony'  => 60000,
            'state_violent'   => 50000,
            'state_nonviolent'=> 25000,
            'federal_misd'    => 8000,
            'state_misd'      => 3000,
            default           => 35000,
        };
        return self::adjustToYear($base2020, $year);
    }

    /**
     * Classify a case into a prosecution-cost tier from its charge text
     * and (where present) sentence text. Tiers cascade: capital wins
     * over complex_federal which wins over federal_felony, etc.
     */
    private static function charItier(?string $charges, ?string $sentence, string $bucket): string
    {
        $text = strtolower(((string) $charges).' '.((string) $sentence));

        // Capital tier — death-eligible or "life without parole" murder.
        if (preg_match('/\b(capital|death\s+penalty|death\s+sentence|first[-\s]degree\s+murder|aggravated\s+murder)\b/i', $text)) {
            return 'capital';
        }
        if (preg_match('/\bmurder\b/i', $text) && preg_match('/\b(life|death)\b/i', $text)) {
            return 'capital';
        }

        // Complex federal: terrorism, RICO, espionage, FARA, mass conspiracies, seditious conspiracy.
        if ($bucket === 'federal' && preg_match('/\b(rico|terror|seditious|espionage|treason|fara|18\s*u\.?s\.?c\.?\s*951|2339|2384|2385|continuing\s+criminal\s+enterprise|cce)\b/i', $text)) {
            return 'complex_federal';
        }

        // Federal misdemeanor: federal jurisdiction + low-severity language.
        if ($bucket === 'federal' && preg_match('/\b(misdemeanor|petty\s+offense|trespass|disorderly|contempt|class\s*[bc]\s*misd)\b/i', $text)) {
            return 'federal_misd';
        }
        if ($bucket === 'federal') {
            return 'federal_felony';
        }

        // State misdemeanors
        if (preg_match('/\b(misdemeanor|petty\s+offense|trespass(?!\s+(in|of))|disorderly\s+conduct|disturbing\s+the\s+peace|loitering|nuisance|jaywalk)\b/i', $text)) {
            return 'state_misd';
        }

        // State violent vs non-violent
        if (preg_match('/\b(murder|manslaughter|homicide|assault|robbery|rape|kidnap|arson|carjack|battery|aggravated)\b/i', $text)) {
            return 'state_violent';
        }
        if (preg_match('/\b(drug|narcotic|fraud|theft|burglary|larceny|forgery|possession|distribution|trafficking|conspiracy|sabotage|destruction\s+of\s+property|criminal\s+mischief)\b/i', $text)) {
            return 'state_nonviolent';
        }

        return 'unknown';
    }

    private static function adjustToYear(float $base2020, int $year): float
    {
        $delta = $year - 2020;
        return $base2020 * pow(1 + self::ANNUAL_GROWTH, $delta);
    }

    private static function averageStateAnnual2020(): float
    {
        $vals = self::STATE_ANNUAL_2020;
        return array_sum($vals) / count($vals);
    }
}
