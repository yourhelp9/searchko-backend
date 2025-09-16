<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            // ▼▼▼ YEH NAYI LINES ADD KI GAYI HAIN ▼▼▼
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->enum('meal_type', ['Breakfast', 'Lunch', 'Dinner']);
            // ▲▲▲ YAHAN TAK ▲▲▲
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};