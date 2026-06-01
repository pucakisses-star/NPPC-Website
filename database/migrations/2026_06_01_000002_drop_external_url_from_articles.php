<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// External links moved out of Articles into their own DashboardLink model, so
// the short-lived articles.external_url column is removed again.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'external_url')) {
                $table->dropColumn('external_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (! Schema::hasColumn('articles', 'external_url')) {
                $table->string('external_url', 2048)->nullable()->after('slug');
            }
        });
    }
};
