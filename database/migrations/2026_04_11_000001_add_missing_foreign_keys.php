<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
        });

        Schema::table('calendar_entries', function (Blueprint $table) {
            $table->foreign('prisoner_id')->references('id')->on('prisoners')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['author_id']);
        });

        Schema::table('calendar_entries', function (Blueprint $table) {
            $table->dropForeign(['prisoner_id']);
        });
    }
};
