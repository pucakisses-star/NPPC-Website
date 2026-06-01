<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Curated links for the dashboard ticker/newswire, managed separately from
// Articles. An item appears once published_at is set and in the past.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('url', 2048);
            $table->string('source')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_links');
    }
};
