<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::table('petition_signatures', function (Blueprint $table) {
            $table->boolean('display_publicly')->default(false)->after('custom_message');
        });
    }

    public function down(): void
    {
        Schema::table('petition_signatures', function (Blueprint $table) {
            $table->dropColumn('display_publicly');
        });
    }
};
