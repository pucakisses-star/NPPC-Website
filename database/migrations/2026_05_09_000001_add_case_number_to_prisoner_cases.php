<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('prisoner_cases', function (Blueprint $table) {
            $table->unsignedInteger('case_number')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('prisoner_cases', function (Blueprint $table) {
            $table->dropUnique(['case_number']);
            $table->dropColumn('case_number');
        });
    }
};
