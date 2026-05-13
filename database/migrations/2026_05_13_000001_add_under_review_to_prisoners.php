<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->boolean('under_review')->default(false)->after('awaiting_trial');
            $table->index('under_review');
        });
    }

    public function down(): void
    {
        Schema::table('prisoners', function (Blueprint $table) {
            $table->dropIndex(['under_review']);
            $table->dropColumn('under_review');
        });
    }
};
