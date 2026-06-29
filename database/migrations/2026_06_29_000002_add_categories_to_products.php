<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Additional categories a product appears under, on top of its primary
            // `category` (e.g. a bumper sticker is Stickers AND Accessories). Used
            // for the store's category filter pills.
            $table->json('categories')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('categories');
        });
    }
};
