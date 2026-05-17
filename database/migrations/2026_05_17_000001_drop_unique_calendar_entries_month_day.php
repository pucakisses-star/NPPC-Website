<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop the UNIQUE constraint on calendar_entries(month, day).
 * Multiple historically-significant events can fall on the same
 * date — e.g. May 4: Kent State (1970) AND the Original 13 Freedom
 * Riders departing Washington DC (1961). The original create
 * migration enforced one entry per day; relax that.
 */
return new class() extends Migration {
    public function up(): void
    {
        Schema::table('calendar_entries', function (Blueprint $table) {
            $table->dropUnique(['month', 'day']);
        });
    }

    public function down(): void
    {
        Schema::table('calendar_entries', function (Blueprint $table) {
            $table->unique(['month', 'day']);
        });
    }
};
