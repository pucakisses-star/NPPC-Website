<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stores how precise each partial date is. The date columns themselves keep a
 * full Y-M-D (missing parts default to 01) so all existing logic — calendar
 * generation, imprisoned_for_days, age, cost totals — keeps working unchanged.
 * This JSON column records, per date field, whether the user actually entered
 * 'year', 'month' (year+month) or 'day' (full) so the display can blank out the
 * parts that were never filled in. Absent/null precision is treated as 'day'
 * (full), so every pre-existing row renders exactly as before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->json('date_precision')->nullable()->after('death_date');
        });

        Schema::table('prisoner_cases', function (Blueprint $table) {
            $table->json('date_precision')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->dropColumn('date_precision');
        });

        Schema::table('prisoner_cases', function (Blueprint $table) {
            $table->dropColumn('date_precision');
        });
    }
};
