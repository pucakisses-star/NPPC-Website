<?php

declare(strict_types=1);

/**
 * Delete the remaining non-facility institution rows the user
 * confirmed: state/county courts, agency rollups, generic
 * catch-all descriptors, and one stray action site.
 *
 * prisoner_cases.institution_id is onDelete('set null'), so every
 * affected case keeps its charges/sentence/dates/judge — only the
 * (fake) institution link is cleared. Idempotent.
 *
 * Run --dry-run first to inspect the list with case counts.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv ?? [], true);

$idsByCategory = [
    '1. State/County courts' => [
        '014d2c1e-5c5c-4c2f-99ad-775a903e8480', // Champaign County Circuit Court
        '023f555a-2c07-446d-ad9f-9fdf84bd5294', // Hennepin County District Court
        '0f27e19f-c001-4ec7-ba04-4b81e8df96f9', // Washtenaw County Circuit Court
        '1e2581b6-1efe-46c1-a1fa-908bb7e8f9df', // Concord District Court
        '1f04f4ec-a064-4b04-a050-a259166e95c8', // Fresno County Superior Court
        '21d23003-ffc6-4759-aa20-6c83cf09647e', // Philadelphia Court of Common Pleas / SCI...
        '23bc7285-85cd-455e-aecc-8aa71fce78e1', // Maricopa County Superior Court
        '2df3bb4e-8e0a-4f57-9467-0488c9fe5b01', // Buncombe County Superior Court
        '2e153244-745d-48a8-9e67-5151bbed7515', // San Bernardino County Superior Court
        '30b4a46a-59ef-4c1c-8fe4-5fe250eb1510', // Bristol County District Court
        '318ef658-b649-40f0-8ddf-3d7a102a83c2', // Alachua County Circuit Court
        '321e0bc3-1c9b-41d6-a45b-50e9e7be7fca', // Mecklenburg County District Court
        '47ed6e5b-69c5-490f-9f6e-e49e7a8a65c0', // Connecticut Superior Court
        '481ac0ca-414a-417e-bc7b-571e8ba3f20b', // Hampshire County District Court
        '4bbb9a38-7c08-43e9-ad8f-37ad2364bb99', // Robertson County Circuit Court
        '526d1976-d210-432a-8deb-150539a2e866', // Philadelphia Court of Common Pleas
        '56039dcc-df94-4c02-8198-8e6868c82b9b', // Ingham County Court
        '6c306bd9-6622-4f3b-8f17-33b2da5d8742', // Allegheny County Court of Common Pleas
        '75c26508-4f9c-4f79-8ca9-3f5d7bdce853', // Lancaster County District Court
        '77a8f61a-ba57-4ecc-96c1-d6bb483784eb', // Washington County District Court
        '7a39d98b-a0a9-48ed-9ff3-07188e2ba457', // Salt Lake County District Court
        '7e987b36-09f4-4200-8d95-66e1c37c1494', // Hamilton County Court
        '8435d131-d652-4387-ab10-9416793cc17f', // Preston County Magistrate Court
        '8737adc5-df32-4339-914a-b518eb182257', // Harris County District Court
        '8b9d133f-9454-46a2-a452-29a15b43dca9', // Shelby County Circuit Court
        '9bd0aafa-d225-4cb9-93ca-9e31580a30b6', // Bland County Circuit Court
        '99e1bc4d-ab17-4231-89b7-68a28a55b773', // Monroe County Magistrate Court
        'a3081e33-1c67-47b0-97f2-12acf0b093bc', // San Diego County Superior Court
        'a721190f-8d79-4f86-84bb-673a1b9a73d4', // Clearwater County District Court
        'a7c04cb8-e360-4734-a878-3a4ae8328ad9', // Dane County Circuit Court
        'a9962c62-0d78-4f34-89b1-dee7d15c794e', // Cook County Circuit Court
        'ac9cf7cd-9f93-4b2b-8334-ae37daa931fe', // Itasca County District Court
        'b6d2cb11-ae3e-4da6-80fb-4ffd1756bd9a', // Franklin County Municipal Court
        'b901d578-699f-4783-9f13-29a1ba561909', // Sanpete County District Court
        'c0fdc715-42be-4070-beb8-a9ddfa41693c', // Ramsey County District Court
        'c92138e0-cb5b-451d-a7b2-f1c03cdfb3a2', // Morton County District Court
        'ce7a162d-9981-421a-a0e0-acab9d792826', // Multnomah County District Court
        'd035e479-a007-4e45-9800-ef8b691298df', // Monroe County Circuit Court
        'd97fbe20-841b-4cfc-b65b-b55bc7d9f7f0', // Riverside County Superior Court
        'dadb21eb-8d7f-489c-be45-022ef0cc6a16', // Los Angeles County Superior Court
        'e088ec5d-b283-47a5-8c67-6cad19310dea', // Multnomah County Circuit Court
        'c30b31d9-9d8c-4aa1-b6da-208fe754d017', // Rhea County Courthouse / no incarceration
        'a64db77d-d8ca-431a-ae6a-db1819ae2e6a', // M.D. Fla. federal court holding
        '6f9c8b45-4eaf-47f0-a063-5807b333fc51', // UK custody / Westminster Magistrates Court
    ],
    '2. Agency rollups / vague jurisdictions' => [
        '8ec9109c-48d0-408b-9788-6746945de840', // Federal Bureau of Prisons
        '940a4faf-95b9-42d3-b5ff-5387ffa14649', // Michigan Department of Corrections
        'c1401f30-5980-4efe-a8c3-eafb7887472b', // South Carolina Department of Corrections
        'c335085f-abed-46a8-903f-c1a6f705622b', // Pennsylvania Department of Corrections (SCI Greene supermax)
        '7f809eb4-2603-4ae3-a8f8-4c3df1554380', // Western District of Missouri
    ],
    '3. Generic / catch-all descriptors' => [
        '2b2d1ca1-344a-4909-8359-bfbe7ae5b2f7', // Washington state and county jails
        '33f380b1-85b8-46bd-91fa-4c666e9e0f6c', // Suffolk County jail / state psychiatric facility
        '3d93f734-9970-4a25-a0de-ea9bf75a03bc', // ICE detention (Minnesota and Texas facilities)
        '4af5c38e-0b1a-4194-9890-abf02a99391a', // West Virginia state custody
        '5da5f09c-df04-4463-8a2b-3741a58a6d12', // Various Southern jails
        '5dfad8c6-4e0b-49d6-9707-f9e377106e1d', // Home confinement (Wagram, NC) on federal pretrial release
        '61305a55-6847-4978-bbeb-61df41f91e29', // Cleveland city/county jail; North Carolina state prison
        '6a8c367e-2b95-441e-9bd5-88502c996444', // New York City jails
        '70ff7b26-24fb-4544-8a73-8aab40ea9bae', // Selma / Birmingham city jails
        '7caf6b95-37de-494a-9111-993036f4c524', // War Tax Resistance
        '8e57c233-158b-451d-aef0-705bf0e66351', // Mississippi state and federal courts
        '8ffb58bc-cf3c-4f22-ae5d-532fff438faf', // ICE Detention Facility, Texas
        '99c457f1-d7d4-49ff-ae80-465814080b1d', // ICE detention (county jail, Indiana)
        '9ff88832-231a-4ced-b09c-70a767cfd56a', // Idaho county jails (Caldwell, Wallace, Rathdrum)
        'a231a0ac-6bd4-476a-a056-3af9a98343d5', // San Quentin / California state courts
        'a346f3ca-d55c-4a47-8a40-ae5c5f96e019', // Wisconsin state prison
        'a4ede4d6-80c2-47dc-84d1-26f497b5be29', // Nashville / Memphis city jails
        'a90abba2-9e63-4e50-a8cc-225379557db9', // ICE Detention (New York / self-removal)
        'a934fb6b-7331-48e1-b4b1-d12e06bc8ada', // UK HM Prison
        'a972f962-cee2-4c90-ad4e-1f13de540ff0', // Allegheny County / federal Pittsburgh courts
        'b8915581-159b-4afb-bd33-62e78dc91421', // ICE custody (New York City field office)
        'badb4751-4dcb-48ab-956f-c0ab4ee459ce', // North Carolina state courts
        'd77d5491-7989-4898-a9ed-9f4da00bfd02', // Illinois state prison
        'dc2e26cd-ee1b-4fad-9c6e-7e76fed6f69a', // U.S. State Department exclusion / Mexican custody
        'e972d6b6-35d5-47f1-840f-aa9c6ee00558', // California state and federal courts
        '57eba76b-8843-4e3e-af4d-0876f052b4b0', // ICE Detention (Washington State)
        '8ae8ea68-b6f1-4f4b-b971-265b6d7eece5', // NYPD / Columbia University Public Safety
        'd5655ad5-63fc-4cd5-b5a3-744a4f888add', // NYPD custody (Hamilton Hall protest)
    ],
    '4. Action sites (not facilities of incarceration)' => [
        '67e196ba-fa54-46e8-9385-8877ac6a0a98', // Bath Iron Works
    ],
];

$totalIds = 0;
$totalCases = 0;

echo "==== Deletion plan ====\n";
foreach ($idsByCategory as $label => $ids) {
    echo "\n-- {$label} --\n";
    foreach ($ids as $id) {
        $row = DB::selectOne(
            "SELECT id, name, city, state,
                    (SELECT COUNT(*) FROM prisoner_cases pc WHERE pc.institution_id = institutions.id) AS case_count
             FROM institutions WHERE id = ?",
            [$id]
        );
        if (! $row) {
            echo sprintf("  %s  [missing — already deleted]\n", substr($id, 0, 8));
            continue;
        }
        echo sprintf("  %s  [cases=%d]  %s  (%s, %s)\n",
            substr($row->id, 0, 8),
            (int) $row->case_count,
            $row->name,
            (string) ($row->city ?: '-'),
            (string) ($row->state ?: '-')
        );
        $totalIds++;
        $totalCases += (int) $row->case_count;
    }
}

echo "\n==== Summary ====\n";
echo "  rows to delete:                     {$totalIds}\n";
echo "  cases that will lose institution_id: {$totalCases}\n";
echo "  (case data — charges/dates/sentence/judge — is preserved)\n";

if ($dryRun) {
    echo "\nDry run — nothing deleted. Re-run without --dry-run to execute.\n";
    return;
}

echo "\nExecuting deletes...\n";
$deleted = 0;
DB::transaction(function () use ($idsByCategory, &$deleted) {
    foreach ($idsByCategory as $ids) {
        foreach ($ids as $id) {
            $inst = Institution::find($id);
            if ($inst) {
                $inst->delete();
                $deleted++;
            }
        }
    }
});
echo "Deleted: {$deleted}\n";
echo "Done.\n";
