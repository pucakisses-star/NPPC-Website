<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (! Schema::hasColumn('articles', 'external_url')) {
                // When set, the article behaves as a "link" item: it points to
                // this external URL (in the ticker/newswire and elsewhere)
                // instead of an internal /news/{slug} page.
                $table->string('external_url', 2048)->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'external_url')) {
                $table->dropColumn('external_url');
            }
        });
    }
};
