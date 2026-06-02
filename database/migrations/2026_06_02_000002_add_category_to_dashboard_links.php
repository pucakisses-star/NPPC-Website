<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Category for a dashboard event marker: protest, arrest, prosecution or other.
// Drives the map marker colour and the legend filters. Defaults to "protest".
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_links', function (Blueprint $table) {
            $table->string('category')->default('protest')->after('location_label');
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_links', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
