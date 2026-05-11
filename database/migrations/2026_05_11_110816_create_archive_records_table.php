<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::create('archive_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('record_type')->default('document'); // document | audio | video
            $table->string('source_format')->nullable(); // periodical | monograph | mp3 | flyer | article | book | pamphlet | other
            $table->string('file')->nullable(); // storage path to PDF/audio/video
            $table->string('thumbnail')->nullable(); // storage path to image
            $table->unsignedSmallInteger('year')->nullable();
            $table->date('date')->nullable();
            $table->string('publisher')->nullable();
            $table->string('authors')->nullable(); // comma-separated
            $table->string('collection')->nullable();
            $table->string('volume')->nullable();
            $table->json('subjects')->nullable();
            $table->boolean('is_digitized')->default(true);
            $table->boolean('published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('record_type');
            $table->index('collection');
            $table->index('year');
            $table->index('published');
        });
    }

    public function down(): void {
        Schema::dropIfExists('archive_records');
    }
};
