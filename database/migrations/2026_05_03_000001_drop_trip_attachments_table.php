<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('trip_attachments');
    }

    public function down(): void
    {
        Schema::create('trip_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('day');
            $table->string('label')->nullable();
            $table->string('file_path');
            $table->string('file_type')->default('image');
            $table->string('preview_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
