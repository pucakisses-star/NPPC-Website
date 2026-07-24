<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('prisoners', function (Blueprint $table) {
            // Flags brief / minor detentions (short holds, dismissed or
            // pretrial-only cases) so they can be filtered in the admin.
            $table->boolean('minor_case')->default(false)->after('awaiting_trial');
        });
    }

    public function down(): void {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->dropColumn('minor_case');
        });
    }
};
