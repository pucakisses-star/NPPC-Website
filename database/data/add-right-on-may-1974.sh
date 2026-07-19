#!/usr/bin/env bash
#
# Mining results from Right On! Vol. 2 No. 9 (May 1974), the East
# Coast BPP paper archived by archive:add-right-on-may-1974:
#
#  1. Adds Gary Garrison — Escuela Tlatelolco teacher swept up in the
#     January 1974 Denver repression of the Crusade for Justice.
#  2. Enriches Thomas Wansley with the 1973-74 bond/reversal/pardon
#     fight the issue reports, and Martin Sostre with Judge Curtin's
#     April 1974 denial and the Clinton solitary record.
#
# Already present: Joseph Remiro and Russell Little (the SLA prisoners
# in the San Quentin Adjustment Center), Imari Obadele, Geronimo
# Pratt, Martin Sostre, Thomas Wansley. Skipped per the custody
# standard: Nancy Ling Perry (fugitive, died May 17, 1974 without ever
# being in custody), Luis "Junior" Martinez (killed by police, not
# imprisoned), the unnamed Buffalo Five, and the Atlanta police-killing
# victims.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/add-right-on-may-1974.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Gary Garrison","first_name":"Gary","last_name":"Garrison","description":"Gary Garrison was a teacher at Escuela Tlatelolco, the Chicano alternative school founded by the Crusade for Justice in Denver. In January 1974, amid a year in which over one hundred Crusade activists and associates were arrested or taken to court, he was arrested for investigation of an alleged attempted bombing and held on a 100,000-dollar bond. After the Crusade organized community support and filed a ten-million-dollar lawsuit against the media over its coverage, he was released on his own recognizance — then re-arrested days later and held on a 50,000-dollar bond, which a judge cut to 7,500 dollars over the district attorney'"'"'s vigorous resistance because the charges were so obviously baseless. Originally arrested for attempted bombing and attempted murder, he ended up charged with attempted arson, mischief and conspiracy. The Denver Chicano Liberation Defense Committee called a national day of solidarity for March 17, 1974, the anniversary of the police attack on Escuela Tlatelolco in which Luis Junior Martinez was killed. Reconstructed from Right On! Vol. 2 No. 9 (May 1974); the disposition of his case has not been located.","state":"Colorado","race":"Chicano","gender":"Male","ideologies":["Chicano movement"],"affiliation":["Crusade for Justice"],"era":"1970s","in_custody":false,"released":true,"cases":[{"charges":"Attempted bombing and attempted murder (later reduced to attempted arson, mischief and conspiracy) — January 1974 Denver arrest amid the repression of the Crusade for Justice; held on $100,000 bond, released on recognizance, re-arrested on $50,000 bond reduced to $7,500","convicted":"Unknown — the movement press reported the charges as a frame-up; disposition not located"}]}' || true

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$appendOnce = function ($p, string $marker, string $paragraph): void {
    if (! $p || str_contains((string) $p->description, $marker)) { return; }
    $p->description = trim((string) $p->description) . "\n\n" . $paragraph;
    $p->save();
    echo "DESC {$p->slug}\n";
};

$appendOnce($find("thomas-wansley"), "Holton", "Right On! (May 1974) reported the last stage of the pardon fight: freed on bond in January 1973 when a federal judge overturned his conviction, Wansley was returned to prison in mid-November 1973 after the U.S. Court of Appeals reversed that ruling, and was held at the prison camp in Chesterfield County, Virginia. On January 8, 1974, days before leaving office, Governor Linwood Holton refused to pardon him despite delegations, thousands of petitions, eleven members of Congress and some sixty elected officials; on the eve of Mills Godwin'"'"'s inauguration his supporters, led by his mother Willie Mae Thornton, rallied in Richmond and marched to visit him at the camp.");

$appendOnce($find("martin-sostre"), "Curtin", "In April 1974, federal Judge John T. Curtin denied Sostre a new trial, ruling the recantation of the State'"'"'s star witness Arto Williams — who had admitted under oath in May 1973 that he helped officer Alvin Gristmacher and Erie County Sheriff Michael Amico frame Sostre in July 1967 — \"unworthy of belief,\" even as Gristmacher stood charged in the theft of half a million dollars of heroin from the police narcotics locker. Right On! reported that Sostre had by then spent 14 months in solitary at Clinton, beaten six times by guards, and faced a possible life sentence on pending charges of assaulting the very guards who attacked him (Right On!, May 1974).");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

php artisan archive:add-right-on-may-1974

echo
echo "Done. Right On! May 1974 applied."
