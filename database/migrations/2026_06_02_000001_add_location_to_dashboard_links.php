<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Optional coordinates so a dashboard link can also be plotted as an event
// marker on the map. Links without coordinates simply stay feed/ticker-only.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_links', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('source');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('location_label')->nullable()->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_links', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'location_label']);
        });
    }
};
