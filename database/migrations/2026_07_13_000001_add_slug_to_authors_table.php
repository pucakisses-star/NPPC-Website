<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('authors', function (Blueprint $table) {
            // Nullable so existing rows migrate cleanly; backfilled by
            // `php artisan authors:generate-slugs` (never by a migration).
            $table->string('slug')->nullable()->unique()->after('name');
        });
    }

    public function down(): void {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
