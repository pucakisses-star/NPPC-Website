<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->string('photo_source_url')->nullable()->after('photo');
            $table->string('photo_attribution')->nullable()->after('photo_source_url');
            $table->string('photo_license')->nullable()->after('photo_attribution');
        });
    }

    public function down(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->dropColumn(['photo_source_url', 'photo_attribution', 'photo_license']);
        });
    }
};
