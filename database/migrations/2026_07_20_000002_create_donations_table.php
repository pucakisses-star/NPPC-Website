<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Donations recorded from the Stripe checkout callback so they are
// visible in the admin, not only in the Stripe dashboard.
return new class() extends Migration {
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('stripe_session_id')->unique();
            $table->unsignedInteger('amount');          // cents
            $table->string('currency', 8)->default('usd');
            $table->string('mode');                     // payment (one-time) or subscription (recurring)
            $table->string('status');                   // Stripe session status
            $table->string('payment_status');           // paid / unpaid / no_payment_required
            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
