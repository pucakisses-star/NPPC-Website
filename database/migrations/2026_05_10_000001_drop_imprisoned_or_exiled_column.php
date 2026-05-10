<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->dropColumn('imprisoned_or_exiled');
        });
    }

    public function down(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->boolean('imprisoned_or_exiled')->default(false)->after('currently_in_exile');
        });

        // Repopulate from the live in_custody/currently_in_exile values
        // so the rollback restores the column with valid data.
        \Illuminate\Support\Facades\DB::statement(
            'UPDATE prisoners SET imprisoned_or_exiled = (in_custody = 1 OR currently_in_exile = 1)'
        );
    }
};
