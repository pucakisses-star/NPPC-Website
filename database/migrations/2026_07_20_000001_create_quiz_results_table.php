<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Anonymous Civic Profile quiz results, posted by the quiz when the
// results screen renders. Reviewed in Filament under Submissions.
return new class() extends Migration {
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('profile');                       // values archetype, e.g. "The Dissent Defender"
            $table->json('values_scores');                   // {liberty: 83, solidarity: 67, ...} as 0-100
            $table->unsignedTinyInteger('engagement_score'); // 0-30
            $table->string('engagement_tier');               // Witness / Supporter / Advocate / Organizer
            $table->unsignedTinyInteger('perception_avg_error')->nullable(); // avg points off, null if skipped
            $table->unsignedTinyInteger('knowledge_correct');
            $table->unsignedTinyInteger('knowledge_total');
            $table->unsignedTinyInteger('knowledge_pct');
            $table->string('knowledge_tier');
            $table->json('answers')->nullable();             // raw answers per part
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
