<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A documented time served, stated in whole months by the source.
 *
 * imprisoned_for_days is derived from the incarceration and release dates, so
 * it can only ever be as good as those dates. Plenty of records have the
 * opposite shape: the duration is the well-attested fact and the endpoints are
 * not. Bill Sutherland is the case in hand — every surviving summary agrees he
 * served 38 months of a four-year sentence, while the summaries disagree about
 * the years themselves (1942-45, 1943-45, 1943-46), and no prison register has
 * turned up to fix an admission or discharge day.
 *
 * When set, this column is authoritative over the date arithmetic, and the
 * public counter reads "38 Months" instead of manufacturing a day-level span
 * out of two dates that cannot support one.
 */
return new class() extends Migration {
    public function up(): void
    {
        Schema::table('prisoner_cases', function (Blueprint $table) {
            $table->integer('imprisoned_for_months')->nullable()->after('imprisoned_for_days');
        });
    }

    public function down(): void
    {
        Schema::table('prisoner_cases', function (Blueprint $table) {
            $table->dropColumn('imprisoned_for_months');
        });
    }
};
