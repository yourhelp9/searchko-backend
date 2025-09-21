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
        Schema::create('daily_menus', function (Blueprint $table) {
            $table->id();
            $table->date('menu_date');
            $table->enum('meal_type', ['Breakfast', 'Lunch', 'Dinner']);
            
            // Yeh 'menu_items' table se link honge
            $table->foreignId('option_1_id')->nullable()->constrained('menu_items')->onDelete('set null');
            $table->foreignId('option_2_id')->nullable()->constrained('menu_items')->onDelete('set null');
            
            $table->timestamps();

            // Ek din me ek meal type (jaise Lunch) ki entry ek hi baar ho sakti hai
            $table->unique(['menu_date', 'meal_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_menus');
    }
};