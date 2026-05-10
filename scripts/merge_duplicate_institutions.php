<?php

declare(strict_types=1);

/**
 * Merge institutions that are obviously the same physical place
 * written multiple ways (FCI vs Federal Correctional Institution,
 * abbreviations, casing, parenthetical clarifications). For each
 * group:
 *   - All prisoner_cases pointing at a duplicate are reassigned to
 *     the canonical row.
 *   - Any non-empty city/state/security/mailing_address/
 *     physical_address/lat/lng on a duplicate fills in a blank slot
 *     on the canonical row.
 *   - The duplicate institution row is deleted.
 *
 * Conservative: leaves event-specific variants alone ("FCI Danbury
 * — Hollywood Ten" stays separate from "FCI Danbury") and leaves
 * different-security-level federal facilities separate (USP vs FCI
 * vs FPC at the same site). After the merge, run
 *   php artisan institutions:backfill-coordinates
 * to re-geocode any newly-merged rows that still lack coordinates.
 *
 * Idempotent: dupes that no longer exist on a re-run are skipped.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use Illuminate\Support\Facades\DB;

// canonical_id => [ duplicate_id_1, duplicate_id_2, ... ], note
$groups = [
    // MDC Brooklyn (Maduro, Cilia Flores et al.)
    'c3068605-3613-49c1-8b95-04f1ada17302' => [
        ['a5902e9c-599b-4504-9941-5e2d5854c66b', '0c62e7ae-91a0-43b8-a37e-ff3c6d390967', '0d778d79-b246-45a0-9825-b7467c883088'],
        'MDC Brooklyn variants',
    ],
    // Ashland FCI (KY)
    '9adfb6b9-53af-41b8-a78d-4434c5bb07ec' => [
        ['19fc0d3b-bfc4-4523-8d92-3fccdd0c75bc', '32454547-c2fb-48b5-a7f5-c4b8885c414a'],
        'Ashland FCI / FCI Ashland',
    ],
    // FCI Allenwood Low
    'e27a5c3a-2c4c-45d7-8fb1-970777bbcfb4' => [
        ['f395cbed-1ee6-4f5a-9643-2e5262fd84ce'],
        'Allenwood Low FCI / FCI Allenwood Low',
    ],
    // FCI Allenwood Medium
    'd5a92166-8bef-4f66-a09c-f958803815a8' => [
        ['aa06e99e-0ae5-4e55-9e98-760e1b3bd9fe'],
        'Allenwood Medium FCI / FCI Allenwood Medium',
    ],
    // Beckley FCI
    '7162d2c6-a929-4638-a79f-e8703881ad04' => [
        ['bb75f78b-b866-47ff-88c4-5fec94f22bcd'],
        'Beckley FCI / FCI Beckley',
    ],
    // Bedford Hills (the "for Women" suffix is just a clarifier; same facility)
    'de928665-a105-46ef-9e81-3cb4848f48bd' => [
        ['99e7dadb-719b-4939-a8ff-0c92f12421f7'],
        'Bedford Hills Correctional Facility',
    ],
    // HMP Cornton Vale (Scotland women's)
    '3dbd7b2d-ee3f-4a73-a245-10c818c995da' => [
        ['5a1bd09f-eacf-43f6-973d-c78aff697bb3'],
        'Cornton Vale / HMP Cornton Vale',
    ],
    // Edgefield FCI
    '87c62313-db4c-4f5c-a96f-a32de37154dd' => [
        ['063d66b2-70f6-439b-b895-ec4a770a634a'],
        'Edgefield FCI / FCI Edgefield',
    ],
    // Essex County Jail Newark
    'b7d9f423-ff91-4bf2-9884-82f9d5462041' => [
        ['aa6cfea5-85ec-4b06-946c-540c59dd5d98'],
        'Essex County Jail (Newark) duplicate',
    ],
    // ADX Florence (Federal Supermax)
    'f040b93c-8fe0-401a-9e61-222f52928788' => [
        ['2c8c2f9b-1050-4795-aaf6-9597e1aae041', '3d75ac37-dbab-4add-ad8e-70fc9cb0f81e', '0c2498d6-d5bd-4731-9bf4-b115d825df90'],
        'ADX Florence variants (Florence ADMAX USP / United States Penitentiary, ADX Florence)',
    ],
    // Fort Sill (military confinement)
    'ee298c47-f227-4c88-b0c8-e3b2dc68f094' => [
        ['a45bd5ce-13a7-4774-853a-8f95a1570020', '8cc2db71-01d1-4336-8e73-edd3ecd026e7'],
        'Fort Sill brig / Military Prison variants',
    ],
    // USP Marion
    '67c9660a-481b-49d4-abb7-f1e245f8c470' => [
        ['ce7f49cf-8b31-4b4f-b660-8d9279cb9576', '609d7b17-dbd2-4ac8-a188-e1dbd0e41b42'],
        'USP Marion variants',
    ],
    // MDC Los Angeles
    'b3765685-ade1-4556-8593-32e6b073c2da' => [
        ['2f96b187-8c18-40a0-bddd-f25ab4052a8a', '6e81ebd4-70d7-499d-9639-9489b9c8110e'],
        'Los Angeles MDC variants',
    ],
    // Federal Detention Center Miami
    '74f4f7e2-1be8-4dca-8911-9b5a618d4302' => [
        ['bf91f288-45e2-493e-8f28-399d5b018aa7', '20a83e89-e947-43de-b985-64b58ee57d1c'],
        'FDC Miami variants',
    ],
    // Missouri State Penitentiary (Jefferson City) — keep Abrams-trial event row separate
    '31b0045d-652d-42e1-b849-91fefbf343ad' => [
        ['6d1afd40-8903-43d9-b5e5-6df92ec21baa'],
        'Missouri State Penitentiary, Jefferson City',
    ],
    // NY State Department of Corrections (DOCCS)
    'f5add62d-5186-4cee-87ac-17569d1e02b9' => [
        ['4cab7c57-162e-4dad-8424-ff82ced0732c', '6aab5a52-8063-45d8-a2a3-356c2a0e95c4', '6a9259e0-3f63-4854-a3a3-0b4000ebd964', '68f85c08-74a7-48a3-897c-47a586b87757'],
        'NY DOCCS variants (with/without "state", with/without facility list)',
    ],
    // Prairieland ICE Detention Center (TX)
    'ece5036e-e48e-46cc-b9dd-321a74ec3e2b' => [
        ['db513981-6714-488c-b43a-7d73de07bb8e', 'a63395c0-d091-4716-aaa5-d4aed21305fd'],
        'Prairieland Detention Center variants',
    ],
    // San Quentin (renamed San Quentin Rehabilitation Center 2023; leave Death Row separate)
    '0099e875-fa60-48ba-930e-a0f9a20479e5' => [
        ['cb2db037-ac2e-4a40-9915-08199efee199'],
        'San Quentin (renamed Rehabilitation Center 2023)',
    ],
    // Sing Sing (leave Rosenberg event row separate)
    'c49df20c-8b57-4b5b-8c5e-a2ede73a66c4' => [
        ['a4353502-c5db-4a30-a44c-ef70ae861ff0'],
        'Sing Sing Correctional Facility',
    ],
    // U.S. Medical Center for Federal Prisoners (Springfield, MO)
    'cb8e8036-4a3e-41ac-ab73-3ab896c03166' => [
        ['2c57686d-126d-4ab4-be83-2ebbac5dc83c'],
        'U.S. Medical Center for Federal Prisoners',
    ],
    // USP Atlanta (event-specific rows kept separate)
    'f77d58f4-ad21-48df-8007-2f6424aed43f' => [
        ['977299a7-e877-43a6-9213-e4752957a9ca', '1b4451d8-fe86-467b-aaa5-a8e88263cb14', '6996be6f-2406-445c-afd6-41b973c3aaa2'],
        'USP Atlanta variants',
    ],
    // USP Leavenworth (event-specific rows kept separate)
    '22ad9197-2c64-4712-aa73-9b6d66655992' => [
        ['7a95c1b3-8c2b-46a3-9944-814eccb6de6d'],
        'USP Leavenworth variants',
    ],
    // USP Lewisburg (FCI Lewisburg & event-specific kept separate)
    'df689c4b-2900-4bb4-bb53-55fd5fd1abd7' => [
        ['2d2c2be9-24e3-4e62-acfe-623722d07ac4'],
        'USP Lewisburg variants',
    ],
    // Communications Management Unit (generic rollups; specific Marion CMU/Terre Haute CMU could be split later)
    '4a5200fd-2cb3-41ef-9260-a10bd3806833' => [
        ['1fac59a4-c4bb-40c1-971a-a4ebb47aa98d', '22a7c35c-8b73-4b27-8ced-47edb1481e62', 'db26b293-b979-49e7-b931-5e34c8f9a26d'],
        'Communications Management Unit (BOP)',
    ],
    // FCI Yazoo City + FCI Yazoo City Medium (same complex; admin uses them interchangeably)
    '965ac789-2b5e-4c2a-b3c4-4c74145419d0' => [
        ['254c6fa2-6cd8-4d04-81a3-a556ee2a1783'],
        'FCI Yazoo City variants',
    ],
];

$mergedGroups = 0;
$movedCases   = 0;
$deleted      = 0;
$skippedMissingCanon = 0;

foreach ($groups as $canonId => [$dupeIds, $note]) {
    $canon = Institution::find($canonId);
    if (! $canon) {
        echo "[skip] canonical {$canonId} not found ({$note})\n";
        $skippedMissingCanon++;
        continue;
    }

    $thisGroupCases = 0;
    $thisGroupDeleted = 0;

    DB::transaction(function () use ($canon, $dupeIds, &$thisGroupCases, &$thisGroupDeleted) {
        foreach ($dupeIds as $dupeId) {
            $dupe = Institution::find($dupeId);
            if (! $dupe) continue;

            // Reassign cases
            $moved = DB::table('prisoner_cases')
                ->where('institution_id', $dupe->id)
                ->update(['institution_id' => $canon->id]);
            $thisGroupCases += (int) $moved;

            // Fill blank fields on canon from dupe
            foreach (['city', 'state', 'security', 'mailing_address', 'physical_address', 'lat', 'lng'] as $col) {
                $canonVal = trim((string) $canon->getAttribute($col));
                $dupeVal  = trim((string) $dupe->getAttribute($col));
                if ($canonVal === '' && $dupeVal !== '') {
                    $canon->setAttribute($col, $dupe->getAttribute($col));
                }
            }
            $canon->save();

            $dupe->delete();
            $thisGroupDeleted++;
        }
    });

    echo sprintf("[merged] %s -> %s :: cases moved=%d, dupes deleted=%d  (%s)\n",
        substr($canonId, 0, 8),
        $canon->name,
        $thisGroupCases,
        $thisGroupDeleted,
        $note
    );

    $mergedGroups++;
    $movedCases += $thisGroupCases;
    $deleted    += $thisGroupDeleted;
}

echo "\nDone.\n";
echo "  groups processed:       {$mergedGroups}\n";
echo "  prisoner_cases moved:   {$movedCases}\n";
echo "  duplicate rows deleted: {$deleted}\n";
echo "  missing canonicals:     {$skippedMissingCanon}\n";
echo "\nNext: php artisan institutions:backfill-coordinates\n";
