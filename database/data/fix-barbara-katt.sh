#!/usr/bin/env bash
#
# Barbara Katt -- corrected record.
#
# WHAT WAS WRONG
#   The 1984 case carried an arrest and incarceration date of August 9 and no
#   release date, so the profile page was rendering
#   "Imprisoned For 41 years 11 months 18 days" for a woman whose entire
#   sentence was suspended. She has not been in custody since 1984.
#
# THE SPERRY ACTION -- August 10, 1984 (not August 9)
#   Katt and John LaForge entered the Sperry Defense Systems plant at Eagan,
#   Minnesota dressed as corporate quality-control inspectors, damaged two
#   prototype guidance computers intended for Trident submarines and F-4G
#   aircraft with household hammers, poured blood over the equipment and
#   delivered a citizens indictment accusing Sperry of violating international
#   law. They stayed at the scene and were arrested. Damage was put at roughly
#   $34,000 to $36,000.
#
#   United States v. LaForge and Katt, No. CR-84-66 (D. Minn.)
#   Convicted   October 11, 1984, by jury, of felony depredation or
#               destruction of government property, 18 U.S.C. section 1361
#   Sentenced   November 8, 1984, by Judge Miles W. Lord:
#                 six months imprisonment, ENTIRELY SUSPENDED
#                 six months unsupervised probation
#                 no prison term to be served
#   Lord declined to imprison either of them, attacking both nuclear-weapons
#   production and the gap between this prosecution and Sperry's own earlier
#   overbilling of the government.
#
#   She was evidently released pending trial rather than held; the date of that
#   release is not documented, so none is recorded and the 1984 case adds no
#   days to the total.
#
# THE 2000 DETENTION -- a separate case, three days
#   Taken into custody June 24, 2000 after witnessing and videotaping the
#   Silence Trident action at the Navy Project ELF installation near Clam Lake,
#   Wisconsin, on suspicion of being party to the cutting of three
#   antenna-support poles. Held at the Ashland County Jail until her first
#   hearing on June 27, 2000, when the court found insufficient probable cause
#   and released her.
#
# NO BIRTH DATE IS SET. A November 1984 account put her at 26, which places her
# birth in 1957 or 1958 -- two calendar years, so recording either would be a
# guess. The reported age goes in the bio instead.
#
# After this the counter reads 3 days, from the 2000 detention alone.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-barbara-katt.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "barbara-katt")
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["barbara katt", "barb katt"]))
    ->with("cases")
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Barbara Katt\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();

echo "BEFORE\n";
foreach ($p->cases as $c) {
    echo "  case  arr=".($c->arrest_date ? $c->arrest_date->toDateString() : "-")
        ."  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-")
        ."  days=".($c->imprisoned_for_days ?? "null")."\n";
}

$p->description = "Barbara Katt was a house painter and peace worker in Bemidji, Minnesota, with a philosophy degree from Bemidji State University and experience working with mentally impaired adults. She and her partner John LaForge had been engaged in nonviolent civil disobedience for about four years before the Sperry action. Reported as 26 years old in November 1984. On August 10, 1984 the two walked into the Sperry Defense Systems plant at Eagan, Minnesota dressed as corporate quality-control inspectors, used household hammers to damage two prototype guidance computers intended for Trident submarines and F-4G aircraft, poured blood over the equipment and delivered a citizens indictment accusing the company of violating international law. They remained at the scene and were arrested. A jury convicted them on October 11, 1984 of felony destruction of government property, and on November 8 Judge Miles W. Lord suspended the entire six-month sentence in favour of six months of unsupervised probation, delivering a sentencing speech that attacked nuclear-weapons production and the contrast between this prosecution and Sperry’s own overbilling of the government. She served no prison time for the action. In June 2000 she was held for three days at the Ashland County Jail after videotaping the Silence Trident action at the Navy’s Project ELF site near Clam Lake, Wisconsin, and was released when the court found no probable cause.";
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();

// ---- 1984 Sperry case ------------------------------------------------------
$sperry = $p->cases->first(fn ($c) => stripos((string) $c->charges, "sperry") !== false)
    ?? $p->cases->first()
    ?? $p->cases()->create([]);
$sperry->charges = "Felony depredation or destruction of government property, 18 U.S.C. section 1361 — the Sperry Software Pair plowshares action at the Sperry Defense Systems plant, Eagan, Minnesota, damaging two prototype guidance computers for Trident submarines and F-4G aircraft. Damage put at roughly \$34,000 to \$36,000.";
$sperry->plead = "Not guilty";
$sperry->convicted = "Yes — convicted by a jury on October 11, 1984 in United States v. LaForge and Katt, No. CR-84-66 (D. Minn.).";
$sperry->sentence = "Six months imprisonment, entirely suspended, plus six months of unsupervised probation — no prison term was served. Judge Miles W. Lord declined to imprison either defendant, attacking nuclear-weapons production and the gap between this prosecution and the company’s own earlier overbilling of the government. Arrested at the scene on August 10, 1984 and evidently released pending trial; the date of that release is not documented, so none is recorded here and this case adds no days to the total.";
$sperry->setPartialDate("arrest_date", 1984, 8, 10);
$sperry->setPartialDate("incarceration_date", 1984, 8, 10);
$sperry->setPartialDate("release_date", null);
$sperry->setPartialDate("sentenced_date", 1984, 11, 8);
$sperry->save();

// ---- 2000 Project ELF detention -- a separate case -------------------------
$ashland = Institution::firstOrCreate(
    ["name" => "Ashland County Jail"],
    ["city" => "Ashland", "state" => "Wisconsin"],
);

$elf = $p->cases()->where("charges", "like", "%Project ELF%")->first()
    ?? $p->cases()->create([]);
$elf->charges = "Suspected of being party to the cutting of three antenna-support poles at the Navy Project ELF installation near Clam Lake, Wisconsin — she had witnessed and videotaped the Silence Trident action.";
$elf->institution_id = $ashland->id;
$elf->convicted = "No — released at her first hearing on June 27, 2000 when the court found insufficient probable cause.";
$elf->sentence = "Held roughly three days at the Ashland County Jail, June 24 to June 27, 2000, then released for lack of probable cause.";
$elf->setPartialDate("arrest_date", 2000, 6, 24);
$elf->setPartialDate("incarceration_date", 2000, 6, 24);
$elf->setPartialDate("release_date", 2000, 6, 27);
$elf->save();

$p->refresh()->load("cases.institution");
echo "\nAFTER\n";
$total = 0;
foreach ($p->cases->sortBy(fn ($c) => (string) $c->arrest_date) as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  case  arr=".($c->formatPartialDate("arrest_date") ?: "-")
        ."  inc=".($c->formatPartialDate("incarceration_date") ?: "-")
        ."  rel=".($c->formatPartialDate("release_date") ?: "- (not documented)")
        ."  days=".($c->imprisoned_for_days ?? "null")
        ."  ".($c->institution->name ?? "-")."\n";
}
echo "  counter now: {$total} days (was 15,327 -- 41 years 11 months 18 days)\n";
echo "  birth date left unset: reported as 26 in November 1984, which spans 1957 and 1958.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
