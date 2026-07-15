<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('petitions', function (Blueprint $table) {
            // Which state the campaign concerns ("Federal" for nationwide /
            // federal-custody petitions). Drives the Filter by State dropdown
            // on /petitions; backfilled by `php artisan petitions:add-images`.
            $table->string('state')->nullable()->after('recipients');
        });
    }

    public function down(): void {
        Schema::table('petitions', function (Blueprint $table) {
            $table->dropColumn('state');
        });
    }
};
