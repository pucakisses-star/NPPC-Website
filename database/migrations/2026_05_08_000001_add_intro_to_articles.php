<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('articles') && ! Schema::hasColumn('articles', 'intro')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->text('intro')->nullable()->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'intro')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn('intro');
            });
        }
    }
};
