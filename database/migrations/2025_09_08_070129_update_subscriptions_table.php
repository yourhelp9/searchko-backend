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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Purane columns ko hatayein
            $table->dropColumn(['plan_name', 'meal_types', 'start_date', 'end_date']);

            // Naye columns ko jodein
            $table->integer('plan_id')->nullable()->after('user_id');
            $table->integer('meals_per_day')->nullable()->after('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Purane columns ko wapas jodein
            $table->string('plan_name')->nullable();
            $table->longText('meal_types')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Naye columns ko hatayein
            $table->dropColumn(['plan_id', 'meals_per_day']);
        });
    }
};