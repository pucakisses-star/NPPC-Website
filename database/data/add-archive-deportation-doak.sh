#!/usr/bin/env bash
#
# Register the archive record for William Z. Foster's 1932 article
# "Deportation: Doak Wants You!" (Labor Defender / International Labor Defense),
# denouncing the Doak deportation drive against foreign-born labor and Communist
# organizers -- including National Miners Union leader Frank Borich, whose
# portrait was cropped from this document. PDF: public/pdfs/pamphlets/.
#
# Idempotent (updateOrCreate by slug). Run from the repo root:
#   bash database/data/add-archive-deportation-doak.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\ArchiveRecord;

ArchiveRecord::updateOrCreate(
    ["slug" => "deportation-doak-wants-you-1932"],
    [
        "title" => "Deportation: Doak Wants You!",
        "description" => "William Z. Foster article denouncing the 1932 deportation drive under Secretary of Labor William Doak, which targeted foreign-born workers and Communist labor organizers -- among them National Miners Union general secretary Frank Borich. Published in the Labor Defender, the magazine of the International Labor Defense.",
        "record_type" => "document",
        "source_format" => "pamphlet",
        "file" => "/pdfs/pamphlets/deportation-doak-1932.pdf",
        "year" => 1932,
        "date" => "1932",
        "authors" => "William Z. Foster",
        "publisher" => "International Labor Defense",
        "collection" => "Labor Defender",
        "subjects" => ["Deportation", "Labor", "Communism", "Political Prisoners"],
        "is_digitized" => true,
        "published" => true,
    ]
);

$r = ArchiveRecord::where("slug", "deportation-doak-wants-you-1932")->first();
echo "Archive record ready: {$r->title} -> {$r->file}\n";
echo "Done.\n";
'

echo
echo "Done."
